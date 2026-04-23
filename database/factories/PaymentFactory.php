<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['incoming', 'outgoing']);
        
        return [
            'payment_number' => $this->generatePaymentNumber($type),
            'type' => $type,
            'organization_id' => Organization::factory(),
            'supplier_id' => $type === 'outgoing' ? Supplier::factory() : null,
            'amount' => $this->faker->randomFloat(2, 100000, 5000000),
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'payment_method' => $this->faker->randomElement([
                'Bank Transfer', 'Cash', 'Virtual Account', 'Giro/Cek', 'QRIS'
            ]),
            'bank_account_id' => BankAccount::factory(),
            'sender_bank_name' => $this->faker->optional()->randomElement([
                'BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga', 'Permata'
            ]),
            'sender_account_number' => $this->faker->optional()->numerify('##########'),
            'giro_number' => $this->faker->optional()->regexify('[A-Z0-9]{8}'),
            'giro_due_date' => $this->faker->optional()->dateTimeBetween('+1 day', '+60 days'),
            'issuing_bank' => $this->faker->optional()->randomElement([
                'Bank Mandiri', 'Bank BCA', 'Bank BNI', 'Bank BRI'
            ]),
            'receipt_number' => $this->faker->optional()->regexify('[A-Z0-9]{10}'),
            'payment_proof_path' => $this->faker->optional()->filePath(),
            'reference' => $this->faker->optional()->regexify('[A-Z0-9]{12}'),
            'bank_name_manual' => $this->faker->optional()->company(),
            'description' => $this->faker->optional()->sentence(),
            'surcharge_amount' => $this->faker->randomFloat(2, 0, 50000),
            'surcharge_percentage' => $this->faker->randomFloat(2, 0, 10),
            'status' => 'completed',
        ];
    }

    private function generatePaymentNumber(string $type): string
    {
        $prefix = $type === 'incoming' ? 'PAY-IN-' : 'PAY-OUT-';
        return $prefix . now()->format('YmdHis') . '-' . $this->faker->randomNumber(4);
    }

    public function incoming(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'incoming',
            'payment_number' => $this->generatePaymentNumber('incoming'),
            'supplier_id' => null,
        ]);
    }

    public function outgoing(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'outgoing',
            'payment_number' => $this->generatePaymentNumber('outgoing'),
            'supplier_id' => Supplier::factory(),
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'Bank Transfer',
            'sender_bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'sender_account_number' => $this->faker->numerify('##########'),
            'reference' => $this->faker->regexify('[A-Z0-9]{12}'),
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'Cash',
            'receipt_number' => $this->faker->regexify('[A-Z0-9]{10}'),
            'sender_bank_name' => null,
            'sender_account_number' => null,
            'reference' => null,
        ]);
    }

    public function giro(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'Giro/Cek',
            'giro_number' => $this->faker->regexify('[A-Z0-9]{8}'),
            'giro_due_date' => $this->faker->dateTimeBetween('+1 day', '+60 days'),
            'issuing_bank' => $this->faker->randomElement([
                'Bank Mandiri', 'Bank BCA', 'Bank BNI', 'Bank BRI'
            ]),
            'reference' => $this->faker->regexify('[A-Z0-9]{12}'),
        ]);
    }

    public function virtualAccount(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'Virtual Account',
            'sender_bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'reference' => $this->faker->regexify('[A-Z0-9]{16}'),
        ]);
    }

    public function qris(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'QRIS',
            'reference' => $this->faker->regexify('[A-Z0-9]{20}'),
            'sender_bank_name' => null,
            'sender_account_number' => null,
        ]);
    }

    public function withSurcharge(): static
    {
        return $this->state(fn(array $attributes) => [
            'surcharge_amount' => $this->faker->randomFloat(2, 5000, 50000),
            'surcharge_percentage' => $this->faker->randomFloat(2, 1, 10),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'failed',
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn(array $attributes) => [
            'amount' => $amount,
        ]);
    }

    public function forSupplier(Supplier $supplier): static
    {
        return $this->state(fn(array $attributes) => [
            'supplier_id' => $supplier->id,
        ]);
    }

    public function withBankAccount(BankAccount $bankAccount): static
    {
        return $this->state(fn(array $attributes) => [
            'bank_account_id' => $bankAccount->id,
        ]);
    }
}