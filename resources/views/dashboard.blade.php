@extends('layouts.app', ['pageTitle' => 'Dashboard'])

@section('content')

    {{-- KPI Stats Row --}}
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-md-6 col-xl-3">
            <div class="card bg-primary hoverable h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex flex-column">
                            <span class="text-white opacity-75 fw-semibold fs-7">Total Purchase Orders</span>
                            <span class="text-white fw-bold fs-2x mt-1">{{ number_format($stats['all'] ?? 0) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded" style="width:56px;height:56px;">
                            <i class="ki-duotone ki-purchase fs-2x text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-white text-primary fw-bold fs-8">Semua Status</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card bg-warning hoverable h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex flex-column">
                            <span class="text-white opacity-75 fw-semibold fs-7">Menunggu Persetujuan</span>
                            <span class="text-white fw-bold fs-2x mt-1">{{ number_format($stats['pending'] ?? 0) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded" style="width:56px;height:56px;">
                            <i class="ki-duotone ki-timer fs-2x text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-white text-warning fw-bold fs-8">Perlu Tindakan</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card bg-success hoverable h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex flex-column">
                            <span class="text-white opacity-75 fw-semibold fs-7">Penerimaan Barang (GR)</span>
                            <span class="text-white fw-bold fs-2x mt-1">{{ number_format($stats['received'] ?? 0) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded" style="width:56px;height:56px;">
                            <i class="ki-duotone ki-package fs-2x text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-white text-success fw-bold fs-8">Barang Diterima</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card bg-info hoverable h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex flex-column">
                            <span class="text-white opacity-75 fw-semibold fs-7">Total Klinik Aktif</span>
                            <span class="text-white fw-bold fs-2x mt-1">{{ number_format($stats['organizations'] ?? 0) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded" style="width:56px;height:56px;">
                            <i class="ki-duotone ki-bank fs-2x text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-white text-info fw-bold fs-8">Faskes Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::KPI Row-->

    <!--begin::Content Row-->
    <div class="row g-5 g-xl-10">
        <!--begin::Recent PO Table-->
        <div class="col-xl-8">
            <div class="card card-flush h-100">
                <div class="card-header pt-5 border-0">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Aktivitas Purchase Order Terbaru</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Pesanan pengadaan yang baru dibuat</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('web.po.index') }}" class="btn btn-sm btn-light btn-active-light-primary">
                            Lihat Semua <i class="ki-duotone ki-right fs-5 ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-150px">Nomor PO</th>
                                    <th class="min-w-140px">Organisasi</th>
                                    <th class="min-w-120px">Total Nilai</th>
                                    <th class="min-w-100px">Status</th>
                                    <th class="min-w-80px text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders ?? [] as $order)
                                @php
                                    $badge = match($order->status) {
                                        'approved','shipped','delivered','completed' => 'success',
                                        'submitted' => 'warning',
                                        'rejected'  => 'danger',
                                        default     => 'primary',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('web.po.show', $order) }}" class="text-dark fw-bold text-hover-primary fs-6">
                                            {{ $order->po_number }}
                                        </a>
                                        <span class="text-muted fw-semibold d-block fs-7">{{ $order->created_at->format('d M Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold d-block fs-6">{{ $order->organization->name }}</span>
                                        <span class="text-muted fw-semibold d-block fs-7">{{ $order->supplier->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold d-block fs-6">
                                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $badge }}">{{ strtoupper($order->status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('web.po.show', $order) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                            <i class="ki-duotone ki-eye fs-4"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-muted">
                                        Belum ada purchase order terbaru.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Recent PO Table-->

        <!--begin::System Info-->
        <div class="col-xl-4">
            <div class="card card-flush h-100">
                <div class="card-header pt-5 border-0">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Informasi Sistem</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Status & akses cepat</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <!--begin::Status-->
                    <div class="d-flex align-items-center bg-light-success rounded p-5 mb-5">
                        <span class="svg-icon svg-icon-success me-5">
                            <i class="ki-duotone ki-shield-tick fs-2x text-success"></i>
                        </span>
                        <div class="flex-grow-1">
                            <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-6">Status Server</a>
                            <span class="text-muted fw-semibold d-block fs-7">Seluruh layanan berjalan optimal</span>
                        </div>
                    </div>
                    <!--end::Status-->

                    <!--begin::Quick Links-->
                    <div class="fw-bold text-uppercase text-muted fs-7 mb-4">Akses Cepat</div>
                    @can('create_purchase_orders')
                    <a href="{{ route('web.po.create') }}" class="d-flex align-items-center py-3 border-bottom">
                        <span class="bullet bullet-vertical h-40px bg-primary me-4"></span>
                        <div class="flex-grow-1">
                            <span class="text-gray-800 fw-bold fs-6 d-block">Buat Purchase Order</span>
                            <span class="text-muted fw-semibold fs-7">Ajukan PO baru</span>
                        </div>
                        <i class="ki-duotone ki-right fs-4 text-muted"></i>
                    </a>
                    @endcan
                    @can('view_invoices')
                    <a href="{{ route('web.invoices.customer.index') }}" class="d-flex align-items-center py-3 border-bottom">
                        <span class="bullet bullet-vertical h-40px bg-warning me-4"></span>
                        <div class="flex-grow-1">
                            <span class="text-gray-800 fw-bold fs-6 d-block">Kelola Invoices</span>
                            <span class="text-muted fw-semibold fs-7">Pantau tagihan masuk/keluar</span>
                        </div>
                        <i class="ki-duotone ki-right fs-4 text-muted"></i>
                    </a>
                    @endcan
                    @can('view_goods_receipt')
                    <a href="{{ route('web.goods-receipts.index') }}" class="d-flex align-items-center py-3">
                        <span class="bullet bullet-vertical h-40px bg-success me-4"></span>
                        <div class="flex-grow-1">
                            <span class="text-gray-800 fw-bold fs-6 d-block">Goods Receipt</span>
                            <span class="text-muted fw-semibold fs-7">Konfirmasi penerimaan barang</span>
                        </div>
                        <i class="ki-duotone ki-right fs-4 text-muted"></i>
                    </a>
                    @endcan
                    <!--end::Quick Links-->
                </div>
            </div>
        </div>
        <!--end::System Info-->
    </div>
    <!--end::Content Row-->

@endsection
