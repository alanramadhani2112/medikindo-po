{{-- FINANCE DASHBOARD --}}
{{-- Purpose: Cashflow & payment control --}}

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Dashboard Finance</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
    <a href="{{ route('web.invoices.index') }}" class="btn btn-primary">
        <i class="ki-outline ki-bill fs-2"></i>
        Kelola Invoice
    </a>
</div>

{{-- Summary Cards --}}
<div class="row g-5 g-xl-8 mb-7">
    @foreach($cards as $card)
    <div class="col-12 col-md-6 col-xl-{{ count($cards) > 4 ? '3' : (12 / min(count($cards), 4)) }}">
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

{{-- Alerts --}}
@if(count($alerts) > 0)
<div class="row g-5 g-xl-8 mb-7">
    <div class="col-12">
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

{{-- Main Content Row --}}
<div class="row g-5 g-xl-8 mb-7">
    {{-- Outstanding Invoices Table --}}
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Invoice Outstanding</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Tagihan yang belum dibayar (diurutkan berdasarkan jatuh tempo)</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('web.invoices.index', ['tab' => 'supplier']) }}" class="btn btn-sm btn-light-primary">
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
                                <th class="ps-4 min-w-150px rounded-start">Nomor Invoice</th>
                                <th class="min-w-150px">Organisasi</th>
                                <th class="min-w-120px">Jumlah</th>
                                <th class="min-w-100px">Jatuh Tempo</th>
                                <th class="text-end pe-4 min-w-100px rounded-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingInvoices as $invoice)
                            @php
                                $isOverdue = $invoice->due_date < now();
                                $daysOverdue = $isOverdue ? now()->diffInDays($invoice->due_date) : 0;
                            @endphp
                            <tr class="{{ $isOverdue ? 'bg-light-danger' : '' }}">
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-6">{{ $invoice->invoice_number }}</span>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $invoice->supplier->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6">{{ $invoice->purchaseOrder->organization->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-6">Rp {{ number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-700 fw-semibold fs-6">{{ $invoice->due_date->format('d M Y') }}</span>
                                    @if($isOverdue)
                                    <span class="badge badge-danger fs-8 fw-semibold d-block mt-1">{{ $daysOverdue }} hari terlambat</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <span class="badge badge-light-{{ $invoice->status === 'paid' ? 'success' : 'warning' }} fs-7 fw-semibold">
                                        {{ strtoupper($invoice->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-check-circle fs-3x text-success mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold">Tidak ada invoice outstanding</span>
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
                    @can('create_invoice')
                    <a href="{{ route('web.invoices.index') }}" class="btn btn-light-primary justify-content-start">
                        <i class="ki-outline ki-bill fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Generate Invoice</div>
                            <div class="text-muted fs-7">Buat invoice baru</div>
                        </div>
                    </a>
                    @endcan
                    @can('verify_payment')
                    <a href="{{ route('web.payments.index') }}" class="btn btn-light-success justify-content-start">
                        <i class="ki-outline ki-check-circle fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Konfirmasi Pembayaran</div>
                            <div class="text-muted fs-7">Verifikasi pembayaran masuk</div>
                        </div>
                    </a>
                    @endcan
                    <a href="{{ route('web.invoices.index', ['tab' => 'supplier']) }}" class="btn btn-light-danger justify-content-start">
                        <i class="ki-outline ki-wallet fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Bayar Distributor</div>
                            <div class="text-muted fs-7">Catat pembayaran ke supplier</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Payments Table --}}
@if(count($recentPayments) > 0)
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Pembayaran Terbaru</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Riwayat pembayaran yang telah diproses</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('web.payments.index') }}" class="btn btn-sm btn-light-primary">
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
                                <th class="ps-4 min-w-150px rounded-start">Nomor Pembayaran</th>
                                <th class="min-w-120px">Jumlah</th>
                                <th class="min-w-100px">Metode</th>
                                <th class="min-w-120px">Diproses Oleh</th>
                                <th class="text-end pe-4 min-w-100px rounded-end">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-6">{{ $payment->payment_number ?? 'PAY-' . $payment->id }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-6">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6">{{ $payment->payment_method ?? 'Transfer' }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6">{{ $payment->user->name ?? '-' }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6">{{ $payment->payment_date->format('d M Y') }}</span>
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
