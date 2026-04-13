<x-layout title="Rekam Penerimaan" pageTitle="Rekam Penerimaan Barang" breadcrumb="Sistem akan mencatat kedatangan fisik logistik">

    <x-page-header 
        title="Rekam Penerimaan Barang" 
        description="Sistem akan mencatat kedatangan fisik logistik dan mengupdate status pesanan.">
    </x-page-header>

    <div x-data="grForm()">
        <form method="POST" action="{{ route('web.goods-receipts.store') }}" id="gr-form">
            @csrf

            {{-- PO Selection --}}
            <x-card title="Pilih Purchase Order" class="mb-5">
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label required fw-semibold fs-6 mb-2">Purchase Order Terotorisasi</label>
                        <select name="purchase_order_id" class="form-select form-select-solid" required 
                                x-model="selectedPoId" @change="loadItems()">
                            <option value="">— Pilih PO yang sudah berstatus Approved / Sent —</option>
                            @foreach($pos as $po)
                                <option value="{{ $po->id }}" data-items="{{ json_encode($po->items) }}">
                                    {{ $po->po_number }} - {{ $po->supplier?->name }} (Pesan: {{ $po->items->sum('quantity') }} items)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-card>

            {{-- Items --}}
            <div x-show="selectedPoId" x-transition>
                <x-card title="Detail Fisik yang Diterima" class="mb-5">
                    <div class="d-flex flex-column gap-5">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="border border-gray-300 rounded bg-light p-5">
                                <input type="hidden" :name="`items[${index}][purchase_order_item_id]`" :value="item.id">
                                
                                <div class="row g-5 align-items-end">
                                    <div class="col-lg-6">
                                        <h6 class="fs-6 fw-bold text-primary mb-1" x-text="item.product.name"></h6>
                                        <span class="fs-7 text-muted" x-text="`Jumlah Dipesan: ${item.quantity} ${item.product.unit}`"></span>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label required fw-semibold fs-7 mb-2">Jumlah Diterima</label>
                                        <input type="number" class="form-control form-control-solid" 
                                               :name="'items[' + index + '][quantity_received]'" 
                                               required 
                                               :min="1" 
                                               :max="item.quantity" 
                                               x-model.number="item.quantity_received">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label fw-semibold fs-7 mb-2">Kondisi Barang</label>
                                        <select class="form-select form-select-solid" :name="'items[' + index + '][condition]'">
                                            <option value="Good">Baik Sempurna</option>
                                            <option value="Minor Damage">Rusak Ringan</option>
                                            <option value="Damaged">Rusak Parah</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold fs-7 mb-2">Catatan Kondisi (Opsional)</label>
                                        <input type="text" class="form-control form-control-solid" 
                                               :name="'items[' + index + '][notes]'" 
                                               placeholder="Misal: Dus penyok sedikit...">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-card>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('web.goods-receipts.index') }}" class="btn btn-light-secondary">
                        <i class="ki-outline ki-cross fs-3"></i>
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-check fs-3"></i>
                        Konfirmasi Penerimaan Barang
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function grForm() {
        return {
            selectedPoId: '',
            items: [],
            loadItems() {
                if (!this.selectedPoId) {
                    this.items = [];
                    return;
                }
                const select = document.querySelector(`select[name="purchase_order_id"]`);
                const option = select.options[select.selectedIndex];
                try {
                    let rawItems = option.dataset.items ? JSON.parse(option.dataset.items) : [];
                    this.items = rawItems.map(i => ({...i, quantity_received: i.quantity}));
                } catch(e) {
                    this.items = [];
                }
            }
        };
    }
    </script>
    @endpush
</x-layout>
