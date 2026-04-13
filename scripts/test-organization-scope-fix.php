<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "Testing OrganizationScope Fix\n";
echo "==============================\n\n";

// Test 1: As Super Admin
$superAdmin = User::whereHas('roles', function($q) {
    $q->where('name', 'Super Admin');
})->first();

if ($superAdmin) {
    Auth::login($superAdmin);
    
    echo "Test 1: As Super Admin ({$superAdmin->name})\n";
    echo "Organization ID: " . ($superAdmin->organization_id ?? 'NULL') . "\n";
    
    $pos = PurchaseOrder::where('status', 'submitted')->get();
    echo "Submitted POs visible: {$pos->count()}\n";
    
    if ($pos->count() > 0) {
        echo "✅ SUCCESS - Super Admin can see all POs\n";
        foreach ($pos as $po) {
            echo "  - {$po->po_number} (Org: {$po->organization_id})\n";
        }
    } else {
        echo "❌ FAIL - Super Admin cannot see POs\n";
    }
    
    Auth::logout();
    echo "\n";
}

// Test 2: As Approver
$approver = User::whereHas('roles', function($q) {
    $q->where('name', 'Approver');
})->first();

if ($approver) {
    Auth::login($approver);
    
    echo "Test 2: As Approver ({$approver->name})\n";
    echo "Organization ID: " . ($approver->organization_id ?? 'NULL') . "\n";
    
    $pos = PurchaseOrder::where('status', 'submitted')->get();
    echo "Submitted POs visible: {$pos->count()}\n";
    
    if ($pos->count() > 0) {
        echo "✅ SUCCESS - Approver can see all POs\n";
        foreach ($pos as $po) {
            echo "  - {$po->po_number} (Org: {$po->organization_id})\n";
        }
    } else {
        echo "❌ FAIL - Approver cannot see POs\n";
    }
    
    Auth::logout();
    echo "\n";
}

// Test 3: As Healthcare User (should only see their org)
$healthcareUser = User::whereHas('roles', function($q) {
    $q->where('name', 'Healthcare User');
})->first();

if ($healthcareUser) {
    Auth::login($healthcareUser);
    
    echo "Test 3: As Healthcare User ({$healthcareUser->name})\n";
    echo "Organization ID: {$healthcareUser->organization_id}\n";
    
    $pos = PurchaseOrder::where('status', 'submitted')->get();
    echo "Submitted POs visible: {$pos->count()}\n";
    
    if ($pos->count() > 0) {
        echo "POs from organization {$healthcareUser->organization_id} only:\n";
        foreach ($pos as $po) {
            echo "  - {$po->po_number} (Org: {$po->organization_id})\n";
            if ($po->organization_id != $healthcareUser->organization_id) {
                echo "    ❌ ERROR: User can see PO from different organization!\n";
            }
        }
    } else {
        echo "No POs visible (expected if no POs in their organization)\n";
    }
    
    Auth::logout();
    echo "\n";
}

echo "==============================\n";
echo "✅ If Super Admin and Approver can see all POs, the fix is working!\n";
echo "✅ If Healthcare User only sees their org's POs, the scope is working correctly!\n";
