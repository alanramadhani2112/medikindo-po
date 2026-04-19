<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Ubah PO','pageTitle' => 'Ubah Purchase Order','breadcrumb' => 'Form ubah draf pesanan pembelian']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Ubah PO','pageTitle' => 'Ubah Purchase Order','breadcrumb' => 'Form ubah draf pesanan pembelian']); ?>

    <?php $__env->startPush('head-scripts'); ?>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('poForm', () => ({
            supplierId: '<?php echo e(old('supplier_id', $purchaseOrder->supplier_id)); ?>',
            products: [],
            items: [],

            // Product search state per row
            searchQuery: {},
            showDropdown: {},

            get total() {
                return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
            },

            init() {
                // Wait for DOM to be ready before reading supplier select options
                this.$nextTick(() => {
                    this.loadProducts(false);

                    <?php if(old('items')): ?>
                        this.items = <?php echo json_encode(old('items'), 15, 512) ?>;
                        this.items.forEach((item, i) => {
                            this.calcSubtotal(i);
                            this.searchQuery[i] = item.product_name || '';
                            this.showDropdown[i] = false;
                        });
                    <?php else: ?>
                        this.items = <?php echo json_encode($poItems, 15, 512) ?>;
                        this.items.forEach((item, i) => {
                            this.searchQuery[i] = item.product_name + (item.product_sku ? ' (' + item.product_sku + ')' : '');
                            this.showDropdown[i] = false;
                        });
                    <?php endif; ?>
                });
            },

            loadProducts(clearItems = true) {
                if (!this.supplierId) {
                    this.products = [];
                    if (clearItems) this.items = [];
                    return;
                }
                const select = document.querySelector('select[name="supplier_id"]');
                const option = select ? Array.from(select.options).find(o => o.value == this.supplierId) : null;
                try {
                    this.products = option && option.dataset.products ? JSON.parse(option.dataset.products) : [];
                } catch(e) {
                    this.products = [];
                }
                if (clearItems) {
                    this.items = [];
                    this.searchQuery = {};
                    this.showDropdown = {};
                }
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
                return this.products.filter(p => {
                    const fullName = p.name.toLowerCase();
                    const fullSku = (p.sku || '').toLowerCase();
                    const combined = fullName + (fullSku ? ' (' + fullSku + ')' : '');
                    return fullName.includes(q) || fullSku.includes(q) || combined.includes(q);
                }).slice(0, 50);
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
                        // Use fixed positioning relative to viewport (no scroll offset needed)
                        dropdown.style.top   = rect.bottom + 'px';
                        dropdown.style.left  = rect.left + 'px';
                        dropdown.style.width = rect.width + 'px';
                    }
                });
            },

            closeSearch(index) {
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
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('styles'); ?>
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
    <?php $__env->stopPush(); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">Ubah Purchase Order #<?php echo e($purchaseOrder->po_number); ?></h1>
            <p class="text-gray-600 fs-6 mb-0">Hanya PO berstatus Draft yang dapat diubah.</p>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('web.po.update', $purchaseOrder)); ?>" id="po-form" x-data="poForm()" x-init="init()">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Informasi Pemesanan','icon' => 'information','class' => 'card-flush mb-7']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Informasi Pemesanan','icon' => 'information','class' => 'card-flush mb-7']); ?>
            <div class="row g-5">
                <?php if(isset($organizations) && $organizations->isNotEmpty()): ?>
                    <div class="col-12">
                        <?php if (isset($component)) { $__componentOriginaled2cde6083938c436304f332ba96bb7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaled2cde6083938c436304f332ba96bb7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.select','data' => ['name' => 'organization_id','label' => 'Organisasi / Fasilitas Kesehatan','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'organization_id','label' => 'Organisasi / Fasilitas Kesehatan','required' => true]); ?>
                            <option value="">— Pilih Organisasi —</option>
                            <?php $__currentLoopData = $organizations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organization): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($organization->id); ?>" <?php echo e(old('organization_id', $purchaseOrder->organization_id) == $organization->id ? 'selected' : ''); ?>>
                                    <?php echo e($organization->name); ?> (<?php echo e(ucfirst($organization->type)); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaled2cde6083938c436304f332ba96bb7c)): ?>
<?php $attributes = $__attributesOriginaled2cde6083938c436304f332ba96bb7c; ?>
<?php unset($__attributesOriginaled2cde6083938c436304f332ba96bb7c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaled2cde6083938c436304f332ba96bb7c)): ?>
<?php $component = $__componentOriginaled2cde6083938c436304f332ba96bb7c; ?>
<?php unset($__componentOriginaled2cde6083938c436304f332ba96bb7c); ?>
<?php endif; ?>
                        <div class="form-text text-gray-600">Pilih klinik atau faskes tujuan pengadaan</div>
                    </div>
                <?php endif; ?>
                <div class="col-md-6">
                    <?php if (isset($component)) { $__componentOriginaled2cde6083938c436304f332ba96bb7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaled2cde6083938c436304f332ba96bb7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.select','data' => ['name' => 'supplier_id','label' => 'Supplier Pemenuhan Item','required' => true,'xModel' => 'supplierId','@change' => 'loadProducts()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'supplier_id','label' => 'Supplier Pemenuhan Item','required' => true,'x-model' => 'supplierId','@change' => 'loadProducts()']); ?>
                        <option value="">— Pilih Supplier —</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($supplier->id); ?>"
                                    data-products="<?php echo e(json_encode($supplier->products)); ?>">
                                <?php echo e($supplier->name); ?> (<?php echo e($supplier->code); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaled2cde6083938c436304f332ba96bb7c)): ?>
<?php $attributes = $__attributesOriginaled2cde6083938c436304f332ba96bb7c; ?>
<?php unset($__attributesOriginaled2cde6083938c436304f332ba96bb7c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaled2cde6083938c436304f332ba96bb7c)): ?>
<?php $component = $__componentOriginaled2cde6083938c436304f332ba96bb7c; ?>
<?php unset($__componentOriginaled2cde6083938c436304f332ba96bb7c); ?>
<?php endif; ?>
                    <div class="form-text text-gray-600">Mengganti supplier akan mereset daftar item</div>
                </div>
                <div class="col-md-6">
                    <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['name' => 'notes','label' => 'Catatan Tambahan (Opsional)','value' => ''.e(old('notes', $purchaseOrder->notes)).'','placeholder' => 'Contoh: Kirim secepatnya...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'notes','label' => 'Catatan Tambahan (Opsional)','value' => ''.e(old('notes', $purchaseOrder->notes)).'','placeholder' => 'Contoh: Kirim secepatnya...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $attributes = $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $component = $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
                    <div class="form-text text-gray-600">Catatan internal untuk tim operasional</div>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Daftar Item Produk','icon' => 'package','class' => 'card-flush mb-7']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Daftar Item Produk','icon' => 'package','class' => 'card-flush mb-7']); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <button type="button"
                        class="btn btn-sm btn-primary d-flex align-items-center gap-2"
                        @click="addItem()"
                        :disabled="!supplierId"
                        :class="{ 'opacity-50': !supplierId }">
                    <i class="ki-outline ki-plus fs-3"></i>
                    <span class="fw-bold">Tambah Produk</span>
                </button>
                <div x-show="!supplierId" class="text-muted fs-8 mt-1">Pilih supplier terlebih dahulu</div>
             <?php $__env->endSlot(); ?>

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
                                        <p class="text-gray-600 fs-7 mb-0">Tekan "Tambah Produk" untuk menambahkan item.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                
                                <td class="px-4 py-3">
                                    <div class="product-search-wrapper">
                                        <input type="hidden" x-bind:name="'items[' + index + '][product_id]'" x-bind:value="item.product_id">

                                        <input type="text"
                                               class="form-control form-control-solid"
                                               placeholder="Ketik nama atau SKU produk..."
                                               x-model="searchQuery[index]"
                                               x-bind:data-search-input="index"
                                               @focus="openSearch(index)"
                                               @blur="closeSearch(index)"
                                               @input="item.product_id = ''; item.product_name = ''"
                                               autocomplete="off">

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
                                    <div x-show="item.product_id" class="mt-1">
                                        <span class="badge badge-light-primary fs-8" x-text="item.product_sku ? 'SKU: ' + item.product_sku : ''"></span>
                                    </div>
                                    <div x-show="!item.product_id && searchQuery[index]" class="text-danger fs-8 mt-1">
                                        Pilih produk dari daftar
                                    </div>
                                </td>

                                
                                <td class="px-4 py-3">
                                    <input type="number"
                                           x-bind:name="'items[' + index + '][quantity]'"
                                           x-bind:value="item.quantity"
                                           @input="updateQuantity(index, $event.target.value)"
                                           min="1"
                                           class="form-control form-control-solid" />
                                </td>

                                
                                <td class="px-4 py-3">
                                    <input type="number"
                                           x-bind:name="'items[' + index + '][unit_price]'"
                                           x-bind:value="item.unit_price"
                                           readonly
                                           class="form-control form-control-solid bg-light"
                                           style="cursor: not-allowed;" />
                                    <div class="form-text text-muted fs-8 mt-1">Otomatis dari master produk</div>
                                </td>

                                
                                <td class="px-4 py-3 text-end align-middle">
                                    <span class="text-gray-900 fw-bold fs-6" x-text="formatRupiah(item.subtotal)"></span>
                                </td>

                                
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
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>

        
        <div class="d-flex align-items-center justify-content-end gap-3 pt-4">
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','outline' => true,'size' => 'lg','href' => ''.e(route('web.po.show', $purchaseOrder)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','outline' => true,'size' => 'lg','href' => ''.e(route('web.po.show', $purchaseOrder)).'']); ?>
                Batalkan Perubahan
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit','variant' => 'primary','size' => 'lg','class' => 'update-confirm','dataName' => 'PO #'.e($purchaseOrder->po_number).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','size' => 'lg','class' => 'update-confirm','data-name' => 'PO #'.e($purchaseOrder->po_number).'']); ?>
                Simpan Perubahan
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
        </div>

    </form>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $attributes = $__attributesOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__attributesOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $component = $__componentOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__componentOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/purchase-orders/edit.blade.php ENDPATH**/ ?>