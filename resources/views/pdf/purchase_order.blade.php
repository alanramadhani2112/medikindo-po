<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Purchase Order {{ $po->po_number }}</title>
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
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 3px 4px; border: none; font-size: 9px; }
        .summary-table .grand-row td { border-top: 2px solid #1B4B7F; font-weight: bold; font-size: 11px; color: #1B4B7F; padding-top: 5px; }
        .terbilang-box { border: 1px solid #ccc; padding: 5px 8px; font-size: 9px; font-style: italic; margin-bottom: 10px; background: #f8f9fa; }
        .signature-section { page-break-inside: avoid; margin-bottom: 10px; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { width: 25%; text-align: center; border: 1px solid #ccc; padding: 8px 4px 6px; font-size: 9px; vertical-align: bottom; }
        .sig-label { font-weight: bold; font-size: 9px; color: #1B4B7F; display: block; margin-bottom: 40px; }
        .sig-line { border-top: 1px solid #333; margin: 0 10px; padding-top: 3px; font-size: 8px; color: #666; }
        .footer-table { width: 100%; margin-top: 8px; border-top: 1px solid #ddd; padding-top: 6px; }
        .footer-table td { vertical-align: middle; border: none; padding: 2px 4px; }
        .print-log { font-size: 8px; color: #888; text-align: right; }
        .notes-box { border-left: 3px solid #1B4B7F; padding: 5px 8px; font-size: 9px; margin-bottom: 10px; background: #f0f4ff; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; background: #e0e7ff; color: #1B4B7F; }
    </style>
</head>
<body>

<div class="watermark">PURCHASE ORDER</div>

{{-- HEADER --}}
@php
    $logoPath = public_path(config('company.logo', 'logo-medikindo.png'));
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
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
            PURCHASE ORDER
        </td>
    </tr>
</table>

{{-- INFO: Pemesan & Supplier --}}
<table class="info-table">
    <tr>
        <td class="divider" style="width: 50%;">
            <div class="section-label">Pihak Pemesan (Organisasi)</div>
            <strong style="font-size: 11px;">{{ $po->organization?->name ?? '—' }}</strong><br>
            @if($po->organization?->address)
                {{ $po->organization->address }}<br>
            @endif
            @if($po->organization?->phone)
                Telp: {{ $po->organization->phone }}
            @endif
        </td>
        <td style="width: 50%;">
            <div class="section-label">Detail PO</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr class="meta-row">
                    <td class="meta-key">No. PO</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po->po_number }}</td>
                    <td class="meta-key">Status</td>
                    <td>:</td>
                    <td><span class="status-badge">{{ str_replace('_', ' ', $po->status) }}</span></td>
                </tr>
                <tr class="meta-row">
                    <td class="meta-key">Tanggal PO</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po->created_at->format('d/m/Y') }}</td>
                    <td class="meta-key">Supplier</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po->supplier?->name ?? '—' }}</td>
                </tr>
                <tr class="meta-row">
                    <td class="meta-key">Tgl. Dibutuhkan</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po->requested_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="meta-key">Tlp Supplier</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po->supplier?->contact_phone ?? '—' }}</td>
                </tr>
                <tr class="meta-row">
                    <td class="meta-key">Est. Pengiriman</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po->expected_delivery_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="meta-key">Dibuat Oleh</td>
                    <td>:</td>
                    <td class="meta-val">{{ $po->creator?->name ?? '—' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- TABEL ITEM --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 35%; text-align: left;">Deskripsi Barang</th>
            <th style="width: 12%;">SKU</th>
            <th style="width: 10%;">Qty</th>
            <th style="width: 8%;">Satuan</th>
            <th style="width: 15%; text-align: right;">Harga Satuan (Rp)</th>
            <th style="width: 16%; text-align: right;">Subtotal (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($po->items as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-left"><strong>{{ $item->product?->name ?? '—' }}</strong></td>
            <td class="text-center" style="font-size: 8px; color: #666;">{{ $item->product?->sku ?? '—' }}</td>
            <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td>
            <td class="text-center">{{ $item->unit ?? $item->product?->unit ?? 'pcs' }}</td>
            <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td class="text-right"><strong>{{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center" style="color: #999; padding: 12px;">Tidak ada item</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- SUMMARY --}}
<table style="width: 100%; margin-bottom: 8px;">
    <tr>
        <td style="width: 55%; vertical-align: top;">
            @if($po->notes)
            <div class="notes-box">
                <strong>Catatan:</strong><br>{{ $po->notes }}
            </div>
            @endif
        </td>
        <td style="width: 45%; padding: 0;">
            <div class="section-label" style="margin-left: 8px;">Ringkasan</div>
            <table class="summary-table">
                <tr class="grand-row">
                    <td>TOTAL BIAYA PO</td>
                    <td class="text-right">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- TANDA TANGAN --}}
<div class="signature-section">
    <table class="signature-table">
        <tr>
            <td>
                <span class="sig-label">Dibuat Oleh</span>
                <div class="sig-line">{{ $po->creator?->name ?? '—' }}</div>
            </td>
            <td>
                <span class="sig-label">Disetujui Oleh</span>
                <div class="sig-line">Nama & Tanda Tangan</div>
            </td>
            <td>
                <span class="sig-label">Penanggung Jawab PBF</span>
                <div class="sig-line">Nama & Tanda Tangan</div>
            </td>
            <td>
                <span class="sig-label">Supplier / Distributor</span>
                <div class="sig-line">Nama & Stempel</div>
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
            {{ $po->po_number }}
        </td>
    </tr>
</table>

</body>
</html>
