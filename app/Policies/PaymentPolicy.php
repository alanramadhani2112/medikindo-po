<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_payments');
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        if (!$user->can('view_payments')) {
            return false;
        }

        // Super Admin can view all payments
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Check if payment belongs to user's organization
        if ($payment->organization_id === $user->organization_id) {
            return true;
        }

        // Check if payment is allocated to invoices from user's organization
        $hasOrganizationAllocation = $payment->allocations()
            ->where(function ($query) use ($user) {
                $query->whereHas('supplierInvoice.purchaseOrder', function ($q) use ($user) {
                    $q->where('organization_id', $user->organization_id);
                })->orWhereHas('customerInvoice', function ($q) use ($user) {
                    $q->where('organization_id', $user->organization_id);
                });
            })
            ->exists();

        return $hasOrganizationAllocation;
    }

    /**
     * Determine whether the user can create payments.
     */
    public function create(User $user): bool
    {
        return $user->can('process_payments');
    }

    /**
     * Determine whether the user can update the payment.
     */
    public function update(User $user, Payment $payment): bool
    {
        return $user->can('process_payments') && $this->view($user, $payment);
    }

    /**
     * Determine whether the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasRole('Super Admin') && $this->view($user, $payment);
    }
}