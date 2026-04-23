<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;
use App\Http\Requests\StoreGoodsReceiptRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GoodsReceiptController extends Controller
{
    public function __construct(private readonly GoodsReceiptService $goodsReceiptService) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->can('view_receipt')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user = $request->user();

        $receipts = GoodsReceipt::with(['purchaseOrder', 'receivedBy', 'items'])
            ->when(! $user->hasRole('Super Admin'), fn($q) => $q->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id)))
            ->latest()
            ->paginate(15);
            
        return response()->json($receipts);
    }

    public function show(Request $request, GoodsReceipt $goodsReceipt): JsonResponse
    {
        if (! $request->user()->can('view_receipt')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (
            ! $request->user()->hasRole('Super Admin')
            && optional($goodsReceipt->purchaseOrder)->organization_id !== $request->user()->organization_id
        ) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($goodsReceipt->load(['purchaseOrder', 'receivedBy', 'items']));
    }

    public function store(StoreGoodsReceiptRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Resolve PO — either from new GR flow or from adding delivery to existing GR
        if (! empty($data['goods_receipt_id'])) {
            $gr = GoodsReceipt::findOrFail($data['goods_receipt_id']);
            $po = $gr->purchaseOrder;
        } else {
            $po = PurchaseOrder::findOrFail($data['purchase_order_id']);
        }

        // Use consistent Policy authorization
        $this->authorize('create', GoodsReceipt::class);
        $this->authorize('confirmReceipt', $po);

        try {
            $receipt = $this->goodsReceiptService->addDelivery(
                po: $po,
                actor: $request->user(),
                items: $data['items'],
                deliveryOrderNumber: $data['delivery_order_number'],
                photo: $request->file('delivery_photo'),
                notes: $data['notes'] ?? null,
            );
            return response()->json([
                'message' => 'Goods Receipt recorded successfully',
                'data'    => $receipt->load('items')
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
