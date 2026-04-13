<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Buat PO','pageTitle' => 'Buat Purchase Order','breadcrumb' => 'Isi form untuk membuat pesanan pembelian baru']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Buat PO','pageTitle' => 'Buat Purchase Order','breadcrumb' => 'Isi form untuk membuat pesanan pembelian baru']); ?>

    
    <?php $__env->startPush('head-scripts'); ?>
    <script>
    // Define poForm globally BEFORE Alpine initializes
    document.addEventListener('alpine:init', () => {
        Alpine.data('poForm', () => ({
            supplierId: '<?php echo e(old('supplier_id', '')); ?>',
            products: [],
            items: [],
            
            get total() {
                return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
            },
            
            init() {
                console.log('PO Form initialized');
                <?php if(old('items')): ?>
                    this.items = <?php echo json_encode(old('items'), 15, 512) ?>;
                    if (this.supplierId) {
                        this.$nextTick(() => {
                            this.loadProducts(true);
                        });
                    }
                <?php endif; ?>
            },
            
            loadProducts(isInit = false) {
                console.log('loadProducts called, supplierId:', this.supplierId);
                
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
                
                const option = Array.from(select.options).find(opt => opt.value == this.supplierId);
                console.log('Selected option:', option);
                console.log('Option dataset.products:', option ? option.dataset.products : 'N/A');
                
                try {
                    this.products = option && option.dataset.products ? JSON.parse(option.dataset.products) : [];
                    console.log('Products loaded:', this.products.length);
                    console.log('Products:', this.products);
                } catch(e) {
                    console.error('Error loading products:', e);
                    this.products = [];
                }
                
                if (!isInit) {
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
    <?php $__env->stopPush(); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">Buat Purchase Order Baru</h1>
            <p class="text-gray-600 fs-6 mb-0">Mohon lengkapi informasi organisasi, pemasok, dan item produk secara cermat.</p>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('web.po.store')); ?>" id="po-form" x-data="poForm()" x-init="init()">
        <?php echo csrf_field(); ?>

        
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
                                <option value="<?php echo e($organization->id); ?>" <?php echo e(old('organization_id') == $organization->id ? 'selected' : ''); ?>>
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
                            <option value="<?php echo e($supplier->id); ?>" data-products="<?php echo e(json_encode($supplier->products)); ?>">
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
                    <div class="form-text text-gray-600">Item produk akan diload berdasarkan supplier terpilih</div>
                </div>
                <div class="col-md-6">
                    <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['name' => 'notes','label' => 'Catatan Tambahan (Opsional)','value' => ''.e(old('notes')).'','placeholder' => 'Contoh: Kirim secepatnya...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'notes','label' => 'Catatan Tambahan (Opsional)','value' => ''.e(old('notes')).'','placeholder' => 'Contoh: Kirim secepatnya...']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Daftar Entitas Produk Pembelian','icon' => 'package','class' => 'card-flush mb-7']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Daftar Entitas Produk Pembelian','icon' => 'package','class' => 'card-flush mb-7']); ?>
             <?php $__env->slot('actions', null, []); ?> 
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
             <?php $__env->endSlot(); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','outline' => true,'size' => 'lg','href' => ''.e(route('web.po.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','outline' => true,'size' => 'lg','href' => ''.e(route('web.po.index')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit','variant' => 'primary','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','size' => 'lg']); ?>
                Simpan sebagai Draf Pengajuan
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

<?php /**PATH C:\laragon\www\medikindo-po\resources\views/purchase-orders/create.blade.php ENDPATH**/ ?>