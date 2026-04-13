<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\Approval;

echo "=== CHECKING PO APPROVAL RECORDS ===\n\n";

// Get submitted PO
$po = PurchaseOrder::where('status', 'submitted')->latest()->first();

if (!$po) {
    echo "❌ No submitted PO found!\n";
    exit(1);
}

echo "PO Details:\n";
echo str_repeat("-", 80) . "\n";
echo "PO Number: {$po->po_number}\n";
echo "Status: {$po->status}\n";
echo "Organization ID: " . ($po->organization_id ?? 'NULL') . "\n";
echo "Submitted At: " . ($po->submitted_at ?? 'NULL') . "\n";
echo "Requires Extra Approval: " . ($po->requires_extra_approval ? 'Yes' : 'No') . "\n";
echo "Has Narcotics: " . ($po->has_narcotics ? 'Yes' : 'No') . "\n";
echo "\n";

// Check approval records
$approvals = Approval::where('purchase_order_id', $po->id)->get();

echo "Approval Records:\n";
echo str_repeat("=", 80) . "\n";

if ($approvals->isEmpty()) {
    echo "❌ NO APPROVAL RECORDS FOUND!\n";
    echo "\n";
    echo "PROBLEM: Approval records were not created when PO was submitted.\n";
    echo "\n";
    echo "EXPECTED:\n";
    echo "- Level 1 (Standard) approval should exist\n";
    if ($po->requires_extra_approval) {
        echo "- Level 2 (Narcotics) approval should exist\n";
    }
    echo "\n";
    echo "SOLUTION: Run FixMissingApprovals seeder to create missing approval records.\n";
} else {
    echo "✅ Found {$approvals->count()} approval record(s)\n\n";
    
    foreach ($approvals as $approval) {
        echo "Approval ID: {$approval->id}\n";
        echo "Level: {$approval->level} (" . ($approval->level == 1 ? 'Standard' : 'Narcotics') . ")\n";
        echo "Status: {$approval->status}\n";
        echo "Approver ID: " . ($approval->approver_id ?? 'NULL') . "\n";
        if ($approval->approver) {
            echo "Approver: {$approval->approver->name}\n";
        }
        echo "Actioned At: " . ($approval->actioned_at ?? 'NULL') . "\n";
        echo "Notes: " . ($approval->notes ?? 'NULL') . "\n";
        echo str_repeat("-", 80) . "\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "DIAGNOSIS:\n";
echo str_repeat("-", 80) . "\n";

if ($approvals->isEmpty()) {
    echo "❌ ISSUE: No approval records exist for this PO\n";
    echo "   This means the PO will NOT appear in the approval queue\n";
    echo "\n";
    echo "POSSIBLE CAUSES:\n";
    echo "1. initializeApprovals() was not called during submit\n";
    echo "2. Database transaction rolled back\n";
    echo "3. Error occurred during approval creation\n";
    echo "\n";
    echo "FIX: Run the FixMissingApprovals seeder:\n";
    echo "   php artisan db:seed --class=FixMissingApprovals\n";
} else {
    $pendingCount = $approvals->where('status', 'pending')->count();
    if ($pendingCount > 0) {
        echo "✅ GOOD: {$pendingCount} pending approval(s) exist\n";
        echo "   This PO SHOULD appear in the approval queue\n";
    } else {
        echo "⚠️ WARNING: No pending approvals (all processed)\n";
    }
}

echo str_repeat("=", 80) . "\n";
