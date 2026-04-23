<x-layout>
    <x-page-header title="Tolak Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10">
        <div class="col-lg-8">
            <x-card title="Penolakan Pembayaran" icon="cross-circle">
                <div class="alert alert-danger d-flex align-items-center mb-10">
                    <i class="ki-outline ki-information-5 fs-2x text-danger me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold fs-6">Peringatan Penolakan</span>
                        <span>Penolakan bukti bayar akan dikirimkan kepada pengirim. Mohon berikan alasan yang jelas agar pengirim dapat melakukan koreksi.</span>
                    </div>
                </div>

                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">No. Invoice</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">{{ $paymentProof->customerInvoice->invoice_number }}</span>
                    </div>
                </div>

                <div class="row mb-10">
                    <label class="col-lg-4 fw-semibold text-muted">Jumlah Bayar</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <form action="{{ route('web.payment-proofs.process-rejection', $paymentProof) }}" method="POST">
                    @csrf
                    <div class="mb-10">
                        <label class="required form-label fw-bold">Alasan Penolakan</label>
                        <textarea name="approval_notes" class="form-control form-control-solid" rows="4" placeholder="Contoh: Bukti transfer tidak terbaca (blur). Mohon upload ulang." required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('web.payment-proofs.show', $paymentProof) }}" class="btn btn-light">Kembali</a>
                        <button type="submit" class="btn btn-danger submit-confirm"
                                data-title="Konfirmasi Tolak Bukti Bayar"
                                data-message="Apakah Anda yakin ingin <strong>menolak</strong> bukti pembayaran ini? Pengirim akan diberitahu dan dapat mengajukan ulang."
                                data-confirm-text="Ya, Tolak!">
                            <i class="ki-outline ki-cross fs-2"></i>
                            Tolak Bukti Bayar
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layout>
