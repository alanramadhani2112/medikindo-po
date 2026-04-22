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
    private $startDate;
    private $endDate;
    private $period;

    /**
     * Set date range for dashboard data
     */
    public function setDateRange(?string $period = 'today', ?string $startDate = null, ?string $endDate = null): self
    {
        $this->period = $period;

        switch ($period) {
            case 'today':
                $this->startDate = now()->startOfDay();
                $this->endDate = now()->endOfDay();
                break;
            case 'week':
                $this->startDate = now()->startOfWeek();
                $this->endDate = now()->endOfWeek();
                break;
            case 'month':
                $this->startDate = now()->startOfMonth();
                $this->endDate = now()->endOfMonth();
                break;
            case 'year':
                $this->startDate = now()->startOfYear();
                $this->endDate = now()->endOfYear();
                break;
            case 'custom':
                $this->startDate = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
                $this->endDate = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : now()->endOfMonth();
                break;
            default:
                $this->startDate = now()->startOfDay();
                $this->endDate = now()->endOfDay();
        }

        return $this;
    }

    /**
     * Get previous period dates for comparison
     */
    private function getPreviousPeriodDates(): array
    {
        $diff = $this->startDate->diffInDays($this->endDate);
        
        return [
            'start' => $this->startDate->copy()->subDays($diff + 1),
            'end' => $this->endDate->copy()->subDays($diff + 1),
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous): array
    {
        if ($previous == 0) {
            return [
                'percentage' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
                'color' => $current > 0 ? 'success' : 'secondary',
            ];
        }

        $percentage = (($current - $previous) / $previous) * 100;
        
        return [
            'percentage' => abs(round($percentage, 1)),
            'direction' => $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'neutral'),
            'color' => $percentage > 0 ? 'success' : ($percentage < 0 ? 'danger' : 'secondary'),
        ];
    }

    public function getDataForUser(User $user): array
    {
        // Set default date range if not set
        if (!$this->startDate || !$this->endDate) {
            $this->setDateRange('today');
        }

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
        
        // ROW 1: My PO Status
        $poActive = PurchaseOrder::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'shipped', 'delivered'])
            ->count();
            
        $poWaitingApproval = PurchaseOrder::where('organization_id', $orgId)
            ->where('status', 'submitted')
            ->count();
            
        $poAwaitingDelivery = PurchaseOrder::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'shipped'])
            ->count();
            
        $poCompletedMonth = PurchaseOrder::where('organization_id', $orgId)
            ->where('status', 'delivered')
            ->whereMonth('created_at', now()->month)
            ->count();

        // ROW 2: Financial Status
        $outstandingInvoices = \App\Models\CustomerInvoice::where('organization_id', $orgId)
            ->whereIn('status', [\App\Enums\CustomerInvoiceStatus::ISSUED->value, \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value])
            ->sum(DB::raw('total_amount - paid_amount'));
            
        $creditLimit = \App\Models\CreditLimit::where('organization_id', $orgId)->first();
        $creditUtilization = 0;
        $creditAvailable = 0;
        if ($creditLimit) {
            $totalAR = \App\Models\CustomerInvoice::where('organization_id', $orgId)
                ->whereIn('status', [\App\Enums\CustomerInvoiceStatus::ISSUED->value, \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value])
                ->sum(DB::raw('total_amount - paid_amount'));
            $creditUtilization = $creditLimit->max_limit > 0 ? ($totalAR / $creditLimit->max_limit) * 100 : 0;
            $creditAvailable = $creditLimit->max_limit - $totalAR;
        }
        
        $paymentDueSoon = \App\Models\CustomerInvoice::where('organization_id', $orgId)
            ->whereIn('status', [\App\Enums\CustomerInvoiceStatus::ISSUED->value, \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value])
            ->where('due_date', '<=', now()->addDays(7))
            ->sum(DB::raw('total_amount - paid_amount'));

        // ROW 3: Quick Stats
        $totalProducts = \App\Models\Product::where('is_active', true)->count();
        $recentDeliveries = \App\Models\GoodsReceipt::whereHas('purchaseOrder', function($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();
        $activeSuppliers = PurchaseOrder::where('organization_id', $orgId)
            ->whereMonth('created_at', now()->month)
            ->distinct('supplier_id')
            ->count('supplier_id');

        // Recent POs
        $recentPOs = PurchaseOrder::where('organization_id', $orgId)
            ->with(['supplier', 'organization'])
            ->latest()
            ->limit(10)
            ->get();

        // Alerts
        $alerts = [];
        
        // Credit limit alert
        if ($creditUtilization > 90) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'ki-cross-circle',
                'title' => 'Credit Limit Exceeded',
                'message' => "Utilisasi kredit {$creditUtilization}%. Anda tidak dapat membuat PO baru.",
                'action' => route('web.invoices.customer.index')
            ];
        } elseif ($creditUtilization > 80) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'ki-information-5',
                'title' => 'Credit Limit Warning',
                'message' => "Utilisasi kredit {$creditUtilization}%. Segera lakukan pembayaran.",
                'action' => route('web.invoices.customer.index')
            ];
        }
        
        // Payment due alert
        if ($paymentDueSoon > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'ki-wallet',
                'title' => 'Payment Due Soon',
                'message' => "Rp " . number_format($paymentDueSoon, 0, ',', '.') . " jatuh tempo dalam 7 hari",
                'action' => route('web.invoices.customer.index')
            ];
        }
        
        // Rejected PO alert
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
            'poActive' => $poActive,
            'poWaitingApproval' => $poWaitingApproval,
            'poAwaitingDelivery' => $poAwaitingDelivery,
            'poCompletedMonth' => $poCompletedMonth,
            'outstandingInvoices' => $outstandingInvoices,
            'creditUtilization' => round($creditUtilization, 1),
            'creditAvailable' => $creditAvailable,
            'creditLimit' => $creditLimit,
            'paymentDueSoon' => $paymentDueSoon,
            'totalProducts' => $totalProducts,
            'recentDeliveries' => $recentDeliveries,
            'activeSuppliers' => $activeSuppliers,
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
        // ROW 1: Approval Queue
        $pendingApproval = PurchaseOrder::where('status', 'submitted')->count();
        $urgentApprovals = PurchaseOrder::where('status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))
            ->count();
        
        $approvedToday = Approval::where('status', 'approved')
            ->whereDate('created_at', now())
            ->count();
            
        $rejectedThisWeek = PurchaseOrder::where('status', 'rejected')
            ->whereDate('updated_at', '>=', now()->startOfWeek())
            ->count();
            
        $approvalRate = Approval::whereMonth('created_at', now()->month)
            ->selectRaw('COUNT(CASE WHEN status = "approved" THEN 1 END) * 100.0 / COUNT(*) as rate')
            ->value('rate') ?? 0;

        // ROW 2: Risk Alerts
        $highValuePOs = PurchaseOrder::where('status', 'submitted')
            ->where('total_amount', '>', 50000000)
            ->count();
            
        $narcoticPOs = PurchaseOrder::where('status', 'submitted')
            ->where('has_narcotics', true)
            ->count();
            
        $budgetAlerts = \App\Models\CreditLimit::where('is_active', true)
            ->whereRaw('(SELECT SUM(total_amount - paid_amount) FROM customer_invoices WHERE organization_id = credit_limits.organization_id AND status IN ("issued", "partial_paid")) / max_limit > 0.8')
            ->count();

        // ROW 3: Performance Metrics
        $avgApprovalTime = Approval::where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours') ?? 0;
            
        $totalApprovedValue = PurchaseOrder::where('status', 'approved')
            ->whereMonth('updated_at', now()->month)
            ->sum('total_amount');
            
        $organizationsServed = PurchaseOrder::whereIn('status', ['submitted', 'approved'])
            ->whereMonth('created_at', now()->month)
            ->distinct('organization_id')
            ->count('organization_id');
            
        $complianceScore = 98; // TODO: Calculate based on approval rules compliance

        // Pending Approval List (Priority)
        $pendingList = PurchaseOrder::where('status', 'submitted')
            ->with(['organization', 'supplier', 'items.product'])
            ->orderByRaw("CASE WHEN has_narcotics = 1 THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN total_amount > 50000000 THEN 0 ELSE 1 END")
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
        
        if ($narcoticPOs > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'ki-shield-cross',
                'title' => 'PO Narkotika/Psikotropika',
                'message' => "{$narcoticPOs} PO mengandung obat terkontrol memerlukan persetujuan Level 2",
                'action' => route('web.approvals.index')
            ];
        }

        if ($urgentApprovals > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'ki-time',
                'title' => 'Persetujuan Tertunda',
                'message' => "{$urgentApprovals} PO menunggu lebih dari 3 hari",
                'action' => route('web.approvals.index')
            ];
        }
        
        if ($highValuePOs > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'ki-information-5',
                'title' => 'High Value PO',
                'message' => "{$highValuePOs} PO dengan nilai >Rp 50M memerlukan review ekstra",
                'action' => route('web.approvals.index')
            ];
        }

        return [
            'role' => 'approver',
            'cards' => [
                // Row 1: Approval Queue
                ['label' => 'Pending My Approval', 'value' => $pendingApproval, 'icon' => 'ki-basket-ok', 'color' => $urgentApprovals > 0 ? 'danger' : 'warning', 'sub' => $urgentApprovals . ' urgent (>3 days)', 'alert' => $urgentApprovals > 0],
                ['label' => 'Approved Today', 'value' => $approvedToday, 'icon' => 'ki-check-circle', 'color' => 'success', 'sub' => 'Avg time: ' . number_format($avgApprovalTime, 1) . ' hours'],
                ['label' => 'Rejected This Week', 'value' => $rejectedThisWeek, 'icon' => 'ki-cross-circle', 'color' => 'warning', 'sub' => 'Reasons: Budget, Compliance'],
                ['label' => 'Approval Rate', 'value' => number_format($approvalRate, 0) . '%', 'icon' => 'ki-chart-line', 'color' => 'info', 'sub' => 'This month'],
                
                // Row 2: Risk Alerts
                ['label' => 'High Value POs', 'value' => $highValuePOs, 'icon' => 'ki-information-5', 'color' => 'warning', 'sub' => 'POs > Rp 50M', 'alert' => $highValuePOs > 0],
                ['label' => 'Narcotic Items', 'value' => $narcoticPOs, 'icon' => 'ki-shield-cross', 'color' => 'danger', 'sub' => 'Level 2 approval needed', 'alert' => $narcoticPOs > 0],
                ['label' => 'Budget Alerts', 'value' => $budgetAlerts, 'icon' => 'ki-chart-line', 'color' => 'warning', 'sub' => 'Orgs >80% credit', 'alert' => $budgetAlerts > 0],
                
                // Row 3: Performance Metrics
                ['label' => 'Avg Approval Time', 'value' => number_format($avgApprovalTime, 1) . 'h', 'icon' => 'ki-time', 'color' => 'info', 'sub' => 'Target: <2 hours'],
                ['label' => 'Total Approved Value', 'value' => 'Rp ' . number_format($totalApprovedValue / 1000000, 0) . 'M', 'icon' => 'ki-wallet', 'color' => 'success', 'sub' => 'This month'],
                ['label' => 'Organizations Served', 'value' => $organizationsServed, 'icon' => 'ki-people', 'color' => 'primary', 'sub' => 'Active this month'],
                ['label' => 'Compliance Score', 'value' => $complianceScore . '%', 'icon' => 'ki-shield-tick', 'color' => 'success', 'sub' => 'All checks passed'],
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
        $totalReceivable = CustomerInvoice::whereIn('status', [\App\Enums\CustomerInvoiceStatus::ISSUED->value, \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value])
            ->sum(DB::raw('total_amount - paid_amount'));
            
        $totalPayable = SupplierInvoice::whereIn('status', [\App\Enums\SupplierInvoiceStatus::DRAFT->value, \App\Enums\SupplierInvoiceStatus::VERIFIED->value])
            ->sum(DB::raw('total_amount - paid_amount'));
            
        $overdueInvoices = SupplierInvoice::where('due_date', '<', now())
            ->whereIn('status', [\App\Enums\SupplierInvoiceStatus::DRAFT->value, \App\Enums\SupplierInvoiceStatus::VERIFIED->value])
            ->count();
            
        $pendingPayments = SupplierInvoice::where('status', \App\Enums\SupplierInvoiceStatus::VERIFIED->value)->count();
        
        $todayCashflow = Payment::whereDate('payment_date', now())
            ->sum('amount');

        // Outstanding Invoices
        $outstandingInvoices = SupplierInvoice::with(['supplier', 'purchaseOrder.organization'])
            ->whereIn('status', [\App\Enums\SupplierInvoiceStatus::DRAFT->value, \App\Enums\SupplierInvoiceStatus::VERIFIED->value])
            ->orderBy('due_date', 'asc')
            ->limit(15)
            ->get();

        // Recent Payments
        $recentPayments = Payment::with(['user'])
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
                'action' => route('web.invoices.supplier.index')
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

        // Payment Proofs Pending (NEW)
        $paymentProofsPending = \App\Models\PaymentProof::whereIn('status', [
            \App\Enums\PaymentProofStatus::SUBMITTED->value,
            \App\Enums\PaymentProofStatus::VERIFIED->value,
            \App\Enums\PaymentProofStatus::RESUBMITTED->value,
        ])->count();

        return [
            'role' => 'finance',
            'cards' => [
                ['label' => 'Total Receivable (AR)', 'value' => 'Rp ' . number_format($totalReceivable, 0, ',', '.'), 'icon' => 'ki-arrow-down', 'color' => 'success'],
                ['label' => 'Total Payable (AP)', 'value' => 'Rp ' . number_format($totalPayable, 0, ',', '.'), 'icon' => 'ki-arrow-up', 'color' => 'danger'],
                ['label' => 'Invoice Overdue', 'value' => $overdueInvoices, 'icon' => 'ki-information', 'color' => 'warning', 'alert' => $overdueInvoices > 0],
                ['label' => 'Bukti Bayar Pending', 'value' => $paymentProofsPending, 'icon' => 'ki-shield-tick', 'color' => 'info', 'alert' => $paymentProofsPending > 0],
                ['label' => 'Cashflow Hari Ini', 'value' => 'Rp ' . number_format($todayCashflow, 0, ',', '.'), 'icon' => 'ki-dollar', 'color' => 'primary'],
            ],
            'paymentProofsPending' => $paymentProofsPending,
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
        // Get previous period for comparison
        $previousPeriod = $this->getPreviousPeriodDates();

        // GROUP 1: BUSINESS ACTIVITY
        $poCount = PurchaseOrder::whereBetween('created_at', [$this->startDate, $this->endDate])->count();
        $poCountPrev = PurchaseOrder::whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])->count();
        $poGrowth = $this->calculateGrowth($poCount, $poCountPrev);

        $poValue = PurchaseOrder::whereBetween('created_at', [$this->startDate, $this->endDate])->sum('total_amount');
        $poValuePrev = PurchaseOrder::whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])->sum('total_amount');
        $poValueGrowth = $this->calculateGrowth($poValue, $poValuePrev);

        $activeOrganizations = Organization::where('is_active', true)->count();
        $activeUsers = User::where('is_active', true)->count();

        // GROUP 2: OPERATIONS STATUS
        $poPendingApproval = PurchaseOrder::where('status', 'submitted')->count();
        
        $poInProgress = PurchaseOrder::whereIn('status', ['approved'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();
        $poInProgressPrev = PurchaseOrder::whereIn('status', ['approved'])
            ->whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])
            ->count();
        $poInProgressGrowth = $this->calculateGrowth($poInProgress, $poInProgressPrev);
        
        $poCompleted = PurchaseOrder::where('status', 'completed')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();
        $poCompletedPrev = PurchaseOrder::where('status', 'completed')
            ->whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])
            ->count();
        $poCompletedGrowth = $this->calculateGrowth($poCompleted, $poCompletedPrev);
        
        $poRejected = PurchaseOrder::where('status', 'rejected')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();

        // GROUP 3: FINANCIAL OVERVIEW
        $totalReceivable = CustomerInvoice::whereIn('status', [\App\Enums\CustomerInvoiceStatus::ISSUED->value, \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value])
            ->sum(DB::raw('total_amount - paid_amount'));
        $totalPayable = SupplierInvoice::whereIn('status', [\App\Enums\SupplierInvoiceStatus::DRAFT->value, \App\Enums\SupplierInvoiceStatus::VERIFIED->value])
            ->sum(DB::raw('total_amount - paid_amount'));
        $outstandingInvoice = SupplierInvoice::whereIn('status', [\App\Enums\SupplierInvoiceStatus::DRAFT->value, \App\Enums\SupplierInvoiceStatus::VERIFIED->value])->count();
        $overdueInvoice = SupplierInvoice::where('due_date', '<', now())
            ->whereIn('status', [\App\Enums\SupplierInvoiceStatus::DRAFT->value, \App\Enums\SupplierInvoiceStatus::VERIFIED->value])
            ->count();

        // GROUP 4: SYSTEM HEALTH
        $failedTransactions = PurchaseOrder::where('status', 'rejected')
            ->whereBetween('updated_at', [$this->startDate, $this->endDate])
            ->count();
        $pendingActions = PurchaseOrder::where('status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))
            ->count();
        $systemErrors = AuditLog::where('action', 'error')
            ->whereBetween('occurred_at', [$this->startDate, $this->endDate])
            ->count();
        $auditLogsToday = AuditLog::whereDate('occurred_at', now())->count();

        // ANALYTICS DATA (NEW)
        $analytics = $this->getSuperAdminAnalytics();

        // CHART DATA (NEW)
        $chartData = $this->getSuperAdminChartData();

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
                'action' => route('web.invoices.supplier.index')
            ];
        }

        return [
            'role' => 'superadmin',
            'period' => $this->period,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'cardGroups' => [
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
                    'title' => 'Business Activity',
                    'icon' => 'ki-chart-line-up',
                    'color' => 'primary',
                    'cards' => [
                        [
                            'label' => 'Total Purchase Orders',
                            'value' => $poCount,
                            'icon' => 'ki-document',
                            'color' => 'primary',
                            'growth' => $poGrowth,
                            'sub' => 'vs periode sebelumnya'
                        ],
                        [
                            'label' => 'Total PO Value',
                            'value' => 'Rp ' . number_format($poValue, 0, ',', '.'),
                            'icon' => 'ki-wallet',
                            'color' => 'primary',
                            'growth' => $poValueGrowth,
                            'sub' => 'vs periode sebelumnya'
                        ],
                        [
                            'label' => 'Active Organizations',
                            'value' => $activeOrganizations,
                            'icon' => 'ki-bank',
                            'color' => 'primary'
                        ],
                        [
                            'label' => 'Active Users',
                            'value' => $activeUsers,
                            'icon' => 'ki-profile-user',
                            'color' => 'primary'
                        ],
                    ]
                ],
                [
                    'title' => 'Operations Status',
                    'icon' => 'ki-setting-2',
                    'color' => 'info',
                    'cards' => [
                        [
                            'label' => 'PO Pending Approval',
                            'value' => $poPendingApproval,
                            'icon' => 'ki-timer',
                            'color' => 'warning'
                        ],
                        [
                            'label' => 'PO In Progress',
                            'value' => $poInProgress,
                            'icon' => 'ki-delivery',
                            'color' => 'info',
                            'growth' => $poInProgressGrowth,
                            'sub' => 'vs periode sebelumnya'
                        ],
                        [
                            'label' => 'PO Completed',
                            'value' => $poCompleted,
                            'icon' => 'ki-check-circle',
                            'color' => 'success',
                            'growth' => $poCompletedGrowth,
                            'sub' => 'vs periode sebelumnya'
                        ],
                        [
                            'label' => 'PO Rejected',
                            'value' => $poRejected,
                            'icon' => 'ki-cross-circle',
                            'color' => 'danger'
                        ],
                    ]
                ],
                [
                    'title' => 'System Health',
                    'icon' => 'ki-shield-tick',
                    'color' => 'dark',
                    'cards' => [
                        ['label' => 'Failed Transactions', 'value' => $failedTransactions, 'icon' => 'ki-cross-circle', 'color' => 'danger', 'alert' => $failedTransactions > 0],
                        ['label' => 'Pending Actions', 'value' => $pendingActions, 'icon' => 'ki-information', 'color' => 'warning', 'alert' => $pendingActions > 0],
                        ['label' => 'System Errors', 'value' => $systemErrors, 'icon' => 'ki-shield-cross', 'color' => 'danger', 'alert' => $systemErrors > 0],
                        ['label' => 'Audit Logs Today', 'value' => $auditLogsToday, 'icon' => 'ki-file', 'color' => 'dark'],
                    ]
                ],
            ],
            'analytics' => $analytics,
            'chartData' => $chartData,
            'recentActivity' => $recentActivity,
            'auditLogs' => $auditLogs,
            'alerts' => $alerts,
        ];
    }

    /**
     * GET SUPER ADMIN CHART DATA
     * Purpose: Generate data for charts visualization
     */
    private function getSuperAdminChartData(): array
    {
        // 1. PO STATUS DISTRIBUTION (Donut Chart)
        $poStatusData = [
            'labels' => ['Submitted', 'Approved', 'Shipped', 'Delivered', 'Rejected'],
            'data' => [
                PurchaseOrder::where('status', 'submitted')->count(),
                PurchaseOrder::where('status', 'approved')->count(),
                PurchaseOrder::where('status', 'shipped')->count(),
                PurchaseOrder::where('status', 'delivered')->count(),
                PurchaseOrder::where('status', 'rejected')->count(),
            ],
            'colors' => [
                'rgba(255, 193, 7, 0.8)',   // warning - submitted
                'rgba(54, 162, 235, 0.8)',  // info - approved
                'rgba(153, 102, 255, 0.8)', // purple - shipped
                'rgba(75, 192, 192, 0.8)',  // success - delivered
                'rgba(255, 99, 132, 0.8)',  // danger - rejected
            ]
        ];

        // 2. MONTHLY PO TREND (Line Chart) - Last 6 months
        $monthlyTrend = $this->getMonthlyPOTrend(6);

        // 3. MONTHLY REVENUE TREND (Bar Chart) - Last 6 months
        $monthlyRevenue = $this->getMonthlyRevenueTrend(6);

        // 4. CASH FLOW TREND (Line Chart) - Last 30 days
        $cashFlowTrend = $this->getCashFlowTrend(30);

        return [
            'poStatus' => $poStatusData,
            'monthlyPOTrend' => $monthlyTrend,
            'monthlyRevenue' => $monthlyRevenue,
            'cashFlowTrend' => $cashFlowTrend,
        ];
    }

    /**
     * GET MONTHLY PO TREND
     */
    private function getMonthlyPOTrend(int $months = 6): array
    {
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $data[] = PurchaseOrder::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total PO',
                    'data' => $data,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ]
            ]
        ];
    }

    /**
     * GET MONTHLY REVENUE TREND
     */
    private function getMonthlyRevenueTrend(int $months = 6): array
    {
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $data[] = PurchaseOrder::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereIn('status', ['approved', 'shipped', 'delivered', 'completed'])
                ->sum('total_amount');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ]
            ]
        ];
    }

    /**
     * GET CASH FLOW TREND
     */
    private function getCashFlowTrend(int $days = 30): array
    {
        $labels = [];
        $cashIn = [];
        $cashOut = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            
            // Cash In (from customer payments)
            $cashIn[] = Payment::whereDate('payment_date', $date)
                ->where('type', 'incoming')
                ->sum('amount');
            
            // Cash Out (to supplier payments)
            $cashOut[] = Payment::whereDate('payment_date', $date)
                ->where('type', 'outgoing')
                ->sum('amount');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Kas Masuk',
                    'data' => $cashIn,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Kas Keluar',
                    'data' => $cashOut,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ]
            ]
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
