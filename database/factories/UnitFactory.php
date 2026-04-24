<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Valid enum values from migration: base, packaging, volume, weight, bundle
        $unitTypes = ['base', 'packaging', 'volume', 'weight', 'bundle'];
        $unitData = [
            'weight' => [
                ['name' => 'Kilogram', 'symbol' => 'kg'],
                ['name' => 'Gram', 'symbol' => 'g'],
                ['name' => 'Pound', 'symbol' => 'lb'],
            ],
            'volume' => [
                ['name' => 'Liter', 'symbol' => 'L'],
                ['name' => 'Milliliter', 'symbol' => 'mL'],
                ['name' => 'Gallon', 'symbol' => 'gal'],
            ],
            'base' => [
                ['name' => 'Pieces', 'symbol' => 'pcs'],
                ['name' => 'Each', 'symbol' => 'ea'],
                ['name' => 'Unit', 'symbol' => 'unit'],
            ],
            'packaging' => [
                ['name' => 'Box', 'symbol' => 'box'],
                ['name' => 'Carton', 'symbol' => 'ctn'],
                ['name' => 'Pack', 'symbol' => 'pack'],
            ],
            'bundle' => [
                ['name' => 'Dozen', 'symbol' => 'dz'],
                ['name' => 'Bundle', 'symbol' => 'bdl'],
                ['name' => 'Set', 'symbol' => 'set'],
            ],
        ];

        $type = $this->faker->randomElement($unitTypes);
        $unit = $this->faker->randomElement($unitData[$type]);

        return [
            'name' => $unit['name'] . ' ' . $this->faker->unique()->randomNumber(3), // Add unique suffix
            'symbol' => $unit['symbol'],
            'type' => $type,
            'description' => $this->faker->optional()->sentence(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Indicate that the unit is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the unit is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a unit of specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}