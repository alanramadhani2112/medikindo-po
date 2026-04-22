{{-- ═══════════════════════════════════════════════════════════
     HEALTHCARE USER DASHBOARD
     Focus: Procurement status + Financial awareness
     ═══════════════════════════════════════════════════════════ --}}

{{-- ── PAGE HEADER ──────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-6">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-1">Dashboard Procurement</h1>
        <p class="text-gray-500 fs-6 mb-0">
            <i class="ki-outline ki-geolocation fs-6 me-1 text-primary"></i>
            {{ auth()->user()->organization?->name ?? 'Organisasi' }} &mdash; {{ auth()->user()->name }}
        </p>
    </div>
    @can('create_purchase_orders')
    <a href="{{ route('web.po.create') }}" class="btn btn-primary btn-lg">
        <i class="ki-outline ki-plus fs-3 me-1"></i>Buat PO Baru
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

{{-- ── ROW 1: 4 KPI CARDS ──────────────────────────────────── --}}
<div class="row g-5 g-xl-8 mb-7">
    {{-- PO Aktif --}}
    <div class="col-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex align-items-center py-5">
                <div class="symbol symbol-50px me-4">
                    <span class="symbol-label bg-light-primary">
                        <i class="ki-outline ki-document fs-2x text-primary"></i>
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 fw-semibold fs-7 d-block">PO Aktif</span>
                    <span class="text-gray-900 fw-bold fs-2">{{ $poActive }}</span>
                    @if($poWaitingApproval > 0)
                    <span class="badge badge-light-warning fs-8 mt-1 d-block">{{ $poWaitingApproval }} pending</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Menunggu Pengiriman --}}
    <div class="col-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex align-items-center py-5">
                <div class="symbol symbol-50px me-4">
                    <span class="symbol-label bg-light-info">
                        <i class="ki-outline ki-delivery fs-2x text-info"></i>
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 fw-semibold fs-7 d-block">Menunggu Pengiriman</span>
                    <span class="text-gray-900 fw-bold fs-2">{{ $poAwaitingDelivery }}</span>
                    <span class="text-muted fs-8 d-block">{{ $poCompletedMonth }} selesai bulan ini</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Sisa Tagihan --}}
    <div class="col-6 col-xl-3">
        <div class="card card-flush h-100 {{ $outstandingInvoices > 0 ? 'border border-danger border-dashed' : '' }}">
            <div class="card-body d-flex align-items-center py-5">
                <div class="symbol symbol-50px me-4">
                    <span class="symbol-label bg-light-danger">
                        <i class="ki-outline ki-bill fs-2x text-danger"></i>
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 fw-semibold fs-7 d-block">Sisa Tagihan</span>
                    <span class="text-gray-900 fw-bold fs-2">Rp {{ number_format($outstandingInvoices / 1000000, 1) }}jt</span>
                    @if($paymentDueSoon > 0)
                    <span class="text-danger fs-8 fw-semibold d-block">Rp {{ number_format($paymentDueSoon / 1000000, 1) }}jt jatuh tempo</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Credit Limit --}}
    <div class="col-6 col-xl-3">
        @php $creditColor = $creditUtilization > 90 ? 'danger' : ($creditUtilization > 70 ? 'warning' : 'success'); @endphp
        <div class="card card-flush h-100">
            <div class="card-body d-flex align-items-center py-5">
                <div class="symbol symbol-50px me-4">
                    <span class="symbol-label bg-light-{{ $creditColor }}">
                        <i class="ki-outline ki-chart-pie-3 fs-2x text-{{ $creditColor }}"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <span class="text-gray-500 fw-semibold fs-7 d-block">Kredit Tersedia</span>
                    <span class="text-{{ $creditColor }} fw-bold fs-2">{{ number_format($creditUtilization, 0) }}%</span>
                    <div class="progress h-4px mt-1" style="max-width: 100px;">
                        <div class="progress-bar bg-{{ $creditColor }}" style="width: {{ min($creditUtilization, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 2: TABEL PO + QUICK ACTIONS ─────────────────────── --}}
<div class="row g-5 mb-6">
    {{-- Tabel PO Terbaru --}}
    <div class="col-xl-8">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title flex-column">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-1">Purchase Order Terbaru</h3>
                    <span class="text-muted fs-7">Aktivitas pengadaan terkini</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('web.po.index') }}" class="btn btn-sm btn-light-primary">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-4 pb-2">
                <div class="table-responsive">
                    <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                        <thead>
                            <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                                <th class="ps-4 rounded-start">No. PO</th>
                                <th>Supplier</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Total</th>
                                <th class="text-end pe-4 rounded-end">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPOs as $po)
                            @php
                                $st = $po->status;
                                $bc = match($st) {
                                    'completed' => 'success',
                                    'approved', 'partially_received' => 'primary',
                                    'submitted' => 'warning',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('web.po.show', $po) }}" class="text-gray-900 fw-bold text-hover-primary fs-7">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td class="text-gray-700 fs-7">{{ $po->supplier?->name ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-light-{{ $bc }} fs-8">{{ strtoupper(str_replace('_', ' ', $st)) }}</span>
                                </td>
                                <td class="text-end text-gray-900 fw-semibold fs-7">
                                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-end pe-4 text-muted fs-8">{{ $po->created_at->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <i class="ki-outline ki-document fs-3x text-gray-300 mb-3 d-block"></i>
                                    <span class="text-muted fs-6">Belum ada purchase order</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                    @can('create_purchase_orders')
                    <a href="{{ route('web.po.create') }}" class="d-flex align-items-center p-3 rounded bg-light-primary text-hover-primary border border-dashed border-primary">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-primary">
                                <i class="ki-outline ki-plus fs-3 text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Buat PO Baru</div>
                            <div class="text-muted fs-8">Ajukan purchase order</div>
                        </div>
                    </a>
                    @endcan

                    @can('confirm_receipt')
                    <a href="{{ route('web.goods-receipts.create') }}" class="d-flex align-items-center p-3 rounded bg-light-warning text-hover-warning">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-warning">
                                <i class="ki-outline ki-package fs-3 text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Terima Barang</div>
                            <div class="text-muted fs-8">Konfirmasi penerimaan GR</div>
                        </div>
                    </a>
                    @endcan

                    @can('submit_payment_proof')
                    <a href="{{ route('web.payment-proofs.create') }}" class="d-flex align-items-center p-3 rounded bg-light-success text-hover-success">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-success">
                                <i class="ki-outline ki-shield-tick fs-3 text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Upload Bukti Bayar</div>
                            <div class="text-muted fs-8">Submit bukti pembayaran</div>
                        </div>
                    </a>
                    @endcan

                    @can('view_payment_status')
                    <a href="{{ route('web.payment-proofs.index') }}" class="d-flex align-items-center p-3 rounded bg-light text-hover-primary">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-info">
                                <i class="ki-outline ki-eye fs-3 text-info"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Status Pembayaran</div>
                            <div class="text-muted fs-8">Pantau bukti bayar</div>
                        </div>
                    </a>
                    @endcan

                    @can('view_invoices')
                    <a href="{{ route('web.invoices.customer.index') }}" class="d-flex align-items-center p-3 rounded bg-light text-hover-primary">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-danger">
                                <i class="ki-outline ki-bill fs-3 text-danger"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Invoice Saya</div>
                            <div class="text-muted fs-8">Tagihan dari Medikindo</div>
                        </div>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
