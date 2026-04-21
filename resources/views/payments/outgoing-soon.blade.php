@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">

        {{-- Icon --}}
        <div class="mb-8 position-relative">
            <div class="d-flex align-items-center justify-content-center rounded-circle"
                 style="width: 120px; height: 120px; background: linear-gradient(135deg, #e8f0f8 0%, #d1e2f0 100%);">
                <i class="ki-outline ki-exit-right fs-5x" style="color: #1b4b7f;"></i>
            </div>
            {{-- Badge --}}
            <span class="badge badge-primary position-absolute"
                  style="top: -8px; right: -8px; font-size: 0.7rem; padding: 6px 10px;">
                SOON
            </span>
        </div>

        {{-- Title --}}
        <h1 class="fs-2hx fw-bold text-gray-900 mb-3 text-center">
            Payment Out (Pembayaran Supplier)
        </h1>
        <p class="text-gray-500 fs-5 mb-8 text-center" style="max-width: 480px;">
            Fitur alur pencairan dana dan pembayaran terpusat ke supplier (Accounts Payable) sedang dalam tahap finalisasi.
            Proses pelunasan hutang saat ini dilakukan secara manual melalui koordinasi tim Finance.
        </p>

        {{-- Feature preview cards --}}
        <div class="row g-5 mb-10" style="max-width: 720px; width: 100%;">
            @php
                $features = [
                    ['icon' => 'timer',            'title' => 'Due Date Tracker',  'desc' => 'Notifikasi otomatis untuk tagihan supplier yang mendekati jatuh tempo'],
                    ['icon' => 'bank',             'title' => 'Batch Payment',     'desc' => 'Proses pembayaran banyak invoice sekaligus dalam satu transaksi'],
                    ['icon' => 'shield-tick',      'title' => 'Approval Workflow', 'desc' => 'Alur persetujuan bertingkat (Manager/Direksi) untuk pengeluaran besar'],
                    ['icon' => 'bill',             'title' => 'Giro/Cek Manager',  'desc' => 'Manajemen dan pencetakan giro atau cek untuk pembayaran supplier'],
                    ['icon' => 'discount',         'title' => 'Early Payment',     'desc' => 'Optimasi diskon pembayaran lebih awal dari supplier'],
                    ['icon' => 'file-sheet',       'title' => 'AP Aging Report',   'desc' => 'Laporan penuaan hutang untuk menjaga cash flow perusahaan'],
                ];
            @endphp
            @foreach($features as $f)
                <div class="col-md-4">
                    <div class="card h-100 border border-dashed border-gray-300">
                        <div class="card-body py-5 px-5">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="d-flex align-items-center justify-content-center rounded"
                                     style="width: 36px; height: 36px; background: #e8f0f8;">
                                    <i class="ki-outline ki-{{ $f['icon'] }} fs-4" style="color: #1b4b7f;"></i>
                                </div>
                                <span class="fw-bold text-gray-800 fs-6">{{ $f['title'] }}</span>
                            </div>
                            <p class="text-gray-500 fs-7 mb-0">{{ $f['desc'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="d-flex gap-3">
            <a href="{{ route('web.payments.index') }}" class="btn btn-primary">
                <i class="ki-outline ki-bill fs-4 me-2"></i>
                Lihat Invoice Supplier
            </a>
            <a href="{{ route('web.dashboard') }}" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-4 me-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

    </div>
</div>
@endsection
