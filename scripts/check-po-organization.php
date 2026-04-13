<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\User;

echo "=== CHECKING PO ORGANIZATION DATA ===\n\n";

// Get latest submitted PO
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
echo "Created By: {$po->created_by}\n";
echo "Total Amount: Rp " . number_format($po->total_amount, 0, ',', '.') . "\n";
echo "\n";

// Get creator info
$creator = User::find($po->created_by);
if ($creator) {
    echo "Creator Details:\n";
    echo str_repeat("-", 80) . "\n";
    echo "Name: {$creator->name}\n";
    echo "Email: {$creator->email}\n";
    echo "Organization ID: " . ($creator->organization_id ?? 'NULL') . "\n";
    echo "Roles: " . $creator->roles->pluck('name')->implode(', ') . "\n";
    echo "\n";
}

// Check if organization_id is set
if ($po->organization_id) {
    echo "✅ PO has organization_id: {$po->organization_id}\n";
    
    // Check which users can see this PO
    echo "\nUsers who can see this PO:\n";
    echo str_repeat("-", 80) . "\n";
    
    $approvers = User::whereHas('roles', function($q) {
        $q->whereIn('name', ['Super Admin', 'Admin Approver']);
    })->get();
    
    foreach ($approvers as $approver) {
        $canSee = $approver->hasRole('Super Admin') || $approver->organization_id == $po->organization_id;
        $status = $canSee ? '✅ CAN SEE' : '❌ CANNOT SEE';
        echo "{$status} - {$approver->name} ({$approver->roles->pluck('name')->implode(', ')}) - Org ID: " . ($approver->organization_id ?? 'NULL') . "\n";
    }
} else {
    echo "❌ PO does NOT have organization_id!\n";
    echo "⚠️ This PO will NOT be visible to non-Super Admin users!\n";
    echo "\n";
    echo "SOLUTION: PO must have organization_id set when created.\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
