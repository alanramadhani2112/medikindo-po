<x-index-layout title="Inventory Control" description="Monitoring stok produk, batch, dan tanggal kadaluarsa" :breadcrumbs="[['label' => 'Inventory']]">
    
    <x-slot name="top">
        <div class="row g-5 mb-7">
            <div class="col-md-3">
                <div class="card bg-primary">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">TOTAL PRODUK STOK</span>
                        <div class="text-white fs-2x fw-bold mt-2">{{ $stats['total_products'] }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">Item unik di gudang</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">TOTAL QUANTITY</span>
                        <div class="text-white fs-2x fw-bold mt-2">{{ number_format($stats['total_stock'], 0, ',', '.') }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">Total unit on-hand</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">LOW STOCK</span>
                        <div class="text-white fs-2x fw-bold mt-2">{{ $stats['low_stock_count'] }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">Item butuh pengadaan</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">EXPIRING SOON</span>
                        <div class="text-white fs-2x fw-bold mt-2">{{ $stats['expiring_soon_count'] }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">Kadaluarsa dalam 60 hari</div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.inventory.index')">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="Cari Produk atau SKU..." value="{{ $search }}">
                </div>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                '' => ['label' => 'Semua Stok', 'icon' => 'ki-home'],
                'low_stock' => ['label' => 'Stok Rendah', 'icon' => 'ki-chart-line-down'],
                'expiring' => ['label' => 'Mendekati Kadaluarsa', 'icon' => 'ki-timer'],
                'expired' => ['label' => 'Kadaluarsa', 'icon' => 'ki-cross-circle'],
            ];
            $currentStatus = request('status', '');
        @endphp
        @foreach($tabOptions as $val => $tabData)
            @php $isActive = (string)$currentStatus === (string)$val; @endphp
            <li class="nav-item">
                <a href="{{ route('web.inventory.index', array_merge(request()->except(['status', 'page']), ['status' => $val === '' ? null : $val])) }}" 
                   class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                    <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Daftar Stok Inventori</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Produk</th>
                <th>Batch No</th>
                <th>Kadaluarsa</th>
                <th class="text-end">On Hand</th>
                <th class="text-end">Reserved</th>
                <th class="text-end">Available</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventoryItems as $item)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                    {{ strtoupper(substr($item->product->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-gray-900 fs-6">{{ $item->product->name }}</span>
                                <span class="text-muted fs-7">{{ $item->product->sku }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light fw-bold text-gray-800">{{ $item->batch_no }}</span>
                    </td>
                    <td>
                        @if($item->expiry_date)
                            <div class="d-flex flex-column">
                                <span class="fw-semibold {{ $item->isExpired() ? 'text-danger' : ($item->isExpiringSoon() ? 'text-warning' : 'text-gray-800') }}">
                                    {{ $item->expiry_date->format('d M Y') }}
                                </span>
                                @if($item->isExpired())
                                    <span class="badge badge-light-danger fs-9 w-fit mt-1">EXPIRED</span>
                                @elseif($item->isExpiringSoon())
                                    <span class="badge badge-light-warning fs-9 w-fit mt-1">EXPIRING</span>
                                @endif
                            </div>
                        @else
                            <span class="text-muted fs-7">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900">{{ number_format($item->quantity_on_hand, 0) }}</span>
                    </td>
                    <td class="text-end">
                        <span class="text-gray-600">{{ number_format($item->quantity_reserved, 0) }}</span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold {{ $item->isLowStock() ? 'text-danger' : 'text-success' }} fs-6">
                            {{ number_format($item->quantity_available, 0) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <x-table-action>
                            <x-table-action.item :href="route('web.inventory.show', $item->product_id)" icon="eye" label="Lihat History" />
                            <x-table-action.item :href="route('web.inventory.adjust.form', $item)" icon="pencil" label="Adjust Stok" color="warning" />
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-10">
                        <x-empty-state icon="parcel" title="Stok Kosong" message="Tidak ada data inventori ditemukan untuk filter ini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($inventoryItems->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $inventoryItems->links() }}
        </div>
    @endif
</x-index-layout>
