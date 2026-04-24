<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'inventory_item_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    const TYPE_IN = 'in';
    const TYPE_OUT = 'out';
    const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Relationships
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeStockIn($query)
    {
        return $query->where('movement_type', self::TYPE_IN);
    }

    public function scopeStockOut($query)
    {
        return $query->where('movement_type', self::TYPE_OUT);
    }

    public function scopeAdjustments($query)
    {
        return $query->where('movement_type', self::TYPE_ADJUSTMENT);
    }
}
