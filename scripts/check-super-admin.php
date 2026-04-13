<?php

/**
 * Super Admin Diagnostic Script
 * 
 * Run: php scripts/check-super-admin.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=================================================\n";
echo "SUPER ADMIN DIAGNOSTIC CHECK\n";
echo "=================================================\n\n";

// Check if user exists
echo "1. Checking if Super Admin user exists...\n";
$user = \App\Models\User::where('email', 'alanramadhani21@gmail.com')->first();

if (!$user) {
    echo "   ❌ PROBLEM: Super Admin user NOT FOUND!\n";
    echo "   💡 SOLUTION: Run: php artisan db:seed --class=SuperAdminSeeder\n\n";
    exit(1);
}

echo "   ✅ User found: {$user->name}\n";
echo "   📧 Email: {$user->email}\n\n";

// Check if user is active
echo "2. Checking if user is active...\n";
if (!$user->is_active) {
    echo "   ❌ PROBLEM: User is INACTIVE!\n";
    echo "   💡 SOLUTION: Activate user in database or run seeder again\n\n";
    exit(1);
}
echo "   ✅ User is active\n\n";

// Check organization_id
echo "3. Checking organization_id...\n";
if ($user->organization_id !== null) {
    echo "   ⚠️  WARNING: Super Admin has organization_id: {$user->organization_id}\n";
    echo "   💡 NOTE: Super Admin should have NULL organization_id\n";
    echo "   💡 This might cause issues with multi-tenant access\n\n";
} else {
    echo "   ✅ Organization ID is NULL (correct for Super Admin)\n\n";
}

// Check if role exists
echo "4. Checking if 'Super Admin' role exists...\n";
$role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')
    ->where('guard_name', 'web')
    ->first();

if (!$role) {
    echo "   ❌ PROBLEM: 'Super Admin' role NOT FOUND!\n";
    echo "   💡 SOLUTION: Run: php artisan db:seed --class=RolePermissionSeeder\n\n";
    exit(1);
}
echo "   ✅ Role exists with guard: {$role->guard_name}\n\n";

// Check if user has role
echo "5. Checking if user has 'Super Admin' role...\n";
$userRoles = $user->roles()->pluck('name')->toArray();

if (empty($userRoles)) {
    echo "   ❌ PROBLEM: User has NO ROLES assigned!\n";
    echo "   💡 SOLUTION: Run: php artisan db:seed --class=SuperAdminSeeder\n\n";
    exit(1);
}

if (!in_array('Super Admin', $userRoles)) {
    echo "   ❌ PROBLEM: User does not have 'Super Admin' role!\n";
    echo "   📋 Current roles: " . implode(', ', $userRoles) . "\n";
    echo "   💡 SOLUTION: Run: php artisan db:seed --class=SuperAdminSeeder\n\n";
    exit(1);
}

echo "   ✅ User has 'Super Admin' role\n";
echo "   📋 All roles: " . implode(', ', $userRoles) . "\n\n";

// Check role permissions
echo "6. Checking role permissions...\n";
$permissions = $role->permissions()->count();
if ($permissions === 0) {
    echo "   ❌ PROBLEM: 'Super Admin' role has NO PERMISSIONS!\n";
    echo "   💡 SOLUTION: Run: php artisan db:seed --class=RolePermissionSeeder\n\n";
    exit(1);
}
echo "   ✅ Role has {$permissions} permissions\n\n";

// Check password hash
echo "7. Checking password hash...\n";
if (empty($user->password)) {
    echo "   ❌ PROBLEM: User has NO PASSWORD!\n";
    echo "   💡 SOLUTION: Run: php artisan db:seed --class=SuperAdminSeeder\n\n";
    exit(1);
}
echo "   ✅ Password hash exists\n\n";

// Test password verification
echo "8. Testing password verification...\n";
$testPassword = 'Medikindo@2026!';
if (!\Illuminate\Support\Facades\Hash::check($testPassword, $user->password)) {
    echo "   ❌ PROBLEM: Password verification FAILED!\n";
    echo "   💡 The password might have been changed\n";
    echo "   💡 Expected password: Medikindo@2026!\n";
    echo "   💡 SOLUTION: Run: php artisan db:seed --class=SuperAdminSeeder\n\n";
    exit(1);
}
echo "   ✅ Password verification successful\n";
echo "   🔑 Password: {$testPassword}\n\n";

// Check permission cache
echo "9. Checking permission cache...\n";
echo "   💡 TIP: If you made changes to permissions, run:\n";
echo "      php artisan permission:cache-reset\n\n";

// Check auth configuration
echo "10. Checking auth configuration...\n";
$defaultGuard = config('auth.defaults.guard');
$webDriver = config('auth.guards.web.driver');
$webProvider = config('auth.guards.web.provider');
$userProvider = config('auth.providers.users.driver');
$userModel = config('auth.providers.users.model');

echo "   📋 Default guard: {$defaultGuard}\n";
echo "   📋 Web guard driver: {$webDriver}\n";
echo "   📋 Web guard provider: {$webProvider}\n";
echo "   📋 User provider driver: {$userProvider}\n";
echo "   📋 User model: {$userModel}\n\n";

if ($defaultGuard !== 'web') {
    echo "   ⚠️  WARNING: Default guard is not 'web'\n";
    echo "   💡 This might cause authentication issues\n\n";
}

// Final summary
echo "=================================================\n";
echo "✅ ALL CHECKS PASSED!\n";
echo "=================================================\n\n";

echo "Super Admin Login Credentials:\n";
echo "   📧 Email   : alanramadhani21@gmail.com\n";
echo "   🔑 Password: Medikindo@2026!\n\n";

echo "If you still cannot login, try:\n";
echo "   1. Clear all caches:\n";
echo "      php artisan cache:clear\n";
echo "      php artisan config:clear\n";
echo "      php artisan route:clear\n";
echo "      php artisan view:clear\n";
echo "      php artisan permission:cache-reset\n\n";
echo "   2. Check browser console for JavaScript errors\n";
echo "   3. Check Laravel logs: storage/logs/laravel.log\n";
echo "   4. Try incognito/private browsing mode\n";
echo "   5. Clear browser cookies and cache\n\n";

echo "=================================================\n";
