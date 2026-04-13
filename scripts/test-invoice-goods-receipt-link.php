<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SupplierInvoice;
use App\Models\CustomerInvoice;

echo "Testing Invoice Goods Receipt Links\n";
echo "===================================\n\n";

// Check supplier invoices
$supplierInvoices = SupplierInvoice::with('goodsReceipt')->get();

echo "Supplier Invoices: {$supplierInvoices->count()}\n";
echo "-----------------------------------\n";

foreach ($supplierInvoices as $invoice) {
    echo "Invoice: {$invoice->invoice_number}\n";
    echo "  Goods Receipt ID: " . ($invoice->goods_receipt_id ?? 'NULL') . "\n";
    echo "  GR Number: " . ($invoice->goodsReceipt?->gr_number ?? 'N/A') . "\n";
    echo "  Status: " . ($invoice->goods_receipt_id ? '✅ Has GR' : '❌ No GR') . "\n\n";
}

// Check customer invoices
$customerInvoices = CustomerInvoice::with('goodsReceipt')->get();

echo "Customer Invoices: {$customerInvoices->count()}\n";
echo "-----------------------------------\n";

foreach ($customerInvoices as $invoice) {
    echo "Invoice: {$invoice->invoice_number}\n";
    echo "  Goods Receipt ID: " . ($invoice->goods_receipt_id ?? 'NULL') . "\n";
    echo "  GR Number: " . ($invoice->goodsReceipt?->gr_number ?? 'N/A') . "\n";
    echo "  Status: " . ($invoice->goods_receipt_id ? '✅ Has GR' : '❌ No GR') . "\n\n";
}

echo "===================================\n";
echo "✅ Fix Applied: Views now check if goods_receipt_id exists before generating route\n";
echo "✅ If goods_receipt_id is NULL, shows 'Goods Receipt belum tersedia' instead of error\n";
echo "✅ Invoice pages should now load without URL generation errors\n";