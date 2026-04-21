@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">
                Cashflow — {{ $bankAccount->bank_name }}
            </h1>
            <p class="text-gray-600 fs-6 mb-0">
                {{ $bankAccount->account_number }} · {{ $bankAccount->account_holder_name }}
                <span class="badge badge-light-{{ $bankAccount->getAccountTypeBadgeColor() }} ms-2">
                    {{ $bankAccount->getAccountTypeLabel() }}
                </span>
            </p>
        </div>
        <a href="{{ route('web.bank-accounts.index') }}" class="btn btn-light btn-sm">
            <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Kembali
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-5 mb-7">
        <div class="col-md-4">
            <div class="card bg-light-success border border-success border-dashed h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="ki-outline ki-entrance-right fs-2x text-success"></i>
                        <span class="text-success fs-7 fw-bold">Total Masuk (AR)</span>
                    </div>
                    <div class="text-success fs-2x fw-bold">Rp {{ number_format($totalIn, 0, ',', '.') }}</div>
                    <div class="text-muted fs-8 mt-1">Pembayaran dari RS/Klinik</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-danger border border-danger border-dashed h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="ki-outline ki-exit-right fs-2x text-danger"></i>
                        <span class="text-danger fs-7 fw-bold">Total Keluar (AP)</span>
                    </div>
                    <div class="text-danger fs-2x fw-bold">Rp {{ number_format($totalOut, 0, ',', '.') }}</div>
                    <div class="text-muted fs-8 mt-1">Pembayaran ke Supplier</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @php $net = $totalIn - $totalOut; @endphp
            <div class="card {{ $net >= 0 ? 'bg-light-primary border border-primary' : 'bg-light-warning border border-warning' }} border-dashed h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="ki-outline ki-chart-line-up fs-2x {{ $net >= 0 ? 'text-primary' : 'text-warning' }}"></i>
                        <span class="{{ $net >= 0 ? 'text-primary' : 'text-warning' }} fs-7 fw-bold">Net Cashflow</span>
                    </div>
                    <div class="{{ $net >= 0 ? 'text-primary' : 'text-warning' }} fs-2x fw-bold">
                        {{ $net >= 0 ? '+' : '' }}Rp {{ number_format($net, 0, ',', '.') }}
                    </div>
                    <div class="text-muted fs-8 mt-1">Masuk - Keluar</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-5">
        <div class="card-body py-4">
            <form method="GET" class="d-flex align-items-center gap-4 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <label class="fw-bold fs-7 text-gray-700 mb-0">Periode:</label>
                    <select name="period" class="form-select form-select-sm form-select-solid w-auto" onchange="this.form.submit()">
                        <option value="month"   {{ $period === 'month'   ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>Kuartal Ini</option>
                        <option value="year"    {{ $period === 'year'    ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="all"     {{ $period === 'all'     ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="fw-bold fs-7 text-gray-700 mb-0">Tipe:</label>
                    <select name="type" class="form-select form-select-sm form-select-solid w-auto" onchange="this.form.submit()">
                        <option value="all"      {{ $type === 'all'      ? 'selected' : '' }}>Semua</option>
                        <option value="incoming" {{ $type === 'incoming' ? 'selected' : '' }}>Masuk (AR)</option>
                        <option value="outgoing" {{ $type === 'outgoing' ? 'selected' : '' }}>Keluar (AP)</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold text-gray-800">Riwayat Transaksi</h3>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>Tanggal</th>
                            <th>No. Pembayaran</th>
                            <th>Tipe</th>
                            <th>Metode</th>
                            <th>Pihak</th>
                            <th>Referensi</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <span class="text-gray-700 fs-7">
                                        {{ $payment->payment_date->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-gray-800 fs-7 font-monospace">
                                        {{ $payment->payment_number }}
                                    </span>
                                </td>
                                <td>
                                    @if($payment->type === 'incoming')
                                        <span class="badge badge-light-success fw-bold">
                                            <i class="ki-outline ki-entrance-right fs-9 me-1"></i>Masuk
                                        </span>
                                    @else
                                        <span class="badge badge-light-danger fw-bold">
                                            <i class="ki-outline ki-exit-right fs-9 me-1"></i>Keluar
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-gray-700 fs-7">{{ $payment->payment_method ?? '—' }}</span>
                                    @if($payment->bank_name_manual)
                                        <div class="text-muted fs-9">{{ $payment->bank_name_manual }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->type === 'incoming')
                                        @php $org = $payment->allocations->first()?->customerInvoice?->organization; @endphp
                                        <span class="text-gray-700 fs-7">{{ $org?->name ?? '—' }}</span>
                                    @else
                                        <span class="text-gray-700 fs-7">{{ $payment->supplier?->name ?? '—' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted fs-8 font-monospace">{{ $payment->reference ?? '—' }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold fs-6 {{ $payment->type === 'incoming' ? 'text-success' : 'text-danger' }}">
                                        {{ $payment->type === 'incoming' ? '+' : '-' }}Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-muted fs-7">
                                    <i class="ki-outline ki-information-5 fs-3 d-block mb-2"></i>
                                    Belum ada transaksi untuk filter yang dipilih
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="d-flex justify-content-end mt-5">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
