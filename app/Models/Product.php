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

    // Product Type (Compliance)
    public const PRODUCT_TYPES = [
        'ALKES' => 'Alat Kesehatan',
        'ALKES_DIV' => 'Alat Kesehatan Diagnostik In Vitro',
        'PKRT' => 'Perbekalan Kesehatan Rumah Tangga',
    ];

    // Risk Class (Compliance)
    public const RISK_CLASS_ALKES = [
        'A' => 'Class A - Risiko Rendah',
        'B' => 'Class B - Risiko Sedang-Rendah',
        'C' => 'Class C - Risiko Sedang-Tinggi',
        'D' => 'Class D - Risiko Tinggi',
    ];

    public const RISK_CLASS_PKRT = [
        '1' => 'Class 1 - Risiko Rendah',
        '2' => 'Class 2 - Risiko Sedang',
        '3' => 'Class 3 - Risiko Tinggi',
    ];

    // Usage Method
    public const USAGE_METHODS = [
        'single_use' => 'Single Use (Sekali Pakai)',
        'reusable' => 'Reusable (Dapat Digunakan Ulang)',
        'sterilizable' => 'Sterilizable (Dapat Disterilkan)',
    ];

    // Target User
    public const TARGET_USERS = [
        'healthcare_professional' => 'Tenaga Kesehatan Profesional',
        'consumer' => 'Konsumen/Pasien',
        'both' => 'Keduanya',
    ];

    // Sterilization Method
    public const STERILIZATION_METHODS = [
        'ETO' => 'Ethylene Oxide (ETO)',
        'Steam' => 'Steam/Autoclave',
        'Radiation' => 'Radiation',
        'Other' => 'Lainnya',
        'None' => 'Tidak Steril',
    ];

    protected $fillable = [
        'supplier_id',
        'manufacturer',
        'country_of_origin',
        'name',
        'sku',
        'registration_number',
        'registration_date',
        'registration_expiry',
        'category',
        'product_type',
        'risk_class',
        'intended_use',
        'usage_method',
        'target_user',
        'unit',
        'base_unit_id',
        'price',
        'cost_price',
        'selling_price',
        'discount_percentage',
        'discount_amount',
        'is_narcotic',
        'narcotic_group',
        'requires_sp',
        'requires_prescription',
        'is_sterile',
        'sterilization_method',
        'description',
        'is_active',
        'min_stock_level',
        'max_stock_level',
        'reorder_quantity',
        'storage_temperature',
        'storage_condition',
        'special_handling',
    ];

    protected function casts(): array
    {
        return [
            'price'                  => 'decimal:2',
            'cost_price'             => 'decimal:2',
            'selling_price'          => 'decimal:2',
            'discount_percentage'    => 'decimal:2',
            'discount_amount'        => 'decimal:2',
            'min_stock_level'        => 'decimal:2',
            'max_stock_level'        => 'decimal:2',
            'reorder_quantity'       => 'decimal:2',
            'is_narcotic'            => 'boolean',
            'requires_sp'            => 'boolean',
            'requires_prescription'  => 'boolean',
            'is_sterile'             => 'boolean',
            'is_active'              => 'boolean',
            'registration_date'      => 'date',
            'registration_expiry'    => 'date',
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

    /**
     * RELATIONSHIPS
     */
    
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get base unit (direct relationship)
     */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Get all units for this product (many-to-many)
     */
    public function units(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'product_units')
                    ->withPivot([
                        'conversion_to_base',
                        'is_base_unit',
                        'is_default_purchase',
                        'is_default_sales',
                        'barcode',
                    ])
                    ->withTimestamps();
    }

    /**
     * Get product_units records
     */
    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * UNIT CONVERSION HELPERS
     */

    /**
     * Get default purchase unit
     */
    public function getDefaultPurchaseUnitAttribute(): ?ProductUnit
    {
        return $this->productUnits()->where('is_default_purchase', true)->first();
    }

    /**
     * Get default sales unit
     */
    public function getDefaultSalesUnitAttribute(): ?ProductUnit
    {
        return $this->productUnits()->where('is_default_sales', true)->first();
    }

    /**
     * Convert quantity from one unit to another
     */
    public function convertUnit(float $quantity, int $fromUnitId, int $toUnitId): float
    {
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        $fromUnit = $this->productUnits()->where('unit_id', $fromUnitId)->first();
        $toUnit = $this->productUnits()->where('unit_id', $toUnitId)->first();

        if (!$fromUnit || !$toUnit) {
            throw new \Exception("Unit not found for this product");
        }

        // Convert to base unit first, then to target unit
        $baseQuantity = $quantity * $fromUnit->conversion_to_base;
        return $baseQuantity / $toUnit->conversion_to_base;
    }

    /**
     * Convert quantity to base unit
     */
    public function toBaseUnit(float $quantity, int $unitId): float
    {
        if ($this->base_unit_id === $unitId) {
            return $quantity;
        }

        $unit = $this->productUnits()->where('unit_id', $unitId)->first();
        
        if (!$unit) {
            throw new \Exception("Unit not found for this product");
        }

        return $quantity * $unit->conversion_to_base;
    }

    /**
     * COMPLIANCE HELPERS
     */

    /**
     * Check if product requires special approval (high risk)
     */
    public function requiresSpecialApproval(): bool
    {
        // Risk class C, D (ALKES) atau 3 (PKRT) = high risk
        return in_array($this->risk_class, ['C', 'D', '3']);
    }

    /**
     * Check if registration is expired
     */
    public function isRegistrationExpired(): bool
    {
        if (!$this->registration_expiry) {
            return false;
        }
        return $this->registration_expiry->isPast();
    }

    /**
     * Check if registration is expiring soon (within 90 days)
     */
    public function isRegistrationExpiringSoon(int $days = 90): bool
    {
        if (!$this->registration_expiry) {
            return false;
        }
        return $this->registration_expiry->isFuture() 
               && $this->registration_expiry->diffInDays(now()) <= $days;
    }
}
