@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">Edit Rekening Bank</h1>
            <p class="text-gray-600 fs-6 mb-0">{{ $bankAccount->bank_name }} — {{ $bankAccount->account_number }}</p>
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
                    <div class="card-toolbar d-flex gap-2">
                        @if($bankAccount->is_default)
                            <span class="badge badge-warning">
                                <i class="ki-outline ki-star fs-8 me-1"></i>DEFAULT
                            </span>
                        @endif
                        @if($bankAccount->is_active)
                            <span class="badge badge-light-success">AKTIF</span>
                        @else
                            <span class="badge badge-light-secondary">NONAKTIF</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.bank-accounts.update', $bankAccount) }}">
                        @csrf @method('PUT')

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
                                        {{ old('bank_name', $bankAccount->bank_name) === $bank['name'] ? 'selected' : '' }}>
                                        {{ $bank['name'] }} ({{ $bank['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="hidden" name="bank_code" id="bankCodeInput"
                            value="{{ old('bank_code', $bankAccount->bank_code) }}">

                        <div class="mb-5">
                            <label class="form-label required fw-bold">Nomor Rekening</label>
                            <input type="text" name="account_number"
                                class="form-control form-control-solid @error('account_number') is-invalid @enderror"
                                value="{{ old('account_number', $bankAccount->account_number) }}" required>
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label required fw-bold">Atas Nama</label>
                            <input type="text" name="account_holder_name"
                                class="form-control form-control-solid @error('account_holder_name') is-invalid @enderror"
                                value="{{ old('account_holder_name', $bankAccount->account_holder_name) }}" required>
                            @error('account_holder_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label required fw-bold">Tipe Rekening</label>
                            <select name="account_type"
                                class="form-select form-select-solid @error('account_type') is-invalid @enderror"
                                required>
                                <option value="both"    {{ old('account_type', $bankAccount->account_type) === 'both'    ? 'selected' : '' }}>
                                    🔄 Masuk & Keluar — bisa terima dari RS dan kirim ke Supplier
                                </option>
                                <option value="receive" {{ old('account_type', $bankAccount->account_type) === 'receive' ? 'selected' : '' }}>
                                    ⬇️ Terima Masuk saja — khusus menerima pembayaran dari RS/Klinik
                                </option>
                                <option value="send"    {{ old('account_type', $bankAccount->account_type) === 'send'    ? 'selected' : '' }}>
                                    ⬆️ Kirim Keluar saja — khusus mengirim pembayaran ke Supplier
                                </option>
                            </select>
                            @error('account_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-8">
                            <label class="form-label fw-bold">Catatan <span class="text-muted">(Opsional)</span></label>
                            <textarea name="notes" rows="3"
                                class="form-control form-control-solid @error('notes') is-invalid @enderror">{{ old('notes', $bankAccount->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-3 pt-5 border-top border-gray-200">
                            <a href="{{ route('web.bank-accounts.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-check fs-4 me-1"></i>Simpan Perubahan
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
<script>
function updateBankCode(select) {
    const option = select.options[select.selectedIndex];
    document.getElementById('bankCodeInput').value = option.dataset.code || '';
}
</script>
@endpush
