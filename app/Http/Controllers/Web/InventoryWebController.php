<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryWebController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {
    }

    /**
     * Display inventory dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        // Get filter parameters
        $search = $request->input('search');
        $status = $request->input('status'); // 'low_stock', 'expiring', 'expired'

        // Base query
        $query = InventoryItem::with('product')
            ->where('organization_id', $organizationId);

        // Apply search
        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status === 'low_stock') {
            $query->lowStock();
        } elseif ($status === 'expiring') {
            $query->expiringSoon();
        } elseif ($status === 'expired') {
            $query->expired();
        }

        $inventoryItems = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get summary stats
        $stats = [
            'total_products' => InventoryItem::where('organization_id', $organizationId)
                ->distinct('product_id')->count('product_id'),
            'total_stock' => InventoryItem::where('organization_id', $organizationId)
                ->sum('quantity_on_hand'),
            'low_stock_count' => InventoryItem::where('organization_id', $organizationId)
                ->lowStock()->count(),
            'expiring_soon_count' => InventoryItem::where('organization_id', $organizationId)
                ->expiringSoon()->count(),
        ];

        return view('inventory.index', compact('inventoryItems', 'stats', 'search', 'status'));
    }

    /**
     * Show inventory details for a specific product
     */
    public function show(Product $product)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        // Get all batches for this product
        $inventoryItems = InventoryItem::where('organization_id', $organizationId)
            ->where('product_id', $product->id)
            ->with('movements.creator')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get total available stock
        $totalAvailable = $this->inventoryService->getAvailableStock($organizationId, $product->id);

        return view('inventory.show', compact('product', 'inventoryItems', 'totalAvailable'));
    }

    /**
     * Show stock movement history
     */
    public function movements(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        // Get filter parameters
        $productId = $request->input('product_id');
        $movementType = $request->input('movement_type');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Base query
        $query = InventoryMovement::with(['inventoryItem.product', 'creator'])
            ->whereHas('inventoryItem', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            });

        // Apply filters
        if ($productId) {
            $query->whereHas('inventoryItem', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        if ($movementType) {
            $query->where('movement_type', $movementType);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get products for filter dropdown
        $products = Product::whereHas('inventoryItems', function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->orderBy('name')->get();

        return view('inventory.movements', compact('movements', 'products', 'productId', 'movementType', 'dateFrom', 'dateTo'));
    }

    /**
     * Show low stock alerts
     */
    public function lowStock()
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        $lowStockItems = $this->inventoryService->getLowStockItems($organizationId);

        return view('inventory.low-stock', compact('lowStockItems'));
    }

    /**
     * Show expiring items
     */
    public function expiring()
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        $expiringItems = $this->inventoryService->getExpiringItems($organizationId, 60);
        $expiredItems = $this->inventoryService->getExpiredItems($organizationId);

        return view('inventory.expiring', compact('expiringItems', 'expiredItems'));
    }

    /**
     * Show stock adjustment form
     */
    public function adjustForm(InventoryItem $inventoryItem)
    {
        $this->authorize('manage_inventory');

        return view('inventory.adjust', compact('inventoryItem'));
    }

    /**
     * Process stock adjustment
     */
    public function adjust(Request $request, InventoryItem $inventoryItem)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'quantity_change' => 'required|integer|not_in:0',
            'notes' => 'required|string|max:500',
        ]);

        try {
            $this->inventoryService->adjustStock(
                inventoryItemId: $inventoryItem->id,
                quantityChange: $validated['quantity_change'],
                notes: $validated['notes'],
                createdBy: Auth::id()
            );

            return redirect()->route('inventory.show', $inventoryItem->product_id)
                ->with('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
