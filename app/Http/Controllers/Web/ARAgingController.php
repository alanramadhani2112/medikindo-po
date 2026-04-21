<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerInvoice;
use App\Enums\CustomerInvoiceStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ARAgingController extends Controller
{
    /**
     * GET /ar-aging
     *
     * Classify outstanding AR invoices into 4 aging buckets:
     *   current  → not yet overdue (green)
     *   1-30     → 1–30 days overdue (yellow)
     *   31-60    → 31–60 days overdue (orange)
     *   61-90    → 61–90 days overdue (red)
     *   90+      → >90 days overdue (dark red)
     */
    public function index(Request $request): View
    {
        $user   = $request->user();
        $today  = now()->startOfDay();
        $search = $request->get('search');
        $bucket = $request->get('bucket');

        $query = CustomerInvoice::with(['organization'])
            ->whereIn('status', [
                CustomerInvoiceStatus::ISSUED->value,
                CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])
            ->whereNotNull('due_date');

        if (! $user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('organization', fn($o) => $o->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->get();

        $buckets = [
            'current' => collect(),
            '1-30'    => collect(),
            '31-60'   => collect(),
            '61-90'   => collect(),
            '90+'     => collect(),
        ];

        foreach ($invoices as $invoice) {
            $buckets[$invoice->aging_bucket]->push($invoice);
        }

        $totals = collect($buckets)->map(
            fn($group) => $group->sum(fn($i) => $i->outstanding_amount)
        )->toArray();

        $grandTotal = array_sum($totals);

        $breadcrumbs = [
            ['label' => 'Finance', 'url' => 'javascript:void(0)'],
            ['label' => 'AR Aging Report'],
        ];

        return view('ar-aging.index', compact('buckets', 'totals', 'grandTotal', 'breadcrumbs'));
    }
}
