<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== CHECKING ROLES AND USERS ===\n\n";

// Get all roles
$roles = Role::all();
echo "Available Roles:\n";
echo str_repeat("-", 80) . "\n";
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}
echo "\n";

// Get all users with their roles
$users = User::with('roles', 'organization')->get();
echo "All Users:\n";
echo str_repeat("=", 80) . "\n";

foreach ($users as $user) {
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Organization ID: " . ($user->organization_id ?? 'NULL') . "\n";
    if ($user->organization) {
        echo "Organization: {$user->organization->name}\n";
    }
    echo "Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
    echo str_repeat("-", 80) . "\n";
}

echo "\n";
echo "Users with Approval Rights:\n";
echo str_repeat("=", 80) . "\n";

$approvers = User::whereHas('roles', function($q) {
    $q->whereIn('name', ['Super Admin', 'Admin Approver', 'Approver']);
})->with('roles')->get();

if ($approvers->isEmpty()) {
    echo "❌ NO APPROVERS FOUND!\n";
} else {
    foreach ($approvers as $approver) {
        echo "✅ {$approver->name} - {$approver->roles->pluck('name')->implode(', ')}\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
