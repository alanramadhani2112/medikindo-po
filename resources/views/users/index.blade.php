<x-index-layout title="Manajemen Pengguna" description="Kelola pengguna dan hak akses sistem" :breadcrumbs="[['label' => 'Users']]">
    <x-slot name="actions">
        @can('manage_users')
            <x-button :href="route('web.users.create')" icon="plus" label="Tambah Pengguna" />
        @endcan
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.users.index')">
            <input type="hidden" name="status" value="{{ request('status') }}">
            
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control form-control-solid ps-12" 
                           placeholder="Cari nama atau email...">
                </div>
            </div>
            
            <div style="min-width: 150px;">
                <select name="role" class="form-select form-select-solid">
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
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                '' => ['label' => 'Semua', 'icon' => 'ki-home'],
                'active' => ['label' => 'Aktif', 'icon' => 'ki-check-circle'],
                'inactive' => ['label' => 'Nonaktif', 'icon' => 'ki-cross-circle'],
            ];
            $currentTab = request('status', '');
            $counts = [
                '' => \App\Models\User::count(),
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
                    <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                    <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                        {{ $counts[$val] }}
                    </span>
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Daftar Pengguna</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Pengguna</th>
                <th>Role</th>
                <th>Organisasi</th>
                <th>Status</th>
                <th>Bergabung</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-6">{{ $user->name }}</span>
                                <span class="text-gray-500 fs-7">{{ $user->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($user->roles as $role)
                                <span class="badge badge-light-info fs-7 fw-semibold">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if($user->organization)
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-semibold fs-6">{{ $user->organization->name }}</span>
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
                    <td>
                        <span class="text-gray-700 fw-semibold fs-7">{{ $user->created_at->format('d M Y') }}</span>
                    </td>
                    <td class="text-end">
                        <x-table-action>
                            <x-table-action.item :href="route('web.users.edit', $user)" icon="pencil" label="Edit Pengguna" color="warning" />
                            @if($user->id !== auth()->id())
                                <x-table-action.divider />
                                <x-table-action.item
                                    icon="{{ $user->is_active ? 'cross-square' : 'check-circle' }}"
                                    label="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    color="{{ $user->is_active ? 'danger' : 'success' }}"
                                    :form="['method' => 'DELETE', 'action' => route('web.users.destroy', $user)]"
                                    :confirm="$user->is_active ? 'Nonaktifkan pengguna ' . $user->name . '?' : 'Aktifkan pengguna ' . $user->name . '?'" />
                            @endif
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-10">
                        <x-empty-state icon="file-deleted" title="Tidak Ada Data" message="Belum ada data pengguna yang tersedia untuk filter ini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $users->links() }}
        </div>
    @endif
</x-index-layout>
