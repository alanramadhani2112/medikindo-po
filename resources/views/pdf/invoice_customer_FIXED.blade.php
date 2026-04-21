@extends('pdf.layout')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('document_name', 'FAKTUR TAGIHAN')
@section('document_number', $invoice->invoice_number)
@section('document_date', $invoice->created_at->format('d F Y'))

@section('content')

    {{-- Header Info --}}
    <table class="info-section">
        <tr>
            <td style="width: 50%;">
                <div class="info-box">
                    <div class="info-title">PT MEDIKINDO SEJAHTERA</div>
                    <strong>Penerbit Invoice (Issued By)</strong><br>
                    Jl. Industri Raya No. 45<br>
                    Jakarta Pusat 10110<br>
                    Telp: (021) 1234-5678<br>
                    Email: finance@medikindo.co.id
                </div>
            </td>
            <td style="width: 50%;">
                <div class="info-box" style="background-color: #f0f9ff; border-left: 4px solid #3b82f6;">
                    <div class="info-title" style="color: #1e40af;">TAGIHAN KEPADA (BILL TO)</div>
                    <strong style="font-size: 14px;">{{ $invoice->organization?->name ?? '—' }}</strong><br>
                    {{ $invoice->organization?->address ?? 'Alamat tidak tersedia' }}<br>
                    @if($invoice->organization?->phone)
                    Telp: {{ $invoice->organization->phone }}<br>
                    @endif
                    @if($invoice->organization?->email)
                    Email: {{ $invoice->organization->email }}
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
                    <strong>Nomor Invoice:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Tanggal Terbit:</strong> {{ $invoice->created_at->format('d F Y') }}<br>
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
    <div style="margin-top: 20px;">
        <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 5px;">
            DETAIL ITEM INVOICE
        </h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%; text-align: center;">No</th>
                    <th style="width: 22%;">Nama Produk</th>
                    <th style="width: 10%;">Batch</th>
                    <th style="width: 10%;">Kadaluarsa</th>
                    <th style="width: 7%; text-align: right;">Qty</th>
                    <th style="width: 8%; text-align: center;">Satuan</th>
                    <th style="width: 12%; text-align: right;">Harga Satuan</th>
                    <th style="width: 8%; text-align: right;">Diskon</th>
                    <th style="width: 15%; text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->lineItems as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong><br>
                        <span style="font-size: 9px; color: #666;">SKU: {{ $item->product_sku ?? '—' }}</span>
                    </td>
                    <td>
                        <span style="background-color: #dbeafe; padding: 2px 6px; border-radius: 3px; font-size: 9px;">
                            {{ $item->batch_no ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <span style="background-color: #fef3c7; padding: 2px 6px; border-radius: 3px; font-size: 9px;">
                            {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '—' }}
                        </span>
                    </td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $item->product?->unit ?? '—' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align: right;">
                        @if($item->discount_percentage > 0)
                            {{ number_format($item->discount_percentage, 1) }}%<br>
                            <span style="font-size: 9px; color: #16a34a;">-Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #666;">
                        Tidak ada item
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

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
                                    Rp {{ number_format($invoice->subtotal_amount + $invoice->discount_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @if($invoice->discount_amount > 0)
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
                                    Rp {{ number_format($invoice->subtotal_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @if($invoice->tax_amount > 0)
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">PPN 11%</td>
                                <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: bold;">
                                    Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                            <tr style="background-color: #dbeafe;">
                                <td style="padding: 12px; font-weight: bold; font-size: 14px; text-transform: uppercase;">
                                    TOTAL TAGIHAN
                                </td>
                                <td style="padding: 12px; text-align: right; font-weight: bold; font-size: 16px; color: #1e40af;">
                                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Payment Instructions --}}
    <div style="margin-top: 20px; padding: 15px; background-color: #f0f9ff; border: 1px solid #3b82f6; border-radius: 5px;">
        <h4 style="font-size: 12px; font-weight: bold; margin-bottom: 10px; color: #1e40af;">
            INSTRUKSI PEMBAYARAN
        </h4>
        <p style="font-size: 11px; margin-bottom: 10px;">
            Mohon transfer sebesar <strong style="color: #dc2626;">Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}</strong> 
            ({{ ucwords(\Illuminate\Support\Str::title(\App\Services\TerbilangService::convert($invoice->total_amount - $invoice->paid_amount))) }} Rupiah) 
            ke rekening berikut:
        </p>
        <table style="width: 100%; font-size: 11px; margin-top: 10px;">
            <tr>
                <td style="width: 30%; font-weight: bold;">Bank</td>
                <td style="width: 70%;">: BCA</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nomor Rekening</td>
                <td>: <strong style="font-size: 14px;">0987654321</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Atas Nama</td>
                <td>: PT Medikindo Sejahtera</td>
            </tr>
        </table>
        <p style="font-size: 10px; margin-top: 10px; color: #dc2626;">
            <strong>PENTING:</strong> Harap mencantumkan nomor invoice <strong>{{ $invoice->invoice_number }}</strong> pada berita transfer.
        </p>
    </div>

    {{-- Signature Section --}}
    <div style="margin-top: 30px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div style="border: 1px solid #e5e7eb; padding: 15px; border-radius: 5px; background-color: #f9fafb;">
                        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">DITERBITKAN OLEH</div>
                        <div style="font-size: 10px; color: #666; margin-bottom: 50px;">PT Medikindo Sejahtera</div>
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
                        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">DITERIMA OLEH</div>
                        <div style="font-size: 10px; color: #666; margin-bottom: 50px;">{{ $invoice->organization?->name ?? '—' }}</div>
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
        <strong>Catatan:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Invoice ini merupakan bukti tagihan resmi dari PT Medikindo Sejahtera</li>
            <li>Pembayaran dianggap sah setelah dana masuk ke rekening perusahaan</li>
            <li>Untuk pertanyaan, hubungi Finance Dept. di (021) 1234-5678 ext. 101</li>
            @if($invoice->notes)
            <li><em>{{ $invoice->notes }}</em></li>
            @endif
        </ul>
    </div>

    {{-- Document Footer --}}
    <div style="margin-top: 15px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 10px;">
        Dokumen ini dicetak secara elektronik dan sah tanpa tanda tangan basah | 
        Versi: v{{ $invoice->version ?? 1 }} | 
        Dicetak: {{ now()->format('d F Y H:i') }} WIB
    </div>

@endsection
