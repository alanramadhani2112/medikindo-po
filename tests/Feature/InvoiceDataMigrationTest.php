<?php

namespace Tests\Feature;

use App\Models\CustomerInvoice;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Organization;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceDataMigrationTest extends TestCase
{
    use RefreshDatabase;

    private User $financeUser;
    private Organization $organization;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles directly
        \Spatie\Permission\Models\Role::create(['name' => 'Finance']);
        \Spatie\Permission\Models\Role::create(['name' => 'Super Admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'Healthcare User']);

        $this->organization = Organization::factory()->create([
            'default_tax_rate' => '10.00',
            'default_discount_percentage' => '5.00',
        ]);

        $this->financeUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $this->financeUser->assignRole('Finance');

        $this->supplier = Supplier::factory()->create();

        $this->product = Product::factory()->create([
            'name' => 'Test Product',
        ]);
    }

    public function test_it_can_run_migration_in_dry_run_mode()
    {
        // Create invoice without line items
        $invoice = $this->createInvoiceWithoutLineItems();

        // Run migration in dry-run mode
        $this->artisan('invoice:migrate-to-line-items', ['--dry-run' => true])
            ->expectsOutput('Mode: DRY RUN (no changes will be made)')
            ->assertExitCode(0);

        // Verify no line items were created
        $this->assertCount(0, $invoice->fresh()->lineItems);
    }

    public function test_it_migrates_supplier_invoice_to_line_items()
    {
        // Create invoice without line items
        $invoice = $this->createInvoiceWithoutLineItems();

        // Run migration
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'supplier'])
            ->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
            ->assertExitCode(0);

        // Verify line items were created
        $invoice->refresh();
        $this->assertCount(2, $invoice->lineItems);

        // Verify line item details
        $lineItem = $invoice->lineItems->first();
        $this->assertEquals($this->product->id, $lineItem->product_id);
        $this->assertEquals('Test Product', $lineItem->product_name);
        $this->assertEquals('10.000', $lineItem->quantity);
        $this->assertEquals('1000.00', $lineItem->unit_price);
        $this->assertEquals('5.00', $lineItem->discount_percentage);
        $this->assertEquals('10.00', $lineItem->tax_rate);

        // Verify calculations
        $this->assertNotNull($lineItem->discount_amount);
        $this->assertNotNull($lineItem->tax_amount);
        $this->assertNotNull($lineItem->line_total);
    }

    public function test_it_migrates_customer_invoice_to_line_items()
    {
        // Create customer invoice without line items
        $po = $this->createPurchaseOrder();
        $gr = $this->createGoodsReceipt($po);

        $invoice = CustomerInvoice::create([
            'invoice_number' => 'INV-TEST-001',
            'organization_id' => $this->organization->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'total_amount' => '20900.00',
            'paid_amount' => '0.00',
            'status' => 'issued',
            'issued_by' => $this->financeUser->id,
            'issued_at' => now(),
            'due_date' => now()->addDays(30),
            'version' => 0,
        ]);

        // Run migration
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'customer'])
            ->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
            ->assertExitCode(0);

        // Verify line items were created
        $invoice->refresh();
        $this->assertCount(2, $invoice->lineItems);

        // Verify subtotal, discount, and tax were calculated
        $this->assertNotNull($invoice->subtotal_amount);
        $this->assertNotNull($invoice->discount_amount);
        $this->assertNotNull($invoice->tax_amount);
    }

    public function test_it_skips_invoices_that_already_have_line_items()
    {
        // Create invoice with line items
        $invoice = $this->createInvoiceWithoutLineItems();
        
        // Manually create a line item
        $invoice->lineItems()->create([
            'product_id' => $this->product->id,
            'product_name' => 'Test Product',
            'quantity' => '10.000',
            'unit_price' => '1000.00',
            'discount_percentage' => '5.00',
            'discount_amount' => '500.00',
            'tax_rate' => '10.00',
            'tax_amount' => '950.00',
            'line_total' => '10450.00',
        ]);

        // Run migration
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'supplier'])
            ->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
            ->assertExitCode(0);

        // Verify only 1 line item exists (not duplicated)
        $this->assertCount(1, $invoice->fresh()->lineItems);
    }

    public function test_it_can_migrate_specific_invoice_by_id()
    {
        // Create two invoices
        $invoice1 = $this->createInvoiceWithoutLineItems();
        $invoice2 = $this->createInvoiceWithoutLineItems();

        // Migrate only invoice1
        $this->artisan('invoice:migrate-to-line-items', [
            '--type' => 'supplier',
            '--invoice-id' => $invoice1->id,
        ])->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
          ->assertExitCode(0);

        // Verify only invoice1 has line items
        $this->assertCount(2, $invoice1->fresh()->lineItems);
        $this->assertCount(0, $invoice2->fresh()->lineItems);
    }

    public function test_it_detects_calculation_discrepancies()
    {
        // Create invoice with incorrect total
        $po = $this->createPurchaseOrder();
        $gr = $this->createGoodsReceipt($po);

        $invoice = SupplierInvoice::create([
            'invoice_number' => 'SI-TEST-001',
            'organization_id' => $this->organization->id,
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'total_amount' => '99999.99', // Incorrect total
            'paid_amount' => '0.00',
            'status' => 'issued',
            'issued_by' => $this->financeUser->id,
            'issued_at' => now(),
            'due_date' => now()->addDays(30),
            'version' => 0,
        ]);

        // Run migration and expect discrepancy warning
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'supplier'])
            ->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
            ->expectsOutput("⚠️  Discrepancy found in Supplier Invoice #{$invoice->id}:")
            ->assertExitCode(0);

        // Verify line items were still created
        $this->assertCount(2, $invoice->fresh()->lineItems);
    }

    public function test_it_uses_organization_defaults_for_tax_and_discount()
    {
        // Create organization with specific defaults
        $org = Organization::factory()->create([
            'default_tax_rate' => '11.00',
            'default_discount_percentage' => '7.50',
        ]);

        $user = User::factory()->create(['organization_id' => $org->id]);
        $user->assignRole('Finance');

        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        // Create PO and GR
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-ORG',
            'organization_id' => $org->id,
            'supplier_id' => $supplier->id,
            'total_amount' => '10000.00',
            'status' => 'completed',
            'requested_by' => $user->id,
            'created_by' => $user->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'quantity' => '10.000',
            'unit_price' => '1000.00',
        ]);

        $gr = GoodsReceipt::create([
            'gr_number' => 'GR-TEST-ORG',
            'organization_id' => $org->id,
            'purchase_order_id' => $po->id,
            'received_by' => $user->id,
            'received_at' => now(),
            'received_date' => now()->toDateString(),
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $gr->id,
            'purchase_order_item_id' => $poItem->id,
            'quantity_received' => '10.000',
        ]);

        // Create invoice
        $invoice = SupplierInvoice::create([
            'invoice_number' => 'SI-TEST-ORG',
            'organization_id' => $org->id,
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'total_amount' => '10000.00',
            'paid_amount' => '0.00',
            'status' => 'issued',
            'issued_by' => $user->id,
            'issued_at' => now(),
            'due_date' => now()->addDays(30),
            'version' => 0,
        ]);

        // Run migration
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'supplier'])
            ->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
            ->assertExitCode(0);

        // Verify line items use organization defaults
        $lineItem = $invoice->fresh()->lineItems->first();
        $this->assertEquals('11.00', $lineItem->tax_rate);
        $this->assertEquals('7.50', $lineItem->discount_percentage);
    }

    public function test_it_handles_invoices_without_goods_receipt()
    {
        // Create invoice without goods receipt
        $po = $this->createPurchaseOrder();

        $invoice = SupplierInvoice::create([
            'invoice_number' => 'SI-NO-GR',
            'organization_id' => $this->organization->id,
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => null, // No GR
            'total_amount' => '10000.00',
            'paid_amount' => '0.00',
            'status' => 'issued',
            'issued_by' => $this->financeUser->id,
            'issued_at' => now(),
            'due_date' => now()->addDays(30),
            'version' => 0,
        ]);

        // Run migration
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'supplier'])
            ->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
            ->assertExitCode(0);

        // Verify invoice was skipped (no line items created)
        $this->assertCount(0, $invoice->fresh()->lineItems);
    }

    public function test_it_processes_invoices_in_batches()
    {
        // Create multiple invoices
        for ($i = 0; $i < 5; $i++) {
            $this->createInvoiceWithoutLineItems();
        }

        // Run migration with small batch size
        $this->artisan('invoice:migrate-to-line-items', [
            '--type' => 'supplier',
            '--batch-size' => 2,
        ])->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
          ->assertExitCode(0);

        // Verify all invoices were migrated
        $this->assertEquals(5, SupplierInvoice::has('lineItems')->count());
    }

    public function test_it_displays_migration_summary()
    {
        // Create invoices
        $this->createInvoiceWithoutLineItems();
        $this->createInvoiceWithoutLineItems();

        // Run migration
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'supplier'])
            ->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
            ->expectsOutput('Migration Summary')
            ->expectsOutput('✅ Migration completed successfully!')
            ->assertExitCode(0);
    }

    public function test_it_preserves_original_total_amount()
    {
        // Create invoice with specific total
        $invoice = $this->createInvoiceWithoutLineItems();
        $originalTotal = $invoice->total_amount;

        // Run migration
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'supplier'])
            ->expectsConfirmation('This will modify invoice data. Have you backed up your database?', 'yes')
            ->assertExitCode(0);

        // Verify original total is preserved
        $this->assertEquals($originalTotal, $invoice->fresh()->total_amount);
    }

    public function test_it_requires_database_backup_confirmation_in_live_mode()
    {
        $this->createInvoiceWithoutLineItems();

        // Run without dry-run and expect confirmation prompt
        $this->artisan('invoice:migrate-to-line-items', ['--type' => 'supplier'])
            ->expectsQuestion('This will modify invoice data. Have you backed up your database?', false)
            ->expectsOutput('Migration cancelled. Please backup your database first.')
            ->assertExitCode(1);
    }

    // -----------------------------------------------------------------------
    // Helper Methods
    // -----------------------------------------------------------------------

    private function createPurchaseOrder(): PurchaseOrder
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . uniqid(),
            'organization_id' => $this->organization->id,
            'supplier_id' => $this->supplier->id,
            'total_amount' => '20000.00',
            'status' => 'completed',
            'requested_by' => $this->financeUser->id,
            'created_by' => $this->financeUser->id,
        ]);

        // Create 2 PO items
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity' => '10.000',
            'unit_price' => '1000.00',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity' => '10.000',
            'unit_price' => '1000.00',
        ]);

        return $po;
    }

    private function createGoodsReceipt(PurchaseOrder $po): GoodsReceipt
    {
        $gr = GoodsReceipt::create([
            'gr_number' => 'GR-' . uniqid(),
            'organization_id' => $this->organization->id,
            'purchase_order_id' => $po->id,
            'received_by' => $this->financeUser->id,
            'received_at' => now(),
            'received_date' => now()->toDateString(),
        ]);

        // Create GR items for each PO item
        foreach ($po->items as $poItem) {
            GoodsReceiptItem::create([
                'goods_receipt_id' => $gr->id,
                'purchase_order_item_id' => $poItem->id,
                'quantity_received' => $poItem->quantity,
            ]);
        }

        return $gr;
    }

    private function createInvoiceWithoutLineItems(): SupplierInvoice
    {
        $po = $this->createPurchaseOrder();
        $gr = $this->createGoodsReceipt($po);

        return SupplierInvoice::create([
            'invoice_number' => 'SI-' . uniqid(),
            'organization_id' => $this->organization->id,
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'total_amount' => '20900.00',
            'paid_amount' => '0.00',
            'status' => 'issued',
            'issued_by' => $this->financeUser->id,
            'issued_at' => now(),
            'due_date' => now()->addDays(30),
            'version' => 0,
        ]);
    }
}
