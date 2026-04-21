<x-layout>
    <x-page-header title="Ajukan Ulang Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10">
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

                    {{-- Invoice info (read-only) --}}
                    <div class="mb-6">
                        <label class="form-label fw-bold">Invoice</label>
                        <div class="form-control bg-light">
                            {{ $paymentProof->customerInvoice->invoice_number }}
                            — {{ $paymentProof->customerInvoice->organization->name }}
                            (Sisa: Rp {{ number_format($paymentProof->customerInvoice->total_amount - $paymentProof->customerInvoice->paid_amount, 0, ',', '.') }})
                        </div>
                    </div>

                    {{-- Tanggal Pembayaran --}}
                    <div class="mb-6">
                        <label class="form-label fw-bold required">Tanggal Pembayaran</label>
                        <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date', $paymentProof->payment_date?->format('Y-m-d')) }}" required>
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div class="mb-6">
                        <label class="form-label fw-bold required">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            @foreach(['Bank Transfer', 'Virtual Account', 'Giro/Cek', 'Tunai'] as $method)
                                <option value="{{ $method }}" {{ old('payment_method', $paymentProof->payment_method) === $method ? 'selected' : '' }}>
                                    {{ $method }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Bank Pengirim --}}
                    <div class="mb-6">
                        <label class="form-label fw-bold">Bank Pengirim</label>
                        <input type="text" name="sender_bank_name" class="form-control"
                               value="{{ old('sender_bank_name', $paymentProof->sender_bank_name) }}"
                               placeholder="Contoh: BCA, Mandiri, BNI">
                    </div>

                    {{-- Nomor Referensi --}}
                    <div class="mb-6">
                        <label class="form-label fw-bold">Nomor Referensi / Bukti Transfer</label>
                        <input type="text" name="bank_reference" class="form-control"
                               value="{{ old('bank_reference', $paymentProof->bank_reference) }}"
                               placeholder="Nomor referensi transfer">
                    </div>

                    {{-- Upload Bukti Baru --}}
                    <div class="mb-6">
                        <label class="form-label fw-bold">Upload Bukti Pembayaran Baru</label>
                        <input type="file" name="payment_proof_file"
                               class="form-control @error('payment_proof_file') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text text-muted">Format: JPG, PNG, PDF. Maks 5MB.</div>
                        @error('payment_proof_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Keterangan Perbaikan (wajib) --}}
                    <div class="mb-6">
                        <label class="form-label fw-bold required">Keterangan Perbaikan</label>
                        <textarea name="resubmission_notes" rows="4"
                                  class="form-control @error('resubmission_notes') is-invalid @enderror"
                                  placeholder="Jelaskan apa yang diperbaiki dari pengajuan sebelumnya..."
                                  required>{{ old('resubmission_notes') }}</textarea>
                        <div class="form-text text-muted">Wajib diisi. Jelaskan perbaikan yang dilakukan.</div>
                        @error('resubmission_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="ki-outline ki-arrows-circle fs-4 me-1"></i>
                            Ajukan Ulang
                        </button>
                        <a href="{{ route('web.payment-proofs.show', $paymentProof) }}" class="btn btn-light">
                            Batal
                        </a>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Sidebar: data asli --}}
        <div class="col-lg-4">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold fs-5">Data Pengajuan Sebelumnya</h3>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <span class="text-muted fs-7">Jumlah</span>
                            <div class="fw-bold">Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7">Tanggal Bayar</span>
                            <div class="fw-bold">{{ $paymentProof->payment_date?->format('d M Y') }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7">Metode</span>
                            <div class="fw-bold">{{ $paymentProof->payment_method ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-muted fs-7">Referensi</span>
                            <div class="fw-bold">{{ $paymentProof->bank_reference ?? '-' }}</div>
                        </div>
                        @if($paymentProof->paymentDocuments->count() > 0)
                        <div>
                            <span class="text-muted fs-7">Dokumen Sebelumnya</span>
                            @foreach($paymentProof->paymentDocuments as $doc)
                                <div class="mt-1">
                                    <a href="{{ route('web.payment-proofs.view-document', [$paymentProof, $doc]) }}"
                                       target="_blank" class="text-primary fs-7">
                                        <i class="ki-outline ki-file fs-6 me-1"></i>{{ $doc->original_filename }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
