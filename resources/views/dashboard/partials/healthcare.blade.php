{{-- HEALTHCARE / CLINIC USER DASHBOARD --}}
{{-- Purpose: Procurement + Payment monitoring --}}

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Dashboard Procurement</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
    @can('create_purchase_orders')
    <a href="{{ route('web.po.create') }}" class="btn btn-primary">
        <i class="ki-outline ki-plus fs-2"></i>
        Buat PO Baru
    </a>
    @endcan
</div>

{{-- Summary Cards --}}
<div class="row g-5 g-xl-8 mb-7">
    @foreach($cards as $card)
    <div class="col-12 col-md-6 col-xl-{{ count($cards) > 4 ? '3' : (12 / count($cards)) }}">
        <div class="card card-flush h-100 bg-{{ $card['color'] }}">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div class="d-flex flex-column">
                        <span class="text-white opacity-75 fw-semibold fs-7 mb-2">{{ $card['label'] }}</span>
                        <span class="text-white fw-bold fs-2x">{{ $card['value'] }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded" style="width:60px;height:60px;">
                        <i class="ki-outline {{ $card['icon'] }} fs-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Main Content Row --}}
<div class="row g-5 g-xl-8 mb-7">
    {{-- Recent Purchase Orders Table --}}
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Purchase Order Terbaru</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Aktivitas pengadaan terkini</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('web.po.index') }}" class="btn btn-sm btn-light-primary">
                        Lihat Semua
                        <i class="ki-outline ki-right fs-5 ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-150px rounded-start">Nomor PO</th>
                                <th class="min-w-150px">Supplier</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-120px">Total</th>
                                <th class="text-end pe-4 min-w-100px rounded-end">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPOs as $po)
                            @php
                                $badgeColor = match($po->status) {
                                    'approved', 'shipped', 'delivered', 'completed' => 'success',
                                    'submitted' => 'warning',
                                    'rejected' => 'danger',
                                    default => 'primary'
                                };
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('web.po.show', $po) }}" class="text-gray-900 fw-bold text-hover-primary fs-6">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6">{{ $po->supplier->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $badgeColor }} fs-7 fw-semibold">{{ strtoupper($po->status) }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-6">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6">{{ $po->created_at->format('d M Y') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold">Belum ada purchase order</span>
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

    {{-- Quick Actions & Alerts --}}
    <div class="col-xl-4">
        {{-- Quick Actions --}}
        <div class="card card-flush mb-5 mb-xl-8">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="card-label fw-bold text-gray-900 fs-3">Aksi Cepat</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex flex-column gap-3">
                    @can('create_purchase_orders')
                    <a href="{{ route('web.po.create') }}" class="btn btn-light-primary justify-content-start">
                        <i class="ki-outline ki-plus fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Buat PO</div>
                            <div class="text-muted fs-7">Ajukan purchase order baru</div>
                        </div>
                    </a>
                    @endcan
                    @can('create_payment')
                    <a href="{{ route('web.payments.index') }}" class="btn btn-light-success justify-content-start">
                        <i class="ki-outline ki-wallet fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Konfirmasi Pembayaran</div>
                            <div class="text-muted fs-7">Catat pembayaran ke supplier</div>
                        </div>
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        @if(count($alerts) > 0)
        <div class="card card-flush">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="card-label fw-bold text-gray-900 fs-3">Notifikasi</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                @foreach($alerts as $alert)
                <div class="alert alert-{{ $alert['type'] }} d-flex align-items-center p-5 mb-5">
                    <i class="ki-outline {{ $alert['icon'] }} fs-2hx text-{{ $alert['type'] }} me-4"></i>
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
    </div>
</div>
