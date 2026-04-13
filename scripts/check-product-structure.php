<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== CHECKING PRODUCT DATA STRUCTURE ===\n\n";

// Get first product to see structure
$product = Product::first();

if ($product) {
    echo "Sample Product: {$product->name}\n";
    echo str_repeat("-", 80) . "\n";
    
    $attributes = $product->getAttributes();
    foreach ($attributes as $key => $value) {
        $displayValue = $value ?? 'NULL';
        if (is_string($displayValue) && strlen($displayValue) > 50) {
            $displayValue = substr($displayValue, 0, 50) . '...';
        }
        echo sprintf("%-20s: %s\n", $key, $displayValue);
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "CHECKING MISSING DATA:\n";
echo str_repeat("-", 80) . "\n";

$total = Product::count();
$missingUnit = Product::whereNull('unit')->orWhere('unit', '')->count();
$missingCategory = Product::whereNull('category')->orWhere('category', '')->count();

echo "Total Products: {$total}\n";
echo "Missing Unit: {$missingUnit}\n";
echo "Missing Category: {$missingCategory}\n";

if ($missingUnit > 0) {
    echo "\nProducts without unit:\n";
    Product::whereNull('unit')->orWhere('unit', '')->limit(5)->get()->each(function($p) {
        echo "  - {$p->name} (SKU: {$p->sku})\n";
    });
}

if ($missingCategory > 0) {
    echo "\nProducts without category:\n";
    Product::whereNull('category')->orWhere('category', '')->limit(5)->get()->each(function($p) {
        echo "  - {$p->name} (SKU: {$p->sku})\n";
    });
}

echo "\n" . str_repeat("=", 80) . "\n";
