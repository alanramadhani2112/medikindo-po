@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">Tambah Rekening Bank</h1>
            <p class="text-gray-600 fs-6 mb-0">Tambahkan rekening bank Medikindo sebagai tujuan pembayaran</p>
        </div>
        <a href="{{ route('web.bank-accounts.index') }}" class="btn btn-light btn-sm">
            <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold">Detail Rekening</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.bank-accounts.store') }}">
                        @csrf

                        <div class="mb-5">
                            <label class="form-label required fw-bold">Nama Bank</label>
                            <select name="bank_name"
                                class="form-select form-select-solid @error('bank_name') is-invalid @enderror"
                                id="bankSelect"
                                onchange="updateBankCode(this)"
                                required>
                                <option value="">— Pilih Bank —</option>
                                @foreach(\Database\Seeders\IndonesianBankSeeder::$BANKS as $bank)
                                    <option value="{{ $bank['name'] }}"
                                        data-code="{{ $bank['code'] }}"
                                        {{ old('bank_name') === $bank['name'] ? 'selected' : '' }}>
                                        {{ $bank['name'] }} ({{ $bank['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Pilih dari daftar bank terpercaya di Indonesia</div>
                        </div>

                        <input type="hidden" name="bank_code" id="bankCodeInput" value="{{ old('bank_code') }}">

                        <div class="mb-5">
                            <label class="form-label required fw-bold">Nomor Rekening</label>
                            <input type="text" name="account_number"
                                class="form-control form-control-solid @error('account_number') is-invalid @enderror"
                                placeholder="Contoh: 1234567890"
                                value="{{ old('account_number') }}" required>
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label required fw-bold">Atas Nama</label>
                            <input type="text" name="account_holder_name"
                                class="form-control form-control-solid @error('account_holder_name') is-invalid @enderror"
                                placeholder="Contoh: PT Medikindo Farma"
                                value="{{ old('account_holder_name') }}" required>
                            @error('account_holder_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-8">
                            <label class="form-label fw-bold">Catatan <span class="text-muted">(Opsional)</span></label>
                            <textarea name="notes" rows="3"
                                class="form-control form-control-solid @error('notes') is-invalid @enderror"
                                placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-3 pt-5 border-top border-gray-200">
                            <a href="{{ route('web.bank-accounts.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-check fs-4 me-1"></i>Simpan Rekening
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border border-dashed border-info">
                <div class="card-body py-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <i class="ki-outline ki-information-5 fs-2 text-info"></i>
                        <span class="fw-bold text-gray-800 fs-6">Informasi</span>
                    </div>
                    <div class="d-flex flex-column gap-3 text-gray-600 fs-7">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ki-outline ki-check-circle fs-6 text-success mt-1"></i>
                            <span>Rekening ini akan ditampilkan di Customer Invoice sebagai tujuan transfer pembayaran RS/Klinik.</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="ki-outline ki-check-circle fs-6 text-success mt-1"></i>
                            <span>Setelah disimpan, Anda dapat menjadikannya sebagai rekening default untuk invoice baru.</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="ki-outline ki-check-circle fs-6 text-success mt-1"></i>
                            <span>Rekening yang sudah digunakan di invoice tidak dapat dihapus, hanya bisa dinonaktifkan.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
    @push('scripts')
    <script>
    function updateBankCode(select) {
        const option = select.options[select.selectedIndex];
        document.getElementById('bankCodeInput').value = option.dataset.code || '';
    }
    </script>
    @endpush
@endsection
