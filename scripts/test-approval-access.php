<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\PurchaseOrder;

echo "=== TESTING APPROVAL ACCESS AFTER FIX ===\n\n";

// Get submitted PO
$po = PurchaseOrder::where('status', 'submitted')->first();

if (!$po) {
    echo "❌ No submitted PO found!\n";
    exit(1);
}

echo "Testing PO: {$po->po_number}\n";
echo "Organization ID: " . ($po->organization_id ?? 'NULL') . "\n";
echo "Status: {$po->status}\n";
echo "\n" . str_repeat("=", 80) . "\n\n";

// Get all users
$users = User::with('roles')->get();

foreach ($users as $user) {
    if ($user->roles->isEmpty()) {
        continue; // Skip users without roles
    }
    
    echo "User: {$user->name}\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Organization ID: " . ($user->organization_id ?? 'NULL') . "\n";
    
    // Simulate approval query
    $query = PurchaseOrder::query();
    
    // Apply the NEW filter logic
    if (! $user->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
        $query->where('organization_id', $user->organization_id);
    }
    
    $query->where('status', 'submitted');
    
    $canSee = $query->where('id', $po->id)->exists();
    
    $status = $canSee ? '✅ CAN SEE' : '❌ CANNOT SEE';
    echo "Result: {$status}\n";
    
    // Explain why
    if ($user->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
        echo "Reason: User has approver role - can see all POs\n";
    } else {
        if ($user->organization_id == $po->organization_id) {
            echo "Reason: Same organization\n";
        } else {
            echo "Reason: Different organization (User: {$user->organization_id}, PO: {$po->organization_id})\n";
        }
    }
    
    echo str_repeat("-", 80) . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "EXPECTED RESULTS:\n";
echo "✅ Super Admin (Alan) - Should see PO\n";
echo "✅ Approver (Siti) - Should see PO\n";
echo "✅ Healthcare User (Budi) - Should see PO (same org)\n";
echo "❌ Finance (Ahmad) - Should NOT see PO (different context)\n";
echo str_repeat("=", 80) . "\n";
