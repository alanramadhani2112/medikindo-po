<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Supplier;
use App\Models\Product;

echo "Checking Products and Suppliers...\n\n";

// Check total products
$totalProducts = Product::count();
echo "Total Products in DB: {$totalProducts}\n";

// Check active products
$activeProducts = Product::where('is_active', true)->count();
echo "Active Products: {$activeProducts}\n\n";

// Check suppliers with products
$suppliers = Supplier::with('products')->where('is_active', true)->get();
echo "Total Active Suppliers: {$suppliers->count()}\n\n";

foreach ($suppliers as $supplier) {
    $productCount = $supplier->products->count();
    echo "Supplier: {$supplier->name} ({$supplier->code})\n";
    echo "  Products: {$productCount}\n";
    
    if ($productCount > 0) {
        $firstProduct = $supplier->products->first();
        echo "  First Product: {$firstProduct->name} - Rp " . number_format($firstProduct->price, 0, ',', '.') . "\n";
    } else {
        echo "  ⚠️  NO PRODUCTS!\n";
    }
    echo "\n";
}

echo "--- Check Complete ---\n";
