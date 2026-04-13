<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    /**
     * Validate that a PO has at least one item before submission.
     *
     * @throws ValidationException
     */
    public function ensurePOHasItems(PurchaseOrder $po): void
    {
        if ($po->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => 'A Purchase Order must have at least one item before submission.',
            ]);
        }
    }

    /**
     * Validate that the supplier exists and is active.
     *
     * @throws ValidationException
     */
    public function ensureSupplierIsValid(int $supplierId): Supplier
    {
        $supplier = Supplier::where('id', $supplierId)->where('is_active', true)->first();

        if (! $supplier) {
            throw ValidationException::withMessages([
                'supplier_id' => 'The selected supplier does not exist or is inactive.',
            ]);
        }

        return $supplier;
    }

    /**
     * Validate that a product exists, is active, and belongs to the given supplier.
     *
     * @throws ValidationException
     */
    public function ensureProductIsValid(int $productId, int $supplierId): Product
    {
        $product = Product::where('id', $productId)
            ->where('supplier_id', $supplierId)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => "Product ID {$productId} is invalid, inactive, or does not belong to the selected supplier.",
            ]);
        }

        return $product;
    }

    /**
     * Guard against duplicate PO numbers.
     *
     * @throws ValidationException
     */
    public function ensureNoDuplicatePO(string $poNumber): void
    {
        if (PurchaseOrder::where('po_number', $poNumber)->exists()) {
            throw ValidationException::withMessages([
                'po_number' => "A Purchase Order with number {$poNumber} already exists.",
            ]);
        }
    }

    /**
     * Ensure the PO can transition to the requested status.
     *
     * @throws ValidationException
     */
    public function ensureValidTransition(PurchaseOrder $po, string $targetStatus): void
    {
        if (! $po->canTransitionTo($targetStatus)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition PO from [{$po->status}] to [{$targetStatus}].",
            ]);
        }
    }
}
