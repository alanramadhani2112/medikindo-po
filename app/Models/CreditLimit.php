<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToOrganization;

class CreditLimit extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'max_limit',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'max_limit'  => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
