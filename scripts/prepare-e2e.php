<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$org = \App\Models\Organization::where('code', 'TEST-ORG')->first();
if ($org) {
    \App\Models\CreditLimit::updateOrCreate(
        ['organization_id' => $org->id],
        ['max_limit' => 1000000000, 'is_active' => true]
    );
    echo "✅ Credit limit set to 1.000.000.000 for {$org->name}\n";
} else {
    echo "❌ Organization TEST-ORG not found. Please run CleanUserSeeder first.\n";
}
