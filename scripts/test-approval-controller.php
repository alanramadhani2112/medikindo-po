<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\ApprovalWebController;
use App\Services\ApprovalService;

echo "=== TESTING APPROVAL CONTROLLER ===\n\n";

// Get approver user
$approver = User::whereHas('roles', function($q) {
    $q->where('name', 'Approver');
})->first();

if (!$approver) {
    echo "❌ No Approver user found!\n";
    exit(1);
}

echo "Testing with user: {$approver->name}\n";
echo "Roles: " . $approver->roles->pluck('name')->implode(', ') . "\n";
echo "\n" . str_repeat("=", 80) . "\n\n";

// Create mock request
$request = Request::create('/approvals', 'GET', ['tab' => 'pending']);
$request->setUserResolver(function () use ($approver) {
    return $approver;
});

// Create controller instance
$approvalService = app(ApprovalService::class);
$controller = new ApprovalWebController($approvalService);

try {
    // Call index method
    $response = $controller->index($request);
    
    // Get view data
    $viewData = $response->getData();
    
    echo "Controller Response:\n";
    echo str_repeat("-", 80) . "\n";
    echo "View: " . $response->name() . "\n";
    echo "\n";
    
    echo "Data passed to view:\n";
    echo "- pendingApprovals count: " . $viewData['pendingApprovals']->count() . "\n";
    echo "- counts: " . json_encode($viewData['counts']) . "\n";
    echo "- tab: " . $viewData['tab'] . "\n";
    echo "\n";
    
    if ($viewData['pendingApprovals']->count() > 0) {
        echo "✅ SUCCESS - Controller returns POs:\n";
        foreach ($viewData['pendingApprovals'] as $po) {
            echo "   - {$po->po_number} (Status: {$po->status})\n";
            echo "     Organization: " . ($po->organization->name ?? 'NULL') . "\n";
            echo "     Pending Approvals: " . $po->approvals->where('status', 'pending')->count() . "\n";
        }
    } else {
        echo "❌ FAIL - Controller returns NO POs\n";
        echo "\n";
        echo "Debugging query:\n";
        
        // Manually run the query to see what's happening
        $query = PurchaseOrder::with(['organization', 'supplier', 'creator', 'approvals.approver']);
        
        if (! $approver->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
            $query->where('organization_id', $approver->organization_id);
            echo "- Filter: WHERE organization_id = {$approver->organization_id}\n";
        } else {
            echo "- Filter: NONE (approver can see all)\n";
        }
        
        $query->where('status', PurchaseOrder::STATUS_SUBMITTED);
        echo "- Status filter: WHERE status = 'submitted'\n";
        
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        
        echo "\nSQL Query:\n";
        echo $sql . "\n";
        echo "\nBindings:\n";
        print_r($bindings);
        
        $results = $query->get();
        echo "\nDirect Query Results: " . $results->count() . " PO(s)\n";
        
        if ($results->count() > 0) {
            echo "\n⚠️ WARNING: Direct query returns results, but controller doesn't!\n";
            echo "This indicates a problem in the controller logic.\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
