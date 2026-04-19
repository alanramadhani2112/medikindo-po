<?php
use App\Models\CustomerInvoice;
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$inv = CustomerInvoice::find(8);
if ($inv) {
    echo "ID: " . $inv->id . "\n";
    echo "Surcharge: " . $inv->surcharge . "\n";
    echo "Total Amount: " . $inv->total_amount . "\n";
} else {
    echo "Invoice not found\n";
}
