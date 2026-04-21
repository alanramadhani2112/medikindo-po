@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">Tambah Pembayaran Masuk</h1>
            <p class="text-gray-600 fs-6 mb-0">Rekam penerimaan pembayaran dari RS/Klinik (AR)</p>
        </div>
        <a href="{{ route('web.payments.index') }}" class="btn btn-light btn-sm">
            <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Kembali
        </a>
    </div>

    <div class="row">
        {{-- Form --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-entrance-right fs-2 me-2 text-success"></i>
                        Formulir Penerimaan Kas (AR)
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.payments.store.incoming') }}" id="paymentForm">
                        @csrf

                        {{-- Invoice Selection --}}
                        <div class="mb-6">
                            <label class="form-label fw-bold required">Pilih Invoice AR</label>
                            <select name="customer_invoice_id" id="invoiceSelect"
                                class="form-select form-select-solid @error('customer_invoice_id') is-invalid @enderror"
                                onchange="onInvoiceChange(this)" required>
                                <option value="">— Pilih Tagihan yang Belum Lunas —</option>
                                @foreach($invoices as $inv)
                                    @php
                                        $outstanding = $inv->total_amount - $inv->paid_amount;
                                        $isPreselected = request('invoice_id') == $inv->id;
                                    @endphp
                                    <option value="{{ $inv->id }}"
                                        data-outstanding="{{ $outstanding }}"
                                        data-total="{{ $inv->total_amount }}"
                                        data-paid="{{ $inv->paid_amount }}"
                                        data-number="{{ $inv->invoice_number }}"
                                        data-org="{{ $inv->organization?->name }}"
                                        data-due="{{ $inv->due_date?->format('d M Y') ?? '—' }}"
                                        data-overdue="{{ $inv->isOverdueByDate() ? '1' : '0' }}"
                                        data-days="{{ $inv->days_overdue }}"
                                        {{ $isPreselected || old('customer_invoice_id') == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} — {{ $inv->organization?->name }}
                                        (Sisa: Rp {{ number_format($outstanding, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_invoice_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Invoice Info Card (dynamic) --}}
                        <div id="invoiceInfoCard" class="alert alert-dismissible d-none mb-6 p-4 rounded border">
                            <div class="row g-3">
                                <div class="col-6">
                                    <span class="text-gray-500 fs-8 fw-bold d-block">TOTAL TAGIHAN</span>
                                    <span id="infoTotal" class="text-gray-900 fw-bold fs-5">—</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-gray-500 fs-8 fw-bold d-block">SUDAH DIBAYAR</span>
                                    <span id="infoPaid" class="text-success fw-bold fs-5">—</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-gray-500 fs-8 fw-bold d-block">SISA TAGIHAN</span>
                                    <span id="infoOutstanding" class="text-danger fw-bold fs-5">—</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-gray-500 fs-8 fw-bold d-block">JATUH TEMPO</span>
                                    <span id="infoDue" class="fw-bold fs-6">—</span>
                                </div>
                            </div>
                            <div id="overdueAlert" class="d-none mt-3 p-2 rounded bg-light-danger">
                                <i class="ki-outline ki-time fs-6 text-danger me-1"></i>
                                <span id="overdueText" class="text-danger fs-7 fw-semibold"></span>
                            </div>
                        </div>

                        {{-- Amount --}}
                        <div class="mb-6">
                            <label class="form-label fw-bold required">Jumlah Pembayaran (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rp</span>
                                <input type="number" name="amount" id="amountInput"
                                    class="form-control form-control-solid @error('amount') is-invalid @enderror"
                                    placeholder="0" min="1" step="1"
                                    value="{{ old('amount') }}" required
                                    oninput="validateAmount(this)">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="amountHelper" class="form-text text-muted mt-1">
                                Pilih invoice terlebih dahulu
                            </div>
                            {{-- Quick fill buttons --}}
                            <div id="quickFillBtns" class="d-none mt-2 d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-light-primary" onclick="fillAmount('full')">
                                    Bayar Penuh
                                </button>
                                <button type="button" class="btn btn-sm btn-light-warning" onclick="fillAmount('half')">
                                    50%
                                </button>
                                <button type="button" class="btn btn-sm btn-light-info" onclick="fillAmount('quarter')">
                                    25%
                                </button>
                            </div>
                        </div>

                        {{-- Date & Method --}}
                        <div class="row g-5 mb-6">
                            <div class="col-md-6">
                                <label class="form-label fw-bold required">Tanggal Pembayaran</label>
                                <input type="date" name="payment_date"
                                    class="form-control form-control-solid @error('payment_date') is-invalid @enderror"
                                    value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold required">Metode Pembayaran</label>
                                <select name="payment_method" id="paymentMethodSelect"
                                    class="form-select form-select-solid @error('payment_method') is-invalid @enderror"
                                    onchange="onMethodChange(this)" required>
                                    <option value="">— Pilih Metode —</option>
                                    <option value="Bank Transfer" @selected(old('payment_method') === 'Bank Transfer')>🏦 Bank Transfer</option>
                                    <option value="Virtual Account" @selected(old('payment_method') === 'Virtual Account')>💳 Virtual Account</option>
                                    <option value="QRIS"          @selected(old('payment_method') === 'QRIS')>📱 QRIS</option>
                                    <option value="Giro"          @selected(old('payment_method') === 'Giro')>📄 Giro</option>
                                    <option value="Cek"           @selected(old('payment_method') === 'Cek')>📝 Cek</option>
                                    <option value="Cash"          @selected(old('payment_method') === 'Cash')>💵 Cash (Tunai)</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Bank Selection (shown when method = Bank Transfer / VA / Giro / Cek) --}}
                        <div id="bankSection" class="mb-6 d-none">
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Bank Penerima (Medikindo)</label>
                                    <select name="bank_account_id"
                                        class="form-select form-select-solid @error('bank_account_id') is-invalid @enderror">
                                        <option value="">— Pilih Rekening Medikindo —</option>
                                        @foreach(\App\Models\BankAccount::forReceive()->orderBy('default_for_receive','desc')->orderBy('default_priority')->get() as $bank)
                                            <option value="{{ $bank->id }}" @selected(old('bank_account_id') == $bank->id || $bank->default_for_receive)>
                                                {{ $bank->bank_name }} — {{ $bank->account_number }}
                                                @if($bank->default_for_receive) ★ Default Terima @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">Rekening Medikindo yang menerima transfer</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Bank Pengirim (RS/Klinik)</label>
                                    <input type="text" name="bank_name_manual"
                                        class="form-control form-control-solid"
                                        placeholder="Mis. BCA, Mandiri, BNI..."
                                        value="{{ old('bank_name_manual') }}">
                                    <div class="form-text text-muted">Bank yang digunakan RS/Klinik untuk transfer</div>
                                </div>
                            </div>
                        </div>

                        {{-- Reference --}}
                        <div class="mb-6">
                            <label class="form-label fw-bold">Nomor Referensi <span class="text-muted">(Opsional)</span></label>
                            <input type="text" name="reference" placeholder="Mis. TRF-20260421-001 / No. Bukti Transfer"
                                class="form-control form-control-solid @error('reference') is-invalid @enderror"
                                value="{{ old('reference') }}">
                            <div class="form-text text-muted">Nomor referensi dari bukti transfer / slip pembayaran</div>
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="mb-8">
                            <label class="form-label fw-bold">Catatan <span class="text-muted">(Opsional)</span></label>
                            <textarea name="notes" rows="2"
                                class="form-control form-control-solid @error('notes') is-invalid @enderror"
                                placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end gap-3 pt-5 border-top border-gray-200">
                            <a href="{{ route('web.payments.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="ki-outline ki-check-circle fs-4 me-1"></i>
                                Rekam Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Info Panel --}}
        <div class="col-lg-5">
            {{-- Validation Rules --}}
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title fs-6">
                        <i class="ki-outline ki-shield-tick fs-4 me-2 text-primary"></i>
                        Aturan Validasi
                    </h3>
                </div>
                <div class="card-body py-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="ki-outline ki-check-circle fs-4 text-success"></i>
                            <span class="text-gray-700 fs-7">Jumlah tidak boleh melebihi sisa tagihan</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="ki-outline ki-check-circle fs-4 text-success"></i>
                            <span class="text-gray-700 fs-7">Jumlah harus lebih dari Rp 0</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="ki-outline ki-check-circle fs-4 text-success"></i>
                            <span class="text-gray-700 fs-7">Invoice harus dalam status Menunggu atau Partial</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="ki-outline ki-check-circle fs-4 text-success"></i>
                            <span class="text-gray-700 fs-7">Status otomatis diperbarui setelah pembayaran</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Flow --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title fs-6">
                        <i class="ki-outline ki-arrows-circle fs-4 me-2 text-info"></i>
                        Alur Status Otomatis
                    </h3>
                </div>
                <div class="card-body py-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3 p-3 rounded bg-light-warning">
                            <span class="badge badge-light-warning fw-bold">Menunggu</span>
                            <i class="ki-outline ki-arrow-right fs-5 text-gray-500"></i>
                            <span class="badge badge-light-info fw-bold">Partial</span>
                            <span class="text-gray-500 fs-8">(bayar sebagian)</span>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-3 rounded bg-light-info">
                            <span class="badge badge-light-info fw-bold">Partial</span>
                            <i class="ki-outline ki-arrow-right fs-5 text-gray-500"></i>
                            <span class="badge badge-light-success fw-bold">Lunas</span>
                            <span class="text-gray-500 fs-8">(bayar penuh)</span>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-3 rounded bg-light-success">
                            <span class="badge badge-light-warning fw-bold">Menunggu</span>
                            <i class="ki-outline ki-arrow-right fs-5 text-gray-500"></i>
                            <span class="badge badge-light-success fw-bold">Lunas</span>
                            <span class="text-gray-500 fs-8">(bayar penuh)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let maxOutstanding = 0;

    function formatRupiah(amount) {
        return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
    }

    function onMethodChange(select) {
        const bankMethods = ['Bank Transfer', 'Virtual Account', 'Giro', 'Cek'];
        const bankSection = document.getElementById('bankSection');
        if (bankMethods.includes(select.value)) {
            bankSection.classList.remove('d-none');
        } else {
            bankSection.classList.add('d-none');
        }
    }

    function onInvoiceChange(select) {
        const option = select.options[select.selectedIndex];
        const card   = document.getElementById('invoiceInfoCard');
        const quickFill = document.getElementById('quickFillBtns');

        if (!option.value) {
            card.classList.add('d-none');
            quickFill.classList.add('d-none');
            maxOutstanding = 0;
            document.getElementById('amountHelper').textContent = 'Pilih invoice terlebih dahulu';
            return;
        }

        const outstanding = parseFloat(option.dataset.outstanding);
        const total       = parseFloat(option.dataset.total);
        const paid        = parseFloat(option.dataset.paid);
        const isOverdue   = option.dataset.overdue === '1';
        const days        = parseInt(option.dataset.days);

        maxOutstanding = outstanding;

        // Update info card
        document.getElementById('infoTotal').textContent       = formatRupiah(total);
        document.getElementById('infoPaid').textContent        = formatRupiah(paid);
        document.getElementById('infoOutstanding').textContent = formatRupiah(outstanding);
        document.getElementById('infoDue').textContent         = option.dataset.due;

        // Overdue alert
        const overdueAlert = document.getElementById('overdueAlert');
        if (isOverdue && days > 0) {
            overdueAlert.classList.remove('d-none');
            document.getElementById('overdueText').textContent =
                'Invoice ini sudah lewat jatuh tempo ' + days + ' hari!';
        } else {
            overdueAlert.classList.add('d-none');
        }

        // Show card
        card.classList.remove('d-none');
        card.classList.remove('alert-warning', 'alert-danger', 'alert-info');
        card.classList.add(isOverdue ? 'alert-danger' : 'alert-info');
        card.style.borderColor = isOverdue ? '#f1416c' : '#009ef7';

        // Update amount input
        const amountInput = document.getElementById('amountInput');
        amountInput.setAttribute('max', outstanding);
        amountInput.value = outstanding;

        // Update helper
        document.getElementById('amountHelper').innerHTML =
            '<span class="text-success fw-semibold">Max: ' + formatRupiah(outstanding) + '</span>';

        // Show quick fill
        quickFill.classList.remove('d-none');
    }

    function fillAmount(type) {
        const input = document.getElementById('amountInput');
        if (!maxOutstanding) return;

        switch(type) {
            case 'full':    input.value = Math.floor(maxOutstanding); break;
            case 'half':    input.value = Math.floor(maxOutstanding * 0.5); break;
            case 'quarter': input.value = Math.floor(maxOutstanding * 0.25); break;
        }
        validateAmount(input);
    }

    function validateAmount(input) {
        const val = parseFloat(input.value);
        const helper = document.getElementById('amountHelper');
        const submitBtn = document.getElementById('submitBtn');

        if (!maxOutstanding) return;

        if (val > maxOutstanding) {
            input.classList.add('is-invalid');
            helper.innerHTML = '<span class="text-danger fw-semibold">⚠ Melebihi sisa tagihan: ' + formatRupiah(maxOutstanding) + '</span>';
            submitBtn.disabled = true;
        } else if (val <= 0) {
            input.classList.add('is-invalid');
            helper.innerHTML = '<span class="text-danger fw-semibold">⚠ Jumlah harus lebih dari 0</span>';
            submitBtn.disabled = true;
        } else {
            input.classList.remove('is-invalid');
            helper.innerHTML = '<span class="text-success fw-semibold">✓ Max: ' + formatRupiah(maxOutstanding) + '</span>';
            submitBtn.disabled = false;
        }
    }

    // Auto-trigger if invoice_id is pre-selected
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('invoiceSelect');
        if (select.value) {
            onInvoiceChange(select);
        }
    });
</script>
@endpush
