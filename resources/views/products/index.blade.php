<x-index-layout title="Manajemen Produk" description="Kelola katalog produk dan obat-obatan" :breadcrumbs="[['label' => 'Products']]">
    <x-slot name="actions">
        @can('manage_products')
            <x-button :href="route('web.products.create')" icon="plus" label="Tambah Produk" />
        @endcan
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.products.index')">
            <input type="hidden" name="type" value="{{ request('type') }}">
            
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control form-control-solid ps-12" 
                           placeholder="Cari nama atau SKU...">
                </div>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
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
                    <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                        {{ $counts[$val] }}
                    </span>
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Daftar Produk</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Produk</th>
                <th>Kategori</th>
                <th>Klasifikasi</th>
                <th>Kadaluarsa</th>
                <th class="text-end">Harga Beli</th>
                <th class="text-end">Harga Jual</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label fs-7 fw-bold bg-light text-gray-400">IMG</div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-6">{{ $product->name }}</span>
                                <span class="text-gray-500 fs-7">{{ $product->sku ?? 'NO-SKU' }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light-primary">{{ strtoupper($product->category ?? 'General') }}</span>
                    </td>
                    <td>
                        @if($product->is_narcotic)
                            <span class="badge badge-danger fs-7 fw-bold">NARKOTIKA</span>
                        @else
                            <span class="badge badge-light-success fs-7 fw-semibold">NON-NARKOTIKA</span>
                        @endif
                    </td>
                    <td>
                        @if($product->expiry_date)
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-semibold fs-7">{{ $product->expiry_date->format('d M Y') }}</span>
                                @if($product->expiry_status !== 'none')
                                    <span class="badge badge-light-{{ $product->expiry_status_color }} fs-8 mt-1">{{ abs($product->days_until_expiry) }} hari</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400 fs-7">-</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="text-gray-600 fs-7">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-end">
                        <span class="text-gray-800 fw-bold fs-7">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        @if($product->is_active)
                            <span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
                        @else
                            <span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('web.products.edit', $product) }}" class="btn btn-icon btn-light-warning btn-sm" title="Edit Produk">
                            <i class="ki-outline ki-pencil fs-2"></i>
                        </a>
                        <form method="POST" action="{{ route('web.products.destroy', $product) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-icon btn-light-danger btn-sm delete-confirm" data-name="{{ $product->name }}" title="Hapus Produk">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-10">
                        <x-empty-state icon="file-deleted" title="Tidak Ada Data" message="Belum ada produk terdaftar untuk filter ini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($products->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $products->links() }}
        </div>
    @endif
</x-index-layout>
