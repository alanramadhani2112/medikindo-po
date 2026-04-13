<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuditLog;
use App\Models\CreditLimit;
use App\Models\Organization;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\DeliveryService;
use App\Services\GoodsReceiptService;
use App\Services\InvoiceService;
use App\Services\POService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

echo "=== MEDIKINDO PO SYSTEM - E2E TEST ===\n\n";

try {
    Artisan::call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);

    $organization = Organization::firstOrCreate(
        ['code' => 'E2E-ORG'],
        ['name' => 'E2E Test Organization', 'type' => 'clinic', 'is_active' => true]
    );

    CreditLimit::updateOrCreate(
        ['organization_id' => $organization->id],
        ['max_limit' => 100000000, 'is_active' => true]
    );
    echo "OK Organization: {$organization->name} (ID: {$organization->id})\n";

    $supplier = Supplier::firstOrCreate(
        ['code' => 'E2E-SUP'],
        ['name' => 'E2E Test Supplier', 'is_active' => true]
    );
    echo "OK Supplier: {$supplier->name} (ID: {$supplier->id})\n";

    $product = Product::firstOrCreate(
        ['sku' => 'E2E-P1'],
        [
            'supplier_id' => $supplier->id,
            'name' => 'Paracetamol 500mg',
            'category' => 'Analgesic',
            'unit' => 'Box',
            'price' => 50000,
            'is_narcotic' => false,
            'is_active' => true,
        ]
    );

    $narcoticProduct = Product::firstOrCreate(
        ['sku' => 'E2E-N1'],
        [
            'supplier_id' => $supplier->id,
            'name' => 'Morphine 10mg',
            'category' => 'Narcotic',
            'unit' => 'Ampoule',
            'price' => 150000,
            'is_narcotic' => true,
            'is_active' => true,
        ]
    );
    echo "OK Products prepared\n";

    $healthcareUser = User::firstOrCreate(
        ['email' => 'healthcare.e2e@test.local'],
        [
            'name' => 'Healthcare E2E',
            'password' => 'password',
            'organization_id' => $organization->id,
            'is_active' => true,
        ]
    );
    $healthcareUser->syncRoles(['Healthcare User']);

    $approverUser = User::firstOrCreate(
        ['email' => 'approver.e2e@test.local'],
        [
            'name' => 'Approver E2E',
            'password' => 'password',
            'organization_id' => $organization->id,
            'is_active' => true,
        ]
    );
    $approverUser->syncRoles(['Approver']);

    $financeUser = User::firstOrCreate(
        ['email' => 'finance.e2e@test.local'],
        [
            'name' => 'Finance E2E',
            'password' => 'password',
            'organization_id' => $organization->id,
            'is_active' => true,
        ]
    );
    $financeUser->syncRoles(['Finance']);
    echo "OK Users prepared (Healthcare, Approver, Finance)\n\n";

    Auth::login($healthcareUser);
    $poService = app(POService::class);

    $po = $poService->createPO($healthcareUser, [
        'supplier_id' => $supplier->id,
        'requested_date' => now()->addDays(3)->toDateString(),
        'expected_delivery_date' => now()->addDays(7)->toDateString(),
        'notes' => 'E2E purchase order',
    ]);
    echo "OK PO created: {$po->po_number} ({$po->status})\n";

    $po = $poService->syncItems($po, [
        ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 50000],
        ['product_id' => $narcoticProduct->id, 'quantity' => 2, 'unit_price' => 150000],
    ]);
    echo "OK PO items synced. Total: {$po->total_amount}\n";

    $po = $poService->submitPO($po, $healthcareUser);
    echo "OK PO submitted: {$po->status}\n";

    Auth::login($approverUser);
    $approvalService = app(ApprovalService::class);

    $approvalService->process($po, $approverUser, 1, 'approved', 'Level 1 approved');
    $po->refresh();
    echo "OK Approval level 1 processed. Status: {$po->status}\n";

    if ($po->requires_extra_approval) {
        $approvalService->process($po, $approverUser, 2, 'approved', 'Level 2 approved');
        $po->refresh();
        echo "OK Approval level 2 processed. Status: {$po->status}\n";
    }

    $deliveryService = app(DeliveryService::class);
    $po = $deliveryService->markShipped($po, $approverUser);
    echo "OK PO shipped: {$po->status}\n";

    $po = $deliveryService->markDelivered($po, $approverUser);
    echo "OK PO delivered: {$po->status}\n";

    Auth::login($healthcareUser);
    $po->load('items');

    $grItems = [];
    foreach ($po->items as $item) {
        $grItems[] = [
            'purchase_order_item_id' => $item->id,
            'quantity_received' => $item->quantity,
            'condition' => 'Good',
        ];
    }

    $goodsReceiptService = app(GoodsReceiptService::class);
    $gr = $goodsReceiptService->confirmReceipt($po->fresh(), $healthcareUser, $grItems, 'All goods received');
    $po->refresh();
    echo "OK Goods receipt confirmed: {$gr->gr_number} ({$gr->status})\n";
    echo "OK PO final status after receipt: {$po->status}\n";

    Auth::login($financeUser);
    $invoiceService = app(InvoiceService::class);
    $issued = $invoiceService->issueInvoice($po, $gr, $financeUser, now()->addDays(30)->toDateString());
    $customerInvoice = $issued['customer_invoice'];
    $supplierInvoice = $issued['supplier_invoice'];
    echo "OK Invoices issued: SI={$supplierInvoice->invoice_number}, CI={$customerInvoice->invoice_number}\n";

    Auth::login($healthcareUser);
    $customerInvoice = $invoiceService->confirmPayment($customerInvoice, $healthcareUser, [
        'payment_reference' => 'E2E-REF-' . now()->timestamp,
        'paid_amount' => $customerInvoice->total_amount,
    ]);
    echo "OK Customer payment submitted: {$customerInvoice->status}\n";

    Auth::login($financeUser);
    $customerInvoice = $invoiceService->verifyPayment($customerInvoice, $financeUser);
    echo "OK Customer payment verified: {$customerInvoice->status}\n";

    $auditCount = AuditLog::where('entity_type', PurchaseOrder::class)
        ->where('entity_id', $po->id)
        ->count();

    echo "\nAudit logs for PO: {$auditCount}\n";

    if (! $po->isCompleted()) {
        throw new RuntimeException('PO did not reach completed status.');
    }

    if (! $customerInvoice->isPaid()) {
        throw new RuntimeException('Customer invoice did not reach paid status.');
    }

    echo "\n=== E2E TEST PASSED ===\n";
    exit(0);
} catch (Throwable $e) {
    echo "\n=== E2E TEST FAILED ===\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

