<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerInvoice;
use App\Services\AuditService;
use App\Services\MarginProtectionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CustomerInvoiceWebController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    /**
     * GET /invoices/customer
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = CustomerInvoice::with(['organization', 'purchaseOrder', 'supplierInvoice'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('invoice_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('invoice_date', '<=', $request->date_to))
            ->when($request->filled('search'), fn($q) => $q
                ->where('invoice_number', 'like', "%{$request->search}%")
                ->orWhereHas('organization', fn($o) => $o->where('name', 'like', "%{$request->search}%"))
            );

        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('invoices.customer.index', compact('invoices'));
    }

    /**
     * GET /invoices/customer/{invoice}
     */
    public function show(CustomerInvoice $invoice): View
    {
        $invoice->load([
            'organization',
            'purchaseOrder',
            'goodsReceipt',
            'supplierInvoice.supplier',
            'lineItems.product',
            'paymentAllocations.payment',
        ]);

        return view('invoices.customer.show', compact('invoice'));
    }

    /**
     * POST /invoices/customer/{invoice}/issue
     */
    public function issue(
        CustomerInvoice $invoice,
        MarginProtectionService $margin
    ): RedirectResponse {
        $violations = $margin->check($invoice);

        if (!empty($violations)) {
            $errorMessages = collect($violations)->map(fn($v) =>
                "{$v['product_name']}: Harga jual Rp " . number_format($v['selling_price'], 0, ',', '.') .
                " < Harga beli Rp " . number_format($v['cost_price'], 0, ',', '.') .
                " (selisih Rp " . number_format(abs($v['diff']), 0, ',', '.') . ")"
            )->toArray();

            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('error', 'Invoice tidak dapat diterbitkan karena ada pelanggaran margin: ' . implode('; ', $errorMessages));
        }

        try {
            $invoice->transitionTo(CustomerInvoice::STATUS_ISSUED);

            $this->auditService->log(
                action: 'customer_invoice.issued',
                entityType: 'customer_invoice',
                entityId: $invoice->id,
                metadata: [
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount'   => $invoice->total_amount,
                    'timestamp'      => now()->toIso8601String(),
                ],
            );

            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('success', "Invoice #{$invoice->invoice_number} berhasil diterbitkan.");
        } catch (\App\Exceptions\InvalidStateTransitionException $e) {
            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * POST /invoices/customer/{invoice}/void
     */
    public function void(Request $request, CustomerInvoice $invoice): RedirectResponse
    {
        $request->validate([
            'credit_note_reference' => 'required|string|max:100',
        ]);

        try {
            $invoice->transitionTo(CustomerInvoice::STATUS_VOID);

            $this->auditService->log(
                action: 'customer_invoice.voided',
                entityType: 'customer_invoice',
                entityId: $invoice->id,
                metadata: [
                    'invoice_number'        => $invoice->invoice_number,
                    'credit_note_reference' => $request->credit_note_reference,
                    'voided_by'             => $request->user()?->id,
                    'timestamp'             => now()->toIso8601String(),
                ],
            );

            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('success', "Invoice #{$invoice->invoice_number} berhasil dibatalkan (VOID).");
        } catch (\App\Exceptions\InvalidStateTransitionException $e) {
            return redirect()
                ->route('web.invoices.customer.show', $invoice)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * GET /invoices/customer/{invoice}/pdf
     * Generate PDF, increment print_count, update last_printed_at, log audit.
     */
    public function print(CustomerInvoice $invoice): Response
    {
        $invoice->load([
            'organization',
            'purchaseOrder',
            'goodsReceipt',
            'supplierInvoice',
            'lineItems.product',
        ]);

        // Auto-generate barcode_serial if not set
        if (!$invoice->barcode_serial) {
            $invoice->update(['barcode_serial' => 'AR-' . str_pad($invoice->id, 8, '0', STR_PAD_LEFT)]);
            $invoice->refresh();
        }

        // Increment print count and update last_printed_at
        $invoice->increment('print_count');
        $invoice->update(['last_printed_at' => now()]);
        $invoice->refresh();

        // Log audit
        $this->auditService->log(
            action: 'customer_invoice.printed',
            entityType: 'customer_invoice',
            entityId: $invoice->id,
            metadata: [
                'invoice_number'  => $invoice->invoice_number,
                'print_count'     => $invoice->print_count,
                'last_printed_at' => now()->toIso8601String(),
                'printed_by'      => auth()->id(),
            ],
        );

        // Generate PDF (stub — Sprint 4 will implement the full template)
        $pdf = Pdf::loadView('pdf.customer_invoice', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('AR_INV_' . $invoice->invoice_number . '.pdf');
    }
}
