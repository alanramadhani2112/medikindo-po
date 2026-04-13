{{-- 
Conversion Metadata:
- Original: Tailwind CSS
- Converted: Bootstrap 5 + Metronic 8
- Date: 2024
- Category: Purchase Orders
- Validated: Pending
--}}
<x-layout title="Ubah PO" pageTitle="Ubah Purchase Order" breadcrumb="Form ubah draf pesanan pembelian">

    {{-- Alpine.js Component Script - Must be defined BEFORE Alpine initializes --}}
    @push('head-scripts')
    <script>
    // Define poForm globally BEFORE Alpine initializes
    document.addEventListener('alpine:init', () => {
        Alpine.data('poForm', () => ({
            supplierId: '{{ old('supplier_id', $purchaseOrder->supplier_id) }}',
            products: [],
            items: [],
            
            get total() {
                return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
            },
            
            init() {
                console.log('PO Form Edit initialized');
                this.loadProducts(false);

                @if(old('items'))
                    this.items = @json(old('items'));
                    this.items.forEach(item => this.calcSubtotal(item));
                @else
                    this.items = @json($purchaseOrder->items->map(fn($i) => [
                        'product_id' => $i->product_id,
                        'quantity'   => $i->quantity,
                        'unit_price' => (int)$i->unit_price,
                        'subtotal'   => (int)$i->subtotal
                    ]));
                @endif
                
                console.log('Loaded items:', this.items.length);
            },
            
            loadProducts(clearItems = true) {
                console.log('loadProducts called (edit), supplierId:', this.supplierId);
                
                if (!this.supplierId) {
                    this.products = [];
                    this.items = [];
                    return;
                }
                
                const select = document.querySelector('select[name="supplier_id"]');
                if (!select) {
                    console.error('Supplier select not found');
                    return;
                }
                
                const option = Array.from(select.options).find(o => o.value == this.supplierId);
                console.log('Selected option:', option);
                console.log('Option dataset.products:', option ? option.dataset.products : 'N/A');
                
                if (option) {
                    try {
                        this.products = option.dataset.products ? JSON.parse(option.dataset.products) : [];
                        console.log('Products loaded:', this.products.length);
                        console.log('Products:', this.products);
                    } catch(e) {
                        console.error('Error loading products:', e);
                        this.products = [];
                    }
                } else {
                    this.products = [];
                }

                if (clearItems) {
                    this.items = [];
                }
            },
            
            addItem() {
                console.log('Adding item...');
                if (!this.supplierId) {
                    alert('Silakan pilih supplier terlebih dahulu');
                    return;
                }
                if (this.products.length === 0) {
                    alert('Tidak ada produk tersedia untuk supplier ini');
                    return;
                }
                this.items.push({ 
                    product_id: '', 
                    product_name: '',
                    quantity: 1, 
                    unit_price: 0, 
                    subtotal: 0 
                });
                console.log('Item added. Total items:', this.items.length);
            },
            
            removeItem(index) {
                console.log('Removing item at index:', index);
                this.items.splice(index, 1);
            },
            
            onProductChange(index) {
                const item = this.items[index];
                
                if (!item.product_id) {
                    console.warn('No product selected');
                    return;
                }
                
                console.log('onProductChange called for index:', index);
                console.log('item.product_id:', item.product_id, 'type:', typeof item.product_id);
                console.log('Available products:', this.products);
                
                // Type-safe product lookup (product.id is number, item.product_id is string)
                const product = this.products.find(p => p.id === Number(item.product_id));
                console.log('Found product:', product);
                
                if (!product) {
                    console.error('Product not found for ID:', item.product_id);
                    return;
                }
                
                // Assign derived fields safely
                const sellingPrice = Number(product.selling_price) || Number(product.price) || 0;
                console.log('Product selling_price:', product.selling_price);
                console.log('Using price:', sellingPrice);
                
                item.unit_price = sellingPrice;
                item.product_name = product.name;
                this.calcSubtotal(index);
                
                console.log('Updated item.unit_price to:', item.unit_price);
                console.log('Item after update:', item);
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

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">Ubah Purchase Order #{{ $purchaseOrder->po_number }}</h1>
            <p class="text-gray-600 fs-6 mb-0">Hanya draf PO yang dapat diubah. Pastikan supplier dan produk sudah sesuai.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('web.po.update', $purchaseOrder) }}" id="po-form" x-data="poForm()" x-init="init()">
        @csrf
        @method('PUT')

        {{-- Organization Selection --}}
        <x-card title="Informasi Pemesanan" icon="information" class="card-flush mb-7">
            <div class="row g-5">
                @if(isset($organizations) && $organizations->isNotEmpty())
                    <div class="col-12">
                        <x-select name="organization_id" label="Organisasi / Fasilitas Kesehatan" required>
                            <option value="">— Pilih Organisasi —</option>
                            @foreach($organizations as $organization)
                                <option value="{{ $organization->id }}" {{ old('organization_id', $purchaseOrder->organization_id) == $organization->id ? 'selected' : '' }}>
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
                            <option value="{{ $supplier->id }}" 
                                    data-products="{{ json_encode($supplier->products) }}">
                                {{ $supplier->name }} ({{ $supplier->code }})
                            </option>
                        @endforeach
                    </x-select>
                    <div class="form-text text-gray-600">Item produk akan diload berdasarkan supplier terpilih</div>
                </div>
                <div class="col-md-6">
                    <x-input name="notes" label="Catatan Tambahan (Opsional)" value="{{ old('notes', $purchaseOrder->notes) }}" placeholder="Contoh: Kirim secepatnya..." />
                    <div class="form-text text-gray-600">Catatan internal untuk tim operasional</div>
                </div>
            </div>
        </x-card>

        {{-- Items --}}
        <x-card title="Daftar Entitas Produk Pembelian" icon="package" class="card-flush mb-7">
            <x-slot name="actions">
                <button type="button" 
                        class="btn btn-sm btn-primary d-flex align-items-center gap-2" 
                        @click="addItem()" 
                        :disabled="!supplierId"
                        :class="{ 'opacity-50': !supplierId }">
                    <i class="ki-outline ki-plus fs-3"></i>
                    <span class="fw-bold">Tambah Produk</span>
                </button>
                <div x-show="!supplierId" class="text-muted fs-8 mt-1">
                    Pilih supplier terlebih dahulu
                </div>
            </x-slot>

            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="min-w-200px">Deskripsi SKU / Alat Medis</th>
                            <th class="min-w-140px">Kuantitas</th>
                            <th class="min-w-200px">Unit Price (Rp)</th>
                            <th class="min-w-150px text-end">Subtotal</th>
                            <th class="min-w-80px text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="items.length === 0">
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-package fs-3x text-gray-400 mb-3"></i>
                                        <h6 class="text-gray-800 fw-semibold fs-6 mb-1">Daftar Item Kosong</h6>
                                        <p class="text-gray-600 fs-7 mb-0">Pilih supplier utama, kemudian tekan "Tambah Produk".</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-5 py-4">
                                    <select x-bind:name="'items[' + index + '][product_id]'" 
                                              x-model="item.product_id"
                                              @change="onProductChange(index)"
                                              class="form-select form-select-solid">
                                        <option value="">— Pilih Produk —</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option x-bind:value="p.id" 
                                                    x-bind:data-price="p.selling_price || p.price || 0" 
                                                    x-bind:selected="p.id == item.product_id"
                                                    x-text="p.name + ' (' + p.sku + ')'"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="px-5 py-4">
                                    <input type="number" 
                                             x-bind:name="'items[' + index + '][quantity]'" 
                                             x-bind:value="item.quantity"
                                             @input="updateQuantity(index, $event.target.value)"
                                             min="1"
                                             class="form-control form-control-solid" />
                                </td>
                                <td class="px-5 py-4">
                                    <input type="number" 
                                             x-bind:name="'items[' + index + '][unit_price]'" 
                                             x-bind:value="item.unit_price"
                                             readonly
                                             class="form-control form-control-solid bg-light" 
                                             style="cursor: not-allowed;" />
                                    <div class="form-text text-muted fs-8 mt-1">Harga otomatis dari master produk</div>
                                </td>
                                <td class="px-5 py-4 text-end align-middle">
                                    <span class="text-gray-900 fw-bold fs-6" x-text="formatRupiah(item.subtotal)"></span>
                                </td>
                                <td class="px-5 py-4 text-center align-middle">
                                    <button type="button" @click="removeItem(index)"
                                            class="btn btn-sm btn-icon btn-light-danger">
                                        <i class="ki-outline ki-trash fs-3"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        
                        <tr x-show="items.length > 0" class="bg-light">
                            <td colspan="3" class="px-5 py-5 text-end">
                                <span class="text-gray-800 fw-semibold fs-5 me-4">Kalkulasi Total Pembayaran</span>
                            </td>
                            <td class="px-5 py-5 text-end align-middle">
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
            <x-button variant="secondary" outline size="lg" href="{{ route('web.po.show', $purchaseOrder) }}">
                Batalkan Perubahan
            </x-button>
            <x-button type="submit" variant="primary" size="lg">
                Simpan Perubahan
            </x-button>
        </div>

    </form>

</x-layout>

