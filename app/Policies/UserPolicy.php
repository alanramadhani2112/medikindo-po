<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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

    public function viewAny(User $actor): bool
    {
        return $actor->hasAnyPermission(['manage_user', 'full_access']);
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->hasPermissionTo('full_access')) {
            return true;
        }
        // Clinic Admin can only view users in their own clinic
        return $actor->hasPermissionTo('manage_user') && $actor->organization_id === $target->organization_id;
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->hasPermissionTo('full_access')) {
            return true;
        }
        // Clinic Admin can only toggle is_active, not assign roles
        return $actor->hasPermissionTo('manage_user') && $actor->organization_id === $target->organization_id;
    }

    public function delete(User $actor, User $target): bool
    {
        // Only Super Admin can deactivate users
        return $actor->hasPermissionTo('full_access') && $actor->id !== $target->id;
    }
}
