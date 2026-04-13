<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "Testing Goods Receipt PO Loading\n";
echo "================================\n\n";

// Test as Healthcare User (who should see their org's POs)
$healthcareUser = User::whereHas('roles', function($q) {
    $q->where('name', 'Healthcare User');
})->first();

if ($healthcareUser) {
    Auth::login($healthcareUser);
    
    echo "Testing as: {$healthcareUser->name}\n";
    echo "Organization ID: {$healthcareUser->organization_id}\n";
    echo "Roles: " . $healthcareUser->roles->pluck('name')->implode(', ') . "\n\n";
    
    // Simulate controller logic
    $pos = PurchaseOrder::with(['items.product', 'organization', 'supplier'])
        ->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_SHIPPED, PurchaseOrder::STATUS_DELIVERED])
        ->when(! $healthcareUser->hasRole('Super Admin'), function ($q) use ($healthcareUser) {
            $q->where('organization_id', $healthcareUser->organization_id);
        })
        ->latest()
        ->get();
    
    echo "POs available for Goods Receipt: {$pos->count()}\n\n";
    
    if ($pos->count() > 0) {
        echo "✅ SUCCESS - Found POs for Goods Receipt:\n";
        foreach ($pos as $po) {
            echo "  - {$po->po_number}\n";
            echo "    Status: {$po->status}\n";
            echo "    Supplier: " . ($po->supplier->name ?? 'N/A') . "\n";
            echo "    Organization: " . ($po->organization->name ?? 'N/A') . "\n";
            echo "    Items: {$po->items->count()}\n\n";
        }
    } else {
        echo "❌ NO POs FOUND\n";
        echo "Checking all approved POs in database...\n\n";
        
        $allApproved = PurchaseOrder::where('status', 'approved')->get();
        echo "Total approved POs: {$allApproved->count()}\n";
        
        if ($allApproved->count() > 0) {
            foreach ($allApproved as $po) {
                echo "  - {$po->po_number} (Org: {$po->organization_id})\n";
            }
            
            if ($healthcareUser->organization_id) {
                $userOrgPOs = $allApproved->where('organization_id', $healthcareUser->organization_id);
                echo "\nPOs in user's organization ({$healthcareUser->organization_id}): {$userOrgPOs->count()}\n";
            }
        }
    }
    
    Auth::logout();
}

echo "\n================================\n";
echo "If POs are found, the dropdown should now show data!\n";
echo "Navigate to /goods-receipts/create to verify.\n";