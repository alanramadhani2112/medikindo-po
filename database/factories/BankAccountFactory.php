<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'bank_name' => $this->faker->randomElement([
                'Bank Central Asia (BCA)',
                'Bank Mandiri',
                'Bank Negara Indonesia (BNI)',
                'Bank Rakyat Indonesia (BRI)',
                'Bank CIMB Niaga',
                'Bank Permata',
                'Bank Danamon'
            ]),
            'bank_code' => $this->faker->randomElement(['014', '008', '009', '002', '022', '013', '011']),
            'account_number' => $this->faker->numerify('##########'),
            'account_holder_name' => $this->faker->company(),
            'is_active' => true,
            'is_default' => false,
            'account_type' => $this->faker->randomElement(['receive', 'send', 'both']),
            'default_for_receive' => false,
            'default_for_send' => false,
            'default_priority' => $this->faker->numberBetween(1, 10),
            'current_balance' => $this->faker->randomFloat(2, 0, 10000000),
            'balance_updated_at' => now(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => [
            // BankAccount doesn't have organization_id - it's global
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
            'default_priority' => 1,
        ]);
    }

    public function forReceive(): static
    {
        return $this->state(fn(array $attributes) => [
            'account_type' => 'receive',
            'default_for_receive' => true,
        ]);
    }

    public function forSend(): static
    {
        return $this->state(fn(array $attributes) => [
            'account_type' => 'send',
            'default_for_send' => true,
        ]);
    }

    public function forBoth(): static
    {
        return $this->state(fn(array $attributes) => [
            'account_type' => 'both',
            'default_for_receive' => true,
            'default_for_send' => true,
        ]);
    }

    public function withBalance(float $balance): static
    {
        return $this->state(fn(array $attributes) => [
            'current_balance' => $balance,
            'balance_updated_at' => now(),
        ]);
    }

    public function bca(): static
    {
        return $this->state(fn(array $attributes) => [
            'bank_name' => 'Bank Central Asia (BCA)',
            'bank_code' => '014',
        ]);
    }

    public function mandiri(): static
    {
        return $this->state(fn(array $attributes) => [
            'bank_name' => 'Bank Mandiri',
            'bank_code' => '008',
        ]);
    }

    public function bni(): static
    {
        return $this->state(fn(array $attributes) => [
            'bank_name' => 'Bank Negara Indonesia (BNI)',
            'bank_code' => '009',
        ]);
    }

    public function bri(): static
    {
        return $this->state(fn(array $attributes) => [
            'bank_name' => 'Bank Rakyat Indonesia (BRI)',
            'bank_code' => '002',
        ]);
    }
}