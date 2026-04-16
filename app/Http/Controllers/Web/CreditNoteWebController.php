<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Services\CreditNoteService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CreditNoteWebController extends Controller
{
    public function __construct(
        private readonly CreditNoteService $creditNoteService
    ) {}

    /**
     * Display credit notes index
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CreditNote::class);

        $user = $request->user();
        $query = CreditNote::with(['organization', 'customerInvoice', 'supplierInvoice', 'issuedBy'])
            ->filter($request, [
                'search_column' => function($q, $s) {
                    $q->where('cn_number', 'like', "%{$s}%")
                      ->orWhere('reason', 'like', "%{$s}%");
                },
                'status' => 'status',
                'type' => 'type',
            ]);

        // Access Control
        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        $creditNotes = $query->latest()->paginate(15)->withQueryString();

        // Calculate counts
        $baseCountQuery = CreditNote::query();
        if (!$user->hasRole('Super Admin')) {
            $baseCountQuery->where('organization_id', $user->organization_id);
        }

        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'draft' => (clone $baseCountQuery)->where('status', 'draft')->count(),
            'issued' => (clone $baseCountQuery)->where('status', 'issued')->count(),
            'applied' => (clone $baseCountQuery)->where('status', 'applied')->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Finance & Accounting', 'url' => 'javascript:void(0)'],
            ['label' => 'Credit Notes']
        ];

        return view('credit-notes.index', compact('creditNotes', 'counts', 'breadcrumbs'));
    }

    /**
     * Show credit note details
     */
    public function show(CreditNote $creditNote): View
    {
        $this->authorize('view', $creditNote);

        $creditNote->load([
            'organization',
            'customerInvoice.organization',
            'supplierInvoice.supplier',
            'issuedBy',
            'lineItems.product'
        ]);

        $breadcrumbs = [
            ['label' => 'Finance & Accounting', 'url' => 'javascript:void(0)'],
            ['label' => 'Credit Notes', 'url' => route('web.credit-notes.index')],
            ['label' => $creditNote->cn_number]
        ];

        return view('credit-notes.show', compact('creditNote', 'breadcrumbs'));
    }

    /**
     * Create credit note for customer invoice
     */
    public function createForCustomerInvoice(CustomerInvoice $invoice): View
    {
        $this->authorize('create', CreditNote::class);
        $this->authorize('view', $invoice);

        $invoice->load(['lineItems.product', 'organization']);

        $breadcrumbs = [
            ['label' => 'Finance & Accounting', 'url' => 'javascript:void(0)'],
            ['label' => 'Credit Notes', 'url' => route('web.credit-notes.index')],
            ['label' => 'Create for Invoice ' . $invoice->invoice_number]
        ];

        return view('credit-notes.create-customer', compact('invoice', 'breadcrumbs'));
    }

    /**
     * Store credit note for customer invoice
     */
    public function storeForCustomerInvoice(Request $request, CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('create', CreditNote::class);
        $this->authorize('view', $invoice);

        $validated = $request->validate([
            'type' => 'required|in:return,discount,correction,cancellation',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.reason' => 'nullable|string',
        ]);

        try {
            $creditNote = $this->creditNoteService->createForCustomerInvoice(
                $invoice,
                $validated,
                $request->user()
            );

            return redirect()
                ->route('web.credit-notes.show', $creditNote)
                ->with('success', "Credit Note {$creditNote->cn_number} berhasil dibuat.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat credit note: ' . $e->getMessage());
        }
    }

    /**
     * Issue credit note
     */
    public function issue(Request $request, CreditNote $creditNote): RedirectResponse
    {
        $this->authorize('update', $creditNote);

        try {
            $this->creditNoteService->issue($creditNote, $request->user());

            return back()->with('success', "Credit Note {$creditNote->cn_number} berhasil diterbitkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerbitkan credit note: ' . $e->getMessage());
        }
    }

    /**
     * Apply credit note
     */
    public function apply(Request $request, CreditNote $creditNote): RedirectResponse
    {
        $this->authorize('update', $creditNote);

        try {
            $this->creditNoteService->apply($creditNote, $request->user());

            return back()->with('success', "Credit Note {$creditNote->cn_number} berhasil diterapkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerapkan credit note: ' . $e->getMessage());
        }
    }

    /**
     * Cancel credit note
     */
    public function cancel(Request $request, CreditNote $creditNote): RedirectResponse
    {
        $this->authorize('update', $creditNote);

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            $this->creditNoteService->cancel(
                $creditNote,
                $request->user(),
                $request->reason
            );

            return back()->with('success', "Credit Note {$creditNote->cn_number} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan credit note: ' . $e->getMessage());
        }
    }
}