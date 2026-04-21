<x-index-layout title="Manajemen Supplier" description="Kelola data supplier dan distributor" :breadcrumbs="[['label' => 'Suppliers']]">
    <x-slot name="actions">
        @can('manage_suppliers')
            <x-button :href="route('web.suppliers.create')" icon="plus" label="Tambah Supplier" />
        @endcan
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.suppliers.index')">
            <input type="hidden" name="status" value="{{ request('status') }}">
            
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control form-control-solid ps-12" 
                           placeholder="Cari nama, kode, atau email...">
                </div>
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
                    <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                    <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                        {{ $counts[$val] }}
                    </span>
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Daftar Supplier</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Supplier / Kode</th>
                <th>Kontak</th>
                <th>Alamat</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">
                                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-6">{{ $supplier->name }}</span>
                                <span class="text-gray-500 fs-7">{{ $supplier->code }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-semibold fs-7">{{ $supplier->email ?? '—' }}</span>
                            <span class="text-gray-600 fs-7">{{ $supplier->phone ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="text-gray-700 fs-7">{{ Str::limit($supplier->address ?? '—', 50) }}</span>
                    </td>
                    <td>
                        @if($supplier->is_active)
                            <span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
                        @else
                            <span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <x-table-action>
                            <x-table-action.item :href="route('web.suppliers.edit', $supplier)" icon="pencil" label="Edit Supplier" color="warning" />
                            <x-table-action.divider />
                            <x-table-action.item
                                icon="{{ $supplier->is_active ? 'cross-square' : 'check-circle' }}"
                                label="{{ $supplier->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                color="{{ $supplier->is_active ? 'danger' : 'success' }}"
                                :form="['method' => 'PATCH', 'action' => route('web.suppliers.toggle_status', $supplier)]"
                                :confirm="$supplier->is_active ? 'Nonaktifkan supplier ' . $supplier->name . '?' : 'Aktifkan supplier ' . $supplier->name . '?'" />
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-10">
                        <x-empty-state icon="file-deleted" title="Tidak Ada Data" message="Belum ada data supplier yang tersedia untuk filter ini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($suppliers->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $suppliers->links() }}
        </div>
    @endif
</x-index-layout>
