<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\SyncPOItemsRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Services\DeliveryService;
use App\Services\POService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly POService $poService,
        private readonly DeliveryService $deliveryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PurchaseOrder::class);

        $user = $request->user();

        $pos = PurchaseOrder::query()
            ->with(['organization:id,name', 'supplier:id,name', 'creator:id,name'])
            ->when(! $user->isSuperAdmin(), fn($q) => $q->where('organization_id', $user->organization_id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->has('narcotics'), fn($q) => $q->where('has_narcotics', true))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($pos);
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        Gate::authorize('create', PurchaseOrder::class);

        $po = $this->poService->createPO($request->user(), $request->validated());

        return response()->json([
            'message'        => 'Purchase Order created.',
            'purchase_order' => $po->load(['organization', 'supplier', 'creator']),
        ], 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        Gate::authorize('view', $purchaseOrder);

        return response()->json([
            'purchase_order' => $purchaseOrder->load([
                'organization',
                'supplier',
                'creator',
                'items.product.supplier',
                'approvals.approver',
            ]),
        ]);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        Gate::authorize('update', $purchaseOrder);

        $po = $this->poService->update($purchaseOrder, $request->validated());

        return response()->json([
            'message'        => 'Purchase Order updated.',
            'purchase_order' => $po,
        ]);
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        Gate::authorize('delete', $purchaseOrder);

        $purchaseOrder->delete();

        return response()->json(['message' => 'Purchase Order deleted.']);
    }

    // -----------------------------------------------------------------------
    // Item management
    // -----------------------------------------------------------------------

    public function syncItems(SyncPOItemsRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        Gate::authorize('update', $purchaseOrder);

        $po = $this->poService->syncItems($purchaseOrder, $request->validated('items'));

        return response()->json([
            'message'        => 'Items updated.',
            'purchase_order' => $po,
        ]);
    }

    // -----------------------------------------------------------------------
    // PO lifecycle actions
    // -----------------------------------------------------------------------

    public function submit(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        Gate::authorize('submit', $purchaseOrder);

        $po = $this->poService->submitPO($purchaseOrder, $request->user());

        return response()->json([
            'message'        => 'Purchase Order submitted for approval.',
            'purchase_order' => $po,
        ]);
    }

    public function sendToSupplier(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (! $request->user()->hasAnyRole(['Approver', 'Super Admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        try {
            $po = $this->deliveryService->markShipped($purchaseOrder, $request->user());

            return response()->json([
                'message'        => 'Purchase Order sent to supplier.',
                'purchase_order' => $po,
            ]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
