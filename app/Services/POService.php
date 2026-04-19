<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Notifications\POSubmittedNotification;
use DomainException;
use Illuminate\Support\Facades\DB;

class POService
{
    public function __construct(
        private readonly ValidationService   $validationService,
        private readonly ApprovalService     $approvalService,
        private readonly AuditService        $auditService,
        private readonly CreditControlService $creditControlService,
    ) {}

    // -----------------------------------------------------------------------
    // Create PO (Organization User / Healthcare User)
    // -----------------------------------------------------------------------

    public function createPO(User $user, array $data): PurchaseOrder
    {
        $this->validationService->ensureSupplierIsValid($data['supplier_id']);

        return DB::transaction(function () use ($user, $data) {
            $po = PurchaseOrder::create([
                'po_number'              => $this->generatePONumber(),
                'organization_id'        => $data['organization_id'] ?? $user->organization_id,
                'supplier_id'            => $data['supplier_id'],
                'created_by'             => $user->id,
                'status'                 => PurchaseOrder::STATUS_DRAFT,
                'requested_date'         => $data['requested_date'] ?? null,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes'                  => $data['notes'] ?? null,
            ]);

            $this->auditService->log(
                action:     'po.created',
                entityType: PurchaseOrder::class,
                entityId:   $po->id,
                metadata:   [
                    'po_number'      => $po->po_number,
                    'before_status'  => null,
                    'after_status'   => PurchaseOrder::STATUS_DRAFT,
                ],
            );

            return $po;
        });
    }

    // -----------------------------------------------------------------------
    // Submit PO (Organization User / Healthcare User)
    // Transitions: draft → submitted → [approval initialised]
    // -----------------------------------------------------------------------

    public function submitPO(PurchaseOrder $po, User $actor): PurchaseOrder
    {
        // Gate: only draft POs can be submitted
        if (! $po->isDraft()) {
            throw new DomainException("Only draft POs can be submitted. Current status: {$po->status}.");
        }

        $this->validationService->ensurePOHasItems($po);

        return DB::transaction(function () use ($po, $actor) {
            $before = $po->status;

            // Reserve credit first — throws if insufficient limit
            $this->creditControlService->reserveCredit($po);

            $po->update([
                'status'       => PurchaseOrder::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            // Initialise approval records for Approvers to action
            $this->approvalService->initializeApprovals($po);

            $this->auditService->log(
                action:     'po.submitted',
                entityType: PurchaseOrder::class,
                entityId:   $po->id,
                metadata:   [
                    'po_number'     => $po->po_number,
                    'before_status' => $before,
                    'after_status'  => PurchaseOrder::STATUS_SUBMITTED,
                    'has_narcotics' => $po->has_narcotics,
                    'total_amount'  => $po->total_amount,
                ],
            );

            // Notify Approvers + Super Admin + Creator
            $po->loadMissing(['organization', 'supplier', 'creator']);

            if ($po->creator) {
                $po->creator->notify(new POSubmittedNotification($po));
            }

            User::role(['Approver', 'Super Admin'])->get()
                ->filter(fn($u) => $u->id !== $po->created_by && (
                    $u->hasRole('Super Admin') || $u->organization_id === $po->organization_id
                ))
                ->each(fn(User $u) => $u->notify(new POSubmittedNotification($po)));

            return $po->fresh();
        });
    }

    // -----------------------------------------------------------------------
    // Sync Items (draft only)
    // -----------------------------------------------------------------------

    public function syncItems(PurchaseOrder $po, array $items): PurchaseOrder
    {
        if (! $po->isEditable()) {
            throw new DomainException('Items can only be modified on draft POs.');
        }

        return DB::transaction(function () use ($po, $items) {
            $po->items()->delete();

            foreach ($items as $item) {
                $product = $this->validationService->ensureProductIsValid(
                    $item['product_id'],
                    $po->supplier_id,
                );

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $product->id,
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'] ?? $product->price,
                    'notes'             => $item['notes'] ?? null,
                ]);
            }

            $po->recalculateTotals();
            $po->save();

            $this->auditService->log(
                action:     'po.items_updated',
                entityType: PurchaseOrder::class,
                entityId:   $po->id,
                metadata:   ['item_count' => count($items), 'has_narcotics' => $po->has_narcotics],
            );

            return $po->fresh(['items.product']);
        });
    }

    // -----------------------------------------------------------------------
    // Update PO (draft only)
    // -----------------------------------------------------------------------

    public function update(PurchaseOrder $po, array $data): PurchaseOrder
    {
        if (! $po->isEditable()) {
            throw new DomainException('POs can only be edited when in draft status.');
        }

        if (isset($data['supplier_id'])) {
            $this->validationService->ensureSupplierIsValid($data['supplier_id']);
        }

        return DB::transaction(function () use ($po, $data) {
            $po->update(array_filter([
                'organization_id'        => $data['organization_id'] ?? null,
                'supplier_id'            => $data['supplier_id'] ?? null,
                'requested_date'         => $data['requested_date'] ?? null,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes'                  => $data['notes'] ?? null,
            ], fn($v) => $v !== null));

            $this->auditService->log(
                action:     'po.updated',
                entityType: PurchaseOrder::class,
                entityId:   $po->id,
                metadata:   $data,
            );

            return $po->fresh();
        });
    }

    // -----------------------------------------------------------------------
    // Reopen PO (rejected → draft)
    // -----------------------------------------------------------------------

    public function reopen(PurchaseOrder $po, User $actor): PurchaseOrder
    {
        if (! $po->isRejected()) {
            throw new DomainException("Only rejected POs can be reopened. Current status: {$po->status}.");
        }

        return DB::transaction(function () use ($po, $actor) {
            $before = $po->status;

            // Delete old approval records so fresh approvals can be created on re-submit
            $po->approvals()->delete();

            $po->update([
                'status'       => PurchaseOrder::STATUS_DRAFT,
                'submitted_at' => null,
            ]);

            $this->auditService->log(
                action:     'po.reopened',
                entityType: PurchaseOrder::class,
                entityId:   $po->id,
                metadata:   [
                    'po_number'     => $po->po_number,
                    'before_status' => $before,
                    'after_status'  => PurchaseOrder::STATUS_DRAFT,
                    'actor_id'      => $actor->id,
                ],
            );

            return $po->fresh();
        });
    }

    // -----------------------------------------------------------------------
    // Delete PO (draft only)
    // -----------------------------------------------------------------------

    public function delete(PurchaseOrder $po): void
    {
        if (! $po->isEditable()) {
            throw new DomainException('Only draft POs can be deleted.');
        }

        DB::transaction(function () use ($po) {
            $poId     = $po->id;
            $poNumber = $po->po_number;

            $po->items()->delete();
            $po->delete();

            $this->auditService->log(
                action:     'po.deleted',
                entityType: PurchaseOrder::class,
                entityId:   $poId,
                metadata:   ['po_number' => $poNumber],
            );
        });
    }

    // -----------------------------------------------------------------------
    // Generate PO Number
    // -----------------------------------------------------------------------

    private function generatePONumber(): string
    {
        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $last   = PurchaseOrder::withoutGlobalScopes()
            ->where('po_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('po_number');

        $sequence = $last
            ? (int) substr($last, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
