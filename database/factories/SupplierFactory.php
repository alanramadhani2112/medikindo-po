<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name'           => 'PT ' . fake()->company(),
            'code'           => strtoupper(fake()->unique()->lexify('SUP-???')),
            'address'        => fake()->address(),
            'phone'          => fake()->phoneNumber(),
            'email'          => fake()->companyEmail(),
            'npwp'           => fake()->numerify('##.###.###.#-###.###'),
            'license_number' => 'SUP-LIC-' . fake()->numerify('####'),
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => ['is_active' => false]);
    }
}
