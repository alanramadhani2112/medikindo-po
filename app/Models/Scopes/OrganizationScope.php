<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Skip scope for users with approver roles (they can see all organizations)
            if ($user->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
                return;
            }
            
            // Apply organization filter for other users
            $builder->where($model->getTable() . '.organization_id', $user->organization_id);
        }
    }
}
