@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-start mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">AR Aging Dashboard</h1>
            <p class="text-gray-600 fs-6 mb-0">Klasifikasi piutang berdasarkan umur tagihan</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted fs-7">
                <i class="ki-outline ki-calendar fs-6 me-1"></i>
                Per tanggal: <strong>{{ now()->format('d M Y') }}</strong>
            </span>
            <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light-primary btn-sm">
                <i class="ki-outline ki-document fs-4 me-1"></i>Semua Invoice
            </a>
            <a href="{{ route('web.payments.create.incoming') }}" class="btn btn-success btn-sm">
                <i class="ki-outline ki-dollar fs-4 me-1"></i>Tambah Pembayaran
            </a>
        </div>
    </div>

    {{-- ── 5 Bucket Cards (equal width) ───────────────────────── --}}
    @php
        $bucketConfig = [
            'current' => ['label' => 'Belum Jatuh Tempo', 'sublabel' => 'On Time',    'color' => 'success', 'icon' => 'check-circle',   'bg' => '#17c653'],
            '1-30'    => ['label' => '1–30 Hari',         'sublabel' => 'Perhatian',  'color' => 'warning', 'icon' => 'time',            'bg' => '#f6c000'],
            '31-60'   => ['label' => '31–60 Hari',        'sublabel' => 'Waspada',    'color' => 'orange',  'icon' => 'information-5',   'bg' => '#ff6f00'],
            '61-90'   => ['label' => '61–90 Hari',        'sublabel' => 'Kritis',     'color' => 'danger',  'icon' => 'cross-circle',    'bg' => '#f1416c'],
            '90+'     => ['label' => '>90 Hari',          'sublabel' => 'Sangat Kritis','color' => 'danger',  'icon' => 'skull',           'bg' => '#b5001e'],
        ];
        $activeBucket = request('bucket', '');
    @endphp

    <div class="row g-4 mb-6">
        @foreach($bucketConfig as $key => $cfg)
            @php
                $count  = ($buckets[$key] ?? collect())->count();
                $amount = $totals[$key] ?? 0;
                $isActive = $activeBucket === $key;
            @endphp
            <div class="col">
                <a href="{{ route('web.ar-aging.index', array_merge(request()->except(['bucket','page']), ['bucket' => $isActive ? '' : $key])) }}"
                   class="card h-100 text-decoration-none {{ $isActive ? 'ring-2' : '' }}"
                   style="background: {{ $cfg['bg'] }}; border: {{ $isActive ? '3px solid #fff' : '3px solid transparent' }}; transition: transform .15s, box-shadow .15s;"
                   onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,.25)'"
                   onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                    <div class="card-body py-5 px-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ki-outline ki-{{ $cfg['icon'] }} fs-2 text-white opacity-75"></i>
                                <div>
                                    <div class="text-white fw-bold fs-7 lh-1">{{ $cfg['label'] }}</div>
                                    <div class="text-white opacity-60 fs-9">{{ $cfg['sublabel'] }}</div>
                                </div>
                            </div>
                            @if($isActive)
                                <span class="badge bg-white text-dark fs-9 fw-bold">Aktif</span>
                            @endif
                        </div>
                        <div class="text-white fw-bolder" style="font-size: 1.5rem; line-height: 1.2;">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </div>
                        <div class="text-white opacity-70 fs-8 mt-1">
                            {{ $count }} invoice
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- ── Grand Total + Distribution Bar ─────────────────────── --}}
    <div class="card mb-6" style="background: linear-gradient(135deg, #1b4b7f 0%, #153a63 100%);">
        <div class="card-body py-5">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-40px">
                        <div class="symbol-label" style="background: rgba(255,255,255,.15);">
                            <i class="ki-outline ki-bill fs-2 text-white opacity-75"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-400 fs-8 fw-bold text-uppercase">Total Piutang Outstanding</div>
                        <div class="text-white fs-2x fw-bolder">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                    </div>
                </div>
                @if($grandTotal > 0)
                    <div class="text-end">
                        <div class="text-gray-400 fs-8 mb-1">{{ collect($buckets)->sum(fn($b) => $b->count()) }} invoice aktif</div>
                        @php
                            $overdueTotal = ($totals['1-30'] ?? 0) + ($totals['31-60'] ?? 0) + ($totals['61-90'] ?? 0) + ($totals['90+'] ?? 0);
                            $overdueRatio = $grandTotal > 0 ? ($overdueTotal / $grandTotal) * 100 : 0;
                        @endphp
                        <span class="badge {{ $overdueRatio > 50 ? 'badge-danger' : ($overdueRatio > 20 ? 'badge-warning' : 'badge-success') }} fs-7">
                            {{ number_format($overdueRatio, 0) }}% overdue
                        </span>
                    </div>
                @endif
            </div>

            {{-- Distribution Bar --}}
            @if($grandTotal > 0)
                <div class="progress h-12px rounded mb-3" style="background: rgba(255,255,255,.1);">
                    @foreach($bucketConfig as $key => $cfg)
                        @php $pct = $grandTotal > 0 ? (($totals[$key] ?? 0) / $grandTotal) * 100 : 0; @endphp
                        @if($pct > 0)
                            <div class="progress-bar" style="width: {{ $pct }}%; background: {{ $cfg['bg'] }};"
                                 title="{{ $cfg['label'] }}: {{ number_format($pct, 1) }}%"
                                 data-bs-toggle="tooltip"></div>
                        @endif
                    @endforeach
                </div>
                <div class="d-flex gap-4 flex-wrap">
                    @foreach($bucketConfig as $key => $cfg)
                        @if(($totals[$key] ?? 0) > 0)
                            @php $pct = $grandTotal > 0 ? (($totals[$key] ?? 0) / $grandTotal) * 100 : 0; @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="w-10px h-10px rounded-circle flex-shrink-0" style="background: {{ $cfg['bg'] }};"></div>
                                <span class="text-gray-400 fs-8">{{ $cfg['label'] }}: <span class="text-white fw-bold">{{ number_format($pct, 0) }}%</span></span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="progress h-8px rounded" style="background: rgba(255,255,255,.1);">
                    <div class="progress-bar bg-success" style="width: 100%;"></div>
                </div>
                <div class="text-gray-500 fs-8 mt-2">Tidak ada piutang outstanding</div>
            @endif
        </div>
    </div>

    {{-- ── Filter Bar ───────────────────────────────────────────── --}}
    <div class="card mb-6">
        <div class="card-body py-4">
            <form action="{{ route('web.ar-aging.index') }}" method="GET"
                  class="d-flex flex-wrap align-items-center gap-3">
                {{-- Search --}}
                <div class="flex-grow-1" style="min-width: 220px; max-width: 360px;">
                    <div class="position-relative">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4 text-gray-400"></i>
                        <input type="text" name="search"
                               class="form-control form-control-solid ps-12"
                               placeholder="No. Invoice / RS/Klinik..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Aging Bucket --}}
                <div style="min-width: 180px;">
                    <select name="bucket" class="form-select form-select-solid">
                        <option value="">Semua Aging Bucket</option>
                        @foreach($bucketConfig as $key => $cfg)
                            <option value="{{ $key }}" @selected(request('bucket') === $key)>
                                {{ $cfg['label'] }} — {{ $cfg['sublabel'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-filter fs-4 me-1"></i>Filter
                </button>
                @if(request()->hasAny(['search', 'bucket']))
                    <a href="{{ route('web.ar-aging.index') }}" class="btn btn-light">
                        <i class="ki-outline ki-arrows-circle fs-4 me-1"></i>Reset
                    </a>
                @endif

                {{-- Active filter badge --}}
                @if(request('bucket'))
                    <span class="badge badge-light-primary fs-7 px-3 py-2">
                        <i class="ki-outline ki-filter fs-7 me-1"></i>
                        Filter: {{ $bucketConfig[request('bucket')]['label'] ?? '' }}
                    </span>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Aging Tables per Bucket ──────────────────────────────── --}}
    @php
        $filterBucket = request('bucket', '');
        $hasAnyData   = collect($buckets)->some(fn($b) => $b->isNotEmpty());
    @endphp

    @if(!$hasAnyData)
        {{-- Empty State --}}
        <div class="card">
            <div class="card-body py-20 text-center">
                <div class="mb-5">
                    <i class="ki-outline ki-check-circle fs-5x text-success"></i>
                </div>
                <h3 class="text-gray-800 fw-bold mb-2">Tidak Ada Piutang Outstanding</h3>
                <p class="text-gray-500 fs-6 mb-6">
                    Semua invoice sudah lunas atau belum ada invoice yang aktif.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light-primary">
                        <i class="ki-outline ki-document fs-4 me-1"></i>Lihat Semua Invoice
                    </a>
                    <a href="{{ route('web.payments.create.incoming') }}" class="btn btn-light-success">
                        <i class="ki-outline ki-dollar fs-4 me-1"></i>Tambah Pembayaran
                    </a>
                </div>
            </div>
        </div>
    @else
        @foreach($bucketConfig as $key => $cfg)
            @php
                $group = $buckets[$key] ?? collect();
                // Skip if filtered to another bucket
                if ($filterBucket && $filterBucket !== $key) continue;
                // Skip empty buckets
                if ($group->isEmpty()) continue;
            @endphp

            <div class="card card-flush mb-6" id="bucket-{{ $key }}">
                {{-- Card Header with colored left border --}}
                <div class="card-header pt-5" style="border-left: 4px solid {{ $cfg['bg'] }};">
                    <h3 class="card-title fw-bold" style="color: {{ $cfg['bg'] }};">
                        <i class="ki-outline ki-{{ $cfg['icon'] }} fs-3 me-2"></i>
                        {{ $cfg['label'] }}
                        <span class="badge ms-2 fs-8 fw-bold text-white" style="background: {{ $cfg['bg'] }};">
                            {{ $group->count() }} invoice
                        </span>
                    </h3>
                    <div class="card-toolbar d-flex align-items-center gap-3">
                        <span class="fw-bold fs-5" style="color: {{ $cfg['bg'] }};">
                            Rp {{ number_format($totals[$key] ?? 0, 0, ',', '.') }}
                        </span>
                        @if($key !== 'current')
                            <span class="badge badge-light-danger fs-8">
                                <i class="ki-outline ki-time fs-8 me-1"></i>Perlu Tindakan
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                            <thead>
                                <tr class="fw-bold text-muted fs-7 text-uppercase bg-light">
                                    <th class="ps-4 rounded-start">Invoice</th>
                                    <th>RS / Klinik</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Terbayar</th>
                                    <th class="text-end">Outstanding</th>
                                    <th class="text-center">Jatuh Tempo</th>
                                    <th class="text-center">Hari Lewat</th>
                                    <th class="text-center pe-4 rounded-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group->sortByDesc('days_overdue') as $invoice)
                                    @php
                                        $pct = $invoice->total_amount > 0
                                            ? ($invoice->paid_amount / $invoice->total_amount) * 100
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('web.invoices.customer.show', $invoice) }}"
                                               class="fw-bold text-gray-900 text-hover-primary fs-6">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                            <div class="mt-1">
                                                <span class="badge {{ $invoice->status->getBadgeClass() }} fs-9">
                                                    {{ $invoice->status->getLabel() }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-semibold">
                                                {{ $invoice->organization?->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-700 fw-semibold">
                                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-semibold">
                                                Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                                            </span>
                                            @if($pct > 0)
                                                <div class="progress h-3px mt-1" style="min-width: 70px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $pct }}%;"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold fs-6" style="color: {{ $cfg['bg'] }};">
                                                Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($invoice->due_date)
                                                <span class="badge {{ $invoice->days_overdue > 0 ? 'badge-light-danger' : 'badge-light-success' }} fw-semibold">
                                                    {{ $invoice->due_date->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($invoice->days_overdue > 0)
                                                <span class="fw-bold fs-7" style="color: {{ $cfg['bg'] }};">
                                                    {{ $invoice->days_overdue }} hari
                                                </span>
                                            @else
                                                <span class="badge badge-light-success fw-bold">On time</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <x-table-action>
                                                <x-table-action.item :href="route('web.invoices.customer.show', $invoice)" icon="eye" label="Lihat Detail" />
                                                @if($invoice->status->canAcceptPayment())
                                                    <x-table-action.item :href="route('web.payments.create.incoming', ['invoice_id' => $invoice->id])" icon="dollar" label="Bayar Sekarang" color="success" />
                                                @endif
                                            </x-table-action>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: rgba(0,0,0,.03);">
                                    <td colspan="4" class="text-end pe-4 fw-bold text-gray-600 ps-4 py-3">
                                        Subtotal {{ $cfg['label'] }}
                                    </td>
                                    <td class="text-end fw-bolder fs-6 py-3" style="color: {{ $cfg['bg'] }};">
                                        Rp {{ number_format($totals[$key] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Bootstrap tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    });
</script>
@endpush
