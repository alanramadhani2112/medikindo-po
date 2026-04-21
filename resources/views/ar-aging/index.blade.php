<x-layout>
    <x-page-header title="AR Aging Dashboard" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted fs-7">
                    <i class="ki-outline ki-calendar fs-6 me-1"></i>
                    Per tanggal: <strong>{{ now()->format('d M Y') }}</strong>
                </span>
                <x-button type="link" href="{{ route('web.invoices.customer.index') }}" color="light-primary" size="sm" icon="document">
                    Semua Invoice
                </x-button>
                <x-button type="link" href="{{ route('web.payments.create.incoming') }}" color="success" size="sm" icon="dollar">
                    Tambah Pembayaran
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    @php
        $bucketConfig = [
            'current' => ['label' => 'Belum Jatuh Tempo', 'sublabel' => 'On Time',    'color' => 'success', 'icon' => 'check-circle',   'bg' => '#17c653'],
            '1-30'    => ['label' => '1–30 Hari',         'sublabel' => 'Perhatian',  'color' => 'warning', 'icon' => 'information-5',   'bg' => '#f6c000'],
            '31-60'   => ['label' => '31–60 Hari',        'sublabel' => 'Waspada',    'color' => 'orange',  'icon' => 'time',            'bg' => '#ff6f00'],
            '61-90'   => ['label' => '61–90 Hari',        'sublabel' => 'Kritis',     'color' => 'danger',  'icon' => 'cross-circle',    'bg' => '#f1416c'],
            '90+'     => ['label' => '>90 Hari',          'sublabel' => 'Sangat Kritis','color' => 'danger',  'icon' => 'shield-cross',    'bg' => '#b5001e'],
        ];
        $activeBucket = request('bucket', '');
    @endphp

    {{-- ── 5 Bucket Cards (Minimalist) ───────────────────────── --}}
    <div class="row g-5 mb-6">
        @foreach($bucketConfig as $key => $cfg)
            @php
                $count  = ($buckets[$key] ?? collect())->count();
                $amount = $totals[$key] ?? 0;
                $isActive = $activeBucket === $key;
            @endphp
            <div class="col">
                <a href="{{ route('web.ar-aging.index', array_merge(request()->except(['bucket','page']), ['bucket' => $isActive ? '' : $key])) }}"
                   class="card h-100 text-decoration-none transition-3d {{ $isActive ? 'border-primary border-dashed bg-light-primary' : 'border-gray-200' }}"
                   style="border-width: 1px; border-style: solid;">
                    <div class="card-body py-5 px-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="symbol symbol-35px">
                                    <div class="symbol-label bg-light-{{ $cfg['color'] }}">
                                        <i class="ki-outline ki-{{ $cfg['icon'] }} fs-2 text-{{ $cfg['color'] }}"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-800 fw-bold fs-7 lh-1">{{ $cfg['label'] }}</div>
                                    <div class="text-muted fs-9">{{ $cfg['sublabel'] }}</div>
                                </div>
                            </div>
                            @if($isActive)
                                <i class="ki-outline ki-filter-tick fs-4 text-primary"></i>
                            @endif
                        </div>
                        <div class="text-gray-900 fw-bolder fs-4">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </div>
                        <div class="text-muted fs-8 mt-1">
                            {{ $count }} invoice
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- ── Grand Total (Minimalist) ───────────────────────────── --}}
    <x-card class="mb-3 pt-6">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-4">
                <div class="symbol symbol-50px">
                    <div class="symbol-label bg-light-primary">
                        <i class="ki-outline ki-bill fs-1 text-primary"></i>
                    </div>
                </div>
                <div>
                    <div class="text-gray-500 fs-7 fw-bold text-uppercase mb-1">Total Piutang Outstanding</div>
                    <div class="text-gray-900 fs-1 fw-bolder">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-10">
                @if($grandTotal > 0)
                    @php
                        $overdueTotal = ($totals['1-30'] ?? 0) + ($totals['31-60'] ?? 0) + ($totals['61-90'] ?? 0) + ($totals['90+'] ?? 0);
                        $overdueRatio = $grandTotal > 0 ? ($overdueTotal / $grandTotal) * 100 : 0;
                    @endphp
                    <div class="text-end">
                        <div class="text-gray-500 fs-8 mb-1">Status Portofolio</div>
                        <x-badge color="{{ $overdueRatio > 50 ? 'danger' : ($overdueRatio > 20 ? 'warning' : 'success') }}" size="lg">
                            {{ number_format($overdueRatio, 0) }}% Overdue
                        </x-badge>
                    </div>
                    
                    <div class="text-end">
                        <div class="text-gray-500 fs-8 mb-1">Volume Tagihan</div>
                        <div class="text-gray-800 fs-4 fw-bold">{{ collect($buckets)->sum(fn($b) => $b->count()) }} Invoice</div>
                    </div>
                @endif
            </div>
        </div>
    </x-card>

    {{-- ── Filter Bar ───────────────────────────────────────────── --}}
    <x-filter-bar :action="route('web.ar-aging.index')" method="GET">
        <div class="d-flex align-items-center position-relative my-1 me-5">
            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
            <input type="text" name="search" class="form-control form-control-solid w-250px ps-12" 
                   placeholder="No. Invoice / RS/Klinik..." value="{{ request('search') }}">
        </div>

        <div class="d-flex align-items-center position-relative my-1">
            <select name="bucket" class="form-select form-select-solid w-200px">
                <option value="">Semua Aging Bucket</option>
                @foreach($bucketConfig as $key => $cfg)
                    <option value="{{ $key }}" @selected(request('bucket') === $key)>
                        {{ $cfg['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <x-slot:actions>
            <x-button type="submit" color="primary">
                <i class="ki-outline ki-filter fs-4 me-1"></i> Filter
            </x-button>
            @if(request()->hasAny(['search', 'bucket']))
                <x-button type="link" href="{{ route('web.ar-aging.index') }}" color="light">
                    <i class="ki-outline ki-arrows-circle fs-4 me-1"></i> Reset
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-bar>

    {{-- ── Aging Tables per Bucket ──────────────────────────────── --}}
    @php
        $filterBucket = request('bucket', '');
        $hasAnyData   = collect($buckets)->some(fn($b) => $b->isNotEmpty());
    @endphp

    @if(!$hasAnyData)
        <x-card class="py-20 text-center">
            <div class="mb-5">
                <i class="ki-outline ki-check-circle fs-5x text-success"></i>
            </div>
            <h3 class="text-gray-800 fw-bold mb-2">Tidak Ada Piutang Outstanding</h3>
            <p class="text-gray-500 fs-6 mb-8">Semua invoice sudah lunas atau belum ada invoice yang aktif.</p>
            <div class="d-flex justify-content-center gap-3">
                <x-button type="link" href="{{ route('web.invoices.customer.index') }}" color="light-primary" icon="document">
                    Lihat Semua Invoice
                </x-button>
                <x-button type="link" href="{{ route('web.payments.create.incoming') }}" color="light-success" icon="dollar">
                    Tambah Pembayaran
                </x-button>
            </div>
        </x-card>
    @else
        @foreach($bucketConfig as $key => $cfg)
            @php
                $group = $buckets[$key] ?? collect();
                if ($filterBucket && $filterBucket !== $key) continue;
                if ($group->isEmpty()) continue;
            @endphp

            <x-card class="mb-6 card-flush" :title="$cfg['label']" icon="{{ $cfg['icon'] }}">
                <x-slot:toolbar>
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold fs-4 text-{{ $cfg['color'] == 'warning' ? 'warning' : ($cfg['color'] == 'success' ? 'success' : 'danger') }}">
                            Rp {{ number_format($totals[$key] ?? 0, 0, ',', '.') }}
                        </span>
                        <x-badge color="{{ $cfg['color'] }}" size="sm">
                            {{ $group->count() }} Invoice
                        </x-badge>
                    </div>
                </x-slot:toolbar>

                <div class="table-responsive">
                    <x-data-table>
                        <thead>
                            <tr class="fw-bold text-muted fs-7 text-uppercase bg-light">
                                <th class="ps-4 rounded-start">Invoice</th>
                                <th>RS / Klinik</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Terbayar</th>
                                <th class="text-end">Outstanding</th>
                                <th class="text-center">Jatuh Tempo</th>
                                <th class="text-center">Hari Lewat</th>
                                <th class="text-end pe-4 rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->sortByDesc('days_overdue') as $invoice)
                                @php
                                    $pct = $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0;
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('web.invoices.customer.show', $invoice) }}" class="fw-bold text-gray-900 text-hover-primary fs-6">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                        <div class="mt-1">
                                            <x-badge :color="$invoice->status->getBadgeClass()" size="sm">
                                                {{ $invoice->status->getLabel() }}
                                            </x-badge>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fw-semibold">{{ $invoice->organization?->name ?? '—' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-700 fw-semibold">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-semibold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                                        @if($pct > 0)
                                            <div class="progress h-4px mt-1 w-80px ms-auto">
                                                <div class="progress-bar bg-success" style="width: {{ $pct }}%;"></div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold fs-6 text-{{ $cfg['color'] == 'warning' ? 'warning' : ($cfg['color'] == 'success' ? 'success' : 'danger') }}">
                                            Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-gray-600 fs-7">{{ $invoice->due_date->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($invoice->days_overdue > 0)
                                            <span class="badge badge-light-danger fw-bold">{{ $invoice->days_overdue }} hari</span>
                                        @else
                                            <span class="badge badge-light-success fw-bold">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <x-button type="link" href="{{ route('web.invoices.customer.show', $invoice) }}" color="light" size="sm" icon="eye"></x-button>
                                        <x-button type="link" href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}" color="light-success" size="sm" icon="dollar"></x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-data-table>
                </div>
            </x-card>
        @endforeach
    @endif
</x-layout>
