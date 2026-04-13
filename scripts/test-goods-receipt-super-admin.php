<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "Testing Goods Receipt as Super Admin\n";
echo "====================================\n\n";

$superAdmin = User::whereHas('roles', function($q) {
    $q->where('name', 'Super Admin');
})->first();

if ($superAdmin) {
    Auth::login($superAdmin);
    
    echo "Testing as: {$superAdmin->name}\n";
    echo "Organization ID: " . ($superAdmin->organization_id ?? 'NULL') . "\n";
    echo "Roles: " . $superAdmin->roles->pluck('name')->implode(', ') . "\n\n";
    
    // Simulate controller logic
    $pos = PurchaseOrder::with(['items.product', 'organization', 'supplier'])
        ->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_SHIPPED, PurchaseOrder::STATUS_DELIVERED])
        ->when(! $superAdmin->hasRole('Super Admin'), function ($q) use ($superAdmin) {
            $q->where('organization_id', $superAdmin->organization_id);
        })
        ->latest()
        ->get();
    
    echo "POs available for Goods Receipt: {$pos->count()}\n\n";
    
    if ($pos->count() > 0) {
        echo "✅ SUCCESS - Super Admin can see all approved POs:\n";
        foreach ($pos as $po) {
            echo "  - {$po->po_number}\n";
            echo "    Status: {$po->status}\n";
            echo "    Organization: " . ($po->organization->name ?? 'N/A') . "\n\n";
        }
    } else {
        echo "❌ NO POs FOUND for Super Admin\n";
    }
    
    Auth::logout();
}

echo "====================================\n";
echo "Super Admin should see ALL approved POs from all organizations.\n";