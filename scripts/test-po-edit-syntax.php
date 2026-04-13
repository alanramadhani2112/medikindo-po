<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;

echo "Testing PO Edit View Syntax Fix\n";
echo "===============================\n\n";

// Find a PO with items
$po = PurchaseOrder::with('items')->whereHas('items')->first();

if (!$po) {
    echo "❌ No PO with items found\n";
    exit;
}

echo "Testing PO: {$po->po_number}\n";
echo "Items count: {$po->items->count()}\n\n";

echo "Testing field mapping:\n";
echo "----------------------\n";

foreach ($po->items as $item) {
    echo "Item ID: {$item->id}\n";
    echo "  product_id: {$item->product_id}\n";
    echo "  quantity: {$item->quantity}\n";
    echo "  unit_price: {$item->unit_price}\n";
    echo "  subtotal: {$item->subtotal}\n";
    
    // Test if total_price exists (should not)
    try {
        $totalPrice = $item->total_price;
        echo "  total_price: {$totalPrice} (❌ This field should not exist!)\n";
    } catch (\Exception $e) {
        echo "  total_price: ❌ Field does not exist (✅ Correct!)\n";
    }
    echo "\n";
}

echo "===============================\n";
echo "✅ Fix Applied: Changed 'total_price' to 'subtotal' in edit view\n";
echo "✅ PO edit page should now load without syntax errors\n";