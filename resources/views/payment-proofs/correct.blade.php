<x-layout>
    <x-page-header title="Koreksi Bukti Pembayaran #{{ $paymentProof->id }}" :breadcrumbs="$breadcrumbs">
        <x-slot name="actions">
            <x-button :href="route('web.payment-proofs.show', $paymentProof)" icon="arrow-left" label="Kembali" color="secondary" />
        </x-slot>
    </x-page-header>

    <div class="row g-5 g-xl-10">
        {{-- Warning banner --}}
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center gap-4 p-5">
                <i class="ki-outline ki-shield-cross fs-2x text-danger flex-shrink-0"></i>
                <div>
                    <h5 class="text-danger mb-1">⚠ Operasi Berisiko Tinggi — Hanya Super Admin</h5>
                    <div class="fs-7 text-muted">
                        Koreksi akan <strong>membatalkan</strong> pencatatan Payment IN yang sudah ada, mengurangi
                        <code>paid_amount</code> di invoice, dan membuat pengajuan baru yang wajib di-approve ulang oleh Finance.
                        Seluruh tindakan ini dicatat dalam audit log.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <x-card title="Form Koreksi Pembayaran" icon="pencil">
                <form action="{{ route('web.payment-proofs.process-correction', $paymentProof) }}" method="POST">
                    @csrf

                    {{-- Reason (mandatory, min 20 chars for audit purposes) --}}
                    <div class="mb-7">
                        <label class="required form-label fw-bold">Alasan Koreksi (Untuk Keperluan Audit)</label>
                        <textarea name="correction_reason" class="form-control form-control-solid" rows="3"
                                  minlength="20" required
                                  placeholder="Jelaskan secara rinci mengapa pembayaran ini perlu dikoreksi (minimal 20 karakter)...">{{ old('correction_reason') }}</textarea>
                        @error('correction_reason')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-muted fs-7 mt-1">Minimal 20 karakter. Contoh: "Nominal yang diterima di rekening adalah Rp 4.500.000 bukan Rp 5.000.000 berdasarkan mutasi bank tanggal ..."</div>
                    </div>

                    <div class="separator separator-dashed my-6"></div>
                    <div class="fs-6 fw-bold text-gray-800 mb-5">Data Koreksi (Nilai Baru)</div>

                    {{-- Corrected Payment Type --}}
                    <div class="mb-7">
                        <label class="required form-label fw-bold">Jenis Pembayaran (Koreksi)</label>
                        <select name="corrected_payment_type" class="form-select form-select-solid" required>
                            <option value="full" {{ old('corrected_payment_type', $paymentProof->payment_type) === 'full' ? 'selected' : '' }}>
                                Bayar Penuh (Pelunasan)
                            </option>
                            <option value="partial" {{ old('corrected_payment_type', $paymentProof->payment_type) === 'partial' ? 'selected' : '' }}>
                                Bayar Sebagian (Cicilan)
                            </option>
                        </select>
                    </div>

                    <div class="row mb-7">
                        <div class="col-md-6">
                            <label class="required form-label fw-bold">Nominal Koreksi (Rp)</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text fw-bold">Rp</span>
                                <input type="number" name="corrected_amount" class="form-control form-control-solid"
                                       value="{{ old('corrected_amount', $paymentProof->amount) }}"
                                       min="0.01" step="0.01" required>
                            </div>
                            @error('corrected_amount')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label fw-bold">Tanggal Pembayaran (Koreksi)</label>
                            <input type="date" name="corrected_payment_date" class="form-control form-control-solid"
                                   value="{{ old('corrected_payment_date', $paymentProof->payment_date->format('Y-m-d')) }}"
                                   required>
                            @error('corrected_payment_date')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-7">
                        <label class="form-label fw-bold">Referensi Bank / No. Reff (Opsional)</label>
                        <input type="text" name="bank_reference" class="form-control form-control-solid"
                               value="{{ old('bank_reference', $paymentProof->bank_reference) }}"
                               placeholder="TRX-...">
                    </div>

                    <div class="mb-7">
                        <label class="form-label fw-bold">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" class="form-control form-control-solid" rows="2"
                                  placeholder="Informasi tambahan...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                        <a href="{{ route('web.payment-proofs.show', $paymentProof) }}" class="btn btn-light">
                            <i class="ki-outline ki-arrow-left fs-4"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-danger submit-confirm"
                                data-title="⚠️ Konfirmasi Koreksi Pembayaran"
                                data-message="Tindakan ini akan <strong>membatalkan</strong> bukti bayar yang sudah disetujui dan membuat pengajuan baru. Tindakan ini <strong>tidak dapat dibatalkan</strong> dan akan merevisi pencatatan keuangan."
                                data-confirm-text="Ya, Proses Koreksi!">
                            <i class="ki-outline ki-check fs-4 me-1"></i> Proses Koreksi
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <div class="col-lg-5">
            {{-- Original Proof Summary --}}
            <x-card title="Data Asli (Sebelum Koreksi)" icon="document">
                <div class="d-flex flex-column gap-4">
                    <div>
                        <div class="text-muted fs-7 mb-1">Invoice</div>
                        <div class="fw-bold">{{ $paymentProof->customerInvoice?->invoice_number }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-7 mb-1">RS / Pelanggan</div>
                        <div class="fw-bold">{{ $paymentProof->customerInvoice?->organization?->name }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-7 mb-1">Nominal (Asli)</div>
                        <div class="fw-bold text-danger fs-4">
                            Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-muted fs-7 mb-1">Jenis Pembayaran</div>
                        <div>
                            @if ($paymentProof->payment_type === 'full')
                                <span class="badge badge-light-success">Bayar Penuh</span>
                            @else
                                <span class="badge badge-light-warning">Bayar Sebagian</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-muted fs-7 mb-1">Tanggal Bayar</div>
                        <div class="fw-bold">{{ $paymentProof->payment_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-7 mb-1">Diajukan Oleh</div>
                        <div class="fw-bold">{{ $paymentProof->submittedBy?->name }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-7 mb-1">Referensi Bank</div>
                        <div class="fw-bold">{{ $paymentProof->bank_reference ?? '-' }}</div>
                    </div>
                </div>

                <div class="separator separator-dashed my-5"></div>

                <div class="bg-light-danger rounded p-4">
                    <div class="fs-7 fw-bold text-danger mb-2">Efek Koreksi:</div>
                    <ul class="text-muted fs-7 mb-0 ps-4">
                        <li>Bukti bayar #{{ $paymentProof->id }} → status <strong>Ditarik</strong></li>
                        <li>Invoice <code>paid_amount</code> dikurangi <strong>Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}</strong></li>
                        <li>Pengajuan baru dibuat → wajib di-approve Finance</li>
                        <li>Semua tindakan dicatat di Audit Log</li>
                    </ul>
                </div>
            </x-card>
        </div>
    </div>
</x-layout>
