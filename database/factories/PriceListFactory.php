<?php

namespace Database\Factories;

use App\Models\PriceList;
use App\Models\Organization;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceList>
 */
class PriceListFactory extends Factory
{
    protected $model = PriceList::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'product_id' => Product::factory(),
            'selling_price' => $this->faker->randomFloat(2, 1000, 100000), // Between 1,000 and 100,000
            'effective_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'expiry_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+90 days'), // 70% chance of having expiry
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
        ];
    }

    /**
     * Indicate that the price list is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the price list is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a price list for specific organization and product.
     */
    public function forOrganizationAndProduct(int $organizationId, int $productId): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organizationId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Create a price list effective from a specific date.
     */
    public function effectiveFrom(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_date' => $date,
        ]);
    }

    /**
     * Create a price list that expires on a specific date.
     */
    public function expiresOn(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $date,
        ]);
    }

    /**
     * Create a price list with no expiry date (permanent).
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => null,
        ]);
    }

    /**
     * Create a price list with specific price.
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'selling_price' => $price,
        ]);
    }
}