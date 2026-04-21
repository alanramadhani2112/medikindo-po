<x-index-layout title="Penerimaan Barang" :breadcrumbs="[['label' => 'Goods Receipt']]">
    <x-slot name="actions">
        @can('confirm_receipt')
            <x-button :href="route('web.goods-receipts.create')" icon="plus" label="Rekam Penerimaan Barang" />
        @endcan
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.goods-receipts.index')">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
            
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="Cari nomor GR atau nomor PO..." value="{{ request('search') }}">
                </div>
            </div>
            
            <div style="min-width: 150px;">
                <select name="status" class="form-select form-select-solid">
                    <option value="">Semua Status GR</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                'all'       => ['label' => 'Semua GR', 'icon' => 'ki-home'],
                'pending'   => ['label' => 'Menunggu Penerimaan', 'icon' => 'ki-notification-on'],
                'partial'   => ['label' => 'Partial', 'icon' => 'ki-information-5'],
                'completed' => ['label' => 'Selesai', 'icon' => 'ki-check-circle'],
            ];
            $currentTab = $tab ?? 'all';
        @endphp
        @foreach($tabOptions as $val => $tabData)
            @php 
                $isActive = $currentTab === $val;
                $count = $counts[$val] ?? 0;
            @endphp
            <li class="nav-item">
                <a href="{{ route('web.goods-receipts.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val])) }}" 
                   class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                    <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                    @if($count > 0)
                        <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                            {{ $count }}
                        </span>
                    @endif
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">
        {{ $currentTab === 'pending' ? 'Purchase Order Menunggu Penerimaan' : 'Daftar Penerimaan Barang' }}
    </x-slot>

    @if($currentTab === 'pending')
        {{-- Table for Pending POs --}}
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
            <thead>
                <tr class="fw-bold text-muted">
                    <th>Nomor PO</th>
                    <th>Organisasi</th>
                    <th>Supplier</th>
                    <th>Tanggal Disetujui</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingPOs as $po)
                    <tr>
                        <td>
                            <a href="{{ route('web.po.show', $po) }}" class="text-gray-900 text-hover-primary fw-bold fs-6">
                                {{ $po->po_number }}
                            </a>
                        </td>
                        <td>{{ $po->organization->name ?? '-' }}</td>
                        <td>{{ $po->supplier->name ?? '-' }}</td>
                        <td>{{ $po->approved_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('web.goods-receipts.create', ['purchase_order_id' => $po->id]) }}" 
                               class="btn btn-sm btn-light-success btn-active-success">
                                <i class="ki-outline ki-delivery fs-3 me-1"></i>
                                Terima Barang
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-10">
                            <x-empty-state icon="notification-on" title="Tidak Ada Antrian" message="Semua PO yang disetujui telah diproses penerimaannya." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        {{-- Standard GR Table --}}
        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
            <thead>
                <tr class="fw-bold text-muted">
                    <th>Nomor GR</th>
                    <th>Referensi PO</th>
                    <th>Supplier / Organisasi</th>
                    <th>Status</th>
                    <th>Tanggal / Penerima</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $receipt)
                    <tr>
                        <td>
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
                            <span class="badge badge-light-{{ $statusColor }} fw-bold">{{ strtoupper($receipt->status) }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-gray-800 fs-6">{{ $receipt->received_date->format('d/m/Y') }}</div>
                            <div class="text-muted fs-7 mt-1">
                                <i class="ki-outline ki-user fs-7 me-1"></i>
                                {{ $receipt->receivedBy?->name ?? '—' }}
                            </div>
                        </td>
                        <td class="text-end">
                            <x-table-action>
                                <x-table-action.item :href="route('web.goods-receipts.show', $receipt)" icon="eye" label="Lihat Detail" />
                                @if(($receipt->status instanceof \BackedEnum ? $receipt->status->value : $receipt->status) === 'partial')
                                    @can('create', \App\Models\GoodsReceipt::class)
                                        <x-table-action.item :href="route('web.goods-receipts.create', ['purchase_order_id' => $receipt->purchase_order_id])" icon="plus" label="Tambah Pengiriman" color="success" />
                                    @endcan
                                @endif
                                <x-table-action.divider />
                                <x-table-action.item :href="route('web.goods-receipts.pdf', $receipt)" icon="file-down" label="Download PDF" color="info" target="_blank" />
                            </x-table-action>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10">
                            <x-empty-state icon="package" title="Belum Ada Penerimaan" message="Data penerimaan barang akan muncul setelah proses konfirmasi penerimaan." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @php $currentItems = $currentTab === 'pending' ? $pendingPOs : $receipts; @endphp
    @if($currentItems->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $currentItems->links() }}
        </div>
    @endif
</x-index-layout>
