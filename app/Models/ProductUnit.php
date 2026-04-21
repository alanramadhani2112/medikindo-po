<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_id',
        'conversion_to_base',
        'is_base_unit',
        'is_default_purchase',
        'is_default_sales',
        'barcode',
    ];

    protected function casts(): array
    {
        return [
            'conversion_to_base' => 'decimal:4',
            'is_base_unit' => 'boolean',
            'is_default_purchase' => 'boolean',
            'is_default_sales' => 'boolean',
        ];
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope: Get base unit only
     */
    public function scopeBaseUnit($query)
    {
        return $query->where('is_base_unit', true);
    }

    /**
     * Scope: Get default purchase unit
     */
    public function scopeDefaultPurchase($query)
    {
        return $query->where('is_default_purchase', true);
    }

    /**
     * Scope: Get default sales unit
     */
    public function scopeDefaultSales($query)
    {
        return $query->where('is_default_sales', true);
    }

    /**
     * Convert quantity to base unit
     */
    public function convertToBase(float $quantity): float
    {
        return $quantity * $this->conversion_to_base;
    }

    /**
     * Convert quantity from base unit to this unit
     */
    public function convertFromBase(float $baseQuantity): float
    {
        if ($this->conversion_to_base == 0) {
            return 0;
        }
        return $baseQuantity / $this->conversion_to_base;
    }
}
