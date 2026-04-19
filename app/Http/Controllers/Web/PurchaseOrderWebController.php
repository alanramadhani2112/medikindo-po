<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Organization;
use App\Services\POService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchaseOrderWebController extends Controller
{
    public function __construct(private readonly POService $poService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $tab = $request->get('tab', 'all');

        $query = PurchaseOrder::with(['organization', 'supplier', 'creator'])
            ->filter($request, [
                'search_column' => 'po_number',
                'status'        => 'status',
                'supplier_id'   => 'supplier_id',
                'date_column'   => 'created_at',
            ]);

        // Tab filtering
        if ($tab !== 'all') {
            $statusMap = [
                'draft' => PurchaseOrder::STATUS_DRAFT,
                'submitted' => PurchaseOrder::STATUS_SUBMITTED,
                'approved' => PurchaseOrder::STATUS_APPROVED,
                'rejected' => PurchaseOrder::STATUS_REJECTED,
                'completed' => PurchaseOrder::STATUS_COMPLETED,
            ];

            if (isset($statusMap[$tab])) {
                $query->where('status', $statusMap[$tab]);
            }
        }

        // Access Control (Organization Isolation)
        if (! $user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        $pos = $query->latest()
            ->paginate(15)
            ->withQueryString();

        // Calculate counts for badges
        $countsQuery = PurchaseOrder::query();
        if (! $user->hasRole('Super Admin')) {
            $countsQuery->where('organization_id', $user->organization_id);
        }

        $counts = [
            'all'       => (clone $countsQuery)->count(),
            'draft'     => (clone $countsQuery)->where('status', PurchaseOrder::STATUS_DRAFT)->count(),
            'submitted' => (clone $countsQuery)->where('status', PurchaseOrder::STATUS_SUBMITTED)->count(),
            'approved'  => (clone $countsQuery)->where('status', PurchaseOrder::STATUS_APPROVED)->count(),
            'rejected'  => (clone $countsQuery)->where('status', PurchaseOrder::STATUS_REJECTED)->count(),
            'completed' => (clone $countsQuery)->where('status', PurchaseOrder::STATUS_COMPLETED)->count(),
        ];

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $organizations = null;
        if ($user->hasRole('Super Admin')) {
            $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        }

        $breadcrumbs = [
            ['label' => 'Procurement', 'url' => 'javascript:void(0)'],
            ['label' => 'Purchase Orders']
        ];

        return view('purchase-orders.index', [
            'purchaseOrders' => $pos,
            'tab'            => $tab,
            'counts'         => $counts,
            'suppliers'      => $suppliers,
            'organizations'  => $organizations,
            'breadcrumbs'    => $breadcrumbs
        ]);
    }

    public function create(Request $request)
    {
        if (! $request->user()->can('create_po')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk membuat Purchase Order.');
        }

        // Load suppliers with only active products
        $suppliers = Supplier::with(['products' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $organizations = null;
        if ($request->user()->hasRole('Super Admin')) {
            $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        }
        $breadcrumbs = [
            ['label' => 'Procurement', 'url' => 'javascript:void(0)'],
            ['label' => 'Purchase Orders', 'url' => route('web.po.index')],
            ['label' => 'Buat PO Baru']
        ];
        return view('purchase-orders.create', compact('suppliers', 'organizations', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        if (! $request->user()->can('create_po')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk menyimpan Purchase Order.');
        }
        $data = $request->validate([
            'organization_id'    => ['nullable', 'integer', 'exists:organizations,id'],
            'supplier_id'        => ['required', 'integer', 'exists:suppliers,id'],
            'notes'              => ['nullable', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        $po   = $this->poService->createPO($user, [
            'organization_id' => $data['organization_id'] ?? null,
            'supplier_id'     => $data['supplier_id'],
            'notes'           => $data['notes'] ?? null,
        ]);

        if (! empty($data['items'])) {
            $this->poService->syncItems($po, $data['items']);
        }

        return redirect()->route('web.po.show', $po)
            ->with('success', "PO #{$po->po_number} berhasil dibuat.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['organization', 'supplier', 'creator', 'items.product', 'approvals.approver']);
        $breadcrumbs = [
            ['label' => 'Procurement', 'url' => 'javascript:void(0)'],
            ['label' => 'Purchase Orders', 'url' => route('web.po.index')],
            ['label' => $purchaseOrder->po_number]
        ];
        return view('purchase-orders.show', ['po' => $purchaseOrder, 'breadcrumbs' => $breadcrumbs]);
    }

    public function submit(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! $request->user()->can('submit_po')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengajukan Purchase Order.');
        }

        try {
            $this->poService->submitPO($purchaseOrder, $request->user());
            return redirect()->route('web.po.show', $purchaseOrder)
                ->with('success', 'PO berhasil diajukan untuk persetujuan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (! auth()->user()->can('update_po')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengubah Purchase Order.');
        }

        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('web.po.show', $purchaseOrder)->with('error', 'Hanya PO berstatus Draft yang dapat diubah.');
        }

        // Load suppliers with only active products
        $suppliers = Supplier::with(['products' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $organizations = null;
        if (auth()->user()->hasRole('Super Admin')) {
            $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        }

        $purchaseOrder->load('items.product');

        // Pre-map items for Alpine.js (avoid arrow function inside @json in Blade)
        $poItems = $purchaseOrder->items->map(function ($i) {
            return [
                'product_id'   => $i->product_id,
                'product_name' => $i->product?->name ?? '',
                'product_sku'  => $i->product?->sku ?? '',
                'quantity'     => $i->quantity,
                'unit_price'   => (int) $i->unit_price,
                'subtotal'     => (int) $i->subtotal,
            ];
        })->values()->all();

        $breadcrumbs = [
            ['label' => 'Procurement', 'url' => 'javascript:void(0)'],
            ['label' => 'Purchase Orders', 'url' => route('web.po.index')],
            ['label' => 'Edit PO', 'url' => 'javascript:void(0)'],
            ['label' => $purchaseOrder->po_number]
        ];
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'organizations', 'breadcrumbs', 'poItems'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! $request->user()->can('update_po')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk menyimpan perubahan Purchase Order.');
        }
        $data = $request->validate([
            'organization_id'    => ['nullable', 'integer', 'exists:organizations,id'],
            'supplier_id'        => ['required', 'integer', 'exists:suppliers,id'],
            'notes'              => ['nullable', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->poService->update($purchaseOrder, $data);
            $this->poService->syncItems($purchaseOrder, $data['items']);

            return redirect()->route('web.po.show', $purchaseOrder)
                ->with('success', "PO #{$purchaseOrder->po_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reopen(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! $request->user()->can('update_po')) {
            abort(403, 'Akses Ditolak.');
        }

        if (! $purchaseOrder->isRejected()) {
            return redirect()->route('web.po.show', $purchaseOrder)
                ->with('error', 'Hanya PO berstatus Ditolak yang dapat dibuka kembali.');
        }

        try {
            $this->poService->reopen($purchaseOrder, $request->user());
            return redirect()->route('web.po.edit', $purchaseOrder)
                ->with('success', "PO #{$purchaseOrder->po_number} dibuka kembali sebagai Draft. Silakan revisi dan ajukan ulang.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (! auth()->user()->hasRole('Super Admin') && ! auth()->user()->can('update_po')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk menghapus Purchase Order.');
        }
        try {
            $this->poService->delete($purchaseOrder);
            return redirect()->route('web.po.index')->with('success', 'Purchase Order berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function exportPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['organization', 'supplier', 'creator', 'items.product', 'approvals.approver']);

        $pdf = Pdf::loadView('pdf.purchase_order', ['po' => $purchaseOrder])->setPaper('a4', 'portrait');

        return $pdf->stream('PO_' . $purchaseOrder->po_number . '.pdf');
    }
}
