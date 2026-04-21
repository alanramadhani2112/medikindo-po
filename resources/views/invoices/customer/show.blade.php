@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-7">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0">{{ $invoice->invoice_number }}</h1>
                <span class="badge {{ $invoice->status->getBadgeClass() }} fs-7">{{ $invoice->status->getLabel() }}</span>
                @if($invoice->isOverdueByDate())
                    <span class="badge badge-danger fs-8">
                        <i class="ki-outline ki-time fs-9 me-1"></i>
                        Lewat {{ $invoice->days_overdue }} hari
                    </span>
                @endif
            </div>
            <p class="text-gray-600 fs-6 mb-0">
                Tagihan kepada: <span class="text-gray-900 fw-semibold">{{ $invoice->organization?->name ?? '—' }}</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($invoice->isDraft())
                @can('create_invoices')
                    <form method="POST" action="{{ route('web.invoices.customer.issue', $invoice) }}"
                          onsubmit="return confirm('Terbitkan tagihan ini ke RS/Klinik?')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="ki-outline ki-send fs-4 me-1"></i>Terbitkan
                        </button>
                    </form>
                @endcan
            @endif
            @if($invoice->status->canAcceptPayment())
                <a href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}"
                   class="btn btn-success btn-sm">
                    <i class="ki-outline ki-dollar fs-4 me-1"></i>Tambah Pembayaran
                </a>
            @endif
            <button onclick="window.open('{{ route('web.invoices.customer.pdf', $invoice) }}', '_blank')"
                class="btn btn-light-primary btn-sm">
                <i class="ki-outline ki-document fs-4 me-1"></i>PDF
            </button>
            <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light btn-sm">
                <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Kembali
            </a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         PAYMENT SUMMARY SECTION (Finance Engine)
    ═══════════════════════════════════════════════════════════ --}}
    <div class="row g-5 mb-7">
        {{-- Total --}}
        <div class="col-md-3">
            <div class="card h-100" style="background: linear-gradient(135deg, #1b4b7f 0%, #153a63 100%);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <span class="text-white opacity-75 fs-8 fw-bold text-uppercase">Total Tagihan</span>
                    <div class="text-white fs-2x fw-bold mt-2">
                        Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="text-white opacity-60 fs-8 fw-bold">JATUH TEMPO</span>
                        <span class="badge {{ $invoice->isOverdueByDate() ? 'badge-danger' : 'badge-light-warning' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Paid --}}
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <span class="text-gray-600 fs-8 fw-bold text-uppercase">Sudah Dibayar</span>
                    <div class="text-success fs-2x fw-bold mt-2">
                        Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                    </div>
                    @php $pct = $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0; @endphp
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-gray-500 fs-9">Progress</span>
                            <span class="text-success fs-9 fw-bold">{{ number_format($pct, 0) }}%</span>
                        </div>
                        <div class="progress h-6px">
                            <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Outstanding --}}
        <div class="col-md-3">
            <div class="card h-100 {{ $invoice->outstanding_amount > 0 ? 'border border-danger' : '' }}">
                <div class="card-body d-flex flex-column justify-content-between">
                    <span class="text-gray-600 fs-8 fw-bold text-uppercase">Sisa Tagihan</span>
                    <div class="fs-2x fw-bold mt-2 {{ $invoice->outstanding_amount > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
                    </div>
                    @if($invoice->outstanding_amount > 0)
                        <div class="mt-3 p-2 rounded bg-light-danger">
                            <span class="text-danger fs-9 fw-semibold">
                                <i class="ki-outline ki-information-5 fs-9 me-1"></i>
                                Belum lunas
                            </span>
                        </div>
                    @else
                        <div class="mt-3 p-2 rounded bg-light-success">
                            <span class="text-success fs-9 fw-semibold">
                                <i class="ki-outline ki-check-circle fs-9 me-1"></i>
                                Sudah lunas
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Aging --}}
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <span class="text-gray-600 fs-8 fw-bold text-uppercase">Aging Status</span>
                    @php
                        $agingBucket = $invoice->aging_bucket;
                        $agingConfig = [
                            'current' => ['label' => 'Belum Jatuh Tempo', 'color' => 'success', 'icon' => 'check-circle'],
                            '1-30'    => ['label' => '1–30 Hari Lewat',   'color' => 'warning', 'icon' => 'time'],
                            '31-60'   => ['label' => '31–60 Hari Lewat',  'color' => 'danger',  'icon' => 'information-5'],
                            '61-90'   => ['label' => '61–90 Hari Lewat',  'color' => 'danger',  'icon' => 'cross-circle'],
                            '90+'     => ['label' => '>90 Hari Lewat',    'color' => 'dark',    'icon' => 'skull'],
                        ];
                        $aging = $agingConfig[$agingBucket] ?? $agingConfig['current'];
                    @endphp
                    <div class="mt-2">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ki-outline ki-{{ $aging['icon'] }} fs-2 text-{{ $aging['color'] }}"></i>
                            <span class="fs-5 fw-bold text-{{ $aging['color'] }}">{{ $aging['label'] }}</span>
                        </div>
                        @if($invoice->days_overdue > 0)
                            <span class="badge badge-light-{{ $aging['color'] }} fs-7 px-3 py-2">
                                {{ $invoice->days_overdue }} hari terlambat
                            </span>
                        @else
                            <span class="badge badge-light-success fs-7 px-3 py-2">On Time</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        {{-- Aging bar visual --}}
                        @php
                            $agingPct = match($agingBucket) {
                                'current' => 10,
                                '1-30'    => 35,
                                '31-60'   => 60,
                                '61-90'   => 80,
                                '90+'     => 100,
                                default   => 0,
                            };
                        @endphp
                        <div class="progress h-6px">
                            <div class="progress-bar bg-{{ $aging['color'] }}" style="width: {{ $agingPct }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-gray-400 fs-9">0</span>
                            <span class="text-gray-400 fs-9">30</span>
                            <span class="text-gray-400 fs-9">60</span>
                            <span class="text-gray-400 fs-9">90+</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bill To & References --}}
    <div class="row mb-7">
        <div class="col-lg-4 mb-5 mb-lg-0">
            <div class="card border-primary h-100">
                <div class="card-header bg-light-primary">
                    <h3 class="card-title text-primary fw-bold">
                        <i class="ki-outline ki-geolocation fs-2 me-2"></i>TAGIHAN KEPADA
                    </h3>
                </div>
                <div class="card-body">
                    <div class="fs-4 fw-bold text-gray-900 mb-2">{{ $invoice->organization?->name ?? '—' }}</div>
                    @if($invoice->organization?->address)
                        <div class="text-gray-600 fs-6 mb-1">
                            <i class="ki-outline ki-geolocation fs-6 me-1"></i>{{ $invoice->organization->address }}
                        </div>
                    @endif
                    @if($invoice->organization?->phone)
                        <div class="text-gray-600 fs-6 mb-1">
                            <i class="ki-outline ki-phone fs-6 me-1"></i>{{ $invoice->organization->phone }}
                        </div>
                    @endif
                    @if($invoice->organization?->email)
                        <div class="text-gray-600 fs-6">
                            <i class="ki-outline ki-sms fs-6 me-1"></i>{{ $invoice->organization->email }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bank Account --}}
        <div class="col-lg-4 mb-5 mb-lg-0">
            <div class="card border-success h-100">
                <div class="card-header bg-light-success">
                    <h3 class="card-title text-success fw-bold">
                        <i class="ki-outline ki-bank fs-2 me-2"></i>REKENING TUJUAN TRANSFER
                    </h3>
                </div>
                <div class="card-body">
                    @if($invoice->bankAccount)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="symbol symbol-45px">
                                <div class="symbol-label bg-light-success text-success fw-bold fs-5">
                                    {{ strtoupper(substr($invoice->bankAccount->bank_name, 0, 2)) }}
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-900 fs-5">{{ $invoice->bankAccount->bank_name }}</div>
                                <div class="text-muted fs-7">Bank Tujuan</div>
                            </div>
                        </div>
                        <div class="p-3 rounded bg-light-success mb-2">
                            <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Nomor Rekening</div>
                            <div class="fw-bold text-gray-900 fs-4 font-monospace">{{ $invoice->bankAccount->account_number }}</div>
                        </div>
                        <div class="text-gray-600 fs-7">
                            <i class="ki-outline ki-profile-user fs-7 me-1"></i>
                            Atas nama: <span class="fw-semibold text-gray-800">{{ $invoice->bankAccount->account_holder_name }}</span>
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
                            <i class="ki-outline ki-bank fs-3x text-gray-300 mb-3"></i>
                            <span class="text-gray-500 fs-7 text-center">Rekening belum ditentukan.<br>Hubungi Medikindo untuk informasi transfer.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="ki-outline ki-document fs-2 me-2"></i>Dokumen Referensi</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @if($invoice->goods_receipt_id)
                            <a href="{{ route('web.goods-receipts.show', $invoice->goods_receipt_id) }}"
                                class="d-flex align-items-center justify-content-between p-3 rounded bg-light-success">
                                <div class="d-flex flex-column">
                                    <span class="text-gray-600 fs-7">Nomor GR</span>
                                    <span class="text-gray-900 fw-bold">{{ $invoice->goodsReceipt?->gr_number ?? '—' }}</span>
                                </div>
                                <i class="ki-outline ki-arrow-right fs-4 text-success"></i>
                            </a>
                        @endif
                        <a href="{{ route('web.po.show', $invoice->purchase_order_id) }}"
                            class="d-flex align-items-center justify-content-between p-3 rounded bg-light-primary">
                            <div class="d-flex flex-column">
                                <span class="text-gray-600 fs-7">Nomor PO Internal</span>
                                <span class="text-gray-900 fw-bold">{{ $invoice->purchaseOrder?->po_number ?? '—' }}</span>
                            </div>
                            <i class="ki-outline ki-arrow-right fs-4 text-primary"></i>
                        </a>
                        <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light">
                            <div class="d-flex flex-column">
                                <span class="text-gray-600 fs-7">Tanggal Invoice</span>
                                <span class="text-gray-900 fw-bold">{{ $invoice->created_at->format('d M Y') }}</span>
                            </div>
                            <i class="ki-outline ki-calendar fs-4 text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title"><i class="ki-outline ki-package fs-2 me-2"></i>Rincian Barang</h3>
            <div class="card-toolbar">
                <span class="badge badge-light-success fs-7">
                    <i class="ki-outline ki-verify fs-7 me-1"></i>Data dari GR
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                    <thead class="bg-light">
                        <tr class="fw-bold text-muted fs-7 text-uppercase">
                            <th class="ps-5 w-40px">No</th>
                            <th>Nama Produk</th>
                            <th class="text-center">No. Batch</th>
                            <th class="text-center">Tgl. Kadaluarsa</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-center">Diskon</th>
                            <th class="text-end pe-5">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->lineItems as $index => $item)
                            <tr>
                                <td class="ps-5 text-gray-600">{{ $index + 1 }}</td>
                                <td><span class="text-gray-900 fw-bold">{{ $item->product_name }}</span></td>
                                <td class="text-center">
                                    <span class="badge badge-light-primary">{{ $item->batch_no ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item->expiry_date)
                                        @php
                                            $isExpired = $item->expiry_date->isPast();
                                            $isSoon = !$isExpired && $item->expiry_date->diffInDays(now()) <= 90;
                                        @endphp
                                        <span class="badge badge-light-{{ $isExpired ? 'danger' : ($isSoon ? 'warning' : 'success') }}">
                                            {{ $item->expiry_date->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                <td class="text-center text-gray-600">{{ $item->unit ?? 'pcs' }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($item->discount_percentage > 0)
                                        <span class="badge badge-light-warning">{{ number_format($item->discount_percentage, 1) }}%</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-5 fw-bold text-gray-900">
                                    Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10">
                                    <x-empty-state icon="package" title="Tidak Ada Item" message="Tidak ada rincian barang." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Bottom Row: Payment History + Pricing Summary --}}
    <div class="row">
        {{-- Payment History --}}
        <div class="col-lg-7 mb-7">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-entrance-right fs-2 me-2"></i>Riwayat Pembayaran
                    </h3>
                    @if($invoice->status->canAcceptPayment())
                        <div class="card-toolbar">
                            <a href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}"
                                class="btn btn-sm btn-primary">
                                <i class="ki-outline ki-dollar fs-4 me-1"></i>Tambah Pembayaran
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Payment progress bar --}}
                    @if($invoice->total_amount > 0)
                        @php $pct = ($invoice->paid_amount / $invoice->total_amount) * 100; @endphp
                        <div class="mb-5">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-gray-600 fs-7">Progress Pembayaran</span>
                                <span class="fw-bold fs-7 {{ $pct >= 100 ? 'text-success' : 'text-primary' }}">
                                    {{ number_format($pct, 0) }}%
                                </span>
                            </div>
                            <div class="progress h-8px rounded">
                                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}"
                                     style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-gray-500 fs-9">Rp 0</span>
                                <span class="text-gray-500 fs-9">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

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
                                            <span class="text-gray-800 fw-bold">
                                                {{ $alloc->payment?->payment_number ?? 'PAY-' . $alloc->id }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-secondary">
                                                {{ strtoupper($alloc->payment?->payment_method ?? '—') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold">
                                                + Rp {{ number_format($alloc->allocated_amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="text-gray-600">{{ $alloc->created_at->format('d/m/Y') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-entrance-right fs-3x text-gray-300 mb-3"></i>
                                                <span class="text-gray-600 fw-semibold">Belum Ada Pembayaran</span>
                                                @if($invoice->status->canAcceptPayment())
                                                    <a href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}"
                                                        class="btn btn-sm btn-light-primary mt-3">
                                                        <i class="ki-outline ki-dollar fs-4 me-1"></i>Input Pembayaran
                                                    </a>
                                                @endif
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

        {{-- Pricing Summary --}}
        <div class="col-lg-5 mb-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ki-outline ki-calculator fs-2 me-2"></i>Ringkasan Harga</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                            <span class="text-gray-600 fs-6">Subtotal</span>
                            <span class="text-gray-900 fw-semibold">Rp {{ number_format($invoice->subtotal_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @if(($invoice->discount_amount ?? 0) > 0)
                            <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                <span class="text-gray-600 fs-6">Diskon</span>
                                <span class="text-danger fw-semibold">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if(($invoice->tax_amount ?? 0) > 0)
                            <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                <span class="text-gray-600 fs-6">PPN (11%)</span>
                                <span class="text-gray-900 fw-semibold">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if(($invoice->surcharge ?? 0) > 0)
                            <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                <span class="text-gray-600 fs-6">Surcharge</span>
                                <span class="text-primary fw-semibold">Rp {{ number_format($invoice->surcharge, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if(($invoice->ematerai_fee ?? 0) > 0)
                            <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                <span class="text-gray-600 fs-6">e-Meterai</span>
                                <span class="text-gray-900 fw-semibold">Rp {{ number_format($invoice->ematerai_fee, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-3 px-4 rounded bg-light-primary">
                            <span class="text-primary fw-bold fs-5">TOTAL TAGIHAN</span>
                            <span class="text-primary fw-bold fs-4">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="separator my-1"></div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-gray-600 fs-6">Sudah Dibayar</span>
                            <span class="text-success fw-semibold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        @php $outstanding = $invoice->outstanding_amount; @endphp
                        <div class="d-flex justify-content-between py-3 px-4 rounded {{ $outstanding > 0 ? 'bg-light-danger' : 'bg-light-success' }}">
                            <span class="fw-bold fs-6 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">Sisa Tagihan</span>
                            <span class="fw-bold fs-5 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($outstanding, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if($invoice->notes)
                <div class="card mt-5">
                    <div class="card-body">
                        <span class="text-gray-600 fs-7 fw-bold">Catatan Invoice</span>
                        <p class="text-gray-700 fs-6 mt-2 fst-italic">"{{ $invoice->notes }}"</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
