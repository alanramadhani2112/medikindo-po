<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerInvoiceRequest;
use App\Http\Requests\StoreSupplierInvoiceRequest;
use App\Http\Requests\StoreInvoiceFromGRRequest;
use App\Models\CustomerInvoice;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use App\Models\Organization;
use App\Services\InvoiceService;
use App\Services\InvoiceFromGRService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceWebController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly InvoiceFromGRService $invoiceFromGRService
    ) {}

    public function index(Request $request)
    {
        $tab    = $request->get('tab', 'supplier');
        $user   = $request->user();

        if ($tab === 'customer') {
            $query = CustomerInvoice::with(['organization', 'purchaseOrder'])
                ->filter($request, [
                    'search_column' => function($q, $s) {
                        $q->where('invoice_number', 'like', "%{$s}%")
                          ->orWhereHas('organization', fn($c) => $c->where('name', 'like', "%{$s}%"));
                    },
                    'status' => 'status',
                ]);

            if (! $user->hasRole('Super Admin')) {
                $query->where('organization_id', $user->organization_id);
            }

            // Standardize filtering for unpaid/paid/overdue using the filter mappings or simple where
            if ($request->status === 'unpaid') {
                $query->whereRaw('paid_amount < total_amount');
            } elseif ($request->status === 'paid') {
                $query->whereRaw('paid_amount >= total_amount');
            } elseif ($request->status === 'overdue') {
                $query->whereRaw('paid_amount < total_amount')->where('due_date', '<', now());
            }

            $invoices = $query->latest()->paginate(15)->withQueryString();

            // Counts
            $baseCountQuery = CustomerInvoice::query();
            if (! $user->hasRole('Super Admin')) {
                $baseCountQuery->where('organization_id', $user->organization_id);
            }

            $counts = [
                'all'     => (clone $baseCountQuery)->count(),
                'unpaid'  => (clone $baseCountQuery)->whereRaw('paid_amount < total_amount')->count(),
                'paid'    => (clone $baseCountQuery)->whereRaw('paid_amount >= total_amount')->count(),
                'overdue' => (clone $baseCountQuery)->whereRaw('paid_amount < total_amount')->where('due_date', '<', now())->count(),
            ];

            $breadcrumbs = [
                ['label' => 'Finance', 'url' => 'javascript:void(0)'],
                ['label' => 'Piutang & Tagihan Klien']
            ];

            return view('invoices.index_customer', compact('invoices', 'counts', 'breadcrumbs'));
        }

        // Supplier Invoices (AP)
        $query = SupplierInvoice::with(['supplier', 'purchaseOrder'])
            ->filter($request, [
                'search_column' => function($q, $s) {
                    $q->where('invoice_number', 'like', "%{$s}%")
                      ->orWhereHas('supplier', fn($s2) => $s2->where('name', 'like', "%{$s}%"));
                },
                'status' => 'status',
            ]);

        if (! $user->hasRole('Super Admin')) {
            $query->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        if ($request->status === 'unpaid') {
            $query->whereRaw('paid_amount < total_amount');
        } elseif ($request->status === 'paid') {
            $query->whereRaw('paid_amount >= total_amount');
        } elseif ($request->status === 'overdue') {
            $query->whereRaw('paid_amount < total_amount')->where('due_date', '<', now());
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        $baseCountQuery = SupplierInvoice::query();
        if (! $user->hasRole('Super Admin')) {
            $baseCountQuery->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        $counts = [
            'all'     => (clone $baseCountQuery)->count(),
            'unpaid'  => (clone $baseCountQuery)->whereRaw('paid_amount < total_amount')->count(),
            'paid'    => (clone $baseCountQuery)->whereRaw('paid_amount >= total_amount')->count(),
            'overdue' => (clone $baseCountQuery)->whereRaw('paid_amount < total_amount')->where('due_date', '<', now())->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Finance', 'url' => 'javascript:void(0)'],
            ['label' => 'Hutang Pemasok']
        ];

        return view('invoices.index_supplier', compact('invoices', 'counts', 'breadcrumbs'));
    }

    public function showSupplier(SupplierInvoice $invoice)
    {
        $invoice->load([
            'supplier', 
            'purchaseOrder.items.product', 
            'goodsReceipt.items.purchaseOrderItem.product', 
            'paymentAllocations.payment',
            'lineItems' // Load line items
        ]);
        
        $breadcrumbs = [
            ['label' => 'Finance', 'url' => 'javascript:void(0)'],
            ['label' => 'Hutang Pemasok', 'url' => route('web.invoices.index', ['tab' => 'supplier'])],
            ['label' => $invoice->invoice_number]
        ];
        
        return view('invoices.show_supplier', compact('invoice', 'breadcrumbs'));
    }

    public function showCustomer(CustomerInvoice $invoice)
    {
        $invoice->load([
            'organization', 
            'purchaseOrder.items.product', 
            'goodsReceipt.items.purchaseOrderItem.product', 
            'paymentAllocations.payment',
            'lineItems' // Load line items
        ]);
        
        $breadcrumbs = [
            ['label' => 'Finance', 'url' => 'javascript:void(0)'],
            ['label' => 'Piutang & Tagihan Klien', 'url' => route('web.invoices.index', ['tab' => 'customer'])],
            ['label' => $invoice->invoice_number]
        ];
        
        return view('invoices.show_customer', compact('invoice', 'breadcrumbs'));
    }

    public function exportSupplierPdf(SupplierInvoice $invoice)
    {
        $invoice->load(['supplier', 'purchaseOrder', 'goodsReceipt', 'lineItems.product']);
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'type' => 'supplier'])->setPaper('a4', 'portrait');
        return $pdf->stream('AP_INV_' . $invoice->invoice_number . '.pdf');
    }

    public function exportCustomerPdf(CustomerInvoice $invoice)
    {
        $invoice->load(['organization', 'purchaseOrder', 'goodsReceipt', 'lineItems.product']);
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'type' => 'customer'])->setPaper('a4', 'portrait');
        return $pdf->stream('AR_INV_' . $invoice->invoice_number . '.pdf');
    }

    // -----------------------------------------------------------------------
    // Create Supplier Invoice from Goods Receipt
    // GET /invoices/supplier/create
    // -----------------------------------------------------------------------

    public function createSupplier(Request $request)
    {
        if (! $request->user()->can('create_invoices')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk membuat invoice.');
        }

        $user = $request->user();

        // Load GRs with status 'completed' AND has remaining quantity
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'items.purchaseOrderItem.product'])
            ->where('status', 'completed')
            ->whereHas('purchaseOrder', function($q) {
                $q->whereNotNull('supplier_id');
            });

        // Filter by organization for non-Super Admin
        if (! $user->hasRole('Super Admin')) {
            $query->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        // Only show GRs that have remaining quantity to invoice
        $goodsReceipts = $query->get()->filter(function($gr) {
            return $gr->hasRemainingQuantity();
        });

        $breadcrumbs = [
            ['label' => 'Finance', 'url' => 'javascript:void(0)'],
            ['label' => 'Hutang Pemasok', 'url' => route('web.invoices.index', ['tab' => 'supplier'])],
            ['label' => 'Buat Invoice Pemasok']
        ];

        return view('invoices.create_supplier', compact('goodsReceipts', 'breadcrumbs'));
    }

    // -----------------------------------------------------------------------
    // Store Supplier Invoice from Goods Receipt
    // POST /invoices/supplier
    // -----------------------------------------------------------------------

    public function storeSupplier(StoreInvoiceFromGRRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // Get GoodsReceipt object
            $gr = GoodsReceipt::findOrFail($validated['goods_receipt_id']);
            
            // Prepare metadata
            $metadata = [
                'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
                'due_date' => $validated['due_date'] ?? now()->addDays(30),
                'notes' => $validated['notes'] ?? null,
            ];
            
            $invoice = $this->invoiceFromGRService->createSupplierInvoiceFromGR(
                $gr,
                $request->user(),
                $validated['items'],
                $metadata
            );

            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('success', "Invoice Pemasok {$invoice->invoice_number} berhasil dibuat.");
        } catch (\DomainException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat invoice: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // Confirm Payment (Clinic Admin)
    // POST /invoices/customer/{invoice}/confirm-payment
    // -----------------------------------------------------------------------

    public function confirmPayment(Request $request, CustomerInvoice $invoice): \Illuminate\Http\RedirectResponse
    {
        if (! $request->user()->can('confirm_payment')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengonfirmasi pembayaran.');
        }

        $request->validate([
            'payment_reference' => 'required|string|max:255',
            'paid_amount'       => 'nullable|numeric|min:0',
        ]);

        try {
            $this->invoiceService->confirmPayment($invoice, $request->user(), $request->only(['payment_reference', 'paid_amount']));
            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('success', 'Konfirmasi pembayaran berhasil dikirim. Menunggu verifikasi Finance.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // Verify Payment (Finance only)
    // POST /invoices/customer/{invoice}/verify-payment
    // -----------------------------------------------------------------------

    public function verifyPayment(Request $request, CustomerInvoice $invoice): \Illuminate\Http\RedirectResponse
    {
        if (! $request->user()->can('verify_payment')) {
            abort(403, 'Akses Ditolak. Hanya bagian Keuangan (Finance) yang dapat memverifikasi pembayaran.');
        }

        try {
            $this->invoiceService->verifyPayment($invoice, $request->user());
            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('success', 'Pembayaran telah diverifikasi dan ditandai sebagai LUNAS.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // Approve Discrepancy (Finance / Super Admin)
    // POST /invoices/customer/{invoice}/approve-discrepancy
    // -----------------------------------------------------------------------

    public function approveDiscrepancy(Request $request, CustomerInvoice $invoice): \Illuminate\Http\RedirectResponse
    {
        if (! $request->user()->can('approve_invoice_discrepancy')) {
            abort(403, 'Akses Ditolak. Hanya Finance atau Super Admin yang dapat menyetujui discrepancy.');
        }

        $request->validate([
            'approval_reason' => 'required|string|max:500',
        ]);

        try {
            $this->invoiceService->approveDiscrepancy(
                $invoice, 
                $request->user(), 
                $request->approval_reason
            );
            
            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('success', 'Discrepancy invoice telah disetujui. Invoice sekarang berstatus ISSUED.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // Reject Discrepancy (Finance / Super Admin)
    // POST /invoices/customer/{invoice}/reject-discrepancy
    // -----------------------------------------------------------------------

    public function rejectDiscrepancy(Request $request, CustomerInvoice $invoice): \Illuminate\Http\RedirectResponse
    {
        if (! $request->user()->can('approve_invoice_discrepancy')) {
            abort(403, 'Akses Ditolak. Hanya Finance atau Super Admin yang dapat menolak discrepancy.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $this->invoiceService->rejectDiscrepancy(
                $invoice, 
                $request->user(), 
                $request->rejection_reason
            );
            
            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('success', 'Discrepancy invoice telah ditolak. Invoice berstatus REJECTED.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
