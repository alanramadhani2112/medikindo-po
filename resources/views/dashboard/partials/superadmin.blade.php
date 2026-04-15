{{-- SUPER ADMIN DASHBOARD --}}
{{-- Purpose: System monitoring with grouped metrics --}}

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Dashboard System Monitoring</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang, {{ auth()->user()->name }} - Monitoring sistem secara menyeluruh</p>
    </div>
    <a href="{{ route('web.users.index') }}" class="btn btn-primary">
        <i class="ki-outline ki-setting-2 fs-2"></i>
        Kelola Sistem
    </a>
</div>

{{-- Period Selector --}}
<x-period-selector :current-period="$currentPeriod ?? 'today'" />

{{-- Alerts (Priority) --}}
@if(count($alerts) > 0)
<div class="row g-5 mb-7">
    <div class="col-12">
        @foreach($alerts as $alert)
        <div class="alert alert-{{ $alert['type'] }} d-flex align-items-center p-5 mb-3">
            <i class="ki-outline {{ $alert['icon'] }} fs-2hx text-{{ $alert['type'] }} me-4"></i>
            <div class="d-flex flex-column flex-grow-1">
                <h4 class="mb-1 text-{{ $alert['type'] }} fw-bold">{{ $alert['title'] }}</h4>
                <span class="fs-6">{{ $alert['message'] }}</span>
            </div>
            @if(isset($alert['action']))
            <a href="{{ $alert['action'] }}" class="btn btn-{{ $alert['type'] }} btn-sm">
                Lihat Detail
                <i class="ki-outline ki-right fs-5 ms-1"></i>
            </a>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- GROUPED CARDS --}}
@foreach($cardGroups as $group)
<div class="mb-7">
    {{-- Group Header --}}
    <div class="d-flex align-items-center mb-5">
        <div class="symbol symbol-40px me-3">
            <div class="symbol-label bg-light-{{ $group['color'] }}">
                <i class="ki-outline {{ $group['icon'] }} fs-2 text-{{ $group['color'] }}"></i>
            </div>
        </div>
        <div>
            <h3 class="fs-2 fw-bold text-gray-900 mb-0">{{ $group['title'] }}</h3>
        </div>
    </div>

    {{-- Cards in Group --}}
    <div class="row g-5 g-xl-8">
        @foreach($group['cards'] as $card)
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush h-100 {{ isset($card['alert']) && $card['alert'] ? 'border border-' . $card['color'] . ' border-2' : '' }}">
                <div class="card-body d-flex flex-column justify-content-between p-6">
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <div class="d-flex flex-column flex-grow-1 me-3">
                            <span class="text-gray-500 fw-semibold fs-7 mb-2">{{ $card['label'] }}</span>
                            <span class="text-gray-900 fw-bold fs-2x">{{ $card['value'] }}</span>
                            
                            {{-- Growth Indicator --}}
                            @if(isset($card['growth']))
                            <div class="d-flex align-items-center mt-2">
                                @if($card['growth']['direction'] === 'up')
                                    <i class="ki-outline ki-arrow-up fs-5 text-{{ $card['growth']['color'] }} me-1"></i>
                                @elseif($card['growth']['direction'] === 'down')
                                    <i class="ki-outline ki-arrow-down fs-5 text-{{ $card['growth']['color'] }} me-1"></i>
                                @else
                                    <i class="ki-outline ki-minus fs-5 text-{{ $card['growth']['color'] }} me-1"></i>
                                @endif
                                <span class="text-{{ $card['growth']['color'] }} fw-bold fs-7">{{ $card['growth']['percentage'] }}%</span>
                                <span class="text-gray-500 fs-8 ms-1">{{ $card['sub'] ?? '' }}</span>
                            </div>
                            @elseif(isset($card['sub']))
                            <span class="text-gray-600 fs-7 mt-1">{{ $card['sub'] }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-center bg-light-{{ $card['color'] }} rounded" style="width:60px;height:60px;">
                            <i class="ki-outline {{ $card['icon'] }} fs-2x text-{{ $card['color'] }}"></i>
                        </div>
                    </div>
                    @if(isset($card['alert']) && $card['alert'])
                    <div class="d-flex align-items-center">
                        <i class="ki-outline ki-information fs-5 text-{{ $card['color'] }} me-2"></i>
                        <span class="text-{{ $card['color'] }} fw-semibold fs-7">Memerlukan perhatian</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

{{-- CHARTS SECTION --}}
@if(isset($chartData))
<div class="mb-7">
    {{-- Section Header --}}
    <div class="d-flex align-items-center mb-5">
        <div class="symbol symbol-40px me-3">
            <div class="symbol-label bg-light-info">
                <i class="ki-outline ki-chart-simple-2 fs-2 text-info"></i>
            </div>
        </div>
        <div>
            <h3 class="fs-2 fw-bold text-gray-900 mb-0">Visual Analytics</h3>
            <span class="text-muted fs-7">Grafik dan trend data sistem</span>
        </div>
    </div>

    {{-- Row 1: PO Status & Monthly PO Trend --}}
    <div class="row g-5 g-xl-8 mb-7">
        {{-- PO Status Distribution --}}
        <div class="col-xl-4">
            <x-charts.donut-chart 
                chart-id="poStatusChart"
                title="PO by Status"
                subtitle="Distribusi status purchase order"
                :labels="$chartData['poStatus']['labels']"
                :data="$chartData['poStatus']['data']"
                :colors="$chartData['poStatus']['colors']"
                height="350"
            />
        </div>

        {{-- Monthly PO Trend --}}
        <div class="col-xl-8">
            <x-charts.line-chart 
                chart-id="monthlyPOTrendChart"
                title="Monthly PO Trend"
                subtitle="Trend jumlah PO 6 bulan terakhir"
                :labels="$chartData['monthlyPOTrend']['labels']"
                :datasets="$chartData['monthlyPOTrend']['datasets']"
                height="350"
            />
        </div>
    </div>

    {{-- Row 2: Monthly Revenue & Cash Flow --}}
    <div class="row g-5 g-xl-8 mb-7">
        {{-- Monthly Revenue --}}
        <div class="col-xl-6">
            <x-charts.bar-chart 
                chart-id="monthlyRevenueChart"
                title="Monthly Revenue"
                subtitle="Total nilai PO per bulan (6 bulan terakhir)"
                :labels="$chartData['monthlyRevenue']['labels']"
                :datasets="$chartData['monthlyRevenue']['datasets']"
                height="350"
            />
        </div>

        {{-- Cash Flow Trend --}}
        <div class="col-xl-6">
            <x-charts.line-chart 
                chart-id="cashFlowChart"
                title="Cash Flow Trend"
                subtitle="Kas masuk & keluar 30 hari terakhir"
                :labels="$chartData['cashFlowTrend']['labels']"
                :datasets="$chartData['cashFlowTrend']['datasets']"
                height="350"
            />
        </div>
    </div>
</div>
@endif

{{-- Main Content Row --}}
<div class="row g-5 g-xl-8 mb-7">
    {{-- Recent Activity Table --}}
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Aktivitas Sistem Terbaru</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Log aktivitas pengguna dan sistem</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('web.dashboard.audit') }}" class="btn btn-sm btn-light-primary">
                        Lihat Semua
                        <i class="ki-outline ki-right fs-5 ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-150px rounded-start">User</th>
                                <th class="min-w-200px">Aktivitas</th>
                                <th class="min-w-100px">Tipe</th>
                                <th class="text-end pe-4 min-w-120px rounded-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $log)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-6">{{ $log->user->name ?? 'System' }}</span>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $log->user->email ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6">{{ $log->description }}</span>
                                </td>
                                <td>
                                    @php
                                        $actionColor = match($log->action) {
                                            'create' => 'success',
                                            'update' => 'info',
                                            'delete' => 'danger',
                                            'error' => 'danger',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <span class="badge badge-light-{{ $actionColor }} fs-7 fw-semibold">{{ strtoupper($log->action) }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6">{{ $log->occurred_at->format('d M Y H:i') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold">Belum ada aktivitas tercatat</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="card-label fw-bold text-gray-900 fs-3">Aksi Cepat</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex flex-column gap-3">
                    @can('manage_users')
                    <a href="{{ route('web.users.index') }}" class="btn btn-light-primary justify-content-start text-start">
                        <i class="ki-outline ki-twitter fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Manage Users</div>
                            <div class="text-muted fs-7">Kelola pengguna sistem</div>
                        </div>
                    </a>
                    @endcan
                    @can('manage_products')
                    <a href="{{ route('web.products.index') }}" class="btn btn-light-success justify-content-start text-start">
                        <i class="ki-outline ki-paintbucket fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Manage Products</div>
                            <div class="text-muted fs-7">Kelola master produk</div>
                        </div>
                    </a>
                    @endcan
                    @can('manage_organizations')
                    <a href="{{ route('web.organizations.index') }}" class="btn btn-light-info justify-content-start text-start">
                        <i class="ki-outline ki-bank fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Manage Organizations</div>
                            <div class="text-muted fs-7">Kelola organisasi/faskes</div>
                        </div>
                    </a>
                    @endcan
                    @can('manage_suppliers')
                    <a href="{{ route('web.suppliers.index') }}" class="btn btn-light-warning justify-content-start text-start">
                        <i class="ki-outline ki-cube-2-3 fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Manage Suppliers</div>
                            <div class="text-muted fs-7">Kelola data supplier</div>
                        </div>
                    </a>
                    @endcan
                    <a href="{{ route('web.dashboard.audit') }}" class="btn btn-light-dark justify-content-start text-start">
                        <i class="ki-outline ki-file fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Audit Logs</div>
                            <div class="text-muted fs-7">Lihat log sistem lengkap</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- System Errors Table (if any) --}}
@if(count($auditLogs) > 0)
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush border border-danger border-2">
            <div class="card-header border-0 pt-6 bg-light-danger">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-danger fs-3">
                        System Errors & Failed Transactions
                    </span>
                    <span class="text-danger mt-1 fw-semibold fs-7">Log error dan transaksi gagal yang memerlukan perhatian segera</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('web.dashboard.audit') }}" class="btn btn-sm btn-danger">
                        Lihat Semua
                        <i class="ki-outline ki-right fs-5 ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-150px rounded-start">User</th>
                                <th class="min-w-200px">Error Description</th>
                                <th class="min-w-100px">Severity</th>
                                <th class="text-end pe-4 min-w-120px rounded-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs as $log)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-6">{{ $log->user->name ?? 'System' }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6">{{ $log->description }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-danger fs-7 fw-semibold">
                                        <i class="ki-outline ki-arrow-zigzag-circle fs-6 me-1"></i>
                                        ERROR
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6">{{ $log->occurred_at->format('d M Y H:i') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif


{{-- ANALYTICS SECTION --}}
@if(isset($analytics))
<div class="mb-7">
    {{-- Section Header --}}
    <div class="d-flex align-items-center mb-5">
        <div class="symbol symbol-40px me-3">
            <div class="symbol-label bg-light-primary">
                <i class="ki-outline ki-chart-simple fs-2 text-primary"></i>
            </div>
        </div>
        <div>
            <h3 class="fs-2 fw-bold text-gray-900 mb-0">Analytics & Insights</h3>
            <span class="text-muted fs-7">Data produk, supplier, dan rekomendasi</span>
        </div>
    </div>

    {{-- Purchase Summary Cards --}}
    <div class="row g-5 g-xl-8 mb-7">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush bg-light-success h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-success fw-bold fs-7">TOTAL PEMBELIAN</span>
                        <i class="ki-outline ki-paintbucket fs-2x text-success"></i>
                    </div>
                    <div class="fw-bold fs-2x text-gray-900 mb-2">{{ number_format($analytics['purchaseSummary']['total_quantity'], 0, ',', '.') }}</div>
                    <div class="text-gray-600 fs-7">Unit produk terbeli</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush bg-light-primary h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-primary fw-bold fs-7">NILAI PEMBELIAN</span>
                        <i class="ki-outline ki-entrance-right fs-2x text-primary"></i>
                    </div>
                    <div class="fw-bold fs-2x text-gray-900 mb-2">Rp {{ number_format($analytics['purchaseSummary']['total_value'], 0, ',', '.') }}</div>
                    <div class="text-gray-600 fs-7">Total nilai transaksi</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush bg-light-info h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-info fw-bold fs-7">BULAN INI</span>
                        <i class="ki-outline ki-calendar fs-2x text-info"></i>
                    </div>
                    <div class="fw-bold fs-2x text-gray-900 mb-2">{{ number_format($analytics['purchaseSummary']['month_quantity'], 0, ',', '.') }}</div>
                    <div class="text-gray-600 fs-7">Unit dibeli bulan ini</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush bg-light-warning h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-warning fw-bold fs-7">AVG ORDER VALUE</span>
                        <i class="ki-outline ki-courier-up fs-2x text-warning"></i>
                    </div>
                    <div class="fw-bold fs-2x text-gray-900 mb-2">Rp {{ number_format($analytics['purchaseSummary']['avg_order_value'], 0, ',', '.') }}</div>
                    <div class="text-gray-600 fs-7">Rata-rata nilai PO</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Products & Top Suppliers --}}
    <div class="row g-5 g-xl-8 mb-7">
        {{-- Top Products --}}
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">
                            Top 10 Produk Terlaris
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Produk dengan pembelian tertinggi</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">#</th>
                                    <th class="min-w-200px">Produk</th>
                                    <th class="min-w-100px text-end">Qty</th>
                                    <th class="min-w-120px text-end">Nilai</th>
                                    <th class="text-end pe-4 rounded-end">Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['topProducts'] as $index => $product)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge badge-light-primary fs-7 fw-bold">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold fs-6">{{ $product->name }}</span>
                                            <span class="text-gray-500 fs-7">{{ $product->sku }}</span>
                                            @if($product->is_narcotic)
                                                <span class="badge badge-danger fs-8 mt-1" style="width: fit-content;">NARKOTIKA</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-900 fw-bold">{{ number_format($product->total_quantity, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-900 fw-semibold">Rp {{ number_format($product->total_value, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="badge badge-light-info">{{ $product->order_count }} PO</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10">
                                        <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                        <div class="text-gray-700 fs-6">Belum ada data pembelian</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Suppliers --}}
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">
                            Top 10 Supplier Terpercaya
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Supplier dengan order terbanyak</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">#</th>
                                    <th class="min-w-200px">Supplier</th>
                                    <th class="min-w-100px text-end">Orders</th>
                                    <th class="text-end pe-4 rounded-end">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['topSuppliers'] as $index => $supplier)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge badge-light-primary fs-7 fw-bold">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold fs-6">{{ $supplier->name }}</span>
                                            <span class="text-gray-500 fs-7">{{ $supplier->phone }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-light-success fs-7 fw-bold">{{ $supplier->order_count }} PO</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="text-gray-900 fw-semibold">Rp {{ number_format($supplier->total_value, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10">
                                        <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                        <div class="text-gray-700 fs-6">Belum ada data supplier</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Slow Moving Products & Recommendations --}}
    <div class="row g-5 g-xl-8">
        {{-- Slow Moving Products --}}
        <div class="col-xl-6">
            <div class="card card-flush h-100 border border-warning border-2">
                <div class="card-header border-0 pt-6 bg-light-warning">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-warning fs-3">
                            Produk Slow Moving
                        </span>
                        <span class="text-warning mt-1 fw-semibold fs-7">Produk dengan pembelian rendah (6 bulan terakhir)</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-200px rounded-start">Produk</th>
                                    <th class="min-w-80px text-center">Orders</th>
                                    <th class="text-end pe-4 min-w-120px rounded-end">Last Purchase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['slowMovingProducts'] as $product)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold fs-6">{{ $product->name }}</span>
                                            <span class="text-gray-500 fs-7">{{ $product->sku }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-warning fs-7">{{ $product->order_count }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($product->last_purchase_date)
                                            <span class="text-gray-700 fs-7">{{ \Carbon\Carbon::parse($product->last_purchase_date)->format('d M Y') }}</span>
                                        @else
                                            <span class="text-gray-400 fs-7 fst-italic">Belum pernah</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-10">
                                        <i class="ki-outline ki-check-circle fs-3x text-success mb-3"></i>
                                        <div class="text-gray-700 fs-6">Semua produk bergerak dengan baik</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recommendations --}}
        <div class="col-xl-6">
            <div class="card card-flush h-100 border border-info border-2">
                <div class="card-header border-0 pt-6 bg-light-info">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-info fs-3">
                            Rekomendasi Smart
                        </span>
                        <span class="text-info mt-1 fw-semibold fs-7">Insight dan saran berdasarkan data</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    @forelse($analytics['recommendations'] as $recommendation)
                    <div class="alert alert-{{ $recommendation['color'] }} d-flex align-items-start p-4 mb-3">
                        <i class="ki-outline {{ $recommendation['icon'] }} fs-2x text-{{ $recommendation['color'] }} me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h5 class="mb-0 text-{{ $recommendation['color'] }} fw-bold">{{ $recommendation['title'] }}</h5>
                                <span class="badge badge-{{ $recommendation['color'] }} ms-auto">{{ strtoupper($recommendation['priority']) }}</span>
                            </div>
                            <p class="mb-0 text-gray-700 fs-6">{{ $recommendation['message'] }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-10">
                        <i class="ki-outline ki-check-circle fs-3x text-success mb-3"></i>
                        <div class="text-gray-700 fs-6">Tidak ada rekomendasi saat ini</div>
                        <div class="text-gray-500 fs-7">Sistem berjalan dengan optimal</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endif
