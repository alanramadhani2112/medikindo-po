<x-layout title="Invoices Management">

    <x-page-header 
        title="Pusat Keuangan & Invoice" 
        description="Kelola piutang (Customer) dan hutang (Supplier) secara real-time.">
        <x-slot name="actions">
            <button class="btn btn-light-secondary btn-sm">
                <i class="ki-outline ki-file-down fs-3"></i>
                Export Laporan
            </button>
        </x-slot>
    </x-page-header>

    {{-- KPI CARDS --}}
    <div class="row g-5 mb-7">
        <div class="col-md-3">
            <x-stat-card 
                title="Total Piutang (AR)" 
                value="Rp 128.450.000" 
                variant="primary"
                badge="+12%"
                badgeText="vs bulan lalu" />
        </div>
        <div class="col-md-3">
            <x-stat-card 
                title="Total Hutang (AP)" 
                value="Rp 92.120.000" 
                variant="secondary"
                badge="Normal"
                badgeText="Termin 14 hari" />
        </div>
        <div class="col-md-3">
            <x-stat-card 
                title="Invoice Menunggu" 
                value="24 Dokumen" 
                variant="warning"
                badgeText="Memerlukan konfirmasi" />
        </div>
        <div class="col-md-3">
            <x-stat-card 
                title="Jatuh Tempo (7 Hari)" 
                value="Rp 12.000.000" 
                variant="danger"
                badgeText="8 Invoice tertunda" />
        </div>
    </div>

    <div class="row g-5">
        {{-- LEFT: Table --}}
        <div class="col-lg-8">
            <x-filter-bar action="{{ route('web.invoices.index') }}" method="GET">
                <div class="col-md-4">
                    <div class="position-relative">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nomor invoice...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-solid">
                        <option value="">Semua Tipe</option>
                        <option value="ar">Customer (AR)</option>
                        <option value="ap">Supplier (AP)</option>
                    </select>
                </div>
            </x-filter-bar>

            <x-card title="Daftar Invoice">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 rounded-start">Nomor Invoice</th>
                                <th>Entitas</th>
                                <th class="text-end">Total</th>
                                <th>Status</th>
                                <th class="text-end pe-4 rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices ?? [] as $invoice)
                                @php
                                    $isSupplier = isset($invoice->supplier_id);
                                    $showRoute = $isSupplier ? 'web.invoices.supplier.show' : 'web.invoices.customer.show';
                                    $entityName = $isSupplier ? ($invoice->supplier?->name ?? '—') : ($invoice->organization?->name ?? '—');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route($showRoute, $invoice) }}" 
                                           class="fw-bold text-gray-900 text-hover-primary fs-6">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                        <div class="badge badge-light-{{ $isSupplier ? 'danger' : 'success' }} fs-8 mt-1">
                                            {{ $isSupplier ? 'Supplier AP' : 'Customer AR' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-gray-700">{{ $entityName }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-primary fs-6">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($invoice->status) {
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'overdue' => 'danger',
                                                'confirmed' => 'primary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <x-badge variant="{{ $statusColor }}">{{ strtoupper($invoice->status) }}</x-badge>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ki-outline ki-dots-vertical fs-3"></i>
                                                Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route($showRoute, $invoice) }}" class="dropdown-item">
                                                    <i class="ki-outline ki-eye fs-4 me-2 text-primary"></i>
                                                    Lihat Detail
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10">
                                        <x-empty-state
                                            icon="document"
                                            title="Belum ada data invoice"
                                            message="Data invoice akan muncul setelah transaksi purchase order diproses." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($invoices) && $invoices->hasPages())
                    <div class="d-flex justify-content-center mt-7">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </x-card>
        </div>

        {{-- RIGHT: Summary --}}
        <div class="col-lg-4">
            <x-card title="Aging Piutang (AR)" class="mb-5">
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-gray-700 fw-semibold">Sesuai Termin</span>
                        <span class="fw-bold text-gray-900">Rp 84.000.000</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-gray-700 fw-semibold">Tertunda < 30 Hari</span>
                        <span class="fw-bold text-gray-900">Rp 32.000.000</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-gray-700 fw-semibold">Tertunda > 30 Hari</span>
                        <span class="fw-bold text-danger">Rp 12.000.000</span>
                    </div>
                    <div class="pt-4 border-top">
                        <button class="btn btn-light w-100">Lihat Laporan Aging</button>
                    </div>
                </div>
            </x-card>

            <x-card title="Aktivitas Terakhir">
                <div class="d-flex flex-column gap-5">
                    <div class="d-flex gap-3">
                        <span class="bullet bullet-dot bg-success h-10px w-10px mt-2"></span>
                        <div class="d-flex flex-column">
                            <span class="fs-7 fw-bold text-gray-800">Pembayaran Diterima</span>
                            <span class="fs-8 text-muted">INV/2024/0041 • Rp 5.200.000</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <span class="bullet bullet-dot bg-primary h-10px w-10px mt-2"></span>
                        <div class="d-flex flex-column">
                            <span class="fs-7 fw-bold text-gray-800">Invoice Diterbitkan</span>
                            <span class="fs-8 text-muted">INV/2024/0042 • Klinik Utama</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

</x-layout>
