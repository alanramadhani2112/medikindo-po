<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\PurchaseOrder;

echo "=== CHECKING APPROVER USERS ===\n\n";

// Get all users with approver roles
$approvers = User::whereHas('roles', function($q) {
    $q->whereIn('name', ['Super Admin', 'Admin Approver']);
})->with('roles', 'organization')->get();

echo "Total Approvers: " . $approvers->count() . "\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($approvers as $user) {
    echo "User: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Organization ID: " . ($user->organization_id ?? 'NULL') . "\n";
    if ($user->organization) {
        echo "Organization Name: {$user->organization->name}\n";
    }
    echo "\n";
}

// Check submitted POs
echo str_repeat("=", 80) . "\n";
echo "SUBMITTED POs:\n";
echo str_repeat("-", 80) . "\n";

$submittedPOs = PurchaseOrder::where('status', 'submitted')
    ->with('organization', 'creator')
    ->get();

echo "Total Submitted POs: " . $submittedPOs->count() . "\n\n";

foreach ($submittedPOs as $po) {
    echo "PO: {$po->po_number}\n";
    echo "Organization ID: " . ($po->organization_id ?? 'NULL') . "\n";
    if ($po->organization) {
        echo "Organization: {$po->organization->name}\n";
    }
    echo "Creator: {$po->creator->name}\n";
    echo "Status: {$po->status}\n";
    
    echo "\nWho can see this PO:\n";
    foreach ($approvers as $approver) {
        $canSee = $approver->hasRole('Super Admin') || $approver->organization_id == $po->organization_id;
        $status = $canSee ? '✅' : '❌';
        echo "  {$status} {$approver->name} ({$approver->roles->pluck('name')->implode(', ')})\n";
    }
    echo "\n" . str_repeat("-", 80) . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
