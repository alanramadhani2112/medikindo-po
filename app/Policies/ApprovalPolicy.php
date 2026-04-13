<?php

namespace App\Policies;

use App\Models\Approval;
use App\Models\User;

class ApprovalPolicy
{
    public function process(User $user, Approval $approval): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Approver']);
    }
}
