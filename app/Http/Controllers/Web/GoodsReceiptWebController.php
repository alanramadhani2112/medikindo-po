<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class GoodsReceiptWebController extends Controller
{
    public function __construct(private readonly GoodsReceiptService $goodsReceiptService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', GoodsReceipt::class);
        
        $user  = $request->user();
        $query = GoodsReceipt::with(['purchaseOrder.organization', 'purchaseOrder.supplier', 'receivedBy', 'items'])
            ->filter($request, [
                'search_column' => function($q, $s) {
                    $q->where('gr_number', 'like', "%{$s}%")
                      ->orWhereHas('purchaseOrder', fn($po) => $po->where('po_number', 'like', "%{$s}%"));
                },
                'status' => 'status',
            ]);

        // Access Control
        if (! $user->hasRole('Super Admin')) {
            $query->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        $receipts = $query->latest()->paginate(15)->withQueryString();

        // Calculate counts
        $baseCountQuery = GoodsReceipt::query();
        if (! $user->hasRole('Super Admin')) {
            $baseCountQuery->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        $counts = [
            'all'       => (clone $baseCountQuery)->count(),
            'partial'   => (clone $baseCountQuery)->where('status', 'partial')->count(),
            'completed' => (clone $baseCountQuery)->where('status', 'completed')->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Logistics & Operations', 'url' => 'javascript:void(0)'],
            ['label' => 'Goods Receipts']
        ];

        return view('goods-receipts.index', compact('receipts', 'counts', 'breadcrumbs'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', GoodsReceipt::class);
        
        $user = $request->user();

        // Load POs available to receive — status must be 'approved' only
        // NOTE: Delivery (shipped/delivered) happens OUTSIDE the system
        $pos = PurchaseOrder::with(['items.product', 'organization', 'supplier'])
            ->where('status', PurchaseOrder::STATUS_APPROVED)
            ->when(! $user->hasRole('Super Admin'), function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->latest()
            ->get();

        $breadcrumbs = [
            ['label' => 'Logistics & Operations', 'url' => 'javascript:void(0)'],
            ['label' => 'Goods Receipts', 'url' => route('web.goods-receipts.index')],
            ['label' => 'Verifikasi Penerimaan']
        ];

        return view('goods-receipts.create', compact('pos', 'breadcrumbs'));
    }

    public function store(StoreGoodsReceiptRequest $request)
    {
        $data = $request->validated();
        $po   = PurchaseOrder::findOrFail($data['purchase_order_id']);
        
        $this->authorize('create', GoodsReceipt::class);
        $this->authorize('confirmReceipt', $po);

        try {
            $receipt = $this->goodsReceiptService->confirmReceipt(
                $po,
                $request->user(),
                $data['items'],
                $data['notes'] ?? null,
            );
            return redirect()->route('web.goods-receipts.show', $receipt)
                ->with('success', "Penerimaan barang {$receipt->gr_number} berhasil dikonfirmasi.");
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('view', $goodsReceipt);
        
        $goodsReceipt->load(['purchaseOrder.supplier', 'purchaseOrder.organization', 'receivedBy', 'items.purchaseOrderItem.product']);
        $breadcrumbs = [
            ['label' => 'Logistics & Operations', 'url' => 'javascript:void(0)'],
            ['label' => 'Goods Receipts', 'url' => route('web.goods-receipts.index')],
            ['label' => $goodsReceipt->gr_number]
        ];
        return view('goods-receipts.show', compact('goodsReceipt', 'breadcrumbs'));
    }

    public function exportPdf(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('view', $goodsReceipt);
        
        $goodsReceipt->load(['purchaseOrder.supplier', 'purchaseOrder.organization', 'receivedBy', 'items.purchaseOrderItem.product']);
        
        $pdf = Pdf::loadView('pdf.goods_receipt', compact('goodsReceipt'))->setPaper('a4', 'portrait');
        
        return $pdf->stream('GR_' . $goodsReceipt->gr_number . '.pdf');
    }
}
