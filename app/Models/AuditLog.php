<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToOrganization;

class AuditLog extends Model
{
    use BelongsToOrganization;

    public const CREATED_AT = 'occurred_at';
    public const UPDATED_AT = null; // immutable — only has occurred_at

    protected $fillable = [
        'user_id',
        'organization_id',
        'action',
        'module',
        'entity_type',
        'entity_id',
        'before_value',
        'after_value',
        'metadata',
        'correlation_id',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'     => 'array',
            'before_value' => 'array',
            'after_value'  => 'array',
            'occurred_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
