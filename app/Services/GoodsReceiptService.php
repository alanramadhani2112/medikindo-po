<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDelivery;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\StateMachines\StateMachineRegistry;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GoodsReceiptService
{
    public function __construct(
        private readonly AuditService          $auditService,
        private readonly InventoryService      $inventoryService,
        private readonly DocumentNumberService $documentNumberService,
    ) {}

    // -----------------------------------------------------------------------
    // Add Delivery to GR (or create GR if first delivery)
    //
    // Design: 1 PO → 1 GR → N Deliveries (pengiriman bertahap)
    //
    // - First delivery: creates the GR record, then adds delivery
    // - Subsequent deliveries: finds existing GR, adds new delivery
    // - Inventory updated immediately on each delivery
    // - GR status: partial until all PO items fully received, then completed
    // - PO status: partially_received → completed
    // -----------------------------------------------------------------------

    public function addDelivery(
        PurchaseOrder $po,
        User $actor,
        array $items,
        string $deliveryOrderNumber,
        UploadedFile $photo,
        ?string $notes = null,
    ): GoodsReceipt {
        // Gate: PO must be in delivery state
        if (! $po->isApproved() && ! $po->isPartiallyReceived()) {
            throw new DomainException(
                "Penerimaan barang hanya bisa dilakukan ketika PO berstatus 'approved' atau 'partially_received'. Status saat ini: [{$po->status}]."
            );
        }

        // Gate: actor must be Organization role
        if (! $actor->hasAnyRole(['Healthcare User', 'Procurement Staff', 'Super Admin'])) {
            throw new DomainException('Hanya staf organisasi atau Super Admin yang dapat mengkonfirmasi penerimaan barang.');
        }

        return DB::transaction(function () use ($po, $actor, $items, $deliveryOrderNumber, $photo, $notes) {

            // --- Get or create the single GR for this PO with proper locking ---
            // Lock the PO row to prevent concurrent GR creation
            $po = PurchaseOrder::lockForUpdate()->findOrFail($po->id);
            
            $gr = GoodsReceipt::where('purchase_order_id', $po->id)->first();
            
            if (!$gr) {
                $gr = GoodsReceipt::create([
                    'purchase_order_id' => $po->id,
                    'gr_number'       => $this->documentNumberService->generateGRNumber(
                        $po->organization_id,
                        $po->po_number,
                    ),
                    'organization_id' => $po->organization_id,
                    'received_by'     => $actor->id,
                    'confirmed_by'    => $actor->id,
                    'confirmed_at'    => now(),
                    'received_date'   => now()->toDateString(),
                    'status'          => GoodsReceipt::STATUS_PARTIAL,
                    'notes'           => null,
                ]);
            }

            // --- Determine delivery sequence with lock to prevent race condition ---
            $sequence = DB::table('goods_receipt_deliveries')
                ->where('goods_receipt_id', $gr->id)
                ->lockForUpdate()
                ->count() + 1;

            // --- Validate items: qty must not exceed remaining + expiry date validation ---
            $grItems = [];
            $allFulfilled = true;

            foreach ($items as $item) {
                $poItem = $po->items()->findOrFail($item['purchase_order_item_id']);

                // Validate expiry date in business logic (defense in depth)
                if (isset($item['expiry_date'])) {
                    $expiryDate = \Carbon\Carbon::parse($item['expiry_date']);
                    if ($expiryDate->isPast() || $expiryDate->isToday()) {
                        throw new DomainException(
                            "Tidak dapat menerima barang kadaluarsa: produk [{$poItem->product?->name}] dengan tanggal expiry {$expiryDate->format('Y-m-d')} sudah atau akan kadaluarsa."
                        );
                    }
                }

                // Total already received across ALL previous deliveries for this PO item
                $alreadyReceived = GoodsReceiptDelivery::where('goods_receipt_id', $gr->id)
                    ->join('goods_receipt_delivery_items as grdi', 'goods_receipt_deliveries.id', '=', 'grdi.goods_receipt_delivery_id')
                    ->where('grdi.purchase_order_item_id', $poItem->id)
                    ->sum('grdi.quantity_received');

                $remaining = $poItem->quantity - $alreadyReceived;

                if ($remaining <= 0) {
                    throw new DomainException(
                        "Produk [{$poItem->product?->name}] sudah diterima penuh. Tidak perlu pengiriman tambahan."
                    );
                }

                if ($item['quantity_received'] > $remaining) {
                    throw new DomainException(
                        "Jumlah diterima ({$item['quantity_received']}) melebihi sisa yang belum diterima ({$remaining}) untuk produk [{$poItem->product?->name}]."
                    );
                }

                // After this delivery, is this item still short?
                $afterThis = $alreadyReceived + $item['quantity_received'];
                if ($afterThis < $poItem->quantity) {
                    $allFulfilled = false;
                }

                $grItems[] = [
                    'po_item' => $poItem,
                    'data'    => $item,
                ];
            }

            // Also check items NOT in this delivery — if any PO item has no delivery at all, not fulfilled
            foreach ($po->items as $poItem) {
                $inThisDelivery = collect($grItems)->contains(fn($g) => $g['po_item']->id === $poItem->id);
                if (! $inThisDelivery) {
                    $alreadyReceived = GoodsReceiptDelivery::where('goods_receipt_id', $gr->id)
                        ->join('goods_receipt_delivery_items as grdi', 'goods_receipt_deliveries.id', '=', 'grdi.goods_receipt_delivery_id')
                        ->where('grdi.purchase_order_item_id', $poItem->id)
                        ->sum('grdi.quantity_received');

                    if ($alreadyReceived < $poItem->quantity) {
                        $allFulfilled = false;
                    }
                }
            }

            // --- Store photo with safe path ---
            $photoPath = $photo->store("gr-photos/" . now()->format('Y/m'), 'public');

            // --- Create delivery record ---
            $delivery = GoodsReceiptDelivery::create([
                'goods_receipt_id'    => $gr->id,
                'delivery_number'     => $deliveryOrderNumber,
                'delivery_sequence'   => $sequence,
                'received_date'       => now()->toDateString(),
                'received_by'         => $actor->id,
                'photo_path'          => $photoPath,
                'photo_original_name' => $photo->getClientOriginalName(),
                'notes'               => $notes,
            ]);

            // --- Create delivery items + update inventory + sync GR items ---
            foreach ($grItems as $grItem) {
                // Create delivery item
                $delivery->items()->create([
                    'purchase_order_item_id' => $grItem['po_item']->id,
                    'quantity_received'      => $grItem['data']['quantity_received'],
                    'batch_no'               => $grItem['data']['batch_no'],
                    'expiry_date'            => $grItem['data']['expiry_date'],
                    'condition'              => $grItem['data']['condition'] ?? 'Good',
                    'notes'                  => $grItem['data']['notes'] ?? null,
                ]);

                // Sync aggregated quantity into goods_receipt_items (for invoicing compatibility)
                $this->syncGRItem($gr, $grItem['po_item'], $grItem['data']);

                // Add to inventory immediately
                $grItemRecord = $gr->items()
                    ->where('purchase_order_item_id', $grItem['po_item']->id)
                    ->first();

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

            // --- Update GR status ---
            $grStatus = $allFulfilled ? GoodsReceipt::STATUS_COMPLETED : GoodsReceipt::STATUS_PARTIAL;
            $gr->update(['status' => $grStatus]);

            // --- Update PO status via state machine ---
            $poBefore = $po->status;
            if ($grStatus === GoodsReceipt::STATUS_COMPLETED) {
                StateMachineRegistry::for(PurchaseOrder::class)->validate(
                    from:   $po->status,
                    to:     PurchaseOrder::STATUS_COMPLETED,
                    actor:  $actor,
                    entity: $po,
                );
                $po->update(['status' => PurchaseOrder::STATUS_COMPLETED, 'completed_at' => now()]);
                $this->auditService->log(
                    action:      'po.completed',
                    entityType:  PurchaseOrder::class,
                    entityId:    $po->id,
                    metadata:    ['po_number' => $po->po_number, 'gr_id' => $gr->id],
                    beforeValue: ['status' => $poBefore],
                    afterValue:  ['status' => PurchaseOrder::STATUS_COMPLETED],
                    userId:      $actor->id,
                );
            } else {
                StateMachineRegistry::for(PurchaseOrder::class)->validate(
                    from:   $po->status,
                    to:     PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                    actor:  $actor,
                    entity: $po,
                );
                $po->update(['status' => PurchaseOrder::STATUS_PARTIALLY_RECEIVED]);
                $this->auditService->log(
                    action:      'po.partially_received',
                    entityType:  PurchaseOrder::class,
                    entityId:    $po->id,
                    metadata:    ['po_number' => $po->po_number, 'gr_id' => $gr->id],
                    beforeValue: ['status' => $poBefore],
                    afterValue:  ['status' => PurchaseOrder::STATUS_PARTIALLY_RECEIVED],
                    userId:      $actor->id,
                );
            }

            // --- Audit log ---
            $this->auditService->log(
                action: 'goods_receipt.delivery_added', entityType: GoodsReceipt::class, entityId: $gr->id,
                metadata: ['po_id' => $po->id, 'delivery_sequence' => $sequence, 'delivery_number' => $deliveryOrderNumber, 'gr_status' => $grStatus, 'item_count' => count($items)],
                userId: $actor->id,
            );

            // --- Notify ---
            $gr->loadMissing(['purchaseOrder', 'receivedBy']);
            $po->loadMissing('creator');

            if ($grStatus === GoodsReceipt::STATUS_PARTIAL) {
                // Hitung total received dan remaining untuk semua item PO
                $totalOrdered  = $po->items->sum('quantity');
                $totalReceived = $gr->items()->sum('quantity_received');
                $remaining     = max(0, $totalOrdered - $totalReceived);

                // Notifikasi khusus partial ke Finance & Super Admin Medikindo (max 50 users)
                try {
                    $financeUsers = \App\Models\User::role(['Finance', 'Super Admin', 'Admin Pusat'])
                        ->where('is_active', true)
                        ->limit(50)
                        ->get();
                } catch (\Exception $e) {
                    // Fallback if roles don't exist
                    $financeUsers = \App\Models\User::role(['Super Admin', 'Admin Pusat'])
                        ->where('is_active', true)
                        ->limit(50)
                        ->get();
                }

                foreach ($financeUsers as $user) {
                    $user->notify(new \App\Notifications\PartialDeliveryNotification(
                        gr: $gr,
                        po: $po,
                        deliverySequence: $sequence,
                        totalReceived: $totalReceived,
                        totalOrdered: $totalOrdered,
                        remaining: $remaining,
                    ));
                }

                // Notifikasi standar ke creator PO dan org users
                if ($po->creator) {
                    $po->creator->notify(new \App\Notifications\GoodsReceiptNotification($gr));
                }
            } else {
                // GR completed — notify creator + org Healthcare + Finance + Super Admin
                if ($po->creator) {
                    $po->creator->notify(new \App\Notifications\GoodsReceiptNotification($gr));
                }

                try {
                    $notifyUsers = \App\Models\User::role(['Super Admin', 'Healthcare User', 'Finance', 'Admin Pusat'])
                        ->where('is_active', true)
                        ->limit(50)
                        ->get();
                } catch (\Exception $e) {
                    // Fallback if roles don't exist
                    $notifyUsers = \App\Models\User::role(['Super Admin', 'Healthcare User', 'Admin Pusat'])
                        ->where('is_active', true)
                        ->limit(50)
                        ->get();
                }

                $notifyUsers
                    ->filter(fn($u) =>
                        $u->id !== $po->created_by && (
                            $u->hasRole(['Super Admin', 'Finance', 'Admin Pusat']) ||
                            $u->organization_id === $po->organization_id
                        )
                    )
                    ->each(fn($u) => $u->notify(new \App\Notifications\GoodsReceiptNotification($gr)));
            }

            return $gr;
        });
    }

    // -----------------------------------------------------------------------
    // Get POs available for receipt with remaining quantities
    // -----------------------------------------------------------------------

    public function getAvailablePOsForReceipt(User $user): \Illuminate\Support\Collection
    {
        $pos = PurchaseOrder::with(['items.product', 'organization', 'supplier', 'goodsReceipts.items'])
            ->whereIn('status', [
                PurchaseOrder::STATUS_APPROVED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ])
            ->when(! $user->hasRole('Super Admin'), function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->latest()
            ->get();

        // Calculate already_received and remaining for each item
        $pos->each(function ($po) {
            $po->items->each(function ($item) use ($po) {
                $alreadyReceived = $po->goodsReceipts->flatMap(function ($gr) {
                    return $gr->items;
                })->where('purchase_order_item_id', $item->id)->sum('quantity_received');
                
                $item->already_received = $alreadyReceived;
                $item->remaining = max(0, $item->quantity - $alreadyReceived);
            });
            
            // Filter out items that are already fully received
            $po->setRelation('items', $po->items->filter(fn($item) => $item->remaining > 0)->values());
        });
        
        // Filter out POs that have no remaining items to receive
        return $pos->filter(fn($po) => $po->items->isNotEmpty())->values();
    }

    // -----------------------------------------------------------------------
    // Sync aggregated GR item (for invoicing compatibility)
    // goods_receipt_items holds the TOTAL received per PO item across all deliveries
    // -----------------------------------------------------------------------

    private function syncGRItem(GoodsReceipt $gr, $poItem, array $data): void
    {
        $existing = $gr->items()->where('purchase_order_item_id', $poItem->id)->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity_received', $data['quantity_received']);
            // Update batch/expiry to latest delivery's values
            $existing->update([
                'batch_no'    => $data['batch_no'],
                'expiry_date' => $data['expiry_date'],
            ]);
        } else {
            $gr->items()->create([
                'purchase_order_item_id' => $poItem->id,
                'product_id'             => $poItem->product_id, // denormalized for direct access
                'quantity_received'      => $data['quantity_received'],
                'batch_no'               => $data['batch_no'],
                'expiry_date'            => $data['expiry_date'],
                'condition'              => $data['condition'] ?? 'Good',
                'notes'                  => $data['notes'] ?? null,
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // Legacy method alias — kept for backward compatibility
    // -----------------------------------------------------------------------

    public function confirmReceipt(
        PurchaseOrder $po,
        User $actor,
        array $items,
        ?string $notes = null,
        ?string $deliveryOrderNumber = null,
        ?UploadedFile $photo = null,
    ): GoodsReceipt {
        if (! $photo) {
            throw new DomainException('Foto bukti penerimaan wajib diupload.');
        }
        return $this->addDelivery($po, $actor, $items, $deliveryOrderNumber ?? 'DO-MANUAL', $photo, $notes);
    }

    // -----------------------------------------------------------------------
    // Generate GR Number — delegated to DocumentNumberService
    // -----------------------------------------------------------------------
}
