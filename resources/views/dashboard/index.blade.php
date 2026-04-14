{{-- 
Conversion Metadata:
- Original: Tailwind CSS
- Converted: Bootstrap 5 + Metronic 8
- Date: 2024
- Category: Dashboard
- Validated: Pending
--}}
<x-layout title="Dashboard">

    {{-- --- 1. HEADER --- --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">Dasbor ERP Medikindo</h1>
            <p class="text-gray-600 fs-6 mb-0">Selamat datang kembali, {{ auth()->user()->name }}. Ini adalah ringkasan sistem Anda hari ini.</p>
        </div>
        @can('create_po')
        <a href="{{ route('web.po.create') }}" class="btn btn-primary">
            <i class="ki-duotone ki-plus fs-2"></i>
            Buat PO Baru
        </a>
        @endcan
    </div>

    {{-- --- 2. KPI SECTION --- --}}
    <div class="row g-5 g-xl-8 mb-7">
        @if($showApprover ?? false)
            <div class="col-12 col-md-6 col-lg-3">
                <x-stat-card 
                    title="Antrean Persetujuan" 
                    :value="$pending_approvals" 
                    icon="document" 
                    color="primary" 
                />
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <x-stat-card 
                    title="Disetujui Hari Ini" 
                    :value="$today_decisions['approved']" 
                    icon="check-circle" 
                    color="success" 
                />
            </div>
        @endif

        @if($showFinance ?? false)
            <div class="col-12 col-md-6 col-lg-3">
                <x-stat-card 
                    title="Outstanding AR" 
                    value="Rp {{ number_format($ar_summary['outstanding'] ?? 0, 0, ',', '.') }}" 
                    icon="dollar" 
                    color="primary" 
                />
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <x-stat-card 
                    title="Risiko Overdue" 
                    :value="$risk['overdue_count'] ?? 0" 
                    icon="information" 
                    color="danger" 
                />
            </div>
        @endif

        @if($showHealthcare ?? false)
            <div class="col-12 col-md-6 col-lg-3">
                <x-stat-card 
                    title="Active PO" 
                    :value="$po_status['total'] ?? 0" 
                    icon="basket" 
                    color="primary" 
                />
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <x-stat-card 
                    title="Pending Receipt" 
                    :value="$goods_receipt['pending'] ?? 0" 
                    icon="package" 
                    color="warning" 
                />
            </div>
        @endif

        @if($showAdmin ?? false)
            <div class="col-12 col-md-6 col-lg-3">
                <x-stat-card 
                    title="User Aktif" 
                    :value="$users['total'] ?? 0" 
                    icon="user" 
                    color="info" 
                />
            </div>
        @endif
    </div>

    {{-- --- 3. MAIN GRID --- --}}
    <div class="row g-5 g-xl-8">
        {{-- LEFT: Main Content (Antrean / Transaksi) --}}
        <div class="col-lg-8">
            @if(($showApprover ?? false) && count($approval_queue ?? []) > 0)
                <div class="card card-flush mb-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title fw-bold fs-3">Antrean Persetujuan Terbaru</h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th class="min-w-150px">Nomor PO</th>
                                        <th class="min-w-150px">Supplier</th>
                                        <th class="min-w-120px">Total</th>
                                        <th class="min-w-100px text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($approval_queue as $po)
                                        <tr>
                                            <td>
                                                <span class="text-gray-900 fw-bold d-block fs-6">{{ $po->po_number }}</span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-semibold d-block fs-6">{{ $po->supplier->name }}</span>
                                            </td>
                                            <td>
                                                <span class="text-primary fw-bold d-block fs-6">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('web.po.show', $po) }}" class="btn btn-sm btn-light-primary">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card card-flush mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold fs-3">Aktivitas Sistem Terkini</h3>
                </div>
                <div class="card-body pt-0">
                    @forelse($activity_logs ?? [] as $log)
                        <div class="d-flex align-items-center justify-content-between pb-4 mb-4 border-bottom border-gray-200">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary" style="width: 8px; height: 8px;"></div>
                                <span class="text-gray-800 fw-semibold fs-6">{{ $log->description }}</span>
                            </div>
                            <span class="text-gray-600 fw-semibold fs-7">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ki-duotone ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                <span class="text-gray-600 fs-5">Belum ada aktivitas tercatat.</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT: Side Content (Revenue / Risk) --}}
        <div class="col-lg-4">
            @if($showFinance ?? false)
                <div class="card card-flush bg-primary mb-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title fw-bold fs-3 text-white">Ringkasan Pendapatan</h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-7">
                            <span class="text-white fw-semibold fs-7 d-block mb-2">Total AR Hari Ini</span>
                            <div class="text-white fw-bold fs-2x">Rp {{ number_format($ar_summary['total'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="row g-4">
                            <div class="col-6">
                                <span class="text-white fw-semibold fs-7 d-block mb-2">Terbayar</span>
                                <div class="text-success fw-bold fs-3">Rp {{ number_format($ar_summary['paid'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-6">
                                <span class="text-white fw-semibold fs-7 d-block mb-2">Outstanding</span>
                                <div class="text-danger fw-bold fs-3">Rp {{ number_format($ar_summary['outstanding'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card card-flush mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold fs-3">Navigasi Cepat</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('web.po.index') }}" class="btn btn-light-primary justify-content-start">
                            <i class="ki-duotone ki-document fs-3 me-2"></i>
                            Manajemen PO
                        </a>
                        @can('view_invoice')
                        <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light-primary justify-content-start">
                            <i class="ki-duotone ki-dollar fs-3 me-2"></i>
                            Faktur & Keuangan
                        </a>
                        @endcan
                        @can('manage_supplier')
                        <a href="{{ route('web.suppliers.index') }}" class="btn btn-light-primary justify-content-start">
                            <i class="ki-duotone ki-basket fs-3 me-2"></i>
                            Master Supplier
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layout>

