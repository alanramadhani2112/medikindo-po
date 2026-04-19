<x-layout>
    <x-page-header title="Persetujuan Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10" x-data="{ checkBank: false, checkAmount: false, checkCustomer: false, checkDoc: false }">
        {{-- Left: Verification Form & Checklist --}}
        <div class="col-lg-7">
            <x-card title="Verifikasi Pembayaran" icon="shield-search" class="mb-5">
                <div class="alert alert-info d-flex align-items-center mb-8">
                    <i class="ki-outline ki-information-5 fs-2x text-info me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold fs-6">Instruksi Finance</span>
                        <span>Silakan periksa dokumen bukti transfer dan pastikan dana sudah masuk ke rekening real sebelum menyetujui.</span>
                    </div>
                </div>

                {{-- Submission Details --}}
                <div class="bg-light-primary rounded p-5 mb-8 border border-primary border-dashed">
                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block">Jumlah yang Disubmit</label>
                            <span class="fs-2 fw-bolder text-primary">Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block">Tanggal Transfer</label>
                            <span class="fs-4 fw-bold text-gray-800">{{ $paymentProof->payment_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-12">
                            <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block">Referensi Bank / No. Reff</label>
                            <span class="fs-6 fw-bold text-gray-800">{{ $paymentProof->bank_reference ?? 'Tidak ada referensi' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Checklist Section --}}
                <div class="mb-10">
                    <h5 class="text-gray-800 fw-bold mb-5 d-flex align-items-center">
                        <i class="ki-outline ki-check-square fs-2 text-success me-2"></i>
                        Checklist Verifikasi Finance (Wajib)
                    </h5>
                    <div class="d-flex flex-column gap-3">
                        <div class="form-check form-check-custom form-check-solid form-check-sm">
                            <input class="form-check-input" type="checkbox" id="check_bank" x-model="checkBank" />
                            <label class="form-check-label text-gray-700 fw-semibold fs-7" for="check_bank">
                                Dana sudah dipastikan masuk ke mutasi Rekening Medikindo (BCA/Mandiri).
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid form-check-sm">
                            <input class="form-check-input" type="checkbox" id="check_amount" x-model="checkAmount" />
                            <label class="form-check-label text-gray-700 fw-semibold fs-7" for="check_amount">
                                Nominal di bukti transfer cocok dengan jumlah yang disubmit RS (Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}).
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid form-check-sm">
                            <input class="form-check-input" type="checkbox" id="check_customer" x-model="checkCustomer" />
                            <label class="form-check-label text-gray-700 fw-semibold fs-7" for="check_customer">
                                Nama pengirim di mutasi bank sesuai dengan RS/Klinik penagih.
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid form-check-sm">
                            <input class="form-check-input" type="checkbox" id="check_doc" x-model="checkDoc" />
                            <label class="form-check-label text-gray-700 fw-semibold fs-7" for="check_doc">
                                Dokumen bukti bayar (gambar/PDF) terbaca jelas dan tidak mencurigakan.
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Action Form --}}
                <form action="{{ route('web.payment-proofs.process-approval', $paymentProof) }}" method="POST">
                    @csrf
                    <div class="mb-10">
                        <label class="form-label fw-bold">Catatan Verifikasi (Internal)</label>
                        <textarea name="approval_notes" class="form-control form-control-solid" rows="3" placeholder="Contoh: Valid, dana masuk Rek BCA Medikindo jam 14:00."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3 border-top pt-5">
                        <a href="{{ route('web.payment-proofs.show', $paymentProof) }}" class="btn btn-light">Batal</a>
                        <a href="{{ route('web.payment-proofs.reject', $paymentProof) }}" class="btn btn-light-danger">
                            <i class="ki-outline ki-cross-circle fs-2"></i> Tolak
                        </a>
                        <button type="submit" class="btn btn-success" :disabled="!(checkBank && checkAmount && checkCustomer && checkDoc)">
                            <i class="ki-outline ki-verify fs-2"></i> Setujui & Lunasi Tagihan
                        </button>
                    </div>
                </form>
            </x-card>

            {{-- Evidence Documents --}}
            <x-card title="Dokumen Bukti Transfer" icon="file-up">
                <div class="row g-3">
                    @forelse($paymentProof->paymentDocuments as $doc)
                        <div class="col-md-12">
                            <div class="border border-dashed border-gray-300 rounded px-7 py-4 mb-3 d-flex flex-stack bg-light">
                                <div class="d-flex align-items-center">
                                    <i class="ki-outline ki-picture fs-2x text-primary me-4"></i>
                                    <div>
                                        <a href="{{ route('web.payment-proofs.download-document', [$paymentProof, $doc->id]) }}" target="_blank" class="fs-6 text-gray-800 text-hover-primary fw-bold">{{ $doc->original_filename }}</a>
                                        <div class="fs-7 text-muted fw-semibold">Ukuran: {{ number_format($doc->file_size / 1024, 2) }} KB</div>
                                    </div>
                                </div>
                                <a href="{{ route('web.payment-proofs.download-document', [$paymentProof, $doc->id]) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="ki-outline ki-eye fs-3 me-1"></i> Lihat
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 py-5 text-center text-muted">Tidak ada lampiran dokumen.</div>
                    @endforelse
                </div>
            </x-card>
        </div>

        {{-- Right: Invoice Details --}}
        <div class="col-lg-5">
            <x-card title="Detail Tagihan RS" icon="bill">
                @php $inv = $paymentProof->customerInvoice; @endphp
                <div class="d-flex flex-column gap-5">
                    <div class="d-flex flex-stack">
                        <span class="text-gray-600 fs-7 fw-bold">Nomor Invoice:</span>
                        <span class="text-gray-900 fw-bold fs-7">{{ $inv->invoice_number }}</span>
                    </div>
                    <div class="d-flex flex-stack">
                        <span class="text-gray-600 fs-7 fw-bold">RS / Klinik:</span>
                        <span class="text-gray-900 fw-bold fs-7 text-end">{{ $inv->organization?->name ?? '—' }}</span>
                    </div>
                    
                    <div class="separator separator-dashed"></div>

                    {{-- Financial Breakdown --}}
                    <div class="bg-gray-100 rounded p-4">
                        <div class="d-flex flex-stack mb-2">
                            <span class="text-gray-600 fs-8 fw-bold">Subtotal Item:</span>
                            <span class="text-gray-800 fs-7 fw-bold">Rp {{ number_format($inv->subtotal_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex flex-stack mb-2">
                            <span class="text-gray-600 fs-8 fw-bold">PPN (11%):</span>
                            <span class="text-gray-800 fs-7 fw-bold">Rp {{ number_format($inv->tax_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($inv->surcharge > 0)
                        <div class="d-flex flex-stack mb-2 text-primary">
                            <span class="fs-8 fw-bold">Surcharge:</span>
                            <span class="fs-7 fw-bold">Rp {{ number_format($inv->surcharge, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($inv->ematerai_fee > 0)
                        <div class="d-flex flex-stack mb-2">
                            <span class="text-gray-600 fs-8 fw-bold">e-Meterai:</span>
                            <span class="text-gray-800 fs-7 fw-bold">Rp {{ number_format($inv->ematerai_fee, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="separator border-gray-300 my-2"></div>
                        <div class="d-flex flex-stack">
                            <span class="text-gray-900 fw-bolder fs-7 text-uppercase">Total Tagihan:</span>
                            <span class="text-gray-900 fw-bolder fs-6">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Item List --}}
                    <div class="mt-5">
                        <h6 class="fs-7 fw-bold text-gray-800 mb-4">Rincian Barang:</h6>
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3">
                                <thead>
                                    <tr class="fw-bold text-muted fs-8 text-uppercase">
                                        <th>Produk</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inv->lineItems as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold fs-8">{{ $item->product_name }}</span>
                                                    <span class="text-muted fs-9">Batch: {{ $item->batch_no ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td class="text-end fs-8 fw-bold">{{ $item->quantity }} {{ $item->unit }}</td>
                                            <td class="text-end fs-8 fw-bold text-gray-900">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-layout>
