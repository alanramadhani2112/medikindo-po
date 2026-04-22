<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'supplier_id'  => Supplier::factory(),
            'name'         => fake()->words(3, true),
            'sku'          => strtoupper(fake()->unique()->bothify('PRD-###??')),
            'unit'         => fake()->randomElement(['Box', 'Bottle', 'Tablet', 'Ampul', 'Vial']),
            'price'        => fake()->numberBetween(5000, 500000),
            'cost_price'   => fake()->numberBetween(3000, 300000),
            'selling_price'=> fake()->numberBetween(5000, 500000),
            'is_narcotic'  => false,
            'description'  => fake()->sentence(),
            'is_active'    => true,
        ];
    }

    public function narcotic(): static
    {
        return $this->state(fn(array $attributes) => ['is_narcotic' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => ['is_active' => false]);
    }

    public function forSupplier(Supplier $supplier): static
    {
        return $this->state(fn(array $attributes) => ['supplier_id' => $supplier->id]);
    }
}
