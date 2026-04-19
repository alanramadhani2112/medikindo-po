<?php

namespace App\Policies;

use App\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
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

        return null;
    }

    /**
     * Determine whether the user can view any goods receipts.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_goods_receipt');
    }

    /**
     * Determine whether the user can view the goods receipt.
     */
    public function view(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return (int) $goodsReceipt->purchaseOrder->organization_id === (int) $user->organization_id;
    }

    /**
     * Determine whether the user can create goods receipts.
     * Healthcare users need 'confirm_receipt' permission to create GR.
     */
    public function create(User $user): bool
    {
        return $user->can('confirm_receipt') || $user->can('view_goods_receipt');
    }

    /**
     * Determine whether the user can update the goods receipt.
     */
    public function update(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return (int) $goodsReceipt->purchaseOrder->organization_id === (int) $user->organization_id;
    }

    /**
     * Determine whether the user can delete the goods receipt.
     */
    public function delete(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return (int) $goodsReceipt->purchaseOrder->organization_id === (int) $user->organization_id;
    }
}