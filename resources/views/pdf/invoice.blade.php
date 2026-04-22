@extends('pdf.layout')

@php
    $isAR = $type !== 'supplier';
    $typeLabel = $isAR ? 'Account Receivable (AR)' : 'Account Payable (AP)';
    $toTitle   = $isAR ? 'TAGIHAN KEPADA' : 'TAGIHAN KE SUPPLIER';
    $entity    = $isAR
        ? ($invoice->organization?->name ?? '—')
        : ($invoice->supplier?->name ?? '—');
    $entityAddress = $isAR
        ? ($invoice->organization?->address ?? null)
        : ($invoice->supplier?->address ?? null);
    $entityPhone = $isAR
        ? ($invoice->organization?->phone ?? null)
        : ($invoice->supplier?->phone ?? null);
@endphp

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('document_name', $isAR ? 'FAKTUR TAGIHAN' : 'BUKTI FAKTUR KEUANGAN')
@section('document_number', $invoice->invoice_number)
@section('document_date', $invoice->created_at->format('d F Y'))

@section('content')

    {{-- Header Info Section --}}
    <table class="info-section">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="info-box">
                    <div class="info-title">Detail Invoice</div>
                    <strong>Klasifikasi: {{ $typeLabel }}</strong><br>
                    Tanggal Invoice: <strong>{{ $invoice->created_at->format('d F Y') }}</strong><br>
                    Jatuh Tempo: <strong style="color: red;">{{ $invoice->due_date?->format('d F Y') ?? '—' }}</strong><br>
                    Status: <strong style="text-transform: uppercase;">{{ $invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->status }}</strong>
                    @if($isAR && $invoice->goods_receipt_id)
                        <br><span style="color: green; font-size: 10px;">✓ Berdasarkan Penerimaan Barang (GR)</span>
                    @endif
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="info-box">
                    <div class="info-title">{{ $toTitle }}</div>
                    <strong style="font-size: 13px;">{{ $entity }}</strong>
                    @if($entityAddress)
                        <br><span style="font-size: 10px; color: #555;">{{ $entityAddress }}</span>
                    @endif
                    @if($entityPhone)
                        <br><span style="font-size: 10px; color: #555;">Telp: {{ $entityPhone }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Reference Section --}}
    <table class="data-table" style="margin-bottom: 15px;">
        <thead>
            <tr>
                <th colspan="4">Referensi Dokumen</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 25%; font-weight: bold; color: #555; font-size: 10px;">PO Internal</td>
                <td style="width: 25%;">{{ $invoice->purchaseOrder?->po_number ?? '—' }}</td>
                <td style="width: 25%; font-weight: bold; color: #555; font-size: 10px;">PO RS/Klinik</td>
                <td style="width: 25%;">{{ $invoice->purchaseOrder?->external_po_number ?? '—' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #555; font-size: 10px;">Nomor GR</td>
                <td>{{ $invoice->goodsReceipt?->gr_number ?? '—' }}</td>
                <td style="font-weight: bold; color: #555; font-size: 10px;">Tanggal GR</td>
                <td>{{ $invoice->goodsReceipt?->received_at?->format('d M Y') ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Item Table --}}
    @if($invoice->lineItems && $invoice->lineItems->count() > 0)
    <div style="margin-bottom: 15px;">
        <h3 style="font-size: 12px; font-weight: bold; margin-bottom: 8px; border-bottom: 2px solid #333; padding-bottom: 4px; text-transform: uppercase;">
            Rincian Barang
        </h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%; text-align: center;">No</th>
                    <th style="width: 22%;">Nama Produk</th>
                    <th style="width: 12%; text-align: center;">No. Batch</th>
                    <th style="width: 11%; text-align: center;">Kadaluarsa</th>
                    <th style="width: 6%; text-align: center;">Qty</th>
                    <th style="width: 6%; text-align: center;">Sat.</th>
                    <th style="width: 14%; text-align: right;">Harga Satuan</th>
                    <th style="width: 8%; text-align: center;">Diskon</th>
                    <th style="width: 17%; text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lineItems as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td><strong>{{ $item->product_name }}</strong></td>
                    <td style="text-align: center; font-size: 10px;">{{ $item->batch_no ?? '—' }}</td>
                    <td style="text-align: center; font-size: 10px;">
                        {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '—' }}
                    </td>
                    <td style="text-align: center;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td style="text-align: center; font-size: 10px;">{{ $item->unit ?? 'pcs' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align: center;">
                        {{ $item->discount_percentage > 0 ? number_format($item->discount_percentage, 1) . '%' : '—' }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Pricing Summary --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 15px; font-size: 10px; color: #555;">
                @if($isAR)
                    <strong>Instruksi Pembayaran:</strong><br>
                    Mohon transfer sebesar nilai tagihan ke:<br>
                    <strong>Bank BCA: 0987654321</strong><br>
                    a.n PT. Mentari Medika Indonesia<br>
                    Cantumkan Nomor Invoice pada berita transfer.
                @endif
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <tr>
                        <td style="padding: 5px 8px; border-bottom: 1px dotted #ccc;">Subtotal (Sebelum Diskon)</td>
                        <td style="padding: 5px 8px; text-align: right; border-bottom: 1px dotted #ccc;">
                            Rp {{ number_format($invoice->subtotal_amount ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @if(($invoice->discount_amount ?? 0) > 0)
                    <tr>
                        <td style="padding: 5px 8px; border-bottom: 1px dotted #ccc; color: #dc2626;">Total Diskon</td>
                        <td style="padding: 5px 8px; text-align: right; border-bottom: 1px dotted #ccc; color: #dc2626;">
                            - Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    @if(($invoice->tax_amount ?? 0) > 0)
                    <tr>
                        <td style="padding: 5px 8px; border-bottom: 1px dotted #ccc;">PPN (11%)</td>
                        <td style="padding: 5px 8px; text-align: right; border-bottom: 1px dotted #ccc;">
                            Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                    <tr style="background-color: #1e293b; color: white;">
                        <td style="padding: 8px; font-weight: bold; font-size: 12px; text-transform: uppercase;">TOTAL TAGIHAN</td>
                        <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 14px;">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 8px; border-bottom: 1px dotted #ccc; color: green;">Sudah Dibayar</td>
                        <td style="padding: 5px 8px; text-align: right; border-bottom: 1px dotted #ccc; color: green;">
                            Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="background-color: #fef2f2;">
                        <td style="padding: 8px; font-weight: bold; color: #dc2626; text-transform: uppercase;">Sisa Tagihan</td>
                        <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 13px; color: #dc2626;">
                            Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Signature Section --}}
    <div class="footer">
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <tr>
                <td style="width: 50%; text-align: center; padding: 0 20px; vertical-align: top;">
                    <div style="margin-bottom: 50px;">Diterbitkan Oleh,</div>
                    <div style="border-top: 1px solid #333; padding-top: 5px;">
                        <strong>Admin Keuangan</strong><br>
                        <span style="color: #666;">PT. Mentari Medika Indonesia</span><br>
                        <span style="color: #999; font-size: 10px;">Tanggal: _______________</span>
                    </div>
                </td>
                @if($isAR)
                <td style="width: 50%; text-align: center; padding: 0 20px; vertical-align: top;">
                    <div style="margin-bottom: 50px;">Diterima Oleh,</div>
                    <div style="border-top: 1px solid #333; padding-top: 5px;">
                        <strong>Nama & Jabatan</strong><br>
                        <span style="color: #666;">{{ $entity }}</span><br>
                        <span style="color: #999; font-size: 10px;">Tanggal: _______________</span>
                    </div>
                </td>
                @endif
            </tr>
        </table>
    </div>

@endsection
