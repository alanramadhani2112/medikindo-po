<x-layout>
    <x-page-header title="Persetujuan Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10" x-data="{ checkBank: false, checkAmount: false, checkCustomer: false, checkDoc: false }">
        {{-- Left: Document & Verification Form --}}
        <div class="col-lg-7">
            {{-- 1. DOKUMEN BUKTI TRANSFER (PRIORITAS UTAMA - DI ATAS) --}}
            <x-card title="Dokumen Bukti Transfer" icon="file-up" class="mb-5">
                <div class="row g-3">
                    @forelse($paymentProof->paymentDocuments as $doc)
                        <div class="col-md-12">
                            <div class="border border-dashed border-primary rounded px-7 py-5 mb-3 d-flex flex-stack bg-light-primary">
                                <div class="d-flex align-items-center">
                                    <i class="ki-outline ki-picture fs-3x text-primary me-4"></i>
                                    <div>
                                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#docPreview{{ $doc->id }}" class="fs-5 text-gray-900 text-hover-primary fw-bold">{{ $doc->original_filename }}</a>
                                        <div class="fs-7 text-muted fw-semibold mt-1">
                                            <i class="ki-outline ki-file fs-7 me-1"></i>
                                            Ukuran: {{ number_format($doc->file_size / 1024, 2) }} KB
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#docPreview{{ $doc->id }}">
                                        <i class="ki-outline ki-eye fs-3 me-1"></i> Lihat Bukti Bayar
                                    </button>
                                    <a href="{{ route('web.payment-proofs.download-document', [$paymentProof, $doc->id]) }}" class="btn btn-light-primary">
                                        <i class="ki-outline ki-cloud-download fs-3 me-1"></i> Download Bukti Bayar
                                    </a>
                                </div>
                            </div>

                            {{-- Modal Lightbox untuk Preview Dokumen --}}
                            <div class="modal fade" id="docPreview{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">
                                                <i class="ki-outline ki-picture text-primary fs-2 me-2"></i>
                                                {{ $doc->original_filename }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center p-5">
                                            @if (in_array($doc->mime_type, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']))
                                                {{-- Preview untuk gambar --}}
                                                <img src="{{ route('web.payment-proofs.view-document', [$paymentProof, $doc->id]) }}"
                                                     alt="{{ $doc->original_filename }}"
                                                     class="img-fluid rounded shadow" 
                                                     style="max-height: 75vh; object-fit: contain;">
                                            @elseif ($doc->mime_type === 'application/pdf')
                                                {{-- Preview untuk PDF --}}
                                                <iframe src="{{ route('web.payment-proofs.view-document', [$paymentProof, $doc->id]) }}"
                                                        class="w-100 rounded shadow"
                                                        style="height: 75vh; border: none;">
                                                </iframe>
                                            @else
                                                {{-- Untuk file lain, tampilkan info dan tombol download --}}
                                                <div class="py-10">
                                                    <i class="ki-outline ki-file fs-5x text-muted mb-5"></i>
                                                    <p class="text-gray-600 fs-5 mb-5">
                                                        Preview tidak tersedia untuk tipe file ini.
                                                    </p>
                                                    <a href="{{ route('web.payment-proofs.download-document', [$paymentProof, $doc->id]) }}" 
                                                       class="btn btn-primary">
                                                        <i class="ki-outline ki-cloud-download fs-3 me-1"></i> Download untuk Melihat
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer border-0 pt-0 justify-content-center">
                                            <a href="{{ route('web.payment-proofs.download-document', [$paymentProof, $doc->id]) }}"
                                               class="btn btn-light-success">
                                                <i class="ki-outline ki-cloud-download fs-3 me-1"></i> Download Bukti Bayar
                                            </a>
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 py-5 text-center text-muted">
                            <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                            <div>Tidak ada lampiran dokumen.</div>
                        </div>
                    @endforelse
                </div>
            </x-card>

            {{-- 2. DETAIL PEMBAYARAN --}}
            <x-card title="Detail Pembayaran" icon="wallet" class="mb-5">
                <div class="row g-5">
                    <div class="col-md-6">
                        <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block mb-2">Jumlah yang Disubmit</label>
                        <span class="fs-2 fw-bolder text-primary">Rp {{ number_format($paymentProof->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block mb-2">Tanggal Transfer</label>
                        <span class="fs-4 fw-bold text-gray-800">{{ $paymentProof->payment_date->format('d M Y') }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block mb-2">Referensi Bank / No. Reff</label>
                        <span class="fs-6 fw-bold text-gray-800">{{ $paymentProof->bank_reference ?? '—' }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block mb-2">Metode Pembayaran</label>
                        <span class="fs-6 fw-bold text-gray-800">{{ $paymentProof->payment_method ?? 'Bank Transfer' }}</span>
                    </div>
                    @if($paymentProof->sender_bank_name)
                    <div class="col-md-6">
                        <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block mb-2">Bank Pengirim</label>
                        <span class="fs-6 fw-bold text-gray-800">{{ $paymentProof->sender_bank_name }}</span>
                    </div>
                    @endif
                    @if($paymentProof->sender_account_number)
                    <div class="col-md-6">
                        <label class="fs-8 fw-bold text-gray-600 text-uppercase d-block mb-2">No. Rekening Pengirim</label>
                        <span class="fs-6 fw-bold text-gray-800 font-monospace">{{ $paymentProof->sender_account_number }}</span>
                    </div>
                    @endif
                </div>
            </x-card>

            {{-- 3. VERIFIKASI & ACTION --}}
            <x-card title="Verifikasi Pembayaran" icon="shield-search">
                <div class="alert alert-info d-flex align-items-center mb-8">
                    <i class="ki-outline ki-information-5 fs-2x text-info me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold fs-6">Instruksi Finance</span>
                        <span>Silakan periksa dokumen bukti transfer dan pastikan dana sudah masuk ke rekening real sebelum menyetujui.</span>
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
                        <a href="{{ route('web.payment-proofs.reject', $paymentProof) }}" class="btn btn-danger">
                            <i class="ki-outline ki-cross-circle fs-2"></i> Tolak
                        </a>
                        <button type="submit" class="btn btn-success" :disabled="!(checkBank && checkAmount && checkCustomer && checkDoc)">
                            <i class="ki-outline ki-verify fs-2"></i> Setujui & Terima Pembayaran
                        </button>
                    </div>
                </form>
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
                        @if((float)$inv->surcharge > 0)
                        <div class="d-flex flex-stack mb-2 text-primary">
                            <span class="fs-8 fw-bold">
                                Surcharge{{ $inv->surcharge_percentage > 0 ? ' (' . $inv->surcharge_percentage . '%)' : '' }}
                                <span data-bs-toggle="tooltip" title="Biaya tambahan atas metode pembayaran tertentu">
                                    <i class="ki-outline ki-information-5 fs-9 text-muted"></i>
                                </span>:
                            </span>
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
