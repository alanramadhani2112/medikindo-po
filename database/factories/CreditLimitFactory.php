<?php

namespace Database\Factories;

use App\Models\CreditLimit;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditLimitFactory extends Factory
{
    protected $model = CreditLimit::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'max_limit' => $this->faker->randomFloat(2, 10000, 1000000), // 10K to 1M
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withLimit(float $limit): static
    {
        return $this->state(fn (array $attributes) => [
            'max_limit' => $limit,
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}