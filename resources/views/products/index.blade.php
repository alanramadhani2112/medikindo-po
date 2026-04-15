@extends('layouts.app')

@section('content')
    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-outline ki-check-circle fs-2 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Page Header with Add Button --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Manajemen Produk</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola katalog produk dan obat-obatan</p>
        </div>
        @can('manage_products')
            <a href="{{ route('web.products.create') }}" class="btn btn-primary">
                <i class="ki-outline ki-picture fs-2"></i>
                Tambah Produk
            </a>
        @endcan
    </div>

    {{-- Filter Bar (STANDARD) --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.products.index') }}" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="type" value="{{ request('type') }}">
                
                {{-- LEFT: Search --}}
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-outline ki-chart
 fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nama atau SKU...">
                    </div>
                </div>
                
                {{-- Search Button --}}
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-outline ki-chart
 fs-2"></i>
                    Filter
                </button>
                
                {{-- Reset Button --}}
                @if(request()->filled('search'))
                    <a href="{{ route('web.products.index', ['type' => request('type')]) }}" class="btn btn-light">
                        <i class="ki-outline ki-arrow-zigzag fs-2"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Tabs (STANDARD) --}}
    <div class="card mb-5">
        <div class="card-header border-0 pt-6 pb-2">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
                @php
                    $tabOptions = [
                        '' => ['label' => 'Semua', 'icon' => 'ki-home'],
                        'non-narcotic' => ['label' => 'Non-Narkotika', 'icon' => 'ki-shield-tick'],
                        'narcotic' => ['label' => 'Narkotika', 'icon' => 'ki-shield-cross'],
                        'expiring' => ['label' => 'Akan Kadaluarsa', 'icon' => 'ki-timer'],
                        'expired' => ['label' => 'Kadaluarsa', 'icon' => 'ki-cross-circle'],
                    ];
                    $currentTab = request('type', '');
                    $counts = [
                        '' => \App\Models\Product::count(),
                        'non-narcotic' => \App\Models\Product::where('is_narcotic', false)->count(),
                        'narcotic' => \App\Models\Product::where('is_narcotic', true)->count(),
                        'expiring' => \App\Models\Product::expiringSoon(60)->count(),
                        'expired' => \App\Models\Product::expired()->count(),
                    ];
                @endphp
                @foreach($tabOptions as $val => $tabData)
                    @php
                        $isActive = (string)$currentTab === (string)$val;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('web.products.index', array_merge(request()->except(['type', 'page']), ['type' => $val === '' ? null : $val])) }}" 
                           class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                            <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                            <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                            <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-auto">
                                {{ $counts[$val] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body pt-6">
            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 min-w-250px rounded-start">Produk</th>
                            <th class="min-w-125px d-none d-md-table-cell">Kategori</th>
                            <th class="min-w-120px">Klasifikasi</th>
                            <th class="min-w-150px d-none d-lg-table-cell">Tanggal Kadaluarsa</th>
                            <th class="min-w-120px d-none d-xl-table-cell">Harga Beli</th>
                            <th class="min-w-120px d-none d-xl-table-cell">Harga Jual</th>
                            <th class="min-w-120px d-none d-lg-table-cell">Laba Bersih</th>
                            <th class="min-w-100px d-none d-sm-table-cell">Status</th>
                            <th class="text-end min-w-100px pe-4 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px">
                                            <div class="symbol-label fs-7 fw-bold bg-light text-gray-400">
                                                IMG
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold">{{ $product->name }}</span>
                                            <span class="text-gray-500 fs-7">{{ $product->sku ?? 'NO-SKU' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge badge-light-primary">
                                        {{ strtoupper($product->category ?? 'General') }}
                                    </span>
                                </td>
                                <td>
                                    @if($product->is_narcotic)
                                        <span class="badge badge-danger fs-7 fw-bold">
                                            <i class="ki-outline ki-shield-cross fs-6 me-1"></i>
                                            NARKOTIKA
                                        </span>
                                    @else
                                        <span class="badge badge-light-success fs-7 fw-semibold">
                                            <i class="ki-outline ki-shield-tick fs-6 me-1"></i>
                                            NON-NARKOTIKA
                                        </span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if($product->expiry_date)
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-semibold">
                                                {{ $product->expiry_date->format('d M Y') }}
                                            </span>
                                            @if($product->batch_no)
                                                <span class="text-gray-500 fs-8">Batch: {{ $product->batch_no }}</span>
                                            @endif
                                            @if($product->expiry_status !== 'none')
                                                <span class="badge badge-{{ $product->expiry_status_color }} fs-8 mt-1">
                                                    @if($product->expiry_status === 'expired')
                                                        <i class="ki-outline ki-arrow-zigzag-circle fs-7 me-1"></i>
                                                        KADALUARSA
                                                    @elseif($product->expiry_status === 'critical')
                                                        <i class="ki-outline ki-information fs-7 me-1"></i>
                                                        {{ abs($product->days_until_expiry) }} hari lagi
                                                    @elseif($product->expiry_status === 'warning')
                                                        <i class="ki-outline ki-calendar-search fs-7 me-1"></i>
                                                        {{ abs($product->days_until_expiry) }} hari lagi
                                                    @else
                                                        <i class="ki-outline ki-check-circle fs-7 me-1"></i>
                                                        {{ abs($product->days_until_expiry) }} hari lagi
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 fs-7">-</span>
                                    @endif
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <span class="text-gray-600 fs-7">
                                        Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <span class="text-gray-800 fw-semibold">
                                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                    </span>
                                    @if($product->discount_percentage > 0)
                                        <span class="badge badge-light-warning fs-8 ms-1">-{{ number_format($product->discount_percentage, 0) }}%</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div class="d-flex flex-column">
                                        <span class="text-{{ $product->net_profit >= 0 ? 'success' : 'danger' }} fw-bold">
                                            Rp {{ number_format($product->net_profit, 0, ',', '.') }}
                                        </span>
                                        <span class="text-gray-500 fs-8">
                                            Margin: {{ number_format($product->net_profit_margin, 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    @if($product->is_active)
                                        <span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
                                    @else
                                        <span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @can('manage_products')
                                        <div class="action-menu-wrapper">
                                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary" data-action-menu>
                                                <i class="ki-outline ki-dots-vertical fs-3"></i>
                                                Aksi
                                            </button>
                                            <div class="action-dropdown-menu" style="display: none;">
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('web.products.edit', $product) }}" class="menu-link px-3">
                                                        <i class="ki-outline ki-parcel fs-4 me-2 text-warning"></i>
                                                        Edit Produk
                                                    </a>
                                                </div>
                                                <div class="separator my-2"></div>
                                                <div class="menu-item px-3">
                                                    <form method="POST" action="{{ route('web.products.destroy', $product) }}" class="d-inline w-100">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="menu-link px-3 w-100 text-start text-danger delete-confirm" 
                                                                data-name="{{ $product->name }}"
                                                                style="background: none; border: none;">
                                                            <i class="ki-outline ki-trash fs-4 me-2"></i>
                                                            Hapus Produk
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum ada produk terdaftar</span>
                                        <span class="text-gray-500 fs-6">Mulai dengan menambahkan produk baru ke katalog.</span>
                                        @can('manage_products')
                                            <a href="{{ route('web.products.create') }}" class="btn btn-primary mt-5">
                                                <i class="ki-outline ki-picture fs-2"></i>
                                                Tambah Produk
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="pagination-wrapper">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
    </div>
@endsection
