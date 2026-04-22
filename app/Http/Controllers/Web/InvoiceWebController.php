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

    public function indexSupplier(Request $request)
    {
        $user = $request->user();
        $tab = $request->get('tab', '');

        // Load Supplier Invoices
        $supplierQuery = SupplierInvoice::with(['supplier', 'purchaseOrder', 'goodsReceipt'])
            ->filter($request, [
                'search_column' => function ($q, $s) {
                    $q->where('invoice_number', 'like', "%{$s}%")
                        ->orWhere('distributor_invoice_number', 'like', "%{$s}%")
                        ->orWhereHas('supplier', fn($s2) => $s2->where('name', 'like', "%{$s}%"));
                },
                'status' => 'status',
            ]);

        if (! $user->hasRole('Super Admin')) {
            $supplierQuery->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        // Apply tab filter if exists
        if ($tab !== '') {
            $supplierQuery->where('status', $tab);
        }

        // Clone for stats
        $statsQuery = SupplierInvoice::query();
        if (! $user->hasRole('Super Admin')) {
            $statsQuery->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        $stats = [
            'draft'    => (clone $statsQuery)->where('status', \App\Enums\SupplierInvoiceStatus::DRAFT->value)->count(),
            'verified' => (clone $statsQuery)->where('status', \App\Enums\SupplierInvoiceStatus::VERIFIED->value)->count(),
            'paid'     => (clone $statsQuery)->where('status', \App\Enums\SupplierInvoiceStatus::PAID->value)->count(),
            'overdue'  => (clone $statsQuery)->where('status', \App\Enums\SupplierInvoiceStatus::OVERDUE->value)->count(),
        ];

        $supplierInvoices = $supplierQuery->latest()->paginate(15)->withQueryString();

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Hutang ke Supplier']
        ];

        return view('invoices.index_supplier', compact('supplierInvoices', 'breadcrumbs', 'stats', 'tab'));
    }

    public function indexCustomer(Request $request)
    {
        $user   = $request->user();
        $tab    = $request->get('tab', '');
        $status = $request->get('status', '');
        $aging  = $request->get('aging', '');

        // Base query
        $customerQuery = CustomerInvoice::with(['organization', 'purchaseOrder', 'goodsReceipt', 'supplierInvoice'])
            ->filter($request, [
                'search_column' => function ($q, $s) {
                    $q->where('invoice_number', 'like', "%{$s}%")
                        ->orWhereHas('organization', fn($c) => $c->where('name', 'like', "%{$s}%"));
                },
            ]);

        if (! $user->hasRole('Super Admin')) {
            $customerQuery->where('organization_id', $user->organization_id);
        }

        // Status filter (from tab or dropdown)
        $activeStatus = $status ?: $tab;
        if ($activeStatus === 'overdue') {
            $customerQuery->whereIn('status', [
                \App\Enums\CustomerInvoiceStatus::ISSUED->value,
                \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])->whereNotNull('due_date')->where('due_date', '<', now()->startOfDay());
        } elseif ($activeStatus !== '' && $activeStatus !== 'all') {
            $customerQuery->where('status', $activeStatus);
        }

        // Aging filter
        if ($aging) {
            $customerQuery->whereIn('status', [
                \App\Enums\CustomerInvoiceStatus::ISSUED->value,
                \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])->whereNotNull('due_date');

            if ($aging === 'current') {
                $customerQuery->where('due_date', '>=', now()->startOfDay());
            } elseif ($aging === '1-30') {
                $customerQuery->whereBetween('due_date', [now()->subDays(30)->startOfDay(), now()->subDay()->endOfDay()]);
            } elseif ($aging === '31-60') {
                $customerQuery->whereBetween('due_date', [now()->subDays(60)->startOfDay(), now()->subDays(31)->endOfDay()]);
            } elseif ($aging === '61-90') {
                $customerQuery->whereBetween('due_date', [now()->subDays(90)->startOfDay(), now()->subDays(61)->endOfDay()]);
            } elseif ($aging === '90+') {
                $customerQuery->where('due_date', '<', now()->subDays(90)->startOfDay());
            }
        }

        // Stats query (unfiltered)
        $statsQuery = CustomerInvoice::query();
        if (! $user->hasRole('Super Admin')) {
            $statsQuery->where('organization_id', $user->organization_id);
        }

        $tabCounts = [
            'all'          => (clone $statsQuery)->count(),
            'issued'       => (clone $statsQuery)->where('status', \App\Enums\CustomerInvoiceStatus::ISSUED)->count(),
            'partial_paid' => (clone $statsQuery)->where('status', \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID)->count(),
            'paid'         => (clone $statsQuery)->where('status', \App\Enums\CustomerInvoiceStatus::PAID)->count(),
            'overdue'      => (clone $statsQuery)->whereIn('status', [
                \App\Enums\CustomerInvoiceStatus::ISSUED->value,
                \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])->whereNotNull('due_date')->where('due_date', '<', now()->startOfDay())->count(),
            'draft'        => (clone $statsQuery)->where('status', \App\Enums\CustomerInvoiceStatus::DRAFT)->count(),
        ];

        // Financial summary stats
        $activeInvoices = (clone $statsQuery)->whereIn('status', [
            \App\Enums\CustomerInvoiceStatus::ISSUED->value,
            \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value,
        ])->get();

        $stats = [
            'total_outstanding' => $activeInvoices->sum('outstanding_amount'),
            'issued_amount'     => (clone $statsQuery)->where('status', \App\Enums\CustomerInvoiceStatus::ISSUED)->get()->sum('outstanding_amount'),
            'partial_amount'    => (clone $statsQuery)->where('status', \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID)->get()->sum('outstanding_amount'),
            'overdue_amount'    => (clone $statsQuery)->whereIn('status', [
                \App\Enums\CustomerInvoiceStatus::ISSUED->value,
                \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])->whereNotNull('due_date')->where('due_date', '<', now()->startOfDay())->get()->sum('outstanding_amount'),
        ];

        $invoices = $customerQuery->latest()->paginate(15)->withQueryString();

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Tagihan ke RS/Klinik'],
        ];

        return view('invoices.customer.index', compact('invoices', 'breadcrumbs', 'stats', 'tabCounts'));
    }

    public function index(Request $request)
    {
        $tab    = $request->get('tab', 'supplier');
        $user   = $request->user();

        // Load Supplier Invoices
        $supplierQuery = SupplierInvoice::with(['supplier', 'purchaseOrder'])
            ->filter($request, [
                'search_column' => function ($q, $s) {
                    $q->where('invoice_number', 'like', "%{$s}%")
                        ->orWhereHas('supplier', fn($s2) => $s2->where('name', 'like', "%{$s}%"));
                },
                'status' => 'status',
            ]);

        if (! $user->hasRole('Super Admin')) {
            $supplierQuery->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        $supplierInvoices = $supplierQuery->latest()->paginate(15, ['*'], 'supplier_page')->withQueryString();

        // Load Customer Invoices
        $customerQuery = CustomerInvoice::with(['organization', 'purchaseOrder'])
            ->filter($request, [
                'search_column' => function ($q, $s) {
                    $q->where('invoice_number', 'like', "%{$s}%")
                        ->orWhereHas('organization', fn($c) => $c->where('name', 'like', "%{$s}%"));
                },
                'status' => 'status',
            ]);

        if (! $user->hasRole('Super Admin')) {
            $customerQuery->where('organization_id', $user->organization_id);
        }

        $customerInvoices = $customerQuery->latest()->paginate(15, ['*'], 'customer_page')->withQueryString();

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Invoice']
        ];

        return view('invoices.index', compact('supplierInvoices', 'customerInvoices', 'breadcrumbs'));
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
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Hutang ke Supplier', 'url' => route('web.invoices.supplier.index')],
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
            'lineItems',
            'supplierInvoice.supplier',
            'bankAccount',
        ]);

        // Auto-assign default receive bank if invoice has none yet (Draft only)
        if ($invoice->isDraft() && ! $invoice->bank_account_id) {
            $defaultBank = \App\Models\BankAccount::defaultReceive()->first();
            if ($defaultBank) {
                $invoice->update(['bank_account_id' => $defaultBank->id]);
                $invoice->setRelation('bankAccount', $defaultBank);
            }
        }

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Tagihan ke RS/Klinik', 'url' => route('web.invoices.customer.index')],
            ['label' => $invoice->invoice_number],
        ];

        return view('invoices.customer.show', compact('invoice', 'breadcrumbs'));
    }

    public function exportSupplierPdf(SupplierInvoice $invoice)
    {
        $invoice->load(['supplier', 'purchaseOrder', 'goodsReceipt', 'lineItems.product', 'issuedBy']);
        $pdf = Pdf::loadView('pdf.invoice_supplier', ['invoice' => $invoice])->setPaper('a4', 'portrait');
        return $pdf->stream('AP_INV_' . str_replace('/', '-', $invoice->invoice_number) . '.pdf');
    }

    public function exportCustomerPdf(CustomerInvoice $invoice)
    {
        $invoice->load(['organization', 'purchaseOrder', 'goodsReceipt', 'lineItems.product', 'issuedBy', 'bankAccount']);
        $pdf = Pdf::loadView('pdf.customer_invoice', ['invoice' => $invoice])->setPaper('a4', 'portrait');
        return $pdf->stream('AR_INV_' . str_replace('/', '-', $invoice->invoice_number) . '.pdf');
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
        $query = GoodsReceipt::with([
                'purchaseOrder.supplier',
                'items.purchaseOrderItem.product',  // path yang benar untuk nama produk
            ])
            ->where('status', 'completed')
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNotNull('supplier_id');
            });

        // Filter by organization for non-Super Admin
        if (! $user->hasRole('Super Admin')) {
            $query->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        // Only show GRs that have remaining quantity to invoice
        $goodsReceipts = $query->get()->filter(function ($gr) {
            return $gr->hasRemainingQuantity();
        })->values();

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Hutang ke Supplier', 'url' => route('web.invoices.supplier.index')],
            ['label' => 'Input Invoice Pemasok']
        ];

        return view('invoices.create_supplier', compact('goodsReceipts', 'breadcrumbs'));
    }

    // -----------------------------------------------------------------------
    // Store Supplier Invoice from Goods Receipt
    // POST /invoices/supplier
    // -----------------------------------------------------------------------

    public function storeSupplier(StoreSupplierInvoiceRequest $request)
    {
        try {
            $validated = $request->validated();

            // Get GoodsReceipt object
            $gr = GoodsReceipt::findOrFail($validated['goods_receipt_id']);

            // Prepare metadata with distributor invoice info
            $metadata = [
                'distributor_invoice_number' => $validated['distributor_invoice_number'],
                'distributor_invoice_date'   => $validated['distributor_invoice_date'],
                'internal_invoice_number'    => $validated['internal_invoice_number'] ?? null,
                'due_date'                   => $validated['due_date'],
                'notes'                      => $validated['notes'] ?? null,
            ];

            $invoice = $this->invoiceFromGRService->createSupplierInvoiceFromGR(
                $gr,
                $request->user(),
                $validated['items'],
                $metadata
            );

            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('success', "Invoice Pemasok {$invoice->invoice_number} berhasil disimpan.");
        } catch (\DomainException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan invoice: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // Create Customer Invoice Form
    // GET /invoices/customer/create
    // BLOCKED: AR hanya bisa dibuat via verifikasi AP (MirrorGenerationService)
    // -----------------------------------------------------------------------

    public function createCustomer(Request $request)
    {
        // AR tidak lagi dibuat manual — harus melalui verifikasi AP
        return redirect()
            ->route('web.invoices.supplier.index')
            ->with('info', 'Tagihan ke RS/Klinik dibuat otomatis saat Invoice Pemasok diverifikasi. Silakan verifikasi Invoice Pemasok terlebih dahulu.');
    }

    // -----------------------------------------------------------------------
    // Store Customer Invoice from Goods Receipt
    // POST /invoices/customer
    // BLOCKED: AR hanya bisa dibuat via verifikasi AP
    // -----------------------------------------------------------------------

    public function storeCustomer(StoreInvoiceFromGRRequest $request)
    {
        return redirect()
            ->route('web.invoices.supplier.index')
            ->with('info', 'Tagihan ke RS/Klinik dibuat otomatis saat Invoice Pemasok diverifikasi.');
    }

    // -----------------------------------------------------------------------
    // Issue Customer Invoice (Draft → Issued)
    // POST /invoices/customer/{invoice}/issue
    // -----------------------------------------------------------------------

    public function issueCustomer(Request $request, CustomerInvoice $invoice): \Illuminate\Http\RedirectResponse
    {
        if (! $request->user()->can('create_invoices')) {
            abort(403, 'Akses Ditolak.');
        }

        if (! $invoice->isDraft()) {
            return back()->with('error', "Invoice hanya bisa diterbitkan dari status Draft. Status saat ini: {$invoice->status->getLabel()}.");
        }

        $request->validate([
            'surcharge'            => 'nullable|numeric|min:0|max:999999999999',
            'surcharge_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Update surcharge jika diisi, lalu recalculate total
        $surcharge = (float) ($request->input('surcharge', 0) ?? 0);
        $surchargePercentage = (float) ($request->input('surcharge_percentage', 0) ?? 0);

        if ($surcharge > 0) {
            // Recalculate total: subtotal - discount + tax + surcharge + ematerai
            $base = (float) $invoice->subtotal_amount
                  - (float) $invoice->discount_amount
                  + (float) $invoice->tax_amount
                  + (float) $invoice->ematerai_fee;

            $newTotal = $base + $surcharge;

            $invoice->update([
                'surcharge'            => $surcharge,
                'surcharge_percentage' => $surchargePercentage,
                'total_amount'         => $newTotal,
            ]);
        }

        $invoice->transitionTo(\App\Enums\CustomerInvoiceStatus::ISSUED);

        return redirect()
            ->route('web.invoices.customer.show', $invoice)
            ->with('success', "Tagihan {$invoice->invoice_number} berhasil diterbitkan ke RS/Klinik.");
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
