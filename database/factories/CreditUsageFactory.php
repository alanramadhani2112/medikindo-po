<?php

namespace Database\Factories;

use App\Models\CreditUsage;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditUsageFactory extends Factory
{
    protected $model = CreditUsage::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'amount_used' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => $this->faker->randomElement(['reserved', 'billed', 'released']),
        ];
    }

    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reserved',
        ]);
    }

    public function billed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'billed',
        ]);
    }

    public function released(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'released',
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    public function forPurchaseOrder(PurchaseOrder $po): static
    {
        return $this->state(fn (array $attributes) => [
            'purchase_order_id' => $po->id,
            'organization_id' => $po->organization_id,
            'amount_used' => $po->total_amount,
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_used' => $amount,
        ]);
    }
}