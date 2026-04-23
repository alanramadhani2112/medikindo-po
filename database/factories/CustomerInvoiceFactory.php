<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\CustomerInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerInvoiceFactory extends Factory
{
    protected $model = CustomerInvoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-CUST-' . $this->faker->unique()->numberBetween(1000, 99999),
            'organization_id' => Organization::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'total_amount' => $this->faker->randomFloat(2, 1000000, 50000000),
            'subtotal_amount' => $this->faker->randomFloat(2, 900000, 45000000),
            'discount_amount' => 0,
            'tax_amount' => 0,
            'paid_amount' => 0,
            'status' => CustomerInvoice::STATUS_ISSUED,
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'version' => 0,
        ];
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CustomerInvoice::STATUS_DRAFT,
        ]);
    }

    public function issued(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CustomerInvoice::STATUS_ISSUED,
        ]);
    }

    public function partialPaid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CustomerInvoice::STATUS_PARTIAL_PAID,
            'paid_amount' => $attributes['total_amount'] * 0.5, // 50% paid
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CustomerInvoice::STATUS_PAID,
            'paid_amount' => $attributes['total_amount'],
        ]);
    }

    public function void(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CustomerInvoice::STATUS_VOID,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CustomerInvoice::STATUS_ISSUED,
            'due_date' => now()->subDays(10),
        ]);
    }
}
