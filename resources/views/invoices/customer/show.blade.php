<x-layout :title="'Invoice ' . $invoice->invoice_number" :pageTitle="$invoice->invoice_number" breadcrumb="Tagihan ke RS/Klinik">

    <x-page-header :title="$invoice->invoice_number" description="Detail tagihan piutang (AR)">
        <x-slot name="actions">
            <span class="badge {{ $invoice->status->getBadgeClass() }} fs-7 me-2">{{ $invoice->status->getLabel() }}</span>
            @if($invoice->supplierInvoice)
                <span class="badge badge-light-primary fs-8 me-2">
                    <i class="ki-outline ki-verify fs-8 me-1"></i>
                    AP: {{ $invoice->supplierInvoice->invoice_number }}
                </span>
            @endif
            
            <a href="{{ route('web.invoices.customer.pdf', $invoice) }}" target="_blank" class="btn btn-light-info btn-sm">
                <i class="ki-outline ki-document fs-3"></i> Cetak PDF
            </a>

            {{-- Verification Button (Finance/Admin) --}}
            @if(!$invoice->isPaid() && $invoice->payment_submitted_at)
                @can('verify_payment')
                    <form method="POST" action="{{ route('web.invoices.customer.verify_payment', $invoice) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm submit-confirm" data-title="Verifikasi Pembayaran?" data-text="Pastikan dana sudah masuk ke rekening real sebelum verifikasi.">
                            <i class="ki-outline ki-verify fs-3"></i> Verifikasi Pembayaran
                        </button>
                    </form>
                @endcan
            @endif

            {{-- Payment Confirmation Button (Clinic/RS) --}}
            @if($invoice->status->canAcceptPayment() && !$invoice->payment_submitted_at)
                @can('confirm_payment')
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#confirmPaymentModal">
                        <i class="ki-outline ki-wallet fs-3"></i> Bayar Tagihan (Konfirmasi)
                    </button>
                @endcan
            @endif

            <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light btn-sm">
                <i class="ki-outline ki-arrow-left fs-3"></i> Kembali
            </a>
        </x-slot>
    </x-page-header>

    {{-- Modals --}}
    @if($invoice->status->canAcceptPayment() && !$invoice->payment_submitted_at)
    <div class="modal fade" id="confirmPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('web.invoices.customer.confirm_payment', $invoice) }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="fw-bold">Konfirmasi Pembayaran</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <div class="mb-5">
                            <label class="form-label required fw-semibold">Tanggal Pembayaran</label>
                            <input type="date" name="payment_date" class="form-control form-control-solid" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required fw-semibold">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select form-select-solid" required>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Tunai</option>
                                <option value="VA">Virtual Account</option>
                            </select>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required fw-semibold">Referensi / No. Transaksi</label>
                            <input type="text" name="payment_reference" class="form-control form-control-solid" placeholder="Contoh: TRF-12345" required>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required fw-semibold">Jumlah yang Dibayar</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="paid_amount" class="form-control form-control-solid" value="{{ (int)$invoice->total_amount }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Kirim Konfirmasi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary Row --}}
    <div class="row g-5 mb-7">
        <div class="col-md-4">
            <div class="card bg-gray-900">
                <div class="card-body p-7">
                    <span class="text-gray-500 fs-7 fw-bold text-uppercase">Total Tagihan (Nett)</span>
                    <h2 class="text-white fs-2hx fw-bold mt-2 mb-0">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-success border border-success border-dashed">
                <div class="card-body p-7">
                    <span class="text-success fs-7 fw-bold text-uppercase">Telah Terbayar</span>
                    <h2 class="text-success fs-2hx fw-bold mt-2 mb-0">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-danger border border-danger border-dashed">
                <div class="card-body p-7">
                    <span class="text-danger fs-7 fw-bold text-uppercase">Sisa Piutang (AR)</span>
                    <h2 class="text-danger fs-2hx fw-bold mt-2 mb-0">Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        {{-- Left: Details --}}
        <div class="col-lg-4">
            <x-card title="Informasi Tagihan" icon="information">
                <div class="d-flex flex-column gap-5">
                    <div>
                        <span class="text-gray-600 fs-7 fw-bold d-block">RS/Klinik:</span>
                        <span class="text-gray-900 fw-bold fs-6">{{ $invoice->organization?->name ?? '—' }}</span>
                    </div>
                    <div class="separator separator-dashed"></div>
                    <div class="row">
                        <div class="col-6">
                            <span class="text-gray-600 fs-7 fw-bold d-block">Tgl Terbit:</span>
                            <span class="text-gray-800 fs-7">{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-gray-600 fs-7 fw-bold d-block">Jatuh Tempo:</span>
                            <span class="text-danger fw-bold fs-7">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="separator separator-dashed"></div>
                    <div>
                        <span class="text-gray-600 fs-7 fw-bold d-block mb-3">Dokumen Referensi:</span>
                        <div class="d-flex flex-column gap-2">
                            @if($invoice->goodsReceipt)
                                <a href="{{ route('web.goods-receipts.show', $invoice->goodsReceipt) }}" class="btn btn-sm btn-light-primary text-start">
                                    <i class="ki-outline ki-package fs-4 me-1"></i> GR: {{ $invoice->goodsReceipt->gr_number }}
                                </a>
                            @endif
                            @if($invoice->purchaseOrder)
                                <a href="{{ route('web.po.show', $invoice->purchaseOrder) }}" class="btn btn-sm btn-light-info text-start">
                                    <i class="ki-outline ki-document fs-4 me-1"></i> PO: {{ $invoice->purchaseOrder->po_number }}
                                </a>
                            @endif
                        </div>
                    </div>
                    @if($invoice->surcharge > 0 || $invoice->ematerai_fee > 0)
                        <div class="separator separator-dashed"></div>
                        <div class="bg-light-warning p-4 rounded border border-warning border-dashed">
                            <h6 class="text-gray-800 fw-bold fs-7 mb-3 text-uppercase">Penyesuaian Biaya</h6>
                            @if($invoice->surcharge > 0)
                                <div class="d-flex flex-stack mb-2">
                                    <span class="text-gray-600 fs-8 fw-bold">Surcharge:</span>
                                    <span class="text-gray-800 fs-7 fw-bolder">Rp {{ number_format($invoice->surcharge, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($invoice->ematerai_fee > 0)
                                <div class="d-flex flex-stack">
                                    <span class="text-gray-600 fs-8 fw-bold">e-Meterai:</span>
                                    <span class="text-gray-800 fs-7 fw-bolder">Rp {{ number_format($invoice->ematerai_fee, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Right: Items & Payments --}}
        <div class="col-lg-8">
            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold mb-8">
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_items">Item Tagihan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_payments">Riwayat Bayar</a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Items Tab --}}
                <div class="tab-pane fade show active" id="kt_items">
                    <x-card title="Rincian Barang" class="mb-5">
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4">Produk</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end pe-4">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->lineItems as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-900 fw-bold fs-6">{{ $item->product_name }}</span>
                                                    <span class="text-gray-500 fs-7">Batch: {{ $item->batch_no ?? '—' }} | Exp: {{ $item->expiry_date?->format('d/m/Y') ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold">{{ $item->quantity }} {{ $item->unit }}</td>
                                            <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-end pe-4 fw-bold text-gray-900">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Calculation Footer --}}
                        <div class="d-flex justify-content-end mt-5">
                            <div class="w-100 w-md-300px">
                                <div class="d-flex flex-stack mb-3">
                                    <div class="fw-semibold text-gray-600 fs-7">Subtotal:</div>
                                    <div class="fw-bold text-gray-800 fs-7">Rp {{ number_format($invoice->subtotal_amount, 0, ',', '.') }}</div>
                                </div>
                                <div class="d-flex flex-stack mb-3">
                                    <div class="fw-semibold text-gray-600 fs-7">PPN (11%):</div>
                                    <div class="fw-bold text-gray-800 fs-7">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</div>
                                </div>
                                @if($invoice->surcharge > 0)
                                    <div class="d-flex flex-stack mb-3">
                                        <div class="fw-semibold text-gray-600 fs-7">Surcharge:</div>
                                        <div class="fw-bold text-primary fs-7">Rp {{ number_format($invoice->surcharge, 0, ',', '.') }}</div>
                                    </div>
                                @endif
                                @if($invoice->ematerai_fee > 0)
                                    <div class="d-flex flex-stack mb-3">
                                        <div class="fw-semibold text-gray-600 fs-7">e-Meterai:</div>
                                        <div class="fw-bold text-gray-800 fs-7">Rp {{ number_format($invoice->ematerai_fee, 0, ',', '.') }}</div>
                                    </div>
                                @endif
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex flex-stack">
                                    <div class="fw-bold text-gray-800 fs-6">Grand Total:</div>
                                    <div class="fw-bolder text-gray-900 fs-5">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>

                {{-- Payments Tab --}}
                <div class="tab-pane fade" id="kt_payments">
                    <x-card title="Riwayat Pembayaran Real" icon="entrance-right">
                        @if($invoice->status->canAcceptPayment())
                            <x-slot name="actions">
                                <a href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-success">
                                    <i class="ki-outline ki-plus fs-4"></i> Catat Pembayaran
                                </a>
                            </x-slot>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4">No. Pembayaran</th>
                                        <th>Metode</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end pe-4">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoice->paymentAllocations as $alloc)
                                        <tr>
                                            <td class="ps-4 fw-bold text-gray-800">{{ $alloc->payment?->payment_number }}</td>
                                            <td><span class="badge badge-light-secondary">{{ strtoupper($alloc->payment?->payment_method ?? '—') }}</span></td>
                                            <td class="text-end text-success fw-bold">+ Rp {{ number_format($alloc->allocated_amount, 0, ',', '.') }}</td>
                                            <td class="text-end pe-4 text-gray-600">{{ $alloc->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-8 text-gray-500 fs-7">Belum ada pembayaran riil yang dicatat.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>

</x-layout>
