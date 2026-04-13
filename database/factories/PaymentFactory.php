<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_number' => 'PAY-' . $this->faker->unique()->numberBetween(1000, 99999),
            'type' => $this->faker->randomElement(['incoming', 'outgoing']),
            'organization_id' => Organization::factory(),
            'amount' => $this->faker->randomFloat(2, 100000, 10000000),
            'payment_date' => now(),
            'payment_method' => 'Transfer',
            'status' => 'completed',
        ];
    }
}
