<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Rekam Penerimaan','pageTitle' => 'Rekam Penerimaan Barang','breadcrumb' => 'Sistem akan mencatat kedatangan fisik logistik']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Rekam Penerimaan','pageTitle' => 'Rekam Penerimaan Barang','breadcrumb' => 'Sistem akan mencatat kedatangan fisik logistik']); ?>

    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Rekam Penerimaan Barang','description' => 'Sistem akan mencatat kedatangan fisik logistik dan mengupdate status pesanan.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Rekam Penerimaan Barang','description' => 'Sistem akan mencatat kedatangan fisik logistik dan mengupdate status pesanan.']); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

    <div x-data="grForm()">
        <form method="POST" action="<?php echo e(route('web.goods-receipts.store')); ?>" id="gr-form">
            <?php echo csrf_field(); ?>

            
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Pilih Purchase Order','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pilih Purchase Order','class' => 'mb-5']); ?>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label required fw-semibold fs-6 mb-2">Purchase Order Terotorisasi</label>
                        <select name="purchase_order_id" class="form-select form-select-solid" required 
                                x-model="selectedPoId" @change="loadItems()">
                            <option value="">— Pilih PO yang sudah berstatus Approved / Sent —</option>
                            <?php $__currentLoopData = $pos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($po->id); ?>" data-items="<?php echo e(json_encode($po->items)); ?>">
                                    <?php echo e($po->po_number); ?> - <?php echo e($po->supplier?->name); ?> (Pesan: <?php echo e($po->items->sum('quantity')); ?> items)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
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

            
            <div x-show="selectedPoId" x-transition>
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Detail Fisik yang Diterima','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Fisik yang Diterima','class' => 'mb-5']); ?>
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

                
                <div class="d-flex justify-content-end gap-3">
                    <a href="<?php echo e(route('web.goods-receipts.index')); ?>" class="btn btn-light-secondary">
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

    <?php $__env->startPush('scripts'); ?>
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
    <?php $__env->stopPush(); ?>
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
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/goods-receipts/create.blade.php ENDPATH**/ ?>