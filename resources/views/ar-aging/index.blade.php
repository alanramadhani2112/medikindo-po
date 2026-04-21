<x-layout>
    <x-page-header title="AR Aging" description="Klasifikasi piutang berdasarkan umur tagihan"
        :breadcrumbs="[['label' => 'Piutang (AR)'], ['label' => 'AR Aging']]">
        <x-slot name="actions">
            <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light">
                <i class="ki-outline ki-document fs-3"></i>Semua Invoice
            </a>
            <a href="{{ route('web.payments.create.incoming') }}" class="btn btn-primary">
                <i class="ki-outline ki-dollar fs-3"></i>Tambah Pembayaran
            </a>
        </x-slot>
    </x-page-header>

    @php
        $bucketConfig = [
            'current' => ['label' => 'Belum Jatuh Tempo', 'sublabel' => 'On Time',       'color' => 'success',  'icon' => 'check-circle',   'bg' => '#17c653', 'text' => '#ffffff'],
            '1-30'    => ['label' => '1–30 Hari',         'sublabel' => 'Perhatian',      'color' => 'warning',  'icon' => 'time',           'bg' => '#f6c000', 'text' => '#ffffff'],
            '31-60'   => ['label' => '31–60 Hari',        'sublabel' => 'Waspada',        'color' => 'orange',   'icon' => 'information-5',  'bg' => '#ff6b35', 'text' => '#ffffff'],
            '61-90'   => ['label' => '61–90 Hari',        'sublabel' => 'Kritis',         'color' => 'danger',   'icon' => 'cross-circle',   'bg' => '#f1416c', 'text' => '#ffffff'],
            '90+'     => ['label' => '>90 Hari',          'sublabel' => 'Sangat Kritis',  'color' => 'crimson',  'icon' => 'skull',          'bg' => '#9b1c3a', 'text' => '#ffffff'],
        ];
        $activeBucket = request('bucket', '');
        $overdueTotal = ($totals['1-30'] ?? 0) + ($totals['31-60'] ?? 0) + ($totals['61-90'] ?? 0) + ($totals['90+'] ?? 0);
        $overdueRatio = $grandTotal > 0 ? ($overdueTotal / $grandTotal) * 100 : 0;
    @endphp

    {{-- ── Stat Cards ──────────────────────────────────────────── --}}
    <div class="row g-5 g-xl-8 mb-7">
        @foreach($bucketConfig as $key => $cfg)
            @php
                $count    = ($buckets[$key] ?? collect())->count();
                $amount   = $totals[$key] ?? 0;
                $isActive = $activeBucket === $key;
                $href     = route('web.ar-aging.index', array_merge(
                    request()->except(['bucket','page']),
                    ['bucket' => $isActive ? '' : $key]
                ));
            @endphp
            <div class="col-12 col-md-6 col-xl">
                <a href="{{ $href }}" class="card card-flush hoverable h-100 text-decoration-none {{ $isActive ? 'shadow-lg' : '' }}"
                   style="background-color: {{ $cfg['bg'] }}; {{ $isActive ? 'transform: scale(1.02); transition: all 0.3s ease; box-shadow: 0 0 0 3px rgba(255,255,255,0.5) !important;' : 'transition: all 0.2s ease;' }}">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-5">
                            <div class="d-flex flex-column">
                                <span class="fw-semibold fs-7 mb-1" style="color: {{ $cfg['text'] }}; opacity: 0.9;">{{ $cfg['label'] }}</span>
                                <span class="fs-8" style="color: {{ $cfg['text'] }}; opacity: 0.75;">{{ $cfg['sublabel'] }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center rounded" style="width:50px;height:50px; background: rgba(255,255,255,0.2);">
                                <i class="ki-outline ki-{{ $cfg['icon'] }} fs-2x" style="color: {{ $cfg['text'] }};"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold fs-2x mb-1" style="color: {{ $cfg['text'] }};">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </span>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fs-7" style="color: {{ $cfg['text'] }}; opacity: 0.75;">{{ $count }} invoice</span>
                                @if($isActive)
                                    <span class="badge fs-8 fw-bold" style="background: rgba(255,255,255,0.9); color: {{ $cfg['bg'] }};">
                                        <i class="ki-outline ki-check fs-7 me-1"></i>Aktif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- ── Summary Bar ──────────────────────────────────────────── --}}
    <div class="card card-flush mb-7" style="background: #ffffff; border: 1px solid #17c653; box-shadow: 0 4px 20px rgba(23, 198, 83, 0.12);">
        <div class="card-body py-6">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="d-flex align-items-center justify-content-center rounded" style="width:60px;height:60px; background: rgba(23,198,83,0.1);">
                        <i class="ki-outline ki-bill fs-2x" style="color: #17c653;"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-semibold fs-7 text-uppercase mb-1 d-block">Total Piutang Outstanding</span>
                        <span class="fw-bold fs-2x text-gray-900">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-5">
                    <div class="text-center">
                        <span class="text-muted fs-8 mb-1 d-block">Invoice Aktif</span>
                        <span class="fw-bold fs-3 text-gray-900">{{ collect($buckets)->sum(fn($b) => $b->count()) }}</span>
                    </div>
                    <div class="separator separator-vertical h-40px" style="background: #e4e6ef;"></div>
                    <div class="text-center">
                        <span class="text-muted fs-8 mb-1 d-block">Overdue</span>
                        <span class="badge {{ $overdueRatio > 50 ? 'badge-danger' : ($overdueRatio > 20 ? 'badge-warning' : 'badge-success') }} fs-6 px-3 py-2">
                            {{ number_format($overdueRatio, 0) }}%
                        </span>
                    </div>
                </div>
            </div>

            @if($grandTotal > 0)
                <div class="progress h-8px rounded mt-6 mb-3" style="background: #e8f5e9;">
                    @foreach($bucketConfig as $key => $cfg)
                        @php 
                            $pct = $grandTotal > 0 ? (($totals[$key] ?? 0) / $grandTotal) * 100 : 0;
                            // Use green shades for all segments
                            $greenShades = [
                                'current' => '#17c653',  // bright green
                                '1-30'    => '#10a142',  // medium green
                                '31-60'   => '#0d8435',  // darker green
                                '61-90'   => '#0a6629',  // very dark green
                                '90+'     => '#074d1f',  // darkest green
                            ];
                            $barColor = $greenShades[$key] ?? '#17c653';
                        @endphp
                        @if($pct > 0)
                            <div class="progress-bar" style="width:{{ $pct }}%; background:{{ $barColor }};"
                                 title="{{ $cfg['label'] }}: {{ number_format($pct,1) }}%"
                                 data-bs-toggle="tooltip"></div>
                        @endif
                    @endforeach
                </div>
                <div class="d-flex gap-5 flex-wrap">
                    @foreach($bucketConfig as $key => $cfg)
                        @if(($totals[$key] ?? 0) > 0)
                            @php
                                $pct = $grandTotal > 0 ? (($totals[$key] ?? 0) / $grandTotal) * 100 : 0;
                                // Use same green shades as progress bar
                                $greenShades = [
                                    'current' => '#17c653',
                                    '1-30'    => '#10a142',
                                    '31-60'   => '#0d8435',
                                    '61-90'   => '#0a6629',
                                    '90+'     => '#074d1f',
                                ];
                                $legendColor = $greenShades[$key] ?? '#17c653';
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="w-8px h-8px rounded-circle" style="background:{{ $legendColor }};"></div>
                                <span class="text-muted fs-8">{{ $cfg['label'] }}: <strong class="text-gray-800">{{ number_format($pct,0) }}%</strong></span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Filter ───────────────────────────────────────────────── --}}
    <div class="card card-flush mb-7">
        <div class="card-body py-5">
            <form action="{{ route('web.ar-aging.index') }}" method="GET"
                  class="d-flex flex-wrap align-items-center gap-3">
                <div class="flex-grow-1" style="max-width:360px;">
                    <div class="position-relative">
                        <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                        <input type="text" name="search" class="form-control form-control-solid ps-12"
                               placeholder="Cari No. Invoice atau RS/Klinik..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div style="min-width:200px;">
                    <select name="bucket" class="form-select form-select-solid">
                        <option value="">Semua Aging Bucket</option>
                        @foreach($bucketConfig as $key => $cfg)
                            <option value="{{ $key }}" @selected(request('bucket') === $key)>
                                {{ $cfg['label'] }} — {{ $cfg['sublabel'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-filter fs-3"></i>Filter
                </button>
                @if(request()->hasAny(['search','bucket']))
                    <a href="{{ route('web.ar-aging.index') }}" class="btn btn-light">
                        <i class="ki-outline ki-arrows-circle fs-3"></i>Reset
                    </a>
                    @if(request('bucket'))
                        <span class="badge badge-light-primary fs-7 px-3 py-2">
                            <i class="ki-outline ki-filter fs-7 me-1"></i>
                            {{ $bucketConfig[request('bucket')]['label'] ?? '' }}
                        </span>
                    @endif
                @endif
            </form>
        </div>
    </div>

    {{-- ── Tables per Bucket ────────────────────────────────────── --}}
    @php
        $filterBucket = request('bucket', '');
        $hasAnyData   = collect($buckets)->some(fn($b) => $b->isNotEmpty());
    @endphp

    @if(!$hasAnyData)
        <div class="card card-flush">
            <div class="card-body text-center py-20">
                <div class="d-flex flex-column align-items-center">
                    <i class="ki-outline ki-check-circle fs-5x text-success opacity-50 mb-5"></i>
                    <h3 class="text-gray-900 fw-bold fs-2 mb-3">Tidak Ada Piutang Outstanding</h3>
                    <p class="text-gray-600 fs-5 mb-8 max-w-400px">Semua invoice sudah lunas atau belum ada invoice yang aktif saat ini.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light-primary">
                            <i class="ki-outline ki-document fs-3"></i>Lihat Semua Invoice
                        </a>
                        <a href="{{ route('web.payments.create.incoming') }}" class="btn btn-light-success">
                            <i class="ki-outline ki-dollar fs-3"></i>Tambah Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        @foreach($bucketConfig as $key => $cfg)
            @php
                $group = $buckets[$key] ?? collect();
                if ($filterBucket && $filterBucket !== $key) continue;
                if ($group->isEmpty()) continue;
            @endphp

            <div class="card card-flush mb-7" id="bucket-{{ $key }}">
                <div class="card-header border-0 pt-6">
                    <div class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">
                            <i class="ki-outline ki-{{ $cfg['icon'] }} fs-2 me-2 text-{{ $cfg['color'] }}"></i>
                            {{ $cfg['label'] }}
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ $cfg['sublabel'] }}</span>
                    </div>
                    <div class="card-toolbar d-flex align-items-center gap-3">
                        <span class="badge badge-light-{{ $cfg['color'] }} fs-7 fw-bold px-3 py-2">
                            {{ $group->count() }} invoice
                        </span>
                        <span class="fw-bold fs-3 text-{{ $cfg['color'] }}">
                            Rp {{ number_format($totals[$key] ?? 0, 0, ',', '.') }}
                        </span>
                        @if($key !== 'current')
                            <span class="badge badge-light-danger fs-7">
                                <i class="ki-outline ki-time fs-7 me-1"></i>Perlu Tindakan
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-200px rounded-start">Invoice</th>
                                    <th class="min-w-150px">RS / Klinik</th>
                                    <th class="text-end min-w-120px">Total</th>
                                    <th class="text-end min-w-120px">Terbayar</th>
                                    <th class="text-end min-w-130px">Outstanding</th>
                                    <th class="text-center min-w-120px">Jatuh Tempo</th>
                                    <th class="text-center min-w-100px">Hari Lewat</th>
                                    <th class="text-end pe-4 min-w-80px rounded-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group->sortByDesc('days_overdue') as $invoice)
                                    @php
                                        $pct = $invoice->total_amount > 0
                                            ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('web.invoices.customer.show', $invoice) }}"
                                               class="text-gray-900 fw-bold text-hover-primary fs-6">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                            <div class="mt-1">
                                                <span class="badge {{ $invoice->status->getBadgeClass() }} fs-8">
                                                    {{ $invoice->status->getLabel() }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-semibold fs-6">
                                                {{ $invoice->organization?->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-semibold fs-6">
                                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-semibold d-block fs-6">
                                                Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                                            </span>
                                            @if($pct > 0)
                                                <div class="progress h-4px mt-1" style="min-width:80px;">
                                                    <div class="progress-bar bg-success" style="width:{{ $pct }}%;"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-{{ $cfg['color'] }} fs-6">
                                                Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($invoice->due_date)
                                                <span class="fw-semibold {{ $invoice->days_overdue > 0 ? 'text-danger' : 'text-gray-700' }} fs-6">
                                                    {{ $invoice->due_date->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted fs-6">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($invoice->days_overdue > 0)
                                                <span class="badge badge-light-{{ $cfg['color'] }} fw-bold">
                                                    <i class="ki-outline ki-time fs-8 me-1"></i>
                                                    {{ $invoice->days_overdue }} hari
                                                </span>
                                            @else
                                                <span class="badge badge-light-success fw-bold">On time</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <x-table-action>
                                                <x-table-action.item :href="route('web.invoices.customer.show', $invoice)" icon="eye" label="Lihat Detail" />
                                                @if($invoice->status->canAcceptPayment())
                                                    <x-table-action.item
                                                        :href="route('web.payments.create.incoming', ['invoice_id' => $invoice->id])"
                                                        icon="dollar" label="Bayar Sekarang" color="success" />
                                                @endif
                                            </x-table-action>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold bg-light">
                                    <td colspan="4" class="text-end ps-4 py-4 text-gray-600 fs-6">
                                        Subtotal {{ $cfg['label'] }}
                                    </td>
                                    <td class="text-end py-4 fw-bold fs-5 text-{{ $cfg['color'] }}">
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

</x-layout>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    
    // Add smooth hover effects for stat cards
    document.querySelectorAll('.card.hoverable').forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('shadow-lg')) {
                this.style.transform = 'translateY(-2px)';
                this.style.transition = 'all 0.3s ease';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('shadow-lg')) {
                this.style.transform = 'translateY(0)';
            }
        });
    });
});
</script>

<style>
.max-w-400px {
    max-width: 400px;
}

.card.hoverable {
    transition: all 0.3s ease;
}

.card.hoverable:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}

.table-responsive {
    border-radius: 0.475rem;
}

.table thead th {
    border-bottom: 1px solid #e4e6ef;
    font-weight: 600;
    font-size: 0.825rem;
    text-transform: uppercase;
    letter-spacing: 0.035em;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.progress {
    background-color: #f1f3f6;
}

.badge {
    font-weight: 600;
}

@media (max-width: 768px) {
    .card-toolbar {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush
