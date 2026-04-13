<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create a request
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Get authenticated user
$user = $request->user();

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Current User Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #009ef7; }
        .success { color: #50cd89; font-weight: bold; }
        .error { color: #f1416c; font-weight: bold; }
        .info-box { background: #e1f0ff; border-left: 4px solid #009ef7; padding: 15px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #009ef7; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Current User Test</h1>
        
        <?php if (!$user): ?>
            <p class="error">❌ NO USER LOGGED IN!</p>
            <p>This is the problem! You need to be logged in to see the approval page.</p>
            <p><a href="/login">Go to Login Page</a></p>
        <?php else: ?>
            <p class="success">✅ USER IS LOGGED IN</p>
            
            <table>
                <tr>
                    <th>Property</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Name</td>
                    <td><strong><?= $user->name ?></strong></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?= $user->email ?></td>
                </tr>
                <tr>
                    <td>Organization ID</td>
                    <td><?= $user->organization_id ?? 'NULL' ?></td>
                </tr>
                <tr>
                    <td>Roles</td>
                    <td><?= $user->roles->pluck('name')->implode(', ') ?></td>
                </tr>
                <tr>
                    <td>Has view_approvals</td>
                    <td><?= $user->can('view_approvals') ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>' ?></td>
                </tr>
            </table>

            <h2>🔍 Query Test</h2>
            <?php
            use App\Models\PurchaseOrder;

            $query = PurchaseOrder::with(['organization', 'supplier', 'creator', 'approvals.approver']);

            // Access Control
            if (! $user->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
                $query->where('organization_id', $user->organization_id);
                echo '<div class="info-box">Filter Applied: WHERE organization_id = ' . $user->organization_id . '</div>';
            } else {
                echo '<div class="info-box"><span class="success">✅ No organization filter</span> - User can see ALL POs</div>';
            }

            $query->where('status', PurchaseOrder::STATUS_SUBMITTED);

            $results = $query->get();
            ?>

            <p><strong>Query Result: <?= $results->count() ?> PO(s)</strong></p>

            <?php if ($results->count() > 0): ?>
                <p class="success">✅ QUERY RETURNS DATA!</p>
                <table>
                    <tr>
                        <th>PO Number</th>
                        <th>Organization</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($results as $po): ?>
                        <tr>
                            <td><?= $po->po_number ?></td>
                            <td><?= $po->organization->name ?? 'NULL' ?></td>
                            <td><?= $po->status ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="error">❌ QUERY RETURNS NO DATA!</p>
                <p>This means the user's role or organization is filtering out the POs.</p>
            <?php endif; ?>

            <h2>📋 Conclusion</h2>
            <div class="info-box">
                <?php if ($results->count() > 0): ?>
                    <p class="success">✅ Everything looks good from this test!</p>
                    <p>The issue might be with how the controller is being called or session handling in the actual page.</p>
                    <p><strong>Try accessing:</strong> <a href="/approvals">/approvals</a></p>
                <?php else: ?>
                    <p class="error">❌ User cannot see the submitted POs!</p>
                    <p><strong>Reason:</strong> User's role or organization is filtering them out.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
