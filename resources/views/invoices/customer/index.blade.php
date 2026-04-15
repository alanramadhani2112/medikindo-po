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
    </div>

    {{-- Filter Bar --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.invoices.customer.index') }}" method="GET" class="d-flex flex-wrap gap-3 align-items-end">
                <div class="flex-grow-1" style="max-width: 300px;">
                    <label class="form-label fs-7 fw-semibold text-gray-600">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control form-control-solid"
                           placeholder="No. Invoice / Nama RS...">
                </div>
                <div>
                    <label class="form-label fs-7 fw-semibold text-gray-600">Status</label>
                    <select name="status" class="form-select form-select-solid" style="min-width: 150px;">
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="issued" @selected(request('status') === 'issued')>Issued</option>
                        <option value="partial_paid" @selected(request('status') === 'partial_paid')>Partial Paid</option>
                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                        <option value="void" @selected(request('status') === 'void')>Void</option>
                    </select>
                </div>
                <div>
                    <label class="form-label fs-7 fw-semibold text-gray-600">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-solid">
                </div>
                <div>
                    <label class="form-label fs-7 fw-semibold text-gray-600">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-solid">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-magnifier fs-3"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light">
                            <i class="ki-outline ki-arrow-circle-left fs-3"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Invoice Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-5 min-w-150px rounded-start">No. Invoice</th>
                            <th class="min-w-180px">RS/Klinik</th>
                            <th class="min-w-110px">Tgl. Invoice</th>
                            <th class="min-w-110px">Jatuh Tempo</th>
                            <th class="text-end min-w-130px">Grand Total</th>
                            <th class="text-center min-w-120px">Status</th>
                            <th class="text-end pe-5 min-w-100px rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ps-5">
                                    <a href="{{ route('web.invoices.customer.show', $invoice) }}"
                                       class="fw-bold text-gray-900 text-hover-primary">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    @if($invoice->supplierInvoice)
                                        <div class="text-muted fs-8 mt-1">
                                            <i class="ki-outline ki-arrow-right fs-8 me-1"></i>
                                            AP: {{ $invoice->supplierInvoice->invoice_number }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold text-gray-700">{{ $invoice->organization?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-600">
                                        {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '—' }}
                                    </span>
                                </td>
                                <td>
                                    @if($invoice->due_date)
                                        @php $isOverdue = $invoice->due_date->isPast() && !in_array($invoice->status, ['paid', 'void']); @endphp
                                        <span class="fw-semibold {{ $isOverdue ? 'text-danger' : 'text-gray-700' }}">
                                            {{ $invoice->due_date->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-gray-900">
                                        Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $badgeClass = match($invoice->status) {
                                            'draft'        => 'badge-secondary',
                                            'issued'       => 'badge-warning',
                                            'partial_paid' => 'badge-info',
                                            'paid'         => 'badge-success',
                                            'void'         => 'badge-danger',
                                            default        => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ strtoupper(str_replace('_', ' ', $invoice->status)) }}</span>
                                </td>
                                <td class="text-end pe-5">
                                    <div class="action-menu-wrapper">
                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary" data-action-menu>
                                            <i class="ki-outline ki-dots-vertical fs-3"></i>
                                            Aksi
                                        </button>
                                        <div class="action-dropdown-menu" style="display: none;">
                                            <div class="menu-item px-3">
                                                <a href="{{ route('web.invoices.customer.show', $invoice) }}" class="menu-link px-3">
                                                    <i class="ki-outline ki-eye fs-4 me-2 text-primary"></i>
                                                    Lihat Detail
                                                </a>
                                            </div>
                                            @if($invoice->status === 'draft')
                                                <div class="menu-item px-3">
                                                    <form method="POST" action="{{ route('web.invoices.customer.issue', $invoice) }}" class="d-inline w-100">
                                                        @csrf
                                                        <button type="submit" class="menu-link px-3 w-100 text-start text-success"
                                                                style="background: none; border: none;">
                                                            <i class="ki-outline ki-check-circle fs-4 me-2"></i>
                                                            Terbitkan
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                            <div class="separator my-2"></div>
                                            <div class="menu-item px-3">
                                                <a href="{{ route('web.invoices.customer.pdf', $invoice) }}" target="_blank" class="menu-link px-3">
                                                    <i class="ki-outline ki-document fs-4 me-2 text-info"></i>
                                                    Cetak PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-document fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Tagihan AR</span>
                                        <span class="text-gray-500 fs-6">Tagihan AR dibuat otomatis saat SupplierInvoice diverifikasi.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="d-flex justify-content-center py-5">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
