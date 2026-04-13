<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;

echo "Checking PO Status...\n";
echo "====================\n\n";

$allPOs = PurchaseOrder::with(['organization', 'approvals'])->get();

echo "Total POs in database: {$allPOs->count()}\n\n";

foreach ($allPOs as $po) {
    echo "PO: {$po->po_number}\n";
    echo "  Status: {$po->status}\n";
    echo "  Organization ID: " . ($po->organization_id ?? 'NULL') . "\n";
    echo "  Organization Name: " . ($po->organization->name ?? 'NULL') . "\n";
    echo "  Approvals: {$po->approvals->count()}\n";
    
    if ($po->approvals->count() > 0) {
        foreach ($po->approvals as $approval) {
            echo "    - Level {$approval->level}: {$approval->status}\n";
        }
    }
    echo "\n";
}

$submittedPOs = PurchaseOrder::where('status', 'submitted')->get();
echo "Submitted POs: {$submittedPOs->count()}\n";

if ($submittedPOs->count() === 0) {
    echo "\n❌ NO SUBMITTED POs FOUND!\n";
    echo "This explains why the approval page is empty.\n";
    echo "\nPossible reasons:\n";
    echo "1. POs were approved/rejected and status changed\n";
    echo "2. POs were never submitted\n";
    echo "3. Database was reset\n";
}
