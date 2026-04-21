<x-layout>
    <x-page-header title="Submit Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10" x-data="{
        invoiceId: '{{ $invoice->id ?? old('customer_invoice_id', '') }}',
        paymentType: '{{ old('payment_type', 'full') }}',
        paymentMethod: '{{ old('payment_method', '') }}',
        outstanding: {{ $invoice ? ($invoice->total_amount - $invoice->paid_amount) : 0 }},
        partialAmount: '{{ old('amount', '') }}',
        invoices: @js($invoices->map(fn($i) => ['id' => $i->id, 'invoice_number' => $i->invoice_number, 'organization_name' => $i->organization->name, 'total_amount' => $i->total_amount, 'paid_amount' => $i->paid_amount, 'outstanding' => $i->total_amount - $i->paid_amount])),

        get showBankDropdown() {
            return ['Bank Transfer', 'Virtual Account'].includes(this.paymentMethod);
        },

        get showGiroFields() {
            return this.paymentMethod === 'Giro/Cek';
        },

        get amount() {
            if (this.paymentType === 'full') return this.outstanding;
            return parseFloat(this.partialAmount) || 0;
        },

        get amountFormatted() {
            return 'Rp ' + this.amount.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        },

        get isPartialValid() {
            const v = parseFloat(this.partialAmount);
            return v > 0 && v < this.outstanding;
        },

        selectInvoice() {
            const inv = this.invoices.find(i => i.id == this.invoiceId);
            if (inv) {
                this.outstanding = inv.outstanding;
                this.partialAmount = '';
            } else {
                this.outstanding = 0;
                this.partialAmount = '';
            }
        }
    }">
        <div class="col-lg-8">
            <x-card title="Form Bukti Pembayaran" icon="file-up">
                <form action="{{ route('web.payment-proofs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- 1. Invoice Selection --}}
                    <div class="mb-8">
                        <label class="required form-label fw-bold">Pilih Invoice Pelanggan</label>

                        @if($invoice)
                            {{-- LOCKED: Coming from specific invoice --}}
                            @php $sisa = $invoice->total_amount - $invoice->paid_amount; @endphp
                            <input type="hidden" name="customer_invoice_id" value="{{ $invoice->id }}">
                            <select class="form-select form-select-solid" disabled>
                                <option selected>
                                    {{ $invoice->invoice_number }} — {{ $invoice->organization->name }}
                                    (Sisa: Rp {{ number_format($sisa, 0, ',', '.') }})
                                </option>
                            </select>
                            <div class="text-muted fs-7 mt-1">
                                <i class="ki-outline ki-lock-2 fs-7"></i>
                                Invoice terkunci — pelunasan untuk pembayaran sebelumnya.
                            </div>
                        @else
                            {{-- OPEN: User picks an invoice --}}
                            <select name="customer_invoice_id" class="form-select form-select-solid"
                                    x-model="invoiceId" @change="selectInvoice()" required>
                                <option value="">-- Pilih Invoice yang Belum Lunas --</option>
                                @foreach($invoices as $inv)
                                    @php $sisa = $inv->total_amount - $inv->paid_amount; @endphp
                                    <option value="{{ $inv->id }}" {{ old('customer_invoice_id') == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} — {{ $inv->organization->name }}
                                        (Sisa: Rp {{ number_format($sisa, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('customer_invoice_id')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 2. Payment Type Toggle --}}
                    <div class="mb-8" x-show="invoiceId !== ''">
                        <label class="required form-label fw-bold">Jenis Pembayaran</label>
                        <div class="d-flex gap-3">
                            {{-- Bayar Penuh --}}
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="_payment_type_ui" id="type_full"
                                       x-model="paymentType" value="full">
                                <label class="btn btn-outline btn-outline-dashed btn-active-primary w-100 py-4" for="type_full">
                                    <span class="d-flex flex-column align-items-center gap-1">
                                        <i class="ki-outline ki-check-circle fs-2"></i>
                                        <span class="fw-bold fs-6">Bayar Penuh</span>
                                        <span class="text-muted fs-7">Lunasi seluruh tagihan</span>
                                    </span>
                                </label>
                            </div>
                            {{-- Bayar Sebagian --}}
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="_payment_type_ui" id="type_partial"
                                       x-model="paymentType" value="partial">
                                <label class="btn btn-outline btn-outline-dashed btn-active-warning w-100 py-4" for="type_partial">
                                    <span class="d-flex flex-column align-items-center gap-1">
                                        <i class="ki-outline ki-abstract-26 fs-2"></i>
                                        <span class="fw-bold fs-6">Bayar Sebagian</span>
                                        <span class="text-muted fs-7">Cicil sebagian tagihan</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Partial Amount Input --}}
                    <div class="mb-8" x-show="paymentType === 'partial' && invoiceId !== ''" x-cloak>
                        <label class="required form-label fw-bold">Nominal Bayar (Rp)</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text fw-bold">Rp</span>
                            <input type="number" class="form-control form-control-solid"
                                   x-model="partialAmount"
                                   :max="outstanding - 1"
                                   min="1" step="1"
                                   placeholder="Masukkan nominal pembayaran sebagian">
                        </div>
                        <div class="mt-2 fs-7"
                             :class="isPartialValid ? 'text-success' : 'text-danger'"
                             x-show="partialAmount !== ''" x-cloak>
                            <span x-show="!isPartialValid">
                                ⚠ Nominal harus lebih dari 0 dan kurang dari total tagihan tersisa.
                            </span>
                            <span x-show="isPartialValid">
                                ✓ Sisa tagihan setelah pembayaran:
                                Rp <span x-text="(outstanding - parseFloat(partialAmount)).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Hidden real amount field --}}
                    <input type="hidden" name="amount" :value="amount">
                    <input type="hidden" name="payment_type" :value="paymentType">

                    {{-- 3. Tanggal Pembayaran --}}
                    <div class="mb-8">
                        <label class="required form-label fw-bold">Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" class="form-control form-control-solid"
                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        @error('payment_date')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 4. Metode Pembayaran --}}
                    <div class="mb-8">
                        <label class="required form-label fw-bold">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select form-select-solid"
                                x-model="paymentMethod" required>
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

                    {{-- 5. Detail Rekening (Conditional) --}}
                    
                    {{-- 5a. Bank Transfer / Virtual Account → Pilih Bank + No. Rekening --}}
                    {{-- ALWAYS RENDER, just hide visually but keep in DOM --}}
                    <div :style="!showBankDropdown ? 'display: none;' : ''">
                        <div class="mb-8">
                            <label class="form-label fw-bold" :class="showBankDropdown ? 'required' : ''">Nama Bank</label>
                            <select name="sender_bank_name" class="form-select form-select-solid" 
                                    :disabled="!showBankDropdown">
                                <option value="">— Pilih Bank —</option>
                                @foreach(\Database\Seeders\IndonesianBankSeeder::$BANKS as $bank)
                                    <option value="{{ $bank['name'] }}" @selected(old('sender_bank_name') === $bank['name'])>
                                        {{ $bank['name'] }} ({{ $bank['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">
                                <i class="ki-outline ki-information-4 fs-7"></i>
                                Pilih bank yang Anda gunakan untuk transfer
                            </div>
                            @error('sender_bank_name')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-8">
                            <label class="form-label fw-bold" :class="showBankDropdown ? 'required' : ''">Nomor Rekening Pengirim</label>
                            <input type="text" name="sender_account_number" class="form-control form-control-solid"
                                   placeholder="Contoh: 1234567890"
                                   value="{{ old('sender_account_number') }}"
                                   :disabled="!showBankDropdown">
                            <div class="form-text text-muted">
                                <i class="ki-outline ki-information-4 fs-7"></i>
                                Nomor rekening RS/Klinik yang digunakan untuk transfer
                            </div>
                            @error('sender_account_number')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 5b. Giro/Cek → Nomor Giro/Cek + Tanggal Jatuh Tempo --}}
                    {{-- ALWAYS RENDER, just hide visually but keep in DOM --}}
                    <div :style="!showGiroFields ? 'display: none;' : ''">
                        <div class="row mb-8">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" :class="showGiroFields ? 'required' : ''">Nomor Giro/Cek</label>
                                <input type="text" name="giro_number" class="form-control form-control-solid"
                                       placeholder="Contoh: GR-12345678"
                                       value="{{ old('giro_number') }}"
                                       :disabled="!showGiroFields">
                                <div class="form-text text-muted">Nomor seri giro atau cek</div>
                                @error('giro_number')
                                    <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" :class="showGiroFields ? 'required' : ''">Tanggal Jatuh Tempo</label>
                                <input type="date" name="giro_due_date" class="form-control form-control-solid"
                                       value="{{ old('giro_due_date') }}"
                                       :disabled="!showGiroFields">
                                <div class="form-text text-muted">Tanggal giro/cek dapat dicairkan</div>
                                @error('giro_due_date')
                                    <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-8">
                            <label class="form-label fw-bold" :class="showGiroFields ? 'required' : ''">Nama Bank Penerbit Giro/Cek</label>
                            <select name="sender_bank_name" class="form-select form-select-solid"
                                    :disabled="!showGiroFields">
                                <option value="">— Pilih Bank —</option>
                                @foreach(\Database\Seeders\IndonesianBankSeeder::$BANKS as $bank)
                                    <option value="{{ $bank['name'] }}" @selected(old('sender_bank_name') === $bank['name'])>
                                        {{ $bank['name'] }} ({{ $bank['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('sender_bank_name')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 6. No. Referensi --}}
                    <div class="mb-8">
                        <label class="form-label fw-bold">
                            <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">No. Referensi Transfer</span>
                            <span x-show="paymentMethod === 'Cash'">No. Kwitansi</span>
                            <span x-show="paymentMethod === 'Giro/Cek'">No. Referensi</span>
                            <span x-show="paymentMethod === ''">No. Referensi</span>
                        </label>
                        <input type="text" name="bank_reference" class="form-control form-control-solid"
                               placeholder="Contoh: TRX-12345678"
                               value="{{ old('bank_reference') }}">
                        <div class="form-text text-muted">
                            <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">Nomor referensi dari slip transfer bank</span>
                            <span x-show="paymentMethod === 'Cash'">Nomor kwitansi pembayaran tunai</span>
                            <span x-show="paymentMethod === 'Giro/Cek'">Nomor referensi transaksi (opsional)</span>
                        </div>
                        @error('bank_reference')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 7. Catatan --}}
                    <div class="mb-8">
                        <label class="form-label fw-bold">Catatan Tambahan <span class="text-muted fs-7">(Opsional)</span></label>
                        <textarea name="notes" class="form-control form-control-solid" rows="3"
                                  placeholder="Informasi tambahan untuk tim Finance...">{{ old('notes') }}</textarea>
                    </div>

                    {{-- 8. Upload Bukti (WAJIB) --}}
                    <div class="mb-10">
                        <label class="required form-label fw-bold">
                            <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">Upload Bukti Transfer</span>
                            <span x-show="paymentMethod === 'Cash'">Upload Kwitansi</span>
                            <span x-show="paymentMethod === 'Giro/Cek'">Upload Foto Giro/Cek</span>
                            <span x-show="paymentMethod === ''">Upload Bukti Pembayaran</span>
                            <span class="badge badge-light-danger ms-2">Wajib</span>
                        </label>
                        <input type="file" name="file" class="form-control form-control-solid"
                               accept=".jpg,.jpeg,.png,.pdf" required>
                        @error('file')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-muted fs-7 mt-2">
                            <i class="ki-outline ki-information-4 fs-6"></i>
                            Format: JPG, PNG, PDF. Maks 5MB.
                            <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">Upload screenshot/foto bukti transfer.</span>
                            <span x-show="paymentMethod === 'Cash'">Upload foto kwitansi pembayaran tunai.</span>
                            <span x-show="paymentMethod === 'Giro/Cek'">Upload foto giro/cek yang jelas dan terbaca.</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                        <a href="{{ route('web.payment-proofs.index') }}" class="btn btn-light">
                            <i class="ki-outline ki-arrow-left fs-2"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary"
                                :disabled="invoiceId === '' || (paymentType === 'partial' && !isPartialValid) || paymentMethod === ''">
                            <i class="ki-outline ki-check fs-2"></i>
                            Submit Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Sidebar Info --}}
        <div class="col-lg-4">
            {{-- Bank Account Info (if invoice selected) --}}
            @if($invoice && $invoice->bankAccount)
                <div class="card card-flush mb-5">
                    <div class="card-header pt-5 bg-light-success">
                        <h3 class="card-title fw-bold text-success">
                            <i class="ki-outline ki-bank fs-2 me-2"></i>
                            Rekening Tujuan Transfer
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="symbol symbol-50px">
                                <div class="symbol-label bg-light-success text-success fw-bold fs-4">
                                    {{ strtoupper(substr($invoice->bankAccount->bank_name, 0, 2)) }}
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-900 fs-5">{{ $invoice->bankAccount->bank_name }}</div>
                                <div class="text-muted fs-7">Bank Tujuan</div>
                            </div>
                        </div>
                        <div class="p-4 rounded bg-light-success mb-3">
                            <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Nomor Rekening</div>
                            <div class="fw-bold text-gray-900 fs-3 font-monospace">{{ $invoice->bankAccount->account_number }}</div>
                        </div>
                        <div class="text-gray-600 fs-7 mb-3">
                            <i class="ki-outline ki-profile-user fs-7 me-1"></i>
                            Atas nama: <span class="fw-semibold text-gray-800">{{ $invoice->bankAccount->account_holder_name }}</span>
                        </div>
                        <div class="alert alert-info d-flex align-items-center p-3 mb-0">
                            <i class="ki-outline ki-information-5 fs-5 text-info me-2"></i>
                            <span class="text-gray-700 fs-8">Pastikan transfer ke rekening ini sesuai dengan jumlah yang Anda input.</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Panduan Pembayaran --}}
            <div class="card card-flush mb-5">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-outline ki-information-5 fs-2 text-primary me-2"></i>
                        Panduan Pembayaran
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-5 p-3 rounded bg-light-success">
                        <i class="ki-outline ki-check-circle fs-2 text-success mt-1"></i>
                        <div>
                            <div class="fw-bold text-gray-800 fs-6">Bayar Penuh</div>
                            <div class="text-muted fs-7">Invoice akan langsung lunas setelah disetujui Finance.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3 mb-5 p-3 rounded bg-light-warning">
                        <i class="ki-outline ki-abstract-26 fs-2 text-warning mt-1"></i>
                        <div>
                            <div class="fw-bold text-gray-800 fs-6">Bayar Sebagian</div>
                            <div class="text-muted fs-7">Invoice menjadi "Partial" — sisa tagihan bisa dibayar di pengajuan berikutnya.</div>
                        </div>
                    </div>
                    <div class="separator separator-dashed my-4"></div>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <i class="ki-outline ki-shield-tick fs-2 text-primary"></i>
                        <div class="text-muted fs-7">Setiap bukti bayar akan diverifikasi tim Finance Medikindo sebelum dicatat.</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="ki-outline ki-notification-on fs-2 text-info"></i>
                        <div class="text-muted fs-7">Status invoice akan otomatis terupdate setelah disetujui.</div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Pembayaran --}}
            <div class="card card-flush">
                <div class="card-header pt-4 pb-2 bg-light-primary">
                    <h3 class="card-title fw-bold fs-6 text-primary">Ringkasan Pembayaran</h3>
                </div>
                <div class="card-body pt-2 pb-4">
                    <div x-show="invoiceId === ''" class="text-center py-5">
                        <i class="ki-outline ki-file-down fs-3x text-gray-400 mb-3"></i>
                        <div class="text-muted fs-6">Pilih invoice untuk melihat ringkasan</div>
                    </div>
                    
                    <div x-show="invoiceId !== ''" x-cloak>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted fs-7">Jenis</span>
                            <span class="fw-bold fs-7">
                                <span x-show="paymentType === 'full'" class="badge badge-light-success">Bayar Penuh</span>
                                <span x-show="paymentType === 'partial'" class="badge badge-light-warning">Bayar Sebagian</span>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted fs-7">Tagihan Tersisa</span>
                            <span class="fw-bold fs-7 text-primary"
                                  x-text="'Rp ' + outstanding.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted fs-7">Jumlah Dibayar</span>
                            <span class="fw-bolder fs-6"
                                  :class="paymentType === 'full' ? 'text-success' : (isPartialValid ? 'text-warning' : 'text-danger')"
                                  x-text="amountFormatted"></span>
                        </div>
                        <div class="separator separator-dashed my-3"></div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted fs-7">Sisa Setelah Bayar</span>
                            <span class="fw-bold fs-7"
                                  :class="paymentType === 'full' ? 'text-success' : 'text-muted'"
                                  x-text="paymentType === 'full' ? 'Rp 0 (Lunas)' : (isPartialValid ? 'Rp ' + (outstanding - parseFloat(partialAmount || 0)).toLocaleString('id-ID') : '-')">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
