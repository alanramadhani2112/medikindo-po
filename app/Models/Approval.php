<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const LEVEL_STANDARD  = 1;
    public const LEVEL_NARCOTICS = 2;

    protected $fillable = [
        'purchase_order_id',
        'approver_id',
        'level',
        'status',
        'notes',
        'actioned_at',
    ];

    protected function casts(): array
    {
        return [
            'level'       => 'integer',
            'actioned_at' => 'datetime',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
