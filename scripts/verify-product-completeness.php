<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== VERIFYING PRODUCT DATA COMPLETENESS ===\n\n";

$total = Product::count();

echo "Total Products: {$total}\n";
echo str_repeat("=", 80) . "\n\n";

// Check each field
$fields = [
    'name' => 'Nama Produk',
    'sku' => 'SKU',
    'category' => 'Kategori',
    'unit' => 'Satuan',
    'price' => 'Harga',
    'cost_price' => 'Harga Beli',
    'selling_price' => 'Harga Jual',
];

echo "FIELD COMPLETENESS:\n";
echo str_repeat("-", 80) . "\n";

foreach ($fields as $field => $label) {
    $filled = Product::whereNotNull($field)
        ->where($field, '!=', '')
        ->where($field, '!=', 0)
        ->count();
    
    $percentage = ($filled / $total) * 100;
    $status = $filled === $total ? '✅' : '⚠️';
    
    echo sprintf("%s %-20s: %d/%d (%.1f%%)\n", $status, $label, $filled, $total, $percentage);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "CATEGORY DISTRIBUTION:\n";
echo str_repeat("-", 80) . "\n";

$categories = Product::selectRaw('category, COUNT(*) as total')
    ->groupBy('category')
    ->orderBy('total', 'desc')
    ->get();

foreach ($categories as $cat) {
    echo sprintf("%-30s: %2d products\n", $cat->category, $cat->total);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "UNIT DISTRIBUTION:\n";
echo str_repeat("-", 80) . "\n";

$units = Product::selectRaw('unit, COUNT(*) as total')
    ->groupBy('unit')
    ->orderBy('total', 'desc')
    ->get();

foreach ($units as $unit) {
    echo sprintf("%-20s: %2d products\n", $unit->unit, $unit->total);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "SAMPLE PRODUCTS (Complete Data):\n";
echo str_repeat("-", 80) . "\n";

$samples = Product::limit(5)->get();

foreach ($samples as $product) {
    echo "\nProduct: {$product->name}\n";
    echo "  SKU: {$product->sku}\n";
    echo "  Kategori: {$product->category}\n";
    echo "  Satuan: {$product->unit}\n";
    echo "  Harga: Rp " . number_format($product->price, 0, ',', '.') . "\n";
    echo "  Harga Beli: Rp " . number_format($product->cost_price, 0, ',', '.') . "\n";
    echo "  Harga Jual: Rp " . number_format($product->selling_price, 0, ',', '.') . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";

// Final check
$complete = Product::whereNotNull('name')
    ->whereNotNull('sku')
    ->whereNotNull('category')
    ->whereNotNull('unit')
    ->where('category', '!=', '')
    ->where('unit', '!=', '')
    ->where('price', '>', 0)
    ->where('cost_price', '>', 0)
    ->where('selling_price', '>', 0)
    ->count();

echo "FINAL RESULT:\n";
echo str_repeat("-", 80) . "\n";
if ($complete === $total) {
    echo "✅ SUCCESS! All {$total} products have complete data!\n";
    echo "   - Name: ✅\n";
    echo "   - SKU: ✅\n";
    echo "   - Category: ✅\n";
    echo "   - Unit: ✅\n";
    echo "   - Price: ✅\n";
    echo "   - Cost Price: ✅\n";
    echo "   - Selling Price: ✅\n";
} else {
    echo "⚠️ WARNING: {$complete}/{$total} products have complete data.\n";
    $incomplete = $total - $complete;
    echo "   {$incomplete} products need attention.\n";
}
echo str_repeat("=", 80) . "\n";
