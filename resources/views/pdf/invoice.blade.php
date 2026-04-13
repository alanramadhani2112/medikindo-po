@extends('pdf.layout')

@php
    $typeLabel = $type === 'supplier' ? 'Account Payable (AP)' : 'Account Receivable (AR)';
    $toTitle   = $type === 'supplier' ? 'Supplier (Tagihan Ke)' : 'Organisasi (Tagihan Dari)';
    $entity    = $type === 'supplier' ? ($invoice->supplier?->name ?? '—') : ($invoice->organization?->name ?? '—');
@endphp

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('document_name', 'BUKTI FAKTUR KEUANGAN')
@section('document_number', $invoice->invoice_number)
@section('document_date', $invoice->created_at->format('d F Y'))

@section('content')

    <table class="info-section">
        <tr>
            <td>
                <div class="info-box">
                    <div class="info-title">Detail Tagihan Finansial</div>
                    <strong>Klasifikasi: {{ $typeLabel }}</strong><br>
                    Tenggat (Jatuh Tempo): <strong style="color: red;">{{ $invoice->due_date?->format('d F Y') ?? '—' }}</strong><br>
                    Status: <strong style="text-transform: uppercase;">{{ $invoice->status }}</strong>
                </div>
            </td>
            <td>
                <div class="info-box">
                    <div class="info-title">{{ $toTitle }}</div>
                    <strong>{{ $entity }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%">Kaitan Dokumen Logistik</th>
                <th style="width: 70%">Rincian Transaksi Nominal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Ref. PO: <b>{{ $invoice->purchaseOrder?->po_number ?? '—' }}</b><br>
                    Ref. GR: <b>{{ $invoice->goodsReceipt?->gr_number ?? '—' }}</b>
                </td>
                <td style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="border: 0; padding: 10px; border-bottom: 1px dotted #ccc;">Total Hutang/Piutang Keseluruhan</td>
                            <td style="border: 0; padding: 10px; text-align: right; border-bottom: 1px dotted #ccc; font-weight: bold;">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="border: 0; padding: 10px; border-bottom: 1px solid #e2e8f0;">Total Kas Tercatat (Sudah Dilunasi)</td>
                            <td style="border: 0; padding: 10px; text-align: right; border-bottom: 1px solid #e2e8f0; color: green;">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr style="background-color: #f8fafc;">
                            <td style="border: 0; padding: 10px; font-weight: bold; font-size: 14px; text-transform: uppercase;">Sisa Tagihan Outstanding</td>
                            <td style="border: 0; padding: 10px; text-align: right; font-weight: bold; font-size: 16px; color: #dc2626;">Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Line Items Detail --}}
    @if($invoice->lineItems && $invoice->lineItems->count() > 0)
    <div style="margin-top: 20px;">
        <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 5px;">
            Detail Item Invoice
        </h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 30%">Produk</th>
                    <th style="width: 15%">Batch</th>
                    <th style="width: 15%">Kadaluarsa</th>
                    <th style="width: 10%; text-align: right;">Qty</th>
                    <th style="width: 25%; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lineItems as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                    </td>
                    <td>{{ $item->batch_no ?? '—' }}</td>
                    <td>{{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '—' }}</td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <div style="float: left; width: 350px; padding-top: 15px;">
            <p style="font-size: 11px;">
                <strong>Instruksi Pembayaran:</strong><br>
                Mohon transfer sebesar nilai <em>Outstanding</em> ke Rekening BCA: 0987654321 (a.n PT Medikindo Sejahtera). Harap mencantumkan Nomor Invoice pada berita acara transfer.
            </p>
        </div>
        
        <div class="signature-box">
            <div style="font-size: 11px; margin-bottom: 50px;">Otorisasi Divisi Keuangan,</div>
            <div class="signature-line"></div>
            <div style="font-size: 12px; font-weight: bold;">Admin Keuangan Pusat</div>
            <div style="font-size: 10px; color: #666;">Finance & Accounting Dept.</div>
        </div>
    </div>

@endsection
