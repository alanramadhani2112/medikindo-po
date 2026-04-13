<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Dashboard: aggregate stats for the authenticated user's scope.
     * Super Admin sees system-wide; others see own clinic.
     */
    public function dashboard(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PurchaseOrder::class);

        $user  = $request->user();
        $query = PurchaseOrder::query()
            ->when(! $user->isSuperAdmin(), fn($q) => $q->where('organization_id', $user->organization_id));

        // PO counts by status
        $byStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses appear (even if zero)
        $statusMap = array_merge(array_fill_keys([
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_SUBMITTED,
            PurchaseOrder::STATUS_SUBMITTED,
            PurchaseOrder::STATUS_APPROVED,
            PurchaseOrder::STATUS_REJECTED,
            PurchaseOrder::STATUS_SHIPPED,
        ], 0), $byStatus);

        // Total spend (approved + sent)
        $totalSpend = (clone $query)
            ->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_SHIPPED])
            ->sum('total_amount');

        $thisMonthSpend = (clone $query)
            ->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_SHIPPED])
            ->whereBetween('approved_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount');

        $lastMonthSpend = (clone $query)
            ->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_SHIPPED])
            ->whereBetween('approved_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->sum('total_amount');

        // POs pending approval (approver queue)
        $pendingApproval = (clone $query)
            ->where('status', PurchaseOrder::STATUS_SUBMITTED)
            ->count();

        // Narcotics POs
        $narcoticsPOs = (clone $query)
            ->where('has_narcotics', true)
            ->count();

        // Top 5 suppliers by total PO value (approved/sent)
        $topSuppliers = (clone $query)
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->whereIn('purchase_orders.status', [
                PurchaseOrder::STATUS_APPROVED,
                PurchaseOrder::STATUS_SHIPPED,
            ])
            ->select(
                'suppliers.id',
                'suppliers.name',
                DB::raw('SUM(purchase_orders.total_amount) as total_value'),
                DB::raw('COUNT(purchase_orders.id) as po_count')
            )
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_value')
            ->limit(5)
            ->get();

        return response()->json([
            'po_by_status'        => $statusMap,
            'pending_approval'    => $pendingApproval,
            'narcotics_pos'       => $narcoticsPOs,
            'spend'               => [
                'total'      => (float) $totalSpend,
                'this_month' => (float) $thisMonthSpend,
                'last_month' => (float) $lastMonthSpend,
            ],
            'top_suppliers'       => $topSuppliers,
        ]);
    }

    /**
     * PO Summary report — filterable, paginated table view.
     */
    public function poSummary(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PurchaseOrder::class);

        $request->validate([
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
            'organization_id'   => ['nullable', 'integer', 'exists:organizations,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'status'      => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $pos = PurchaseOrder::with(['organization:id,name', 'supplier:id,name', 'creator:id,name'])
            ->when(! $user->isSuperAdmin(), fn($q) => $q->where('organization_id', $user->organization_id))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->organization_id, fn($q, $id) => $q->where('organization_id', $id))
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->has('narcotics'), fn($q) => $q->where('has_narcotics', true))
            ->orderByDesc('created_at')
            ->paginate(25);

        // Aggregate totals for the filtered set (without pagination)
        $totalsQuery = PurchaseOrder::query()
            ->when(! $user->isSuperAdmin(), fn($q) => $q->where('organization_id', $user->organization_id))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->organization_id, fn($q, $id) => $q->where('organization_id', $id))
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s));

        $totals = [
            'count'        => $totalsQuery->count(),
            'total_amount' => (float) $totalsQuery->sum('total_amount'),
        ];

        return response()->json([
            'data'    => $pos->items(),
            'meta'    => [
                'current_page' => $pos->currentPage(),
                'last_page'    => $pos->lastPage(),
                'per_page'     => $pos->perPage(),
                'total'        => $pos->total(),
            ],
            'totals'  => $totals,
        ]);
    }
}

