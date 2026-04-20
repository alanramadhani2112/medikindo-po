<x-layout title="Rekam Penerimaan" pageTitle="Rekam Penerimaan Barang" breadcrumb="Sistem akan mencatat kedatangan fisik logistik">

    <x-page-header 
        title="Rekam Penerimaan Barang" 
        description="Sistem akan mencatat kedatangan fisik logistik dan mengupdate status pesanan.">
    </x-page-header>

    @push('styles')
    <style>
    .po-search-wrapper { position: relative; }
    .po-dropdown {
        position: absolute;
        z-index: 1050;
        background: #fff;
        border: 1px solid #e4e6ef;
        border-radius: 0.475rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.1);
        max-height: 250px;
        overflow-y: auto;
        width: 100%;
        margin-top: 2px;
    }
    .po-dropdown-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f5f5f5;
        transition: background .15s;
    }
    .po-dropdown-item:last-child { border-bottom: none; }
    .po-dropdown-item:hover { background: #f1f3f9; }
    .po-dropdown-empty { padding: 0.75rem 1rem; color: #a1a5b7; font-size: 0.85rem; }
    </style>
    @endpush

    <div x-data="grForm()" x-init='initData(@json($pos))'>
        <form method="POST" action="{{ route('web.goods-receipts.store') }}" id="gr-form">
            @csrf

            {{-- PO Selection --}}
            <x-card title="Pilih Purchase Order" class="mb-5">
                <div class="row g-5">
                    <div class="col-md-6">
                        <label class="form-label required fw-semibold fs-6 mb-2">Purchase Order Terotorisasi</label>
                        <div class="po-search-wrapper">
                            <input type="hidden" name="purchase_order_id" x-model="selectedPoId">
                            <div class="position-relative">
                                <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                                <input type="text"
                                       class="form-control form-control-solid ps-12"
                                       placeholder="Ketik Nomor PO atau Nama Supplier..."
                                       x-model="searchQuery"
                                       @focus="showDropdown = true"
                                       @blur="setTimeout(() => showDropdown = false, 200)"
                                       @input="selectedPoId = ''; items = []">
                            </div>

                            {{-- Dropdown --}}
                            <div class="po-dropdown" x-show="showDropdown && filteredPos().length > 0" x-cloak>
                                <template x-for="po in filteredPos()" :key="po.id">
                                    <div class="po-dropdown-item" @mousedown.prevent="selectPo(po)">
                                        <div class="fw-bold text-gray-800 fs-7" x-text="po.po_number"></div>
                                        <div class="text-muted fs-8">
                                            <span x-text="po.supplier.name"></span>
                                            <span class="ms-2 badge badge-light-primary fs-9" x-text="po.items.length + ' item'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="po-dropdown" x-show="showDropdown && searchQuery && filteredPos().length === 0" x-cloak>
                                <div class="po-dropdown-empty">Data PO tidak ditemukan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required fw-semibold fs-6 mb-2">Nomor Surat Jalan (DO)</label>
                        <input type="text" 
                               name="delivery_order_number" 
                               id="delivery_order_number"
                               class="form-control form-control-solid @error('delivery_order_number') is-invalid @enderror" 
                               required 
                               placeholder="Contoh: DO/2026/001"
                               value="{{ old('delivery_order_number') }}">
                        @error('delivery_order_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Nomor surat jalan dari supplier (wajib diisi)</div>
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
                                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                                
                                <div class="row g-5 align-items-end">
                                    <div class="col-lg-12">
                                        <h6 class="fs-6 fw-bold text-primary mb-1" x-text="item.product.name"></h6>
                                        <span class="fs-7 text-muted" x-text="`Jumlah Dipesan: ${item.quantity} ${item.product.unit || 'unit'}`"></span>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label required fw-semibold fs-7 mb-2">Jumlah Diterima</label>
                                        <input type="number" class="form-control bg-white" 
                                               :name="'items[' + index + '][quantity_received]'" 
                                               required 
                                               :min="1" 
                                               :max="item.quantity" 
                                               x-model.number="item.quantity_received">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label required fw-semibold fs-7 mb-2">Nomor Batch</label>
                                        <input type="text" class="form-control bg-white" 
                                               :name="'items[' + index + '][batch_no]'" required placeholder="Contoh: B-123">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label required fw-semibold fs-7 mb-2">Tgl Kadaluarsa</label>
                                        <input type="date" class="form-control bg-white" 
                                               :name="'items[' + index + '][expiry_date]'" required>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label fw-semibold fs-7 mb-2">Kondisi Barang</label>
                                        <select class="form-select bg-white" :name="'items[' + index + '][condition]'">
                                            <option value="Good">Baik Sempurna</option>
                                            <option value="Minor Damage">Rusak Ringan</option>
                                            <option value="Damaged">Rusak Parah</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold fs-7 mb-2">Catatan Kondisi (Opsional)</label>
                                        <input type="text" class="form-control bg-white" 
                                               :name="'items[' + index + '][notes]'" 
                                               placeholder="Misal: Dus penyok sedikit...">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-card>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-3 pt-5">
                    <a href="{{ route('web.goods-receipts.index') }}" class="btn btn-light">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary create-confirm" data-type="Penerimaan Barang">
                        <i class="ki-outline ki-check-circle fs-3"></i>
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
            pos: [],
            selectedPoId: '',
            searchQuery: '',
            showDropdown: false,
            items: [],

            initData(pos) {
                this.pos = pos;
            },

            filteredPos() {
                if (!this.searchQuery) return this.pos;
                const q = this.searchQuery.toLowerCase();
                return this.pos.filter(po => 
                    po.po_number.toLowerCase().includes(q) || 
                    po.supplier.name.toLowerCase().includes(q)
                );
            },

            selectPo(po) {
                this.selectedPoId = po.id;
                this.searchQuery = po.po_number;
                this.showDropdown = false;
                
                // Map items with default received qty
                this.items = po.items.map(item => ({
                    ...item,
                    quantity_received: item.quantity
                }));
            }
        };
    }

    // Form validation before submit
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('gr-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const deliveryOrderNumber = document.getElementById('delivery_order_number');
                
                if (!deliveryOrderNumber || !deliveryOrderNumber.value.trim()) {
                    e.preventDefault();
                    
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Nomor Surat Jalan (DO) wajib diisi!',
                        confirmButtonText: 'OK'
                    });
                    
                    // Focus on the field
                    if (deliveryOrderNumber) {
                        deliveryOrderNumber.focus();
                        deliveryOrderNumber.classList.add('is-invalid');
                    }
                    
                    return false;
                }
            });
        }
    });
    
    </script>
    @endpush
</x-layout>
