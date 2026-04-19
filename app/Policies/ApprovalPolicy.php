<?php

namespace App\Policies;

use App\Models\Approval;
use App\Models\User;

class ApprovalPolicy
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

    public function process(User $user, Approval $approval): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Approver']);
    }
}
