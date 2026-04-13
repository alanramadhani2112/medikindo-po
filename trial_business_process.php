<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CreditLimit;
use App\Models\Organization;
use App\Models\Product;
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

echo ">>> START BUSINESS PROCESS TRIAL <<<\n\n";

try {
    Artisan::call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);

    $organization = Organization::firstOrCreate(
        ['code' => 'TRIAL-ORG'],
        ['name' => 'Trial Organization', 'type' => 'hospital', 'is_active' => true]
    );

    CreditLimit::updateOrCreate(
        ['organization_id' => $organization->id],
        ['max_limit' => 10000000, 'is_active' => true]
    );

    $supplier = Supplier::firstOrCreate(
        ['code' => 'TRIAL-SUP'],
        ['name' => 'Trial Supplier', 'is_active' => true]
    );

    $p1 = Product::updateOrCreate(['sku' => 'TRIAL-P01'], [
        'supplier_id' => $supplier->id,
        'name' => 'Amoxicillin 500mg',
        'category' => 'General Medicine',
        'unit' => 'Box',
        'price' => 45000,
        'is_narcotic' => false,
        'is_active' => true,
    ]);

    $n1 = Product::updateOrCreate(['sku' => 'TRIAL-N01'], [
        'supplier_id' => $supplier->id,
        'name' => 'Codeine 10mg',
        'category' => 'Narcotic',
        'unit' => 'Strip',
        'price' => 120000,
        'is_narcotic' => true,
        'is_active' => true,
    ]);

    $healthcare = User::firstOrCreate(
        ['email' => 'healthcare.trial@test.local'],
        [
            'name' => 'Healthcare Trial',
            'password' => 'password',
            'organization_id' => $organization->id,
            'is_active' => true,
        ]
    );
    $healthcare->syncRoles(['Healthcare User']);

    $approver = User::firstOrCreate(
        ['email' => 'approver.trial@test.local'],
        [
            'name' => 'Approver Trial',
            'password' => 'password',
            'organization_id' => $organization->id,
            'is_active' => true,
        ]
    );
    $approver->syncRoles(['Approver']);

    $finance = User::firstOrCreate(
        ['email' => 'finance.trial@test.local'],
        [
            'name' => 'Finance Trial',
            'password' => 'password',
            'organization_id' => $organization->id,
            'is_active' => true,
        ]
    );
    $finance->syncRoles(['Finance']);

    echo "Data setup complete.\n\n";

    $poService = app(POService::class);
    $approvalService = app(ApprovalService::class);
    $deliveryService = app(DeliveryService::class);
    $goodsReceiptService = app(GoodsReceiptService::class);
    $invoiceService = app(InvoiceService::class);

    Auth::login($healthcare);

    $po = $poService->createPO($healthcare, [
        'supplier_id' => $supplier->id,
        'requested_date' => now()->addDays(5)->toDateString(),
        'expected_delivery_date' => now()->addDays(10)->toDateString(),
        'notes' => 'Business trial PO',
    ]);
    echo "[1] PO created: {$po->po_number} ({$po->status})\n";

    $po = $poService->syncItems($po, [
        ['product_id' => $p1->id, 'quantity' => 10, 'unit_price' => 45000],
        ['product_id' => $n1->id, 'quantity' => 2, 'unit_price' => 120000],
    ]);

    $po = $poService->submitPO($po, $healthcare);
    echo "[2] PO submitted: {$po->status}\n";

    Auth::login($approver);
    $approvalService->process($po, $approver, 1, 'approved', 'Approval level 1');
    $po->refresh();

    if ($po->requires_extra_approval) {
        $approvalService->process($po, $approver, 2, 'approved', 'Approval level 2');
        $po->refresh();
    }
    echo "[3] PO approved: {$po->status}\n";

    // Edge case: delivered without shipped must fail
    try {
        $deliveryService->markDelivered($po->fresh(), $approver);
        throw new RuntimeException('Invalid transition accepted: approved -> delivered');
    } catch (DomainException $e) {
        echo "[4] Invalid transition blocked as expected (approved -> delivered).\n";
    }

    $po = $deliveryService->markShipped($po->fresh(), $approver);
    $po = $deliveryService->markDelivered($po->fresh(), $approver);
    echo "[5] Logistics complete: {$po->status}\n";

    Auth::login($healthcare);
    $po->load('items');

    $firstItem = $po->items->first();
    $secondItem = $po->items->skip(1)->first();

    // Partial receipt first
    $gr1 = $goodsReceiptService->confirmReceipt($po->fresh(), $healthcare, [
        ['purchase_order_item_id' => $firstItem->id, 'quantity_received' => 6, 'condition' => 'Good'],
        ['purchase_order_item_id' => $secondItem->id, 'quantity_received' => 1, 'condition' => 'Good'],
    ], 'Partial receipt');
    $po->refresh();
    echo "[6] Partial GR: {$gr1->gr_number} ({$gr1->status}), PO={$po->status}\n";

    // Final receipt
    $gr2 = $goodsReceiptService->confirmReceipt($po->fresh(), $healthcare, [
        ['purchase_order_item_id' => $firstItem->id, 'quantity_received' => 4, 'condition' => 'Good'],
        ['purchase_order_item_id' => $secondItem->id, 'quantity_received' => 1, 'condition' => 'Good'],
    ], 'Final receipt');
    $po->refresh();
    echo "[7] Final GR: {$gr2->gr_number} ({$gr2->status}), PO={$po->status}\n";

    Auth::login($finance);
    $issued = $invoiceService->issueInvoice($po->fresh(), $gr2, $finance, now()->addDays(30)->toDateString());
    $customerInvoice = $issued['customer_invoice'];
    echo "[8] Invoice issued: {$customerInvoice->invoice_number} ({$customerInvoice->status})\n";

    Auth::login($healthcare);
    $customerInvoice = $invoiceService->confirmPayment($customerInvoice, $healthcare, [
        'payment_reference' => 'TRIAL-REF-' . now()->timestamp,
        'paid_amount' => $customerInvoice->total_amount,
    ]);
    echo "[9] Payment submitted: {$customerInvoice->status}\n";

    Auth::login($finance);
    $customerInvoice = $invoiceService->verifyPayment($customerInvoice, $finance);
    echo "[10] Payment verified: {$customerInvoice->status}\n";

    if (! $po->isCompleted()) {
        throw new RuntimeException('PO did not reach completed state.');
    }

    if (! $customerInvoice->isPaid()) {
        throw new RuntimeException('Invoice did not reach paid state.');
    }

    echo "\n>>> BUSINESS PROCESS TRIAL PASSED <<<\n";
    exit(0);
} catch (Throwable $e) {
    echo "\n>>> BUSINESS PROCESS TRIAL FAILED <<<\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

