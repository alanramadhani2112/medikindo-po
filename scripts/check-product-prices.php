<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "Checking Product Prices...\n\n";

// Get first 10 products
$products = Product::limit(10)->get();

foreach ($products as $product) {
    echo "Product: {$product->name}\n";
    echo "  SKU: {$product->sku}\n";
    echo "  Price: Rp " . number_format($product->price, 0, ',', '.') . "\n";
    echo "  Cost Price: Rp " . number_format($product->cost_price, 0, ',', '.') . "\n";
    echo "  Selling Price: Rp " . number_format($product->selling_price, 0, ',', '.') . "\n";
    echo "\n";
}

echo "--- Statistics ---\n";
echo "Total Products: " . Product::count() . "\n";
echo "Products with price > 0: " . Product::where('price', '>', 0)->count() . "\n";
echo "Products with price = 0: " . Product::where('price', '=', 0)->count() . "\n";
echo "Min Price: Rp " . number_format(Product::min('price'), 0, ',', '.') . "\n";
echo "Max Price: Rp " . number_format(Product::max('price'), 0, ',', '.') . "\n";
echo "Avg Price: Rp " . number_format(Product::avg('price'), 0, ',', '.') . "\n";

echo "\n--- Check Complete ---\n";
