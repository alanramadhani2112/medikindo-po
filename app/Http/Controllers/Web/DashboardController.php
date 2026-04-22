<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\DashboardService;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        // Get period parameters
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Set date range in dashboard service
        $this->dashboardService->setDateRange($period, $startDate, $endDate);

        // Get dashboard data
        $data = $this->dashboardService->getDataForUser($request->user());
        $data['breadcrumbs'] = [];
        $data['currentPeriod'] = $period;
        
        return view('dashboard.role-based', $data);
    }

    /**
     * View for Detailed Financial Status.
     */
    public function finance(Request $request)
    {
        if (! $request->user()->can('view_invoice')) {
            abort(403);
        }

        $user = $request->user();

        $arQuery = \App\Models\CustomerInvoice::when(! $user->hasRole('Super Admin'), fn($q) => $q->where('organization_id', $user->organization_id));
        $apQuery = \App\Models\SupplierInvoice::when(! $user->hasRole('Super Admin'), fn($q) => $q->whereHas('purchaseOrder', fn($pq) => $pq->where('organization_id', $user->organization_id)));

        $arInvoices = (clone $arQuery)->with(['organization', 'purchaseOrder'])->latest('due_date')->paginate(15)->withQueryString();
        $apInvoices = (clone $apQuery)->with(['supplier', 'purchaseOrder'])->latest('due_date')->paginate(15)->withQueryString();

        // Summaries
        $stats = [
            'ar_total'      => (clone $arQuery)->sum(\DB::raw('total_amount - paid_amount')),
            'ap_total'      => (clone $apQuery)->sum(\DB::raw('total_amount - paid_amount')),
            'ar_overdue'    => (clone $arQuery)->where('due_date', '<', now())->whereIn('status', ['issued', 'partial_paid'])->count(),
            'ap_overdue'    => (clone $apQuery)->where('due_date', '<', now())->whereIn('status', ['draft', 'verified'])->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Finance Overview']
        ];

        return view('dashboard.finance', compact('arInvoices', 'apInvoices', 'stats', 'breadcrumbs'));
    }

    public function audit(Request $request)
    {
        if (! $request->user()->can('view_audit')) {
            abort(403);
        }

        $user = $request->user();

        $logs = \App\Models\AuditLog::with('user')
            ->when(! $user->hasRole('Super Admin'), fn($q) => $q->where('organization_id', $user->organization_id))
            ->latest('occurred_at')
            ->paginate(30)
            ->withQueryString();

        $breadcrumbs = [
            ['label' => 'Audit Logs']
        ];

        return view('dashboard.audit', compact('logs', 'breadcrumbs'));
    }
}
