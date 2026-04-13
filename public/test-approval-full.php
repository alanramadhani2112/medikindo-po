<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\User;

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Approval Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #009ef7; padding-bottom: 10px; }
        h2 { color: #009ef7; margin-top: 30px; }
        .success { color: #50cd89; font-weight: bold; }
        .error { color: #f1416c; font-weight: bold; }
        .warning { color: #ffc700; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #009ef7; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #50cd89; color: white; }
        .badge-warning { background: #ffc700; color: white; }
        .badge-danger { background: #f1416c; color: white; }
        .info-box { background: #e1f0ff; border-left: 4px solid #009ef7; padding: 15px; margin: 20px 0; }
        .debug-box { background: #fff5e1; border-left: 4px solid #ffc700; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Full Approval System Test</h1>
        
        <?php
        // Test as Approver
        $approver = User::whereHas('roles', function($q) {
            $q->where('name', 'Approver');
        })->first();

        if (!$approver) {
            echo '<p class="error">❌ NO APPROVER USER FOUND!</p>';
            exit;
        }
        ?>

        <div class="info-box">
            <strong>Testing as:</strong> <?= $approver->name ?> (<?= $approver->roles->pluck('name')->implode(', ') ?>)<br>
            <strong>Email:</strong> <?= $approver->email ?><br>
            <strong>Organization ID:</strong> <?= $approver->organization_id ?? 'NULL' ?><br>
            <strong>Has view_approvals:</strong> <?= $approver->can('view_approvals') ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>' ?>
        </div>

        <h2>📊 Database Status</h2>
        <?php
        $totalPOs = PurchaseOrder::count();
        $submittedPOs = PurchaseOrder::where('status', 'submitted')->count();
        $approvedPOs = PurchaseOrder::where('status', 'approved')->count();
        $rejectedPOs = PurchaseOrder::where('status', 'rejected')->count();
        ?>
        <table>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
            <tr>
                <td>Total POs</td>
                <td><strong><?= $totalPOs ?></strong></td>
            </tr>
            <tr>
                <td>Submitted (Pending Approval)</td>
                <td><strong class="warning"><?= $submittedPOs ?></strong></td>
            </tr>
            <tr>
                <td>Approved</td>
                <td><strong class="success"><?= $approvedPOs ?></strong></td>
            </tr>
            <tr>
                <td>Rejected</td>
                <td><strong class="error"><?= $rejectedPOs ?></strong></td>
            </tr>
        </table>

        <h2>🔄 Controller Query Simulation (Pending Tab)</h2>
        <?php
        $tab = 'pending';
        $search = null;

        $query = PurchaseOrder::with(['organization', 'supplier', 'creator', 'approvals.approver']);

        // Access Control
        if (! $approver->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
            $query->where('organization_id', $approver->organization_id);
            echo '<div class="debug-box">Filter Applied: WHERE organization_id = ' . $approver->organization_id . '</div>';
        } else {
            echo '<div class="info-box"><span class="success">✅ No organization filter</span> - Approver can see ALL POs</div>';
        }

        $query->where('status', PurchaseOrder::STATUS_SUBMITTED);

        $pendingApprovals = $query->latest()->get();
        ?>

        <div class="info-box">
            <strong>Query Result:</strong> <?= $pendingApprovals->count() ?> PO(s) found
        </div>

        <?php if ($pendingApprovals->isEmpty()): ?>
            <p class="error">❌ NO DATA RETURNED FROM QUERY!</p>
            <p>This means there are no submitted POs in the database.</p>
        <?php else: ?>
            <p class="success">✅ DATA FOUND! Displaying results:</p>
            
            <table>
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Organization</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Created</th>
                        <th>Pending Approval</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingApprovals as $po): ?>
                        <?php $pendingApproval = $po->approvals->filter(fn($a) => $a->status === 'pending')->first(); ?>
                        <tr>
                            <td><strong><?= $po->po_number ?></strong></td>
                            <td><?= $po->organization->name ?? 'NULL' ?></td>
                            <td><?= $po->supplier->name ?? 'NULL' ?></td>
                            <td><span class="badge badge-warning"><?= strtoupper($po->status) ?></span></td>
                            <td>Rp <?= number_format($po->total_amount, 0, ',', '.') ?></td>
                            <td><?= $po->created_at->format('d/m/Y H:i') ?></td>
                            <td>
                                <?php if ($pendingApproval): ?>
                                    <span class="badge badge-warning">Level <?= $pendingApproval->level ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger">None</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>📈 Tab Counts</h2>
        <?php
        $baseCountQuery = PurchaseOrder::query();
        
        if (! $approver->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
            $baseCountQuery->where('organization_id', $approver->organization_id);
        }

        $counts = [
            'pending' => (clone $baseCountQuery)->where('status', PurchaseOrder::STATUS_SUBMITTED)->count(),
            'history' => (clone $baseCountQuery)->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_REJECTED])->count(),
        ];
        ?>
        <table>
            <tr>
                <th>Tab</th>
                <th>Count</th>
            </tr>
            <tr>
                <td>Pending (Antrian Persetujuan)</td>
                <td><strong class="warning"><?= $counts['pending'] ?></strong></td>
            </tr>
            <tr>
                <td>History (Riwayat Keputusan)</td>
                <td><strong><?= $counts['history'] ?></strong></td>
            </tr>
        </table>

        <h2>✅ Conclusion</h2>
        <div class="info-box">
            <?php if ($pendingApprovals->count() > 0): ?>
                <p class="success"><strong>✅ BACKEND IS WORKING CORRECTLY!</strong></p>
                <p>The controller is returning <?= $pendingApprovals->count() ?> PO(s) for approval.</p>
                <p><strong>If the approval page is still showing empty:</strong></p>
                <ul>
                    <li>Clear browser cache: <code>Ctrl + Shift + Delete</code></li>
                    <li>Hard refresh the page: <code>Ctrl + F5</code></li>
                    <li>Check browser console for JavaScript errors (F12)</li>
                    <li>Verify you're logged in as the correct user</li>
                    <li>Check the DEBUG INFO box on the approval page</li>
                </ul>
            <?php else: ?>
                <p class="error"><strong>❌ NO SUBMITTED POs FOUND!</strong></p>
                <p>You need to create and submit a Purchase Order first.</p>
            <?php endif; ?>
        </div>

        <div class="debug-box">
            <strong>Next Steps:</strong>
            <ol>
                <li>Access the approval page: <a href="/approvals" target="_blank">/approvals</a></li>
                <li>Look for the DEBUG INFO box at the top of the page</li>
                <li>If DEBUG INFO shows data but table is empty, there's a view rendering issue</li>
                <li>If DEBUG INFO shows no data, there's a controller/query issue</li>
            </ol>
        </div>
    </div>
</body>
</html>
