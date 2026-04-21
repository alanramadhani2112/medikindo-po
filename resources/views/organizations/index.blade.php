<x-index-layout title="Manajemen Organisasi" description="Kelola data klinik dan rumah sakit" :breadcrumbs="[['label' => 'Organizations']]">
    <x-slot name="actions">
        @can('manage_organizations')
            <x-button :href="route('web.organizations.create')" icon="plus" label="Tambah Organisasi" />
        @endcan
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.organizations.index')">
            <input type="hidden" name="tab" value="{{ request('tab', 'all') }}">
            
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control form-control-solid ps-12" 
                           placeholder="Cari nama atau kode...">
                </div>
            </div>
            
            <div style="min-width: 150px;">
                <select name="status" class="form-select form-select-solid">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                'all' => ['label' => 'Semua', 'icon' => 'ki-home'],
                'hospital' => ['label' => 'Rumah Sakit', 'icon' => 'ki-geolocation'],
                'clinic' => ['label' => 'Klinik', 'icon' => 'ki-office-bag'],
            ];
            $currentTab = request('tab', 'all');
            // Re-calculating counts for the tabs
            $allCounts = \App\Models\Organization::selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type');
            $totalCount = $allCounts->sum();
        @endphp
        @foreach($tabOptions as $val => $tabData)
            @php 
                $isActive = $currentTab === $val;
                $count = $val === 'all' ? $totalCount : ($allCounts[$val] ?? 0);
            @endphp
            <li class="nav-item">
                <a href="{{ route('web.organizations.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val])) }}" 
                   class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                    <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                    <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                        {{ $count }}
                    </span>
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Daftar Organisasi Pelanggan</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Organisasi / Kode</th>
                <th>Tipe</th>
                <th>Kontak</th>
                <th>Izin Operasional</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($organizations as $org)
                <tr>
                    <td>
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
                        <span class="badge badge-light-info fw-bold">{{ strtoupper($org->type) }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold fs-7">{{ $org->phone ?? '—' }}</span>
                            <span class="text-muted fs-7">{{ $org->email ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="text-gray-800 fw-semibold fs-7">{{ $org->license_number ?? '—' }}</span>
                    </td>
                    <td>
                        @if($org->is_active)
                            <span class="badge badge-light-success fs-7 fw-bold">AKTIF</span>
                        @else
                            <span class="badge badge-light-secondary fs-7 fw-bold">NONAKTIF</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <x-table-action>
                            <x-table-action.item :href="route('web.organizations.edit', $org)" icon="pencil" label="Edit Organisasi" color="warning" />
                            <x-table-action.divider />
                            <x-table-action.item
                                icon="{{ $org->is_active ? 'cross-square' : 'check-circle' }}"
                                label="{{ $org->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                color="{{ $org->is_active ? 'danger' : 'success' }}"
                                :form="['method' => 'PATCH', 'action' => route('web.organizations.toggle_status', $org)]"
                                :confirm="$org->is_active ? 'Nonaktifkan organisasi ' . $org->name . '?' : 'Aktifkan organisasi ' . $org->name . '?'" />
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-10">
                        <x-empty-state icon="file-deleted" title="Tidak Ada Data" message="Belum ada data organisasi yang tersedia untuk filter ini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($organizations->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $organizations->links() }}
        </div>
    @endif
</x-index-layout>
