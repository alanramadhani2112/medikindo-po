<x-layout :title="'Adjustment Stok: ' . $inventoryItem->product->name" :breadcrumbs="[['label' => 'Inventory', 'url' => route('web.inventory.index')], ['label' => 'Adjustment']]">
    <x-page-header :title="'Adjustment Stok: ' . $inventoryItem->product->name">
        <x-slot name="actions">
            <a href="{{ route('web.inventory.show', $inventoryItem->product_id) }}" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-3"></i> Batal
            </a>
        </x-slot>
    </x-page-header>

    <div class="row g-5">
        <div class="col-lg-6">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Detail Batch</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-stack mb-5">
                        <span class="text-gray-500 fw-bold">Batch No:</span>
                        <span class="badge badge-light-dark fw-bold fs-6">{{ $inventoryItem->batch_no }}</span>
                    </div>
                    <div class="d-flex flex-stack mb-5">
                        <span class="text-gray-500 fw-bold">Tanggal Kadaluarsa:</span>
                        <span class="text-gray-800 fw-bold fs-6">{{ $inventoryItem->expiry_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                    <div class="separator separator-dashed my-5"></div>
                    <div class="d-flex flex-stack mb-5">
                        <span class="text-gray-500 fw-bold fs-4">Stok Saat Ini (On Hand):</span>
                        <span class="text-gray-900 fw-bold fs-2hx">{{ number_format($inventoryItem->quantity_on_hand, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Form Penyesuaian</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('web.inventory.adjust', $inventoryItem) }}" method="POST">
                        @csrf
                        <div class="mb-8">
                            <label class="form-label required fw-bold">Quantity Penyesuaian Baru</label>
                            <input type="number" name="quantity" class="form-control form-control-solid" value="{{ $inventoryItem->quantity_on_hand }}" required>
                            <div class="text-muted fs-7 mt-2">Masukkan jumlah TOTAL stok yang benar setelah penyesuaian.</div>
                        </div>

                        <div class="mb-8">
                            <label class="form-label required fw-bold">Alasan Penyesuaian</label>
                            <textarea name="reason" class="form-control form-control-solid" rows="3" required placeholder="Contoh: Koreksi stok opname, Barang rusak, dsb..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-check fs-3"></i> Simpan Penyesuaian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
