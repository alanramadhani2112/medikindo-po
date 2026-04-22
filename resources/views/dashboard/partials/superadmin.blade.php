{{-- ═══════════════════════════════════════════════════════════
     SUPER ADMIN DASHBOARD
     Focus: System-wide monitoring — all metrics at a glance
     ═══════════════════════════════════════════════════════════ --}}

{{-- ── PAGE HEADER ──────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-6">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-1">System Dashboard</h1>
        <p class="text-gray-500 fs-6 mb-0">
            <i class="ki-outline ki-setting-2 fs-6 me-1 text-primary"></i>
            {{ auth()->user()->name }} &mdash; Super Admin &mdash;
            <span class="text-primary fw-semibold">{{ now()->format('d M Y, H:i') }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('web.users.index') }}" class="btn btn-light-primary btn-sm">
            <i class="ki-outline ki-profile-user fs-4 me-1"></i>Users
        </a>
        <a href="{{ route('web.dashboard.audit') }}" class="btn btn-light-dark btn-sm">
            <i class="ki-outline ki-file fs-4 me-1"></i>Audit Log
        </a>
    </div>
</div>

{{-- ── PERIOD SELECTOR ─────────────────────────────────────── --}}
<x-period-selector :current-period="$currentPeriod ?? 'today'" />

{{-- ── CRITICAL ALERTS ─────────────────────────────────────── --}}
@if(count($alerts) > 0)
<div class="mb-6">
    @foreach($alerts as $alert)
    <div class="alert alert-{{ $alert['type'] }} border border-{{ $alert['type'] }} d-flex align-items-center p-4 mb-3">
        <i class="ki-outline {{ $alert['icon'] }} fs-2x text-{{ $alert['type'] }} me-4 flex-shrink-0"></i>
        <div class="flex-grow-1">
            <div class="fw-bold fs-6 text-{{ $alert['type'] }}">{{ $alert['title'] }}</div>
            <div class="fs-7 text-gray-700">{{ $alert['message'] }}</div>
        </div>
        @if(isset($alert['action']))
        <a href="{{ $alert['action'] }}" class="btn btn-sm btn-{{ $alert['type'] }} ms-3">Lihat →</a>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ── GROUPED CARD SECTIONS ───────────────────────────────── --}}
@foreach($cardGroups as $group)
<div class="mb-7">
    <div class="d-flex align-items-center mb-4">
        <div class="symbol symbol-35px me-3">
            <div class="symbol-label bg-light-{{ $group['color'] }}">
                <i class="ki-outline {{ $group['icon'] }} fs-3 text-{{ $group['color'] }}"></i>
            </div>
        </div>
        <h3 class="fs-4 fw-bold text-gray-900 mb-0">{{ $group['title'] }}</h3>
    </div>
    <div class="row g-5 g-xl-8">
        @foreach($group['cards'] as $card)
        <div class="col-6 col-xl-3">
            <div class="card card-flush h-100 {{ isset($card['alert']) && $card['alert'] ? 'border border-' . $card['color'] . ' border-dashed' : '' }}">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-{{ $card['color'] }}">
                            <i class="ki-outline {{ $card['icon'] }} fs-2x text-{{ $card['color'] }}"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">{{ $card['label'] }}</span>
                        <span class="text-gray-900 fw-bold fs-2">{{ $card['value'] }}</span>
                        @if(isset($card['growth']))
                        <div class="d-flex align-items-center gap-1 mt-1">
                            <i class="ki-outline ki-arrow-{{ $card['growth']['direction'] === 'up' ? 'up' : 'down' }} fs-7 text-{{ $card['growth']['color'] }}"></i>
                            <span class="text-{{ $card['growth']['color'] }} fw-bold fs-8">{{ $card['growth']['percentage'] }}%</span>
                        </div>
                        @elseif(isset($card['sub']))
                        <span class="text-muted fs-8 d-block">{{ $card['sub'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

{{-- ── CHARTS ───────────────────────────────────────────────── --}}
@if(isset($chartData))
<div class="mb-7">
    <div class="d-flex align-items-center mb-4">
        <div class="symbol symbol-35px me-3">
            <div class="symbol-label bg-light-info">
                <i class="ki-outline ki-chart-simple-2 fs-3 text-info"></i>
            </div>
        </div>
        <h3 class="fs-4 fw-bold text-gray-900 mb-0">Visual Analytics</h3>
    </div>
    <div class="row g-5 mb-5">
        <div class="col-xl-4">
            <x-charts.donut-chart
                chart-id="poStatusChart"
                title="PO by Status"
                subtitle="Distribusi status purchase order"
                :labels="$chartData['poStatus']['labels']"
                :data="$chartData['poStatus']['data']"
                :colors="$chartData['poStatus']['colors']"
                height="300" />
        </div>
        <div class="col-xl-8">
            <x-charts.line-chart
                chart-id="monthlyPOTrendChart"
                title="Trend PO Bulanan"
                subtitle="6 bulan terakhir"
                :labels="$chartData['monthlyPOTrend']['labels']"
                :datasets="$chartData['monthlyPOTrend']['datasets']"
                height="300" />
        </div>
    </div>
    <div class="row g-5">
        <div class="col-xl-6">
            <x-charts.bar-chart
                chart-id="monthlyRevenueChart"
                title="Revenue Bulanan"
                subtitle="Total nilai PO per bulan"
                :labels="$chartData['monthlyRevenue']['labels']"
                :datasets="$chartData['monthlyRevenue']['datasets']"
                height="300" />
        </div>
        <div class="col-xl-6">
            <x-charts.line-chart
                chart-id="cashFlowChart"
                title="Cash Flow Trend"
                subtitle="Kas masuk & keluar 30 hari terakhir"
                :labels="$chartData['cashFlowTrend']['labels']"
                :datasets="$chartData['cashFlowTrend']['datasets']"
                height="300" />
        </div>
    </div>
</div>
@endif

{{-- ── ROW: ACTIVITY LOG + QUICK ACTIONS ──────────────────── --}}
<div class="row g-5 mb-6">
    {{-- Activity Log --}}
    <div class="col-xl-8">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title flex-column">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-1">Aktivitas Sistem Terbaru</h3>
                    <span class="text-muted fs-7">Log aktivitas pengguna & sistem</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('web.dashboard.audit') }}" class="btn btn-sm btn-light-primary">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-4 pb-2">
                <div class="table-responsive">
                    <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                        <thead>
                            <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                                <th class="ps-4 rounded-start">User</th>
                                <th>Aktivitas</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-end pe-4 rounded-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $log)
                            @php
                                $ac = match($log->action) {
                                    'create' => 'success',
                                    'update' => 'info',
                                    'delete' => 'danger',
                                    'error', 'failed' => 'danger',
                                    default => 'primary',
                                };
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-7">{{ $log->user?->name ?? 'System' }}</span>
                                    <div class="text-muted fs-8">{{ $log->user?->email ?? '—' }}</div>
                                </td>
                                <td class="text-gray-700 fs-7">{{ $log->description }}</td>
                                <td class="text-center">
                                    <span class="badge badge-light-{{ $ac }} fs-8">{{ strtoupper($log->action) }}</span>
                                </td>
                                <td class="text-end pe-4 text-muted fs-8">{{ $log->occurred_at->format('d M H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-8">
                                    <span class="text-muted fs-6">Belum ada aktivitas</span>
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
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-0">Aksi Cepat</h3>
                </div>
            </div>
            <div class="card-body pt-4">
                <div class="d-flex flex-column gap-3">
                    @can('manage_users')
                    <a href="{{ route('web.users.index') }}" class="d-flex align-items-center p-3 rounded bg-light-primary border border-dashed border-primary">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-primary">
                                <i class="ki-outline ki-profile-user fs-3 text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Manage Users</div>
                            <div class="text-muted fs-8">Kelola pengguna sistem</div>
                        </div>
                    </a>
                    @endcan
                    @can('manage_organizations')
                    <a href="{{ route('web.organizations.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-info">
                                <i class="ki-outline ki-bank fs-3 text-info"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Manage Organizations</div>
                            <div class="text-muted fs-8">Kelola RS/Klinik</div>
                        </div>
                    </a>
                    @endcan
                    @can('manage_products')
                    <a href="{{ route('web.products.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-success">
                                <i class="ki-outline ki-capsule fs-3 text-success"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Manage Products</div>
                            <div class="text-muted fs-8">Master data produk</div>
                        </div>
                    </a>
                    @endcan
                    @can('manage_suppliers')
                    <a href="{{ route('web.suppliers.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-warning">
                                <i class="ki-outline ki-cube-2 fs-3 text-warning"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Manage Suppliers</div>
                            <div class="text-muted fs-8">Data distributor</div>
                        </div>
                    </a>
                    @endcan
                    <a href="{{ route('web.dashboard.audit') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-dark">
                                <i class="ki-outline ki-file fs-3 text-dark"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Audit Logs</div>
                            <div class="text-muted fs-8">Log sistem lengkap</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── ANALYTICS SECTION ───────────────────────────────────── --}}
@if(isset($analytics))
<div class="mb-7">
    <div class="d-flex align-items-center mb-4">
        <div class="symbol symbol-35px me-3">
            <div class="symbol-label bg-light-primary">
                <i class="ki-outline ki-chart-simple fs-3 text-primary"></i>
            </div>
        </div>
        <h3 class="fs-4 fw-bold text-gray-900 mb-0">Analytics & Insights</h3>
    </div>

    {{-- Summary mini cards --}}
    <div class="row g-5 g-xl-8 mb-5">
        <div class="col-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-outline ki-capsule fs-2x text-success"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">Total Unit Terbeli</span>
                        <span class="text-gray-900 fw-bold fs-2">{{ number_format($analytics['purchaseSummary']['total_quantity'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-outline ki-wallet fs-2x text-primary"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">Nilai Pembelian</span>
                        <span class="text-gray-900 fw-bold fs-2">Rp {{ number_format($analytics['purchaseSummary']['total_value'] / 1000000, 0) }}jt</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-info">
                            <i class="ki-outline ki-calendar fs-2x text-info"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">Unit Bulan Ini</span>
                        <span class="text-gray-900 fw-bold fs-2">{{ number_format($analytics['purchaseSummary']['month_quantity'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-warning">
                            <i class="ki-outline ki-chart-line-up fs-2x text-warning"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">Avg Order Value</span>
                        <span class="text-gray-900 fw-bold fs-2">Rp {{ number_format($analytics['purchaseSummary']['avg_order_value'] / 1000000, 1) }}jt</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Products & Suppliers --}}
    <div class="row g-5">
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header border-0 pt-5 pb-0">
                    <div class="card-title flex-column">
                        <h3 class="fw-bold text-gray-900 fs-4 mb-1">Top 10 Produk Terlaris</h3>
                        <span class="text-muted fs-7">Berdasarkan volume pembelian</span>
                    </div>
                </div>
                <div class="card-body pt-4 pb-2">
                    <div class="table-responsive">
                        <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                            <thead>
                                <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                                    <th class="ps-4 rounded-start">#</th>
                                    <th>Produk</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end pe-4 rounded-end">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['topProducts'] as $i => $product)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge badge-light-primary fs-8">{{ $i + 1 }}</span>
                                    </td>
                                    <td>
                                        <span class="text-gray-900 fw-bold fs-7">{{ $product->name }}</span>
                                        <div class="text-muted fs-8">{{ $product->sku }}</div>
                                        @if($product->is_narcotic)
                                        <span class="badge badge-danger fs-9">NARKOTIKA</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold fs-7">{{ number_format($product->total_quantity, 0, ',', '.') }}</td>
                                    <td class="text-end pe-4 text-gray-700 fs-7">Rp {{ number_format($product->total_value / 1000000, 1) }}jt</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-6 text-muted">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header border-0 pt-5 pb-0">
                    <div class="card-title flex-column">
                        <h3 class="fw-bold text-gray-900 fs-4 mb-1">Top 10 Supplier</h3>
                        <span class="text-muted fs-7">Berdasarkan jumlah order</span>
                    </div>
                </div>
                <div class="card-body pt-4 pb-2">
                    <div class="table-responsive">
                        <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                            <thead>
                                <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                                    <th class="ps-4 rounded-start">#</th>
                                    <th>Supplier</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end pe-4 rounded-end">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['topSuppliers'] as $i => $supplier)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge badge-light-primary fs-8">{{ $i + 1 }}</span>
                                    </td>
                                    <td>
                                        <span class="text-gray-900 fw-bold fs-7">{{ $supplier->name }}</span>
                                        <div class="text-muted fs-8">{{ $supplier->phone }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-info fs-8">{{ $supplier->order_count }} PO</span>
                                    </td>
                                    <td class="text-end pe-4 fw-bold fs-7 text-gray-900">
                                        Rp {{ number_format($supplier->total_value / 1000000, 1) }}jt
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-6 text-muted">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── SYSTEM ERRORS (jika ada) ────────────────────────────── --}}
@if(count($auditLogs) > 0)
<div class="card shadow-sm border border-danger border-dashed">
    <div class="card-header border-0 pt-5 pb-0 bg-light-danger">
        <div class="card-title flex-column">
            <h3 class="fw-bold text-danger fs-4 mb-1">System Errors & Failed Transactions</h3>
            <span class="text-danger fs-7">Memerlukan perhatian segera</span>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('web.dashboard.audit') }}" class="btn btn-sm btn-danger">Lihat Semua</a>
        </div>
    </div>
    <div class="card-body pt-4 pb-2">
        <div class="table-responsive">
            <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                <thead>
                    <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                        <th class="ps-4 rounded-start">User</th>
                        <th>Deskripsi Error</th>
                        <th class="text-end pe-4 rounded-end">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditLogs as $log)
                    <tr>
                        <td class="ps-4 text-gray-900 fw-bold fs-7">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="text-gray-700 fs-7">{{ $log->description }}</td>
                        <td class="text-end pe-4 text-muted fs-8">{{ $log->occurred_at->format('d M Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
