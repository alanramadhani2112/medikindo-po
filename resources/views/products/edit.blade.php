@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-7">
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Ubah Produk</h1>
        <p class="text-gray-600 fs-6 mb-0">Form ubah data produk</p>
    </div>

    {{-- Form Card --}}
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Ubah Data Produk
                    </h3>
                </div>
                <div class="card-body">
                    {{-- Error Alert --}}
                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-start mb-5">
                            <i class="ki-outline ki-information-5 fs-2 me-3"></i>
                            <div>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('web.products.update', $product) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Supplier --}}
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold required">Supplier</label>
                                <select name="supplier_id" required class="form-select form-select-solid @error('supplier_id') is-invalid @enderror">
                                    <option value="">— Pilih Supplier —</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" {{ (old('supplier_id', $product->supplier_id) == $s->id) ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                {{-- Nama Produk --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Nama Produk</label>
                                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                                           placeholder="Contoh: Amoxicillin 500mg"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Satuan --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Satuan</label>
                                    <select name="unit" required class="form-select form-select-solid @error('unit') is-invalid @enderror">
                                        <option value="">— Pilih Satuan —</option>
                                        @foreach($units as $u)
                                            <option value="{{ $u }}" {{ old('unit', $product->unit) == $u ? 'selected' : '' }}>{{ $u }}</option>
                                        @endforeach
                                    </select>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- SKU --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">SKU / Kode Produk</label>
                                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required maxlength="50"
                                           placeholder="AMX-001"
                                           class="form-control form-control-solid @error('sku') is-invalid @enderror">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- PROFIT CALCULATION SECTION --}}
                            <div class="col-12">
                                <div class="separator separator-dashed my-7"></div>
                                <h3 class="fs-5 fw-bold text-gray-900 mb-5">
                                    <i class="ki-outline ki-delivery fs-3 text-success me-2"></i>
                                    Perhitungan Harga & Profit
                                </h3>
                            </div>

                            <div class="col-md-6">
                                {{-- Harga Beli (Cost Price) --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Harga Beli (Cost Price)</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price', (int)$product->cost_price) }}" 
                                               required min="0" step="1" placeholder="0"
                                               class="form-control @error('cost_price') is-invalid @enderror">
                                    </div>
                                    <div class="form-text text-muted">Harga beli dari supplier</div>
                                    @error('cost_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Diskon Persentase --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Diskon (%)</label>
                                    <div class="input-group input-group-solid">
                                        <input type="number" name="discount_percentage" id="discount_percentage" 
                                               value="{{ old('discount_percentage', $product->discount_percentage) }}" 
                                               min="0" max="100" step="0.01" placeholder="0"
                                               class="form-control @error('discount_percentage') is-invalid @enderror">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text text-muted">Persentase diskon yang dapat diberikan</div>
                                    @error('discount_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- Harga Jual (Selling Price) --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Harga Jual (Selling Price)</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price', (int)$product->selling_price) }}" 
                                               required min="0" step="1" placeholder="0"
                                               class="form-control @error('selling_price') is-invalid @enderror">
                                    </div>
                                    <div class="form-text text-muted">Harga jual ke customer</div>
                                    @error('selling_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Diskon Nominal (Auto-calculated) --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Diskon Nominal</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="discount_amount" id="discount_amount" 
                                               value="{{ old('discount_amount', (int)$product->discount_amount) }}" 
                                               readonly
                                               class="form-control bg-light">
                                    </div>
                                    <div class="form-text text-muted">Otomatis dihitung dari persentase diskon</div>
                                </div>
                            </div>

                            {{-- Profit Preview Card --}}
                            <div class="col-12 mb-5">
                                <div class="card bg-light-success border border-success border-dashed">
                                    <div class="card-body p-5">
                                        <div class="row g-5">
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">LABA KOTOR</span>
                                                    <span class="text-success fs-2 fw-bold" id="gross_profit_display">Rp {{ number_format($product->gross_profit, 0, ',', '.') }}</span>
                                                    <span class="text-gray-600 fs-8" id="gross_margin_display">Margin: {{ number_format($product->gross_profit_margin, 2) }}%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">HARGA SETELAH DISKON</span>
                                                    <span class="text-primary fs-2 fw-bold" id="final_price_display">Rp {{ number_format($product->final_price, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">LABA BERSIH</span>
                                                    <span class="text-success fs-2 fw-bold" id="net_profit_display">Rp {{ number_format($product->net_profit, 0, ',', '.') }}</span>
                                                    <span class="text-gray-600 fs-8" id="net_margin_display">Margin: {{ number_format($product->net_profit_margin, 2) }}%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">STATUS PROFIT</span>
                                                    <span class="badge badge-{{ $product->profit_status_color }} fs-6 fw-bold" id="profit_status_badge">
                                                        @if($product->net_profit_margin >= 20)
                                                            PROFIT TINGGI
                                                        @elseif($product->net_profit_margin >= 10)
                                                            PROFIT BAIK
                                                        @elseif($product->net_profit_margin >= 5)
                                                            PROFIT RENDAH
                                                        @elseif($product->net_profit_margin > 0)
                                                            PROFIT MINIMAL
                                                        @else
                                                            RUGI / NO PROFIT
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="separator separator-dashed my-7"></div>
                            </div>

                            {{-- Kategori --}}
                            @include('products.partials.category-classification')

                            <div class="col-12">
                                <div class="separator separator-dashed my-7"></div>
                            </div>

                            {{-- Deskripsi --}}
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold">Deskripsi Produk</label>
                                <textarea name="description" rows="2" placeholder="Keterangan tambahan produk..."
                                          class="form-control form-control-solid @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Narkotika Checkbox --}}
                            <div class="col-12 mb-5">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" name="is_narcotic" value="1" id="is_narcotic" 
                                           {{ old('is_narcotic', $product->is_narcotic) ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label class="form-check-label" for="is_narcotic">
                                        <span class="fw-bold text-primary">Produk Narkotika / Psikotropika</span>
                                        <span class="d-block text-gray-600 fs-7 mt-1">Centang jika produk ini memerlukan persetujuan approval 2 level khusus.</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Conditional: Narcotic Group (shown only if is_narcotic = true) --}}
                            <div class="col-12 mb-5" id="narcotic_group_field" style="display: none;">
                                <label class="form-label fs-6 fw-semibold required">Golongan Narkotika</label>
                                <select name="narcotic_group" class="form-select form-select-solid @error('narcotic_group') is-invalid @enderror" id="narcotic_group_select">
                                    <option value="">Pilih Golongan</option>
                                    <option value="I" {{ old('narcotic_group', $product->narcotic_group) === 'I' ? 'selected' : '' }}>Golongan I</option>
                                    <option value="II" {{ old('narcotic_group', $product->narcotic_group) === 'II' ? 'selected' : '' }}>Golongan II</option>
                                    <option value="III" {{ old('narcotic_group', $product->narcotic_group) === 'III' ? 'selected' : '' }}>Golongan III</option>
                                </select>
                                <div class="form-text">
                                    <strong>Golongan I:</strong> Paling berbahaya, hanya untuk penelitian<br>
                                    <strong>Golongan II:</strong> Dapat digunakan untuk terapi dengan pengawasan ketat<br>
                                    <strong>Golongan III:</strong> Dapat digunakan untuk terapi dengan pengawasan
                                </div>
                                @error('narcotic_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end gap-3 mt-7 pt-7 border-top">
                            <a href="{{ route('web.products.index') }}" class="btn btn-light">
                                <i class="ki-outline ki-arrow-zigzag fs-2"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary update-confirm" data-name="{{ $product->name }}">
                                <i class="ki-outline ki-check fs-2"></i>
                                Perbarui Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/master-data-forms.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const costPrice = document.getElementById('cost_price');
    const sellingPrice = document.getElementById('selling_price');
    const discountPercentage = document.getElementById('discount_percentage');
    const discountAmount = document.getElementById('discount_amount');
    
    const grossProfitDisplay = document.getElementById('gross_profit_display');
    const grossMarginDisplay = document.getElementById('gross_margin_display');
    const finalPriceDisplay = document.getElementById('final_price_display');
    const netProfitDisplay = document.getElementById('net_profit_display');
    const netMarginDisplay = document.getElementById('net_margin_display');
    const profitStatusBadge = document.getElementById('profit_status_badge');

    function formatRupiah(number) {
        return 'Rp ' + Math.round(number).toLocaleString('id-ID');
    }

    function calculateProfit() {
        const cost = parseFloat(costPrice.value) || 0;
        const selling = parseFloat(sellingPrice.value) || 0;
        const discountPct = parseFloat(discountPercentage.value) || 0;

        // Calculate discount amount
        const discount = (selling * discountPct) / 100;
        discountAmount.value = Math.round(discount);

        // Calculate gross profit
        const grossProfit = selling - cost;
        const grossMargin = selling > 0 ? (grossProfit / selling) * 100 : 0;

        // Calculate final price after discount
        const finalPrice = selling - discount;

        // Calculate net profit
        const netProfit = finalPrice - cost;
        const netMargin = finalPrice > 0 ? (netProfit / finalPrice) * 100 : 0;

        // Update displays
        grossProfitDisplay.textContent = formatRupiah(grossProfit);
        grossMarginDisplay.textContent = 'Margin: ' + grossMargin.toFixed(2) + '%';
        finalPriceDisplay.textContent = formatRupiah(finalPrice);
        netProfitDisplay.textContent = formatRupiah(netProfit);
        netMarginDisplay.textContent = 'Margin: ' + netMargin.toFixed(2) + '%';

        // Update profit status badge
        let statusText = '';
        let statusColor = '';
        
        if (netMargin >= 20) {
            statusText = 'PROFIT TINGGI';
            statusColor = 'success';
        } else if (netMargin >= 10) {
            statusText = 'PROFIT BAIK';
            statusColor = 'primary';
        } else if (netMargin >= 5) {
            statusText = 'PROFIT RENDAH';
            statusColor = 'warning';
        } else if (netMargin > 0) {
            statusText = 'PROFIT MINIMAL';
            statusColor = 'info';
        } else {
            statusText = 'RUGI / NO PROFIT';
            statusColor = 'danger';
        }

        profitStatusBadge.textContent = statusText;
        profitStatusBadge.className = 'badge badge-' + statusColor + ' fs-6 fw-bold';
    }

    // Attach event listeners
    costPrice.addEventListener('input', calculateProfit);
    sellingPrice.addEventListener('input', calculateProfit);
    discountPercentage.addEventListener('input', calculateProfit);

    // Initial calculation
    calculateProfit();
});
</script>
@endpush
