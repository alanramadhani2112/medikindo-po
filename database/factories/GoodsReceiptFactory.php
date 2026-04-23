<?php

namespace Database\Factories;

use App\Models\GoodsReceipt;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReceiptFactory extends Factory
{
    protected $model = GoodsReceipt::class;

    public function definition(): array
    {
        return [
            'gr_number'         => 'GR-' . $this->faker->unique()->numberBetween(1000, 99999),
            'organization_id'   => Organization::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'received_date'     => $this->faker->dateTimeBetween('-30 days', 'now'),
            'status'            => GoodsReceipt::STATUS_PARTIAL,
            'notes'             => $this->faker->optional()->sentence(),
            'received_by'       => User::factory(),
        ];
    }

    public function forPurchaseOrder(PurchaseOrder $purchaseOrder): static
    {
        return $this->state(fn(array $attributes) => [
            'purchase_order_id' => $purchaseOrder->id,
            'organization_id' => $purchaseOrder->organization_id,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => GoodsReceipt::STATUS_COMPLETED,
            'confirmed_at' => now(),
        ]);
    }

    public function partial(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => GoodsReceipt::STATUS_PARTIAL,
        ]);
    }
}