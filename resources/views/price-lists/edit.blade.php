@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">
                <i class="ki-outline ki-price-tag fs-2 text-warning me-2"></i>
                Edit Harga Jual
            </h1>
            <p class="text-gray-600 fs-6 mb-0">
                {{ $priceList->organization?->name ?? '—' }} — {{ $priceList->product?->name ?? '—' }}
            </p>
        </div>
        <a href="{{ route('web.price-lists.index') }}" class="btn btn-light">
            <i class="ki-outline ki-arrow-left fs-3"></i>
            Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title fw-bold">Form Edit Harga Jual</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.price-lists.update', $priceList) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <label class="form-label fw-semibold required">RS/Klinik</label>
                            <select name="organization_id" class="form-select form-select-solid @error('organization_id') is-invalid @enderror" required>
                                <option value="">-- Pilih RS/Klinik --</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" @selected(old('organization_id', $priceList->organization_id) == $org->id)>
                                        {{ $org->name }}
                                        @if($org->customer_code) ({{ $org->customer_code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('organization_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-semibold required">Produk</label>
                            <select name="product_id" class="form-select form-select-solid @error('product_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id', $priceList->product_id) == $product->id)>
                                        {{ $product->name }}
                                        @if($product->sku) [{{ $product->sku }}] @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-semibold required">Harga Jual (Rp)</label>
                            <input type="number" name="selling_price"
                                   value="{{ old('selling_price', $priceList->selling_price) }}"
                                   class="form-control form-control-solid @error('selling_price') is-invalid @enderror"
                                   placeholder="0" min="0" step="0.01" required>
                            @error('selling_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold required">Berlaku Dari</label>
                                <input type="date" name="effective_date"
                                       value="{{ old('effective_date', $priceList->effective_date?->toDateString()) }}"
                                       class="form-control form-control-solid @error('effective_date') is-invalid @enderror" required>
                                @error('effective_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Berlaku Sampai <span class="text-muted fs-8">(opsional)</span></label>
                                <input type="date" name="expiry_date"
                                       value="{{ old('expiry_date', $priceList->expiry_date?->toDateString()) }}"
                                       class="form-control form-control-solid @error('expiry_date') is-invalid @enderror">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-7">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       value="1" @checked(old('is_active', $priceList->is_active))>
                                <label class="form-check-label fw-semibold" for="is_active">Aktif</label>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-check fs-3"></i>
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('web.price-lists.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
