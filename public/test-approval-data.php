<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\User;

header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>Approval Data Test</title></head><body>";
echo "<h1>Approval Data Test</h1>";
echo "<hr>";

// Get submitted POs
$submittedPOs = PurchaseOrder::where('status', 'submitted')
    ->with(['organization', 'supplier', 'creator', 'approvals'])
    ->get();

echo "<h2>Submitted POs in Database: " . $submittedPOs->count() . "</h2>";

if ($submittedPOs->isEmpty()) {
    echo "<p style='color: red;'>❌ NO SUBMITTED POs FOUND!</p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>PO Number</th><th>Organization</th><th>Status</th><th>Pending Approvals</th></tr>";
    
    foreach ($submittedPOs as $po) {
        $pendingApprovals = $po->approvals->where('status', 'pending')->count();
        echo "<tr>";
        echo "<td>{$po->po_number}</td>";
        echo "<td>" . ($po->organization->name ?? 'NULL') . "</td>";
        echo "<td><strong>{$po->status}</strong></td>";
        echo "<td>{$pendingApprovals}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<hr>";
echo "<h2>Approver Users:</h2>";

$approvers = User::whereHas('roles', function($q) {
    $q->whereIn('name', ['Super Admin', 'Approver', 'Admin Approver']);
})->with('roles')->get();

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Name</th><th>Email</th><th>Roles</th><th>Org ID</th></tr>";

foreach ($approvers as $user) {
    echo "<tr>";
    echo "<td>{$user->name}</td>";
    echo "<td>{$user->email}</td>";
    echo "<td>" . $user->roles->pluck('name')->implode(', ') . "</td>";
    echo "<td>" . ($user->organization_id ?? 'NULL') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Test Query (as Approver):</h2>";

$approver = $approvers->first();
if ($approver) {
    echo "<p>Testing as: <strong>{$approver->name}</strong></p>";
    
    $query = PurchaseOrder::with(['organization', 'supplier', 'creator', 'approvals.approver']);
    
    if (! $approver->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
        $query->where('organization_id', $approver->organization_id);
        echo "<p>Filter: WHERE organization_id = {$approver->organization_id}</p>";
    } else {
        echo "<p style='color: green;'>✅ Filter: NONE (approver can see all POs)</p>";
    }
    
    $query->where('status', 'submitted');
    
    $results = $query->get();
    
    echo "<p><strong>Query Result: " . $results->count() . " PO(s)</strong></p>";
    
    if ($results->count() > 0) {
        echo "<p style='color: green;'>✅ SUCCESS - Query returns POs!</p>";
        echo "<ul>";
        foreach ($results as $po) {
            echo "<li>{$po->po_number} - {$po->status}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ FAIL - Query returns NO POs!</p>";
    }
}

echo "<hr>";
echo "<p><strong>Conclusion:</strong></p>";
echo "<p>If query returns POs but approval page is empty, the problem is in the VIEW or BROWSER CACHE.</p>";
echo "<p><strong>Solution:</strong> Clear browser cache (Ctrl + Shift + Delete) and hard refresh (Ctrl + F5)</p>";

echo "</body></html>";
