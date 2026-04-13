<?php

namespace Database\Factories;

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
            'invoice_number' => 'INV-SUP-' . $this->faker->unique()->numberBetween(1000, 99999),
            'supplier_id' => Supplier::factory(),
            'organization_id' => Organization::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'total_amount' => $this->faker->randomFloat(2, 1000000, 50000000),
            'paid_amount' => 0,
            'status' => SupplierInvoice::STATUS_ISSUED,
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
        ];
    }
}
