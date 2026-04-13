<?php

/**
 * Verify Users Script
 * 
 * Run: php scripts/verify-users.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=================================================\n";
echo "USER VERIFICATION\n";
echo "=================================================\n\n";

$users = \App\Models\User::with(['roles', 'organization'])->get();

if ($users->isEmpty()) {
    echo "❌ NO USERS FOUND!\n";
    echo "💡 Run: php artisan db:seed --class=CleanUserSeeder\n\n";
    exit(1);
}

echo "Total Users: {$users->count()}\n\n";

foreach ($users as $user) {
    $roleName = $user->roles->pluck('name')->first() ?? 'No Role';
    $orgName = $user->organization ? $user->organization->name : 'NULL';
    $status = $user->is_active ? '✅' : '❌';
    
    echo "─────────────────────────────────────────────────\n";
    echo "{$status} {$user->name}\n";
    echo "   📧 Email : {$user->email}\n";
    echo "   📋 Role  : {$roleName}\n";
    echo "   🏢 Org   : {$orgName}\n";
    echo "   🔓 Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
    
    // Check permissions
    $permissionCount = $user->getAllPermissions()->count();
    echo "   🔑 Perms : {$permissionCount}\n";
}

echo "─────────────────────────────────────────────────\n\n";

// Verify expected users
$expectedRoles = ['Super Admin', 'Healthcare User', 'Approver', 'Finance'];
$actualRoles = $users->flatMap(function($user) {
    return $user->roles->pluck('name');
})->unique()->values()->toArray();

echo "Expected Roles: " . implode(', ', $expectedRoles) . "\n";
echo "Actual Roles  : " . implode(', ', $actualRoles) . "\n\n";

$missingRoles = array_diff($expectedRoles, $actualRoles);
if (!empty($missingRoles)) {
    echo "⚠️  WARNING: Missing roles: " . implode(', ', $missingRoles) . "\n";
    echo "💡 Run: php artisan db:seed --class=CleanUserSeeder\n\n";
} else {
    echo "✅ All expected roles present!\n\n";
}

// Summary table
echo "=================================================\n";
echo "SUMMARY\n";
echo "=================================================\n\n";

$table = [];
foreach ($users as $user) {
    $table[] = [
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->roles->pluck('name')->first() ?? 'No Role',
        'org' => $user->organization ? $user->organization->name : 'NULL',
        'active' => $user->is_active ? 'Yes' : 'No',
        'perms' => $user->getAllPermissions()->count(),
    ];
}

// Print table
$headers = ['Name', 'Email', 'Role', 'Organization', 'Active', 'Perms'];
$widths = [
    'name' => 20,
    'email' => 35,
    'role' => 15,
    'org' => 20,
    'active' => 6,
    'perms' => 5,
];

// Header
echo str_pad('Name', $widths['name']) . ' | ';
echo str_pad('Email', $widths['email']) . ' | ';
echo str_pad('Role', $widths['role']) . ' | ';
echo str_pad('Organization', $widths['org']) . ' | ';
echo str_pad('Active', $widths['active']) . ' | ';
echo str_pad('Perms', $widths['perms']) . "\n";

echo str_repeat('─', array_sum($widths) + 15) . "\n";

// Rows
foreach ($table as $row) {
    echo str_pad(substr($row['name'], 0, $widths['name']), $widths['name']) . ' | ';
    echo str_pad(substr($row['email'], 0, $widths['email']), $widths['email']) . ' | ';
    echo str_pad(substr($row['role'], 0, $widths['role']), $widths['role']) . ' | ';
    echo str_pad(substr($row['org'], 0, $widths['org']), $widths['org']) . ' | ';
    echo str_pad($row['active'], $widths['active']) . ' | ';
    echo str_pad($row['perms'], $widths['perms']) . "\n";
}

echo "\n";
echo "=================================================\n";
echo "✅ VERIFICATION COMPLETE\n";
echo "=================================================\n";
