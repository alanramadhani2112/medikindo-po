<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'type',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all products that use this unit
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_units')
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
     * Scope: Get only active units
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get units by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted name with symbol
     */
    public function getFormattedNameAttribute(): string
    {
        return $this->symbol ? "{$this->name} ({$this->symbol})" : $this->name;
    }
}
