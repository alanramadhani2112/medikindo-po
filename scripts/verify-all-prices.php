<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== VERIFYING ALL PRICE COLUMNS ===\n\n";

// Check sample products
echo "Sample Products (first 5):\n";
echo str_repeat("-", 80) . "\n";
$products = Product::select('name', 'price', 'cost_price', 'selling_price')->limit(5)->get();

foreach ($products as $product) {
    echo "Product: {$product->name}\n";
    echo "  Price: Rp " . number_format($product->price, 0, ',', '.') . "\n";
    echo "  Cost Price: Rp " . number_format($product->cost_price, 0, ',', '.') . "\n";
    echo "  Selling Price: Rp " . number_format($product->selling_price, 0, ',', '.') . "\n";
    echo "\n";
}

// Statistics
echo str_repeat("=", 80) . "\n";
echo "STATISTICS:\n";
echo str_repeat("-", 80) . "\n";
echo "Total Products: " . Product::count() . "\n";
echo "\n";

echo "Price Column:\n";
echo "  - Products with price > 0: " . Product::where('price', '>', 0)->count() . "\n";
echo "  - Products with price = 0: " . Product::where('price', '=', 0)->count() . "\n";
echo "\n";

echo "Cost Price Column:\n";
echo "  - Products with cost_price > 0: " . Product::where('cost_price', '>', 0)->count() . "\n";
echo "  - Products with cost_price = 0: " . Product::where('cost_price', '=', 0)->count() . "\n";
echo "\n";

echo "Selling Price Column:\n";
echo "  - Products with selling_price > 0: " . Product::where('selling_price', '>', 0)->count() . "\n";
echo "  - Products with selling_price = 0: " . Product::where('selling_price', '=', 0)->count() . "\n";
echo "\n";

echo str_repeat("=", 80) . "\n";
echo "RESULT: ";
$allFilled = Product::where('price', '>', 0)
    ->where('cost_price', '>', 0)
    ->where('selling_price', '>', 0)
    ->count();

if ($allFilled === Product::count()) {
    echo "✅ SUCCESS! All products have complete price data.\n";
} else {
    echo "⚠️ WARNING! Some products are missing price data.\n";
}
echo str_repeat("=", 80) . "\n";
