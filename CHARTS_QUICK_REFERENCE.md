# Charts - Quick Reference Guide

## 🚀 Quick Start

### 1. Line Chart (Trend Over Time)
```blade
<x-charts.line-chart 
    chart-id="salesTrend"
    title="Sales Trend"
    subtitle="Monthly sales data"
    :labels="['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']"
    :datasets="[
        [
            'label' => 'Sales',
            'data' => [100, 150, 200, 180, 220, 250],
            'borderColor' => 'rgba(54, 162, 235, 1)',
            'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
            'borderWidth' => 2,
            'fill' => true,
            'tension' => 0.4,
        ]
    ]"
    height="300"
/>
```

### 2. Bar Chart (Comparison)
```blade
<x-charts.bar-chart 
    chart-id="monthlyRevenue"
    title="Monthly Revenue"
    subtitle="Revenue per month"
    :labels="['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']"
    :datasets="[
        [
            'label' => 'Revenue (Rp)',
            'data' => [1000000, 1500000, 2000000, 1800000, 2200000, 2500000],
            'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
            'borderColor' => 'rgba(75, 192, 192, 1)',
            'borderWidth' => 1,
        ]
    ]"
    height="300"
/>
```

### 3. Donut Chart (Distribution)
```blade
<x-charts.donut-chart 
    chart-id="poStatus"
    title="PO by Status"
    subtitle="Distribution of PO status"
    :labels="['Approved', 'Pending', 'Rejected', 'Completed']"
    :data="[50, 30, 10, 60]"
    :colors="[
        'rgba(75, 192, 192, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
    ]"
    height="300"
/>
```

---

## 📊 Chart Types & Use Cases

| Chart Type | Best For | Example |
|------------|----------|---------|
| **Line** | Trends over time, continuous data | Sales trend, user growth, cash flow |
| **Bar** | Comparison between categories | Monthly revenue, sales by region |
| **Donut** | Distribution, percentage breakdown | Status distribution, category split |

---

## 🎨 Standard Colors

```php
// In your controller/service
$colors = [
    'primary'   => 'rgba(54, 162, 235, 0.8)',   // Blue
    'success'   => 'rgba(75, 192, 192, 0.8)',   // Green
    'warning'   => 'rgba(255, 206, 86, 0.8)',   // Yellow
    'danger'    => 'rgba(255, 99, 132, 0.8)',   // Red
    'info'      => 'rgba(153, 102, 255, 0.8)',  // Purple
    'secondary' => 'rgba(255, 159, 64, 0.8)',   // Orange
];
```

---

## 📈 Common Patterns

### Pattern 1: Monthly Trend (Last 6 Months)
```php
private function getMonthlyTrend(): array
{
    $labels = [];
    $data = [];

    for ($i = 5; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $labels[] = $date->format('M Y');
        $data[] = Model::whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->count();
    }

    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Count',
            'data' => $data,
            'borderColor' => 'rgba(54, 162, 235, 1)',
            'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
        ]]
    ];
}
```

### Pattern 2: Status Distribution
```php
private function getStatusDistribution(): array
{
    return [
        'labels' => ['Approved', 'Pending', 'Rejected'],
        'data' => [
            Model::where('status', 'approved')->count(),
            Model::where('status', 'pending')->count(),
            Model::where('status', 'rejected')->count(),
        ],
        'colors' => [
            'rgba(75, 192, 192, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(255, 99, 132, 0.8)',
        ]
    ];
}
```

### Pattern 3: Dual-Axis Line Chart
```php
return [
    'labels' => $labels,
    'datasets' => [
        [
            'label' => 'Quantity',
            'data' => $quantityData,
            'borderColor' => 'rgba(54, 162, 235, 1)',
            'yAxisID' => 'y',
        ],
        [
            'label' => 'Value (Rp)',
            'data' => $valueData,
            'borderColor' => 'rgba(75, 192, 192, 1)',
            'yAxisID' => 'y1',
        ]
    ]
];
```

---

## 🔧 Component Props

### Line Chart Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `chartId` | string | 'lineChart' | Unique ID for canvas |
| `title` | string | 'Line Chart' | Chart title |
| `subtitle` | string | null | Chart subtitle |
| `labels` | array | [] | X-axis labels |
| `datasets` | array | [] | Chart datasets |
| `height` | string | '300' | Chart height in px |
| `showLegend` | bool | true | Show/hide legend |
| `showGrid` | bool | true | Show/hide grid |

### Bar Chart Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `chartId` | string | 'barChart' | Unique ID for canvas |
| `title` | string | 'Bar Chart' | Chart title |
| `subtitle` | string | null | Chart subtitle |
| `labels` | array | [] | X-axis labels |
| `datasets` | array | [] | Chart datasets |
| `height` | string | '300' | Chart height in px |
| `showLegend` | bool | true | Show/hide legend |
| `horizontal` | bool | false | Horizontal bars |

### Donut Chart Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `chartId` | string | 'donutChart' | Unique ID for canvas |
| `title` | string | 'Donut Chart' | Chart title |
| `subtitle` | string | null | Chart subtitle |
| `labels` | array | [] | Segment labels |
| `data` | array | [] | Segment values |
| `colors` | array | [] | Segment colors |
| `height` | string | '300' | Chart height in px |
| `showLegend` | bool | true | Show/hide legend |

---

## 🎯 Dataset Configuration

### Line Chart Dataset
```php
[
    'label' => 'Sales',                          // Legend label
    'data' => [100, 150, 200],                   // Data points
    'borderColor' => 'rgba(54, 162, 235, 1)',    // Line color
    'backgroundColor' => 'rgba(54, 162, 235, 0.1)', // Fill color
    'borderWidth' => 2,                          // Line thickness
    'fill' => true,                              // Fill under line
    'tension' => 0.4,                            // Curve smoothness (0-1)
    'pointRadius' => 4,                          // Point size
    'pointHoverRadius' => 6,                     // Point size on hover
]
```

### Bar Chart Dataset
```php
[
    'label' => 'Revenue',
    'data' => [1000000, 1500000, 2000000],
    'backgroundColor' => 'rgba(75, 192, 192, 0.8)', // Bar color
    'borderColor' => 'rgba(75, 192, 192, 1)',       // Bar border
    'borderWidth' => 1,
]
```

---

## 📱 Responsive Design

### Container Setup
```html
<div class="col-xl-6">
    <x-charts.line-chart ... />
</div>
```

### Grid Layouts
```html
<!-- Full Width -->
<div class="col-12">
    <x-charts.line-chart ... />
</div>

<!-- Half Width -->
<div class="col-xl-6">
    <x-charts.bar-chart ... />
</div>

<!-- One Third -->
<div class="col-xl-4">
    <x-charts.donut-chart ... />
</div>
```

---

## 🐛 Common Issues

### Issue: Chart not displaying
```javascript
// Check if Chart.js is loaded
console.log(typeof Chart); // Should be "function"
```

### Issue: Wrong number format
```php
// Use Indonesian format
new Intl.NumberFormat('id-ID').format(1000000)
// Output: "1.000.000"
```

### Issue: Chart too small/large
```blade
<!-- Set explicit height -->
<x-charts.line-chart height="400" ... />
```

### Issue: Colors not showing
```php
// Ensure colors array has enough items
$colors = array_pad($colors, count($data), 'rgba(200, 200, 200, 0.8)');
```

---

## 📚 Examples

### Example 1: Simple Line Chart
```php
// Controller
$chartData = [
    'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    'datasets' => [[
        'label' => 'Orders',
        'data' => [10, 15, 12, 18, 20],
        'borderColor' => 'rgba(54, 162, 235, 1)',
        'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
    ]]
];

return view('dashboard', compact('chartData'));
```

```blade
<!-- View -->
<x-charts.line-chart 
    chart-id="ordersChart"
    title="Daily Orders"
    :labels="$chartData['labels']"
    :datasets="$chartData['datasets']"
/>
```

### Example 2: Multi-Dataset Bar Chart
```php
$chartData = [
    'labels' => ['Q1', 'Q2', 'Q3', 'Q4'],
    'datasets' => [
        [
            'label' => '2025',
            'data' => [100, 150, 180, 200],
            'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
        ],
        [
            'label' => '2026',
            'data' => [120, 170, 190, 220],
            'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
        ]
    ]
];
```

### Example 3: Dynamic Donut Chart
```php
$statuses = ['approved', 'pending', 'rejected'];
$labels = [];
$data = [];
$colors = [
    'rgba(75, 192, 192, 0.8)',
    'rgba(255, 206, 86, 0.8)',
    'rgba(255, 99, 132, 0.8)',
];

foreach ($statuses as $index => $status) {
    $labels[] = ucfirst($status);
    $data[] = Model::where('status', $status)->count();
}

$chartData = compact('labels', 'data', 'colors');
```

---

## 🎓 Tips & Tricks

### Tip 1: Use Consistent Colors
Define colors once, reuse everywhere:
```php
class ChartHelper {
    public static function getColors() {
        return [
            'primary' => 'rgba(54, 162, 235, 0.8)',
            'success' => 'rgba(75, 192, 192, 0.8)',
            // ...
        ];
    }
}
```

### Tip 2: Cache Chart Data
```php
$chartData = Cache::remember('dashboard_charts', 3600, function() {
    return $this->generateChartData();
});
```

### Tip 3: Lazy Load Charts
Only render charts when visible (for performance):
```javascript
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            renderChart(entry.target);
        }
    });
});
```

### Tip 4: Export Chart as Image
```javascript
const canvas = document.getElementById('myChart');
const url = canvas.toDataURL('image/png');
// Download or save URL
```

---

## 📖 Further Reading

- **Chart.js Docs**: https://www.chartjs.org/docs/
- **Color Theory**: https://www.interaction-design.org/literature/article/the-ultimate-guide-to-color-theory
- **Data Visualization**: https://www.storytellingwithdata.com/

---

**Last Updated**: 15 April 2026  
**Version**: 1.0
