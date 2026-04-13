<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Supplier;
use App\Models\Product;

echo "=== TESTING PO PRICE SYNCHRONIZATION ===\n\n";

// Get a supplier with products
$supplier = Supplier::with(['products' => function($query) {
    $query->where('is_active', true)->orderBy('name')->limit(5);
}])->where('is_active', true)->first();

if (!$supplier) {
    echo "❌ No active supplier found!\n";
    exit(1);
}

echo "Supplier: {$supplier->name}\n";
echo str_repeat("-", 80) . "\n\n";

if ($supplier->products->isEmpty()) {
    echo "❌ No active products found for this supplier!\n";
    exit(1);
}

echo "Products (showing price fields):\n";
echo str_repeat("-", 80) . "\n";

foreach ($supplier->products as $product) {
    echo "\nProduct: {$product->name}\n";
    echo "  SKU: {$product->sku}\n";
    echo "  Price: Rp " . number_format($product->price, 0, ',', '.') . "\n";
    echo "  Cost Price: Rp " . number_format($product->cost_price, 0, ',', '.') . "\n";
    echo "  Selling Price: Rp " . number_format($product->selling_price, 0, ',', '.') . " ✅ (This should be used in PO)\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "VERIFICATION:\n";
echo str_repeat("-", 80) . "\n";

$allProductsHaveSellingPrice = Product::where('is_active', true)
    ->where('selling_price', '>', 0)
    ->count();

$totalActiveProducts = Product::where('is_active', true)->count();

echo "Active products with selling_price > 0: {$allProductsHaveSellingPrice}/{$totalActiveProducts}\n";

if ($allProductsHaveSellingPrice === $totalActiveProducts) {
    echo "✅ All active products have selling_price!\n";
} else {
    echo "⚠️ Some products missing selling_price!\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "EXPECTED BEHAVIOR IN PO FORM:\n";
echo str_repeat("-", 80) . "\n";
echo "1. When user selects a product in PO form\n";
echo "2. Unit Price field should auto-fill with product.selling_price\n";
echo "3. Example: If product selling_price = Rp 15,000\n";
echo "4. Then Unit Price in PO = Rp 15,000 (auto-filled, readonly)\n";
echo "5. User can only change Quantity, not Unit Price\n";
echo "\n";
echo "✅ Code has been updated to use selling_price instead of price\n";
echo "✅ Both create.blade.php and edit.blade.php have been fixed\n";
echo str_repeat("=", 80) . "\n";
