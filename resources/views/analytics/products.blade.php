@extends('layouts.app')

@section('content')
{{-- Breadcrumbs --}}
@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
<div class="mb-5">
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
        @foreach($breadcrumbs as $index => $breadcrumb)
            @if($index < count($breadcrumbs) - 1)
                <li class="breadcrumb-item text-muted">
                    <a href="{{ $breadcrumb['url'] ?? '#' }}" class="text-muted text-hover-primary">{{ $breadcrumb['label'] }}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
            @else
                <li class="breadcrumb-item text-dark">{{ $breadcrumb['label'] }}</li>
            @endif
        @endforeach
    </ul>
</div>
@endif

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Product Sales Analytics</h1>
        <p class="text-gray-600 fs-6 mb-0">Analisis penjualan produk dengan trend mingguan, bulanan, dan tahunan</p>
    </div>
    <a href="{{ route('web.products.index') }}" class="btn btn-primary">
        <i class="ki-outline ki-paintbucket fs-2"></i>
        Kelola Produk
    </a>
</div>

{{-- Period Selector --}}
<x-period-selector :current-period="$period ?? 'month'" />

{{-- Summary Cards --}}
<div class="row g-5 g-xl-8 mb-7">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-flush h-100 bg-primary">
            <div class="card-body p-6">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white fw-bold fs-7">TOTAL PRODUK AKTIF</span>
                    <i class="ki-outline ki-paintbucket fs-2x text-white"></i>
                </div>
                <div class="fw-bold fs-2x text-white mb-2">{{ \App\Models\Product::where('is_active', true)->count() }}</div>
                <div class="text-white opacity-75 fs-7">Produk dalam katalog</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-flush h-100 bg-success">
            <div class="card-body p-6">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white fw-bold fs-7">TOP SELLER</span>
                    <i class="ki-outline ki-arrow-up fs-2x text-white"></i>
                </div>
                <div class="fw-bold fs-2x text-white mb-2">{{ $topProducts->first()->name ?? '-' }}</div>
                <div class="text-white opacity-75 fs-7">{{ number_format($topProducts->first()->total_quantity ?? 0) }} unit terjual</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-flush h-100 bg-info">
            <div class="card-body p-6">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white fw-bold fs-7">TOTAL PENJUALAN</span>
                    <i class="ki-outline ki-entrance-right fs-2x text-white"></i>
                </div>
                <div class="fw-bold fs-2x text-white mb-2">Rp {{ number_format($topProducts->sum('total_value'), 0, ',', '.') }}</div>
                <div class="text-white opacity-75 fs-7">Periode saat ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-flush h-100 bg-warning">
            <div class="card-body p-6">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-white fw-bold fs-7">KATEGORI PRODUK</span>
                    <i class="ki-outline ki-category fs-2x text-white"></i>
                </div>
                <div class="fw-bold fs-2x text-white mb-2">{{ count($salesByCategory['labels']) }}</div>
                <div class="text-white opacity-75 fs-7">Kategori aktif</div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="mb-7">
    {{-- Section Header --}}
    <div class="d-flex align-items-center mb-5">
        <div class="symbol symbol-40px me-3">
            <div class="symbol-label bg-light-primary">
                <i class="ki-outline ki-delivery fs-2 text-primary"></i>
            </div>
        </div>
        <div>
            <h3 class="fs-2 fw-bold text-gray-900 mb-0">Sales Trend Analysis</h3>
            <span class="text-muted fs-7">Trend penjualan produk dari waktu ke waktu</span>
        </div>
    </div>

    {{-- Weekly Trend --}}
    <div class="row g-5 g-xl-8 mb-7">
        <div class="col-12">
            <x-charts.line-chart 
                chart-id="weeklySalesChart"
                title="Weekly Sales Trend"
                subtitle="Penjualan 4 minggu terakhir"
                :labels="$weeklySales['labels']"
                :datasets="$weeklySales['datasets']"
                height="300"
            />
        </div>
    </div>

    {{-- Monthly Trend --}}
    <div class="row g-5 g-xl-8 mb-7">
        <div class="col-12">
            <x-charts.line-chart 
                chart-id="monthlySalesChart"
                title="Monthly Sales Trend"
                subtitle="Penjualan 12 bulan terakhir (Unit & Nilai)"
                :labels="$monthlySales['labels']"
                :datasets="$monthlySales['datasets']"
                height="350"
            />
        </div>
    </div>

    {{-- Yearly Trend & Sales by Category --}}
    <div class="row g-5 g-xl-8 mb-7">
        <div class="col-xl-6">
            <x-charts.bar-chart 
                chart-id="yearlySalesChart"
                title="Yearly Sales Trend"
                subtitle="Total penjualan 3 tahun terakhir"
                :labels="$yearlySales['labels']"
                :datasets="$yearlySales['datasets']"
                height="350"
            />
        </div>
        <div class="col-xl-6">
            <x-charts.donut-chart 
                chart-id="salesByCategoryChart"
                title="Sales by Category"
                subtitle="Distribusi penjualan per kategori"
                :labels="$salesByCategory['labels']"
                :data="$salesByCategory['data']"
                :colors="$salesByCategory['colors']"
                height="350"
            />
        </div>
    </div>
</div>

{{-- Top Products Table --}}
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">
                        <i class="ki-outline ki-medal-star fs-3 text-warning me-2"></i>
                        Top 10 Best Selling Products
                    </span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Produk dengan penjualan tertinggi periode ini</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 rounded-start">#</th>
                                <th class="min-w-200px">Produk</th>
                                <th class="min-w-100px text-end">Qty Terjual</th>
                                <th class="min-w-120px text-end">Total Nilai</th>
                                <th class="text-end pe-4 rounded-end">Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $index => $product)
                            <tr>
                                <td class="ps-4">
                                    @if($index < 3)
                                        <span class="badge badge-light-warning fs-7 fw-bold">{{ $index + 1 }}</span>
                                    @else
                                        <span class="badge badge-light fs-7">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold fs-6">{{ $product->name }}</span>
                                        <span class="text-gray-500 fs-7">{{ $product->sku }} • {{ \App\Models\Product::CATEGORY_REGULATORY[$product->category_regulatory] ?? ($product->category_regulatory ?? '—') }}</span>
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
                                    <div class="text-gray-700 fs-6">Belum ada data penjualan</div>
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

@endsection
