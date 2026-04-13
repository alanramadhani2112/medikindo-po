<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Schema;

echo "PurchaseOrderItem Schema Check\n";
echo "==============================\n\n";

// Get table columns
$columns = Schema::getColumnListing('purchase_order_items');

echo "Database Columns:\n";
echo "-----------------\n";
foreach ($columns as $column) {
    echo "- {$column}\n";
}

echo "\nModel Fillable Fields:\n";
echo "----------------------\n";
$item = new PurchaseOrderItem();
foreach ($item->getFillable() as $field) {
    echo "- {$field}\n";
}

echo "\nTesting with actual data:\n";
echo "-------------------------\n";
$firstItem = PurchaseOrderItem::first();
if ($firstItem) {
    echo "Item ID: {$firstItem->id}\n";
    $attributes = $firstItem->getAttributes();
    foreach ($attributes as $key => $value) {
        echo "  {$key}: " . ($value ?? 'NULL') . "\n";
    }
} else {
    echo "No items found in database\n";
}

echo "\n==============================\n";
echo "✅ This confirms which fields actually exist in the database\n";