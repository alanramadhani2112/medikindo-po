<?php

namespace Database\Factories;

use App\Models\CustomerInvoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\SupplierInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentAllocationFactory extends Factory
{
    protected $model = PaymentAllocation::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'allocated_amount' => $this->faker->randomFloat(2, 50000, 2000000),
        ];
    }

    public function forCustomerInvoice(CustomerInvoice $invoice): static
    {
        return $this->state(fn(array $attributes) => [
            'customer_invoice_id' => $invoice->id,
            'supplier_invoice_id' => null,
        ]);
    }

    public function forSupplierInvoice(SupplierInvoice $invoice): static
    {
        return $this->state(fn(array $attributes) => [
            'supplier_invoice_id' => $invoice->id,
            'customer_invoice_id' => null,
        ]);
    }

    public function forPayment(Payment $payment): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_id' => $payment->id,
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn(array $attributes) => [
            'allocated_amount' => $amount,
        ]);
    }

    public function incoming(): static
    {
        return $this->state(fn(array $attributes) => [
            'customer_invoice_id' => CustomerInvoice::factory(),
            'supplier_invoice_id' => null,
        ]);
    }

    public function outgoing(): static
    {
        return $this->state(fn(array $attributes) => [
            'supplier_invoice_id' => SupplierInvoice::factory(),
            'customer_invoice_id' => null,
        ]);
    }
}