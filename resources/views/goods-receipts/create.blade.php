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
        <form method="POST" action="{{ route('web.goods-receipts.store') }}" id="gr-form" enctype="multipart/form-data">
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
                                        <div class="d-flex flex-wrap gap-4 mt-2">
                                            <span class="fs-7 text-muted" x-html="`<strong>Total Pesanan:</strong> ${item.quantity} ${item.product.unit || 'unit'}`"></span>
                                            <span class="fs-7 text-warning" x-show="item.already_received > 0" x-html="`<strong>Sudah Diterima:</strong> ${item.already_received} ${item.product.unit || 'unit'}`"></span>
                                            <span class="fs-7 text-success" x-html="`<strong>Sisa untuk Diterima:</strong> ${item.remaining} ${item.product.unit || 'unit'}`"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label required fw-semibold fs-7 mb-2">Jumlah Diterima</label>
                                        <input type="number" class="form-control bg-white" 
                                               :name="'items[' + index + '][quantity_received]'" 
                                               required 
                                               :min="1" 
                                               :max="item.remaining" 
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

                {{-- Foto Bukti Penerimaan --}}
                <x-card title="Bukti Foto Penerimaan" class="mb-5">
                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">
                                <i class="ki-outline ki-picture fs-4 me-1 text-primary"></i>
                                Foto Barang yang Diterima
                            </label>
                            <input type="file"
                                   name="delivery_photo"
                                   id="delivery_photo"
                                   class="form-control form-control-solid @error('delivery_photo') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/webp"
                                   required
                                   onchange="previewPhoto(this)">
                            @error('delivery_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">
                                Format: JPG, PNG, WebP. Maks 5MB. Foto harus menampilkan barang yang diterima beserta surat jalan.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="photo-preview-wrapper" class="d-none">
                                <label class="form-label fw-semibold fs-7 mb-2 text-muted">Preview</label>
                                <img id="photo-preview" src="" alt="Preview foto" 
                                     class="rounded border border-gray-300" 
                                     style="max-height: 200px; max-width: 100%; object-fit: contain;">
                            </div>
                        </div>
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
            oldItems: [],

            initData(pos) {
                this.pos = pos;
                
                // Ambil old() input data jika ada validasi gagal
                const oldItemsRaw = {!! json_encode(old('items', [])) !!};
                this.oldItems = Array.isArray(oldItemsRaw) ? oldItemsRaw : Object.values(oldItemsRaw);
                
                const urlParams = new URLSearchParams(window.location.search);
                const preselectedId = urlParams.get('purchase_order_id') || '{{ old('purchase_order_id') }}';
                
                if (preselectedId) {
                    const po = this.pos.find(p => p.id == preselectedId);
                    if (po) {
                        this.selectPo(po);
                    }
                }
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
                
                // Map items with default received qty or old inputs if validation failed
                this.items = po.items.map((item, index) => {
                    const oldData = this.oldItems ? this.oldItems[index] : null;
                    return {
                        ...item,
                        quantity_received: oldData ? oldData.quantity_received : item.remaining,
                        batch_no: oldData ? oldData.batch_no : '',
                        expiry_date: oldData ? oldData.expiry_date : '',
                        condition: oldData ? oldData.condition : 'Baik',
                        notes: oldData ? oldData.notes : ''
                    };
                });
            }
        };
    }

    // Form validation before submit
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('gr-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const deliveryOrderNumber = document.getElementById('delivery_order_number');
                const photo = document.getElementById('delivery_photo');

                if (!deliveryOrderNumber || !deliveryOrderNumber.value.trim()) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: 'Nomor Surat Jalan (DO) wajib diisi!', confirmButtonText: 'OK' });
                    deliveryOrderNumber && deliveryOrderNumber.focus();
                    return false;
                }

                if (!photo || !photo.files || photo.files.length === 0) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: 'Foto bukti penerimaan barang wajib diupload!', confirmButtonText: 'OK' });
                    photo && photo.focus();
                    return false;
                }
            });
        }
    });

    function previewPhoto(input) {
        const wrapper = document.getElementById('photo-preview-wrapper');
        const preview = document.getElementById('photo-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                wrapper.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            wrapper.classList.add('d-none');
        }
    }
    </script>
    @endpush
</x-layout>
