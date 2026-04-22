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
        $tab   = $request->get('tab', 'all');
        
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

        // Tab filtering for GRs
        if ($tab !== 'all' && $tab !== 'pending') {
            $query->where('status', $tab);
        }

        $receipts = $query->latest()->paginate(15)->withQueryString();

        // Pending POs (Approved or Partially Received — still awaiting full delivery)
        $pendingPOsQuery = PurchaseOrder::with(['organization', 'supplier'])
            ->whereIn('status', [
                PurchaseOrder::STATUS_APPROVED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ]);
        
        if (! $user->hasRole('Super Admin')) {
            $pendingPOsQuery->where('organization_id', $user->organization_id);
        }

        $pendingPOs = $tab === 'pending' ? $pendingPOsQuery->latest()->paginate(15)->withQueryString() : collect();

        // Calculate counts
        $baseCountQuery = GoodsReceipt::query();
        if (! $user->hasRole('Super Admin')) {
            $baseCountQuery->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id));
        }

        $counts = [
            'all'       => (clone $baseCountQuery)->count(),
            'pending'   => $pendingPOsQuery->count(),
            'partial'   => (clone $baseCountQuery)->where('status', 'partial')->count(),
            'completed' => (clone $baseCountQuery)->where('status', 'completed')->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Logistics & Operations', 'url' => 'javascript:void(0)'],
            ['label' => 'Goods Receipts']
        ];

        return view('goods-receipts.index', compact('receipts', 'pendingPOs', 'counts', 'breadcrumbs', 'tab'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', GoodsReceipt::class);
        
        $user = $request->user();

        // Load POs available to receive — approved or partially received
        // NOTE: Delivery (shipped/delivered) happens OUTSIDE the system
        $pos = PurchaseOrder::with(['items.product', 'organization', 'supplier', 'goodsReceipts.items'])
            ->whereIn('status', [
                PurchaseOrder::STATUS_APPROVED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ])
            ->when(! $user->hasRole('Super Admin'), function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            })
            ->latest()
            ->get();

        // Calculate already_received and remaining for each item
        $pos->each(function ($po) {
            $po->items->each(function ($item) use ($po) {
                $alreadyReceived = $po->goodsReceipts->flatMap(function ($gr) {
                    return $gr->items;
                })->where('purchase_order_item_id', $item->id)->sum('quantity_received');
                
                $item->already_received = $alreadyReceived;
                $item->remaining = max(0, $item->quantity - $alreadyReceived);
            });
            
            // Filter out items that are already fully received
            $po->setRelation('items', $po->items->filter(fn($item) => $item->remaining > 0)->values());
        });
        
        // Filter out POs that have no remaining items to receive
        $pos = $pos->filter(fn($po) => $po->items->isNotEmpty())->values();

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

        // Resolve PO — either from new GR flow or from adding delivery to existing GR
        if (! empty($data['goods_receipt_id'])) {
            $gr = \App\Models\GoodsReceipt::findOrFail($data['goods_receipt_id']);
            $po = $gr->purchaseOrder;
        } else {
            $po = PurchaseOrder::findOrFail($data['purchase_order_id']);
        }

        $this->authorize('create', GoodsReceipt::class);
        $this->authorize('confirmReceipt', $po);

        try {
            $gr = $this->goodsReceiptService->addDelivery(
                po: $po,
                actor: $request->user(),
                items: $data['items'],
                deliveryOrderNumber: $data['delivery_order_number'],
                photo: $request->file('delivery_photo'),
                notes: $data['notes'] ?? null,
            );
            return redirect()->route('web.goods-receipts.show', $gr)
                ->with('success', "Pengiriman ke-{$gr->deliveries()->count()} untuk {$gr->gr_number} berhasil dikonfirmasi.");
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('view', $goodsReceipt);

        $goodsReceipt->load([
            'purchaseOrder.supplier',
            'purchaseOrder.organization',
            'purchaseOrder.items.product',
            'receivedBy',
            'items.purchaseOrderItem.product',
            'deliveries' => fn($q) => $q->orderBy('delivery_sequence'),
            'deliveries.receivedBy',
            'deliveries.items.purchaseOrderItem.product',
        ]);

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
        
        return $pdf->stream('GR_' . str_replace('/', '-', $goodsReceipt->gr_number) . '.pdf');
    }
}
