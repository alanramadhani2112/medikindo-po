<?php

namespace Tests\Feature;

use App\Models\CustomerInvoice;
use App\Models\GoodsReceipt;
use App\Models\Organization;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use Tests\TestCase;

class ApiCriticalFlowTest extends TestCase
{
    private function makeCompletedFlowFixture(): array
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->forSupplier($supplier)->create([
            'price' => 100000,
        ]);

        $creator = User::factory()->healthcareUser()->forOrganization($organization)->create();

        $po = PurchaseOrder::factory()
            ->completed()
            ->forOrganization($organization)
            ->forSupplier($supplier)
            ->createdBy($creator)
            ->create([
                'total_amount' => 200000,
            ]);

        $item = $po->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'subtotal' => 200000,
        ]);

        $gr = GoodsReceipt::create([
            'gr_number' => 'GR-FIX-' . strtoupper(substr(uniqid(), -6)),
            'purchase_order_id' => $po->id,
            'organization_id' => $organization->id,
            'received_by' => $creator->id,
            'confirmed_by' => $creator->id,
            'confirmed_at' => now(),
            'received_date' => now()->toDateString(),
            'status' => GoodsReceipt::STATUS_COMPLETED,
        ]);

        $gr->items()->create([
            'purchase_order_item_id' => $item->id,
            'quantity_received' => 2,
            'condition' => 'Good',
        ]);

        return [$organization, $supplier, $creator, $po, $item, $gr];
    }

    public function test_send_to_supplier_endpoint_ships_approved_po(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $creator = User::factory()->healthcareUser()->forOrganization($organization)->create();
        $approver = User::factory()->approver()->create(['organization_id' => $organization->id]);

        $po = PurchaseOrder::factory()
            ->approved()
            ->forOrganization($organization)
            ->forSupplier($supplier)
            ->createdBy($creator)
            ->create();

        $this->actingAsUser($approver);

        $this->postJson("/api/purchase-orders/{$po->id}/send-to-supplier")
            ->assertOk()
            ->assertJsonPath('purchase_order.status', PurchaseOrder::STATUS_APPROVED);
    }

    public function test_goods_receipt_store_endpoint_confirms_receipt_and_completes_po(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->forSupplier($supplier)->create([
            'price' => 50000,
        ]);
        $healthcare = User::factory()->healthcareUser()->forOrganization($organization)->create();

        $po = PurchaseOrder::factory()
            ->approved()
            ->forOrganization($organization)
            ->forSupplier($supplier)
            ->createdBy($healthcare)
            ->create();

        $item = $po->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 50000,
            'subtotal' => 150000,
        ]);

        $this->actingAsUser($healthcare);

        $fakePhoto = \Illuminate\Http\UploadedFile::fake()->image('delivery.jpg', 100, 100);

        $this->postJson('/api/goods-receipts', [
            'purchase_order_id'     => $po->id,
            'delivery_order_number' => 'DO-TEST-001',
            'delivery_photo'        => $fakePhoto,
            'items' => [
                [
                    'purchase_order_item_id' => $item->id,
                    'product_id'             => $product->id,
                    'quantity_received'      => 3,
                    'batch_no'               => 'BATCH-001',
                    'expiry_date'            => now()->addYear()->toDateString(),
                    'condition'              => 'Good',
                ],
            ],
        ])->assertStatus(201)
            ->assertJsonPath('data.status', GoodsReceipt::STATUS_PARTIAL);

        $this->assertEquals(PurchaseOrder::STATUS_COMPLETED, $po->fresh()->status);
    }

    public function test_customer_invoice_store_requires_manage_invoice_permission_and_finance_can_create(): void
    {
        [$organization, $supplier, $healthcare, $po, $item, $gr] = $this->makeCompletedFlowFixture();
        $finance = User::factory()->finance()->create(['organization_id' => $organization->id]);

        $this->actingAsUser($healthcare);
        $this->postJson('/api/invoices/customer', [
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'due_date' => now()->addDays(14)->toDateString(),
        ])->assertStatus(403);

        $this->actingAsUser($finance);
        $this->postJson('/api/invoices/customer', [
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'due_date' => now()->addDays(14)->toDateString(),
        ])->assertStatus(201)
            ->assertJsonPath('data.status', CustomerInvoice::STATUS_ISSUED);

        $this->assertDatabaseHas('supplier_invoices', [
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
        ]);
    }

    public function test_outgoing_payment_endpoint_blocks_healthcare_and_allows_finance(): void
    {
        [$organization, $supplier, $healthcare, $po, $item, $gr] = $this->makeCompletedFlowFixture();
        $finance = User::factory()->finance()->create(['organization_id' => $organization->id]);

        $supplierInvoice = SupplierInvoice::create([
            'invoice_number' => 'SI-API-' . strtoupper(substr(uniqid(), -6)),
            'organization_id' => $organization->id,
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'total_amount' => 200000,
            'paid_amount' => 0,
            'status' => \App\Enums\SupplierInvoiceStatus::VERIFIED->value,
            'due_date' => now()->addDays(14)->toDateString(),
        ]);

        $payload = [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 100000,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->toDateString(),
        ];

        $this->actingAsUser($healthcare);
        $this->postJson('/api/payments/outgoing', $payload)->assertStatus(403);

        $this->actingAsUser($finance);
        $this->postJson('/api/payments/outgoing', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.type', 'outgoing');
    }

    public function test_customer_invoice_store_is_idempotent_for_same_po_and_goods_receipt(): void
    {
        [$organization, $supplier, $healthcare, $po, $item, $gr] = $this->makeCompletedFlowFixture();
        $finance = User::factory()->finance()->create(['organization_id' => $organization->id]);

        $payload = [
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'due_date' => now()->addDays(14)->toDateString(),
        ];

        $this->actingAsUser($finance);
        $this->postJson('/api/invoices/customer', $payload)->assertStatus(201);
        $this->postJson('/api/invoices/customer', $payload)->assertStatus(201);

        $this->assertDatabaseCount('customer_invoices', 1);
        $this->assertDatabaseCount('supplier_invoices', 1);
    }
}
