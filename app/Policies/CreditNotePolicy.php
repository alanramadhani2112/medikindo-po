<?php

namespace App\Policies;

use App\Models\CreditNote;
use App\Models\User;

class CreditNotePolicy
{
    /**
     * Determine whether the user can view any credit notes.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_invoices') || $user->can('create_invoices');
    }

    /**
     * Determine whether the user can view the credit note.
     */
    public function view(User $user, CreditNote $creditNote): bool
    {
        // Super Admin can view all
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Organization users can only view their own organization's credit notes
        return (int) $creditNote->organization_id === (int) $user->organization_id;
    }

    /**
     * Determine whether the user can create credit notes.
     */
    public function create(User $user): bool
    {
        return $user->can('create_invoices') || $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can update the credit note.
     */
    public function update(User $user, CreditNote $creditNote): bool
    {
        // Super Admin can update all
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Must have permission and belong to same organization
        if (!$user->can('create_invoices')) {
            return false;
        }

        return (int) $creditNote->organization_id === (int) $user->organization_id;
    }

    /**
     * Determine whether the user can delete the credit note.
     */
    public function delete(User $user, CreditNote $creditNote): bool
    {
        // Only Super Admin can delete credit notes
        if (!$user->hasRole('Super Admin')) {
            return false;
        }

        // Can only delete draft credit notes
        return $creditNote->isDraft();
    }
}