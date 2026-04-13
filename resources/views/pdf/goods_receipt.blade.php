@extends('pdf.layout')

@section('title', 'Tanda Terima ' . $goodsReceipt->gr_number)
@section('document_name', 'SURAT JALAN & TANDA TERIMA FAKTUR (GR)')
@section('document_number', $goodsReceipt->gr_number)
@section('document_date', $goodsReceipt->received_date->format('d F Y'))

@section('content')

    <table class="info-section">
        <tr>
            <td>
                <div class="info-box">
                    <div class="info-title">Distributor (Pengirim)</div>
                    <strong>{{ $goodsReceipt->purchaseOrder?->supplier?->name ?? '—' }}</strong><br>
                    Tlp: {{ $goodsReceipt->purchaseOrder?->supplier?->contact_phone ?? '—' }}
                </div>
            </td>
            <td>
                <div class="info-box">
                    <div class="info-title">Referensi Pemesanan Terkait</div>
                    <strong>P.O. Number: {{ $goodsReceipt->purchaseOrder?->po_number ?? '—' }}</strong><br>
                    Lokasi: {{ $goodsReceipt->purchaseOrder?->organization?->name ?? 'Gudang Pusat Medikindo' }}<br>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 40%">Informasi Logistik Produk</th>
                <th style="width: 15%" class="text-center">Kuantitas Dipesan (PO)</th>
                <th style="width: 15%" class="text-center">Kuantitas Masuk Gudang (GR)</th>
                <th style="width: 25%" class="text-center">Kondisi & Catatan Verifikasi Fisik</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goodsReceipt->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->purchaseOrderItem?->product?->name ?? '—' }}</strong>
                </td>
                <td class="text-center" style="color: #64748b;">{{ $item->purchaseOrderItem?->quantity ?? 0 }} {{ $item->purchaseOrderItem?->product?->unit ?? 'Unit' }}</td>
                <td class="text-center font-bold">{{ $item->quantity_received }} {{ $item->purchaseOrderItem?->product?->unit ?? 'Unit' }}</td>
                <td>
                    <strong style="{{ $item->condition !== 'Good' ? 'color: red;' : 'color: green;' }}">[{{ strtoupper($item->condition) }}]</strong><br>
                    <span style="font-size: 10px; color: #666;">{{ $item->notes ?? '-' }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div style="float: left; width: 400px; padding-top: 15px;">
            <p style="font-size: 11px;">
                <strong>Keterangan Validasi:</strong><br>
                Barang telah diperiksa kesesuaiannya dengan faktur pengiriman distributor. Tanda terima ini sah dan dapat dilanjutkan ke proses piutang akuntansi (Accounts Payable / Receivable).
            </p>
        </div>
        
        <div class="signature-box">
            <div style="font-size: 11px; margin-bottom: 50px;">Diperiksa & Diterima Oleh,</div>
            <div class="signature-line"></div>
            <div style="font-size: 12px; font-weight: bold;">{{ $goodsReceipt->receivedBy?->name ?? 'Admin Gudang' }}</div>
            <div style="font-size: 10px; color: #666;">Medikindo Logistics Dept.</div>
        </div>
    </div>

@endsection
