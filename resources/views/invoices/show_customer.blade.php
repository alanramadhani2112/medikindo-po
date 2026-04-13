@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0">{{ $invoice->invoice_number }}</h1>
                @php
                    $statusColor = match($invoice->status) {
                        'paid' => 'success',
                        'overdue' => 'danger',
                        default => 'warning'
                    };
                @endphp
                <span class="badge badge-{{ $statusColor }}">{{ strtoupper($invoice->status) }}</span>
            </div>
            <p class="text-gray-600 fs-6 mb-0">Customer: <span class="text-gray-900 fw-semibold">{{ $invoice->organization?->name ?? '—' }}</span></p>
        </div>
        <div class="d-flex gap-3">
            <button onclick="window.open('{{ route('web.invoices.customer.pdf', $invoice) }}', '_blank')" 
                    class="btn btn-light-primary">
                <i class="ki-outline ki-printer fs-2"></i>
                Cetak PDF
            </button>
            <a href="{{ route('web.invoices.index') }}" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-2"></i>
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
        {{-- Left Column: Payment History --}}
        <div class="col-lg-8 mb-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-wallet fs-2 me-2"></i>
                        Riwayat Alokasi Pembayaran
                    </h3>
                    @if($invoice->status !== 'paid')
                        <div class="card-toolbar">
                            <a href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="ki-outline ki-plus fs-4"></i>
                                Input Pembayaran Klinik
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
                                    <th class="text-end">Jumlah Terbayar</th>
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
                                                <i class="ki-outline ki-wallet fs-3x text-gray-400 mb-3"></i>
                                                <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Pembayaran</span>
                                                <span class="text-gray-500 fs-6">Pembayaran untuk invoice ini belum diterima atau dialokasikan.</span>
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

        {{-- Right Column: References & Info --}}
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-5">
                {{-- Document References --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-outline ki-document fs-2 me-2"></i>
                            Dokumen Referensi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            @if($invoice->goods_receipt_id)
                                <a href="{{ route('web.goods-receipts.show', $invoice->goods_receipt_id) }}" 
                                   class="d-flex align-items-center justify-content-between p-3 rounded bg-light-primary">
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-600 fs-7">Nomor GR</span>
                                        <span class="text-gray-900 fw-bold">{{ $invoice->goodsReceipt?->gr_number ?? '—' }}</span>
                                    </div>
                                    <i class="ki-outline ki-arrow-right fs-4 text-primary"></i>
                                </a>
                            @else
                                <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light-secondary">
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-600 fs-7">Nomor GR</span>
                                        <span class="text-gray-500 fw-bold">Goods Receipt belum tersedia</span>
                                    </div>
                                    <i class="ki-outline ki-information fs-4 text-muted"></i>
                                </div>
                            @endif
                            <a href="{{ route('web.po.show', $invoice->purchase_order_id) }}" 
                               class="d-flex align-items-center justify-content-between p-3 rounded bg-light-primary">
                                <div class="d-flex flex-column">
                                    <span class="text-gray-600 fs-7">Nomor PO</span>
                                    <span class="text-gray-900 fw-bold">{{ $invoice->purchaseOrder?->po_number ?? '—' }}</span>
                                </div>
                                <i class="ki-outline ki-arrow-right fs-4 text-primary"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Clinical Info --}}
                <div class="card border-primary">
                    <div class="card-header bg-light-primary">
                        <h3 class="card-title text-primary">
                            <i class="ki-outline ki-hospital fs-2 me-2"></i>
                            Informasi Klinis
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-5">
                            <span class="text-gray-600 fs-7 fw-bold">Unit Pengirim</span>
                            <div class="text-gray-900 fw-bold fs-5 mt-1">{{ $invoice->organization?->name ?? '—' }}</div>
                        </div>
                        <div>
                            <span class="text-gray-600 fs-7 fw-bold">Catatan Invoice</span>
                            <p class="text-gray-700 fs-6 mt-1 fst-italic">"{{ $invoice->notes ?? 'Tidak ada catatan.' }}"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection