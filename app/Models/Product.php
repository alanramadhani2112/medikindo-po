<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    
    public const CATEGORIES = [
        'Obat Umum',
        'Obat Keras',
        'Narkotika',
        'Psikotropika',
        'Alat Kesehatan',
        'BMHP'
    ];

    public const UNITS = [
        'Box',
        'Botol',
        'Tablet',
        'Strip',
        'Ampul',
        'Vial',
        'PCS'
    ];

    protected $fillable = [
        'supplier_id',
        'name',
        'sku',
        'category',
        'unit',
        'price',
        'cost_price',
        'selling_price',
        'discount_percentage',
        'discount_amount',
        'is_narcotic',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'               => 'decimal:2',
            'cost_price'          => 'decimal:2',
            'selling_price'       => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount'     => 'decimal:2',
            'is_narcotic'         => 'boolean',
            'is_active'           => 'boolean',
        ];
    }

    /**
     * PROFIT CALCULATIONS
     */

    /**
     * Get Gross Profit (Laba Kotor)
     * Formula: Selling Price - Cost Price
     */
    public function getGrossProfitAttribute(): float
    {
        return $this->selling_price - $this->cost_price;
    }

    /**
     * Get Gross Profit Margin (%)
     * Formula: (Gross Profit / Selling Price) × 100
     */
    public function getGrossProfitMarginAttribute(): float
    {
        if ($this->selling_price == 0) {
            return 0;
        }
        return ($this->gross_profit / $this->selling_price) * 100;
    }

    /**
     * Get Final Price (Harga Setelah Diskon)
     * Formula: Selling Price - Discount Amount
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->selling_price - $this->discount_amount;
    }

    /**
     * Get Net Profit (Laba Bersih)
     * Formula: Final Price - Cost Price
     */
    public function getNetProfitAttribute(): float
    {
        return $this->final_price - $this->cost_price;
    }

    /**
     * Get Net Profit Margin (%)
     * Formula: (Net Profit / Final Price) × 100
     */
    public function getNetProfitMarginAttribute(): float
    {
        if ($this->final_price == 0) {
            return 0;
        }
        return ($this->net_profit / $this->final_price) * 100;
    }

    /**
     * Calculate discount amount from percentage
     */
    public function calculateDiscountAmount(): float
    {
        return ($this->selling_price * $this->discount_percentage) / 100;
    }

    /**
     * Check if product is profitable
     */
    public function isProfitable(): bool
    {
        return $this->net_profit > 0;
    }

    /**
     * Get profit status color for UI
     */
    public function getProfitStatusColorAttribute(): string
    {
        if ($this->net_profit_margin >= 20) {
            return 'success'; // High profit
        } elseif ($this->net_profit_margin >= 10) {
            return 'primary'; // Good profit
        } elseif ($this->net_profit_margin >= 5) {
            return 'warning'; // Low profit
        } else {
            return 'danger'; // Very low or negative profit
        }
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
