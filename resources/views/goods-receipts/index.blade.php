@extends('layouts.app', ['pageTitle' => 'Penerimaan Barang'])

@section('content')
        {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-outline ki-check-circle fs-2 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Filter Bar (STANDARD) --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.goods-receipts.index') }}" method="GET" class="d-flex flex-wrap gap-3">
                {{-- LEFT: Search --}}
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-outline ki-chart
 fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nomor GR atau nomor PO...">
                    </div>
                </div>
                
                {{-- Status Filter --}}
                <select name="status" class="form-select form-select-solid" style="max-width: 200px;">
                    <option value="">Semua Status</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
                
                {{-- Search Button --}}
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-outline ki-chart
 fs-2"></i>
                    Filter
                </button>
                
                {{-- Reset Button --}}
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('web.goods-receipts.index') }}" class="btn btn-light">
                        <i class="ki-outline ki-arrow-zigzag fs-2"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Tabs (STANDARD) --}}
    <div class="card mb-5">
        <div class="card-header border-0 pt-6 pb-2">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
                @php
                    $tabOptions = [
                        'all' => ['label' => 'Semua', 'icon' => 'ki-home'],
                        'partial' => ['label' => 'Partial', 'icon' => 'ki-information-5'],
                        'completed' => ['label' => 'Selesai', 'icon' => 'ki-check-circle'],
                    ];
                    $tab = request('tab', 'all');
                    $counts = [
                        'all' => $receipts->total(),
                        'partial' => $receipts->where('status', 'partial')->count(),
                        'completed' => $receipts->where('status', 'completed')->count(),
                    ];
                @endphp
                @foreach($tabOptions as $val => $tabData)
                    @php 
                        $isActive = $tab === $val;
                        $count = $counts[$val] ?? 0;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('web.goods-receipts.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val])) }}" 
                           class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                            <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                            <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                            <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-auto">
                                {{ $count }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Table (STANDARD) --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                Daftar Penerimaan Barang
            </h3>
            
            @can('view_goods_receipt')
            <div>
                <a href="{{ route('web.goods-receipts.create') }}" class="btn btn-primary">
                    <i class="ki-outline ki-picture fs-2"></i>
                    Rekam Penerimaan Barang
                </a>
            </div>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start min-w-150px">Nomor GR</th>
                            <th class="min-w-150px">Referensi PO</th>
                            <th class="min-w-200px">Supplier / Organisasi</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-150px">Tanggal / Penerima</th>
                            <th class="text-end pe-4 rounded-end min-w-120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('web.goods-receipts.show', $receipt) }}" 
                                       class="text-gray-900 text-hover-primary fw-bold fs-6">
                                        {{ $receipt->gr_number }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('web.po.show', $receipt->purchase_order_id) }}" 
                                       class="text-primary text-hover-primary fw-bold fs-6">
                                        {{ $receipt->purchaseOrder?->po_number ?? '—' }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-6 mb-1">{{ $receipt->purchaseOrder?->supplier?->name ?? '—' }}</div>
                                    <div class="text-muted fs-7">
                                        <i class="ki-outline ki-office-bag fs-7 me-1"></i>
                                        {{ $receipt->purchaseOrder?->organization?->name ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusColor = match($receipt->status) {
                                            'completed' => 'success',
                                            'partial' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $statusColor }}">{{ strtoupper($receipt->status) }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-6">{{ $receipt->received_date->format('d/m/Y') }}</div>
                                    <div class="text-muted fs-7 mt-1">
                                        <i class="ki-outline ki-user"></i>
                                        {{ $receipt->receivedBy?->name ?? '—' }}
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="action-menu-wrapper">
                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary" data-action-menu>
                                            <i class="ki-outline ki-dots-vertical fs-3"></i>
                                            Aksi
                                        </button>
                                        <div class="action-dropdown-menu" style="display: none;">
                                            <a href="{{ route('web.goods-receipts.show', $receipt) }}" class="d-flex align-items-center">
                                                <i class="ki-outline ki-facebook fs-4 me-2 text-primary"></i>
                                                Lihat Detail
                                            </a>
                                            <a href="{{ route('web.goods-receipts.pdf', $receipt) }}" class="d-flex align-items-center" target="_blank">
                                                <i class="
ki-outline ki-document fs-4 me-2 text-info"></i>
                                                Download PDF
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-package fs-3x text-gray-400 mb-3"></i>
                                        <h3 class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Penerimaan Barang</h3>
                                        <p class="text-muted fs-7">Data penerimaan barang akan muncul setelah proses konfirmasi penerimaan.</p>
                                        @can('confirm_receipt')
                                            <a href="{{ route('web.goods-receipts.create') }}" class="btn btn-primary mt-3">
                                                <i class="ki-outline ki-picture fs-2"></i>
                                                Rekam Penerimaan
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination (STANDARD) --}}
            @if($receipts->hasPages())
            <div class="d-flex flex-stack flex-wrap pt-7">
                <div class="text-muted fs-7">
                    Menampilkan {{ $receipts->firstItem() }} - {{ $receipts->lastItem() }} dari {{ $receipts->total() }} data
                </div>
                <div>
                    {{ $receipts->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection


