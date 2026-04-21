<x-layout>
    <x-page-header :title="'Detail Bukti Pembayaran #' . $paymentProof->id" :breadcrumbs="$breadcrumbs">
        <x-slot name="actions">
            @can('verify', $paymentProof)
                @if ($paymentProof->canBeVerified() || $paymentProof->isResubmitted())
                    <x-button :href="route('web.payment-proofs.verify', $paymentProof)" icon="shield-search" label="Verifikasi Pembayaran" color="primary" />
                @endif
            @endcan
            @can('reject', $paymentProof)
                @if ($paymentProof->canBeApproved() || $paymentProof->canBeVerified() || $paymentProof->isResubmitted())
                    <x-button :href="route('web.payment-proofs.reject', $paymentProof)" icon="cross-circle" label="Tolak Bukti Bayar" color="danger" />
                @endif
            @endcan
        </x-slot>
    </x-page-header>

    <div class="row g-5 g-xl-10">
        <div class="col-lg-8">

            {{-- Alert: Bukti Ditolak (tampil di atas) --}}
            @if ($paymentProof->status === App\Enums\PaymentProofStatus::REJECTED)
                <div class="card card-flush border-danger border border-dashed mb-5">
                    <div class="card-body">
                        <h4 class="text-danger mb-3">
                            <i class="ki-outline ki-cross-circle fs-3 text-danger me-2"></i>
                            Bukti Pembayaran Ditolak
                        </h4>
                        <p class="text-gray-700 mb-4"><strong>Alasan:</strong> {{ $paymentProof->rejection_reason }}</p>

                        @can('resubmit', $paymentProof)
                            @if($paymentProof->resubmissions()->count() === 0)
                                <a href="{{ route('web.payment-proofs.resubmit', $paymentProof) }}"
                                   class="btn btn-warning">
                                    <i class="ki-outline ki-arrows-circle fs-4 me-1"></i>
                                    Ajukan Ulang Bukti Pembayaran
                                </a>
                            @else
                                <div class="alert alert-info d-flex align-items-center p-4 mb-0">
                                    <i class="ki-outline ki-information-5 fs-2 text-info me-3"></i>
                                    <span>Bukti pembayaran ulang sudah diajukan.
                                        <a href="{{ route('web.payment-proofs.show', $paymentProof->resubmissions()->latest()->first()) }}" class="fw-bold">
                                            Lihat pengajuan ulang →
                                        </a>
                                    </span>
                                </div>
                            @endif
                        @endcan
                    </div>
                </div>
            @endif

            {{-- Info: ini adalah resubmission dari proof yang ditolak --}}
            @if ($paymentProof->resubmission_of_id)
                <div class="card card-flush border-warning border border-dashed mb-5">
                    <div class="card-body">
                        <h4 class="text-warning mb-2">
                            <i class="ki-outline ki-arrows-circle fs-3 text-warning me-2"></i>
                            Pengajuan Ulang
                        </h4>
                        <p class="text-gray-700 mb-2">
                            Ini adalah pengajuan ulang dari
                            <a href="{{ route('web.payment-proofs.show', $paymentProof->resubmission_of_id) }}" class="fw-bold">
                                Bukti #{{ $paymentProof->resubmission_of_id }}
                            </a>
                            yang sebelumnya ditolak.
                        </p>
                        @if($paymentProof->resubmission_notes)
                            <p class="text-gray-700 mb-0"><strong>Keterangan perbaikan:</strong> {{ $paymentProof->resubmission_notes }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <x-card title="Informasi Pembayaran" icon="bill">
                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">Invoice Pelanggan</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">{{ $paymentProof->customerInvoice->invoice_number }}</span>
                        <a href="{{ route('web.invoices.customer.show', $paymentProof->customerInvoice) }}" class="ms-2">
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
                    <label class="col-lg-4 fw-semibold text-muted">Jenis Pembayaran</label>
                    <div class="col-lg-8">
                        @if ($paymentProof->payment_type === 'full')
                            <span class="badge badge-light-success fw-bold fs-7">
                                <i class="ki-outline ki-check-circle fs-7 me-1"></i> Bayar Penuh (Pelunasan)
                            </span>
                        @else
                            <span class="badge badge-light-warning fw-bold fs-7">
                                <i class="ki-outline ki-abstract-26 fs-7 me-1"></i> Bayar Sebagian (Cicilan)
                            </span>
                        @endif
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

                <h3 class="mb-5">
                    <i class="ki-outline ki-picture fs-3 text-primary me-2"></i>
                    Bukti Pembayaran
                    @if ($relatedProofs->count() > 1)
                        <span class="badge badge-sm badge-light-primary ms-2">{{ $relatedProofs->count() }} pembayaran</span>
                    @endif
                </h3>

                {{-- Unified: show ALL proofs for this invoice --}}
                <div class="d-flex flex-column gap-4">
                    @foreach ($relatedProofs as $proof)
                        @php
                            $isCurrent = $proof->id === $paymentProof->id;
                        @endphp
                        <div class="border rounded p-4 {{ $isCurrent ? 'border-primary border-start border-start-3' : 'border-dashed border-gray-300' }}">
                            {{-- Header row: proof info + amount + status --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    @if ($proof->status === App\Enums\PaymentProofStatus::APPROVED)
                                        <div class="symbol symbol-35px symbol-circle">
                                            <span class="symbol-label bg-light-success"><i class="ki-outline ki-check-circle fs-3 text-success"></i></span>
                                        </div>
                                    @elseif ($proof->status === App\Enums\PaymentProofStatus::SUBMITTED)
                                        <div class="symbol symbol-35px symbol-circle">
                                            <span class="symbol-label bg-light-primary"><i class="ki-outline ki-time fs-3 text-primary"></i></span>
                                        </div>
                                    @elseif ($proof->status === App\Enums\PaymentProofStatus::REJECTED)
                                        <div class="symbol symbol-35px symbol-circle">
                                            <span class="symbol-label bg-light-danger"><i class="ki-outline ki-cross-circle fs-3 text-danger"></i></span>
                                        </div>
                                    @else
                                        <div class="symbol symbol-35px symbol-circle">
                                            <span class="symbol-label bg-light-warning"><i class="ki-outline ki-abstract-26 fs-3 text-warning"></i></span>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('web.payment-proofs.show', $proof) }}"
                                           class="fs-6 fw-bold text-gray-800 text-hover-primary">
                                            Pembayaran {{ $loop->iteration }}
                                            @if ($isCurrent)
                                                <span class="badge badge-sm badge-primary ms-1">Sedang Dilihat</span>
                                            @endif
                                        </a>
                                        <div class="text-muted fs-7">
                                            {{ $proof->payment_type === 'full' ? 'Bayar Penuh' : 'Bayar Sebagian' }}
                                            &middot; {{ $proof->created_at->format('d M Y H:i') }}
                                            &middot; {{ $proof->submittedBy?->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bolder fs-5">Rp {{ number_format($proof->amount, 0, ',', '.') }}</div>
                                    <x-badge :variant="$proof->status->color()">{{ $proof->status->label() }}</x-badge>
                                </div>
                            </div>

                            {{-- Documents for this proof --}}
                            @if ($proof->paymentDocuments->isNotEmpty())
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($proof->paymentDocuments as $doc)
                                        <div class="d-flex align-items-center border border-dashed border-gray-300 rounded px-4 py-3 flex-grow-1" style="min-width: 250px;">
                                            @if (in_array($doc->mime_type, ['image/jpeg', 'image/png', 'image/jpg']))
                                                <i class="ki-outline ki-picture fs-2x text-primary me-3"></i>
                                            @else
                                                <i class="ki-outline ki-file fs-2x text-danger me-3"></i>
                                            @endif
                                            <div class="flex-grow-1 me-3">
                                                <div class="fs-7 fw-bold text-gray-800 text-truncate" style="max-width: 180px;">{{ $doc->original_filename }}</div>
                                                <div class="fs-8 text-muted">{{ number_format($doc->file_size / 1024, 2) }} KB</div>
                                            </div>
                                            <div class="d-flex gap-1">
                                                {{-- VIEW --}}
                                                @if (in_array($doc->mime_type, ['image/jpeg', 'image/png', 'image/jpg']))
                                                    <button type="button" class="btn btn-sm btn-icon btn-light-primary"
                                                            data-bs-toggle="modal" data-bs-target="#imgPreview{{ $doc->id }}"
                                                            title="Lihat Bukti Bayar">
                                                        <i class="ki-outline ki-eye fs-4"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('web.payment-proofs.view-document', [$proof, $doc->id]) }}"
                                                       target="_blank" class="btn btn-sm btn-icon btn-light-primary" title="Lihat Bukti Bayar">
                                                        <i class="ki-outline ki-eye fs-4"></i>
                                                    </a>
                                                @endif
                                                {{-- DOWNLOAD --}}
                                                <a href="{{ route('web.payment-proofs.download-document', [$proof, $doc->id]) }}"
                                                   class="btn btn-sm btn-icon btn-light-success" title="Download Bukti Bayar" download>
                                                    <i class="ki-outline ki-cloud-download fs-4"></i>
                                                </a>
                                            </div>
                                        </div>

                                        {{-- Image Popup Modal --}}
                                        @if (in_array($doc->mime_type, ['image/jpeg', 'image/png', 'image/jpg']))
                                            <div class="modal fade" id="imgPreview{{ $doc->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="modal-title fw-bold">
                                                                Pembayaran {{ $loop->parent->iteration }} — {{ $doc->original_filename }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center p-5">
                                                            <img src="{{ route('web.payment-proofs.view-document', [$proof, $doc->id]) }}"
                                                                 alt="{{ $doc->original_filename }}"
                                                                 class="img-fluid rounded shadow" style="max-height: 70vh; object-fit: contain;">
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0 justify-content-center">
                                                            <a href="{{ route('web.payment-proofs.download-document', [$proof, $doc->id]) }}"
                                                               class="btn btn-sm btn-light-success" download>
                                                                <i class="ki-outline ki-cloud-download fs-4 me-1"></i> Download Bukti Bayar
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted fst-italic fs-7">Tidak ada bukti yang diunggah.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>

            {{-- ═══ LANJUTKAN PELUNASAN ═══ --}}
            {{-- Shown when: proof is APPROVED, payment was partial, and invoice is not yet fully paid --}}
            @if (
                $paymentProof->status === App\Enums\PaymentProofStatus::APPROVED
                && $paymentProof->payment_type === 'partial'
                && $paymentProof->customerInvoice->status !== App\Enums\CustomerInvoiceStatus::PAID
            )
                @php
                    $invoice = $paymentProof->customerInvoice;
                    $sisaTagihan = (float) $invoice->total_amount - (float) $invoice->paid_amount;
                @endphp
                <div class="card card-flush border-success border border-dashed mt-5 shadow-sm">
                    <div class="card-body py-6">
                        <div class="d-flex align-items-center gap-4">
                            <div class="symbol symbol-50px">
                                <span class="symbol-label bg-light-success">
                                    <i class="ki-outline ki-wallet fs-2x text-success"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="text-success mb-1">Lanjutkan Pelunasan</h4>
                                <div class="text-muted fs-7">
                                    Pembayaran sebagian telah disetujui. Sisa tagihan untuk invoice
                                    <strong>{{ $invoice->invoice_number }}</strong> adalah:
                                </div>
                                <div class="fs-3 fw-bolder text-success mt-2">
                                    Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('web.payment-proofs.create', ['invoice_id' => $invoice->id]) }}"
                                   class="btn btn-success">
                                    <i class="ki-outline ki-plus fs-4 me-1"></i>
                                    Bayar Pelunasan
                                </a>
                            </div>
                        </div>
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
                            <x-badge :variant="$paymentProof->status->color()">{{ $paymentProof->status->label() }}</x-badge>
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

                    @if ($paymentProof->verifiedBy)
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

                    @if ($paymentProof->approvedBy)
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
        </div>
    </div>
</x-layout>
