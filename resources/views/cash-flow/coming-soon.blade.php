@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">

        {{-- Icon --}}
        <div class="mb-8 position-relative">
            <div class="d-flex align-items-center justify-content-center rounded-circle"
                 style="width: 120px; height: 120px; background: linear-gradient(135deg, #e8f0f8 0%, #d1e2f0 100%);">
                <i class="ki-outline ki-chart-line-up fs-5x" style="color: #1b4b7f;"></i>
            </div>
            {{-- Badge --}}
            <span class="badge badge-primary position-absolute"
                  style="top: -8px; right: -8px; font-size: 0.7rem; padding: 6px 10px;">
                SOON
            </span>
        </div>

        {{-- Title --}}
        <h1 class="fs-2hx fw-bold text-gray-900 mb-3 text-center">
            Cash Flow Management
        </h1>
        <p class="text-gray-500 fs-5 mb-8 text-center" style="max-width: 480px;">
            Fitur laporan proyeksi arus kas dan pergerakan keuangan masuk/keluar sedang dalam pengembangan.
            Manajemen saldo kas saat ini dapat dipantau melalui Buku Kas & Pembayaran.
        </p>

        {{-- Feature preview cards --}}
        <div class="row g-5 mb-10" style="max-width: 720px; width: 100%;">
            @php
                $features = [
                    ['icon' => 'chart-line-star', 'title' => 'Cash Projection',    'desc' => 'Prediksi saldo kas di masa depan berdasarkan jadwal invoice'],
                    ['icon' => 'bank',             'title' => 'Bank Recon',         'desc' => 'Rekonsiliasi otomatis antara mutasi bank dan catatan sistem'],
                    ['icon' => 'graph-3',          'title' => 'Real-time Reports',  'desc' => 'Laporan arus kas masuk dan keluar secara instan'],
                    ['icon' => 'search-list',      'title' => 'Expense Analysis',   'desc' => 'Analisa mendalam pengeluaran operasional per kategori'],
                    ['icon' => 'calendar-8',       'title' => 'Payment Calendar',   'desc' => 'Visualisasi jadwal pembayaran dan penerimaan kas'],
                    ['icon' => 'safe',             'title' => 'Multi-Account',      'desc' => 'Konsolidasi saldo dari berbagai rekening bank Medikindo'],
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
                <i class="ki-outline ki-wallet fs-4 me-2"></i>
                Lihat Buku Kas
            </a>
            <a href="{{ route('web.dashboard') }}" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-4 me-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

    </div>
</div>
@endsection
