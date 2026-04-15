@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">
                <i class="ki-outline ki-price-tag fs-2 text-primary me-2"></i>
                Master Harga Jual
            </h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola harga jual per RS/Klinik dan produk</p>
        </div>
        @can('manage_products')
            <a href="{{ route('web.price-lists.create') }}" class="btn btn-primary">
                <i class="ki-outline ki-plus fs-3"></i>
                Tambah Harga
            </a>
        @endcan
    </div>

    {{-- Filter Bar --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.price-lists.index') }}" method="GET" class="d-flex flex-wrap gap-3 align-items-end">
                <div>
                    <label class="form-label fs-7 fw-semibold text-gray-600">RS/Klinik</label>
                    <select name="organization_id" class="form-select form-select-solid" style="min-width: 200px;">
                        <option value="">Semua Organisasi</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label fs-7 fw-semibold text-gray-600">Produk</label>
                    <select name="product_id" class="form-select form-select-solid" style="min-width: 200px;">
                        <option value="">Semua Produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label fs-7 fw-semibold text-gray-600">Status</label>
                    <select name="is_active" class="form-select form-select-solid">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('is_active') === '1')>Aktif</option>
                        <option value="0" @selected(request('is_active') === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-magnifier fs-3"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['organization_id', 'product_id', 'is_active']))
                        <a href="{{ route('web.price-lists.index') }}" class="btn btn-light">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-5 min-w-180px rounded-start">RS/Klinik</th>
                            <th class="min-w-200px">Produk</th>
                            <th class="text-end min-w-130px">Harga Jual</th>
                            <th class="min-w-110px">Berlaku Dari</th>
                            <th class="min-w-110px">Berlaku Sampai</th>
                            <th class="text-center min-w-90px">Status</th>
                            <th class="text-end pe-5 min-w-100px rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($priceLists as $pl)
                            <tr>
                                <td class="ps-5">
                                    <span class="fw-semibold text-gray-800">{{ $pl->organization?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-gray-800">{{ $pl->product?->name ?? '—' }}</span>
                                    @if($pl->product?->sku)
                                        <div class="text-muted fs-8">{{ $pl->product->sku }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-gray-900">Rp {{ number_format($pl->selling_price, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-700">{{ $pl->effective_date?->format('d M Y') ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($pl->expiry_date)
                                        <span class="{{ $pl->expiry_date->isPast() ? 'text-danger' : 'text-gray-700' }}">
                                            {{ $pl->expiry_date->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($pl->is_active)
                                        <span class="badge badge-light-success">Aktif</span>
                                    @else
                                        <span class="badge badge-light-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end pe-5">
                                    @can('manage_products')
                                        <div class="action-menu-wrapper">
                                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary" data-action-menu>
                                                <i class="ki-outline ki-dots-vertical fs-3"></i>
                                                Aksi
                                            </button>
                                            <div class="action-dropdown-menu" style="display: none;">
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('web.price-lists.edit', $pl) }}" class="menu-link px-3">
                                                        <i class="ki-outline ki-pencil fs-4 me-2 text-warning"></i>
                                                        Edit
                                                    </a>
                                                </div>
                                                @if($pl->is_active)
                                                    <div class="separator my-2"></div>
                                                    <div class="menu-item px-3">
                                                        <form method="POST" action="{{ route('web.price-lists.destroy', $pl) }}" class="d-inline w-100">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="menu-link px-3 w-100 text-start text-danger"
                                                                    style="background: none; border: none;">
                                                                <i class="ki-outline ki-cross-circle fs-4 me-2"></i>
                                                                Nonaktifkan
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-price-tag fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Harga Jual</span>
                                        <span class="text-gray-500 fs-6">Tambahkan harga jual untuk RS/Klinik dan produk.</span>
                                        @can('manage_products')
                                            <a href="{{ route('web.price-lists.create') }}" class="btn btn-primary mt-4">
                                                <i class="ki-outline ki-plus fs-3"></i>
                                                Tambah Harga
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($priceLists->hasPages())
                <div class="d-flex justify-content-center py-5">
                    {{ $priceLists->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
