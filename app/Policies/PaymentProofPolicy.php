<?php

namespace App\Policies;

use App\Models\PaymentProof;
use App\Models\User;
use App\Models\CustomerInvoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentProofPolicy
{
    use HandlesAuthorization;

    /**
     * Super Admin God Mode — bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PaymentProof $paymentProof): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin Pusat', 'Finance', 'Approver'])) {
            return true;
        }

        return $paymentProof->submitted_by === $user->id || 
               $paymentProof->customerInvoice->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can submit payment proof for a specific invoice.
     */
    public function submit(User $user, CustomerInvoice $invoice): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin Pusat'])) {
            return true;
        }

        return $invoice->organization_id === $user->organization_id;
    }

    /**
     * Determine whether the user can verify the model.
     */
    public function verify(User $user, PaymentProof $paymentProof): bool
    {
        return $user->hasPermissionTo('verify_payment_proof') || 
               $user->hasRole(['Finance', 'Super Admin']);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, PaymentProof $paymentProof): bool
    {
        return $user->hasPermissionTo('approve_payment') || 
               $user->hasRole(['Finance', 'Super Admin']);
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, PaymentProof $paymentProof): bool
    {
        return $user->hasPermissionTo('approve_payment') || 
               $user->hasRole(['Finance', 'Super Admin']);
    }

    /**
     * Determine whether the user can upload documents to the model.
     */
    public function uploadDocument(User $user, PaymentProof $paymentProof): bool
    {
        return $paymentProof->submitted_by === $user->id ||
               $user->hasRole(['Super Admin', 'Finance']);
    }

    /**
     * Determine whether the user can recall (withdraw) the payment proof.
     * Only allowed if SUBMITTED and by the submitter OR Super Admin.
     */
    public function recall(User $user, PaymentProof $paymentProof): bool
    {
        if (!$paymentProof->canBeRecalled()) {
            return false;
        }

        return $paymentProof->submitted_by === $user->id
            || $user->isSuperAdmin();
    }

    /**
     * Correct an already-approved payment proof (Super Admin only).
     */
    public function correct(User $user, PaymentProof $paymentProof): bool
    {
        return $user->isSuperAdmin() && $paymentProof->canBeCorrected();
    }
}
