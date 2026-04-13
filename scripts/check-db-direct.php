<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Direct Database Query\n";
echo "=====================\n\n";

$result = DB::table('purchase_orders')
    ->whereNull('deleted_at')
    ->get();

echo "Total POs in database: {$result->count()}\n\n";

foreach ($result as $po) {
    echo "PO: {$po->po_number}\n";
    echo "  Status: {$po->status}\n";
    echo "  Organization ID: {$po->organization_id}\n";
    echo "  Deleted At: " . ($po->deleted_at ?? 'NULL') . "\n\n";
}

$submitted = DB::table('purchase_orders')
    ->where('status', 'submitted')
    ->whereNull('deleted_at')
    ->get();

echo "Submitted POs: {$submitted->count()}\n";
if ($submitted->count() > 0) {
    foreach ($submitted as $po) {
        echo "  - {$po->po_number} (Org: {$po->organization_id})\n";
    }
}
