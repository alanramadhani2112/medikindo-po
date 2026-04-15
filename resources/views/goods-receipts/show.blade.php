<x-layout :title="'GR ' . $goodsReceipt->gr_number" :pageTitle="$goodsReceipt->gr_number" breadcrumb="Tanda Terima Logistik">

    <x-page-header :title="$goodsReceipt->gr_number" description="Detail penerimaan barang">
        <x-slot name="actions">
            <x-badge variant="success">COMPLETED</x-badge>
            <a href="{{ route('web.goods-receipts.pdf', $goodsReceipt) }}" target="_blank" 
               class="btn btn-light-primary btn-sm">
                <i class="
ki-outline ki-document fs-3"></i>
                PDF
            </a>
            <a href="{{ route('web.goods-receipts.index') }}" class="btn btn-light-secondary btn-sm">
                <i class="ki-outline ki-arrow-down fs-3"></i>
                Kembali
            </a>
        </x-slot>
    </x-page-header>

    {{-- Informasi Penerimaan --}}
    <x-card title="Informasi Penerimaan" class="mb-5">
        <div class="row g-5">
            <div class="col-md-3">
                <span class="text-muted fs-7 fw-semibold d-block mb-2">Referensi PO</span>
                <a href="{{ route('web.po.show', $goodsReceipt->purchase_order_id) }}" 
                   class="fs-6 fw-bold text-primary text-hover-primary">
                    {{ $goodsReceipt->purchaseOrder?->po_number }}
                </a>
            </div>
            <div class="col-md-3">
                <span class="text-muted fs-7 fw-semibold d-block mb-2">Organisasi Tujuan</span>
                <span class="fs-6 fw-bold text-gray-800">{{ $goodsReceipt->purchaseOrder?->organization?->name ?? '—' }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted fs-7 fw-semibold d-block mb-2">Supplier</span>
                <span class="fs-6 fw-bold text-gray-800">{{ $goodsReceipt->purchaseOrder?->supplier?->name ?? '—' }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted fs-7 fw-semibold d-block mb-2">Diterima Oleh</span>
                <span class="fs-6 fw-bold text-gray-800 d-block">{{ $goodsReceipt->receivedBy?->name ?? '—' }}</span>
                <span class="text-muted fs-7">
                    <i class="ki-outline ki-calendar fs-7 me-1"></i>
                    {{ $goodsReceipt->received_date->format('d M Y') }}
                </span>
            </div>
        </div>
    </x-card>

    {{-- Rincian Fisik Barang --}}
    <x-card title="Rincian Fisik Barang">
        <x-slot name="actions">
            <span class="badge badge-light-primary">{{ $goodsReceipt->items->count() }} Line Item(s)</span>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                <thead>
                    <tr class="fw-bold text-muted bg-light">
                        <th class="ps-4 rounded-start">Produk</th>
                        <th class="text-end">Dipesan (PO)</th>
                        <th class="text-end">Masuk (GR)</th>
                        <th class="text-center">Selisih</th>
                        <th class="text-center">Kondisi</th>
                        <th class="pe-4 rounded-end">Catatan Gudang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($goodsReceipt->items as $item)
                        @php
                            $poQty = $item->purchaseOrderItem?->quantity ?? 0;
                            $grQty = $item->quantity_received;
                            $diff = $poQty - $grQty;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-gray-900 fs-6">{{ $item->purchaseOrderItem?->product?->name ?? '—' }}</span>
                                    <span class="text-muted fs-7">SKU: {{ $item->purchaseOrderItem?->product?->sku ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-gray-600">{{ $poQty }} units</span>
                            </td>
                            <td class="text-end bg-light-primary">
                                <span class="fw-bold text-primary fs-5">{{ $grQty }} units</span>
                            </td>
                            <td class="text-center">
                                @if($diff > 0)
                                    <x-badge variant="danger">−{{ $diff }} (Kurang)</x-badge>
                                @elseif($diff < 0)
                                    <x-badge variant="warning">+{{ abs($diff) }} (Lebih)</x-badge>
                                @else
                                    <x-badge variant="success">Sesuai</x-badge>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->condition === 'Good' || $item->condition === 'Baik Sempurna' || $item->condition === 'Baik')
                                    <span class="text-success fw-bold">
                                        <i class="ki-outline ki-check-circle fs-3"></i>
                                        Baik
                                    </span>
                                @else
                                    <span class="text-danger fw-bold">{{ $item->condition }}</span>
                                @endif
                            </td>
                            <td class="pe-4">
                                <span class="text-muted">{{ $item->notes ?? '—' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>

</x-layout>
