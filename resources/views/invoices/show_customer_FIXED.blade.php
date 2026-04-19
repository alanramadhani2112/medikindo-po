@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0">{{ $invoice->invoice_number }}</h1>
                <span class="badge {{ $invoice->status->getBadgeClass() }}">{{ $invoice->status->getLabel() }}</span>

                {{-- GR Compliance Badge --}}                @if($invoice->goods_receipt_id)
                    <span class="badge badge-light-success">
                        <i class="ki-outline ki-check-circle fs-5 me-1"></i>
                        Berdasarkan Penerimaan Barang
                    </span>
                @endif
            </div>
            <p class="text-gray-600 fs-6 mb-0">
                Tagihan Kepada: <span class="text-gray-900 fw-semibold">{{ $invoice->organization?->name ?? '—' }}</span>
            </p>
        </div>
        <div class="d-flex gap-3">
            <button onclick="window.open('{{ route('web.invoices.customer.pdf', $invoice) }}', '_blank')" 
                    class="btn btn-light-primary">
                <i class="ki-outline ki-document fs-2"></i>
                Cetak PDF
            </button>
            <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light">
                <i class="ki-outline ki-arrow-down fs-2"></i>
                Kembali
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-7">
        <div class="col-md-4">
            <div class="card bg-dark">
                <div class="card-body">
                    <span class="text-gray-400 fs-8 fw-bold text-uppercase">Total Penagihan</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <span class="text-gray-500 fs-8 fw-bold">JATUH TEMPO</span>
                        <span class="badge badge-light-danger">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <span class="text-gray-600 fs-7 fw-bold">Terbayar</span>
                    <div class="text-success fs-2 fw-bold mt-2">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</div>
                    <div class="mt-4">
                        @php $percent = $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0; @endphp
                        <div class="progress h-6px">
                            <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <span class="text-gray-600 fs-7 fw-bold">Sisa Tagihan</span>
                    <div class="text-gray-900 fs-2 fw-bold mt-2">Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}</div>
                    <div class="mt-4">
                        <div class="progress h-6px">
                            <div class="progress-bar @if($invoice->status === 'overdue') bg-danger @else bg-gray-300 @endif" style="width: {{ 100 - $percent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column: Invoice Details --}}
        <div class="col-lg-8 mb-7">
            
            {{-- BILL TO SECTION --}}
            <div class="card mb-5">
                <div class="card-header bg-light-primary">
                    <h3 class="card-title text-primary">
                        <i class="ki-outline ki-hospital fs-2 me-2"></i>
                        Tagihan Kepada (Bill To)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <span class="text-gray-600 fs-7 fw-bold">Nama Rumah Sakit / Klinik</span>
                            <div class="text-gray-900 fw-bold fs-4 mt-1">{{ $invoice->organization?->name ?? '—' }}</div>
                        </div>
                        <div>
                            <span class="text-gray-600 fs-7 fw-bold">Alamat</span>
                            <div class="text-gray-800 fs-6 mt-1">{{ $invoice->organization?->address ?? 'Alamat tidak tersedia' }}</div>
                        </div>
                        @if($invoice->organization?->phone)
                        <div>
                            <span class="text-gray-600 fs-7 fw-bold">Kontak</span>
                            <div class="text-gray-800 fs-6 mt-1">{{ $invoice->organization->phone }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- REFERENCE SECTION --}}
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-document fs-2 me-2"></i>
                        Referensi Dokumen
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle">
                            <tbody>
                                <tr>
                                    <td class="fw-bold text-gray-700" style="width: 40%">Nomor PO Internal</td>
                                    <td>
                                        <a href="{{ route('web.po.show', $invoice->purchase_order_id) }}" class="text-primary fw-bold">
                                            {{ $invoice->purchaseOrder?->po_number ?? '—' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-700">Nomor PO RS/Klinik</td>
                                    <td class="text-gray-900 fw-semibold">{{ $invoice->purchaseOrder?->external_po_number ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-700">Nomor Penerimaan Barang (GR)</td>
                                    <td>
                                        @if($invoice->goods_receipt_id)
                                            <a href="{{ route('web.goods-receipts.show', $invoice->goods_receipt_id) }}" class="text-primary fw-bold">
                                                {{ $invoice->goodsReceipt?->gr_number ?? '—' }}
                                            </a>
                                            <span class="badge badge-light-success ms-2">
                                                <i class="ki-outline ki-check fs-7"></i>
                                                Verified
                                            </span>
                                        @else
                                            <span class="text-gray-500">Tidak tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-700">Tanggal Invoice</td>
                                    <td class="text-gray-900">{{ $invoice->created_at->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-700">Jatuh Tempo</td>
                                    <td>
                                        <span class="badge badge-light-danger">{{ $invoice->due_date?->format('d F Y') ?? '—' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ITEM TABLE (HIGHEST PRIORITY) --}}
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-package fs-2 me-2"></i>
                        Detail Item Invoice
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4 mb-0">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-50px">No</th>
                                    <th class="min-w-200px">Nama Produk</th>
                                    <th class="min-w-100px">Batch</th>
                                    <th class="min-w-100px">Kadaluarsa</th>
                                    <th class="text-end min-w-80px">Qty</th>
                                    <th class="text-end min-w-100px">Satuan</th>
                                    <th class="text-end min-w-120px">Harga Satuan</th>
                                    <th class="text-end min-w-80px">Diskon</th>
                                    <th class="text-end min-w-120px pe-4">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->lineItems as $index => $item)
                                    <tr>
                                        <td class="ps-4 text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold">{{ $item->product_name }}</span>
                                                <span class="text-gray-500 fs-7">SKU: {{ $item->product_sku ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-primary">
                                                <i class="ki-outline ki-lock fs-7 me-1"></i>
                                                {{ $item->batch_no ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-warning">
                                                <i class="ki-outline ki-calendar fs-7 me-1"></i>
                                                {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-900 fw-semibold">{{ number_format($item->quantity, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-700">{{ $item->product?->unit ?? '—' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-900 fw-semibold">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if($item->discount_percentage > 0)
                                                <span class="badge badge-light-success">{{ number_format($item->discount_percentage, 1) }}%</span>
                                                <div class="text-gray-500 fs-8 mt-1">-Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</div>
                                            @else
                                                <span class="text-gray-500">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="text-gray-900 fw-bold">Rp {{ number_format($item->line_total, 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                                <span class="text-gray-700 fs-5 fw-semibold">Tidak ada item</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- PRICING SUMMARY --}}
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-calculator fs-2 me-2"></i>
                        Ringkasan Perhitungan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered align-middle">
                            <tbody>
                                <tr>
                                    <td class="text-gray-700 fw-semibold" style="width: 70%">Subtotal (Sebelum Diskon)</td>
                                    <td class="text-end text-gray-900 fw-bold">Rp {{ number_format($invoice->subtotal_amount + $invoice->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td class="text-gray-700 fw-semibold">Total Diskon</td>
                                    <td class="text-end text-success fw-bold">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-gray-700 fw-semibold">Subtotal (Setelah Diskon)</td>
                                    <td class="text-end text-gray-900 fw-bold">Rp {{ number_format($invoice->subtotal_amount, 0, ',', '.') }}</td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td class="text-gray-700 fw-semibold">PPN 11%</td>
                                    <td class="text-end text-gray-900 fw-bold">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="bg-light-primary">
                                    <td class="text-primary fw-bold fs-4">TOTAL TAGIHAN</td>
                                    <td class="text-end text-primary fw-bold fs-3">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- PAYMENT HISTORY --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-entrance-right fs-2 me-2"></i>
                        Riwayat Pembayaran
                    </h3>
                    @if($invoice->status !== 'paid')
                        <div class="card-toolbar">
                            <a href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="ki-outline ki-picture fs-4"></i>
                                Input Pembayaran
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">Nomor Ref</th>
                                    <th>Metode</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end pe-4 rounded-end">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->paymentAllocations as $alloc)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-gray-800 fw-bold">{{ $alloc->payment?->payment_number }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-secondary">{{ strtoupper($alloc->payment?->payment_method) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold">+ Rp {{ number_format($alloc->allocated_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="text-gray-600">{{ $alloc->created_at->format('d/m/Y') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-entrance-right fs-3x text-gray-400 mb-3"></i>
                                                <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Pembayaran</span>
                                                <span class="text-gray-500 fs-6">Pembayaran untuk invoice ini belum diterima.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Additional Info --}}
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-5">
                
                {{-- PAYMENT INSTRUCTIONS --}}
                <div class="card border-primary">
                    <div class="card-header bg-light-primary">
                        <h3 class="card-title text-primary">
                            <i class="ki-outline ki-bank fs-2 me-2"></i>
                            Instruksi Pembayaran
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-primary d-flex align-items-start mb-5">
                            <i class="ki-outline ki-information-5 fs-2x text-primary me-3"></i>
                            <div class="fs-7">
                                Mohon transfer sebesar <strong>Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}</strong> 
                                ke rekening di bawah ini.
                            </div>
                        </div>
                        
                        <div class="d-flex flex-column gap-4">
                            <div class="p-4 rounded bg-light">
                                <div class="text-gray-600 fs-7 fw-bold mb-2">Bank</div>
                                <div class="text-gray-900 fw-bold fs-5">BCA</div>
                            </div>
                            <div class="p-4 rounded bg-light">
                                <div class="text-gray-600 fs-7 fw-bold mb-2">Nomor Rekening</div>
                                <div class="text-gray-900 fw-bold fs-4">0987654321</div>
                            </div>
                            <div class="p-4 rounded bg-light">
                                <div class="text-gray-600 fs-7 fw-bold mb-2">Atas Nama</div>
                                <div class="text-gray-900 fw-bold fs-5">PT Medikindo Sejahtera</div>
                            </div>
                        </div>
                        
                        <div class="separator my-5"></div>
                        
                        <div class="alert alert-warning d-flex align-items-start">
                            <i class="ki-outline ki-information fs-2x text-warning me-3"></i>
                            <div class="fs-7">
                                <strong>Penting:</strong> Harap mencantumkan nomor invoice 
                                <strong>{{ $invoice->invoice_number }}</strong> pada berita transfer.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- NOTES --}}
                @if($invoice->notes)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-outline ki-note fs-2 me-2"></i>
                            Catatan
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-gray-700 fs-6 fst-italic mb-0">"{{ $invoice->notes }}"</p>
                    </div>
                </div>
                @endif

                {{-- AUDIT INFO --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-outline ki-shield-tick fs-2 me-2"></i>
                            Informasi Audit
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <span class="text-gray-600 fs-7 fw-bold">Diterbitkan Oleh</span>
                                <div class="text-gray-900 fw-semibold mt-1">{{ $invoice->issuedBy?->name ?? '—' }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600 fs-7 fw-bold">Tanggal Terbit</span>
                                <div class="text-gray-900 fw-semibold mt-1">{{ $invoice->issued_at?->format('d F Y H:i') ?? '—' }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600 fs-7 fw-bold">Versi Dokumen</span>
                                <div class="text-gray-900 fw-semibold mt-1">v{{ $invoice->version ?? 1 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
