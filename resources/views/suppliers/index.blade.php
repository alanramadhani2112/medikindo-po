@extends('layouts.app')

@section('content')
    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-duotone ki-check-circle fs-2 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Page Header with Add Button --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Manajemen Supplier</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola data supplier dan distributor</p>
        </div>
        @can('manage_suppliers')
            <a href="{{ route('web.suppliers.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i>
                Tambah Supplier
            </a>
        @endcan
    </div>

    {{-- Filter Bar (STANDARD) --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.suppliers.index') }}" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="status" value="{{ request('status') }}">
                
                {{-- LEFT: Search --}}
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nama, kode, atau email...">
                    </div>
                </div>
                
                {{-- Search Button --}}
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-duotone ki-magnifier fs-2"></i>
                    Cari
                </button>
                
                {{-- Reset Button --}}
                @if(request()->filled('search'))
                    <a href="{{ route('web.suppliers.index', ['status' => request('status')]) }}" class="btn btn-light">
                        <i class="ki-duotone ki-cross fs-2"></i>
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
                        '' => ['label' => 'Semua', 'icon' => 'ki-element-11'],
                        'active' => ['label' => 'Aktif', 'icon' => 'ki-check-circle'],
                        'inactive' => ['label' => 'Nonaktif', 'icon' => 'ki-cross-circle'],
                    ];
                    $currentTab = request('status', '');
                    $counts = [
                        '' => \App\Models\Supplier::count(),
                        'active' => \App\Models\Supplier::where('is_active', true)->count(),
                        'inactive' => \App\Models\Supplier::where('is_active', false)->count(),
                    ];
                @endphp
                @foreach($tabOptions as $val => $tabData)
                    @php
                        $isActive = (string)$currentTab === (string)$val;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('web.suppliers.index', array_merge(request()->except(['status', 'page']), ['status' => $val === '' ? null : $val])) }}" 
                           class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                            <i class="ki-duotone {{ $tabData['icon'] }} fs-4 me-2"></i>
                            <span class="fs-6 fw-bold">{{ $tabData['label'] }}</span>
                            <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-auto">
                                {{ $counts[$val] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body pt-6">
            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 min-w-250px rounded-start">Supplier / Kode</th>
                            <th class="min-w-200px">Kontak</th>
                            <th class="min-w-200px">Alamat</th>
                            <th class="min-w-100px">Status</th>
                            <th class="text-end min-w-100px pe-4 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px">
                                            <div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">
                                                {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold">{{ $supplier->name }}</span>
                                            <span class="text-gray-500 fs-7">{{ $supplier->code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-semibold">{{ $supplier->email ?? '—' }}</span>
                                        <span class="text-gray-600 fs-7">{{ $supplier->phone ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-gray-700">{{ Str::limit($supplier->address ?? '—', 50) }}</span>
                                </td>
                                <td>
                                    @if($supplier->is_active)
                                        <span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
                                    @else
                                        <span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ki-duotone ki-dots-vertical fs-3"></i>
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="{{ route('web.suppliers.edit', $supplier) }}" class="dropdown-item">
                                                <i class="ki-duotone ki-notepad-edit fs-4 me-2 text-primary"></i>
                                                Edit Supplier
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('web.suppliers.toggle_status', $supplier) }}" 
                                                  onsubmit="return confirm('{{ $supplier->is_active ? 'Nonaktifkan' : 'Aktifkan' }} supplier ini?')" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item {{ $supplier->is_active ? 'text-warning' : 'text-success' }}">
                                                    <i class="ki-duotone ki-{{ $supplier->is_active ? 'shield-cross' : 'shield-tick' }} fs-4 me-2"></i>
                                                    {{ $supplier->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Supplier
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum ada data supplier</span>
                                        <span class="text-gray-500 fs-6">Data supplier akan muncul setelah proses registrasi.</span>
                                        @can('manage_supplier')
                                            <a href="{{ route('web.suppliers.create') }}" class="btn btn-primary mt-5">
                                                <i class="ki-duotone ki-plus fs-2"></i>
                                                Tambah Supplier
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($suppliers->hasPages())
                <div class="pagination-wrapper">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
    </div>
@endsection
