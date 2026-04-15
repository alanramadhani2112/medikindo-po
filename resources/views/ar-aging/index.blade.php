@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">
                <i class="ki-outline ki-courier-up fs-2 text-primary me-2"></i>
                AR Aging Dashboard
            </h1>
            <p class="text-gray-600 fs-6 mb-0">Klasifikasi piutang berdasarkan umur tagihan</p>
        </div>
        <div>
            <span class="text-muted fs-7">Per tanggal: <strong>{{ now()->format('d M Y') }}</strong></span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-7">
        {{-- Current (0-30 days) --}}
        <div class="col-md-4 mb-5 mb-md-0">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #50cd89 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <span class="text-gray-500 fs-7 fw-bold text-uppercase">Current</span>
                            <div class="text-gray-400 fs-8">0 – 30 Hari</div>
                        </div>
                        <div class="symbol symbol-45px">
                            <div class="symbol-label bg-light-success">
                                <i class="ki-outline ki-check-circle fs-2 text-success"></i>
                            </div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-success mb-1">
                        Rp {{ number_format($totals['current'], 0, ',', '.') }}
                    </div>
                    <div class="text-gray-500 fs-7">
                        {{ $buckets['current']->count() }} invoice
                    </div>
                </div>
            </div>
        </div>

        {{-- Warning (31-60 days) --}}
        <div class="col-md-4 mb-5 mb-md-0">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc700 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <span class="text-gray-500 fs-7 fw-bold text-uppercase">Warning</span>
                            <div class="text-gray-400 fs-8">31 – 60 Hari</div>
                        </div>
                        <div class="symbol symbol-45px">
                            <div class="symbol-label bg-light-warning">
                                <i class="ki-outline ki-information-5 fs-2 text-warning"></i>
                            </div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-warning mb-1">
                        Rp {{ number_format($totals['warning'], 0, ',', '.') }}
                    </div>
                    <div class="text-gray-500 fs-7">
                        {{ $buckets['warning']->count() }} invoice
                    </div>
                </div>
            </div>
        </div>

        {{-- Overdue (>60 days) --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #f1416c !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <span class="text-gray-500 fs-7 fw-bold text-uppercase">Overdue</span>
                            <div class="text-gray-400 fs-8">&gt; 60 Hari</div>
                        </div>
                        <div class="symbol symbol-45px">
                            <div class="symbol-label bg-light-danger">
                                <i class="ki-outline ki-cross-circle fs-2 text-danger"></i>
                            </div>
                        </div>
                    </div>
                    <div class="fs-2x fw-bold text-danger mb-1">
                        Rp {{ number_format($totals['overdue'], 0, ',', '.') }}
                    </div>
                    <div class="text-gray-500 fs-7">
                        {{ $buckets['overdue']->count() }} invoice
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Outstanding --}}
    <div class="card bg-dark mb-7">
        <div class="card-body py-4">
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-gray-400 fs-6 fw-bold">TOTAL OUTSTANDING</span>
                <span class="text-white fs-2x fw-bold">
                    Rp {{ number_format(array_sum($totals), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Current Bucket Table --}}
    @if($buckets['current']->isNotEmpty())
    <div class="card mb-7">
        <div class="card-header" style="border-left: 4px solid #50cd89;">
            <h3 class="card-title text-success fw-bold">
                <i class="ki-outline ki-check-circle fs-2 me-2 text-success"></i>
                Current (0–30 Hari)
                <span class="badge badge-light-success ms-2">{{ $buckets['current']->count() }}</span>
            </h3>
        </div>
        <div class="card-body p-0">
            @include('ar-aging._bucket_table', ['invoices' => $buckets['current'], 'badgeClass' => 'badge-light-success'])
        </div>
    </div>
    @endif

    {{-- Warning Bucket Table --}}
    @if($buckets['warning']->isNotEmpty())
    <div class="card mb-7">
        <div class="card-header" style="border-left: 4px solid #ffc700;">
            <h3 class="card-title text-warning fw-bold">
                <i class="ki-outline ki-information-5 fs-2 me-2 text-warning"></i>
                Warning (31–60 Hari)
                <span class="badge badge-light-warning ms-2">{{ $buckets['warning']->count() }}</span>
            </h3>
        </div>
        <div class="card-body p-0">
            @include('ar-aging._bucket_table', ['invoices' => $buckets['warning'], 'badgeClass' => 'badge-light-warning'])
        </div>
    </div>
    @endif

    {{-- Overdue Bucket Table --}}
    @if($buckets['overdue']->isNotEmpty())
    <div class="card mb-7">
        <div class="card-header" style="border-left: 4px solid #f1416c;">
            <h3 class="card-title text-danger fw-bold">
                <i class="ki-outline ki-cross-circle fs-2 me-2 text-danger"></i>
                Overdue (&gt;60 Hari)
                <span class="badge badge-light-danger ms-2">{{ $buckets['overdue']->count() }}</span>
            </h3>
        </div>
        <div class="card-body p-0">
            @include('ar-aging._bucket_table', ['invoices' => $buckets['overdue'], 'badgeClass' => 'badge-light-danger'])
        </div>
    </div>
    @endif

    @if($buckets['current']->isEmpty() && $buckets['warning']->isEmpty() && $buckets['overdue']->isEmpty())
        <div class="card">
            <div class="card-body text-center py-15">
                <i class="ki-outline ki-check-circle fs-3x text-success mb-4"></i>
                <div class="fs-4 fw-bold text-gray-700 mb-2">Tidak Ada Piutang Outstanding</div>
                <div class="text-gray-500 fs-6">Semua invoice sudah lunas atau dibatalkan.</div>
            </div>
        </div>
    @endif
</div>
@endsection
