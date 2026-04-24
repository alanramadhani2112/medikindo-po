<x-layout :title="'Stok Produk: ' . $product->name" :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.index')], ['label' => $product->name]]">
    <x-page-header :title="$product->name">
        <x-slot name="actions">
            <a href="{{ route('inventory.index') }}" class="btn btn-light me-3">
                <i class="ki-outline ki-arrow-left fs-3"></i> Kembali
            </a>
            <div class="d-flex align-items-center">
                <span class="text-gray-600 fw-bold me-2">Total Available:</span>
                <span class="badge badge-success fs-3 fw-bold">{{ number_format($totalAvailable, 0) }} unit</span>
            </div>
        </x-slot>
    </x-page-header>

    <div class="row g-5">
        {{-- BATCH LIST --}}
        <div class="col-xl-4">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold">Detail Per Batch</h3>
                </div>
                <div class="card-body">
                    @foreach($inventoryItems as $item)
                        <div class="p-5 border border-dashed border-gray-300 rounded mb-5 {{ $item->isLowStock() ? 'bg-light-danger' : '' }}">
                            <div class="d-flex flex-stack mb-3">
                                <span class="badge badge-light-dark fw-bold">BATCH: {{ $item->batch_no }}</span>
                                @if($item->expiry_date)
                                    <span class="fs-7 fw-bold {{ $item->isExpired() ? 'text-danger' : 'text-gray-600' }}">
                                        EXP: {{ $item->expiry_date->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex flex-stack">
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bold fs-4">{{ number_format($item->quantity_available, 0) }}</span>
                                    <span class="text-muted fs-8">Tersedia</span>
                                </div>
                                <div class="d-flex flex-column text-end">
                                    <span class="text-gray-600 fw-semibold fs-7">{{ number_format($item->quantity_on_hand, 0) }}</span>
                                    <span class="text-muted fs-8">Total On Hand</span>
                                </div>
                                <div class="text-end">
                                    <a href="{{ route('inventory.adjust-form', $item) }}" class="btn btn-icon btn-sm btn-light-warning">
                                        <i class="ki-outline ki-pencil fs-4"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- MOVEMENT HISTORY --}}
        <div class="col-xl-8">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold">Riwayat Pergerakan Stok</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th>Tanggal</th>
                                    <th>Batch</th>
                                    <th>Tipe</th>
                                    <th class="text-end">Qty</th>
                                    <th>Referesi</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventoryItems->flatMap->movements->sortByDesc('created_at') as $move)
                                    <tr>
                                        <td>
                                            <span class="text-gray-700 fs-7 fw-semibold">{{ $move->created_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light fs-8">{{ $move->inventoryItem->batch_no }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $typeColor = match($move->type) {
                                                    'in' => 'success',
                                                    'out' => 'danger',
                                                    'adjustment' => 'warning',
                                                    default => 'primary'
                                                };
                                            @endphp
                                            <span class="badge badge-light-{{ $typeColor }} fw-bold">{{ strtoupper($move->type) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-{{ $move->type === 'in' ? 'success' : 'danger' }}">
                                                {{ $move->type === 'in' ? '+' : '-' }}{{ number_format($move->quantity, 0) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-gray-600 fs-7">{{ $move->reference_type }}: {{ $move->reference_id }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fs-7 fw-semibold">{{ $move->creator?->name ?? 'System' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
