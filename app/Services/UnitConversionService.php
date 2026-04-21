<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;

class UnitConversionService
{
    /**
     * Convert quantity from one unit to another for a specific product
     * 
     * @param int $productId
     * @param float $quantity
     * @param int $fromUnitId
     * @param int $toUnitId
     * @return float
     * @throws \Exception
     */
    public function convert(int $productId, float $quantity, int $fromUnitId, int $toUnitId): float
    {
        // If same unit, no conversion needed
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        // Get conversion ratios
        $fromUnit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $fromUnitId)
            ->first();

        $toUnit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $toUnitId)
            ->first();

        if (!$fromUnit) {
            throw new \Exception("Source unit (ID: {$fromUnitId}) not found for product ID: {$productId}");
        }

        if (!$toUnit) {
            throw new \Exception("Target unit (ID: {$toUnitId}) not found for product ID: {$productId}");
        }

        // Convert: quantity * fromRatio / toRatio
        // Example: 2 Box (50 pcs) to Pcs = 2 * 50 / 1 = 100 Pcs
        $baseQuantity = $quantity * $fromUnit->conversion_to_base;
        return $baseQuantity / $toUnit->conversion_to_base;
    }

    /**
     * Convert quantity to base unit
     * 
     * @param int $productId
     * @param float $quantity
     * @param int $unitId
     * @return float
     * @throws \Exception
     */
    public function toBaseUnit(int $productId, float $quantity, int $unitId): float
    {
        $product = Product::findOrFail($productId);

        // If already in base unit, return as is
        if ($product->base_unit_id === $unitId) {
            return $quantity;
        }

        $unit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->first();

        if (!$unit) {
            throw new \Exception("Unit (ID: {$unitId}) not found for product ID: {$productId}");
        }

        return $quantity * $unit->conversion_to_base;
    }

    /**
     * Convert quantity from base unit to target unit
     * 
     * @param int $productId
     * @param float $baseQuantity
     * @param int $targetUnitId
     * @return float
     * @throws \Exception
     */
    public function fromBaseUnit(int $productId, float $baseQuantity, int $targetUnitId): float
    {
        $product = Product::findOrFail($productId);

        // If target is base unit, return as is
        if ($product->base_unit_id === $targetUnitId) {
            return $baseQuantity;
        }

        $unit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $targetUnitId)
            ->first();

        if (!$unit) {
            throw new \Exception("Unit (ID: {$targetUnitId}) not found for product ID: {$productId}");
        }

        if ($unit->conversion_to_base == 0) {
            throw new \Exception("Invalid conversion ratio (zero) for unit ID: {$targetUnitId}");
        }

        return $baseQuantity / $unit->conversion_to_base;
    }

    /**
     * Get all available units for a product with their conversion info
     * 
     * @param int $productId
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableUnits(int $productId)
    {
        return ProductUnit::where('product_id', $productId)
            ->with('unit')
            ->get()
            ->map(function ($productUnit) {
                return [
                    'unit_id' => $productUnit->unit_id,
                    'unit_name' => $productUnit->unit->name,
                    'unit_symbol' => $productUnit->unit->symbol,
                    'conversion_to_base' => $productUnit->conversion_to_base,
                    'is_base_unit' => $productUnit->is_base_unit,
                    'is_default_purchase' => $productUnit->is_default_purchase,
                    'is_default_sales' => $productUnit->is_default_sales,
                ];
            });
    }

    /**
     * Calculate price per base unit
     * 
     * @param float $price Price in current unit
     * @param int $productId
     * @param int $unitId Current unit ID
     * @return float Price per base unit
     * @throws \Exception
     */
    public function pricePerBaseUnit(float $price, int $productId, int $unitId): float
    {
        $unit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->first();

        if (!$unit) {
            throw new \Exception("Unit (ID: {$unitId}) not found for product ID: {$productId}");
        }

        if ($unit->conversion_to_base == 0) {
            throw new \Exception("Invalid conversion ratio (zero) for unit ID: {$unitId}");
        }

        // Price per base unit = price / conversion_to_base
        // Example: Rp 50,000 per Box (50 pcs) = Rp 50,000 / 50 = Rp 1,000 per Pcs
        return $price / $unit->conversion_to_base;
    }

    /**
     * Validate if unit is available for product
     * 
     * @param int $productId
     * @param int $unitId
     * @return bool
     */
    public function isUnitAvailable(int $productId, int $unitId): bool
    {
        return ProductUnit::where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->exists();
    }
}
