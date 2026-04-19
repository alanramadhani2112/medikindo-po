<?php

use App\Models\GoodsReceipt;
use App\Models\SupplierInvoice;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$grNumber = 'GR-20260419-0B10A4';
$gr = GoodsReceipt::where('gr_number', $grNumber)->first();

if (!$gr) {
    echo "GR $grNumber not found.\n";
    exit;
}

echo "GR ID: {$gr->id} | Number: {$gr->gr_number} | Status: {$gr->status}\n";
echo "Has Remaining Qty: " . ($gr->hasRemainingQuantity() ? 'Yes' : 'No') . "\n";

$siCount = SupplierInvoice::where('goods_receipt_id', $gr->id)->count();
echo "Associated Supplier Invoices: $siCount\n";

$si = SupplierInvoice::where('goods_receipt_id', $gr->id)->get();
foreach($si as $i) {
    echo "  - SI ID: {$i->id} | Number: {$i->invoice_number} | Status: {$i->status->value}\n";
}

// Check the query logic used in controller
$user = User::where('email', 'alanramadhani21@gmail.com')->first();
echo "Testing as user: {$user->email} (Org ID: {$user->organization_id})\n";

$query = GoodsReceipt::with(['purchaseOrder.organization', 'items.product', 'items.purchaseOrderItem'])
    ->where('status', 'completed')
    ->whereHas('purchaseOrder', function($q) {
        $q->whereNotNull('organization_id');
    });

if (! $user->hasRole('Super Admin')) {
    $query->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
}

$results = $query->get()->filter(function($item) use ($gr) {
    $hasSI = SupplierInvoice::where('goods_receipt_id', $item->id)->exists();
    $match = ($item->id == $gr->id);
    if ($match) {
        echo "Match found in query. Has SI: " . ($hasSI ? 'Yes' : 'No') . " | Has Remaining AR: " . ($item->hasRemainingArQuantity() ? 'Yes' : 'No') . "\n";
    }
    return $hasSI && $item->hasRemainingArQuantity();
});

echo "Final Count for Customer Invoice selection: " . $results->count() . "\n";
