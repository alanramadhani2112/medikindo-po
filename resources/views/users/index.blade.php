@extends('layouts.app')

@section('content')
    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-solid ki-check-circle fs-2 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Page Header with Add Button --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Manajemen Pengguna</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola pengguna dan hak akses sistem</p>
        </div>
        @can('manage_users')
            <a href="{{ route('web.users.create') }}" class="btn btn-primary">
                <i class="ki-solid ki-plus fs-2"></i>
                Tambah Pengguna
            </a>
        @endcan
    </div>

    {{-- Filter Bar (STANDARD) --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.users.index') }}" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="status" value="{{ request('status') }}">
                
                {{-- LEFT: Search --}}
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-solid ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nama atau email...">
                    </div>
                </div>
                
                {{-- Role Filter --}}
                <select name="role" class="form-select form-select-solid" style="max-width: 200px;">
                    <option value="">Semua Role</option>
                    @php
                        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
                        $selectedRole = request('role');
                    @endphp
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $selectedRole === $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                
                {{-- Search Button --}}
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-solid ki-magnifier fs-2"></i>
                    Cari
                </button>
                
                {{-- Reset Button --}}
                @if(request()->filled('search') || request()->filled('role'))
                    <a href="{{ route('web.users.index', ['status' => request('status')]) }}" class="btn btn-light">
                        <i class="ki-solid ki-cross fs-2"></i>
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
                        '' => ['label' => 'Semua', 'icon' => 'ki-home'],
                        'active' => ['label' => 'Aktif', 'icon' => 'ki-check-circle'],
                        'inactive' => ['label' => 'Nonaktif', 'icon' => 'ki-cross-circle'],
                    ];
                    $currentTab = request('status', '');
                    $counts = [
                        '' => $users->total(),
                        'active' => \App\Models\User::where('is_active', true)->count(),
                        'inactive' => \App\Models\User::where('is_active', false)->count(),
                    ];
                @endphp
                @foreach($tabOptions as $val => $tabData)
                    @php
                        $isActive = (string)$currentTab === (string)$val;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('web.users.index', array_merge(request()->except(['status', 'page']), ['status' => $val === '' ? null : $val])) }}" 
                           class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                            <i class="ki-solid {{ $tabData['icon'] }} fs-4 me-2"></i>
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
                            <th class="ps-4 min-w-250px rounded-start">Pengguna</th>
                            <th class="min-w-150px d-none d-md-table-cell">Role</th>
                            <th class="min-w-200px d-none d-lg-table-cell">Organisasi</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-125px d-none d-sm-table-cell">Bergabung</th>
                            <th class="text-end min-w-100px pe-4 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-45px">
                                            <div class="symbol-label fs-5 fw-bold bg-light-primary text-primary">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold fs-6 mb-1">{{ $user->name }}</span>
                                            <span class="text-gray-500 fs-7">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="badge badge-light-info fs-7 fw-semibold">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if($user->organization)
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-semibold fs-6 mb-1">{{ $user->organization->name }}</span>
                                            <span class="text-gray-500 fs-7">{{ $user->organization->type }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 fs-7 fst-italic">System Wide</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
                                    @else
                                        <span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>
                                    @endif
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="text-gray-700 fw-semibold fs-6">{{ $user->created_at->format('d M Y') }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    @can('manage_users')
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ki-solid ki-dots-vertical fs-3"></i>
                                                Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('web.users.edit', $user) }}" class="dropdown-item">
                                                    <i class="ki-solid ki-notepad-edit fs-4 me-2 text-primary"></i>
                                                    Edit Pengguna
                                                </a>
                                                @if($user->id !== auth()->id())
                                                    <div class="dropdown-divider"></div>
                                                    <form method="POST" action="{{ route('web.users.destroy', $user) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="dropdown-item {{ $user->is_active ? 'text-warning' : 'text-success' }} toggle-status-confirm"
                                                                data-name="{{ $user->name }}"
                                                                data-status="{{ $user->is_active ? 'active' : 'inactive' }}">
                                                            <i class="ki-solid ki-{{ $user->is_active ? 'cross-square' : 'check-circle' }} fs-4 me-2"></i>
                                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Pengguna
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-solid ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum ada data pengguna</span>
                                        <span class="text-gray-500 fs-6">Tambahkan pengguna baru untuk mulai mengelola akses sistem.</span>
                                        @can('manage_users')
                                            <a href="{{ route('web.users.create') }}" class="btn btn-primary mt-5">
                                                <i class="ki-solid ki-plus fs-2"></i>
                                                Tambah Pengguna
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
            @if($users->hasPages())
                <div class="pagination-wrapper">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
