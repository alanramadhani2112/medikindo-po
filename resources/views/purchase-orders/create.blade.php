<x-layout title="Buat PO" pageTitle="Buat Purchase Order" breadcrumb="Isi form untuk membuat pesanan pembelian baru">

    @push('head-scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('poForm', () => ({
            supplierId: '{{ old('supplier_id', '') }}',
            products: [],
            items: [],

            // Product search state per row
            searchQuery: {},
            showDropdown: {},

            get total() {
                return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
            },

            init() {
                @if(old('items'))
                    this.items = @json(old('items'));
                    if (this.supplierId) {
                        this.$nextTick(() => this.loadProducts(true));
                    }
                @endif
            },

            loadProducts(isInit = false) {
                if (!this.supplierId) {
                    this.products = [];
                    if (!isInit) this.items = [];
                    return;
                }
                const select = document.querySelector('select[name="supplier_id"]');
                const option = select ? Array.from(select.options).find(o => o.value == this.supplierId) : null;
                try {
                    this.products = option && option.dataset.products ? JSON.parse(option.dataset.products) : [];
                } catch(e) {
                    this.products = [];
                }
                if (!isInit) this.items = [];
            },

            addItem() {
                if (!this.supplierId) {
                    alert('Silakan pilih supplier terlebih dahulu');
                    return;
                }
                const idx = this.items.length;
                this.items.push({ product_id: '', product_name: '', product_sku: '', quantity: 1, unit_price: 0, subtotal: 0 });
                this.searchQuery[idx] = '';
                this.showDropdown[idx] = false;
            },

            removeItem(index) {
                this.items.splice(index, 1);
                // Re-index search state
                const newSearch = {};
                const newDropdown = {};
                this.items.forEach((_, i) => {
                    newSearch[i] = this.searchQuery[i < index ? i : i + 1] || '';
                    newDropdown[i] = false;
                });
                this.searchQuery = newSearch;
                this.showDropdown = newDropdown;
            },

            filteredProducts(index) {
                const q = (this.searchQuery[index] || '').toLowerCase();
                if (!q) return this.products.slice(0, 50);
                return this.products.filter(p =>
                    p.name.toLowerCase().includes(q) ||
                    (p.sku && p.sku.toLowerCase().includes(q))
                ).slice(0, 50);
            },

            selectProduct(index, product) {
                const item = this.items[index];
                item.product_id   = product.id;
                item.product_name = product.name;
                item.product_sku  = product.sku || '';
                item.unit_price   = Number(product.cost_price) || Number(product.price) || 0;
                this.searchQuery[index]  = product.name + (product.sku ? ' (' + product.sku + ')' : '');
                this.showDropdown[index] = false;
                this.calcSubtotal(index);
            },

            openSearch(index) {
                this.showDropdown[index] = true;
                this.$nextTick(() => {
                    const input = document.querySelector(`[data-search-input="${index}"]`);
                    const dropdown = document.querySelector(`[data-search-dropdown="${index}"]`);
                    if (input && dropdown) {
                        const rect = input.getBoundingClientRect();
                        dropdown.style.top   = rect.bottom + 'px';
                        dropdown.style.left  = rect.left + 'px';
                        dropdown.style.width = rect.width + 'px';
                    }
                });
            },

            closeSearch(index) {
                // Delay to allow click on dropdown item
                setTimeout(() => { this.showDropdown[index] = false; }, 200);
            },

            calcSubtotal(index) {
                const item = this.items[index];
                item.subtotal = (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
            },

            updateQuantity(index, value) {
                this.items[index].quantity = parseInt(value) || 1;
                this.calcSubtotal(index);
            },

            formatRupiah(value) {
                return 'Rp ' + (value || 0).toLocaleString('id-ID');
            }
        }));
    });
    </script>
    @endpush

    @push('styles')
    <style>
    .product-search-wrapper { position: relative; }
    .product-dropdown {
        position: fixed;
        z-index: 9999;
        background: #fff;
        border: 1px solid #e4e6ef;
        border-radius: 0.475rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.12);
        max-height: 220px;
        overflow-y: auto;
        min-width: 280px;
    }
    .product-dropdown-item {
        padding: 0.6rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f5f5f5;
        transition: background .15s;
    }
    .product-dropdown-item:last-child { border-bottom: none; }
    .product-dropdown-item:hover { background: #f1f3f9; }
    .product-dropdown-empty { padding: 0.75rem 1rem; color: #a1a5b7; font-size: 0.85rem; }
    </style>
    @endpush

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">Buat Purchase Order Baru</h1>
            <p class="text-gray-600 fs-6 mb-0">Mohon lengkapi informasi organisasi, pemasok, dan item produk secara cermat.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('web.po.store') }}" id="po-form" x-data="poForm()" x-init="init()">
        @csrf

        {{-- Informasi Pemesanan --}}
        <x-card title="Informasi Pemesanan" icon="information" class="card-flush mb-7">
            <div class="row g-5">
                @if(isset($organizations) && $organizations->isNotEmpty())
                    <div class="col-12">
                        <x-select name="organization_id" label="Organisasi / Fasilitas Kesehatan" required>
                            <option value="">— Pilih Organisasi —</option>
                            @foreach($organizations as $organization)
                                <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>
                                    {{ $organization->name }} ({{ ucfirst($organization->type) }})
                                </option>
                            @endforeach
                        </x-select>
                        <div class="form-text text-gray-600">Pilih klinik atau faskes tujuan pengadaan</div>
                    </div>
                @endif
                <div class="col-md-6">
                    <x-select name="supplier_id" label="Supplier Pemenuhan Item" required x-model="supplierId" @change="loadProducts()">
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" data-products="{{ json_encode($supplier->products) }}">
                                {{ $supplier->name }} ({{ $supplier->code }})
                            </option>
                        @endforeach
                    </x-select>
                    <div class="form-text text-gray-600">Item produk akan diload berdasarkan supplier terpilih</div>
                </div>
                <div class="col-md-6">
                    <x-input name="notes" label="Catatan Tambahan (Opsional)" value="{{ old('notes') }}" placeholder="Contoh: Kirim secepatnya..." />
                    <div class="form-text text-gray-600">Catatan internal untuk tim operasional</div>
                </div>
            </div>
        </x-card>

        {{-- Daftar Produk --}}
        <x-card title="Daftar Item Produk" icon="package" class="card-flush mb-7">
            <x-slot name="actions">
                <button type="button"
                        class="btn btn-sm btn-primary d-flex align-items-center gap-2"
                        @click="addItem()"
                        :disabled="!supplierId"
                        :class="{ 'opacity-50': !supplierId }">
                    <i class="ki-outline ki-plus fs-3"></i>
                    <span class="fw-bold">Tambah Produk</span>
                </button>
                <div x-show="!supplierId" class="text-muted fs-8 mt-1">Pilih supplier terlebih dahulu</div>
            </x-slot>

            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="min-w-250px">Produk</th>
                            <th class="min-w-120px">Kuantitas</th>
                            <th class="min-w-160px">Harga Satuan (Rp)</th>
                            <th class="min-w-140px text-end">Subtotal</th>
                            <th class="min-w-60px text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="items.length === 0">
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <h6 class="text-gray-800 fw-semibold fs-6 mb-1">Daftar Item Kosong</h6>
                                        <p class="text-gray-600 fs-7 mb-0">Pilih supplier, kemudian tekan "Tambah Produk".</p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                {{-- Product Search --}}
                                <td class="px-4 py-3">
                                    <div class="product-search-wrapper">
                                        {{-- Hidden input for form submission --}}
                                        <input type="hidden" x-bind:name="'items[' + index + '][product_id]'" x-bind:value="item.product_id">

                                        {{-- Search input --}}
                                        <div class="position-relative">
                                            <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                                            <input type="text"
                                                   class="form-control form-control-solid ps-12"
                                                   placeholder="Ketik nama atau SKU produk..."
                                                   x-model="searchQuery[index]"
                                                   x-bind:data-search-input="index"
                                                   @focus="openSearch(index)"
                                                   @blur="closeSearch(index)"
                                                   @input="item.product_id = ''; item.product_name = ''"
                                                   autocomplete="off">
                                        </div>

                                        {{-- Dropdown hasil search --}}
                                        <div class="product-dropdown"
                                             x-show="showDropdown[index]"
                                             x-bind:data-search-dropdown="index"
                                             x-cloak>
                                            <template x-if="filteredProducts(index).length === 0">
                                                <div class="product-dropdown-empty">
                                                    <i class="ki-outline ki-information-5 me-1"></i>
                                                    Produk tidak ditemukan
                                                </div>
                                            </template>
                                            <template x-for="p in filteredProducts(index)" :key="p.id">
                                                <div class="product-dropdown-item"
                                                     @mousedown.prevent="selectProduct(index, p)">
                                                    <div class="fw-bold text-gray-800 fs-7" x-text="p.name"></div>
                                                    <div class="text-muted fs-8">
                                                        <span x-text="'SKU: ' + (p.sku || '-')"></span>
                                                        <span class="ms-3 text-primary fw-semibold" x-text="'Rp ' + (Number(p.cost_price || p.price || 0)).toLocaleString('id-ID')"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    {{-- Tampilkan produk terpilih --}}
                                    <div x-show="item.product_id" class="mt-1">
                                        <span class="badge badge-light-primary fs-8" x-text="item.product_sku ? 'SKU: ' + item.product_sku : ''"></span>
                                    </div>
                                    <div x-show="!item.product_id && searchQuery[index]" class="text-danger fs-8 mt-1">
                                        Pilih produk dari daftar
                                    </div>
                                </td>

                                {{-- Quantity --}}
                                <td class="px-4 py-3">
                                    <input type="number"
                                           x-bind:name="'items[' + index + '][quantity]'"
                                           x-bind:value="item.quantity"
                                           @input="updateQuantity(index, $event.target.value)"
                                           min="1"
                                           class="form-control form-control-solid" />
                                </td>

                                {{-- Unit Price --}}
                                <td class="px-4 py-3">
                                    <input type="number"
                                           x-bind:name="'items[' + index + '][unit_price]'"
                                           x-bind:value="item.unit_price"
                                           readonly
                                           class="form-control form-control-solid bg-light"
                                           style="cursor: not-allowed;" />
                                    <div class="form-text text-muted fs-8 mt-1">Otomatis dari master produk</div>
                                </td>

                                {{-- Subtotal --}}
                                <td class="px-4 py-3 text-end align-middle">
                                    <span class="text-gray-900 fw-bold fs-6" x-text="formatRupiah(item.subtotal)"></span>
                                </td>

                                {{-- Remove --}}
                                <td class="px-4 py-3 text-center align-middle">
                                    <button type="button" @click="removeItem(index)"
                                            class="btn btn-sm btn-icon btn-light-danger">
                                        <i class="ki-outline ki-trash fs-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="items.length > 0" class="bg-light">
                            <td colspan="3" class="px-4 py-4 text-end">
                                <span class="text-gray-800 fw-semibold fs-5">Total Nilai PO</span>
                            </td>
                            <td class="px-4 py-4 text-end align-middle">
                                <span class="text-primary fw-bold fs-4" x-text="formatRupiah(total)"></span>
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Submit --}}
        <div class="d-flex align-items-center justify-content-end gap-3 pt-4">
            <x-button variant="secondary" outline size="lg" href="{{ route('web.po.index') }}">
                Batal
            </x-button>
            <x-button type="submit" variant="primary" size="lg" class="create-confirm" data-type="Purchase Order">
                Simpan sebagai Draft
            </x-button>
        </div>

    </form>

</x-layout>
