@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-7">
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Tambah Produk</h1>
        <p class="text-gray-600 fs-6 mb-0">Form tambah produk baru</p>
    </div>

    {{-- Form Card --}}
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Tambah Produk Baru
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

                    <form method="POST" action="{{ url('/products') }}">
                        @csrf

                        <div class="row">
                            {{-- Supplier --}}
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold required">Supplier</label>
                                <select name="supplier_id" required class="form-select form-select-solid @error('supplier_id') is-invalid @enderror">
                                    <option value="">— Pilih Supplier —</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
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
                                    <input type="text" name="name" value="{{ old('name') }}" required
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
                                            <option value="{{ $u }}" {{ old('unit') == $u ? 'selected' : '' }}>{{ $u }}</option>
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
                                    <input type="text" name="sku" value="{{ old('sku') }}" required maxlength="50"
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
                                        <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price', 0) }}" 
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
                                               value="{{ old('discount_percentage', 0) }}" 
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
                                        <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price', 0) }}" 
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
                                               value="{{ old('discount_amount', 0) }}" 
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
                                                    <span class="text-success fs-2 fw-bold" id="gross_profit_display">Rp 0</span>
                                                    <span class="text-gray-600 fs-8" id="gross_margin_display">Margin: 0%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">HARGA SETELAH DISKON</span>
                                                    <span class="text-primary fs-2 fw-bold" id="final_price_display">Rp 0</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">LABA BERSIH</span>
                                                    <span class="text-success fs-2 fw-bold" id="net_profit_display">Rp 0</span>
                                                    <span class="text-gray-600 fs-8" id="net_margin_display">Margin: 0%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">STATUS PROFIT</span>
                                                    <span class="badge fs-6 fw-bold" id="profit_status_badge">-</span>
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
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold">Kategori Produk</label>
                                <select name="category" class="form-select form-select-solid @error('category') is-invalid @enderror">
                                    <option value="">— Pilih Kategori —</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c }}" {{ old('category') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="separator separator-dashed my-7"></div>
                            </div>

                            {{-- Deskripsi --}}
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold">Deskripsi Produk</label>
                                <textarea name="description" rows="2" placeholder="Keterangan tambahan produk..."
                                          class="form-control form-control-solid @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Narkotika Checkbox --}}
                            <div class="col-12 mb-5">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" name="is_narcotic" value="1" id="is_narcotic" 
                                           {{ old('is_narcotic') ? 'checked' : '' }}
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
                                    <option value="I" {{ old('narcotic_group') === 'I' ? 'selected' : '' }}>Golongan I</option>
                                    <option value="II" {{ old('narcotic_group') === 'II' ? 'selected' : '' }}>Golongan II</option>
                                    <option value="III" {{ old('narcotic_group') === 'III' ? 'selected' : '' }}>Golongan III</option>
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
                            <a href="{{ url('/products') }}" class="btn btn-light">
                                <i class="ki-outline ki-arrow-zigzag fs-2"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary create-confirm" data-type="produk">
                                <i class="ki-outline ki-picture fs-2"></i>
                                Simpan Produk
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
// Profit Calculator
document.addEventListener('DOMContentLoaded', function() {
    const costPriceInput = document.getElementById('cost_price');
    const sellingPriceInput = document.getElementById('selling_price');
    const discountPercentageInput = document.getElementById('discount_percentage');
    const discountAmountInput = document.getElementById('discount_amount');

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    }

    function calculateProfit() {
        const costPrice = parseFloat(costPriceInput.value) || 0;
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
        const discountPercentage = parseFloat(discountPercentageInput.value) || 0;

        // Calculate discount amount
        const discountAmount = (sellingPrice * discountPercentage) / 100;
        discountAmountInput.value = Math.round(discountAmount);

        // Calculate gross profit
        const grossProfit = sellingPrice - costPrice;
        const grossMargin = sellingPrice > 0 ? (grossProfit / sellingPrice) * 100 : 0;

        // Calculate final price after discount
        const finalPrice = sellingPrice - discountAmount;

        // Calculate net profit
        const netProfit = finalPrice - costPrice;
        const netMargin = finalPrice > 0 ? (netProfit / finalPrice) * 100 : 0;

        // Update display
        document.getElementById('gross_profit_display').textContent = formatRupiah(grossProfit);
        document.getElementById('gross_margin_display').textContent = `Margin: ${grossMargin.toFixed(2)}%`;
        
        document.getElementById('final_price_display').textContent = formatRupiah(finalPrice);
        
        document.getElementById('net_profit_display').textContent = formatRupiah(netProfit);
        document.getElementById('net_margin_display').textContent = `Margin: ${netMargin.toFixed(2)}%`;

        // Update profit status badge
        const statusBadge = document.getElementById('profit_status_badge');
        if (netMargin >= 20) {
            statusBadge.textContent = 'PROFIT TINGGI';
            statusBadge.className = 'badge badge-success fs-6 fw-bold';
        } else if (netMargin >= 10) {
            statusBadge.textContent = 'PROFIT BAIK';
            statusBadge.className = 'badge badge-primary fs-6 fw-bold';
        } else if (netMargin >= 5) {
            statusBadge.textContent = 'PROFIT RENDAH';
            statusBadge.className = 'badge badge-warning fs-6 fw-bold';
        } else if (netMargin > 0) {
            statusBadge.textContent = 'PROFIT MINIMAL';
            statusBadge.className = 'badge badge-warning fs-6 fw-bold';
        } else {
            statusBadge.textContent = 'RUGI / NO PROFIT';
            statusBadge.className = 'badge badge-danger fs-6 fw-bold';
        }
    }

    // Add event listeners
    costPriceInput.addEventListener('input', calculateProfit);
    sellingPriceInput.addEventListener('input', calculateProfit);
    discountPercentageInput.addEventListener('input', calculateProfit);

    // Initial calculation
    calculateProfit();
});
</script>
@endpush
