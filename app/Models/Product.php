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
        'expiry_date',
        'batch_no',
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
            'expiry_date'         => 'date',
        ];
    }

    /**
     * EXPIRY DATE HELPERS
     */

    /**
     * Check if product is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Check if product is expiring soon (within 60 days)
     */
    public function isExpiringSoon(int $days = 60): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isFuture() && $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return $this->expiry_date->diffInDays(now(), false);
    }

    /**
     * Get expiry status for UI
     */
    public function getExpiryStatusAttribute(): string
    {
        if (!$this->expiry_date) {
            return 'none';
        }
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($this->isExpiringSoon(30)) {
            return 'critical'; // < 30 days
        }
        if ($this->isExpiringSoon(60)) {
            return 'warning'; // < 60 days
        }
        return 'ok';
    }

    /**
     * Get expiry status color for UI
     */
    public function getExpiryStatusColorAttribute(): string
    {
        return match($this->expiry_status) {
            'expired' => 'danger',
            'critical' => 'danger',
            'warning' => 'warning',
            'ok' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Scope: Get expiring products
     */
    public function scopeExpiringSoon($query, int $days = 60)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>', now())
            ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Scope: Get expired products
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now());
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
