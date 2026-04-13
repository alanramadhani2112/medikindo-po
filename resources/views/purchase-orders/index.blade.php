@extends('layouts.app', ['pageTitle' => 'Purchase Orders'])

@section('content')
{{-- Filter Bar (STANDARD) --}}
<div class="card mb-5">
    <div class="card-body">
        <form action="{{ route('web.po.index') }}" method="GET" class="d-flex flex-wrap gap-3">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
            
            {{-- LEFT: Search --}}
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control form-control-solid ps-12" 
                           placeholder="Cari nomor PO, organisasi, atau supplier...">
                </div>
            </div>
            
            {{-- Organization Filter (Super Admin only) --}}
            @if(auth()->user()->hasRole('Super Admin'))
            <select name="organization" class="form-select form-select-solid" style="max-width: 200px;">
                <option value="">Semua Organisasi</option>
                @foreach($organizations ?? [] as $org)
                    <option value="{{ $org->id }}" {{ request('organization') == $org->id ? 'selected' : '' }}>
                        {{ $org->name }}
                    </option>
                @endforeach
            </select>
            @endif
            
            {{-- Date Range --}}
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                   class="form-control form-control-solid" style="max-width: 180px;" 
                   placeholder="Dari Tanggal">
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                   class="form-control form-control-solid" style="max-width: 180px;" 
                   placeholder="Sampai Tanggal">
            
            {{-- Search Button --}}
            <button type="submit" class="btn btn-dark">
                <i class="ki-outline ki-magnifier fs-2"></i>
                Filter
            </button>
            
            {{-- Reset Button --}}
            @if(request()->filled('search') || request()->filled('organization') || request()->filled('date_from'))
                <a href="{{ route('web.po.index', ['tab' => $tab ?? 'all']) }}" class="btn btn-light">
                    <i class="ki-outline ki-cross fs-2"></i>
                    Reset
                </a>
            @endif
            
            {{-- RIGHT: Create Button --}}
            @can('create_purchase_orders')
            <div class="ms-auto">
                <a href="{{ route('web.po.create') }}" class="btn btn-primary">
                    <i class="ki-outline ki-plus fs-2"></i>
                    Buat PO Baru
                </a>
            </div>
            @endcan
        </form>
    </div>
</div>

{{-- Tabs (STANDARD) --}}
<div class="card mb-5">
    <div class="card-header border-0 pt-6 pb-2">
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
            @php
                $tabOptions = [
                    'all' => ['label' => 'Semua', 'icon' => 'ki-element-11'],
                    'draft' => ['label' => 'Draft', 'icon' => 'ki-document'],
                    'submitted' => ['label' => 'Diajukan', 'icon' => 'ki-send'],
                    'approved' => ['label' => 'Disetujui', 'icon' => 'ki-check-circle'],
                    'rejected' => ['label' => 'Ditolak', 'icon' => 'ki-cross-circle'],
                    'completed' => ['label' => 'Selesai', 'icon' => 'ki-verify'],
                ];
            @endphp
            @foreach($tabOptions as $val => $tabData)
                @php 
                    $isActive = ($tab ?? 'all') === $val;
                    $count = $counts[$val] ?? 0;
                @endphp
                <li class="nav-item">
                    <a href="{{ route('web.po.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val])) }}" 
                       class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                        <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-2"></i>
                        <span class="fs-6 fw-bold">{{ $tabData['label'] }}</span>
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                <thead>
                    <tr class="fw-bold text-muted bg-light">
                        <th class="ps-4 rounded-start min-w-150px">Nomor PO</th>
                        <th class="min-w-150px">Organisasi</th>
                        <th class="min-w-150px">Supplier</th>
                        <th class="min-w-100px">Status</th>
                        <th class="text-end min-w-120px">Total Amount</th>
                        <th class="min-w-120px d-none d-md-table-cell">Tanggal</th>
                        <th class="text-end pe-4 rounded-end min-w-150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $order)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('web.po.show', $order) }}" class="text-gray-900 text-hover-primary fw-bold fs-6">
                                    {{ $order->po_number }}
                                </a>
                                <div class="text-muted fs-7 mt-1">
                                    <i class="ki-outline ki-user fs-7 me-1"></i>
                                    {{ $order->creator->name ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-gray-800 fs-6">{{ $order->organization->name ?? '-' }}</div>
                                <div class="text-muted fs-7">{{ $order->organization->type ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-gray-800 fs-6">{{ $order->supplier->name ?? '-' }}</div>
                                <div class="text-muted fs-7">{{ $order->supplier->contact ?? '-' }}</div>
                            </td>
                            <td>
                                @php
                                    $statusColor = match($order->status) {
                                        'draft' => 'secondary',
                                        'submitted' => 'warning',
                                        'approved' => 'success',
                                        'shipped' => 'primary',
                                        'delivered', 'completed' => 'success',
                                        'rejected', 'cancelled' => 'danger',
                                        default => 'primary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $statusColor }}">{{ strtoupper($order->status) }}</span>
                                @if($order->has_narcotics)
                                    <span class="badge badge-danger d-block mt-1">⚠ NARKOTIKA</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <div class="text-gray-800 fw-semibold fs-7">{{ $order->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted fs-8">{{ $order->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ki-outline ki-dots-vertical fs-3"></i>
                                        Aksi
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="{{ route('web.po.show', $order) }}" class="dropdown-item">
                                            <i class="ki-outline ki-eye fs-4 me-2 text-primary"></i>
                                            Lihat Detail
                                        </a>
                                        @if($order->status === 'draft')
                                            @can('update_purchase_orders')
                                            <a href="{{ route('web.po.edit', $order) }}" class="dropdown-item">
                                                <i class="ki-outline ki-notepad-edit fs-4 me-2 text-primary"></i>
                                                Edit PO
                                            </a>
                                            @endcan
                                        @endif
                                        <a href="{{ route('web.po.pdf', $order) }}" class="dropdown-item" target="_blank">
                                            <i class="ki-outline ki-file-down fs-4 me-2 text-info"></i>
                                            Download PDF
                                        </a>
                                        @if($order->status === 'draft')
                                            @can('delete_purchase_orders')
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('web.po.destroy', $order) }}" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Yakin ingin menghapus PO ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ki-outline ki-trash fs-4 me-2"></i>
                                                    Hapus PO
                                                </button>
                                            </form>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                    <h3 class="fs-5 fw-bold text-gray-800 mb-1">Tidak Ada Data</h3>
                                    <p class="text-muted fs-7">Belum ada purchase order yang tersedia saat ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination (STANDARD) --}}
        @if($purchaseOrders->hasPages())
        <div class="d-flex flex-stack flex-wrap pt-7">
            <div class="fs-6 fw-semibold text-gray-700">
                Menampilkan {{ $purchaseOrders->firstItem() }} - {{ $purchaseOrders->lastItem() }} dari {{ $purchaseOrders->total() }} data
            </div>
            <div>
                {{ $purchaseOrders->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
