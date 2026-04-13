<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "Debugging OrganizationScope\n";
echo "===========================\n\n";

$approver = User::whereHas('roles', function($q) {
    $q->where('name', 'Approver');
})->first();

if (!$approver) {
    echo "❌ No approver found\n";
    exit;
}

Auth::login($approver);

echo "Logged in as: {$approver->name}\n";
echo "Roles: " . $approver->roles->pluck('name')->implode(', ') . "\n";
echo "Organization ID: " . ($approver->organization_id ?? 'NULL') . "\n\n";

// Test hasAnyRole
echo "Testing hasAnyRole method:\n";
echo "hasAnyRole(['Super Admin']): " . ($approver->hasAnyRole(['Super Admin']) ? 'true' : 'false') . "\n";
echo "hasAnyRole(['Approver']): " . ($approver->hasAnyRole(['Approver']) ? 'true' : 'false') . "\n";
echo "hasAnyRole(['Super Admin', 'Approver']): " . ($approver->hasAnyRole(['Super Admin', 'Approver']) ? 'true' : 'false') . "\n";
echo "hasAnyRole(['Super Admin', 'Approver', 'Admin Approver']): " . ($approver->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver']) ? 'true' : 'false') . "\n\n";

// Test hasRole
echo "Testing hasRole method:\n";
echo "hasRole('Approver'): " . ($approver->hasRole('Approver') ? 'true' : 'false') . "\n";
echo "hasRole('Super Admin'): " . ($approver->hasRole('Super Admin') ? 'true' : 'false') . "\n\n";

// Check if Auth::user() returns the same
echo "Testing Auth::user():\n";
$authUser = Auth::user();
echo "Auth::user()->name: {$authUser->name}\n";
echo "Auth::user()->hasAnyRole(['Approver']): " . ($authUser->hasAnyRole(['Approver']) ? 'true' : 'false') . "\n";
