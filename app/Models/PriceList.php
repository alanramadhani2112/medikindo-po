<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'product_id',
        'selling_price',
        'effective_date',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'selling_price'  => 'decimal:2',
        'effective_date' => 'date',
        'expiry_date'    => 'date',
        'is_active'      => 'boolean',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    /**
     * Scope: active price lists valid for a given date.
     * 
     * Filters: is_active = true, effective_date <= $date, expiry_date IS NULL OR expiry_date >= $date
     */
    public function scopeActiveForDate(Builder $query, Carbon $date): Builder
    {
        return $query->where('is_active', true)
                     ->where('effective_date', '<=', $date->toDateString())
                     ->where(function (Builder $q) use ($date) {
                         $q->whereNull('expiry_date')
                           ->orWhere('expiry_date', '>=', $date->toDateString());
                     });
    }
}
