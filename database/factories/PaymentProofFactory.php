<?php

namespace Database\Factories;

use App\Enums\PaymentProofStatus;
use App\Models\CustomerInvoice;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentProofFactory extends Factory
{
    protected $model = PaymentProof::class;

    public function definition(): array
    {
        return [
            'customer_invoice_id' => CustomerInvoice::factory(),
            'submitted_by' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 100000, 5000000),
            'payment_type' => $this->faker->randomElement(['full', 'partial']),
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'payment_method' => $this->faker->randomElement(['Bank Transfer', 'Virtual Account', 'Giro/Cek', 'Cash']),
            'sender_bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga']),
            'sender_account_number' => $this->faker->numerify('##########'),
            'bank_reference' => $this->faker->optional()->regexify('[A-Z0-9]{10}'),
            'notes' => $this->faker->optional()->sentence(),
            'status' => PaymentProofStatus::SUBMITTED,
        ];
    }

    public function forCustomerInvoice(CustomerInvoice $invoice): static
    {
        return $this->state(fn(array $attributes) => [
            'customer_invoice_id' => $invoice->id,
        ]);
    }

    public function submittedBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'submitted_by' => $user->id,
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PaymentProofStatus::SUBMITTED,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PaymentProofStatus::VERIFIED,
            'verified_by' => User::factory(),
            'verified_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PaymentProofStatus::APPROVED,
            'verified_by' => User::factory(),
            'verified_at' => now()->subHour(),
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PaymentProofStatus::REJECTED,
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    public function recalled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PaymentProofStatus::RECALLED,
            'recall_reason' => $this->faker->sentence(),
            'recalled_at' => now(),
        ]);
    }

    public function resubmitted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PaymentProofStatus::RESUBMITTED,
            'resubmission_of_id' => PaymentProof::factory(),
            'resubmission_notes' => $this->faker->sentence(),
        ]);
    }

    public function fullPayment(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_type' => 'full',
        ]);
    }

    public function partialPayment(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_type' => 'partial',
        ]);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'Bank Transfer',
            'sender_bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'sender_account_number' => $this->faker->numerify('##########'),
        ]);
    }

    public function giro(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'Giro/Cek',
            'giro_number' => $this->faker->regexify('[A-Z0-9]{8}'),
            'giro_due_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }
}