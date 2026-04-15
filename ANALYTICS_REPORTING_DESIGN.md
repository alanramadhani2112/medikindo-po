# Analytics & Reporting System - Design Concept

**Tanggal**: 14 April 2026  
**Tujuan**: Tracking uang masuk/keluar, PO, dan data lainnya per minggu/bulan/tahun  
**Status**: Design Phase

---

## 🎯 Kebutuhan Bisnis

### Yang Ingin Diketahui:

1. **Cash Flow Analysis**
   - Uang masuk per minggu/bulan/tahun
   - Uang keluar per minggu/bulan/tahun
   - Saldo netto per periode
   - Trend naik/turun

2. **Purchase Order Analysis**
   - Total PO per minggu/bulan/tahun
   - Nilai PO per periode
   - PO by status (draft, approved, completed)
   - Top suppliers

3. **Invoice Analysis**
   - Invoice issued per periode
   - Invoice paid vs unpaid
   - Aging analysis (overdue)
   - Payment collection rate

4. **Product Analysis**
   - Top selling products
   - Product by category
   - Stock movement
   - Profit margin per product

5. **Supplier Performance**
   - Delivery time
   - Quality score
   - Total purchase value
   - Payment terms compliance

---

## 📊 Visualisasi yang Direkomendasikan

### 1. Dashboard Overview (Landing Page)

```
┌─────────────────────────────────────────────────────────────┐
│ DASHBOARD - MEDIKINDO PO SYSTEM                             │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│ [Period Selector: Minggu Ini ▼] [Custom Date Range]         │
│                                                               │
│ ┌──────────────┬──────────────┬──────────────┬─────────────┐│
│ │ Kas Masuk    │ Kas Keluar   │ Saldo Netto  │ Total PO    ││
│ │ Rp 50 jt     │ Rp 30 jt     │ Rp 20 jt     │ 25 PO       ││
│ │ ↑ +15%       │ ↓ -5%        │ ↑ +25%       │ ↑ +10%      ││
│ └──────────────┴──────────────┴──────────────┴─────────────┘│
│                                                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ CASH FLOW TREND (Line Chart)                           │ │
│ │                                                         │ │
│ │     50M ┤     ╭─────╮                                  │ │
│ │     40M ┤   ╭─╯     ╰─╮    ← Kas Masuk (Hijau)       │ │
│ │     30M ┤ ╭─╯         ╰─╮  ← Kas Keluar (Merah)      │ │
│ │     20M ┤─╯             ╰─                            │ │
│ │         └─────────────────────────────────            │ │
│ │          Jan  Feb  Mar  Apr  May  Jun                 │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                               │
│ ┌──────────────────────┬──────────────────────────────────┐ │
│ │ TOP 5 PRODUCTS       │ TOP 5 SUPPLIERS                  │ │
│ │ (Bar Chart)          │ (Pie Chart)                      │ │
│ │                      │                                  │ │
│ │ Paracetamol ████████ │     PT Pharma (40%)             │ │
│ │ Amoxicillin ██████   │     PT Medika (30%)             │ │
│ │ Vitamin C   ████     │     PT Supplier (20%)           │ │
│ └──────────────────────┴──────────────────────────────────┘ │
│                                                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ RECENT ACTIVITIES (Timeline)                           │ │
│ │ • 10:30 - PO #PO-123 approved (Rp 5 jt)               │ │
│ │ • 09:15 - Payment received from RS Harapan (Rp 10 jt) │ │
│ │ • 08:00 - Invoice #INV-456 issued (Rp 8 jt)           │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### 2. Payment Analytics Page

```
┌─────────────────────────────────────────────────────────────┐
│ PAYMENT ANALYTICS                                            │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│ [Filter: Tahun 2026 ▼] [Bulan: Semua ▼] [Export PDF/Excel] │
│                                                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ MONTHLY CASH FLOW (Bar Chart)                          │ │
│ │                                                         │ │
│ │ 100M ┤                                                 │ │
│ │  80M ┤  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██│ │
│ │  60M ┤  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██│ │
│ │  40M ┤  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██│ │
│ │  20M ┤  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██  ██│ │
│ │      └──────────────────────────────────────────────  │ │
│ │       Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec │ │
│ │       ██ Masuk  ██ Keluar  ██ Netto                   │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                               │
│ ┌──────────────────────────────────────────────────────────┐│
│ │ DETAILED TABLE                                           ││
│ ├──────┬──────────┬──────────┬──────────┬─────────────────┤│
│ │Period│Kas Masuk │Kas Keluar│Saldo Net │Growth           ││
│ ├──────┼──────────┼──────────┼──────────┼─────────────────┤│
│ │Jan   │50.000.000│30.000.000│20.000.000│ -               ││
│ │Feb   │60.000.000│35.000.000│25.000.000│↑ +25%           ││
│ │Mar   │55.000.000│40.000.000│15.000.000│↓ -40%           ││
│ │Apr   │70.000.000│45.000.000│25.000.000│↑ +67%           ││
│ └──────┴──────────┴──────────┴──────────┴─────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

### 3. Purchase Order Analytics

```
┌─────────────────────────────────────────────────────────────┐
│ PURCHASE ORDER ANALYTICS                                     │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│ [View: Bulan Ini ▼] [Group By: Minggu ▼] [Export]          │
│                                                               │
│ ┌──────────────┬──────────────┬──────────────┬─────────────┐│
│ │ Total PO     │ Total Value  │ Avg PO Value │ Approval    ││
│ │ 125 PO       │ Rp 500 jt    │ Rp 4 jt      │ Rate 95%    ││
│ │ ↑ +20%       │ ↑ +15%       │ ↓ -5%        │ ↑ +2%       ││
│ └──────────────┴──────────────┴──────────────┴─────────────┘│
│                                                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ PO BY STATUS (Donut Chart)                             │ │
│ │                                                         │ │
│ │         ╭───────╮                                       │ │
│ │       ╭─╯       ╰─╮                                     │ │
│ │      │   Draft    │  Draft: 10 (8%)                    │ │
│ │      │  Approved  │  Approved: 80 (64%)                │ │
│ │      │ Completed  │  Completed: 35 (28%)               │ │
│ │       ╰─╮       ╭─╯                                     │ │
│ │         ╰───────╯                                       │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ WEEKLY PO TREND (Line + Bar Combo)                     │ │
│ │                                                         │ │
│ │  40 ┤                                    ●              │ │
│ │  30 ┤                          ●                        │ │
│ │  20 ┤                ●                                  │ │
│ │  10 ┤      ●                                            │ │
│ │     └─────────────────────────────────                 │ │
│ │      W1   W2   W3   W4   (April 2026)                  │ │
│ │      ██ PO Count  ● Cumulative                         │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## 🏗️ Struktur Menu yang Direkomendasikan

### Option 1: Dedicated Analytics Menu

```
Sidebar Menu:
├── Dashboard (Overview)
├── Procurement
│   ├── Purchase Orders
│   ├── Approvals
│   └── Goods Receipt
├── Invoicing
│   ├── Tagihan ke RS/Klinik
│   └── Hutang ke Supplier
├── Payment
│   └── Buku Kas & Pembayaran
├── 📊 Analytics & Reports ← NEW!
│   ├── Cash Flow Analysis
│   ├── Purchase Order Analytics
│   ├── Invoice Analytics
│   ├── Product Performance
│   ├── Supplier Performance
│   └── Custom Reports
├── Master Data
│   ├── Organizations
│   ├── Suppliers
│   ├── Products
│   └── Users
```

### Option 2: Integrated in Each Module

```
Payment Menu:
├── Buku Kas & Pembayaran (List)
├── 📊 Analytics ← Tab baru
│   ├── Monthly Report
│   ├── Yearly Report
│   └── Custom Period

Purchase Order Menu:
├── Purchase Orders (List)
├── 📊 Analytics ← Tab baru
│   ├── Weekly Summary
│   ├── Monthly Summary
│   └── Supplier Comparison
```

### Option 3: Hybrid (RECOMMENDED)

```
1. Dashboard = Overview semua modul
2. Setiap modul punya tab "Analytics" sendiri
3. Menu "Reports" terpisah untuk custom reports
```

---

## 🎨 UI Components yang Dibutuhkan

### 1. Period Selector Component

```blade
<div class="period-selector">
    <div class="btn-group">
        <button class="btn btn-sm {{ $period == 'today' ? 'btn-primary' : 'btn-light' }}">
            Hari Ini
        </button>
        <button class="btn btn-sm {{ $period == 'week' ? 'btn-primary' : 'btn-light' }}">
            Minggu Ini
        </button>
        <button class="btn btn-sm {{ $period == 'month' ? 'btn-primary' : 'btn-light' }}">
            Bulan Ini
        </button>
        <button class="btn btn-sm {{ $period == 'year' ? 'btn-primary' : 'btn-light' }}">
            Tahun Ini
        </button>
        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#customPeriodModal">
            <i class="ki-outline ki-calendar"></i>
            Custom
        </button>
    </div>
</div>
```

### 2. KPI Card with Trend

```blade
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="text-muted fs-7 fw-bold">Total Kas Masuk</span>
                <div class="fs-2x fw-bold text-gray-900 mt-2">
                    Rp {{ number_format($totalIncoming, 0, ',', '.') }}
                </div>
            </div>
            <div class="symbol symbol-50px">
                <span class="symbol-label bg-light-success">
                    <i class="ki-outline ki-arrow-down fs-2x text-success"></i>
                </span>
            </div>
        </div>
        
        {{-- Trend Indicator --}}
        <div class="mt-4">
            @if($trend > 0)
                <span class="badge badge-light-success">
                    <i class="ki-outline ki-arrow-up fs-7"></i>
                    {{ number_format($trend, 1) }}% vs last period
                </span>
            @else
                <span class="badge badge-light-danger">
                    <i class="ki-outline ki-arrow-down fs-7"></i>
                    {{ number_format(abs($trend), 1) }}% vs last period
                </span>
            @endif
        </div>
    </div>
</div>
```

### 3. Chart Component (Using Chart.js)

```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Cash Flow Trend</h3>
    </div>
    <div class="card-body">
        <canvas id="cashFlowChart" height="300"></canvas>
    </div>
</div>

<script>
const ctx = document.getElementById('cashFlowChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Kas Masuk',
            data: [50, 60, 55, 70, 65, 80],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
        }, {
            label: 'Kas Keluar',
            data: [30, 35, 40, 45, 50, 55],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Monthly Cash Flow'
            }
        }
    }
});
</script>
```

---

## 💾 Database Query Examples

### 1. Cash Flow per Bulan

```php
// Controller
public function getCashFlowByMonth($year = null)
{
    $year = $year ?? now()->year;
    
    $data = Payment::selectRaw('
            MONTH(payment_date) as month,
            SUM(CASE WHEN type = "incoming" THEN amount ELSE 0 END) as total_incoming,
            SUM(CASE WHEN type = "outgoing" THEN amount ELSE 0 END) as total_outgoing
        ')
        ->whereYear('payment_date', $year)
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->map(function($item) {
            $item->net_balance = $item->total_incoming - $item->total_outgoing;
            $item->month_name = Carbon::create()->month($item->month)->format('F');
            return $item;
        });
    
    return $data;
}
```

### 2. PO per Minggu

```php
public function getPOByWeek($month = null, $year = null)
{
    $month = $month ?? now()->month;
    $year = $year ?? now()->year;
    
    $data = PurchaseOrder::selectRaw('
            WEEK(created_at, 1) as week_number,
            COUNT(*) as total_po,
            SUM(total_amount) as total_value,
            AVG(total_amount) as avg_value
        ')
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->groupBy('week_number')
        ->orderBy('week_number')
        ->get();
    
    return $data;
}
```

### 3. Comparison dengan Period Sebelumnya

```php
public function getComparisonData($startDate, $endDate)
{
    // Current period
    $current = Payment::whereBetween('payment_date', [$startDate, $endDate])
        ->selectRaw('
            SUM(CASE WHEN type = "incoming" THEN amount ELSE 0 END) as incoming,
            SUM(CASE WHEN type = "outgoing" THEN amount ELSE 0 END) as outgoing
        ')
        ->first();
    
    // Previous period (same duration)
    $duration = $startDate->diffInDays($endDate);
    $prevStart = $startDate->copy()->subDays($duration + 1);
    $prevEnd = $startDate->copy()->subDay();
    
    $previous = Payment::whereBetween('payment_date', [$prevStart, $prevEnd])
        ->selectRaw('
            SUM(CASE WHEN type = "incoming" THEN amount ELSE 0 END) as incoming,
            SUM(CASE WHEN type = "outgoing" THEN amount ELSE 0 END) as outgoing
        ')
        ->first();
    
    // Calculate growth
    $incomingGrowth = $previous->incoming > 0 
        ? (($current->incoming - $previous->incoming) / $previous->incoming) * 100 
        : 0;
    
    $outgoingGrowth = $previous->outgoing > 0 
        ? (($current->outgoing - $previous->outgoing) / $previous->outgoing) * 100 
        : 0;
    
    return [
        'current' => $current,
        'previous' => $previous,
        'growth' => [
            'incoming' => round($incomingGrowth, 2),
            'outgoing' => round($outgoingGrowth, 2),
        ]
    ];
}
```

---

## 📈 Chart Library Recommendations

### Option 1: Chart.js (RECOMMENDED)
**Pros**:
- ✅ Free & open source
- ✅ Lightweight
- ✅ Easy to use
- ✅ Responsive
- ✅ Good documentation

**Cons**:
- ❌ Limited advanced features

**Installation**:
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

### Option 2: ApexCharts
**Pros**:
- ✅ Modern & beautiful
- ✅ Interactive
- ✅ Many chart types
- ✅ Good for dashboards

**Cons**:
- ❌ Slightly heavier

**Installation**:
```html
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
```

### Option 3: Metronic Built-in Charts
**Pros**:
- ✅ Already integrated
- ✅ Consistent styling
- ✅ No extra installation

**Cons**:
- ❌ Limited customization

---

## 🎯 Implementation Priority

### Phase 1: Basic Analytics (Week 1-2)
1. ✅ Period selector component
2. ✅ KPI cards with trend
3. ✅ Monthly cash flow chart
4. ✅ Basic data table
5. ✅ Export to Excel

### Phase 2: Advanced Analytics (Week 3-4)
1. ✅ Weekly/Yearly views
2. ✅ Comparison charts
3. ✅ Top products/suppliers
4. ✅ Drill-down capability
5. ✅ Export to PDF

### Phase 3: Predictive Analytics (Week 5-6)
1. ✅ Forecast next month
2. ✅ Trend analysis
3. ✅ Anomaly detection
4. ✅ Recommendations
5. ✅ Alerts & notifications

---

## 📱 Responsive Design

### Desktop (≥992px)
- Full charts with legends
- Side-by-side comparisons
- Detailed tables

### Tablet (768px - 991px)
- Stacked charts
- Simplified legends
- Scrollable tables

### Mobile (<768px)
- Compact charts
- Swipeable cards
- Minimal tables
- Focus on KPIs

---

## 🔐 Permission & Access Control

```php
// Permissions
'view_analytics'           => Super Admin, Finance
'view_payment_analytics'   => Super Admin, Finance
'view_po_analytics'        => Super Admin, Procurement
'export_reports'           => Super Admin, Finance
'view_all_organizations'   => Super Admin only
```

---

## 📊 Sample Implementation

Apakah Anda ingin saya implementasikan salah satu dari ini:

1. **Payment Analytics Page** - Complete dengan charts
2. **Dashboard Enhancement** - Tambah analytics widgets
3. **Period Selector Component** - Reusable component
4. **Export to Excel/PDF** - Report generation

Pilih mana yang paling prioritas, saya akan implementasikan sekarang! 😊
