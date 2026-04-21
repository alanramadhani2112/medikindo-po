@props([
    'invoice',
    'showAction' => true,
    'compact'    => false,
])

@php
    $outstanding = $invoice->outstanding_amount;
    $pct         = $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0;
    $isOverdue   = $invoice->isOverdueByDate();
    $agingBucket = $invoice->aging_bucket;
    $agingColors = [
        'current' => 'success',
        '1-30'    => 'warning',
        '31-60'   => 'danger',
        '61-90'   => 'danger',
        '90+'     => 'dark',
    ];
    $agingColor = $agingColors[$agingBucket] ?? 'secondary';
@endphp

@if($compact)
    {{-- Compact version for list views --}}
    <div class="d-flex flex-column gap-1">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-gray-500 fs-9">Outstanding</span>
            <span class="fw-bold fs-8 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                Rp {{ number_format($outstanding, 0, ',', '.') }}
            </span>
        </div>
        <div class="progress h-4px">
            <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
        </div>
        @if($isOverdue)
            <span class="badge badge-danger fs-9 mt-1">+{{ $invoice->days_overdue }} hari lewat</span>
        @endif
    </div>
@else
    {{-- Full version for detail views --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-wallet fs-2 me-2"></i>
                Ringkasan Pembayaran
            </h3>
            @if($showAction && $invoice->status->canAcceptPayment())
                <div class="card-toolbar">
                    <a href="{{ route('web.payments.create.incoming', ['invoice_id' => $invoice->id]) }}"
                       class="btn btn-sm btn-success">
                        <i class="ki-outline ki-dollar fs-4 me-1"></i>Tambah Pembayaran
                    </a>
                </div>
            @endif
        </div>
        <div class="card-body">
            {{-- Progress --}}
            <div class="mb-5">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-gray-600 fs-7">Progress Pelunasan</span>
                    <span class="fw-bold fs-7 {{ $pct >= 100 ? 'text-success' : 'text-primary' }}">
                        {{ number_format($pct, 0) }}%
                    </span>
                </div>
                <div class="progress h-10px rounded">
                    <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}"
                         style="width: {{ min($pct, 100) }}%"></div>
                </div>
            </div>

            {{-- Amounts --}}
            <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                    <span class="text-gray-600 fs-7">Total Tagihan</span>
                    <span class="text-gray-900 fw-bold">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom border-gray-200">
                    <span class="text-gray-600 fs-7">Sudah Dibayar</span>
                    <span class="text-success fw-bold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 px-3 rounded {{ $outstanding > 0 ? 'bg-light-danger' : 'bg-light-success' }}">
                    <span class="fw-bold fs-6 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">Sisa Tagihan</span>
                    <span class="fw-bold fs-5 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($outstanding, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Due Date & Aging --}}
            <div class="separator my-4"></div>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-gray-500 fs-8 fw-bold d-block">JATUH TEMPO</span>
                    <span class="fw-bold {{ $isOverdue ? 'text-danger' : 'text-gray-800' }}">
                        {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                    </span>
                </div>
                <div class="text-end">
                    <span class="text-gray-500 fs-8 fw-bold d-block">AGING</span>
                    <span class="badge badge-light-{{ $agingColor }} fw-bold">
                        @if($agingBucket === 'current')
                            On Time
                        @else
                            {{ $agingBucket }} hari
                        @endif
                    </span>
                    @if($isOverdue)
                        <div class="text-danger fs-9 mt-1">{{ $invoice->days_overdue }} hari terlambat</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
