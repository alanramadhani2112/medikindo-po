<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Organization;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'product_id' => Product::factory(),
            'batch_no' => 'BATCH-' . $this->faker->unique()->numerify('######'),
            'expiry_date' => $this->faker->dateTimeBetween('+30 days', '+2 years'),
            'quantity_on_hand' => $this->faker->numberBetween(50, 500),
            'quantity_reserved' => 0,
            'unit_cost' => $this->faker->randomFloat(2, 10, 1000),
            'location' => $this->faker->randomElement(['A1-01', 'A1-02', 'B2-01', 'B2-02', 'C3-01']),
        ];
    }

    /**
     * Create inventory item for specific organization
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create inventory item for specific product
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Create inventory item with low stock
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $this->faker->numberBetween(1, 9),
            'quantity_reserved' => 0,
        ]);
    }

    /**
     * Create inventory item with no stock
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => 0,
            'quantity_reserved' => 0,
        ]);
    }

    /**
     * Create inventory item expiring soon
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('+1 day', '+60 days'),
        ]);
    }

    /**
     * Create expired inventory item
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Create inventory item with no expiry date
     */
    public function noExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => null,
        ]);
    }

    /**
     * Create inventory item with reserved stock
     */
    public function withReservedStock(int $reserved = null): static
    {
        return $this->state(function (array $attributes) use ($reserved) {
            $onHand = $attributes['quantity_on_hand'] ?? 100;
            $reservedQty = $reserved ?? $this->faker->numberBetween(1, $onHand);
            
            return [
                'quantity_on_hand' => $onHand,
                'quantity_reserved' => min($reservedQty, $onHand),
            ];
        });
    }

    /**
     * Create inventory item with specific batch number
     */
    public function withBatch(string $batchNo): static
    {
        return $this->state(fn (array $attributes) => [
            'batch_no' => $batchNo,
        ]);
    }

    /**
     * Create inventory item with specific quantities
     */
    public function withQuantities(int $onHand, int $reserved = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $onHand,
            'quantity_reserved' => $reserved,
        ]);
    }
}