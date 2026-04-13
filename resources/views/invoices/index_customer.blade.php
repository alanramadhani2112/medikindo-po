@extends('layouts.app')

@section('content')
        {{-- KPI Cards --}}
    <div class="row mb-7">
        <div class="col-md-4">
            <div class="card bg-warning">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Menunggu Pembayaran</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($invoices->where('status', '!=', 'paid')->sum('total_amount'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Sudah Dibayar</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($invoices->where('status', 'paid')->sum('total_amount'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Jatuh Tempo</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($invoices->where('status', 'overdue')->sum('total_amount'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.invoices.index') }}" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="tab" value="customer">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="flex-grow-1" style="max-width: 400px;">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nomor invoice atau klinik..." 
                           class="form-control form-control-solid">
                </div>
                <button type="submit" class="btn btn-dark">
                    <i class="ki-outline ki-magnifier fs-2"></i>
                    Filter
                </button>
                @if(request('search'))
                    <a href="{{ route('web.invoices.index', array_merge(request()->except(['search', 'page']), ['tab' => 'customer'])) }}" 
                       class="btn btn-light">
                        <i class="ki-outline ki-cross fs-2"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Main Card with Tabs --}}
    <div class="card">
        {{-- TABS --}}
        <div class="card-header border-0 pt-6 pb-2">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
                @php
                    $tabStatus = [
                        '' => ['label' => 'Semua Faktur', 'icon' => 'ki-document', 'color' => 'primary'],
                        'unpaid' => ['label' => 'Belum Lunas', 'icon' => 'ki-time', 'color' => 'warning'],
                        'paid' => ['label' => 'Lunas', 'icon' => 'ki-check-circle', 'color' => 'success'],
                        'overdue' => ['label' => 'Jatuh Tempo', 'icon' => 'ki-information-5', 'color' => 'danger'],
                    ];
                @endphp
                @foreach($tabStatus as $val => $tabData)
                    @php 
                        $isActive = (string)request('status', '') === (string)$val;
                        $count = $invoices->where('status', $val === '' ? null : $val)->count();
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('web.invoices.index', array_merge(request()->except(['status', 'page']), ['tab' => 'customer', 'status' => $val === '' ? null : $val])) }}" 
                           class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                            <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-2 text-{{ $tabData['color'] }}"></i>
                            <span class="fs-6 fw-bold">{{ $tabData['label'] }}</span>
                            <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-' . $tabData['color'] }} ms-auto">
                                {{ $count }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body">
            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start">Nomor Faktur</th>
                            <th>Klinik / Organisasi</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-end">Total Tagihan</th>
                            <th>Status</th>
                            <th class="text-end pe-4 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('web.invoices.customer.show', $invoice) }}" 
                                           class="text-gray-900 text-hover-primary fw-bold fs-6">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                        <span class="text-gray-500 fs-7 mt-1">Ref: {{ $invoice->purchaseOrder?->po_number ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold">{{ $invoice->organization?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-semibold {{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                                            {{ $invoice->due_date->format('d M Y') }}
                                        </span>
                                        @if(!$invoice->isPaid())
                                            <span class="text-gray-500 fs-7 mt-1">{{ $invoice->due_date->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold fs-6">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                        <span class="text-primary fs-7 fw-semibold mt-1">Terbayar: Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusColor = match($invoice->status) {
                                            'paid' => 'success',
                                            'unpaid' => 'warning',
                                            'overdue' => 'danger',
                                            'draft' => 'secondary',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $statusColor }}">{{ strtoupper($invoice->status) }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ki-outline ki-dots-vertical fs-3"></i>
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="{{ route('web.invoices.customer.show', $invoice) }}" class="dropdown-item">
                                                <i class="ki-outline ki-eye fs-4 me-2 text-primary"></i>
                                                Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Tidak ada faktur penagihan</span>
                                        <span class="text-gray-500 fs-6">Data faktur akan muncul setelah proses penagihan aktif.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($invoices->hasPages())
                <div class="d-flex flex-stack flex-wrap pt-7">
                    <div class="fs-6 fw-semibold text-gray-700">
                        Menampilkan {{ $invoices->firstItem() }} - {{ $invoices->lastItem() }} dari {{ $invoices->total() }} faktur
                    </div>
                    <div>
                        {{ $invoices->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection