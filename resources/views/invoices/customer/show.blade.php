@extends('layouts.app')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0">{{ $invoice->invoice_number }}</h1>
                @php
                    $badgeClass = match($invoice->status) {
                        'draft'        => 'badge-secondary',
                        'issued'       => 'badge-warning',
                        'partial_paid' => 'badge-info',
                        'paid'         => 'badge-success',
                        'void'         => 'badge-danger',
                        default        => 'badge-secondary',
                    };
                @endphp
                <span class="badge {{ $badgeClass }} fs-7">{{ strtoupper(str_replace('_', ' ', $invoice->status)) }}</span>
                @if($invoice->supplierInvoice)
                    <span class="badge badge-light-primary fs-8">
                        <i class="ki-outline ki-verify fs-8 me-1"></i>
                        AP: {{ $invoice->supplierInvoice->invoice_number }}
                    </span>
                @endif
            </div>
            <p class="text-gray-600 fs-6 mb-0">
                Tagihan kepada: <span class="text-gray-900 fw-semibold">{{ $invoice->organization?->name ?? '—' }}</span>
            </p>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('web.invoices.customer.pdf', $invoice) }}" target="_blank" class="btn btn-light-info">
                <i class="ki-outline ki-document fs-3"></i>
                Cetak PDF
            </a>
            <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-3"></i>
                Kembali
            </a>
        </div>
    </div>

    {{-- Margin Violation Warnings --}}
    @php
        $marginService = app(\App\Services\MarginProtectionService::class);
        $violations = $invoice->status === 'draft' ? $marginService->check($invoice) : [];
    @endphp
    @if(!empty($violations))
        <div class="alert alert-warning d-flex align-items-start mb-7">
            <i class="ki-outline ki-information-5 fs-2 text-warning me-3 mt-1"></i>
            <div>
                <div class="fw-bold mb-2">Peringatan Margin: Beberapa baris memiliki harga jual di bawah harga beli</div>
                <ul class="mb-0 ps-4">
                    @foreach($violations as $v)
                        <li class="fs-7">
                            <strong>{{ $v['product_name'] }}</strong>:
                            Harga jual Rp {{ number_format($v['selling_price'], 0, ',', '.') }}
                            &lt; Harga beli Rp {{ number_format($v['cost_price'], 0, ',', '.') }}
                            (selisih Rp {{ number_format(abs($v['diff']), 0, ',', '.') }})
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="d-flex gap-3 mb-7">
        @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('web.invoices.customer.issue', $invoice) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="ki-outline ki-check-circle fs-3"></i>
                    Terbitkan Invoice
                </button>
            </form>
        @endif

        @if(in_array($invoice->status, ['issued', 'partial_paid']))
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#voidModal">
                <i class="ki-outline ki-cross-circle fs-3"></i>
                Batalkan (VOID)
            </button>
        @endif
    </div>

    {{-- Header Info Row --}}
    <div class="row mb-7">
        {{-- Customer Info --}}
        <div class="col-lg-6 mb-5 mb-lg-0">
            <div class="card border-primary h-100">
                <div class="card-header bg-light-primary">
                    <h3 class="card-title text-primary fw-bold">
                        <i class="ki-outline ki-hospital fs-2 me-2"></i>
                        TAGIHAN KEPADA
                    </h3>
                </div>
                <div class="card-body">
                    <div class="fs-4 fw-bold text-gray-900 mb-2">{{ $invoice->organization?->name ?? '—' }}</div>
                    @if($invoice->organization?->customer_code)
                        <div class="text-muted fs-7 mb-2">Kode: {{ $invoice->organization->customer_code }}</div>
                    @endif
                    @if($invoice->organization?->address)
                        <div class="text-gray-600 fs-6 mb-1">
                            <i class="ki-outline ki-geolocation fs-6 me-1"></i>
                            {{ $invoice->organization->address }}
                        </div>
                    @endif
                    @if($invoice->organization?->npwp)
                        <div class="text-gray-600 fs-6 mb-1">
                            <i class="ki-outline ki-document fs-6 me-1"></i>
                            NPWP: {{ $invoice->organization->npwp }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Invoice Metadata --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-information-5 fs-2 me-2"></i>
                        Metadata Invoice
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-gray-500 fs-7 d-block">Tanggal Invoice</span>
                            <span class="fw-semibold text-gray-800">
                                {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '—' }}
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-gray-500 fs-7 d-block">Jatuh Tempo</span>
                            <span class="fw-semibold {{ $invoice->due_date?->isPast() && !in_array($invoice->status, ['paid','void']) ? 'text-danger' : 'text-gray-800' }}">
                                {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                            </span>
                        </div>
                        @if($invoice->payment_term)
                            <div class="col-6">
                                <span class="text-gray-500 fs-7 d-block">Payment Term</span>
                                <span class="fw-semibold text-gray-800">{{ $invoice->payment_term }}</span>
                            </div>
                        @endif
                        @if($invoice->salesman)
                            <div class="col-6">
                                <span class="text-gray-500 fs-7 d-block">Salesman</span>
                                <span class="fw-semibold text-gray-800">{{ $invoice->salesman }}</span>
                            </div>
                        @endif
                        @if($invoice->supplierInvoice)
                            <div class="col-12">
                                <span class="text-gray-500 fs-7 d-block">Referensi AP (Supplier Invoice)</span>
                                <a href="{{ route('web.invoices.supplier.show', $invoice->supplierInvoice) }}"
                                   class="fw-semibold text-primary">
                                    {{ $invoice->supplierInvoice->invoice_number }}
                                    @if($invoice->supplierInvoice->supplier)
                                        — {{ $invoice->supplierInvoice->supplier->name }}
                                    @endif
                                </a>
                            </div>
                        @endif
                        @if($invoice->print_count > 0)
                            <div class="col-12">
                                <span class="text-gray-500 fs-7 d-block">Riwayat Cetak</span>
                                <span class="text-gray-700 fs-7">
                                    Dicetak {{ $invoice->print_count }}x
                                    @if($invoice->last_printed_at)
                                        | Terakhir: {{ $invoice->last_printed_at->format('d M Y H:i') }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items Table --}}
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-package fs-2 me-2"></i>
                Rincian Barang
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                    <thead class="bg-light">
                        <tr class="fw-bold text-muted fs-7 text-uppercase">
                            <th class="ps-5 w-30px">No</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Batch / ED</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">UoM</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center">Tax Rate</th>
                            <th class="text-end">Tax Amount</th>
                            <th class="text-end pe-5">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->lineItems as $index => $item)
                            <tr>
                                <td class="ps-5 text-gray-500">{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-bold text-gray-900">{{ $item->product_name }}</span>
                                    @if($item->product)
                                        <div class="text-muted fs-8">{{ $item->product->sku ?? '' }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->batch_number || $item->expiry_date)
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            @if($item->batch_number)
                                                <span class="badge badge-light-primary fs-8">{{ $item->batch_number }}</span>
                                            @endif
                                            @if($item->expiry_date)
                                                <span class="badge badge-light-warning fs-8">{{ $item->expiry_date->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                <td class="text-center text-gray-600">{{ $item->uom ?? 'pcs' }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($item->tax_rate > 0)
                                        <span class="badge badge-light-info">{{ number_format($item->tax_rate, 0) }}%</span>
                                    @else
                                        <span class="text-muted">0%</span>
                                    @endif
                                </td>
                                <td class="text-end text-gray-700">Rp {{ number_format($item->tax_amount, 0, ',', '.') }}</td>
                                <td class="text-end pe-5 fw-bold text-gray-900">
                                    Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8">
                                    <span class="text-gray-500">Tidak ada item</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Calculation Summary + Payment History --}}
    <div class="row">
        {{-- Payment History --}}
        <div class="col-lg-7 mb-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-entrance-right fs-2 me-2"></i>
                        Riwayat Pembayaran
                    </h3>
                    @if(in_array($invoice->status, ['issued', 'partial_paid']))
                        <div class="card-toolbar">
                            <a href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}"
                               class="btn btn-sm btn-primary">
                                <i class="ki-outline ki-plus fs-4"></i>
                                Input Pembayaran
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">Nomor Ref</th>
                                    <th>Metode</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end pe-4 rounded-end">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->paymentAllocations as $alloc)
                                    <tr>
                                        <td class="ps-4 fw-bold text-gray-800">{{ $alloc->payment?->payment_number }}</td>
                                        <td><span class="badge badge-light-secondary">{{ strtoupper($alloc->payment?->payment_method ?? '—') }}</span></td>
                                        <td class="text-end text-success fw-bold">+ Rp {{ number_format($alloc->allocated_amount, 0, ',', '.') }}</td>
                                        <td class="text-end pe-4 text-gray-600">{{ $alloc->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8">
                                            <span class="text-gray-500 fs-7">Belum ada pembayaran</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Calculation Summary --}}
        <div class="col-lg-5 mb-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-calculator fs-2 me-2"></i>
                        Ringkasan Kalkulasi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                            <span class="text-gray-600 fs-6">Subtotal</span>
                            <span class="fw-semibold text-gray-800">Rp {{ number_format($invoice->subtotal_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @if(($invoice->discount_amount ?? 0) > 0)
                            <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                <span class="text-gray-600 fs-6">Diskon</span>
                                <span class="fw-semibold text-danger">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if(($invoice->surcharge ?? 0) > 0)
                            <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                <span class="text-gray-600 fs-6">Surcharge</span>
                                <span class="fw-semibold text-gray-800">+ Rp {{ number_format($invoice->surcharge, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @php
                            $nett = ($invoice->subtotal_amount ?? 0) - ($invoice->discount_amount ?? 0) + ($invoice->surcharge ?? 0);
                        @endphp
                        <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                            <span class="text-gray-600 fs-6">Nett</span>
                            <span class="fw-semibold text-gray-800">Rp {{ number_format($nett, 0, ',', '.') }}</span>
                        </div>
                        @if(($invoice->tax_amount ?? 0) > 0)
                            <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                <span class="text-gray-600 fs-6">PPN</span>
                                <span class="fw-semibold text-gray-800">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if(($invoice->ematerai_fee ?? 0) > 0)
                            <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                                <span class="text-gray-600 fs-6">Biaya e-Meterai</span>
                                <span class="fw-semibold text-gray-800">Rp {{ number_format($invoice->ematerai_fee, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-3 px-4 rounded bg-light-primary mt-2">
                            <span class="text-primary fw-bold fs-5">GRAND TOTAL</span>
                            <span class="text-primary fw-bold fs-4">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="separator my-2"></div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-gray-600 fs-6">Sudah Dibayar</span>
                            <span class="text-success fw-semibold">Rp {{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @php $outstanding = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0); @endphp
                        <div class="d-flex justify-content-between py-3 px-4 rounded {{ $outstanding > 0 ? 'bg-light-danger' : 'bg-light-success' }}">
                            <span class="fw-bold fs-6 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">Sisa Tagihan</span>
                            <span class="fw-bold fs-5 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($outstanding, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alpine.js Live Calculation Preview (DRAFT only) --}}
@if($invoice->status === 'draft')
<div class="card mb-7"
     x-data="{
         surcharge: {{ (float) ($invoice->surcharge ?? 0) }},
         subtotal: {{ (float) ($invoice->subtotal_amount ?? 0) }},
         discount: {{ (float) ($invoice->discount_amount ?? 0) }},
         taxTotal: {{ (float) ($invoice->tax_amount ?? 0) }},
         emateraiThreshold: 5000000,
         emateraiFee: 10000,
         get nett() {
             return this.subtotal - this.discount + parseFloat(this.surcharge || 0);
         },
         get preEmateraiTotal() {
             return this.nett + this.taxTotal;
         },
         get ematerai() {
             return this.preEmateraiTotal >= this.emateraiThreshold ? this.emateraiFee : 0;
         },
         get grandTotal() {
             return this.preEmateraiTotal + this.ematerai;
         },
         formatRp(val) {
             return 'Rp ' + Math.round(val).toLocaleString('id-ID');
         }
     }">
    <div class="card-header bg-light-primary">
        <h3 class="card-title text-primary fw-bold">
            <i class="ki-outline ki-calculator fs-2 me-2"></i>
            Live Calculation Preview
            <span class="badge badge-light-primary ms-2 fs-8">Alpine.js</span>
        </h3>
    </div>
    <div class="card-body">
        <div class="row align-items-start">
            <div class="col-md-4 mb-5 mb-md-0">
                <label class="form-label fw-semibold">Override Surcharge (Rp)</label>
                <input type="number" x-model="surcharge" min="0" step="0.01"
                       class="form-control form-control-solid"
                       placeholder="0">
                <div class="form-text text-muted fs-8">Ubah surcharge untuk melihat kalkulasi live.</div>
            </div>
            <div class="col-md-8">
                <div class="bg-light rounded p-5">
                    <div class="fs-7 fw-bold text-gray-500 text-uppercase mb-3">Ringkasan Kalkulasi (Live)</div>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-600 fs-7">Subtotal</span>
                            <span class="fw-semibold text-gray-800 fs-7" x-text="formatRp(subtotal)"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-600 fs-7">Diskon</span>
                            <span class="fw-semibold text-danger fs-7" x-text="'- ' + formatRp(discount)"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-600 fs-7">Surcharge</span>
                            <span class="fw-semibold text-gray-800 fs-7" x-text="'+ ' + formatRp(surcharge || 0)"></span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="text-gray-700 fs-7 fw-bold">Nett</span>
                            <span class="fw-bold text-gray-900 fs-7" x-text="formatRp(nett)"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-600 fs-7">PPN</span>
                            <span class="fw-semibold text-gray-800 fs-7" x-text="formatRp(taxTotal)"></span>
                        </div>
                        <div class="d-flex justify-content-between" x-show="ematerai > 0">
                            <span class="text-warning fs-7 fw-semibold">
                                <i class="ki-outline ki-information-5 fs-8 me-1"></i>
                                Biaya e-Meterai
                            </span>
                            <span class="fw-semibold text-warning fs-7" x-text="formatRp(ematerai)"></span>
                        </div>
                        <div class="d-flex justify-content-between py-2 px-3 rounded mt-1"
                             :class="grandTotal >= emateraiThreshold ? 'bg-light-warning' : 'bg-light-primary'">
                            <span class="fw-bold fs-6" :class="grandTotal >= emateraiThreshold ? 'text-warning' : 'text-primary'">
                                GRAND TOTAL
                            </span>
                            <span class="fw-bold fs-5" :class="grandTotal >= emateraiThreshold ? 'text-warning' : 'text-primary'"
                                  x-text="formatRp(grandTotal)"></span>
                        </div>
                        <div class="text-muted fs-8 text-center mt-1" x-show="preEmateraiTotal >= emateraiThreshold">
                            <i class="ki-outline ki-information-5 fs-8 me-1"></i>
                            e-Meterai otomatis ditambahkan karena total ≥ Rp 5.000.000
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Void Modal --}}
@if(in_array($invoice->status, ['issued', 'partial_paid']))
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('web.invoices.customer.void', $invoice) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">Batalkan Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-4">
                        <i class="ki-outline ki-information-5 fs-4 me-2"></i>
                        Tindakan ini tidak dapat dibatalkan. Invoice akan berstatus VOID.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold required">Nomor Credit Note</label>
                        <input type="text" name="credit_note_reference" class="form-control"
                               placeholder="CN-XXXX" required>
                        <div class="form-text text-muted">Masukkan nomor credit note sebagai referensi pembatalan.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ki-outline ki-cross-circle fs-4 me-1"></i>
                        Konfirmasi VOID
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
