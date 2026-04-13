@extends('pdf.layout')

@section('title', 'Purchase Order ' . $po->po_number)
@section('document_name', 'PURCHASE ORDER')
@section('document_number', $po->po_number)
@section('document_date', $po->created_at->format('d F Y'))

@section('content')

    <table class="info-section">
        <tr>
            <td>
                <div class="info-box">
                    <div class="info-title">Pihak Pemesan (Organisasi)</div>
                    <strong>{{ $po->organization?->name ?? '—' }}</strong><br>
                    Tlp: {{ $po->organization?->phone ?? '—' }}<br>
                    Alamat: {{ $po->organization?->address ?? '—' }}
                </div>
            </td>
            <td>
                <div class="info-box">
                    <div class="info-title">Ditujukan Kepada (Distributor)</div>
                    <strong>{{ $po->supplier?->name ?? '—' }}</strong><br>
                    Tlp: {{ $po->supplier?->contact_phone ?? '—' }}<br>
                    Alamat: {{ $po->supplier?->address ?? '—' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 45%">Deskripsi Barang (SKU)</th>
                <th style="width: 15%" class="text-center">Kuantitas</th>
                <th style="width: 15%" class="text-right">Harga Satuan</th>
                <th style="width: 20%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->product?->name ?? '—' }}</strong><br>
                    <span style="font-size: 10px; color: #666;">SKU: {{ $item->product?->sku ?? '—' }}</span>
                </td>
                <td class="text-center">{{ $item->quantity }} {{ $item->product?->unit ?? 'Unit' }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right font-bold text-lg">TOTAL BIAYA:</td>
                <td class="text-right font-bold text-lg text-blue">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px;">
        <strong>Catatan Tambahan:</strong><br>
        <span style="color: #666;">{{ $po->notes ?? 'Tidak ada catatan.' }}</span>
    </div>

    <div class="footer">
        <div style="float: left; width: 300px; padding-top: 15px;">
            <p style="font-size: 11px;">
                Dicetak pada: {{ now()->format('d M Y H:i') }}<br>
                Status Transaksi: <strong style="text-transform: uppercase;">{{ str_replace('_', ' ', $po->status) }}</strong>
            </p>
        </div>
        
        <div class="signature-box">
            <div style="font-size: 11px; margin-bottom: 50px;">Otorisasi Pemesanan,</div>
            <div class="signature-line"></div>
            <div style="font-size: 12px; font-weight: bold;">{{ $po->createdBy?->name ?? 'System Admin' }}</div>
            <div style="font-size: 10px; color: #666;">Medikindo Procurement Officer</div>
        </div>
    </div>

@endsection
