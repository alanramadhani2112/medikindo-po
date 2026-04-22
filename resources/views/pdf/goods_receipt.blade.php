<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Goods Receipt {{ $goodsReceipt->gr_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
            padding: 15px 20px;
        }
        .watermark {
            position: fixed;
            top: 35%;
            left: -5%;
            width: 120%;
            text-align: center;
            font-size: 52px;
            font-weight: bold;
            color: #000;
            opacity: 0.07;
            transform: rotate(-45deg);
            z-index: -1000;
            white-space: nowrap;
            letter-spacing: 4px;
        }
        .header-table { width: 100%; border-bottom: 2px solid #1B4B7F; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; padding: 4px 6px; border: none; }
        .header-company { font-size: 9px; color: #444; line-height: 1.5; }
        .header-company strong { font-size: 12px; color: #1B4B7F; }
        .header-title { text-align: right; font-size: 20px; font-weight: bold; color: #1B4B7F; letter-spacing: 2px; }
        .info-table { width: 100%; margin-bottom: 10px; border: 1px solid #ccc; }
        .info-table td { vertical-align: top; padding: 6px 8px; border: none; }
        .info-table .divider { border-right: 1px solid #ccc; }
        .section-label { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #1B4B7F; border-bottom: 1px solid #ddd; padding-bottom: 3px; margin-bottom: 5px; }
        .meta-row td { padding: 2px 4px; border: none; font-size: 9px; }
        .meta-key { color: #666; width: 110px; }
        .meta-val { font-weight: bold; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9px; }
        .items-table th { background-color: #1B4B7F; color: #fff; padding: 5px 4px; text-align: center; border: 1px solid #1B4B7F; font-size: 9px; }
        .items-table td { padding: 4px; border: 1px solid #ddd; vertical-align: middle; }
        .items-table tr:nth-child(even) td { background-color: #f7f9fc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .signature-section { page-break-inside: avoid; margin-bottom: 10px; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { width: 33.33%; text-align: center; border: 1px solid #ccc; padding: 8px 4px 6px; font-size: 9px; vertical-align: bottom; }
        .sig-label { font-weight: bold; font-size: 9px; color: #1B4B7F; display: block; margin-bottom: 40px; }
        .sig-line { border-top: 1px solid #333; margin: 0 10px; padding-top: 3px; font-size: 8px; color: #666; }
        .footer-table { width: 100%; margin-top: 8px; border-top: 1px solid #ddd; padding-top: 6px; }
        .footer-table td { vertical-align: middle; border: none; padding: 2px 4px; }
        .print-log { font-size: 8px; color: #888; text-align: right; }
        .status-good { color: #16a34a; font-weight: bold; }
        .status-bad { color: #dc2626; font-weight: bold; }
        .validation-box { border-left: 3px solid #1B4B7F; padding: 5px 8px; font-size: 9px; margin-bottom: 10px; background: #f0f4ff; }
    </style>
</head>
<body>

<div class="watermark">GOODS RECEIPT</div>

{{-- HEADER --}}
@php
    $logoPath = public_path(config('company.logo', 'logo-medikindo.png'));
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
    $po = $goodsReceipt->purchaseOrder;
@endphp
<table class="header-table">
    <tr>
        <td style="width: 80px;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="MMI" style="max-width: 75px; max-height: 50px;">
            @else
                <div style="font-size: 14px; font-weight: bold; color: #1B4B7F;">MMI</div>
            @endif
        </td>
        <td class="header-company">
            <strong>{{ config('company.name_upper') }}</strong><br>
            NPWP: {{ config('company.npwp') }}<br>
            Izin PBF: {{ config('company.pbf_license') }}<br>
            {{ config('company.address') }}<br>
            Telp: {{ config('company.phone') }} | Fax: {{ config('company.fax') }}
        </td>
        <td class="header-title">
            GOODS RECEIPT
        </td>
    </tr>
</table>

{{-- INFO: Distributor & Referensi --}}
<table class="info-table">
    <tr>
        <td class="divider" style="width: 50%;">
            <div class="section-label">Distributor (Pengirim)</div>
            <strong style="font-size: 11px;">{{ $po?->supplier?->name ?? '—' }}</strong><br>
            @if($po?->supplier?->contact_phone)
                Telp: {{ $po->supplier->contact_phone }}<br>
            @endif
            @if($po?->supplier?->address)
                {{ $po->supplier->address }}
            @endif
        </td>
        <td style="width: 50%;">
            <div class="section-label">Detail GR</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr class="meta-row">
                    <td class="meta-key">No. GR</td>
                    <td>:</td>
                    <td class="meta-val">{{ $goodsReceipt->gr_number }}</td>
                    <td class="meta-key">No. PO</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po?->po_number ?? '—' }}</td>
                </tr>
                <tr class="meta-row">
                    <td class="meta-key">Tgl. Terima</td>
                    <td>:</td>
                    <td class="meta-val">{{ $goodsReceipt->received_date->format('d/m/Y') }}</td>
                    <td class="meta-key">Organisasi</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po?->organization?->name ?? '—' }}</td>
                </tr>
                <tr class="meta-row">
                    <td class="meta-key">Status GR</td>
                    <td>:</td>
                    <td class="meta-val" style="text-transform: uppercase;">{{ $goodsReceipt->status }}</td>
                    <td class="meta-key">Diterima Oleh</td>
                    <td>:</td>
                    <td class="meta-val">{{ $goodsReceipt->receivedBy?->name ?? '—' }}</td>
                </tr>
                @if($goodsReceipt->do_number)
                <tr class="meta-row">
                    <td class="meta-key">No. DO</td>
                    <td>:</td>
                    <td class="meta-val" colspan="3">{{ $goodsReceipt->do_number }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

{{-- TABEL ITEM --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 30%; text-align: left;">Nama Produk</th>
            <th style="width: 12%;">No. Batch</th>
            <th style="width: 10%;">Kadaluarsa</th>
            <th style="width: 10%;">Qty PO</th>
            <th style="width: 10%;">Qty Diterima</th>
            <th style="width: 7%;">Satuan</th>
            <th style="width: 17%;">Kondisi & Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($goodsReceipt->items as $index => $item)
        @php
            $product = $item->product ?? $item->purchaseOrderItem?->product;
            $poItem  = $item->purchaseOrderItem;
        @endphp
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-left"><strong>{{ $product?->name ?? '—' }}</strong></td>
            <td class="text-center">
                <span style="background-color: #dbeafe; padding: 1px 5px; border-radius: 3px; font-size: 8px;">
                    {{ $item->batch_no ?? '—' }}
                </span>
            </td>
            <td class="text-center">
                @if($item->expiry_date)
                    <span style="background-color: #fef3c7; padding: 1px 5px; border-radius: 3px; font-size: 8px;">
                        {{ $item->expiry_date->format('d/m/Y') }}
                    </span>
                @else
                    <span style="color: #999;">—</span>
                @endif
            </td>
            <td class="text-center" style="color: #64748b;">{{ $poItem?->quantity ?? '—' }}</td>
            <td class="text-center"><strong>{{ $item->quantity_received }}</strong></td>
            <td class="text-center">{{ $item->uom ?? $product?->unit ?? 'pcs' }}</td>
            <td>
                <span class="{{ $item->condition === 'Good' ? 'status-good' : 'status-bad' }}">
                    [{{ strtoupper($item->condition ?? 'GOOD') }}]
                </span>
                @if($item->notes)
                    <br><span style="font-size: 8px; color: #666;">{{ $item->notes }}</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center" style="color: #999; padding: 12px;">Tidak ada item</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- VALIDASI --}}
<div class="validation-box">
    <strong>Keterangan Validasi:</strong>
    Barang telah diperiksa kesesuaiannya dengan faktur pengiriman distributor. Tanda terima ini sah dan dapat dilanjutkan ke proses akuntansi (Accounts Payable / Receivable).
    @if($goodsReceipt->notes)
        <br><strong>Catatan:</strong> {{ $goodsReceipt->notes }}
    @endif
</div>

{{-- TANDA TANGAN --}}
<div class="signature-section">
    <table class="signature-table">
        <tr>
            <td>
                <span class="sig-label">Distributor / Pengirim</span>
                <div class="sig-line">Nama & Stempel</div>
            </td>
            <td>
                <span class="sig-label">Diperiksa & Diterima Oleh</span>
                <div class="sig-line">{{ $goodsReceipt->receivedBy?->name ?? '—' }}</div>
            </td>
            <td>
                <span class="sig-label">Penanggung Jawab Gudang</span>
                <div class="sig-line">Nama & Tanda Tangan</div>
            </td>
        </tr>
    </table>
</div>

{{-- FOOTER --}}
<table class="footer-table">
    <tr>
        <td style="width: 50%;">
            <span style="font-size: 8px; color: #aaa;">[ Dokumen resmi {{ config('company.name') }} ]</span>
        </td>
        <td class="print-log">
            Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
            {{ $goodsReceipt->gr_number }}
        </td>
    </tr>
</table>

</body>
</html>
