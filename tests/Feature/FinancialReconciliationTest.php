<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\CreditLimit;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Approval;
use App\Models\GoodsReceipt;
use App\Services\ApprovalService;
use App\Services\GoodsReceiptService;
use App\Services\POService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_financial_lifecycle()
    {
        // 0. Seed Roles
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // 1. Setup Data
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $admin = User::factory()->create(['organization_id' => $organization->id]);
        $admin->assignRole('Healthcare User'); // Required for confirmReceipt gate
        $approver = User::factory()->create(['organization_id' => $organization->id]);
        $approver->assignRole('Super Admin');

        $limit = CreditLimit::updateOrCreate(
            ['organization_id' => $organization->id],
            ['max_limit' => 5000000, 'is_active' => true]
        );

        $product = Product::factory()->create(['price' => 100000]);

        // 2. Create and Submit PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-FIN',
            'organization_id' => $organization->id,
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'status' => PurchaseOrder::STATUS_DRAFT,
            'total_amount' => 1000000, // 10 units
        ]);
        $po->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100000,
            'subtotal' => 1000000,
        ]);

        $poService = app(POService::class);
        $poService->submitPO($po, $admin);

        $this->assertEquals(PurchaseOrder::STATUS_SUBMITTED, $po->status);
        // Credit control is tracked via outstanding AR invoices, not a separate credit_usages table
        $this->assertDatabaseHas('purchase_orders', [
            'id'     => $po->id,
            'status' => PurchaseOrder::STATUS_SUBMITTED,
        ]);

        // 3. Approve PO
        $approvalService = app(ApprovalService::class);
        $approvalService->process($po, $approver, Approval::LEVEL_STANDARD, Approval::STATUS_APPROVED);
        
        $this->assertEquals(PurchaseOrder::STATUS_APPROVED, $po->fresh()->status);
        // Credit control is tracked via outstanding AR invoices (no credit_usages table insert)

        // 4. Send to Supplier — delivery happens outside system, PO stays approved
        // (no status change needed)

        // 5. Partial Goods Receipt
        $grService = app(GoodsReceiptService::class);
        $fakePhoto = \Illuminate\Http\UploadedFile::fake()->image('delivery.jpg');

        $gr = $grService->confirmReceipt($po->fresh(), $admin, [
            [
                'purchase_order_item_id' => $po->items->first()->id,
                'product_id'             => $po->items->first()->product_id,
                'quantity_received'      => 6,
                'batch_no'               => 'BATCH-001',
                'expiry_date'            => now()->addYear()->toDateString(),
            ]
        ], null, 'DO-TEST-001', $fakePhoto);

        // GR is partial (6/10) so PO transitions to partially_received
        $this->assertEquals(GoodsReceipt::STATUS_PARTIAL, $gr->fresh()->status);

        // In new architecture, invoices are NOT auto-generated. Finance must issue explicitly.
        // So supplier_invoices table is empty at this stage — by design.
        $this->assertDatabaseCount('supplier_invoices', 0);
        
        // 6. Complete Goods Receipt
        $fakePhoto2 = \Illuminate\Http\UploadedFile::fake()->image('delivery2.jpg');
        $gr2 = $grService->confirmReceipt($po->fresh(), $admin, [
            [
                'purchase_order_item_id' => $po->items->first()->id,
                'product_id'             => $po->items->first()->product_id,
                'quantity_received'      => 4,
                'batch_no'               => 'BATCH-002',
                'expiry_date'            => now()->addYear()->toDateString(),
            ]
        ], null, 'DO-TEST-002', $fakePhoto2);

        $this->assertEquals(PurchaseOrder::STATUS_COMPLETED, $po->fresh()->status);

        // 7. In the new workflow, Finance must explicitly issue the invoice.
        //    Payment is confirmed by Organization, verified by Finance via InvoiceService.
        //    These steps are covered in InvoiceService unit/feature tests.
        $this->assertDatabaseHas('goods_receipts', [
            'purchase_order_id' => $po->id,
        ]);
    }
}

