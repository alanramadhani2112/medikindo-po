<x-layout :title="'AP ' . $invoice->invoice_number" :pageTitle="$invoice->invoice_number" breadcrumb="Hutang ke Supplier">

    <x-page-header :title="$invoice->invoice_number" description="Detail tagihan dari distributor/supplier">
        <x-slot name="actions">
            <span class="badge {{ $invoice->status->getBadgeClass() }} fs-7 me-2">{{ $invoice->status->getLabel() }}</span>

            {{-- Tombol Verifikasi AP → trigger generate AR --}}
            @if($invoice->isDraft())
                @can('create_invoices')
                    <form method="POST" action="{{ route('web.invoices.supplier.verify', $invoice) }}"
                          onsubmit="return confirm('Verifikasi invoice ini? Sistem akan otomatis membuat draft tagihan ke RS/Klinik.')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="ki-outline ki-check-circle fs-4 me-1"></i>
                            Verifikasi & Buat Tagihan RS
                        </button>
                    </form>
                @endcan
            @elseif($invoice->isVerified())
                @php
                    $arInvoice = \App\Models\CustomerInvoice::where('supplier_invoice_id', $invoice->id)
                        ->whereNotIn('status', [\App\Enums\CustomerInvoiceStatus::VOID->value])
                        ->first();
                @endphp
                @if($arInvoice)
                    <a href="{{ route('web.invoices.customer.show', $arInvoice) }}"
                       class="btn btn-light-success btn-sm">
                        <i class="ki-outline ki-arrow-right fs-4 me-1"></i>
                        Lihat Tagihan RS ({{ $arInvoice->invoice_number }})
                    </a>
                @endif
            @endif

            <button onclick="window.open('{{ route('web.invoices.supplier.pdf', $invoice) }}', '_blank')"
                    class="btn btn-light-primary btn-sm">
                <i class="ki-outline ki-document fs-3"></i>
                Cetak Invoice AP
            </button>
            <a href="{{ route('web.invoices.supplier.index') }}" class="btn btn-light-secondary btn-sm">
                <i class="ki-outline ki-arrow-left fs-3"></i>
                Kembali
            </a>
        </x-slot>
    </x-page-header>

    {{-- Info banner: AP verified, AR sudah dibuat --}}
    @if($invoice->isVerified())
        @php
            $arInvoice = $arInvoice ?? \App\Models\CustomerInvoice::where('supplier_invoice_id', $invoice->id)
                ->whereNotIn('status', [\App\Enums\CustomerInvoiceStatus::VOID->value])
                ->first();
        @endphp
        @if($arInvoice)
            <div class="alert alert-success d-flex align-items-center mb-7">
                <i class="ki-outline ki-verify fs-2hx text-success me-4"></i>
                <div class="flex-grow-1">
                    <strong>Invoice sudah diverifikasi.</strong>
                    Draft tagihan ke RS/Klinik sudah dibuat:
                    <a href="{{ route('web.invoices.customer.show', $arInvoice) }}" class="fw-bold ms-1">
                        {{ $arInvoice->invoice_number }}
                    </a>
                    <span class="badge {{ $arInvoice->status->getBadgeClass() }} ms-2">{{ $arInvoice->status->getLabel() }}</span>
                </div>
            </div>
        @endif
    @elseif($invoice->isDraft())
        <div class="alert alert-warning d-flex align-items-center mb-7">
            <i class="ki-outline ki-information-5 fs-2hx text-warning me-4"></i>
            <div>
                <strong>Menunggu verifikasi.</strong>
                Klik <strong>"Verifikasi & Buat Tagihan RS"</strong> untuk memverifikasi invoice ini dan otomatis membuat draft tagihan ke RS/Klinik.
            </div>
        </div>
    @endif

    {{-- Financial Metrics --}}
    <div class="row g-5 mb-7">
        {{-- Total Tagihan --}}
        <div class="col-md-4">
            <div class="card bg-primary hoverable border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <span class="text-white opacity-75 fs-7 fw-bold text-uppercase mb-2 d-block">Total Nilai Tagihan AP</span>
                    <span class="text-white fs-1 fw-bolder mb-4">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    
                    <div class="text-white opacity-75 fs-7 d-flex align-items-center mt-auto">
                        <i class="ki-outline ki-calendar fs-5 me-2 text-white"></i>
                        <span>Tenggat: <span class="fw-bold">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</span></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sudah Dibayar --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <span class="text-muted fs-7 fw-bold text-uppercase mb-2 d-block">Sudah Dibayar</span>
                    <span class="text-gray-900 fs-1 fw-bold mb-4">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                    
                    <div class="mt-auto">
                        @php
                            $paidPct = $invoice->total_amount > 0 ? round(($invoice->paid_amount / $invoice->total_amount) * 100) : 0;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fs-8 fw-semibold text-gray-400">Persentase</span>
                            <span class="fs-8 fw-bold text-success">{{ $paidPct }}%</span>
                        </div>
                        <div class="progress h-6px rounded bg-light-success">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $paidPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisa Tagihan --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <span class="text-danger fs-7 fw-bold text-uppercase mb-2 d-block">Sisa Tagihan (Utang)</span>
                    <span class="text-danger fs-1 fw-bold mb-4">Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}</span>
                    
                    <div class="fs-7 d-flex align-items-center bg-light-danger p-3 rounded mt-auto">
                        <i class="ki-outline ki-information-5 fs-5 text-danger me-2"></i>
                        <span class="text-danger fw-semibold fs-8">Pastikan lunas sebelum jatuh tempo!</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    @if($invoice->lineItems->count() > 0)
    <x-card title="Rincian Item Invoice" class="mb-7">
        <x-slot name="actions">
            <span class="badge badge-light-primary fs-7 px-3 py-2">{{ $invoice->lineItems->count() }} Item</span>
        </x-slot>
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted bg-light">
                        <th class="ps-4 rounded-start min-w-200px">Produk</th>
                        <th>Batch / Exp</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Diskon</th>
                        <th class="text-end pe-4 rounded-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->lineItems as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-gray-900 fs-6">{{ $item->product_name }}</div>
                                <div class="text-muted fs-8">SKU: {{ $item->product_sku ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-dark fw-bold fs-8">{{ $item->batch_no ?? '—' }}</span>
                                <div class="text-muted fs-8 mt-1">
                                    <i class="ki-outline ki-time fs-8 me-1"></i>
                                    {{ $item->expiry_date?->format('d M Y') ?? '—' }}
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-gray-800">{{ number_format($item->quantity, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-end">
                                <span class="text-gray-700">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-end">
                                @if($item->discount_percentage > 0)
                                    <span class="text-warning fw-bold">{{ $item->discount_percentage }}%</span>
                                    <div class="text-muted fs-8">-Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <span class="fw-bold text-primary fs-6">Rp {{ number_format($item->line_total, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <td colspan="5" class="text-end pe-4 fw-bold text-gray-700 fs-6">Subtotal</td>
                        <td class="text-end pe-4 fw-bold text-gray-900 fs-5">Rp {{ number_format($invoice->subtotal_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td colspan="5" class="text-end pe-4 text-warning fw-bold">Diskon</td>
                        <td class="text-end pe-4 text-warning fw-bold">-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($invoice->tax_amount > 0)
                    <tr>
                        <td colspan="5" class="text-end pe-4 text-gray-600 fw-bold">PPN</td>
                        <td class="text-end pe-4 text-gray-600 fw-bold">+Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="bg-gray-100">
                        <td colspan="5" class="text-end pe-4 fw-bolder text-gray-900 fs-5">TOTAL</td>
                        <td class="text-end pe-4 fw-bolder text-primary fs-3">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>
    @endif

    <div class="row">
        {{-- Left Column: Reference Info --}}
        <div class="col-lg-4 mb-7">
            <x-card title="Informasi Referensi" icon="information">
                <div class="mb-7">
                    <span class="text-gray-600 fs-7 fw-bold d-block">Supplier Penagih</span>
                    <h6 class="text-gray-900 fw-bold fs-5 mt-1">{{ $invoice->supplier?->name ?? '—' }}</h6>
                </div>
                
                <div class="border-top pt-7">
                    <span class="text-gray-400 fs-8 fw-bold text-uppercase mb-5 d-block">Referensi Dokumen</span>
                    <div class="d-flex flex-column gap-3">
                        @if($invoice->goods_receipt_id)
                            <a href="{{ route('web.goods-receipts.show', $invoice->goods_receipt_id) }}" 
                               class="d-flex align-items-center gap-4 p-4 rounded-3 bg-light-primary text-hover-primary transition">
                                <div class="symbol symbol-40px">
                                    <div class="symbol-label bg-primary text-inverse-primary rounded-circle">
                                        <i class="ki-outline ki-package fs-4"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold fs-6">Goods Receipt</span>
                                    <span class="text-muted fw-semibold fs-8">{{ $invoice->goodsReceipt?->gr_number ?? 'Dokumen GR' }}</span>
                                </div>
                            </a>
                        @endif
                        <a href="{{ route('web.po.show', $invoice->purchase_order_id) }}" 
                           class="d-flex align-items-center gap-4 p-4 rounded-3 bg-light-info text-hover-info transition">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label bg-info text-inverse-info rounded-circle">
                                    <i class="ki-outline ki-document fs-4"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-900 fw-bold fs-6">Purchase Order</span>
                                <span class="text-muted fw-semibold fs-8">{{ $invoice->purchaseOrder?->po_number ?? 'Dokumen PO' }}</span>
                            </div>
                        </a>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Right Column: Payment History --}}
        <div class="col-lg-8">
            <x-card title="Riwayat Pembayaran" icon="entrance-right">


                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 rounded-start">Nomor Pembayaran / Ref</th>
                                <th class="text-end">Total Terbayar</th>
                                <th class="text-end pe-4 rounded-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->paymentAllocations as $alloc)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-3">
                                                <div class="symbol-label bg-light-success text-success rounded-circle">
                                                    <i class="ki-outline ki-wallet fs-3"></i>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold fs-6">{{ $alloc->payment?->payment_number }}</span>
                                                <span class="text-muted fw-semibold fs-8 mt-1"><i class="ki-outline ki-bank fs-8 me-1"></i>{{ $alloc->payment?->payment_method }} - {{ $alloc->payment?->reference ?? 'Tanpa Ref' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-light-success fw-bold fs-6 px-3 py-2">+ Rp {{ number_format($alloc->allocated_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="text-gray-700 fw-bold fs-7">{{ $alloc->created_at->format('d M Y') }}</span>
                                            <span class="text-muted fs-8">{{ $alloc->created_at->format('H:i') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-10">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ki-outline ki-bill fs-4x text-gray-300 mb-4"></i>
                                            <span class="text-gray-800 fw-bold fs-5 mb-2">Belum ada pembayaran riil</span>
                                            <span class="text-muted fs-7">Uang AP akan otomatis ditarik / dibayarkan saat uang Piutang Pelanggan CAIR.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

</x-layout>
