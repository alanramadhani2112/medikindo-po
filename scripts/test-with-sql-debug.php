<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "Testing with SQL Debug\n";
echo "======================\n\n";

// Enable query logging
DB::enableQueryLog();

$approver = User::whereHas('roles', function($q) {
    $q->where('name', 'Approver');
})->first();

Auth::login($approver);

echo "Logged in as: {$approver->name}\n";
echo "Roles: " . $approver->roles->pluck('name')->implode(', ') . "\n\n";

echo "Executing query: PurchaseOrder::where('status', 'submitted')->get()\n\n";

$pos = PurchaseOrder::where('status', 'submitted')->get();

echo "Result count: {$pos->count()}\n\n";

// Get executed queries
$queries = DB::getQueryLog();

echo "SQL Queries Executed:\n";
echo "=====================\n";
foreach ($queries as $query) {
    echo "SQL: {$query['query']}\n";
    echo "Bindings: " . json_encode($query['bindings']) . "\n";
    echo "Time: {$query['time']}ms\n\n";
}

if ($pos->count() > 0) {
    echo "✅ SUCCESS - Found POs:\n";
    foreach ($pos as $po) {
        echo "  - {$po->po_number} (Org: {$po->organization_id}, Status: {$po->status})\n";
    }
} else {
    echo "❌ NO POs FOUND\n";
    echo "\nLet's check without scope:\n";
    $posWithoutScope = PurchaseOrder::withoutGlobalScope(\App\Models\Scopes\OrganizationScope::class)
        ->where('status', 'submitted')
        ->get();
    
    echo "POs without scope: {$posWithoutScope->count()}\n";
    if ($posWithoutScope->count() > 0) {
        foreach ($posWithoutScope as $po) {
            echo "  - {$po->po_number} (Org: {$po->organization_id}, Status: {$po->status})\n";
        }
    }
}
