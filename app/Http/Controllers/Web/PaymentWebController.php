<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomingPaymentRequest;
use App\Http\Requests\StoreOutgoingPaymentRequest;
use App\Models\CustomerInvoice;
use App\Models\Payment;
use App\Models\SupplierInvoice;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentWebController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(Request $request)
    {
        $user   = $request->user();
        $tab    = $request->get('tab', 'all');
        $type   = $request->get('type');
        $search = $request->get('search');

        $query = Payment::with(['organization', 'supplier', 'allocations.supplierInvoice', 'allocations.customerInvoice'])
            ->when(! $user->hasRole('Super Admin'), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('organization_id', $user->organization_id)
                        ->orWhereHas('allocations.supplierInvoice.purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id))
                        ->orWhereHas('allocations.customerInvoice', fn($ci) => $ci->where('organization_id', $user->organization_id));
                });
            });

        // Tab Filtering
        if ($tab === 'incoming') {
            $query->where('type', 'incoming');
        } elseif ($tab === 'outgoing') {
            $query->where('type', 'outgoing');
        } elseif ($tab === 'pending') {
            $query->where('status', 'pending');
        } elseif ($tab === 'confirmed') {
            $query->whereIn('status', ['confirmed', 'completed']);
        }

        // Type Filtering (from filter bar)
        if ($type === 'incoming') {
            $query->where('type', 'incoming');
        } elseif ($type === 'outgoing') {
            $query->where('type', 'outgoing');
        }

        // Search Filtering
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('organization', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        // Totals & Balance (Scoped)
        $scopedBase = Payment::query()
            ->when(! $user->hasRole('Super Admin'), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('organization_id', $user->organization_id)
                        ->orWhereHas('allocations.supplierInvoice.purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id))
                        ->orWhereHas('allocations.customerInvoice', fn($ci) => $ci->where('organization_id', $user->organization_id));
                });
            });

        $totalIn  = (clone $scopedBase)->where('type', 'incoming')->whereIn('status', ['confirmed', 'completed'])->sum('amount');
        $totalOut = (clone $scopedBase)->where('type', 'outgoing')->whereIn('status', ['confirmed', 'completed'])->sum('amount');
        $balance  = $totalIn - $totalOut;

        // Counts for badges
        $counts = [
            'all'       => (clone $scopedBase)->count(),
            'incoming'  => (clone $scopedBase)->where('type', 'incoming')->count(),
            'outgoing'  => (clone $scopedBase)->where('type', 'outgoing')->count(),
            'pending'   => (clone $scopedBase)->where('status', 'pending')->count(),
            'confirmed' => (clone $scopedBase)->whereIn('status', ['confirmed', 'completed'])->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Finance', 'url' => 'javascript:void(0)'],
            ['label' => 'Buku Kas & Pembayaran']
        ];

        return view('payments.index', compact('payments', 'totalIn', 'totalOut', 'balance', 'counts', 'breadcrumbs', 'tab'));
    }

    public function createIncoming(Request $request)
    {
        // Prevent seeing others' invoices if Organization User
        $user = $request->user();
        
        $invoices = CustomerInvoice::with(['organization'])
            ->where('status', '!=', 'paid')
            ->whereRaw('paid_amount < total_amount')
            ->when(! $user->hasRole('Super Admin'), fn($q) => $q->where('organization_id', $user->organization_id))
            ->get();
            
        $breadcrumbs = [
            ['label' => 'Finance', 'url' => 'javascript:void(0)'],
            ['label' => 'Buku Kas & Pembayaran', 'url' => route('web.payments.index')],
            ['label' => 'Terima Pembayaran']
        ];
            
        return view('payments.create_incoming', compact('invoices', 'breadcrumbs'));
    }

    public function createOutgoing(Request $request)
    {
        $invoices = SupplierInvoice::with(['supplier'])
            ->where('status', '!=', 'paid')
            ->whereRaw('paid_amount < total_amount')
            ->get();
            
        $breadcrumbs = [
            ['label' => 'Finance', 'url' => 'javascript:void(0)'],
            ['label' => 'Buku Kas & Pembayaran', 'url' => route('web.payments.index')],
            ['label' => 'Kirim Pembayaran']
        ];
            
        return view('payments.create_outgoing', compact('invoices', 'breadcrumbs'));
    }

    public function storeIncoming(StoreIncomingPaymentRequest $request)
    {
        $data = $request->validated();
        $invoice = CustomerInvoice::findOrFail($data['customer_invoice_id']);

        try {
            $payment = $this->paymentService->processIncomingPayment($data, $invoice);
            return redirect()->route('web.payments.index')
                ->with('success', "Pemasukan kas sejumlah Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil direkam ke Invoice {$invoice->invoice_number}.");
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function storeOutgoing(StoreOutgoingPaymentRequest $request)
    {
        $data = $request->validated();
        $invoice = SupplierInvoice::findOrFail($data['supplier_invoice_id']);

        try {
            $payment = $this->paymentService->processOutgoingPayment($data, $invoice);
            return redirect()->route('web.payments.index')
                ->with('success', "Pengeluaran kas sejumlah Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dikirimkan mereferensi Invoice {$invoice->invoice_number}.");
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
