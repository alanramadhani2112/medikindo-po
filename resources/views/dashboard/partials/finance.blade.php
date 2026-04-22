{{-- ═══════════════════════════════════════════════════════════
     FINANCE DASHBOARD
     Focus: Cashflow control + Payment proof verification
     ═══════════════════════════════════════════════════════════ --}}

{{-- ── PAGE HEADER ──────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-6">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-1">Dashboard Finance</h1>
        <p class="text-gray-500 fs-6 mb-0">
            <i class="ki-outline ki-dollar fs-6 me-1 text-success"></i>
            {{ auth()->user()->name }} &mdash; Finance
        </p>
    </div>
    @can('verify_payment_proof')
    <a href="{{ route('web.payment-proofs.index') }}" class="btn btn-success btn-lg position-relative">
        <i class="ki-outline ki-shield-tick fs-3 me-1"></i>Verifikasi Bukti Bayar
        @if(($paymentProofsPending ?? 0) > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger fs-8">
            {{ $paymentProofsPending }}
        </span>
        @endif
    </a>
    @endcan
</div>

{{-- ── CRITICAL ALERTS ─────────────────────────────────────── --}}
@if(count($alerts) > 0)
<div class="mb-6">
    @foreach($alerts as $alert)
    <div class="alert alert-{{ $alert['type'] }} border border-{{ $alert['type'] }} d-flex align-items-center p-4 mb-3">
        <i class="ki-outline {{ $alert['icon'] }} fs-2x text-{{ $alert['type'] }} me-4 flex-shrink-0"></i>
        <div class="flex-grow-1">
            <div class="fw-bold fs-6 text-{{ $alert['type'] }}">{{ $alert['title'] }}</div>
            <div class="fs-7 text-gray-700">{{ $alert['message'] }}</div>
        </div>
        @if(isset($alert['action']))
        <a href="{{ $alert['action'] }}" class="btn btn-sm btn-{{ $alert['type'] }} ms-3">Lihat →</a>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ── ROW 1: 5 KPI CARDS ──────────────────────────────────── --}}
<div class="row g-5 g-xl-8 mb-7">
    @foreach($cards as $card)
    <div class="col-6 col-xl">
        <div class="card card-flush h-100 {{ isset($card['alert']) && $card['alert'] ? 'border border-' . $card['color'] . ' border-dashed' : '' }}">
            <div class="card-body d-flex align-items-center py-5">
                <div class="symbol symbol-50px me-4">
                    <span class="symbol-label bg-light-{{ $card['color'] }}">
                        <i class="ki-outline {{ $card['icon'] }} fs-2x text-{{ $card['color'] }}"></i>
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 fw-semibold fs-7 d-block">{{ $card['label'] }}</span>
                    <span class="text-gray-900 fw-bold fs-2">{{ $card['value'] }}</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── PARTIAL DELIVERY MONITOR ────────────────────────────── --}}
@php
    $partialPOs = \App\Models\PurchaseOrder::with(['organization', 'supplier', 'goodsReceipts.items'])
        ->where('status', \App\Models\PurchaseOrder::STATUS_PARTIALLY_RECEIVED)
        ->orderBy('approved_at')
        ->limit(5)
        ->get();
@endphp
@if($partialPOs->isNotEmpty())
<div class="card shadow-sm border border-warning border-dashed mb-6">
    <div class="card-header pt-5 pb-0 border-0">
        <div class="card-title flex-column">
            <h3 class="fw-bold text-gray-900 fs-4 mb-1 d-flex align-items-center gap-2">
                <i class="ki-outline ki-delivery fs-3 text-warning"></i>
                Pengiriman Sebagian — Menunggu Sisa Barang
            </h3>
            <span class="text-muted fs-7">{{ $partialPOs->count() }} PO masih menunggu sisa pengiriman dari supplier</span>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('web.goods-receipts.index') }}" class="btn btn-sm btn-light-warning">Lihat Semua</a>
        </div>
    </div>
    <div class="card-body pt-4 pb-2">
        <div class="table-responsive">
            <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                <thead>
                    <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                        <th class="ps-4 rounded-start">PO / Supplier</th>
                        <th>Organisasi</th>
                        <th>Progress</th>
                        <th class="text-center">Pengiriman ke-</th>
                        <th class="text-center">Sejak Disetujui</th>
                        <th class="text-end pe-4 rounded-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partialPOs as $po)
                    @php
                        $gr = $po->goodsReceipts->first();
                        $totalOrdered  = $po->items->sum('quantity');
                        $totalReceived = $gr ? $gr->items->sum('quantity_received') : 0;
                        $remaining     = max(0, $totalOrdered - $totalReceived);
                        $pct           = $totalOrdered > 0 ? ($totalReceived / $totalOrdered) * 100 : 0;
                        $deliveryCount = $gr ? $gr->deliveries()->count() : 0;
                        $daysSince     = $po->approved_at ? (int) $po->approved_at->diffInDays(now()) : 0;
                        $urgencyColor  = $daysSince > 14 ? 'danger' : ($daysSince > 7 ? 'warning' : 'success');
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('web.po.show', $po) }}" class="fw-bold text-gray-900 text-hover-primary fs-7">{{ $po->po_number }}</a>
                            <div class="text-muted fs-8 mt-1">{{ $po->supplier?->name }}</div>
                        </td>
                        <td class="text-gray-700 fs-7">{{ $po->organization?->name }}</td>
                        <td style="min-width: 160px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <div class="progress h-6px">
                                        <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                                <span class="text-warning fw-bold fs-8 text-nowrap">{{ $totalReceived }}/{{ $totalOrdered }}</span>
                            </div>
                            <div class="text-muted fs-9 mt-1">Sisa <span class="text-danger fw-bold">{{ $remaining }}</span> unit</div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light-primary fs-8">ke-{{ $deliveryCount }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light-{{ $urgencyColor }} fs-8">{{ $daysSince }} hari</span>
                        </td>
                        <td class="text-end pe-4">
                            @if($gr)
                            <a href="{{ route('web.goods-receipts.show', $gr) }}" class="btn btn-sm btn-light-primary">Lihat GR</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ── ROW 2: PAYMENT PROOFS + OUTSTANDING INVOICES ────────── --}}
<div class="row g-5 mb-6">
    {{-- Payment Proofs Pending --}}
    <div class="col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title flex-column">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-1 d-flex align-items-center gap-2">
                        Bukti Bayar Pending
                        @if(($paymentProofsPending ?? 0) > 0)
                        <span class="badge badge-danger">{{ $paymentProofsPending }}</span>
                        @endif
                    </h3>
                    <span class="text-muted fs-7">Menunggu verifikasi & persetujuan</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('web.payment-proofs.index') }}" class="btn btn-sm btn-light-success">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-4 pb-2">
                @php
                    $pendingProofs = \App\Models\PaymentProof::with(['customerInvoice.organization', 'submittedBy'])
                        ->whereIn('status', [
                            \App\Enums\PaymentProofStatus::SUBMITTED->value,
                            \App\Enums\PaymentProofStatus::VERIFIED->value,
                            \App\Enums\PaymentProofStatus::RESUBMITTED->value,
                        ])
                        ->latest()
                        ->limit(8)
                        ->get();
                @endphp
                @forelse($pendingProofs as $proof)
                @php $statusVal = $proof->status instanceof \BackedEnum ? $proof->status->value : $proof->status; @endphp
                <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-gray-200">
                    <div class="d-flex align-items-center gap-3">
                        <div class="symbol symbol-35px">
                            <div class="symbol-label bg-light-{{ $statusVal === 'verified' ? 'info' : 'warning' }}">
                                <i class="ki-outline ki-shield-tick fs-5 text-{{ $statusVal === 'verified' ? 'info' : 'warning' }}"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-7 text-gray-900">{{ $proof->customerInvoice?->invoice_number ?? '—' }}</div>
                            <div class="text-muted fs-8">{{ $proof->customerInvoice?->organization?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold fs-7 text-gray-900">Rp {{ number_format($proof->amount, 0, ',', '.') }}</div>
                        <a href="{{ route('web.payment-proofs.show', $proof) }}" class="btn btn-xs btn-light-success mt-1" style="font-size: 10px; padding: 2px 8px;">
                            Verifikasi
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="ki-outline ki-check-circle fs-3x text-success mb-3 d-block"></i>
                    <span class="text-muted fs-6">Tidak ada bukti bayar pending</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Outstanding Invoices --}}
    <div class="col-xl-7">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title flex-column">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-1">Invoice Outstanding</h3>
                    <span class="text-muted fs-7">Diurutkan berdasarkan jatuh tempo</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('web.invoices.supplier.index') }}" class="btn btn-sm btn-light-primary">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-4 pb-2">
                <div class="table-responsive">
                    <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                        <thead>
                            <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                                <th class="ps-4 rounded-start">Invoice / Supplier</th>
                                <th class="text-end">Sisa</th>
                                <th class="text-center">Jatuh Tempo</th>
                                <th class="text-end pe-4 rounded-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingInvoices as $invoice)
                            @php
                                $isOverdue = $invoice->due_date && $invoice->due_date->isPast();
                                $daysOverdue = $isOverdue ? now()->diffInDays($invoice->due_date) : 0;
                            @endphp
                            <tr class="{{ $isOverdue ? 'bg-light-danger' : '' }}">
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-7">{{ $invoice->invoice_number }}</span>
                                    <div class="text-muted fs-8 mt-1">{{ $invoice->supplier?->name ?? '—' }}</div>
                                </td>
                                <td class="text-end fw-bold fs-7 {{ $isOverdue ? 'text-danger' : 'text-gray-900' }}">
                                    Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="fs-8 {{ $isOverdue ? 'text-danger fw-bold' : 'text-gray-600' }}">
                                        {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                                    </span>
                                    @if($isOverdue)
                                    <div class="badge badge-danger fs-9 mt-1">{{ $daysOverdue }}h terlambat</div>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @php $sv = $invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->status; @endphp
                                    <span class="badge badge-light-{{ $isOverdue ? 'danger' : 'warning' }} fs-8">{{ strtoupper($sv) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-8">
                                    <i class="ki-outline ki-check-circle fs-3x text-success mb-3 d-block"></i>
                                    <span class="text-muted fs-6">Tidak ada invoice outstanding</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 3: QUICK ACTIONS + RECENT PAYMENTS ──────────────── --}}
<div class="row g-5">
    {{-- Quick Actions --}}
    <div class="col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-0">Aksi Cepat</h3>
                </div>
            </div>
            <div class="card-body pt-4">
                <div class="d-flex flex-column gap-3">
                    @can('verify_payment_proof')
                    <a href="{{ route('web.payment-proofs.index') }}" class="d-flex align-items-center p-3 rounded bg-light-success border border-dashed border-success">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-success">
                                <i class="ki-outline ki-shield-tick fs-3 text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Verifikasi Bukti Bayar</div>
                            <div class="text-muted fs-8">Review & approve payment proof</div>
                        </div>
                    </a>
                    @endcan
                    @can('create_invoices')
                    <a href="{{ route('web.invoices.customer.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-bill fs-3 text-primary"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Customer Invoices</div>
                            <div class="text-muted fs-8">Tagihan ke RS/Klinik</div>
                        </div>
                    </a>
                    @endcan
                    @can('view_invoices')
                    <a href="{{ route('web.invoices.supplier.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-danger">
                                <i class="ki-outline ki-entrance-right fs-3 text-danger"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Supplier Invoices</div>
                            <div class="text-muted fs-8">Tagihan dari distributor</div>
                        </div>
                    </a>
                    @endcan
                    @can('process_payments')
                    <a href="{{ route('web.payments.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-info">
                                <i class="ki-outline ki-book fs-3 text-info"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Payment Ledger</div>
                            <div class="text-muted fs-8">Buku kas & pembayaran</div>
                        </div>
                    </a>
                    @endcan
                    @can('view_reports')
                    <a href="{{ route('web.ar-aging.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-warning">
                                <i class="ki-outline ki-calendar-tick fs-3 text-warning"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">AR Aging</div>
                            <div class="text-muted fs-8">Analisis umur piutang</div>
                        </div>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="col-xl-8">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title flex-column">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-1">Pembayaran Terbaru</h3>
                    <span class="text-muted fs-7">Riwayat transaksi kas masuk & keluar</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('web.payments.index') }}" class="btn btn-sm btn-light-primary">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-4 pb-2">
                <div class="table-responsive">
                    <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                        <thead>
                            <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                                <th class="ps-4 rounded-start">No. Pembayaran</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end pe-4 rounded-end">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('web.payments.show', $payment) }}" class="text-gray-900 fw-bold text-hover-primary fs-7">
                                        {{ $payment->payment_number ?? 'PAY-' . $payment->id }}
                                    </a>
                                    <div class="text-muted fs-8 mt-1">{{ $payment->payment_method ?? 'Transfer' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light-{{ $payment->type === 'incoming' ? 'success' : 'danger' }} fs-8">
                                        {{ $payment->type === 'incoming' ? 'MASUK' : 'KELUAR' }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold fs-7 text-{{ $payment->type === 'incoming' ? 'success' : 'danger' }}">
                                    {{ $payment->type === 'incoming' ? '+' : '-' }} Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="text-end pe-4 text-muted fs-8">{{ $payment->payment_date->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-8">
                                    <span class="text-muted fs-6">Belum ada pembayaran</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
