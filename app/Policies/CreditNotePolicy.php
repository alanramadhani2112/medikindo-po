<?php

namespace App\Policies;

use App\Models\CreditNote;
use App\Models\User;

class CreditNotePolicy
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
        return (int) $creditNote->organization_id === (int) $user->organization_id;
    }

    /**
     * Determine whether the user can create credit notes.
     */
    public function create(User $user): bool
    {
        return $user->can('create_invoices');
    }

    /**
     * Determine whether the user can update the credit note.
     */
    public function update(User $user, CreditNote $creditNote): bool
    {
        return $user->can('create_invoices')
            && (int) $creditNote->organization_id === (int) $user->organization_id;
    }

    /**
     * Determine whether the user can delete the credit note.
     */
    public function delete(User $user, CreditNote $creditNote): bool
    {
        return $creditNote->isDraft();
    }
}