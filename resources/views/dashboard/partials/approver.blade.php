{{-- APPROVER DASHBOARD --}}
{{-- Purpose: Approval control --}}

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Dashboard Approval</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
    <a href="{{ route('web.approvals.index') }}" class="btn btn-primary">
        <i class="ki-solid ki-document fs-2"></i>
        Lihat Semua Approval
    </a>
</div>

{{-- Summary Cards --}}
<div class="row g-5 g-xl-8 mb-7">
    @foreach($cards as $card)
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-flush h-100 bg-{{ $card['color'] }}">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div class="d-flex flex-column">
                        <span class="text-white opacity-75 fw-semibold fs-7 mb-2">{{ $card['label'] }}</span>
                        <span class="text-white fw-bold fs-2x">{{ $card['value'] }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded" style="width:60px;height:60px;">
                        <i class="ki-solid {{ $card['icon'] }} fs-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Alerts --}}
@if(count($alerts) > 0)
<div class="row g-5 g-xl-8 mb-7">
    <div class="col-12">
        @foreach($alerts as $alert)
        <div class="alert alert-{{ $alert['type'] }} d-flex align-items-center p-5 mb-5">
            <i class="ki-solid {{ $alert['icon'] }} fs-2hx text-{{ $alert['type'] }} me-4"></i>
            <div class="d-flex flex-column flex-grow-1">
                <h4 class="mb-1 text-{{ $alert['type'] }} fw-bold">{{ $alert['title'] }}</h4>
                <span class="fs-6">{{ $alert['message'] }}</span>
                @if(isset($alert['action']))
                <a href="{{ $alert['action'] }}" class="fw-bold mt-2">Lihat Detail →</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Main Content Row --}}
<div class="row g-5 g-xl-8 mb-7">
    {{-- Pending Approval Table (Priority) --}}
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Antrean Persetujuan</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">PO yang menunggu persetujuan (prioritas: narkotika/psikotropika)</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('web.approvals.index') }}" class="btn btn-sm btn-light-primary">
                        Lihat Semua
                        <i class="ki-solid ki-right fs-5 ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-150px rounded-start">Nomor PO</th>
                                <th class="min-w-150px">Organisasi</th>
                                <th class="min-w-120px">Total</th>
                                <th class="min-w-100px">Risk Level</th>
                                <th class="text-end pe-4 min-w-100px rounded-end">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingList as $po)
                            @php
                                $hasNarcotics = $po->items->some(function($item) {
                                    return $item->product && (
                                        stripos($item->product->category, 'narkotika') !== false ||
                                        stripos($item->product->category, 'psikotropika') !== false
                                    );
                                });
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('web.po.show', $po) }}" class="text-gray-900 fw-bold text-hover-primary fs-6">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6">{{ $po->organization->name }}</span>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $po->supplier->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-6">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @if($hasNarcotics)
                                    <span class="badge badge-light-danger fs-7 fw-semibold">
                                        <i class="ki-solid ki-shield-cross fs-6 me-1"></i>
                                        HIGH RISK
                                    </span>
                                    @else
                                    <span class="badge badge-light-success fs-7 fw-semibold">NORMAL</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6">{{ $po->submitted_at?->format('d M Y') ?? $po->created_at->format('d M Y') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-solid ki-check-circle fs-3x text-success mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold">Tidak ada PO yang menunggu persetujuan</span>
                                    </div>
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
        <div class="card card-flush mb-5 mb-xl-8">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="card-label fw-bold text-gray-900 fs-3">Aksi Cepat</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex flex-column gap-3">
                    <a href="{{ route('web.approvals.index') }}" class="btn btn-light-primary justify-content-start">
                        <i class="ki-solid ki-check-circle fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Approve PO</div>
                            <div class="text-muted fs-7">Setujui purchase order</div>
                        </div>
                    </a>
                    <a href="{{ route('web.approvals.index') }}" class="btn btn-light-danger justify-content-start">
                        <i class="ki-solid ki-cross-circle fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Reject PO</div>
                            <div class="text-muted fs-7">Tolak purchase order</div>
                        </div>
                    </a>
                    <a href="{{ route('web.po.index') }}" class="btn btn-light-info justify-content-start">
                        <i class="ki-solid ki-document fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Lihat Semua PO</div>
                            <div class="text-muted fs-7">Riwayat purchase order</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity Table --}}
@if(count($recentActivity) > 0)
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Aktivitas Approval Terbaru</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Riwayat keputusan approval</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-150px rounded-start">Nomor PO</th>
                                <th class="min-w-150px">Approver</th>
                                <th class="min-w-100px">Keputusan</th>
                                <th class="text-end pe-4 min-w-120px rounded-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivity as $approval)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('web.po.show', $approval->purchaseOrder) }}" class="text-gray-900 fw-bold text-hover-primary fs-6">
                                        {{ $approval->purchaseOrder->po_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6">{{ $approval->user->name }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $approval->status === 'approved' ? 'success' : 'danger' }} fs-7 fw-semibold">
                                        {{ strtoupper($approval->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6">{{ $approval->created_at->format('d M Y H:i') }}</span>
                                </td>
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
