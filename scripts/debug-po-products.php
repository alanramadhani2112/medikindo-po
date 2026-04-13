<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Supplier;

echo "=== DEBUGGING PO PRODUCTS DATA ===\n\n";

// Get suppliers with products (same as controller)
$suppliers = Supplier::with(['products' => function($query) {
    $query->where('is_active', true)->orderBy('name');
}])
->where('is_active', true)
->orderBy('name')
->get();

echo "Total Suppliers: " . $suppliers->count() . "\n";
echo str_repeat("-", 80) . "\n\n";

foreach ($suppliers as $supplier) {
    echo "Supplier: {$supplier->name} (ID: {$supplier->id})\n";
    echo "Products Count: " . $supplier->products->count() . "\n";
    
    if ($supplier->products->count() > 0) {
        echo "Sample Products:\n";
        foreach ($supplier->products->take(3) as $product) {
            echo "  - {$product->name} (SKU: {$product->sku})\n";
            echo "    ID: {$product->id}\n";
            echo "    Price: {$product->price}\n";
            echo "    Cost Price: {$product->cost_price}\n";
            echo "    Selling Price: {$product->selling_price}\n";
            echo "    Is Active: " . ($product->is_active ? 'Yes' : 'No') . "\n";
            echo "\n";
        }
        
        // Test JSON encoding (same as in blade)
        $jsonData = json_encode($supplier->products);
        echo "JSON Data Length: " . strlen($jsonData) . " characters\n";
        echo "JSON Valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON Error: " . json_last_error_msg() . "\n";
        }
    }
    
    echo str_repeat("-", 80) . "\n\n";
}

// Test specific supplier that user might be using
echo "=== TESTING SPECIFIC SUPPLIER ===\n";
$testSupplier = $suppliers->first();
if ($testSupplier) {
    echo "Testing with: {$testSupplier->name}\n";
    echo "Products available: " . $testSupplier->products->count() . "\n";
    
    if ($testSupplier->products->count() > 0) {
        $firstProduct = $testSupplier->products->first();
        echo "\nFirst Product Details:\n";
        echo "  Name: {$firstProduct->name}\n";
        echo "  SKU: {$firstProduct->sku}\n";
        echo "  ID: {$firstProduct->id}\n";
        echo "  Price: {$firstProduct->price}\n";
        echo "  Selling Price: {$firstProduct->selling_price}\n";
        echo "  Expected Unit Price in PO: {$firstProduct->selling_price}\n";
    }
}

echo str_repeat("=", 80) . "\n";