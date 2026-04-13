<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\PurchaseOrder;

echo "=== TESTING APPROVAL QUERY ===\n\n";

// Get approver users
$approvers = User::whereHas('roles', function($q) {
    $q->whereIn('name', ['Super Admin', 'Approver', 'Admin Approver']);
})->with('roles')->get();

echo "Testing with " . $approvers->count() . " approver(s):\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($approvers as $user) {
    echo "User: {$user->name}\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Organization ID: " . ($user->organization_id ?? 'NULL') . "\n";
    echo "\n";
    
    // Simulate the EXACT query from ApprovalWebController
    $query = PurchaseOrder::with(['organization', 'supplier', 'creator', 'approvals.approver']);
    
    // Apply the NEW access control logic
    if (! $user->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
        $query->where('organization_id', $user->organization_id);
        echo "Filter Applied: WHERE organization_id = {$user->organization_id}\n";
    } else {
        echo "Filter Applied: NONE (can see all POs)\n";
    }
    
    // Filter by status (pending tab)
    $query->where('status', PurchaseOrder::STATUS_SUBMITTED);
    
    $pendingApprovals = $query->get();
    
    echo "Result: Found {$pendingApprovals->count()} submitted PO(s)\n";
    
    if ($pendingApprovals->isNotEmpty()) {
        echo "✅ SUCCESS - User CAN see submitted POs:\n";
        foreach ($pendingApprovals as $po) {
            echo "   - {$po->po_number} (Org: {$po->organization_id})\n";
            
            // Check approval records
            $pendingApprovalRecords = $po->approvals->where('status', 'pending');
            echo "     Pending Approvals: {$pendingApprovalRecords->count()}\n";
        }
    } else {
        echo "❌ FAIL - User CANNOT see any submitted POs\n";
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo str_repeat("=", 80) . "\n";
echo "SUMMARY:\n";
echo str_repeat("-", 80) . "\n";

$submittedPOs = PurchaseOrder::where('status', 'submitted')->count();
echo "Total Submitted POs in database: {$submittedPOs}\n";

if ($submittedPOs > 0) {
    echo "\n";
    echo "Expected Behavior:\n";
    echo "✅ Super Admin should see all {$submittedPOs} PO(s)\n";
    echo "✅ Approver should see all {$submittedPOs} PO(s)\n";
    echo "✅ Admin Approver should see all {$submittedPOs} PO(s)\n";
}

echo str_repeat("=", 80) . "\n";
