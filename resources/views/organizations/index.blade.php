@extends('layouts.app', ['pageTitle' => 'Organizations'])

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
            <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Manajemen Organisasi</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola data klinik dan rumah sakit</p>
        </div>
        @can('manage_organizations')
            <a href="{{ route('web.organizations.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i>
                Tambah Organisasi
            </a>
        @endcan
    </div>

    {{-- Filter Bar (STANDARD) --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.organizations.index') }}" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
                
                {{-- LEFT: Search --}}
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nama atau kode...">
                    </div>
                </div>
                
                {{-- Status Filter --}}
                <select name="status" class="form-select form-select-solid" style="max-width: 180px;">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                
                {{-- Search Button --}}
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-duotone ki-magnifier fs-2"></i>
                    Cari
                </button>
                
                {{-- Reset Button --}}
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('web.organizations.index', ['tab' => $tab ?? 'all']) }}" class="btn btn-light">
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
                        'all' => ['label' => 'Semua', 'icon' => 'ki-home-2'],
                        'hospital' => ['label' => 'Rumah Sakit', 'icon' => 'ki-hospital'],
                        'clinic' => ['label' => 'Klinik', 'icon' => 'ki-office-bag'],
                    ];
                    $tab = request('tab', 'all');
                    $counts = [
                        'all' => $organizations->total(),
                        'hospital' => $organizations->where('type', 'hospital')->count(),
                        'clinic' => $organizations->where('type', 'clinic')->count(),
                    ];
                @endphp
                @foreach($tabOptions as $val => $tabData)
                    @php 
                        $isActive = $tab === $val;
                        $count = $counts[$val] ?? 0;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('web.organizations.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val])) }}" 
                           class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                            <i class="ki-duotone {{ $tabData['icon'] }} fs-4 me-2"></i>
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
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-duotone ki-office-bag fs-2 me-2"></i>
                Daftar Organisasi
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start min-w-250px">Organisasi / Kode</th>
                            <th class="min-w-120px">Tipe</th>
                            <th class="min-w-200px">Kontak</th>
                            <th class="min-w-150px">Izin Operasional</th>
                            <th class="min-w-100px">Status</th>
                            <th class="text-end pe-4 rounded-end min-w-120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organizations as $org)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px">
                                            <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                                {{ strtoupper(substr($org->name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-gray-900 fs-6">{{ $org->name }}</span>
                                            <span class="text-muted fs-7">{{ $org->code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-info">{{ strtoupper($org->type) }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-6 mb-1">{{ $org->phone ?? '—' }}</div>
                                    <div class="text-muted fs-7">{{ $org->email ?? '—' }}</div>
                                </td>
                                <td>
                                    <div class="text-gray-800 fw-semibold fs-7">{{ $org->license_number ?? '—' }}</div>
                                </td>
                                <td>
                                    @if($org->is_active)
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
                                            <a href="{{ route('web.organizations.edit', $org) }}" class="dropdown-item">
                                                <i class="ki-duotone ki-notepad-edit fs-4 me-2 text-primary"></i>
                                                Edit Organisasi
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('web.organizations.toggle_status', $org) }}" 
                                                  onsubmit="return confirm('{{ $org->is_active ? 'Nonaktifkan' : 'Aktifkan' }} organisasi ini?')" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item {{ $org->is_active ? 'text-warning' : 'text-success' }}">
                                                    <i class="ki-duotone ki-{{ $org->is_active ? 'shield-cross' : 'shield-tick' }} fs-4 me-2"></i>
                                                    {{ $org->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Organisasi
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-office-bag fs-3x text-gray-400 mb-3"></i>
                                        <h3 class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Data Organisasi</h3>
                                        <p class="text-muted fs-7">Tambahkan organisasi untuk mulai mengelola data lintas fasilitas.</p>
                                        @can('manage_organizations')
                                            <a href="{{ route('web.organizations.create') }}" class="btn btn-primary mt-3">
                                                <i class="ki-duotone ki-plus fs-2"></i>
                                                Registrasi Organisasi
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
            @if($organizations->hasPages())
            <div class="d-flex flex-stack flex-wrap pt-7">
                <div class="text-muted fs-7">
                    Menampilkan {{ $organizations->firstItem() }} - {{ $organizations->lastItem() }} dari {{ $organizations->total() }} data
                </div>
                <div>
                    {{ $organizations->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
