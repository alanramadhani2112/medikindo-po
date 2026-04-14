<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Approval;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDataForUser(User $user): array
    {
        // Detect primary role and return ONLY relevant data
        if ($user->hasRole('Super Admin')) {
            return $this->getSuperAdminDashboard($user);
        }
        
        if ($user->hasRole('Finance')) {
            return $this->getFinanceDashboard($user);
        }
        
        if ($user->can('approve_po')) {
            return $this->getApproverDashboard($user);
        }
        
        if ($user->hasRole('Healthcare User') || $user->hasRole('Clinic User')) {
            return $this->getHealthcareDashboard($user);
        }

        // Default fallback
        return $this->getBasicDashboard($user);
    }

    /**
     * HOSPITAL / CLINIC USER DASHBOARD
     * Purpose: Procurement + Payment monitoring
     */
    private function getHealthcareDashboard(User $user): array
    {
        $orgId = $user->organization_id;
        
        // Cards Data
        $poActive = PurchaseOrder::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'shipped', 'delivered'])
            ->count();
            
        $poWaitingApproval = PurchaseOrder::where('organization_id', $orgId)
            ->where('status', 'submitted')
            ->count();
            
        $poInDelivery = PurchaseOrder::where('organization_id', $orgId)
            ->whereIn('status', ['shipped', 'delivered'])
            ->count();

        // Recent POs
        $recentPOs = PurchaseOrder::where('organization_id', $orgId)
            ->with(['supplier', 'organization'])
            ->latest()
            ->limit(10)
            ->get();

        // Alerts
        $alerts = [];
        
        $rejectedPOs = PurchaseOrder::where('organization_id', $orgId)
            ->where('status', 'rejected')
            ->whereDate('updated_at', '>=', now()->subDays(7))
            ->count();
        if ($rejectedPOs > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'ki-cross-circle',
                'title' => 'PO Ditolak',
                'message' => "{$rejectedPOs} PO ditolak dalam 7 hari terakhir",
                'action' => route('web.po.index', ['status' => 'rejected'])
            ];
        }

        return [
            'role' => 'healthcare',
            'cards' => [
                ['label' => 'Total PO Aktif', 'value' => $poActive, 'icon' => 'ki-document', 'color' => 'primary'],
                ['label' => 'PO Menunggu Persetujuan', 'value' => $poWaitingApproval, 'icon' => 'ki-timer', 'color' => 'warning'],
                ['label' => 'PO Dalam Pengiriman', 'value' => $poInDelivery, 'icon' => 'ki-delivery', 'color' => 'info'],
            ],
            'recentPOs' => $recentPOs,
            'alerts' => $alerts,
        ];
    }

    /**
     * APPROVER DASHBOARD
     * Purpose: Approval control
     */
    private function getApproverDashboard(User $user): array
    {
        // Cards Data
        $pendingApproval = PurchaseOrder::where('status', 'submitted')->count();
        
        $approvedToday = Approval::where('status', 'approved')
            ->whereDate('created_at', now())
            ->count();
            
        $rejectedPOs = PurchaseOrder::where('status', 'rejected')
            ->whereDate('updated_at', now())
            ->count();
            
        $highRiskPOs = PurchaseOrder::where('status', 'submitted')
            ->whereHas('items', function($q) {
                $q->whereHas('product', function($pq) {
                    $pq->where('category', 'like', '%narkotika%')
                      ->orWhere('category', 'like', '%psikotropika%');
                });
            })
            ->count();

        // Pending Approval List (Priority)
        $pendingList = PurchaseOrder::where('status', 'submitted')
            ->with(['organization', 'supplier', 'items.product'])
            ->orderByRaw("CASE WHEN EXISTS (
                SELECT 1 FROM purchase_order_items poi 
                JOIN products p ON poi->product_id = p.id 
                WHERE poi.purchase_order_id = purchase_orders.id 
                AND (p.category LIKE '%narkotika%' OR p.category LIKE '%psikotropika%')
            ) THEN 0 ELSE 1 END")
            ->orderBy('submitted_at', 'asc')
            ->limit(15)
            ->get();

        // Recent Activity
        $recentActivity = Approval::with(['purchaseOrder', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        // Alerts
        $alerts = [];
        
        if ($highRiskPOs > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'ki-shield-cross',
                'title' => 'PO Narkotika/Psikotropika',
                'message' => "{$highRiskPOs} PO mengandung obat terkontrol memerlukan persetujuan",
                'action' => route('web.approvals.index')
            ];
        }

        $overdueApprovals = PurchaseOrder::where('status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))
            ->count();
        if ($overdueApprovals > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'ki-timer',
                'title' => 'Persetujuan Tertunda',
                'message' => "{$overdueApprovals} PO menunggu lebih dari 3 hari",
                'action' => route('web.approvals.index')
            ];
        }

        return [
            'role' => 'approver',
            'cards' => [
                ['label' => 'Pending Approval', 'value' => $pendingApproval, 'icon' => 'ki-document', 'color' => 'warning'],
                ['label' => 'Disetujui Hari Ini', 'value' => $approvedToday, 'icon' => 'ki-check-circle', 'color' => 'success'],
                ['label' => 'Ditolak Hari Ini', 'value' => $rejectedPOs, 'icon' => 'ki-cross-circle', 'color' => 'danger'],
                ['label' => 'PO High Risk (Narkotika)', 'value' => $highRiskPOs, 'icon' => 'ki-shield-cross', 'color' => 'danger'],
            ],
            'pendingList' => $pendingList,
            'recentActivity' => $recentActivity,
            'alerts' => $alerts,
        ];
    }

    /**
     * FINANCE DASHBOARD
     * Purpose: Cashflow & payment control
     */
    private function getFinanceDashboard(User $user): array
    {
        // Cards Data
        $totalReceivable = CustomerInvoice::whereIn('status', ['unpaid', 'partial'])
            ->sum(DB::raw('total_amount - paid_amount'));
            
        $totalPayable = SupplierInvoice::whereIn('status', ['issued', 'payment_submitted'])
            ->sum(DB::raw('total_amount - paid_amount'));
            
        $overdueInvoices = SupplierInvoice::where('due_date', '<', now())
            ->whereIn('status', ['issued', 'payment_submitted'])
            ->count();
            
        $pendingPayments = SupplierInvoice::where('status', 'payment_submitted')->count();
        
        $todayCashflow = Payment::whereDate('payment_date', now())
            ->sum('amount');

        // Outstanding Invoices
        $outstandingInvoices = SupplierInvoice::with(['supplier', 'purchaseOrder.organization'])
            ->whereIn('status', ['issued', 'payment_submitted'])
            ->orderBy('due_date', 'asc')
            ->limit(15)
            ->get();

        // Recent Payments
        $recentPayments = Payment::with(['invoice', 'user'])
            ->latest('payment_date')
            ->limit(10)
            ->get();

        // Alerts
        $alerts = [];
        
        if ($overdueInvoices > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'ki-information',
                'title' => 'Invoice Overdue',
                'message' => "{$overdueInvoices} invoice melewati jatuh tempo",
                'action' => route('web.invoices.index', ['tab' => 'supplier'])
            ];
        }

        $unconfirmedPayments = Payment::where('status', 'pending')
            ->whereDate('payment_date', '<', now()->subDays(2))
            ->count();
        if ($unconfirmedPayments > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'ki-wallet',
                'title' => 'Pembayaran Belum Dikonfirmasi',
                'message' => "{$unconfirmedPayments} pembayaran menunggu konfirmasi",
                'action' => route('web.payments.index')
            ];
        }

        return [
            'role' => 'finance',
            'cards' => [
                ['label' => 'Total Receivable (AR)', 'value' => 'Rp ' . number_format($totalReceivable, 0, ',', '.'), 'icon' => 'ki-arrow-down', 'color' => 'success'],
                ['label' => 'Total Payable (AP)', 'value' => 'Rp ' . number_format($totalPayable, 0, ',', '.'), 'icon' => 'ki-arrow-up', 'color' => 'danger'],
                ['label' => 'Invoice Overdue', 'value' => $overdueInvoices, 'icon' => 'ki-information', 'color' => 'warning'],
                ['label' => 'Pending Payment', 'value' => $pendingPayments, 'icon' => 'ki-wallet', 'color' => 'info'],
                ['label' => 'Cashflow Hari Ini', 'value' => 'Rp ' . number_format($todayCashflow, 0, ',', '.'), 'icon' => 'ki-dollar', 'color' => 'primary'],
            ],
            'outstandingInvoices' => $outstandingInvoices,
            'recentPayments' => $recentPayments,
            'alerts' => $alerts,
        ];
    }

    /**
     * SUPER ADMIN DASHBOARD
     * Purpose: System monitoring
     */
    private function getSuperAdminDashboard(User $user): array
    {
        // GROUP 1: BUSINESS ACTIVITY
        $poToday = PurchaseOrder::whereDate('created_at', now())->count();
        $poMonth = PurchaseOrder::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $poValueMonth = PurchaseOrder::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        $activeOrganizations = Organization::where('is_active', true)->count();
        $activeUsers = User::where('is_active', true)->count();

        // GROUP 2: OPERATIONS STATUS
        $poPendingApproval = PurchaseOrder::where('status', 'submitted')->count();
        $poInProgress = PurchaseOrder::whereIn('status', ['approved', 'shipped'])->count();
        $poCompleted = PurchaseOrder::where('status', 'delivered')
            ->whereMonth('created_at', now()->month)
            ->count();
        $poRejected = PurchaseOrder::where('status', 'rejected')
            ->whereMonth('created_at', now()->month)
            ->count();

        // GROUP 3: FINANCIAL OVERVIEW
        $totalReceivable = CustomerInvoice::whereIn('status', ['unpaid', 'partial'])
            ->sum(DB::raw('total_amount - paid_amount'));
        $totalPayable = SupplierInvoice::whereIn('status', ['issued', 'payment_submitted'])
            ->sum(DB::raw('total_amount - paid_amount'));
        $outstandingInvoice = SupplierInvoice::whereIn('status', ['issued', 'payment_submitted'])->count();
        $overdueInvoice = SupplierInvoice::where('due_date', '<', now())
            ->whereIn('status', ['issued', 'payment_submitted'])
            ->count();

        // GROUP 4: SYSTEM HEALTH
        $failedTransactions = PurchaseOrder::where('status', 'rejected')
            ->whereDate('updated_at', '>=', now()->subDay())
            ->count();
        $pendingActions = PurchaseOrder::where('status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))
            ->count();
        $systemErrors = AuditLog::where('action', 'error')
            ->whereDate('occurred_at', '>=', now()->subDay())
            ->count();
        $auditLogsToday = AuditLog::whereDate('occurred_at', now())->count();

        // ANALYTICS DATA (NEW)
        $analytics = $this->getSuperAdminAnalytics();

        // Recent Activity
        $recentActivity = AuditLog::with(['user'])
            ->latest('occurred_at')
            ->limit(15)
            ->get();

        // Audit Logs (System errors)
        $auditLogs = AuditLog::where('action', 'error')
            ->orWhere('action', 'failed')
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        // Alerts
        $alerts = [];
        
        if ($systemErrors > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'ki-cross-circle',
                'title' => 'System Error',
                'message' => "{$systemErrors} error tercatat dalam 24 jam terakhir",
                'action' => route('web.dashboard.audit')
            ];
        }

        if ($failedTransactions > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'ki-information',
                'title' => 'Transaksi Gagal',
                'message' => "{$failedTransactions} PO ditolak dalam 24 jam terakhir",
                'action' => route('web.po.index', ['status' => 'rejected'])
            ];
        }

        if ($overdueInvoice > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'ki-calendar',
                'title' => 'Invoice Overdue',
                'message' => "{$overdueInvoice} invoice melewati jatuh tempo",
                'action' => route('web.invoices.index', ['tab' => 'supplier'])
            ];
        }

        return [
            'role' => 'superadmin',
            'cardGroups' => [
                [
                    'title' => 'Business Activity',
                    'icon' => 'ki-chart-line-up',
                    'color' => 'primary',
                    'cards' => [
                        ['label' => 'Total PO (Hari Ini / Bulan)', 'value' => $poToday . ' / ' . $poMonth, 'icon' => 'ki-document', 'color' => 'primary'],
                        ['label' => 'Total PO Value (Bulan Ini)', 'value' => 'Rp ' . number_format($poValueMonth, 0, ',', '.'), 'icon' => 'ki-wallet', 'color' => 'primary'],
                        ['label' => 'Active Organizations', 'value' => $activeOrganizations, 'icon' => 'ki-bank', 'color' => 'primary'],
                        ['label' => 'Active Users', 'value' => $activeUsers, 'icon' => 'ki-profile-user', 'color' => 'primary'],
                    ]
                ],
                [
                    'title' => 'Operations Status',
                    'icon' => 'ki-setting-2',
                    'color' => 'info',
                    'cards' => [
                        ['label' => 'PO Pending Approval', 'value' => $poPendingApproval, 'icon' => 'ki-timer', 'color' => 'warning'],
                        ['label' => 'PO In Progress', 'value' => $poInProgress, 'icon' => 'ki-delivery', 'color' => 'info'],
                        ['label' => 'PO Completed (Bulan Ini)', 'value' => $poCompleted, 'icon' => 'ki-check-circle', 'color' => 'success'],
                        ['label' => 'PO Rejected (Bulan Ini)', 'value' => $poRejected, 'icon' => 'ki-cross-circle', 'color' => 'danger'],
                    ]
                ],
                [
                    'title' => 'Financial Overview',
                    'icon' => 'ki-dollar',
                    'color' => 'success',
                    'cards' => [
                        ['label' => 'Total Receivable (AR)', 'value' => 'Rp ' . number_format($totalReceivable, 0, ',', '.'), 'icon' => 'ki-arrow-down', 'color' => 'success'],
                        ['label' => 'Total Payable (AP)', 'value' => 'Rp ' . number_format($totalPayable, 0, ',', '.'), 'icon' => 'ki-arrow-up', 'color' => 'danger'],
                        ['label' => 'Outstanding Invoice', 'value' => $outstandingInvoice, 'icon' => 'ki-bill', 'color' => 'warning'],
                        ['label' => 'Overdue Invoice', 'value' => $overdueInvoice, 'icon' => 'ki-information', 'color' => 'danger', 'alert' => true],
                    ]
                ],
                [
                    'title' => 'System Health',
                    'icon' => 'ki-shield-tick',
                    'color' => 'dark',
                    'cards' => [
                        ['label' => 'Failed Transactions', 'value' => $failedTransactions, 'icon' => 'ki-cross-circle', 'color' => 'danger', 'alert' => $failedTransactions > 0],
                        ['label' => 'Pending Actions', 'value' => $pendingActions, 'icon' => 'ki-information', 'color' => 'warning', 'alert' => $pendingActions > 0],
                        ['label' => 'System Errors (24h)', 'value' => $systemErrors, 'icon' => 'ki-shield-cross', 'color' => 'danger', 'alert' => $systemErrors > 0],
                        ['label' => 'Audit Logs Today', 'value' => $auditLogsToday, 'icon' => 'ki-file', 'color' => 'dark'],
                    ]
                ],
            ],
            'analytics' => $analytics,
            'recentActivity' => $recentActivity,
            'auditLogs' => $auditLogs,
            'alerts' => $alerts,
        ];
    }

    /**
     * GET SUPER ADMIN ANALYTICS DATA
     * Purpose: Product & Supplier analytics
     */
    private function getSuperAdminAnalytics(): array
    {
        // 1. TOP PRODUCTS (Most Purchased)
        $topProducts = DB::table('purchase_order_items')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->whereIn('purchase_orders.status', ['approved', 'shipped', 'delivered', 'completed'])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.category',
                'products.is_narcotic',
                DB::raw('SUM(purchase_order_items.quantity) as total_quantity'),
                DB::raw('SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total_value'),
                DB::raw('COUNT(DISTINCT purchase_orders.id) as order_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.category', 'products.is_narcotic')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // 2. TOP SUPPLIERS (Most Ordered From)
        $topSuppliers = DB::table('purchase_orders')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->whereIn('purchase_orders.status', ['approved', 'shipped', 'delivered', 'completed'])
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.email',
                'suppliers.phone',
                DB::raw('COUNT(purchase_orders.id) as order_count'),
                DB::raw('SUM(purchase_orders.total_amount) as total_value'),
                DB::raw('AVG(purchase_orders.total_amount) as avg_order_value')
            )
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.email', 'suppliers.phone')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();

        // 3. SLOW MOVING PRODUCTS (Rarely Purchased)
        $slowMovingProducts = DB::table('products')
            ->leftJoin('purchase_order_items', 'products.id', '=', 'purchase_order_items.product_id')
            ->leftJoin('purchase_orders', function($join) {
                $join->on('purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                     ->whereIn('purchase_orders.status', ['approved', 'shipped', 'delivered', 'completed'])
                     ->where('purchase_orders.created_at', '>=', now()->subMonths(6));
            })
            ->where('products.is_active', true)
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.category',
                'products.is_narcotic',
                DB::raw('COALESCE(SUM(purchase_order_items.quantity), 0) as total_quantity'),
                DB::raw('COALESCE(COUNT(DISTINCT purchase_orders.id), 0) as order_count'),
                DB::raw('MAX(purchase_orders.created_at) as last_purchase_date')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.category', 'products.is_narcotic')
            ->havingRaw('COALESCE(COUNT(DISTINCT purchase_orders.id), 0) <= 2')
            ->orderBy('order_count', 'asc')
            ->orderBy('last_purchase_date', 'asc')
            ->limit(10)
            ->get();

        // 4. TOTAL PURCHASE SUMMARY
        $purchaseSummary = [
            'total_quantity' => DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                ->whereIn('purchase_orders.status', ['approved', 'shipped', 'delivered', 'completed'])
                ->sum('purchase_order_items.quantity'),
            
            'total_value' => DB::table('purchase_orders')
                ->whereIn('status', ['approved', 'shipped', 'delivered', 'completed'])
                ->sum('total_amount'),
            
            'total_orders' => DB::table('purchase_orders')
                ->whereIn('status', ['approved', 'shipped', 'delivered', 'completed'])
                ->count(),
            
            'avg_order_value' => DB::table('purchase_orders')
                ->whereIn('status', ['approved', 'shipped', 'delivered', 'completed'])
                ->avg('total_amount'),
            
            'month_quantity' => DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                ->whereIn('purchase_orders.status', ['approved', 'shipped', 'delivered', 'completed'])
                ->whereMonth('purchase_orders.created_at', now()->month)
                ->whereYear('purchase_orders.created_at', now()->year)
                ->sum('purchase_order_items.quantity'),
            
            'month_value' => DB::table('purchase_orders')
                ->whereIn('status', ['approved', 'shipped', 'delivered', 'completed'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount'),
        ];

        // 5. PRODUCT RECOMMENDATIONS
        $recommendations = $this->generateProductRecommendations($topProducts, $slowMovingProducts);

        return [
            'topProducts' => $topProducts,
            'topSuppliers' => $topSuppliers,
            'slowMovingProducts' => $slowMovingProducts,
            'purchaseSummary' => $purchaseSummary,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * GENERATE PRODUCT RECOMMENDATIONS
     */
    private function generateProductRecommendations($topProducts, $slowMovingProducts): array
    {
        $recommendations = [];

        // Recommendation 1: Restock popular items
        if ($topProducts->count() > 0) {
            $topProduct = $topProducts->first();
            $recommendations[] = [
                'type' => 'restock',
                'priority' => 'high',
                'icon' => 'ki-arrow-up',
                'color' => 'success',
                'title' => 'Restock Produk Populer',
                'message' => "Produk '{$topProduct->name}' sangat diminati ({$topProduct->total_quantity} unit terjual). Pertimbangkan untuk menambah stok.",
                'product_id' => $topProduct->id,
                'product_name' => $topProduct->name,
            ];
        }

        // Recommendation 2: Review slow moving items
        if ($slowMovingProducts->count() > 0) {
            $slowProduct = $slowMovingProducts->first();
            $recommendations[] = [
                'type' => 'review',
                'priority' => 'medium',
                'icon' => 'ki-information',
                'color' => 'warning',
                'title' => 'Review Produk Slow Moving',
                'message' => "Produk '{$slowProduct->name}' jarang dibeli (hanya {$slowProduct->order_count} order dalam 6 bulan). Evaluasi kebutuhan stok.",
                'product_id' => $slowProduct->id,
                'product_name' => $slowProduct->name,
            ];
        }

        // Recommendation 3: Narcotic products alert
        $narcoticCount = DB::table('purchase_order_items')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('products.is_narcotic', true)
            ->whereIn('purchase_orders.status', ['approved', 'shipped', 'delivered'])
            ->whereMonth('purchase_orders.created_at', now()->month)
            ->sum('purchase_order_items.quantity');

        if ($narcoticCount > 0) {
            $recommendations[] = [
                'type' => 'alert',
                'priority' => 'high',
                'icon' => 'ki-shield-cross',
                'color' => 'danger',
                'title' => 'Monitoring Narkotika',
                'message' => "{$narcoticCount} unit produk narkotika dibeli bulan ini. Pastikan dokumentasi lengkap dan sesuai regulasi.",
            ];
        }

        // Recommendation 4: Supplier diversification
        $supplierCount = DB::table('purchase_orders')
            ->whereIn('status', ['approved', 'shipped', 'delivered', 'completed'])
            ->whereMonth('created_at', now()->month)
            ->distinct('supplier_id')
            ->count('supplier_id');

        if ($supplierCount < 3) {
            $recommendations[] = [
                'type' => 'diversify',
                'priority' => 'low',
                'icon' => 'ki-delivery',
                'color' => 'info',
                'title' => 'Diversifikasi Supplier',
                'message' => "Hanya {$supplierCount} supplier aktif bulan ini. Pertimbangkan untuk menambah supplier alternatif untuk mitigasi risiko.",
            ];
        }

        return $recommendations;
    }

    /**
     * BASIC DASHBOARD (Fallback)
     */
    private function getBasicDashboard(User $user): array
    {
        return [
            'role' => 'basic',
            'cards' => [],
            'alerts' => [],
        ];
    }
}
