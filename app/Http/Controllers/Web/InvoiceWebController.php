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

        // Load Supplier Invoices
        $supplierQuery = SupplierInvoice::with(['supplier', 'purchaseOrder', 'goodsReceipt'])
            ->filter($request, [
                'search_column' => function($q, $s) {
                    $q->where('invoice_number', 'like', "%{$s}%")
                      ->orWhere('distributor_invoice_number', 'like', "%{$s}%")
                      ->orWhereHas('supplier', fn($s2) => $s2->where('name', 'like', "%{$s}%"));
                },
                'status' => 'status',
            ]);

        if (! $user->hasRole('Super Admin')) {
            $supplierQuery->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        $supplierInvoices = $supplierQuery->latest()->paginate(15);

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Hutang ke Supplier']
        ];

        return view('invoices.index_supplier', compact('supplierInvoices', 'breadcrumbs'));
    }

    public function indexCustomer(Request $request)
    {
        $user = $request->user();

        // Load Customer Invoices
        $customerQuery = CustomerInvoice::with(['organization', 'purchaseOrder', 'goodsReceipt'])
            ->filter($request, [
                'search_column' => function($q, $s) {
                    $q->where('invoice_number', 'like', "%{$s}%")
                      ->orWhereHas('organization', fn($c) => $c->where('name', 'like', "%{$s}%"));
                },
                'status' => 'status',
            ]);

        if (! $user->hasRole('Super Admin')) {
            $customerQuery->where('organization_id', $user->organization_id);
        }

        $customerInvoices = $customerQuery->latest()->paginate(15);

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Tagihan ke RS/Klinik']
        ];

        return view('invoices.index_customer', compact('customerInvoices', 'breadcrumbs'));
    }

    public function index(Request $request)
    {
        $tab    = $request->get('tab', 'supplier');
        $user   = $request->user();

        // Load Supplier Invoices
        $supplierQuery = SupplierInvoice::with(['supplier', 'purchaseOrder'])
            ->filter($request, [
                'search_column' => function($q, $s) {
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
                'search_column' => function($q, $s) {
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
            'lineItems' // Load line items
        ]);
        
        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Tagihan ke RS/Klinik', 'url' => route('web.invoices.customer.index')],
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
    // Create Customer Invoice Form (Admin Pusat / Finance)
    // GET /invoices/customer/create
    // -----------------------------------------------------------------------

    public function createCustomer(Request $request)
    {
        if (! $request->user()->can('create_invoices')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk membuat invoice.');
        }

        $user = $request->user();

        // Load GRs with status 'completed' AND has remaining quantity
        $query = GoodsReceipt::with(['purchaseOrder.organization', 'items.purchaseOrderItem.product'])
            ->where('status', 'completed')
            ->whereHas('purchaseOrder', function($q) {
                $q->whereNotNull('organization_id');
            });

        // Filter by organization for non-Super Admin
        if (! $user->hasRole('Super Admin')) {
            $query->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        // Only show GRs that have remaining quantity to invoice AND have an associated Supplier Invoice
        $goodsReceipts = $query->get()->filter(function($gr) {
            $hasSupplierInvoice = \App\Models\SupplierInvoice::where('goods_receipt_id', $gr->id)->exists();
            return $hasSupplierInvoice && $gr->hasRemainingQuantity();
        });

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Tagihan ke RS/Klinik', 'url' => route('web.invoices.customer.index')],
            ['label' => 'Buat Tagihan ke RS/Klinik']
        ];

        return view('invoices.create_customer', compact('goodsReceipts', 'breadcrumbs'));
    }

    // -----------------------------------------------------------------------
    // Store Customer Invoice from Goods Receipt
    // POST /invoices/customer
    // -----------------------------------------------------------------------

    public function storeCustomer(StoreInvoiceFromGRRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // Get GoodsReceipt object
            $gr = GoodsReceipt::findOrFail($validated['goods_receipt_id']);
            
            // Prepare metadata
            $metadata = [
                'custom_invoice_number' => $validated['custom_invoice_number'] ?? null,
                'due_date' => $validated['due_date'] ?? now()->addDays(30),
                'notes' => $validated['notes'] ?? null,
                'surcharge' => $request->input('surcharge', 0),
            ];
            
            $invoice = $this->invoiceFromGRService->createCustomerInvoiceFromGR(
                $gr,
                $request->user(),
                $validated['items'],
                $metadata
            );

            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('success', "Tagihan ke RS/Klinik {$invoice->invoice_number} berhasil diterbitkan.");
        } catch (\DomainException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat tagihan: ' . $e->getMessage());
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
