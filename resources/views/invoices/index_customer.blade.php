@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">
                <i class="ki-outline ki-arrow-up fs-2 text-success me-2"></i>
                Tagihan ke RS/Klinik (AR)
            </h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola tagihan yang diterbitkan ke RS/Klinik</p>
        </div>
        @can('create_invoices')
        <div>
            <a href="{{ route('web.invoices.customer.create') }}" class="btn btn-success">
                <i class="ki-outline ki-plus fs-3"></i>
                Buat Tagihan ke RS/Klinik
            </a>
        </div>
        @endcan
    </div>

    {{-- Invoice Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start">Nomor Invoice</th>
                            <th>RS/Klinik</th>
                            <th>PO Number</th>
                            <th>GR Number</th>
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
                                <td>
                                    <span class="text-primary">{{ $invoice->goodsReceipt?->gr_number ?? '—' }}</span>
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
                                <td colspan="7" class="text-center py-10">
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
                    {{ $customerInvoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
