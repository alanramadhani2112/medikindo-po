<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function __construct(
        private readonly AuditService $auditService,
        private readonly InventoryService $inventoryService,
    ) {}

    // -----------------------------------------------------------------------
    // Confirm Receipt (Clinic Admin / Procurement Staff)
    // Gate: PO must be in 'approved' status (delivery happens outside system)
    // Validates: per-item quantity <= remaining ordered
    // Transitions: GR → partial or completed; PO → completed (when all received)
    // -----------------------------------------------------------------------

    public function confirmReceipt(
        PurchaseOrder $po,
        User $actor,
        array $items,
        ?string $notes = null,
    ): GoodsReceipt {
        // Gate: only for approved POs (delivery tracking removed - happens outside system)
        if (! $po->isApproved()) {
            throw new DomainException(
                "Goods receipt can only be confirmed when PO status is 'approved'. Current: [{$po->status}]."
            );
        }

        // Gate: actor must be Organization role
        if (! $actor->hasAnyRole(['Healthcare User', 'Procurement Staff', 'Super Admin'])) {
            throw new DomainException('Only Organization staff or Super Admin can confirm goods receipt.');
        }

        return DB::transaction(function () use ($po, $actor, $items, $notes) {
            
            $allFulfilled = true;
            $grItems = [];

            // --- Validate and prepare items ---
            foreach ($items as $item) {
                $poItem = $po->items()->findOrFail($item['purchase_order_item_id']);

                // --- Per-item qty validation ---
                $alreadyReceived = $po->goodsReceipts()
                    ->join('goods_receipt_items', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
                    ->where('goods_receipt_items.purchase_order_item_id', $poItem->id)
                    ->sum('goods_receipt_items.quantity_received');

                $remaining = $poItem->quantity - $alreadyReceived;

                if ($item['quantity_received'] > $remaining) {
                    throw new DomainException(
                        "Quantity received ({$item['quantity_received']}) exceeds remaining ordered quantity ({$remaining}) for product [{$poItem->product?->name}]."
                    );
                }

                if ($item['quantity_received'] < $remaining) {
                    $allFulfilled = false;
                }

                $grItems[] = [
                    'po_item' => $poItem,
                    'data' => $item,
                ];
            }

            // --- Determine GR Status: partial or completed ---
            $grStatus = $allFulfilled ? GoodsReceipt::STATUS_COMPLETED : GoodsReceipt::STATUS_PARTIAL;

            // --- Build GR record with final status ---
            $gr = GoodsReceipt::create([
                'gr_number'         => $this->generateGRNumber(),
                'purchase_order_id' => $po->id,
                'organization_id'   => $po->organization_id,
                'received_by'       => $actor->id,
                'confirmed_by'      => $actor->id,
                'confirmed_at'      => now(),
                'received_date'     => now()->toDateString(),
                'status'            => $grStatus,
                'notes'             => $notes,
            ]);

            // --- Create GR items ---
            foreach ($grItems as $grItem) {
                $grItemRecord = $gr->items()->create([
                    'purchase_order_item_id' => $grItem['po_item']->id,
                    'quantity_received'      => $grItem['data']['quantity_received'],
                    'condition'              => $grItem['data']['condition'] ?? 'Good',
                    'notes'                  => $grItem['data']['notes'] ?? null,
                    'batch_no'               => $grItem['data']['batch_no'] ?? null,
                    'expiry_date'            => $grItem['data']['expiry_date'] ?? null,
                ]);

                // --- Add to Inventory (Stock IN) ---
                if ($grStatus === GoodsReceipt::STATUS_COMPLETED) {
                    $this->inventoryService->addStock(
                        organizationId: $po->organization_id,
                        productId: $grItem['po_item']->product_id,
                        batchNo: $grItem['data']['batch_no'] ?? 'NO-BATCH',
                        expiryDate: $grItem['data']['expiry_date'] ?? null,
                        quantity: $grItem['data']['quantity_received'],
                        unitCost: $grItem['po_item']->unit_price,
                        referenceType: 'App\Models\GoodsReceiptItem',
                        referenceId: $grItemRecord->id,
                        createdBy: $actor->id,
                    );
                }
            }

            // --- Advance PO to Completed only when all goods are received ---
            if ($grStatus === GoodsReceipt::STATUS_COMPLETED) {
                $poBefore = $po->status;
                $po->update([
                    'status'       => PurchaseOrder::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);

                $this->auditService->log(
                    action:     'po.completed',
                    entityType: PurchaseOrder::class,
                    entityId:   $po->id,
                    metadata:   [
                        'po_number'     => $po->po_number,
                        'before_status' => $poBefore,
                        'after_status'  => PurchaseOrder::STATUS_COMPLETED,
                        'gr_id'         => $gr->id,
                    ],
                    userId: $actor->id,
                );
            }

            $this->auditService->log(
                action:     'goods_receipt.confirmed',
                entityType: GoodsReceipt::class,
                entityId:   $gr->id,
                metadata:   [
                    'po_id'         => $po->id,
                    'gr_status'     => $grStatus,
                    'item_count'    => count($items),
                ],
                userId: $actor->id,
            );

            // Notify relevant users
            $gr->loadMissing(['purchaseOrder', 'receivedBy']);
            $po->loadMissing('creator');

            if ($po->creator) {
                $po->creator->notify(new \App\Notifications\GoodsReceiptNotification($gr));
            }

            User::role(['Super Admin', 'Healthcare User'])->get()
                ->filter(fn($u) => $u->id !== $po->created_by && (
                    $u->hasRole('Super Admin') || $u->organization_id === $po->organization_id
                ))
                ->each(fn($u) => $u->notify(new \App\Notifications\GoodsReceiptNotification($gr)));

            return $gr;
        });
    }

    // -----------------------------------------------------------------------
    // Generate GR Number
    // -----------------------------------------------------------------------

    private function generateGRNumber(): string
    {
        return 'GR-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
