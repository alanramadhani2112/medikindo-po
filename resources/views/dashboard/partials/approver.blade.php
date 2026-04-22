{{-- ═══════════════════════════════════════════════════════════
     APPROVER DASHBOARD
     Focus: Approval queue — decision ready in 3 seconds
     ═══════════════════════════════════════════════════════════ --}}

{{-- ── PAGE HEADER ──────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-6">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-1">Dashboard Approval</h1>
        <p class="text-gray-500 fs-6 mb-0">
            <i class="ki-outline ki-shield-tick fs-6 me-1 text-warning"></i>
            {{ auth()->user()->name }} &mdash; Approver
        </p>
    </div>
    <a href="{{ route('web.approvals.index') }}" class="btn btn-warning btn-lg position-relative">
        <i class="ki-outline ki-check-circle fs-3 me-1"></i>Proses Approval
        @php $pendingCount = collect($cards)->firstWhere('key', 'pending')['value'] ?? 0; @endphp
        @if(isset($cards[0]['value']) && $cards[0]['value'] > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger fs-8">
            {{ $cards[0]['value'] }}
        </span>
        @endif
    </a>
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
        <a href="{{ $alert['action'] }}" class="btn btn-sm btn-{{ $alert['type'] }} ms-3">Proses →</a>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ── ROW 1: KPI CARDS ─────────────────────────────────────── --}}
<div class="row g-5 g-xl-8 mb-7">
    @foreach($cards as $card)
    <div class="col-6 col-xl-3">
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
                    @if(isset($card['sub']))
                    <span class="text-muted fs-8 d-block">{{ $card['sub'] }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── ROW 2: APPROVAL QUEUE + QUICK ACTIONS ───────────────── --}}
<div class="row g-5 mb-6">
    {{-- Approval Queue dengan inline action --}}
    <div class="col-xl-8">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title flex-column">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-1">
                        Antrean Persetujuan
                        @if(count($pendingList) > 0)
                        <span class="badge badge-danger ms-2">{{ count($pendingList) }}</span>
                        @endif
                    </h3>
                    <span class="text-muted fs-7">Diurutkan: Narkotika → High Value → Terlama</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('web.approvals.index') }}" class="btn btn-sm btn-light-warning">Lihat Semua</a>
                </div>
            </div>
            <div class="card-body pt-4 pb-2">
                <div class="table-responsive">
                    <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                        <thead>
                            <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                                <th class="ps-4 rounded-start">No. PO / Organisasi</th>
                                <th class="text-end">Nilai</th>
                                <th class="text-center">Risk</th>
                                <th class="text-center">Tunggu</th>
                                <th class="text-end pe-4 rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingList as $po)
                            @php
                                $waitDays = $po->submitted_at ? (int) $po->submitted_at->diffInDays(now()) : 0;
                                $waitColor = $waitDays >= 3 ? 'danger' : ($waitDays >= 1 ? 'warning' : 'success');
                                $isHighValue = $po->total_amount > 50000000;
                                $isNarcotic = $po->has_narcotics;
                            @endphp
                            <tr class="{{ $isNarcotic ? 'bg-light-danger' : ($isHighValue ? 'bg-light-warning' : '') }}">
                                <td class="ps-4">
                                    <a href="{{ route('web.po.show', $po) }}" class="text-gray-900 fw-bold text-hover-primary fs-7">
                                        {{ $po->po_number }}
                                    </a>
                                    <div class="text-muted fs-8 mt-1">{{ $po->organization?->name }}</div>
                                </td>
                                <td class="text-end fw-bold fs-7 text-gray-900">
                                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($isNarcotic)
                                    <span class="badge badge-danger fs-8">
                                        <i class="ki-outline ki-shield-cross fs-8 me-1"></i>NARKOTIKA
                                    </span>
                                    @elseif($isHighValue)
                                    <span class="badge badge-warning fs-8">HIGH VALUE</span>
                                    @else
                                    <span class="badge badge-light-success fs-8">NORMAL</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light-{{ $waitColor }} fs-8">{{ $waitDays }}h</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('web.approvals.index') }}" class="btn btn-sm btn-primary">
                                        <i class="ki-outline ki-check fs-6 me-1"></i>Proses
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <i class="ki-outline ki-check-circle fs-3x text-success mb-3 d-block"></i>
                                    <span class="text-muted fs-6">Tidak ada PO yang menunggu persetujuan</span>
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
                    <a href="{{ route('web.approvals.index') }}" class="d-flex align-items-center p-3 rounded bg-light-warning border border-dashed border-warning">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-warning">
                                <i class="ki-outline ki-check-circle fs-3 text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Approve / Reject PO</div>
                            <div class="text-muted fs-8">Proses antrean persetujuan</div>
                        </div>
                    </a>
                    <a href="{{ route('web.po.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-document fs-3 text-primary"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Semua Purchase Order</div>
                            <div class="text-muted fs-8">Riwayat & status PO</div>
                        </div>
                    </a>
                    @can('view_goods_receipt')
                    <a href="{{ route('web.goods-receipts.index') }}" class="d-flex align-items-center p-3 rounded bg-light">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <div class="symbol-label bg-light-info">
                                <i class="ki-outline ki-package fs-3 text-info"></i>
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-gray-900">Goods Receipt</div>
                            <div class="text-muted fs-8">Verifikasi penerimaan barang</div>
                        </div>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 3: RECENT DECISIONS ─────────────────────────────── --}}
@if(count($recentActivity) > 0)
<div class="row g-5">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title flex-column">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-1">Keputusan Approval Terbaru</h3>
                    <span class="text-muted fs-7">Riwayat persetujuan & penolakan</span>
                </div>
            </div>
            <div class="card-body pt-4 pb-2">
                <div class="table-responsive">
                    <table class="table table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                        <thead>
                            <tr class="fw-semibold text-muted fs-7 text-uppercase bg-light">
                                <th class="ps-4 rounded-start">No. PO</th>
                                <th>Approver</th>
                                <th class="text-center">Keputusan</th>
                                <th class="text-end pe-4 rounded-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivity as $approval)
                            @php $statusVal = $approval->status instanceof \BackedEnum ? $approval->status->value : $approval->status; @endphp
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('web.po.show', $approval->purchaseOrder) }}" class="text-gray-900 fw-bold text-hover-primary fs-7">
                                        {{ $approval->purchaseOrder?->po_number ?? '—' }}
                                    </a>
                                </td>
                                <td class="text-gray-700 fs-7">{{ $approval->user?->name ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-light-{{ $statusVal === 'approved' ? 'success' : 'danger' }} fs-8">
                                        {{ strtoupper($statusVal) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4 text-muted fs-8">{{ $approval->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
