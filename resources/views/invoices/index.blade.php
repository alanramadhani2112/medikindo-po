@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">Manajemen Invoice</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola invoice supplier (AP) dan customer (AR)</p>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-7 fs-6">
        <li class="nav-item">
            <a class="nav-link {{ request('tab') === 'supplier' || !request('tab') ? 'active' : '' }}" 
               href="{{ route('web.invoices.index', ['tab' => 'supplier']) }}">
                <i class="ki-outline ki-arrow-down fs-2 text-danger me-2"></i>
                Hutang Pemasok (AP)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('tab') === 'customer' ? 'active' : '' }}" 
               href="{{ route('web.invoices.index', ['tab' => 'customer']) }}">
                <i class="ki-outline ki-arrow-up fs-2 text-success me-2"></i>
                Tagihan ke RS/Klinik (AR)
            </a>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content">
        {{-- Supplier Invoice Tab --}}
        @if(request('tab') === 'supplier' || !request('tab'))
        <div class="tab-pane fade show active">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-document fs-2 me-2"></i>
                        Daftar Invoice Pemasok (AP)
                    </h3>
                    @can('create_invoices')
                    <div class="card-toolbar">
                        <a href="{{ route('web.invoices.supplier.create') }}" class="btn btn-sm btn-primary">
                            <i class="ki-outline ki-plus fs-4"></i>
                            Buat Invoice Pemasok
                        </a>
                    </div>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">Nomor Invoice</th>
                                    <th>Supplier</th>
                                    <th>PO Number</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4 rounded-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplierInvoices as $invoice)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('web.invoices.supplier.show', $invoice) }}" 
                                               class="fw-bold text-gray-900 text-hover-primary">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                            <div class="text-muted fs-7 mt-1">{{ $invoice->created_at->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-gray-700">{{ $invoice->supplier?->name ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-600">{{ $invoice->purchaseOrder?->po_number ?? '—' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-gray-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusColor = match($invoice->status) {
                                                    'paid' => 'success',
                                                    'overdue' => 'danger',
                                                    default => 'warning'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $statusColor }}">{{ strtoupper($invoice->status) }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('web.invoices.supplier.show', $invoice) }}" 
                                               class="btn btn-sm btn-light btn-active-light-primary">
                                                <i class="ki-outline ki-eye fs-4"></i>
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-document fs-3x text-gray-400 mb-3"></i>
                                                <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Invoice Pemasok</span>
                                                <span class="text-gray-500 fs-6">Invoice akan muncul setelah dibuat dari Goods Receipt.</span>
                                                @can('create_invoices')
                                                <a href="{{ route('web.invoices.supplier.create') }}" class="btn btn-sm btn-primary mt-4">
                                                    <i class="ki-outline ki-plus fs-4"></i>
                                                    Buat Invoice Pertama
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($supplierInvoices->hasPages())
                        <div class="d-flex justify-content-center mt-7">
                            {{ $supplierInvoices->appends(['tab' => 'supplier'])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Customer Invoice Tab --}}
        @if(request('tab') === 'customer')
        <div class="tab-pane fade show active">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-document fs-2 me-2"></i>
                        Daftar Tagihan ke RS/Klinik (AR)
                    </h3>
                    @can('create_invoices')
                    <div class="card-toolbar">
                        <a href="{{ route('web.invoices.customer.create') }}" class="btn btn-sm btn-success">
                            <i class="ki-outline ki-plus fs-4"></i>
                            Buat Tagihan ke RS/Klinik
                        </a>
                    </div>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">Nomor Invoice</th>
                                    <th>RS/Klinik</th>
                                    <th>PO Number</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4 rounded-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customerInvoices as $invoice)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('web.invoices.customer.show', $invoice) }}" 
                                               class="fw-bold text-gray-900 text-hover-primary">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                            <div class="text-muted fs-7 mt-1">{{ $invoice->created_at->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-gray-700">{{ $invoice->organization?->name ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-600">{{ $invoice->purchaseOrder?->po_number ?? '—' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-gray-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusColor = match($invoice->status) {
                                                    'paid' => 'success',
                                                    'overdue' => 'danger',
                                                    default => 'warning'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $statusColor }}">{{ strtoupper($invoice->status) }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('web.invoices.customer.show', $invoice) }}" 
                                               class="btn btn-sm btn-light btn-active-light-primary">
                                                <i class="ki-outline ki-eye fs-4"></i>
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-document fs-3x text-gray-400 mb-3"></i>
                                                <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Tagihan ke RS/Klinik</span>
                                                <span class="text-gray-500 fs-6">Tagihan akan muncul setelah dibuat dari Goods Receipt.</span>
                                                @can('create_invoices')
                                                <a href="{{ route('web.invoices.customer.create') }}" class="btn btn-sm btn-success mt-4">
                                                    <i class="ki-outline ki-plus fs-4"></i>
                                                    Buat Tagihan Pertama
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($customerInvoices->hasPages())
                        <div class="d-flex justify-content-center mt-7">
                            {{ $customerInvoices->appends(['tab' => 'customer'])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
