<x-layout>
    <x-page-header :title="'Detail Bukti Pembayaran #' . $paymentProof->id" :breadcrumbs="$breadcrumbs">
        @can('verify', $paymentProof)
            @if($paymentProof->canBeVerified())
                <x-button :href="route('web.payment-proofs.verify', $paymentProof)" icon="shield-search" label="Setujui Pembayaran" color="primary" />
                <x-button :href="route('web.payment-proofs.reject', $paymentProof)" icon="cross-circle" label="Tolak Bukti Bayar" color="danger" />
            @endif
        @endcan
    </x-page-header>

    <div class="row g-5 g-xl-10">
        <div class="col-lg-8">
            <x-card title="Informasi Pembayaran" icon="bill">
                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">Invoice Pelanggan</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">{{ $paymentProof->customerInvoice->invoice_number }}</span>
                        <a href="{{ route('web.invoices.show-customer', $paymentProof->customerInvoice) }}" class="ms-2">
                            <i class="ki-outline ki-eye fs-4"></i>
                        </a>
                    </div>
                </div>

                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">RS / Pelanggan</label>
                    <div class="col-lg-8 fv-row">
                        <span class="fw-bold fs-6 text-gray-800">{{ $paymentProof->customerInvoice->organization->name }}</span>
                    </div>
                </div>

                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">Jumlah Dibayarkan</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">Tanggal Bayar</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">{{ $paymentProof->payment_date->format('d F Y') }}</span>
                    </div>
                </div>

                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">Referensi Bank</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">{{ $paymentProof->bank_reference ?? '-' }}</span>
                    </div>
                </div>

                <div class="row mb-10">
                    <label class="col-lg-4 fw-semibold text-muted">Catatan</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">{{ $paymentProof->notes ?? '-' }}</span>
                    </div>
                </div>

                <div class="separator separator-dashed my-10"></div>

                <h3 class="mb-5">Dokumen Pendukung</h3>
                <div class="row g-3">
                    @forelse($paymentProof->paymentDocuments as $doc)
                        <div class="col-md-6">
                            <div class="border border-dashed border-gray-300 rounded px-7 py-3 mb-5">
                                <div class="d-flex flex-stack">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-outline ki-file-up fs-2x text-primary me-4"></i>
                                        <div class="ms-1">
                                            <a href="{{ route('web.payment-proofs.download-document', [$paymentProof, $doc->id]) }}" class="fs-6 text-gray-800 text-hover-primary fw-bold">{{ $doc->original_filename }}</a>
                                            <div class="fs-7 text-muted fw-semibold">{{ number_format($doc->file_size / 1024, 2) }} KB</div>
                                        </div>
                                    </div>
                                    <a href="{{ route('web.payment-proofs.download-document', [$paymentProof, $doc->id]) }}" class="btn btn-sm btn-icon btn-light-primary">
                                        <i class="ki-outline ki-cloud-download fs-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted italic">Tidak ada dokumen yang diunggah.</div>
                    @endforelse
                </div>
            </x-card>

            @if($paymentProof->status === App\Enums\PaymentProofStatus::REJECTED)
                <div class="card card-flush border-danger border border-dashed mt-5">
                    <div class="card-body">
                        <h4 class="text-danger mb-4">Alasan Penolakan</h4>
                        <p class="mb-0">{{ $paymentProof->rejection_reason }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <x-card title="Status & Audit" icon="history">
                <div class="d-flex flex-column gap-7">
                    <div class="d-flex flex-column">
                        <span class="text-muted fw-bold mb-1">Status Saat Ini</span>
                        <div>
                            <x-badge :color="$paymentProof->status->color()" :label="$paymentProof->status->label()" />
                        </div>
                    </div>

                    <div class="d-flex flex-column">
                        <span class="text-muted fw-bold mb-1">Submitted Oleh</span>
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-3">
                                <span class="symbol-label bg-light-primary text-primary fw-bold">{{ substr($paymentProof->submittedBy->name, 0, 1) }}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-7">{{ $paymentProof->submittedBy->name }}</span>
                                <span class="text-muted fs-7">{{ $paymentProof->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    @if($paymentProof->verifiedBy)
                    <div class="d-flex flex-column">
                        <span class="text-muted fw-bold mb-1">Diverifikasi Oleh</span>
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-3">
                                <span class="symbol-label bg-light-info text-info fw-bold">{{ substr($paymentProof->verifiedBy->name, 0, 1) }}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-7">{{ $paymentProof->verifiedBy->name }}</span>
                                <span class="text-muted fs-7">{{ $paymentProof->verified_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($paymentProof->approvedBy)
                    <div class="d-flex flex-column">
                        <span class="text-muted fw-bold mb-1">Disetujui Oleh</span>
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-3">
                                <span class="symbol-label bg-light-success text-success fw-bold">{{ substr($paymentProof->approvedBy->name, 0, 1) }}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-7">{{ $paymentProof->approvedBy->name }}</span>
                                <span class="text-muted fs-7">{{ $paymentProof->approved_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>

            @can('uploadDocument', $paymentProof)
            <div class="card card-flush shadow-sm mt-5">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Tambah Dokumen</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('web.payment-proofs.upload-document', $paymentProof) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-5">
                            <input type="file" name="file" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Upload Dokumen</button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</x-layout>
