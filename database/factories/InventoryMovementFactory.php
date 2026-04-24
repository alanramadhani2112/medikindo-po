<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'movement_type' => $this->faker->randomElement([
                InventoryMovement::TYPE_IN,
                InventoryMovement::TYPE_OUT,
                InventoryMovement::TYPE_ADJUSTMENT
            ]),
            'quantity' => $this->faker->numberBetween(-50, 100),
            'reference_type' => null,
            'reference_id' => null,
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create stock in movement
     */
    public function stockIn(int $quantity = null): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => InventoryMovement::TYPE_IN,
            'quantity' => $quantity ?? $this->faker->numberBetween(10, 100),
        ]);
    }

    /**
     * Create stock out movement
     */
    public function stockOut(int $quantity = null): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => InventoryMovement::TYPE_OUT,
            'quantity' => -abs($quantity ?? $this->faker->numberBetween(1, 50)),
        ]);
    }

    /**
     * Create adjustment movement
     */
    public function adjustment(int $quantity = null): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => InventoryMovement::TYPE_ADJUSTMENT,
            'quantity' => $quantity ?? $this->faker->numberBetween(-20, 20),
        ]);
    }

    /**
     * Create movement for specific inventory item
     */
    public function forInventoryItem(InventoryItem $inventoryItem): static
    {
        return $this->state(fn (array $attributes) => [
            'inventory_item_id' => $inventoryItem->id,
        ]);
    }

    /**
     * Create movement by specific user
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }

    /**
     * Create movement with reference
     */
    public function withReference(string $referenceType, int $referenceId): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Create movement with notes
     */
    public function withNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $notes,
        ]);
    }
}