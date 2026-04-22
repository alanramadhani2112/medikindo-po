<?php

namespace Database\Factories;

use App\Enums\SupplierInvoiceStatus;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierInvoiceFactory extends Factory
{
    protected $model = SupplierInvoice::class;

    public function definition(): array
    {
        return [
            'invoice_number'    => 'INV-SUP-' . $this->faker->unique()->numberBetween(1000, 99999),
            'supplier_id'       => Supplier::factory(),
            'organization_id'   => Organization::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'total_amount'      => $this->faker->randomFloat(2, 1000000, 50000000),
            'subtotal_amount'   => $this->faker->randomFloat(2, 900000, 45000000),
            'discount_amount'   => 0,
            'tax_amount'        => 0,
            'paid_amount'       => 0,
            'status'            => SupplierInvoiceStatus::DRAFT,
            'due_date'          => $this->faker->dateTimeBetween('now', '+30 days'),
            'version'           => 0,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SupplierInvoiceStatus::VERIFIED,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SupplierInvoiceStatus::PAID,
            'paid_amount' => $attributes['total_amount'],
        ]);
    }
}
