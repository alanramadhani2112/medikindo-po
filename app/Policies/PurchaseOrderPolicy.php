<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super Admin bypasses all authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null; // Continue to specific policy methods
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Healthcare User', 'Approver']);
    }

    public function view(User $user, PurchaseOrder $po): bool
    {
        return $user->organization_id === $po->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Healthcare User'])
            && $user->organization_id !== null;
    }

    public function update(User $user, PurchaseOrder $po): bool
    {
        return $po->isDraft()
            && $user->hasAnyRole(['Super Admin', 'Healthcare User'])
            && (int) $user->organization_id === (int) $po->organization_id;
    }

    /**
     * Determine if user can confirm goods receipt for this PO.
     * This is different from update - PO must be approved, not draft.
     */
    public function confirmReceipt(User $user, PurchaseOrder $po): bool
    {
        return $po->isApproved()
            && $user->hasAnyRole(['Super Admin', 'Healthcare User', 'Admin Pusat'])
            && (int) $user->organization_id === (int) $po->organization_id;
    }

    public function submit(User $user, PurchaseOrder $po): bool
    {
        return $po->isDraft()
            && $user->hasAnyRole(['Super Admin', 'Healthcare User'])
            && $user->organization_id === $po->organization_id;
    }

    public function approve(User $user, PurchaseOrder $po): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Approver'])
            && $po->status === PurchaseOrder::STATUS_SUBMITTED;
    }

    public function sendToSupplier(User $user, PurchaseOrder $po): bool
    {
        return $po->isApproved()
            && $user->hasAnyRole(['Super Admin', 'Healthcare User'])
            && $user->organization_id === $po->organization_id;
    }

    public function delete(User $user, PurchaseOrder $po): bool
    {
        return $po->isDraft() && $user->id === $po->created_by;
    }
}
