<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerInvoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ARAgingController extends Controller
{
    /**
     * GET /ar-aging
     *
     * Classify outstanding invoices into aging buckets:
     *   0-30 days  → current  (green)
     *   31-60 days → warning  (yellow)
     *   >60 days   → overdue  (red)
     *
     * Excludes PAID and VOID invoices.
     */
    public function index(Request $request): View
    {
        $today = now()->startOfDay();

        $invoices = CustomerInvoice::with(['organization'])
            ->whereNotIn('status', [CustomerInvoice::STATUS_PAID, CustomerInvoice::STATUS_VOID])
            ->whereNotNull('due_date')
            ->get();

        $buckets = [
            'current' => collect(),
            'warning' => collect(),
            'overdue' => collect(),
        ];

        foreach ($invoices as $invoice) {
            $daysDiff = $today->diffInDays($invoice->due_date, false);
            // diffInDays with false: positive = due_date is in the future, negative = past due
            $daysOverdue = -$daysDiff; // positive means overdue

            if ($daysOverdue <= 30) {
                $buckets['current']->push($invoice);
            } elseif ($daysOverdue <= 60) {
                $buckets['warning']->push($invoice);
            } else {
                $buckets['overdue']->push($invoice);
            }
        }

        $totals = [
            'current' => $buckets['current']->sum(fn($i) => (float) $i->total_amount - (float) $i->paid_amount),
            'warning' => $buckets['warning']->sum(fn($i) => (float) $i->total_amount - (float) $i->paid_amount),
            'overdue' => $buckets['overdue']->sum(fn($i) => (float) $i->total_amount - (float) $i->paid_amount),
        ];

        return view('ar-aging.index', compact('buckets', 'totals'));
    }
}
