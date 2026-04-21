@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-7">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0">{{ $goodsReceipt->gr_number }}</h1>
                @if($goodsReceipt->status === 'completed')
                    <span class="badge badge-light-success fs-7">
                        <i class="ki-outline ki-check-circle fs-7 me-1"></i>Selesai
                    </span>
                @else
                    <span class="badge badge-light-warning fs-7">
                        <i class="ki-outline ki-time fs-7 me-1"></i>Sebagian Diterima
                    </span>
                @endif
            </div>
            <p class="text-gray-600 fs-6 mb-0">
                PO: <a href="{{ route('web.po.show', $goodsReceipt->purchase_order_id) }}"
                       class="fw-semibold text-primary text-hover-primary">
                    {{ $goodsReceipt->purchaseOrder?->po_number }}
                </a>
                &nbsp;·&nbsp; {{ $goodsReceipt->purchaseOrder?->supplier?->name }}
                &nbsp;·&nbsp; {{ $goodsReceipt->deliveries->count() }} pengiriman
            </p>
        </div>
        <div class="d-flex gap-2">
            {{-- Tombol tambah pengiriman hanya jika masih partial --}}
            @if($goodsReceipt->status === 'partial')
                @can('create', \App\Models\GoodsReceipt::class)
                    <a href="{{ route('web.goods-receipts.create', ['purchase_order_id' => $goodsReceipt->purchase_order_id]) }}"
                       class="btn btn-primary btn-sm">
                        <i class="ki-outline ki-plus fs-4 me-1"></i>Tambah Pengiriman
                    </a>
                @endcan
            @endif
            <a href="{{ route('web.goods-receipts.pdf', $goodsReceipt) }}" target="_blank"
               class="btn btn-light-primary btn-sm">
                <i class="ki-outline ki-document fs-4 me-1"></i>PDF
            </a>
            <a href="{{ route('web.goods-receipts.index') }}" class="btn btn-light btn-sm">
                <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Kembali
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-5 mb-7">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-gray-500 fs-8 fw-bold text-uppercase d-block mb-2">Organisasi</span>
                    <span class="text-gray-900 fw-bold fs-6">{{ $goodsReceipt->purchaseOrder?->organization?->name ?? '—' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-gray-500 fs-8 fw-bold text-uppercase d-block mb-2">Supplier</span>
                    <span class="text-gray-900 fw-bold fs-6">{{ $goodsReceipt->purchaseOrder?->supplier?->name ?? '—' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-gray-500 fs-8 fw-bold text-uppercase d-block mb-2">Diterima Oleh</span>
                    <span class="text-gray-900 fw-bold fs-6 d-block">{{ $goodsReceipt->receivedBy?->name ?? '—' }}</span>
                    <span class="text-muted fs-7">{{ $goodsReceipt->received_date->format('d M Y') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-gray-500 fs-8 fw-bold text-uppercase d-block mb-2">Total Pengiriman</span>
                    <span class="text-gray-900 fw-bold fs-2x">{{ $goodsReceipt->deliveries->count() }}</span>
                    <span class="text-muted fs-7 ms-1">sesi</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Penerimaan --}}
    @php
        $po = $goodsReceipt->purchaseOrder;
        $totalPoQty = $po->items->sum('quantity');
        $totalReceivedQty = $goodsReceipt->items->sum('quantity_received');
        $pct = $totalPoQty > 0 ? ($totalReceivedQty / $totalPoQty) * 100 : 0;
    @endphp
    <div class="card mb-7">
        <div class="card-body py-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-gray-700 fw-bold fs-6">Progress Penerimaan Keseluruhan</span>
                    <span class="text-muted fs-7 ms-2">{{ $totalReceivedQty }} / {{ $totalPoQty }} unit</span>
                </div>
                <span class="fw-bold fs-5 {{ $pct >= 100 ? 'text-success' : 'text-warning' }}">
                    {{ number_format($pct, 0) }}%
                </span>
            </div>
            <div class="progress h-12px rounded">
                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-warning' }}"
                     style="width: {{ min($pct, 100) }}%"></div>
            </div>
            @if($goodsReceipt->status === 'partial')
                <div class="mt-2 text-warning fs-7 fw-semibold">
                    <i class="ki-outline ki-information-5 fs-7 me-1"></i>
                    Masih ada {{ $totalPoQty - $totalReceivedQty }} unit yang belum diterima.
                </div>
            @endif
        </div>
    </div>

    {{-- Riwayat Pengiriman per Sesi --}}
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-truck fs-2 me-2"></i>
                Riwayat Pengiriman
            </h3>
            <div class="card-toolbar">
                <span class="badge badge-light-primary">{{ $goodsReceipt->deliveries->count() }} pengiriman</span>
            </div>
        </div>
        <div class="card-body p-0">
            @forelse($goodsReceipt->deliveries as $delivery)
                <div class="border-bottom border-gray-200 p-6 {{ !$loop->last ? '' : '' }}">
                    {{-- Delivery Header --}}
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label bg-light-primary">
                                    <span class="text-primary fw-bold fs-6">{{ $delivery->delivery_sequence }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-900 fs-6">
                                    Pengiriman ke-{{ $delivery->delivery_sequence }}
                                </div>
                                <div class="text-muted fs-7">
                                    DO: <span class="fw-semibold text-gray-700">{{ $delivery->delivery_number }}</span>
                                    &nbsp;·&nbsp;
                                    {{ \Carbon\Carbon::parse($delivery->received_date)->format('d M Y') }}
                                    &nbsp;·&nbsp;
                                    Diterima oleh: {{ $delivery->receivedBy?->name ?? '—' }}
                                </div>
                            </div>
                        </div>
                        {{-- Foto bukti --}}
                        @if($delivery->photo_path)
                            <a href="{{ asset('storage/' . $delivery->photo_path) }}" target="_blank"
                               class="btn btn-sm btn-light-info">
                                <i class="ki-outline ki-picture fs-4 me-1"></i>Lihat Foto
                            </a>
                        @endif
                    </div>

                    {{-- Items per delivery --}}
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-2 mb-0">
                            <thead class="bg-light">
                                <tr class="fw-bold text-muted fs-8 text-uppercase">
                                    <th class="ps-4">Produk</th>
                                    <th class="text-center">Qty Diterima</th>
                                    <th class="text-center">Batch No</th>
                                    <th class="text-center">Kadaluarsa</th>
                                    <th class="text-center">Kondisi</th>
                                    <th class="pe-4">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($delivery->items as $dItem)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-gray-800 fs-7">
                                                {{ $dItem->purchaseOrderItem?->product?->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light-primary fw-bold">
                                                {{ $dItem->quantity_received }} unit
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light-secondary">{{ $dItem->batch_no ?? '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($dItem->expiry_date)
                                                @php
                                                    $isExpired = \Carbon\Carbon::parse($dItem->expiry_date)->isPast();
                                                    $isSoon = !$isExpired && \Carbon\Carbon::parse($dItem->expiry_date)->diffInDays(now()) <= 90;
                                                @endphp
                                                <span class="badge badge-light-{{ $isExpired ? 'danger' : ($isSoon ? 'warning' : 'success') }}">
                                                    {{ \Carbon\Carbon::parse($dItem->expiry_date)->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php $cond = strtolower($dItem->condition ?? 'good'); @endphp
                                            @if(in_array($cond, ['good', 'baik sempurna', 'baik']))
                                                <span class="badge badge-light-success">Baik</span>
                                            @elseif($cond === 'minor damage')
                                                <span class="badge badge-light-warning">Rusak Ringan</span>
                                            @else
                                                <span class="badge badge-light-danger">Rusak Parah</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-muted fs-8">{{ $dItem->notes ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($delivery->notes)
                        <div class="mt-3 p-3 rounded bg-light-info">
                            <i class="ki-outline ki-information-5 fs-7 text-info me-1"></i>
                            <span class="text-gray-700 fs-7">{{ $delivery->notes }}</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-10">
                    <x-empty-state icon="truck" title="Belum Ada Pengiriman" message="Belum ada data pengiriman." />
                </div>
            @endforelse
        </div>
    </div>

    {{-- Rekap Akumulasi per Item PO --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-package fs-2 me-2"></i>
                Rekap Akumulasi Penerimaan
            </h3>
            <div class="card-toolbar">
                <span class="text-muted fs-7">Total dari semua pengiriman</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                    <thead class="bg-light">
                        <tr class="fw-bold text-muted fs-7 text-uppercase">
                            <th class="ps-5">Produk</th>
                            <th class="text-center">Dipesan (PO)</th>
                            <th class="text-center">Total Diterima</th>
                            <th class="text-center">Sisa</th>
                            <th class="text-center">Progress</th>
                            <th class="text-center pe-5">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->items as $poItem)
                            @php
                                // Hitung dari GR items yang terkait GR ini saja
                                $grItem = $goodsReceipt->items
                                    ->firstWhere('purchase_order_item_id', $poItem->id);
                                $received = $grItem?->quantity_received ?? 0;
                                $ordered  = $poItem->quantity;
                                $sisa     = max(0, $ordered - $received);
                                $itemPct  = $ordered > 0 ? ($received / $ordered) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="ps-5">
                                    <span class="fw-bold text-gray-900 fs-6">{{ $poItem->product?->name ?? '—' }}</span>
                                    <div class="text-muted fs-8">SKU: {{ $poItem->product?->sku ?? '—' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-gray-700">{{ $ordered }}</span>
                                    <span class="text-muted fs-8 ms-1">unit</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-success fs-5">{{ $received }}</span>
                                    <span class="text-muted fs-8 ms-1">unit</span>
                                </td>
                                <td class="text-center">
                                    @if($sisa > 0)
                                        <span class="fw-bold text-warning">{{ $sisa }}</span>
                                        <span class="text-muted fs-8 ms-1">unit</span>
                                    @else
                                        <span class="text-success fw-bold">—</span>
                                    @endif
                                </td>
                                <td class="text-center" style="min-width: 120px;">
                                    <div class="progress h-6px mb-1">
                                        <div class="progress-bar {{ $itemPct >= 100 ? 'bg-success' : 'bg-warning' }}"
                                             style="width: {{ min($itemPct, 100) }}%"></div>
                                    </div>
                                    <span class="text-muted fs-9">{{ number_format($itemPct, 0) }}%</span>
                                </td>
                                <td class="text-center pe-5">
                                    @if($sisa <= 0)
                                        <span class="badge badge-light-success fw-bold">
                                            <i class="ki-outline ki-check-circle fs-8 me-1"></i>Terpenuhi
                                        </span>
                                    @else
                                        <span class="badge badge-light-warning fw-bold">
                                            <i class="ki-outline ki-time fs-8 me-1"></i>Kurang {{ $sisa }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
