<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'product_id',
        'batch_no',
        'expiry_date',
        'quantity_on_hand',
        'quantity_reserved',
        'unit_cost',
        'location',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity_on_hand' => 'integer',
        'quantity_reserved' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    protected $appends = ['quantity_available'];

    /**
     * Get available quantity (on_hand - reserved)
     */
    public function getQuantityAvailableAttribute(): int
    {
        return $this->quantity_on_hand - $this->quantity_reserved;
    }

    /**
     * Check if stock is low (less than 20% of initial stock or below 10 units)
     */
    public function isLowStock(): bool
    {
        return $this->quantity_available < 10;
    }

    /**
     * Check if expiring soon (within 60 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        // Check if expiry date is in the future and within 60 days
        return $this->expiry_date->isFuture() && 
               now()->diffInDays($this->expiry_date, false) <= 60;
    }

    /**
     * Check if expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Relationships
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Scopes
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('(quantity_on_hand - quantity_reserved) < 10');
    }

    public function scopeExpiringSoon($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(60));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now());
    }
}
