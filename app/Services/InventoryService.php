<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Add stock from Goods Receipt
     */
    public function addStock(
        int $organizationId,
        int $productId,
        string $batchNo,
        ?string $expiryDate,
        int $quantity,
        float $unitCost,
        string $referenceType,
        int $referenceId,
        int $createdBy,
        ?string $location = null
    ): InventoryItem {
        return DB::transaction(function () use (
            $organizationId,
            $productId,
            $batchNo,
            $expiryDate,
            $quantity,
            $unitCost,
            $referenceType,
            $referenceId,
            $createdBy,
            $location
        ) {
            // Find or create inventory item
            $inventoryItem = InventoryItem::firstOrCreate(
                [
                    'organization_id' => $organizationId,
                    'product_id' => $productId,
                    'batch_no' => $batchNo,
                ],
                [
                    'expiry_date' => $expiryDate,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'unit_cost' => $unitCost,
                    'location' => $location,
                ]
            );

            // Update quantity
            $inventoryItem->increment('quantity_on_hand', $quantity);

            // Record movement
            InventoryMovement::create([
                'inventory_item_id' => $inventoryItem->id,
                'movement_type' => InventoryMovement::TYPE_IN,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $createdBy,
            ]);

            return $inventoryItem->fresh();
        });
    }

    /**
     * Reduce stock (FEFO) for Customer Invoice
     */
    public function reduceStock(
        int $organizationId,
        int $productId,
        int $quantity,
        string $referenceType,
        int $referenceId,
        int $createdBy
    ): array {
        return DB::transaction(function () use (
            $organizationId,
            $productId,
            $quantity,
            $referenceType,
            $referenceId,
            $createdBy
        ) {
            // Get available inventory items (FEFO - earliest expiry first, NULL expiry last)
            $inventoryItems = InventoryItem::where('organization_id', $organizationId)
                ->where('product_id', $productId)
                ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
                ->where(function ($query) {
                    $query->whereNull('expiry_date')
                          ->orWhereDate('expiry_date', '>', now());
                })
                ->orderBy('expiry_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            $totalAvailable = $inventoryItems->sum('quantity_available');

            if ($totalAvailable < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$totalAvailable}, Required: {$quantity}");
            }

            $remainingQty = $quantity;
            $movements = [];

            foreach ($inventoryItems as $item) {
                if ($remainingQty <= 0) {
                    break;
                }

                $availableQty = $item->quantity_available;
                $qtyToReduce = min($remainingQty, $availableQty);

                // Reduce quantity
                $item->decrement('quantity_on_hand', $qtyToReduce);

                // Record movement
                $movement = InventoryMovement::create([
                    'inventory_item_id' => $item->id,
                    'movement_type' => InventoryMovement::TYPE_OUT,
                    'quantity' => -$qtyToReduce,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'created_by' => $createdBy,
                ]);

                $movements[] = $movement;
                $remainingQty -= $qtyToReduce;
            }

            return $movements;
        });
    }

    /**
     * Check available stock for a product
     */
    public function getAvailableStock(int $organizationId, int $productId): int
    {
        return InventoryItem::where('organization_id', $organizationId)
            ->where('product_id', $productId)
            ->sum(DB::raw('quantity_on_hand - quantity_reserved'));
    }

    /**
     * Get stock by batch
     */
    public function getStockByBatch(int $organizationId, int $productId)
    {
        return InventoryItem::where('organization_id', $organizationId)
            ->where('product_id', $productId)
            ->with('product')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems(int $organizationId)
    {
        return InventoryItem::where('organization_id', $organizationId)
            ->lowStock()
            ->with('product')
            ->get();
    }

    /**
     * Get expiring items
     */
    public function getExpiringItems(int $organizationId, int $days = 60)
    {
        return InventoryItem::where('organization_id', $organizationId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->whereDate('expiry_date', '>=', now())
            ->with('product')
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get expired items
     */
    public function getExpiredItems(int $organizationId)
    {
        return InventoryItem::where('organization_id', $organizationId)
            ->expired()
            ->with('product')
            ->get();
    }

    /**
     * Manual stock adjustment
     */
    public function adjustStock(
        int $inventoryItemId,
        int $quantityChange,
        string $notes,
        int $createdBy
    ): InventoryItem {
        return DB::transaction(function () use ($inventoryItemId, $quantityChange, $notes, $createdBy) {
            $inventoryItem = InventoryItem::findOrFail($inventoryItemId);

            // Update quantity
            $inventoryItem->increment('quantity_on_hand', $quantityChange);

            // Record movement
            InventoryMovement::create([
                'inventory_item_id' => $inventoryItem->id,
                'movement_type' => InventoryMovement::TYPE_ADJUSTMENT,
                'quantity' => $quantityChange,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]);

            return $inventoryItem->fresh();
        });
    }
}
