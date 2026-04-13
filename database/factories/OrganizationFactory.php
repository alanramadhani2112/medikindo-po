<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->company() . ' Hospital',
            'type'           => fake()->randomElement(['clinic', 'hospital']),
            'code'           => strtoupper(fake()->unique()->lexify('ORG-???')),
            'address'        => fake()->address(),
            'phone'          => fake()->phoneNumber(),
            'email'          => fake()->companyEmail(),
            'license_number' => 'LIC-' . fake()->numerify('######'),
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => ['is_active' => false]);
    }
}
