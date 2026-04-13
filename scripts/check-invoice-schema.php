<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Invoice Schema Check\n";
echo "====================\n\n";

// Check supplier_invoices
echo "supplier_invoices columns:\n";
$columns = Schema::getColumnListing('supplier_invoices');
foreach ($columns as $col) {
    echo "  - {$col}\n";
}

echo "\nsupplier_invoice_line_items columns:\n";
$columns = Schema::getColumnListing('supplier_invoice_line_items');
foreach ($columns as $col) {
    echo "  - {$col}\n";
}

echo "\ngoods_receipt_items columns:\n";
$columns = Schema::getColumnListing('goods_receipt_items');
foreach ($columns as $col) {
    echo "  - {$col}\n";
}
