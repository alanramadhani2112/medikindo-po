<?php

namespace App\Services;

use App\Exceptions\PriceListNotFoundException;
use App\Models\PriceList;
use App\Models\Product;
use Carbon\Carbon;

/**
 * Price List Service
 *
 * Resolves the selling price for a given (organization, product) combination.
 * Priority: customer-specific active price list → fallback to products.selling_price.
 *
 * @package App\Services
 */
class PriceListService
{
    /**
     * Look up the selling price for a given organization and product.
     *
     * Priority:
     *   1. Active price list record: is_active=true, effective_date <= today,
     *      expiry_date IS NULL OR expiry_date >= today, ordered by effective_date DESC.
     *   2. Fallback: products.selling_price
     *
     * @param int $organizationId
     * @param int $productId
     * @return string Selling price as string with 2 decimal places
     * @throws PriceListNotFoundException If no price found at all
     */
    public function lookup(int $organizationId, int $productId): string
    {
        $today = Carbon::today();

        // 1. Customer-specific active price list
        $priceList = PriceList::where('organization_id', $organizationId)
            ->where('product_id', $productId)
            ->activeForDate($today)
            ->orderByDesc('effective_date')
            ->first();

        if ($priceList !== null) {
            return number_format((float) $priceList->selling_price, 2, '.', '');
        }

        // 2. Fallback to products.selling_price
        $product = Product::find($productId);

        if ($product !== null && $product->selling_price !== null) {
            return number_format((float) $product->selling_price, 2, '.', '');
        }

        throw new PriceListNotFoundException(
            "Harga jual untuk produk ID {$productId} belum dikonfigurasi"
        );
    }
}
