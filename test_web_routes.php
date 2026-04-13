<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

echo "=== MEDIKINDO WEB ROUTE RENDER TEST ===\n\n";

try {
    Artisan::call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);

    $organization = Organization::firstOrCreate(
        ['code' => 'ROUTE-ORG'],
        ['name' => 'Route Test Organization', 'type' => 'clinic', 'is_active' => true]
    );

    $superAdmin = User::firstOrCreate(
        ['email' => 'superadmin.route@test.local'],
        [
            'name' => 'Super Admin Route',
            'password' => 'password',
            'organization_id' => $organization->id,
            'is_active' => true,
        ]
    );
    $superAdmin->syncRoles(['Super Admin']);

    $healthcareUser = User::firstOrCreate(
        ['email' => 'healthcare.route@test.local'],
        [
            'name' => 'Healthcare Route',
            'password' => 'password',
            'organization_id' => $organization->id,
            'is_active' => true,
        ]
    );
    $healthcareUser->syncRoles(['Healthcare User']);

    $routes = [
        '/dashboard',
        '/purchase-orders',
        '/approvals',
        '/goods-receipts',
        '/finance',
        '/invoices',
        '/payments',
        '/financial-controls',
        '/organizations',
        '/suppliers',
        '/products',
        '/users',
        '/audit',
    ];

    $httpKernel = app(HttpKernel::class);

    $testUrl = static function (string $url, User $user) use ($httpKernel): int {
        Auth::login($user);
        $request = \Illuminate\Http\Request::create($url, 'GET');

        try {
            $response = $httpKernel->handle($request);
            $status = $response->getStatusCode();
            $label = str_pad($url, 30, ' ');
            echo "{$label} | Status: {$status} | User: {$user->name}\n";
            return $status;
        } finally {
            Auth::logout();
        }
    };

    $hasFatal = false;

    echo "Testing as SUPER ADMIN...\n";
    echo "--------------------------------------------------------\n";
    foreach ($routes as $route) {
        $status = $testUrl($route, $superAdmin);
        if ($status >= 500) {
            $hasFatal = true;
        }
    }

    echo "\nTesting as HEALTHCARE USER...\n";
    echo "--------------------------------------------------------\n";
    foreach ($routes as $route) {
        $status = $testUrl($route, $healthcareUser);
        if ($status >= 500) {
            $hasFatal = true;
        }
    }

    if ($hasFatal) {
        throw new RuntimeException('One or more routes returned 5xx errors.');
    }

    echo "\n=== WEB ROUTE RENDER TEST PASSED ===\n";
    exit(0);
} catch (Throwable $e) {
    echo "\n=== WEB ROUTE RENDER TEST FAILED ===\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

