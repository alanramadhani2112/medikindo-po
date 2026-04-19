<x-layout>
    <x-page-header title="Persetujuan Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10">
        <div class="col-lg-8">
            <x-card title="Verifikasi Pembayaran" icon="shield-search">
                <div class="alert alert-info d-flex align-items-center mb-10">
                    <i class="ki-outline ki-information-5 fs-2x text-info me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold fs-6">Instruksi Finance</span>
                        <span>Silakan periksa dokumen bukti transfer dan pastikan jumlah yang masuk ke bank sesuai dengan data di bawah ini.</span>
                    </div>
                </div>

                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">No. Invoice</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">{{ $paymentProof->customerInvoice->invoice_number }}</span>
                    </div>
                </div>

                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">Jumlah Bayar</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-success">Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="row mb-10">
                    <label class="col-lg-4 fw-semibold text-muted">Dokumen</label>
                    <div class="col-lg-8">
                        @foreach($paymentProof->paymentDocuments as $doc)
                            <div class="d-flex align-items-center mb-2">
                                <i class="ki-outline ki-file-up fs-2 text-primary me-2"></i>
                                <a href="{{ route('web.payment-proofs.download-document', [$paymentProof, $doc->id]) }}" target="_blank" class="text-primary fw-bold">{{ $doc->original_filename }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <form action="{{ route('web.payment-proofs.process-approval', $paymentProof) }}" method="POST">
                    @csrf
                    <div class="mb-10">
                        <label class="form-label fw-bold">Catatan Verifikasi (Internal)</label>
                        <textarea name="approval_notes" class="form-control form-control-solid" rows="3" placeholder="Contoh: Dana sudah masuk ke Rek BCA Medikindo. Valid."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('web.payment-proofs.show', $paymentProof) }}" class="btn btn-light">Kembali</a>
                        <button type="submit" class="btn btn-success">
                            <i class="ki-outline ki-check-circle fs-2"></i>
                            Setujui & Verifikasi Pembayaran
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layout>
