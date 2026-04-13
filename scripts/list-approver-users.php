<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "Approver Users List\n";
echo "===================\n\n";

$approvers = User::whereHas('roles', function($q) {
    $q->whereIn('name', ['Super Admin', 'Approver', 'Admin Approver']);
})->with('roles', 'organization')->get();

if ($approvers->isEmpty()) {
    echo "❌ NO APPROVER USERS FOUND!\n";
    exit;
}

echo "Total Approver Users: {$approvers->count()}\n\n";

foreach ($approvers as $user) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Password: (use the password you set during seeding)\n";
    echo "Organization ID: " . ($user->organization_id ?? 'NULL') . "\n";
    echo "Organization: " . ($user->organization->name ?? 'N/A') . "\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Can view_approvals: " . ($user->can('view_approvals') ? '✅ YES' : '❌ NO') . "\n";
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n📋 INSTRUCTIONS:\n";
echo "1. Login with one of the users above\n";
echo "2. Navigate to /approvals\n";
echo "3. Check if data appears\n";
echo "4. If still empty, check storage/logs/laravel.log for DEBUG info\n";
