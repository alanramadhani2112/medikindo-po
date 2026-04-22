<x-index-layout title="Purchase Orders" :breadcrumbs="[['label' => 'Purchase Orders']]">
    <x-slot name="actions">
        @can('create_purchase_orders')
            <x-button :href="route('web.po.create')" icon="plus" label="Buat PO Baru" />
        @endcan
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.po.index')">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
            
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="Cari nomor PO, organisasi, atau supplier..." value="{{ request('search') }}">
                </div>
            </div>
            
            @if(auth()->user()->hasRole('Super Admin'))
                <div style="min-width: 150px;">
                    <select name="organization" class="form-select form-select-solid">
                        <option value="">Semua Organisasi</option>
                        @foreach($organizations ?? [] as $org)
                            <option value="{{ $org->id }}" {{ request('organization') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div style="max-width: 150px;">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-solid">
            </div>
            <div style="max-width: 150px;">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-solid">
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                'all'                => ['label' => 'Semua',             'icon' => 'home'],
                'draft'              => ['label' => 'Draft',             'icon' => 'document'],
                'submitted'          => ['label' => 'Diajukan',          'icon' => 'send'],
                'approved'           => ['label' => 'Disetujui',         'icon' => 'check-circle'],
                'partially_received' => ['label' => 'Diterima Sebagian', 'icon' => 'delivery'],
                'rejected'           => ['label' => 'Ditolak',           'icon' => 'cross-circle'],
                'completed'          => ['label' => 'Selesai',           'icon' => 'verify'],
            ];
        @endphp
        <x-status-tabs :tabs="$tabOptions" :current="$tab ?? 'all'" route="web.po.index" :counts="$counts" />
    </x-slot>

    <x-slot name="tableHeader">Daftar Purchase Order</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Nomor PO</th>
                <th>Organisasi</th>
                <th>Supplier</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrders as $order)
                <tr>
                    <td>
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
                            $statusMap = [
                                'draft'              => ['label' => 'Draft',             'color' => 'secondary'],
                                'submitted'          => ['label' => 'Diajukan',          'color' => 'warning'],
                                'approved'           => ['label' => 'Disetujui',         'color' => 'info'],
                                'partially_received' => ['label' => 'Diterima Sebagian', 'color' => 'primary'],
                                'rejected'           => ['label' => 'Ditolak',           'color' => 'danger'],
                                'completed'          => ['label' => 'Selesai',           'color' => 'success'],
                            ];
                            $st = $statusMap[$order->status] ?? ['label' => strtoupper($order->status), 'color' => 'primary'];
                        @endphp
                        <span class="badge badge-light-{{ $st['color'] }} fw-bold">{{ $st['label'] }}</span>
                        @if($order->has_narcotics)
                            <span class="badge badge-light-danger d-block mt-1">⚠ NARKOTIKA</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        <div class="text-muted fs-8 mt-1">{{ $order->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td class="text-end">
                        <x-table-action>
                            <x-table-action.item :href="route('web.po.show', $order)" icon="eye" label="Lihat Detail" />
                            @if(($order->status instanceof \BackedEnum ? $order->status->value : $order->status) === 'draft')
                                @can('update_purchase_orders')
                                    <x-table-action.item :href="route('web.po.edit', $order)" icon="pencil" label="Edit PO" color="warning" />
                                @endcan
                            @endif
                            <x-table-action.divider />
                            <x-table-action.item :href="route('web.po.pdf', $order)" icon="file-down" label="Download PDF" color="info" target="_blank" />
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-10">
                        <x-empty-state icon="file-deleted" title="Tidak Ada Data" message="Belum ada purchase order yang tersedia untuk filter ini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($purchaseOrders->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $purchaseOrders->links() }}
        </div>
    @endif
</x-index-layout>
