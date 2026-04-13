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
            'paid_amount' => 0,
            'status' => CustomerInvoice::STATUS_ISSUED,
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
        ];
    }
}
