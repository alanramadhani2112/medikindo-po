<x-index-layout title="Master Harga Jual" description="Kelola harga jual per RS/Klinik dan produk" :breadcrumbs="[['label' => 'Price Lists']]">
    <x-slot name="actions">
        @can('manage_products')
            <x-button :href="route('web.price-lists.create')" icon="plus" label="Tambah Harga" />
        @endcan
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.price-lists.index')">
            <div style="min-width: 200px;">
                <select name="organization_id" class="form-select form-select-solid">
                    <option value="">Semua Organisasi</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 200px;">
                <select name="product_id" class="form-select form-select-solid">
                    <option value="">Semua Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 120px;">
                <select name="is_active" class="form-select form-select-solid">
                    <option value="">Semua</option>
                    <option value="1" @selected(request('is_active') === '1')>Aktif</option>
                    <option value="0" @selected(request('is_active') === '0')>Nonaktif</option>
                </select>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tableHeader">Daftar Harga Jual Khusus</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>RS/Klinik</th>
                <th>Produk</th>
                <th class="text-end">Harga Jual</th>
                <th>Berlaku Dari</th>
                <th>Berlaku Sampai</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($priceLists as $pl)
                <tr>
                    <td>
                        <span class="fw-bold text-gray-800">{{ $pl->organization?->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="fw-bold text-gray-800">{{ $pl->product?->name ?? '—' }}</span>
                        @if($pl->product?->sku)
                            <div class="text-muted fs-8">{{ $pl->product->sku }}</div>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900">Rp {{ number_format($pl->selling_price, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        <span class="text-gray-700 fs-7">{{ $pl->effective_date?->format('d M Y') ?? '—' }}</span>
                    </td>
                    <td>
                        @if($pl->expiry_date)
                            <span class="{{ $pl->expiry_date->isPast() ? 'text-danger' : 'text-gray-700' }} fs-7">
                                {{ $pl->expiry_date->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-muted fs-7">Tidak ada</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($pl->is_active)
                            <span class="badge badge-light-success fw-bold">Aktif</span>
                        @else
                            <span class="badge badge-light-secondary fw-bold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('web.price-lists.edit', $pl) }}" class="btn btn-icon btn-light-warning btn-sm" title="Edit">
                            <i class="ki-outline ki-pencil fs-2"></i>
                        </a>
                        @if($pl->is_active)
                            <form method="POST" action="{{ route('web.price-lists.destroy', $pl) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Nonaktifkan">
                                    <i class="ki-outline ki-cross-circle fs-2"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-10">
                        <x-empty-state icon="price-tag" title="Tidak Ada Data" message="Belum ada harga jual khusus terdaftar untuk filter ini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($priceLists->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $priceLists->links() }}
        </div>
    @endif
</x-index-layout>
