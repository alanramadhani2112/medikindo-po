<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "Checking Approval Permissions...\n";
echo "================================\n\n";

$approver = User::whereHas('roles', function($q) {
    $q->where('name', 'Approver');
})->first();

if ($approver) {
    echo "Approver: {$approver->name}\n";
    echo "Email: {$approver->email}\n";
    echo "Has view_approvals: " . ($approver->can('view_approvals') ? '✅ YES' : '❌ NO') . "\n";
    echo "Has approve_purchase_orders: " . ($approver->can('approve_purchase_orders') ? '✅ YES' : '❌ NO') . "\n";
    echo "\n";
}

$superAdmin = User::whereHas('roles', function($q) {
    $q->where('name', 'Super Admin');
})->first();

if ($superAdmin) {
    echo "Super Admin: {$superAdmin->name}\n";
    echo "Email: {$superAdmin->email}\n";
    echo "Has view_approvals: " . ($superAdmin->can('view_approvals') ? '✅ YES' : '❌ NO') . "\n";
    echo "Has approve_purchase_orders: " . ($superAdmin->can('approve_purchase_orders') ? '✅ YES' : '❌ NO') . "\n";
    echo "\n";
}

echo "================================\n";
echo "If both users have permissions, the issue is likely:\n";
echo "1. Browser cache (clear with Ctrl + Shift + Delete)\n";
echo "2. View rendering issue\n";
echo "3. Check the debug info on the approval page\n";
