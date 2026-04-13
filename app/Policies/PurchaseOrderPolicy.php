<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Healthcare User', 'Approver']);
    }

    public function view(User $user, PurchaseOrder $po): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->organization_id === $po->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Healthcare User'])
            && ($user->isSuperAdmin() || $user->organization_id !== null);
    }

    public function update(User $user, PurchaseOrder $po): bool
    {
        return $po->isDraft()
            && $user->hasAnyRole(['Super Admin', 'Healthcare User'])
            && ($user->isSuperAdmin() || $user->organization_id === $po->organization_id);
    }

    public function submit(User $user, PurchaseOrder $po): bool
    {
        return $po->isDraft()
            && $user->hasAnyRole(['Super Admin', 'Healthcare User'])
            && ($user->isSuperAdmin() || $user->organization_id === $po->organization_id);
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
            && ($user->isSuperAdmin() || $user->organization_id === $po->organization_id);
    }

    public function delete(User $user, PurchaseOrder $po): bool
    {
        return $po->isDraft()
            && ($user->isSuperAdmin() || $user->id === $po->created_by);
    }
}
