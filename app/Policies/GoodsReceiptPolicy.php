<?php

namespace App\Policies;

use App\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
{
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
        // Super Admin can view all
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Organization users can only view their own organization's receipts
        return (int) $goodsReceipt->purchaseOrder->organization_id === (int) $user->organization_id;
    }

    /**
     * Determine whether the user can create goods receipts.
     * Healthcare users need 'confirm_receipt' permission to create GR.
     */
    public function create(User $user): bool
    {
        // Check if user has the specific permission to confirm receipt
        if ($user->can('confirm_receipt')) {
            return true;
        }

        // Super Admin can always create
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Fallback: check view permission (for backward compatibility)
        return $user->can('view_goods_receipt');
    }

    /**
     * Determine whether the user can update the goods receipt.
     */
    public function update(User $user, GoodsReceipt $goodsReceipt): bool
    {
        // Super Admin can update all
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Organization users can only update their own organization's receipts
        return (int) $goodsReceipt->purchaseOrder->organization_id === (int) $user->organization_id;
    }

    /**
     * Determine whether the user can delete the goods receipt.
     */
    public function delete(User $user, GoodsReceipt $goodsReceipt): bool
    {
        // Super Admin can delete all
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Organization users can only delete their own organization's receipts
        return (int) $goodsReceipt->purchaseOrder->organization_id === (int) $user->organization_id;
    }
}