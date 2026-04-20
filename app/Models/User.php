<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'is_active',
        'is_pharmacist',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'is_pharmacist'     => 'boolean',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Do NOT hardcode 'sanctum' here.
     * Spatie will auto-detect 'web' guard from the default auth config.
     * Removing this override ensures @can, $user->can(), and route middleware
     * all use the correct guard consistently.
     */
    // public function getDefaultGuardName(): string { ... } // REMOVED

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }
}
