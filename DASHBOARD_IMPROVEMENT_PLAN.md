# 📊 Dashboard Improvement Plan

**Tanggal**: 15 April 2026  
**Tujuan**: Meningkatkan UX dashboard dengan fokus pada visualisasi data dan akses cepat

---

## 🎯 Permintaan User

### **1. Pindahkan Aksi Cepat ke Atas Dashboard**
- **Current**: Aksi Cepat ada di sidebar kanan (col-xl-4)
- **New**: Pindahkan ke atas dashboard sebelum summary cards
- **Layout**: Horizontal cards dengan icon besar

### **2. Perbaiki Tampilan Log**
- **Current**: Table log terlalu panjang dan kurang informatif
- **New**: Compact timeline view dengan grouping by date
- **Features**: 
  - Timeline vertical dengan icon
  - Grouping by date
  - Color coding by action type
  - Limit 10 items dengan "Load More"

### **3. Ganti Period Selector dengan Chart Filter**
- **Current**: Button-based period selector (Hari Ini, Minggu Ini, dll)
- **New**: Visual chart dengan filter dropdown
- **Features**:
  - Chart untuk melihat pendapatan/PO per periode
  - Filter: Minggu, Bulan, Tahun
  - Interactive chart (hover untuk detail)
  - Summary metrics di atas chart

---

## 📐 New Dashboard Layout

```
┌─────────────────────────────────────────────────────────────┐
│ HEADER: Dashboard System Monitoring                         │
│ [Kelola Sistem Button]                                      │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ AKSI CEPAT (Horizontal Cards)                               │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│ │ 👤 Users │ │ 💊 Produk│ │ 🏢 Org   │ │ 🚚 Supp  │       │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ CHART FILTER & VISUALIZATION                                │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ [Minggu ▼] [Bulan ▼] [Tahun ▼]                         │ │
│ │                                                          │ │
│ │ Total PO: 150  |  Total Value: Rp 500M  |  Avg: Rp 3.3M│ │
│ │                                                          │ │
│ │     📊 CHART (Bar/Line)                                 │ │
│ │     ┌─┐                                                 │ │
│ │     │█│     ┌─┐                                         │ │
│ │     │█│ ┌─┐ │█│                                         │ │
│ │     │█│ │█│ │█│ ┌─┐                                     │ │
│ │ ────┴─┴─┴─┴─┴─┴─┴─┴────                                │ │
│ │     W1  W2  W3  W4                                      │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ ALERTS (if any)                                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ GROUPED CARDS (Business Activity, Operations, etc)          │
└─────────────────────────────────────────────────────────────┘

┌──────────────────────────────────┬──────────────────────────┐
│ AKTIVITAS SISTEM TERBARU         │ (removed - too long)     │
│ (Timeline View - Compact)        │                          │
│                                  │                          │
│ 📅 15 Apr 2026                   │                          │
│ ├─ 04:00 • PO.CREATED           │                          │
│ │  Alan Ramadhani                │                          │
│ │  Created PO #PO-001            │                          │
│ │                                 │                          │
│ ├─ 03:51 • INVOICE.CALCULATED    │                          │
│ │  Alan Ramadhani                │                          │
│ │  Calculated invoice totals     │                          │
│ │                                 │                          │
│ └─ [Load More...]                │                          │
└──────────────────────────────────┴──────────────────────────┘
```

---

## 🎨 Component Design

### **1. Quick Actions Component**

**File**: `resources/views/components/dashboard/quick-actions.blade.php`

```blade
<div class="row g-5 mb-7">
    @foreach($actions as $action)
    <div class="col-6 col-md-3">
        <a href="{{ $action['url'] }}" class="card card-flush bg-light-{{ $action['color'] }} hoverable h-100">
            <div class="card-body text-center p-6">
                <i class="ki-outline {{ $action['icon'] }} fs-3x text-{{ $action['color'] }} mb-3"></i>
                <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ $action['label'] }}</h3>
                <span class="text-gray-600 fs-7">{{ $action['description'] }}</span>
            </div>
        </a>
    </div>
    @endforeach
</div>
```

---

### **2. Chart Filter Component**

**File**: `resources/views/components/dashboard/chart-filter.blade.php`

```blade
<div class="card card-flush mb-7">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title">
            <span class="card-label fw-bold text-gray-900 fs-3">Analisis Pendapatan</span>
        </h3>
        <div class="card-toolbar">
            <select class="form-select form-select-sm w-150px" id="chartPeriodFilter">
                <option value="week">Per Minggu</option>
                <option value="month" selected>Per Bulan</option>
                <option value="year">Per Tahun</option>
            </select>
        </div>
    </div>
    <div class="card-body pt-3">
        {{-- Summary Metrics --}}
        <div class="row g-5 mb-7">
            <div class="col-4">
                <div class="text-center">
                    <span class="text-gray-600 fs-7 d-block mb-2">Total PO</span>
                    <span class="fs-2x fw-bold text-gray-900" id="totalPO">{{ $summary['total_po'] }}</span>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center">
                    <span class="text-gray-600 fs-7 d-block mb-2">Total Nilai</span>
                    <span class="fs-2x fw-bold text-primary" id="totalValue">Rp {{ number_format($summary['total_value'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center">
                    <span class="text-gray-600 fs-7 d-block mb-2">Rata-rata</span>
                    <span class="fs-2x fw-bold text-success" id="avgValue">Rp {{ number_format($summary['avg_value'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Chart Canvas --}}
        <div class="chart-container" style="position: relative; height:350px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>
```

---

### **3. Activity Timeline Component**

**File**: `resources/views/components/dashboard/activity-timeline.blade.php`

```blade
<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title">
            <span class="card-label fw-bold text-gray-900 fs-3">Aktivitas Sistem Terbaru</span>
        </h3>
    </div>
    <div class="card-body pt-3">
        <div class="timeline timeline-border-dashed">
            @php
                $groupedLogs = $logs->groupBy(function($log) {
                    return $log->occurred_at->format('Y-m-d');
                });
            @endphp

            @foreach($groupedLogs as $date => $dateLogs)
            {{-- Date Header --}}
            <div class="timeline-item mb-5">
                <div class="timeline-line w-40px"></div>
                <div class="timeline-icon symbol symbol-circle symbol-40px">
                    <div class="symbol-label bg-light-primary">
                        <i class="ki-outline ki-calendar fs-2 text-primary"></i>
                    </div>
                </div>
                <div class="timeline-content mb-3 mt-n1">
                    <div class="fw-bold text-gray-800 fs-6">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</div>
                </div>
            </div>

            {{-- Activities for this date --}}
            @foreach($dateLogs->take(5) as $log)
            <div class="timeline-item">
                <div class="timeline-line w-40px"></div>
                <div class="timeline-icon symbol symbol-circle symbol-40px">
                    <div class="symbol-label bg-light-{{ $log->getActionColor() }}">
                        <i class="ki-outline {{ $log->getActionIcon() }} fs-3 text-{{ $log->getActionColor() }}"></i>
                    </div>
                </div>
                <div class="timeline-content mb-5 mt-n1">
                    <div class="d-flex align-items-center mb-1">
                        <span class="text-gray-800 fw-bold fs-6 me-2">{{ $log->occurred_at->format('H:i') }}</span>
                        <span class="badge badge-light-{{ $log->getActionColor() }} fs-8">{{ strtoupper($log->action) }}</span>
                    </div>
                    <div class="text-gray-700 fs-7 mb-1">
                        <span class="fw-semibold">{{ $log->user->name ?? 'System' }}</span>
                    </div>
                    <div class="text-gray-600 fs-7">{{ $log->description }}</div>
                </div>
            </div>
            @endforeach
            @endforeach

            {{-- Load More --}}
            <div class="timeline-item">
                <div class="timeline-line w-40px"></div>
                <div class="timeline-content">
                    <a href="{{ route('web.dashboard.audit') }}" class="btn btn-sm btn-light-primary">
                        <i class="ki-outline ki-right fs-5 me-1"></i>
                        Lihat Semua Log
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 🔧 Backend Changes

### **1. Add Chart Data Method to DashboardService**

```php
/**
 * Get chart data for revenue/PO analysis
 */
public function getChartData(string $period = 'month'): array
{
    $data = [];
    $labels = [];
    
    switch ($period) {
        case 'week':
            // Last 4 weeks
            for ($i = 3; $i >= 0; $i--) {
                $start = now()->subWeeks($i)->startOfWeek();
                $end = now()->subWeeks($i)->endOfWeek();
                
                $labels[] = 'W' . $start->weekOfYear;
                $data[] = PurchaseOrder::whereBetween('created_at', [$start, $end])
                    ->sum('total_amount');
            }
            break;
            
        case 'month':
            // Last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M Y');
                $data[] = PurchaseOrder::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount');
            }
            break;
            
        case 'year':
            // Last 3 years
            for ($i = 2; $i >= 0; $i--) {
                $year = now()->subYears($i)->year;
                $labels[] = $year;
                $data[] = PurchaseOrder::whereYear('created_at', $year)
                    ->sum('total_amount');
            }
            break;
    }
    
    return [
        'labels' => $labels,
        'data' => $data,
        'summary' => [
            'total_po' => array_sum($data) > 0 ? count(array_filter($data)) : 0,
            'total_value' => array_sum($data),
            'avg_value' => count($data) > 0 ? array_sum($data) / count($data) : 0,
        ]
    ];
}
```

### **2. Add Helper Methods to AuditLog Model**

```php
// app/Models/AuditLog.php

public function getActionColor(): string
{
    return match($this->action) {
        'create', 'created' => 'success',
        'update', 'updated' => 'info',
        'delete', 'deleted' => 'danger',
        'error', 'failed' => 'danger',
        'login', 'logout' => 'primary',
        default => 'secondary'
    };
}

public function getActionIcon(): string
{
    return match($this->action) {
        'create', 'created' => 'ki-plus-circle',
        'update', 'updated' => 'ki-notepad-edit',
        'delete', 'deleted' => 'ki-trash',
        'error', 'failed' => 'ki-cross-circle',
        'login' => 'ki-entrance-right',
        'logout' => 'ki-exit-right',
        default => 'ki-information-5'
    };
}
```

---

## 📝 Implementation Steps

### **Phase 1: Create Components** ✅
1. Create `quick-actions.blade.php` component
2. Create `chart-filter.blade.php` component
3. Create `activity-timeline.blade.php` component

### **Phase 2: Update DashboardService** ✅
1. Add `getChartData()` method
2. Add `getQuickActions()` method
3. Update `getSuperAdminDashboard()` to include chart data

### **Phase 3: Update Dashboard Views** ✅
1. Update `superadmin.blade.php` layout
2. Move quick actions to top
3. Replace period selector with chart filter
4. Replace activity table with timeline

### **Phase 4: Add JavaScript** ✅
1. Chart.js integration for revenue chart
2. AJAX for chart filter changes
3. Timeline lazy loading

---

## 🎯 Expected Results

### **Before**
- ❌ Aksi cepat tersembunyi di sidebar
- ❌ Period selector tidak intuitif
- ❌ Log table terlalu panjang dan membosankan
- ❌ Sulit melihat trend data

### **After**
- ✅ Aksi cepat mudah diakses di atas
- ✅ Chart visual untuk melihat trend
- ✅ Timeline compact dan informatif
- ✅ Filter periode yang jelas (Minggu/Bulan/Tahun)
- ✅ Summary metrics di atas chart

---

**Apakah design ini sudah sesuai dengan yang Anda inginkan?**

