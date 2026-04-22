<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        if (!$request->user()->can('view_reports')) {
            abort(403);
        }

        // Get period parameters
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Calculate date range
        [$start, $end] = $this->calculateDateRange($period, $startDate, $endDate);

        // Get analytics data
        $weeklySales = $this->getWeeklySalesTrend();
        $monthlySales = $this->getMonthlySalesTrend();
        $yearlySales = $this->getYearlySalesTrend();
        $topProducts = $this->getTopProducts($start, $end);
        $salesByCategory = $this->getSalesByCategory($start, $end);

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('web.dashboard')],
            ['label' => 'Product Sales Analytics']
        ];

        return view('analytics.products', compact(
            'weeklySales',
            'monthlySales',
            'yearlySales',
            'topProducts',
            'salesByCategory',
            'breadcrumbs',
            'period'
        ));
    }

    /**
     * Calculate date range based on period
     */
    private function calculateDateRange(string $period, ?string $startDate, ?string $endDate): array
    {
        switch ($period) {
            case 'week':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'year':
                return [now()->startOfYear(), now()->endOfYear()];
            case 'custom':
                return [
                    $startDate ? \Carbon\Carbon::parse($startDate) : now()->startOfMonth(),
                    $endDate ? \Carbon\Carbon::parse($endDate) : now()->endOfMonth()
                ];
            default:
                return [now()->startOfMonth(), now()->endOfMonth()];
        }
    }

    /**
     * Get weekly sales trend (last 4 weeks)
     */
    private function getWeeklySalesTrend(): array
    {
        $labels = [];
        $data = [];

        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $labels[] = $weekStart->format('d M') . ' - ' . $weekEnd->format('d M');
            
            $data[] = PurchaseOrderItem::whereHas('purchaseOrder', function($q) use ($weekStart, $weekEnd) {
                    $q->whereIn('status', ['approved', 'completed'])
                      ->whereBetween('created_at', [$weekStart, $weekEnd]);
                })
                ->sum('quantity');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Unit Terjual',
                    'data' => $data,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ]
            ]
        ];
    }

    /**
     * Get monthly sales trend (last 12 months)
     */
    private function getMonthlySalesTrend(): array
    {
        $labels = [];
        $quantityData = [];
        $valueData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            // Quantity
            $quantityData[] = PurchaseOrderItem::whereHas('purchaseOrder', function($q) use ($date) {
                    $q->whereIn('status', ['approved', 'completed'])
                      ->whereYear('created_at', $date->year)
                      ->whereMonth('created_at', $date->month);
                })
                ->sum('quantity');
            
            // Value
            $valueData[] = PurchaseOrderItem::whereHas('purchaseOrder', function($q) use ($date) {
                    $q->whereIn('status', ['approved', 'completed'])
                      ->whereYear('created_at', $date->year)
                      ->whereMonth('created_at', $date->month);
                })
                ->sum(DB::raw('quantity * unit_price'));
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Unit Terjual',
                    'data' => $quantityData,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Nilai (Rp)',
                    'data' => $valueData,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ]
            ]
        ];
    }

    /**
     * Get yearly sales trend (last 3 years)
     */
    private function getYearlySalesTrend(): array
    {
        $labels = [];
        $data = [];

        for ($i = 2; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            $labels[] = $year;
            
            $data[] = PurchaseOrderItem::whereHas('purchaseOrder', function($q) use ($year) {
                    $q->whereIn('status', ['approved', 'completed'])
                      ->whereYear('created_at', $year);
                })
                ->sum(DB::raw('quantity * unit_price'));
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Penjualan (Rp)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ]
            ]
        ];
    }

    /**
     * Get top products by sales
     */
    private function getTopProducts($startDate, $endDate, int $limit = 10): \Illuminate\Support\Collection
    {
        return DB::table('purchase_order_items')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->whereIn('purchase_orders.status', ['approved', 'completed'])
            ->whereBetween('purchase_orders.created_at', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.category_regulatory',
                DB::raw('SUM(purchase_order_items.quantity) as total_quantity'),
                DB::raw('SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total_value'),
                DB::raw('COUNT(DISTINCT purchase_orders.id) as order_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.category_regulatory')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sales by category
     */
    private function getSalesByCategory($startDate, $endDate): array
    {
        $data = DB::table('purchase_order_items')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->whereIn('purchase_orders.status', ['approved', 'completed'])
            ->whereBetween('purchase_orders.created_at', [$startDate, $endDate])
            ->select(
                'products.category_regulatory',
                DB::raw('SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total_value')
            )
            ->groupBy('products.category_regulatory')
            ->orderByDesc('total_value')
            ->get();

        // Map enum values to Indonesian labels
        $regulatoryLabels = \App\Models\Product::CATEGORY_REGULATORY;

        return [
            'labels' => $data->pluck('category_regulatory')->map(fn($v) => $regulatoryLabels[$v] ?? ($v ?? 'Tidak Dikategorikan'))->toArray(),
            'data' => $data->pluck('total_value')->toArray(),
            'colors' => [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
            ]
        ];
    }
}
