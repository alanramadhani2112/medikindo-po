<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* ================================================================
           BASE STYLES — dompdf compatible (no flexbox, no grid)
           ================================================================ */
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
            padding: 15px 20px;
        }

        /* ================================================================
           WATERMARK — dompdf uses position:fixed for page-level overlay
           ================================================================ */
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

        /* ================================================================
           HEADER
           ================================================================ */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1B4B7F;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 4px 6px;
            border: none;
        }
        .header-logo {
            width: 80px;
        }
        .header-company {
            font-size: 9px;
            color: #444;
            line-height: 1.5;
        }
        .header-company strong {
            font-size: 12px;
            color: #1B4B7F;
        }
        .header-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #1B4B7F;
            letter-spacing: 2px;
        }

        /* ================================================================
           SOLD TO + INVOICE META (2-column table)
           ================================================================ */
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #ccc;
        }
        .info-table td {
            vertical-align: top;
            padding: 6px 8px;
            border: none;
        }
        .info-table .divider {
            border-right: 1px solid #ccc;
        }
        .section-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1B4B7F;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .meta-row td {
            padding: 2px 4px;
            border: none;
            font-size: 9px;
        }
        .meta-key {
            color: #666;
            width: 110px;
        }
        .meta-val {
            font-weight: bold;
        }

        /* ================================================================
           LINE ITEMS TABLE
           ================================================================ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .items-table th {
            background-color: #1B4B7F;
            color: #fff;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #1B4B7F;
            font-size: 9px;
        }
        .items-table td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f7f9fc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        /* ================================================================
           BANK INFO + SUMMARY (2-column)
           ================================================================ */
        .bottom-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .bottom-table td {
            vertical-align: top;
            padding: 0 6px 0 0;
            border: none;
        }
        .bank-box {
            border: 1px solid #ccc;
            padding: 6px 8px;
            font-size: 9px;
            line-height: 1.6;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 3px 4px;
            border: none;
            font-size: 9px;
        }
        .summary-table .grand-row td {
            border-top: 2px solid #1B4B7F;
            font-weight: bold;
            font-size: 11px;
            color: #1B4B7F;
            padding-top: 5px;
        }

        /* ================================================================
           TERBILANG
           ================================================================ */
        .terbilang-box {
            border: 1px solid #ccc;
            padding: 5px 8px;
            font-size: 9px;
            font-style: italic;
            margin-bottom: 10px;
            background: #f8f9fa;
        }

        /* ================================================================
           SIGNATURE COLUMNS (4-column table)
           ================================================================ */
        .signature-section {
            page-break-inside: avoid;
            margin-bottom: 10px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 25%;
            text-align: center;
            border: 1px solid #ccc;
            padding: 8px 4px 6px;
            font-size: 9px;
            vertical-align: bottom;
        }
        .sig-label {
            font-weight: bold;
            font-size: 9px;
            color: #1B4B7F;
            display: block;
            margin-bottom: 40px;
        }
        .sig-line {
            border-top: 1px solid #333;
            margin: 0 10px;
            padding-top: 3px;
            font-size: 8px;
            color: #666;
        }

        /* ================================================================
           BARCODE + PRINT LOG FOOTER
           ================================================================ */
        .footer-table {
            width: 100%;
            margin-top: 8px;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }
        .footer-table td {
            vertical-align: middle;
            border: none;
            padding: 2px 4px;
        }
        .print-log {
            font-size: 8px;
            color: #888;
            text-align: right;
        }
    </style>
</head>
<body>

{{-- ================================================================
     WATERMARK
     ================================================================ --}}
<div class="watermark">ASLI UNTUK PENAGIHAN/CUSTOMER</div>

{{-- ================================================================
     HEADER: Logo | Company Info | Title
     ================================================================ --}}
@php
    $logoPath = public_path('logo-medikindo.png');
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
@endphp
<table class="header-table">
    <tr>
        <td style="width: 80px;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Medikindo" style="max-width: 75px; max-height: 50px;">
            @else
                <div style="font-size: 14px; font-weight: bold; color: #1B4B7F;">MEDIKINDO</div>
            @endif
        </td>
        <td class="header-company">
            <strong>PT. MEDIKINDO ARTHA MEDIKA</strong><br>
            NPWP: 01.234.567.8-901.000<br>
            Izin PBF: PBF-2024-001/KEMENKES<br>
            Jl. Raya Farmasi No. 123, Jakarta Selatan 12345<br>
            Telp: (021) 1234-5678 | Fax: (021) 1234-5679
        </td>
        <td class="header-title">
            INVOICE LOCAL
        </td>
    </tr>
</table>

{{-- ================================================================
     SOLD TO + INVOICE METADATA (2-column)
     ================================================================ --}}
<table class="info-table">
    <tr>
        {{-- Left: Sold To --}}
        <td class="divider" style="width: 50%;">
            <div class="section-label">Tagihan Kepada (Sold To)</div>
            <strong style="font-size: 11px;">{{ $invoice->organization?->name ?? '—' }}</strong><br>
            @if($invoice->organization?->customer_code)
                <span style="color: #666;">Kode Customer:</span> <strong>{{ $invoice->organization->customer_code }}</strong><br>
            @endif
            @if($invoice->organization?->address)
                {{ $invoice->organization->address }}<br>
            @endif
            @if($invoice->organization?->npwp)
                <span style="color: #666;">NPWP:</span> {{ $invoice->organization->npwp }}
            @endif
        </td>
        {{-- Right: Invoice Metadata --}}
        <td style="width: 50%;">
            <div class="section-label">Detail Invoice</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr class="meta-row">
                    <td class="meta-key">No. Invoice</td>
                    <td>:</td>
                    <td class="meta-val">{{ $invoice->invoice_number }}</td>
                    <td class="meta-key">No. PO</td>
                    <td>:</td>
                    <td class="meta-val">{{ $invoice->purchaseOrder?->po_number ?? '—' }}</td>
                </tr>
                <tr class="meta-row">
                    <td class="meta-key">Tanggal Invoice</td>
                    <td>:</td>
                    <td class="meta-val">{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') : '—' }}</td>
                    <td class="meta-key">Payment Term</td>
                    <td>:</td>
                    <td class="meta-val">{{ $invoice->payment_term ?? '—' }}</td>
                </tr>
                <tr class="meta-row">
                    <td class="meta-key">Jatuh Tempo</td>
                    <td>:</td>
                    <td class="meta-val">{{ $invoice->due_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="meta-key">Tipe Pelayanan</td>
                    <td>:</td>
                    <td class="meta-val">Reguler</td>
                </tr>
                <tr class="meta-row">
                    <td class="meta-key">No. Faktur Pajak</td>
                    <td>:</td>
                    <td class="meta-val">{{ $invoice->tax_number ?? '—' }}</td>
                    <td class="meta-key">Salesman</td>
                    <td>:</td>
                    <td class="meta-val">{{ $invoice->salesman ?? '—' }}</td>
                </tr>
                @if($invoice->goodsReceipt?->do_number)
                <tr class="meta-row">
                    <td class="meta-key">No. DO</td>
                    <td>:</td>
                    <td class="meta-val" colspan="3">{{ $invoice->goodsReceipt->do_number }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

{{-- ================================================================
     LINE ITEMS TABLE
     Columns: No | DO No. | Deskripsi Material | Batch/ED | Qty | UoM | Price | Disc% | Amount
     ================================================================ --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 3%;">No</th>
            <th style="width: 10%;">DO No.</th>
            <th style="width: 28%; text-align: left;">Deskripsi Material</th>
            <th style="width: 14%;">Batch / ED</th>
            <th style="width: 6%;">Qty</th>
            <th style="width: 5%;">UoM</th>
            <th style="width: 12%; text-align: right;">Harga (Rp)</th>
            <th style="width: 6%;">Disc%</th>
            <th style="width: 16%; text-align: right;">Amount (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->lineItems as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $invoice->goodsReceipt?->do_number ?? '—' }}</td>
                <td class="text-left">
                    {{ $item->product_name ?? $item->product?->name ?? '—' }}
                    @if($item->tax_rate > 0)
                        <br><span style="font-size: 8px; color: #888;">PPN {{ number_format($item->tax_rate, 0) }}%</span>
                    @endif
                </td>
                <td class="text-center">
                    {{ $item->batch_number ?? '—' }}
                    @if($item->expiry_date)
                        <br><span style="font-size: 8px;">{{ $item->expiry_date->format('d/m/Y') }}</span>
                    @endif
                </td>
                <td class="text-center">{{ number_format((float)$item->quantity, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->uom ?? 'pcs' }}</td>
                <td class="text-right">{{ number_format((float)$item->unit_price, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format((float)($item->discount_percentage ?? 0), 1) }}%</td>
                <td class="text-right">{{ number_format((float)$item->line_total, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center" style="color: #999; padding: 12px;">
                    Tidak ada item
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- ================================================================
     BANK INFO + CALCULATION SUMMARY (2-column)
     ================================================================ --}}
@php
    $subtotal  = (float)($invoice->subtotal_amount ?? 0);
    $discount  = (float)($invoice->discount_amount ?? 0);
    $surcharge = (float)($invoice->surcharge ?? 0);
    $tax       = (float)($invoice->tax_amount ?? 0);
    $ematerai  = (float)($invoice->ematerai_fee ?? 0);
    $grandTotal = (float)($invoice->total_amount ?? 0);
    $nett = $subtotal - $discount + $surcharge;
@endphp
<table class="bottom-table">
    <tr>
        {{-- Left: Bank Info --}}
        <td style="width: 48%;">
            <div class="bank-box">
                <div class="section-label">Informasi Rekening Bank</div>
                <strong>BRI</strong> — No. Rek: <strong>0123-01-012345-30-6</strong><br>
                a.n. PT. Medikindo Artha Medika<br>
                <br>
                <strong>Danamon</strong> — No. Rek: <strong>000-1234567</strong><br>
                a.n. PT. Medikindo Artha Medika
            </div>
        </td>
        {{-- Right: Calculation Summary --}}
        <td style="width: 52%; padding: 0;">
            <div class="section-label" style="margin-left: 8px;">Ringkasan Pembayaran</div>
            <table class="summary-table">
                <tr>
                    <td>Total Amount</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Diskon</td>
                    <td class="text-right" style="color: #c00;">
                        @if($discount > 0)
                            (Rp {{ number_format($discount, 0, ',', '.') }})
                        @else
                            Rp 0
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Surcharge</td>
                    <td class="text-right">Rp {{ number_format($surcharge, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Nett</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($nett, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td>PPN</td>
                    <td class="text-right">Rp {{ number_format($tax, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Biaya e-Meterai</td>
                    <td class="text-right">Rp {{ number_format($ematerai, 0, ',', '.') }}</td>
                </tr>
                <tr class="grand-row">
                    <td>GRAND TOTAL</td>
                    <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ================================================================
     TERBILANG
     ================================================================ --}}
@php
    $terbilang = app(\App\Services\TerbilangService::class)->convert((float)$grandTotal);
@endphp
<div class="terbilang-box">
    Terbilang: <strong>{{ $terbilang }}</strong>
</div>

{{-- ================================================================
     4 SIGNATURE COLUMNS
     ================================================================ --}}
<div class="signature-section">
    <table class="signature-table">
        <tr>
            <td>
                <span class="sig-label">Customer</span>
                <div class="sig-line">Nama &amp; Stempel</div>
            </td>
            <td>
                <span class="sig-label">Spv. Penjualan</span>
                <div class="sig-line">Nama &amp; Tanda Tangan</div>
            </td>
            <td>
                <span class="sig-label">Penanggung Jawab PBF</span>
                <div class="sig-line">Nama &amp; Tanda Tangan</div>
            </td>
            <td>
                <span class="sig-label">Branch Manager</span>
                <div class="sig-line">Nama &amp; Tanda Tangan</div>
            </td>
        </tr>
    </table>
</div>

{{-- ================================================================
     BARCODE + PRINT LOG FOOTER
     ================================================================ --}}
@php
    $barcodeImg = '';
    if (!empty($invoice->barcode_serial)) {
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $barcodeData = $generator->getBarcode(
                $invoice->barcode_serial,
                \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_CODE_128,
                2,
                40
            );
            $barcodeImg = 'data:image/png;base64,' . base64_encode($barcodeData);
        } catch (\Throwable $barcodeErr) {
            $barcodeImg = '';
        }
    }
@endphp
<table class="footer-table">
    <tr>
        <td style="width: 50%;">
            @if($barcodeImg)
                <img src="{{ $barcodeImg }}" alt="Barcode" style="height: 40px; max-width: 200px;"><br>
                <span style="font-size: 8px; color: #666; letter-spacing: 1px;">{{ $invoice->barcode_serial }}</span>
            @else
                <span style="font-size: 8px; color: #aaa;">[ Barcode tidak tersedia ]</span>
            @endif
        </td>
        <td class="print-log">
            Dicetak: {{ $invoice->last_printed_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}<br>
            Cetak ke-{{ $invoice->print_count ?? 1 }}<br>
            {{ $invoice->invoice_number }}
        </td>
    </tr>
</table>

</body>
</html>
