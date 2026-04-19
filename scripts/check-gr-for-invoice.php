<?php

use App\Models\GoodsReceipt;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('email', 'alanramadhani21@gmail.com')->first(); // Admin user from E2E
if (!$user) {
    echo "User not found\n";
    exit;
}

echo "Testing for user: " . $user->name . " (Org ID: " . $user->organization_id . ")\n";
echo "Is Super Admin: " . ($user->hasRole('Super Admin') ? 'Yes' : 'No') . "\n";

$query = GoodsReceipt::with(['purchaseOrder.supplier', 'items.supplierInvoiceLineItems'])
    ->where('status', 'completed')
    ->whereHas('purchaseOrder', function($q) {
        $q->whereNotNull('supplier_id');
    });

if (! $user->hasRole('Super Admin')) {
    $query->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
}

$allGrs = $query->get();

echo "Found " . $allGrs->count() . " completed GRs for this user's criteria.\n";

foreach ($allGrs as $gr) {
    $hasRemaining = $gr->hasRemainingQuantity();
    echo "GR: {$gr->gr_number} | Status: {$gr->status} | Has Remaining: " . ($hasRemaining ? 'Yes' : 'No') . "\n";
    foreach ($gr->items as $item) {
        echo "  - Item ID: {$item->id} | Qty Received: {$item->quantity_received} | Invoiced: {$item->invoiced_quantity} | Remaining: {$item->remaining_quantity}\n";
    }
}
