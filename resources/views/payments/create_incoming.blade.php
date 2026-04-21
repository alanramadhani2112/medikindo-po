@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">Manual Payment Entry</h1>
            <p class="text-gray-600 fs-6 mb-0">Rekam penerimaan pembayaran dari RS/Klinik (AR)</p>
        </div>
        <a href="{{ route('web.payments.index') }}" class="btn btn-light btn-sm">
            <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Kembali
        </a>
    </div>

    {{-- Important Notice --}}
    <div class="alert alert-warning d-flex align-items-center mb-7">
        <i class="ki-outline ki-information-5 fs-2x text-warning me-4"></i>
        <div class="d-flex flex-column">
            <h5 class="mb-1 fw-bold">Perhatian: Manual Entry</h5>
            <span class="fs-7">
                Halaman ini untuk <strong>input manual pembayaran khusus</strong> (cash, cek cair, koreksi, dll). 
                <br>Jika RS sudah submit bukti bayar, <strong>JANGAN input ulang di sini</strong> — cukup approve di menu 
                <a href="{{ route('web.payment-proofs.index') }}" class="fw-bold text-warning text-decoration-underline">Payment Proofs</a> 
                dan sistem akan otomatis mencatat pembayaran.
            </span>
        </div>
    </div>

    <div class="row g-5 g-xl-10" x-data="{
        invoiceId: '{{ old('customer_invoice_id', '') }}',
        paymentMethod: '{{ old('payment_method', '') }}',
        invoices: @js($invoices->map(fn($i) => ['id' => $i->id, 'outstanding' => $i->total_amount - $i->paid_amount])),
        
        get showBankFields() {
            return ['Bank Transfer', 'Virtual Account'].includes(this.paymentMethod);
        },
        
        get showGiroFields() {
            return this.paymentMethod === 'Giro/Cek';
        },
        
        get showCashFields() {
            return this.paymentMethod === 'Cash';
        },
        
        selectInvoice() {
            const inv = this.invoices.find(i => i.id == this.invoiceId);
            if (inv) {
                const amountInput = document.getElementById('amountInput');
                if (amountInput) {
                    amountInput.value = inv.outstanding;
                }
            }
        }
    }">
        {{-- Form --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-entrance-right fs-2 me-2 text-success"></i>
                        Formulir Penerimaan Kas (AR)
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.payments.store.incoming') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- 1. Invoice Selection --}}
                        <div class="mb-8">
                            <label class="form-label fw-bold required">Pilih Invoice AR</label>
                            <select name="customer_invoice_id" x-model="invoiceId" @change="selectInvoice()"
                                class="form-select form-select-solid @error('customer_invoice_id') is-invalid @enderror"
                                required>
                                <option value="">— Pilih Tagihan yang Belum Lunas —</option>
                                @foreach($invoices as $inv)
                                    @php
                                        $outstanding = $inv->total_amount - $inv->paid_amount;
                                    @endphp
                                    <option value="{{ $inv->id }}" {{ old('customer_invoice_id') == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} — {{ $inv->organization?->name }}
                                        (Sisa: Rp {{ number_format($outstanding, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_invoice_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 2. Amount (Autofill dari sisa tagihan) --}}
                        <div class="mb-8">
                            <label class="form-label fw-bold required">Jumlah Pembayaran (Rp)</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text fw-bold">Rp</span>
                                <input type="number" name="amount" id="amountInput"
                                    class="form-control form-control-solid @error('amount') is-invalid @enderror"
                                    placeholder="Pilih invoice terlebih dahulu" min="1" step="1"
                                    value="{{ old('amount') }}" required>
                            </div>
                            <div class="form-text text-muted">
                                <i class="ki-outline ki-information-4 fs-7"></i>
                                Jumlah otomatis terisi sesuai sisa tagihan. Anda bisa ubah jika pembayaran sebagian.
                            </div>
                            @error('amount')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 3. Tanggal Pembayaran --}}
                        <div class="mb-8">
                            <label class="form-label fw-bold required">Tanggal Pembayaran</label>
                            <input type="date" name="payment_date"
                                class="form-control form-control-solid @error('payment_date') is-invalid @enderror"
                                value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 4. Metode Pembayaran --}}
                        <div class="mb-8">
                            <label class="form-label fw-bold required">Metode Pembayaran</label>
                            <select name="payment_method" x-model="paymentMethod"
                                class="form-select form-select-solid @error('payment_method') is-invalid @enderror"
                                required>
                                <option value="">— Pilih Metode Pembayaran —</option>
                                <option value="Bank Transfer" @selected(old('payment_method') === 'Bank Transfer')>🏦 Bank Transfer</option>
                                <option value="Virtual Account" @selected(old('payment_method') === 'Virtual Account')>💳 Virtual Account</option>
                                <option value="Giro/Cek" @selected(old('payment_method') === 'Giro/Cek')>📄 Giro/Cek</option>
                                <option value="Cash" @selected(old('payment_method') === 'Cash')>💵 Cash (Tunai)</option>
                            </select>
                            @error('payment_method')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 5. Detail Rekening (Conditional) - HANYA TAMPILKAN SESUAI METODE --}}
                        
                        {{-- 5a. Bank Transfer / Virtual Account ONLY --}}
                        <template x-if="showBankFields">
                            <div>
                                <div class="mb-8">
                                    <label class="form-label fw-bold required">Bank Pengirim (RS/Klinik)</label>
                                    <select name="sender_bank_name" class="form-select form-select-solid @error('sender_bank_name') is-invalid @enderror">
                                        <option value="">— Pilih Bank —</option>
                                        @foreach(\Database\Seeders\IndonesianBankSeeder::$BANKS as $bank)
                                            <option value="{{ $bank['name'] }}" @selected(old('sender_bank_name') === $bank['name'])>
                                                {{ $bank['name'] }} ({{ $bank['code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">
                                        <i class="ki-outline ki-information-4 fs-7"></i>
                                        Bank yang digunakan RS/Klinik untuk transfer
                                    </div>
                                    @error('sender_bank_name')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-8">
                                    <label class="form-label fw-bold required">Nomor Rekening Pengirim</label>
                                    <input type="text" name="sender_account_number" class="form-control form-control-solid @error('sender_account_number') is-invalid @enderror"
                                           placeholder="Contoh: 1234567890"
                                           value="{{ old('sender_account_number') }}">
                                    <div class="form-text text-muted">
                                        <i class="ki-outline ki-information-4 fs-7"></i>
                                        Nomor rekening RS/Klinik yang digunakan untuk transfer
                                    </div>
                                    @error('sender_account_number')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-8">
                                    <label class="form-label fw-bold required">Nomor Referensi Transfer</label>
                                    <input type="text" name="reference" class="form-control form-control-solid @error('reference') is-invalid @enderror"
                                           placeholder="Contoh: TRF-20260421-001"
                                           value="{{ old('reference') }}">
                                    <div class="form-text text-muted">Nomor referensi dari slip transfer bank</div>
                                    @error('reference')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </template>

                        {{-- 5b. Giro/Cek ONLY --}}
                        <template x-if="showGiroFields">
                            <div>
                                <div class="row mb-8">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold required">Nomor Giro/Cek</label>
                                        <input type="text" name="giro_number" class="form-control form-control-solid @error('giro_number') is-invalid @enderror"
                                               placeholder="Contoh: GR-12345678"
                                               value="{{ old('giro_number') }}">
                                        <div class="form-text text-muted">Nomor seri giro atau cek</div>
                                        @error('giro_number')
                                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold required">Tanggal Jatuh Tempo</label>
                                        <input type="date" name="giro_due_date" class="form-control form-control-solid @error('giro_due_date') is-invalid @enderror"
                                               value="{{ old('giro_due_date') }}">
                                        <div class="form-text text-muted">Tanggal giro/cek dapat dicairkan</div>
                                        @error('giro_due_date')
                                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-8">
                                    <label class="form-label fw-bold required">Bank Penerbit Giro/Cek</label>
                                    <select name="issuing_bank" class="form-select form-select-solid @error('issuing_bank') is-invalid @enderror">
                                        <option value="">— Pilih Bank —</option>
                                        @foreach(\Database\Seeders\IndonesianBankSeeder::$BANKS as $bank)
                                            <option value="{{ $bank['name'] }}" @selected(old('issuing_bank') === $bank['name'])>
                                                {{ $bank['name'] }} ({{ $bank['code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">Bank yang menerbitkan giro/cek</div>
                                    @error('issuing_bank')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-8">
                                    <label class="form-label fw-bold required">Nomor Referensi</label>
                                    <input type="text" name="reference" class="form-control form-control-solid @error('reference') is-invalid @enderror"
                                           placeholder="Contoh: REF-001234"
                                           value="{{ old('reference') }}">
                                    <div class="form-text text-muted">Nomor referensi transaksi giro/cek</div>
                                    @error('reference')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </template>

                        {{-- 5c. Cash ONLY --}}
                        <template x-if="showCashFields">
                            <div>
                                <div class="mb-8">
                                    <label class="form-label fw-bold required">Nomor Kwitansi</label>
                                    <input type="text" name="receipt_number" class="form-control form-control-solid @error('receipt_number') is-invalid @enderror"
                                           placeholder="Contoh: KWT-001234"
                                           value="{{ old('receipt_number') }}">
                                    <div class="form-text text-muted">Nomor kwitansi pembayaran tunai</div>
                                    @error('receipt_number')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </template>

                        {{-- 6. Upload Bukti Bayar (WAJIB) --}}
                        <div class="mb-8">
                            <label class="form-label fw-bold required">
                                <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">Upload Bukti Transfer</span>
                                <span x-show="paymentMethod === 'Giro/Cek'">Upload Foto Giro/Cek</span>
                                <span x-show="paymentMethod === 'Cash'">Upload Kwitansi</span>
                                <span x-show="paymentMethod === ''">Upload Bukti Pembayaran</span>
                                <span class="badge badge-light-danger ms-2">Wajib</span>
                            </label>
                            <input type="file" name="payment_proof_file"
                                class="form-control form-control-solid @error('payment_proof_file') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.pdf" required>
                            @error('payment_proof_file')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-muted fs-7 mt-2">
                                <i class="ki-outline ki-information-4 fs-6"></i>
                                Format: JPG, PNG, PDF. Maks 5MB.
                                <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">Upload screenshot/foto bukti transfer.</span>
                                <span x-show="paymentMethod === 'Giro/Cek'">Upload foto giro/cek yang jelas dan terbaca.</span>
                                <span x-show="paymentMethod === 'Cash'">Upload foto kwitansi pembayaran tunai.</span>
                                <span x-show="paymentMethod === ''">Bukti transfer/kwitansi/slip pembayaran.</span>
                            </div>
                        </div>

                        {{-- 7. Bank Penerima (Medikindo) - WAJIB --}}
                        <div class="mb-8">
                            <label class="form-label fw-bold required">Bank Penerima (Medikindo)</label>
                            <select name="bank_account_id"
                                class="form-select form-select-solid @error('bank_account_id') is-invalid @enderror"
                                required>
                                <option value="">— Pilih Rekening Medikindo —</option>
                                @foreach(\App\Models\BankAccount::forReceive()->orderBy('default_for_receive','desc')->orderBy('default_priority')->get() as $bank)
                                    <option value="{{ $bank->id }}" @selected(old('bank_account_id') == $bank->id || $bank->default_for_receive)>
                                        {{ $bank->bank_name }} — {{ $bank->account_number }}
                                        @if($bank->default_for_receive) ★ Default @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">Rekening Medikindo yang menerima transfer</div>
                            @error('bank_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 8. Catatan (Opsional) --}}
                        <div class="mb-10">
                            <label class="form-label fw-bold">Catatan Tambahan <span class="text-muted fs-7">(Opsional)</span></label>
                            <textarea name="notes" rows="3"
                                class="form-control form-control-solid @error('notes') is-invalid @enderror"
                                placeholder="Informasi tambahan...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end gap-3 pt-5 border-top border-gray-200">
                            <a href="{{ route('web.payments.index') }}" class="btn btn-light">
                                <i class="ki-outline ki-arrow-left fs-2"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success"
                                    :disabled="invoiceId === '' || paymentMethod === ''">
                                <i class="ki-outline ki-check fs-2"></i>
                                Rekam Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Info Panel --}}
        <div class="col-lg-4">
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
