<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Organization;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use App\Services\POService;

echo "Creating Test PO for Approval\n";
echo "==============================\n\n";

$organization = Organization::first();
$supplier = Supplier::first();
$product = Product::where('is_active', true)->first();
$creator = User::whereHas('roles', function($q) {
    $q->where('name', 'Healthcare User');
})->first();

if (!$organization || !$supplier || !$product || !$creator) {
    echo "❌ Missing required data (organization, supplier, product, or user)\n";
    exit;
}

echo "Creating PO...\n";
echo "Organization: {$organization->name}\n";
echo "Supplier: {$supplier->name}\n";
echo "Creator: {$creator->name}\n\n";

// Create PO
$po = PurchaseOrder::create([
    'po_number' => 'PO-' . date('Ymd-His'),
    'organization_id' => $organization->id,
    'supplier_id' => $supplier->id,
    'created_by' => $creator->id,
    'status' => PurchaseOrder::STATUS_DRAFT,
    'has_narcotics' => false,
    'requires_extra_approval' => false,
    'total_amount' => 0,
    'requested_date' => now(),
    'expected_delivery_date' => now()->addDays(7),
    'notes' => 'Test PO for approval testing',
]);

echo "✅ PO Created: {$po->po_number}\n\n";

// Add item
echo "Adding item...\n";
$item = PurchaseOrderItem::create([
    'purchase_order_id' => $po->id,
    'product_id' => $product->id,
    'quantity' => 10,
    'unit_price' => $product->selling_price,
    'subtotal' => 10 * $product->selling_price,
]);

echo "✅ Item Added: {$product->name} x 10\n\n";

// Recalculate totals
$po->recalculateTotals();
$po->save();

echo "Total Amount: Rp " . number_format($po->total_amount, 0, ',', '.') . "\n\n";

// Submit PO
echo "Submitting PO...\n";
$poService = app(POService::class);

try {
    $poService->submitPO($po, $creator);
    $po->refresh();
    
    echo "✅ PO Submitted Successfully!\n";
    echo "Status: {$po->status}\n";
    echo "Approvals created: {$po->approvals->count()}\n\n";
    
    foreach ($po->approvals as $approval) {
        echo "  - Level {$approval->level}: {$approval->status}\n";
    }
    
    echo "\n✅ TEST PO READY FOR APPROVAL!\n";
    echo "PO Number: {$po->po_number}\n";
    echo "Now login as Super Admin or Approver and check /approvals page\n";
    
} catch (\Exception $e) {
    echo "❌ Error submitting PO: {$e->getMessage()}\n";
}
