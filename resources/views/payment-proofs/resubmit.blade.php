<x-layout>
    <x-page-header title="Ajukan Ulang Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10" x-data="{
        paymentMethod: '{{ old('payment_method', $paymentProof->payment_method) }}',
        resubmissionNotes: '{{ old('resubmission_notes') }}',

        get showBankDropdown() {
            return ['Bank Transfer', 'Virtual Account'].includes(this.paymentMethod);
        },

        get showGiroFields() {
            return this.paymentMethod === 'Giro/Cek';
        },

        get wordCount() {
            return this.resubmissionNotes.trim().split(/\s+/).filter(w => w.length > 0).length;
        },

        get isNotesValid() {
            return this.wordCount >= 10;
        }
    }">
        <div class="col-lg-8">

            {{-- Info: alasan penolakan --}}
            <div class="card card-flush border-danger border border-dashed mb-5">
                <div class="card-body py-5">
                    <div class="d-flex align-items-start gap-3">
                        <i class="ki-outline ki-cross-circle fs-2x text-danger mt-1"></i>
                        <div>
                            <h5 class="text-danger mb-1">Alasan Penolakan Sebelumnya</h5>
                            <p class="text-gray-700 mb-0">{{ $paymentProof->rejection_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <x-card title="Form Pengajuan Ulang" icon="arrows-circle">
                <form action="{{ route('web.payment-proofs.process-resubmit', $paymentProof) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- 1. Invoice info (read-only) --}}
                    <div class="mb-8">
                        <label class="form-label fw-bold">Invoice Pelanggan</label>
                        <div class="form-control form-control-solid bg-light">
                            {{ $paymentProof->customerInvoice->invoice_number }}
                            — {{ $paymentProof->customerInvoice->organization->name }}
                            (Sisa: Rp {{ number_format($paymentProof->customerInvoice->total_amount - $paymentProof->customerInvoice->paid_amount, 0, ',', '.') }})
                        </div>
                    </div>

                    {{-- 2. Jumlah Pembayaran (read-only) --}}
                    <div class="mb-8">
                        <label class="form-label fw-bold">Jumlah Pembayaran</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light">Rp</span>
                            <input type="text" class="form-control form-control-solid bg-light" 
                                   value="{{ number_format($paymentProof->amount, 0, ',', '.') }}" readonly>
                        </div>
                        <div class="form-text text-muted">
                            <i class="ki-outline ki-information-4 fs-7"></i>
                            Jumlah tidak dapat diubah saat resubmit
                        </div>
                    </div>

                    {{-- 3. Tanggal Pembayaran --}}
                    <div class="mb-8">
                        <label class="required form-label fw-bold">Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" class="form-control form-control-solid @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date', $paymentProof->payment_date?->format('Y-m-d')) }}" required>
                        @error('payment_date')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 4. Metode Pembayaran --}}
                    <div class="mb-8">
                        <label class="required form-label fw-bold">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select form-select-solid @error('payment_method') is-invalid @enderror"
                                x-model="paymentMethod" required>
                            <option value="">— Pilih Metode Pembayaran —</option>
                            <option value="Bank Transfer">🏦 Bank Transfer</option>
                            <option value="Virtual Account">💳 Virtual Account</option>
                            <option value="Giro/Cek">📄 Giro/Cek</option>
                            <option value="Cash">💵 Cash (Tunai)</option>
                        </select>
                        @error('payment_method')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 5. Detail Rekening (Conditional) --}}
                    
                    {{-- 5a. Bank Transfer / Virtual Account → Pilih Bank + No. Rekening --}}
                    <div :style="!showBankDropdown ? 'display: none;' : ''">
                        <div class="mb-8">
                            <label class="form-label fw-bold" :class="showBankDropdown ? 'required' : ''">Nama Bank</label>
                            <select name="sender_bank_name" class="form-select form-select-solid" 
                                    :disabled="!showBankDropdown">
                                <option value="">— Pilih Bank —</option>
                                @foreach(\Database\Seeders\IndonesianBankSeeder::$BANKS as $bank)
                                    <option value="{{ $bank['name'] }}" @selected(old('sender_bank_name', $paymentProof->sender_bank_name) === $bank['name'])>
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
                                   value="{{ old('sender_account_number', $paymentProof->sender_account_number) }}"
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
                    <div :style="!showGiroFields ? 'display: none;' : ''">
                        <div class="row mb-8">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" :class="showGiroFields ? 'required' : ''">Nomor Giro/Cek</label>
                                <input type="text" name="giro_number" class="form-control form-control-solid"
                                       placeholder="Contoh: GR-12345678"
                                       value="{{ old('giro_number', $paymentProof->giro_number) }}"
                                       :disabled="!showGiroFields">
                                <div class="form-text text-muted">Nomor seri giro atau cek</div>
                                @error('giro_number')
                                    <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" :class="showGiroFields ? 'required' : ''">Tanggal Jatuh Tempo</label>
                                <input type="date" name="giro_due_date" class="form-control form-control-solid"
                                       value="{{ old('giro_due_date', $paymentProof->giro_due_date?->format('Y-m-d')) }}"
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
                                    <option value="{{ $bank['name'] }}" @selected(old('sender_bank_name', $paymentProof->sender_bank_name) === $bank['name'])>
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
                               value="{{ old('bank_reference', $paymentProof->bank_reference) }}">
                        <div class="form-text text-muted">
                            <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">Nomor referensi dari slip transfer bank</span>
                            <span x-show="paymentMethod === 'Cash'">Nomor kwitansi pembayaran tunai</span>
                            <span x-show="paymentMethod === 'Giro/Cek'">Nomor referensi transaksi (opsional)</span>
                        </div>
                        @error('bank_reference')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 7. Upload Bukti Baru (WAJIB) --}}
                    <div class="mb-8">
                        <label class="required form-label fw-bold">
                            <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">Upload Bukti Transfer Baru</span>
                            <span x-show="paymentMethod === 'Cash'">Upload Kwitansi Baru</span>
                            <span x-show="paymentMethod === 'Giro/Cek'">Upload Foto Giro/Cek Baru</span>
                            <span x-show="paymentMethod === ''">Upload Bukti Pembayaran Baru</span>
                            <span class="badge badge-light-danger ms-2">Wajib</span>
                        </label>
                        <input type="file" name="file" class="form-control form-control-solid @error('file') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.pdf" required>
                        @error('file')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-muted fs-7 mt-2">
                            <i class="ki-outline ki-information-4 fs-6"></i>
                            Format: JPG, PNG, PDF. Maks 5MB. Upload bukti pembayaran baru wajib dilakukan.
                        </div>
                    </div>

                    {{-- 8. Keterangan Perbaikan (WAJIB - Min 10 kata) --}}
                    <div class="mb-10">
                        <label class="required form-label fw-bold">
                            Keterangan Perbaikan
                            <span class="badge badge-light-danger ms-2">Wajib - Min 10 Kata</span>
                        </label>
                        <textarea name="resubmission_notes" rows="5"
                                  class="form-control form-control-solid @error('resubmission_notes') is-invalid @enderror"
                                  placeholder="Jelaskan secara detail apa yang diperbaiki dari pengajuan sebelumnya. Minimal 10 kata..."
                                  x-model="resubmissionNotes"
                                  required>{{ old('resubmission_notes') }}</textarea>
                        
                        {{-- Word Counter --}}
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <div class="fs-7"
                                 :class="isNotesValid ? 'text-success' : 'text-danger'">
                                <span x-show="!isNotesValid">
                                    <i class="ki-outline ki-information-5 fs-6"></i>
                                    Minimal 10 kata diperlukan
                                </span>
                                <span x-show="isNotesValid">
                                    <i class="ki-outline ki-check-circle fs-6"></i>
                                    Keterangan sudah memenuhi syarat
                                </span>
                            </div>
                            <div class="badge fs-7"
                                 :class="isNotesValid ? 'badge-light-success' : 'badge-light-danger'">
                                <span x-text="wordCount"></span> kata
                            </div>
                        </div>
                        
                        @error('resubmission_notes')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-muted fs-7 mt-2">
                            <i class="ki-outline ki-information-4 fs-6"></i>
                            Jelaskan perbaikan yang dilakukan: perubahan data, dokumen baru, atau koreksi lainnya.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                        <a href="{{ route('web.payment-proofs.show', $paymentProof) }}" class="btn btn-light">
                            <i class="ki-outline ki-arrow-left fs-2"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-warning"
                                :disabled="!isNotesValid || paymentMethod === ''">
                            <i class="ki-outline ki-arrows-circle fs-2"></i>
                            Ajukan Ulang
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Sidebar: data asli --}}
        <div class="col-lg-4">
            <div class="card card-flush mb-5">
                <div class="card-header pt-5 bg-light-warning">
                    <h3 class="card-title fw-bold text-warning">
                        <i class="ki-outline ki-information-5 fs-2 me-2"></i>
                        Data Pengajuan Sebelumnya
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex flex-column gap-4">
                        <div>
                            <span class="text-muted fs-7">Jumlah</span>
                            <div class="fw-bold fs-5 text-gray-900">Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7">Tanggal Bayar</span>
                            <div class="fw-bold text-gray-800">{{ $paymentProof->payment_date?->format('d M Y') }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7">Metode</span>
                            <div class="fw-bold text-gray-800">{{ $paymentProof->payment_method ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7">Bank</span>
                            <div class="fw-bold text-gray-800">{{ $paymentProof->sender_bank_name ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7">No. Rekening</span>
                            <div class="fw-bold text-gray-800">{{ $paymentProof->sender_account_number ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7">Referensi</span>
                            <div class="fw-bold text-gray-800">{{ $paymentProof->bank_reference ?? '-' }}</div>
                        </div>
                        @if($paymentProof->paymentDocuments->count() > 0)
                        <div class="separator separator-dashed my-2"></div>
                        <div>
                            <span class="text-muted fs-7 mb-2 d-block">Dokumen Sebelumnya</span>
                            @foreach($paymentProof->paymentDocuments as $doc)
                                <div class="d-flex align-items-center gap-2 p-2 rounded bg-light-primary mb-2">
                                    <i class="ki-outline ki-file fs-2 text-primary"></i>
                                    <a href="{{ route('web.payment-proofs.view-document', [$paymentProof, $doc]) }}"
                                       target="_blank" class="text-primary fs-7 fw-semibold text-hover-primary">
                                        {{ $doc->original_filename }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Panduan Resubmit --}}
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-outline ki-information-5 fs-2 text-primary me-2"></i>
                        Panduan Pengajuan Ulang
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <i class="ki-outline ki-check-circle fs-2 text-success mt-1"></i>
                        <div class="text-muted fs-7">Perbaiki data yang salah sesuai alasan penolakan di atas.</div>
                    </div>
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <i class="ki-outline ki-document fs-2 text-warning mt-1"></i>
                        <div class="text-muted fs-7">Upload dokumen baru jika dokumen sebelumnya tidak jelas atau salah.</div>
                    </div>
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <i class="ki-outline ki-message-text-2 fs-2 text-info mt-1"></i>
                        <div class="text-muted fs-7">Jelaskan perbaikan dengan detail minimal 10 kata agar Finance dapat memahami perubahan.</div>
                    </div>
                    <div class="separator separator-dashed my-4"></div>
                    <div class="alert alert-warning d-flex align-items-center p-3 mb-0">
                        <i class="ki-outline ki-information-5 fs-5 text-warning me-2"></i>
                        <span class="text-gray-700 fs-8">Pengajuan ulang akan direview kembali oleh tim Finance.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
