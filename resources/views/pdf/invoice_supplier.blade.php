@extends('pdf.layout')

@section('title', 'Supplier Invoice ' . $invoice->invoice_number)
@section('document_name', 'BUKTI FAKTUR KEUANGAN (AP)')
@section('document_number', $invoice->invoice_number)
@section('document_date', $invoice->created_at->format('d F Y'))

@section('content')

    {{-- Header Info Section --}}
    <table class="info-section">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="info-box">
                    <div class="info-title">{{ config('company.name_upper') }}</div>
                    <strong>Penerima Invoice (Received By)</strong><br>
                    {{ config('company.address') }}<br>
                    Telp: {{ config('company.phone') }}<br>
                    Email: {{ config('company.email') }}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="info-box" style="background-color: #fef3c7; border-left: 4px solid #f59e0b;">
                    <div class="info-title" style="color: #92400e;">INVOICE DARI SUPPLIER</div>
                    <strong style="font-size: 13px;">{{ $invoice->supplier?->name ?? '—' }}</strong>
                    @if($invoice->supplier?->address)
                        <br><span style="font-size: 10px; color: #555;">{{ $invoice->supplier->address }}</span>
                    @endif
                    @if($invoice->supplier?->contact_phone)
                        <br><span style="font-size: 10px; color: #555;">Telp: {{ $invoice->supplier->contact_phone }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Invoice Details --}}
    <table class="info-section" style="margin-top: 10px;">
        <tr>
            <td style="width: 50%;">
                <div class="info-box">
                    <div class="info-title">Detail Invoice</div>
                    <strong>Nomor Invoice Internal:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Invoice Distributor:</strong> {{ $invoice->distributor_invoice_number ?? '—' }}<br>
                    <strong>Tanggal Invoice Distributor:</strong> {{ $invoice->distributor_invoice_date ? \Carbon\Carbon::parse($invoice->distributor_invoice_date)->format('d F Y') : '—' }}<br>
                    <strong>Jatuh Tempo:</strong> <span style="color: #dc2626; font-weight: bold;">{{ $invoice->due_date?->format('d F Y') ?? '—' }}</span><br>
                    <strong>Status:</strong> <span style="text-transform: uppercase; font-weight: bold;">{{ $invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->status }}</span>
                </div>
            </td>
            <td style="width: 50%;">
                <div class="info-box">
                    <div class="info-title">Referensi Dokumen</div>
                    <strong>PO Internal:</strong> {{ $invoice->purchaseOrder?->po_number ?? '—' }}<br>
                    <strong>PO RS/Klinik:</strong> {{ $invoice->purchaseOrder?->external_po_number ?? '—' }}<br>
                    <strong>Goods Receipt:</strong> {{ $invoice->goodsReceipt?->gr_number ?? '—' }}<br>
                    @if($invoice->goods_receipt_id)
                    <span style="color: #16a34a; font-size: 10px;">
                        ✓ Invoice berdasarkan Penerimaan Barang
                    </span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Item Table --}}
    @if($invoice->lineItems && $invoice->lineItems->count() > 0)
    <div style="margin-top: 20px;">
        <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 5px;">
            RINCIAN BARANG
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
                    <td>
                        <strong>{{ $item->product_name }}</strong><br>
                        <span style="font-size: 9px; color: #666;">SKU: {{ $item->product_sku ?? '—' }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span style="background-color: #dbeafe; padding: 2px 6px; border-radius: 3px; font-size: 9px;">
                            {{ $item->batch_no ?? '—' }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span style="background-color: #fef3c7; padding: 2px 6px; border-radius: 3px; font-size: 9px;">
                            {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '—' }}
                        </span>
                    </td>
                    <td style="text-align: center;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td style="text-align: center; font-size: 10px;">{{ $item->uom ?? 'pcs' }}</td>
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
    <div style="margin-top: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%;">
                    <table class="data-table">
                        <tbody>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">Subtotal (Sebelum Diskon)</td>
                                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: bold;">
                                    Rp {{ number_format(($invoice->subtotal_amount ?? 0) + ($invoice->discount_amount ?? 0), 0, ',', '.') }}
                                </td>
                            </tr>
                            @if(($invoice->discount_amount ?? 0) > 0)
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">Total Diskon</td>
                                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #16a34a;">
                                    - Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">Subtotal (Setelah Diskon)</td>
                                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: bold;">
                                    Rp {{ number_format($invoice->subtotal_amount ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @if(($invoice->tax_amount ?? 0) > 0)
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">PPN 11%</td>
                                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: bold;">
                                    Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                            <tr style="background-color: #fef3c7;">
                                <td style="padding: 12px; font-weight: bold; font-size: 14px; text-transform: uppercase;">
                                    TOTAL HUTANG
                                </td>
                                <td style="padding: 12px; text-align: right; font-weight: bold; font-size: 16px; color: #92400e;">
                                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; color: #16a34a;">Sudah Dibayar</td>
                                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #16a34a;">
                                    Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr style="background-color: #fef2f2;">
                                <td style="padding: 8px; font-weight: bold; color: #dc2626; text-transform: uppercase;">Sisa Hutang</td>
                                <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 13px; color: #dc2626;">
                                    Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Notes Section --}}
    @if($invoice->notes)
    <div style="margin-top: 20px; padding: 10px; background-color: #f9fafb; border-left: 4px solid #6b7280; font-size: 10px;">
        <strong>Catatan:</strong><br>
        {{ $invoice->notes }}
    </div>
    @endif

    {{-- Signature Section --}}
    <div style="margin-top: 30px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div style="border: 1px solid #e5e7eb; padding: 15px; border-radius: 5px; background-color: #f9fafb;">
                        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">DITERIMA OLEH</div>
                        <div style="font-size: 10px; color: #666; margin-bottom: 50px;">{{ config('company.name') }}</div>
                        <div style="border-top: 1px solid #333; width: 150px; margin: 0 auto; padding-top: 5px;">
                            <div style="font-size: 11px; font-weight: bold;">{{ $invoice->issuedBy?->name ?? 'Admin Keuangan' }}</div>
                            <div style="font-size: 9px; color: #666;">Finance & Accounting Dept.</div>
                            <div style="font-size: 9px; color: #666; margin-top: 3px;">
                                Tanggal: {{ $invoice->issued_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div style="border: 1px solid #e5e7eb; padding: 15px; border-radius: 5px; background-color: #f9fafb;">
                        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">DIVERIFIKASI OLEH</div>
                        <div style="font-size: 10px; color: #666; margin-bottom: 50px;">Finance {{ config('company.name') }}</div>
                        <div style="border-top: 1px solid #333; width: 150px; margin: 0 auto; padding-top: 5px;">
                            <div style="font-size: 11px; font-weight: bold;">( _________________ )</div>
                            <div style="font-size: 9px; color: #666;">Nama & Tanda Tangan</div>
                            <div style="font-size: 9px; color: #666; margin-top: 3px;">
                                Tanggal: _______________
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer Notes --}}
    <div style="margin-top: 20px; padding: 10px; background-color: #fef3c7; border-left: 4px solid #f59e0b; font-size: 10px;">
        <strong>Catatan Penting:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Dokumen ini adalah pencatatan internal invoice dari supplier</li>
            <li>Invoice asli dari supplier disimpan sebagai lampiran digital</li>
            <li>Pembayaran ke supplier hanya dilakukan setelah pembayaran dari RS/Klinik diterima</li>
            <li>Untuk pertanyaan, hubungi Finance Dept. di {{ config('company.phone') }} ext. 101</li>
        </ul>
    </div>

    {{-- Document Footer --}}
    <div style="margin-top: 15px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 10px;">
        Dokumen ini dicetak secara elektronik dan sah tanpa tanda tangan basah | 
        Versi: v{{ $invoice->version ?? 1 }} | 
        Dicetak: {{ now()->format('d F Y H:i') }} WIB
    </div>

@endsection
