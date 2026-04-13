
<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Detail PO '.e($po->po_number).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail PO '.e($po->po_number).'']); ?>

    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
        <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center gap-3">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0"><?php echo e($po->po_number); ?></h1>
                <?php
                    $badgeClass = match($po->status) {
                        'draft' => 'badge-light-secondary',
                        'submitted' => 'badge-light-warning',
                        'approved' => 'badge-light-primary',
                        'shipped' => 'badge-light-primary',
                        'delivered', 'completed' => 'badge-light-success',
                        'rejected', 'cancelled' => 'badge-light-danger',
                        default => 'badge-light-secondary'
                    };
                ?>
                <span class="badge <?php echo e($badgeClass); ?> fw-bold"><?php echo e(strtoupper($po->status)); ?></span>
            </div>
            <p class="text-gray-600 fs-6 mb-0">Pesanan diterbitkan pada <?php echo e($po->created_at->format('d M Y, H:i')); ?> oleh <?php echo e($po->creator?->name ?? 'System'); ?></p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if($po->isDraft()): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('submit_po')): ?>
                    <form method="POST" action="<?php echo e(route('web.po.submit', $po)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-outline ki-send fs-3"></i>
                            Submit
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php if($po->isApproved()): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve_po')): ?>
                    <form method="POST" action="<?php echo e(route('web.po.mark_shipped', $po)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-outline ki-delivery fs-3"></i>
                            Kirim
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <button type="button" class="btn btn-light" onclick="window.open('<?php echo e(route('web.po.pdf', $po)); ?>', '_blank')">
                <i class="ki-outline ki-cloud-download fs-3"></i>
                PDF
            </button>
            <a href="<?php echo e(route('web.po.index')); ?>" class="btn btn-secondary">
                <i class="ki-outline ki-arrow-left fs-3"></i>
                Kembali
            </a>
        </div>
    </div>

    
    <div class="row g-5 g-xl-8">
        
        <div class="col-lg-8">
            <div class="card card-flush mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold fs-3">Rincian Item Pengadaan</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-200px">Produk</th>
                                    <th class="min-w-100px">SKU</th>
                                    <th class="min-w-80px text-end">Qty</th>
                                    <th class="min-w-120px text-end">Harga Satuan</th>
                                    <th class="min-w-120px text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $po->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold d-block fs-6"><?php echo e($item->product?->name ?? '—'); ?></span>
                                                <span class="text-gray-600 fw-semibold fs-7"><?php echo e($item->product?->category ?? 'General'); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-600 fw-semibold fs-7 font-monospace"><?php echo e($item->product?->sku ?? '—'); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-900 fw-bold d-block fs-6"><?php echo e(number_format($item->quantity, 0, ',', '.')); ?></span>
                                            <span class="text-gray-600 fw-semibold fs-7"><?php echo e($item->product?->unit ?? 'Unit'); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-semibold d-block fs-6">Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-primary fw-bold d-block fs-6">Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <tr class="bg-light">
                                    <td colspan="4" class="text-end">
                                        <span class="text-gray-700 fw-bold fs-5">Total Nilai Kontrak PO</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-primary fw-bold fs-3">Rp <?php echo e(number_format($po->total_amount, 0, ',', '.')); ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if($po->notes): ?>
                <div class="card card-flush mb-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title fw-bold fs-3">Catatan Internal</h3>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-gray-600 fs-6 mb-0 lh-lg"><?php echo e($po->notes); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="col-lg-4">
            <div class="card card-flush bg-light-primary mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold fs-3 text-primary">Informasi Transaksi</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-7">
                        <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Organisasi Pemesan</span>
                        <div class="text-gray-900 fw-bold fs-4 mb-1"><?php echo e($po->organization?->name ?? '—'); ?></div>
                        <span class="text-primary fw-semibold fs-7"><?php echo e($po->organization?->type ?? 'Clinic'); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Rekan Supplier</span>
                        <div class="text-gray-900 fw-bold fs-4 mb-1"><?php echo e($po->supplier?->name ?? '—'); ?></div>
                        <span class="text-gray-600 fw-semibold fs-7"><?php echo e($po->supplier?->code ?? 'SUP'); ?></span>
                    </div>
                </div>
            </div>

            <div class="card card-flush mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold fs-3">Riwayat Approval</h3>
                </div>
                <div class="card-body pt-0">
                    <?php $__empty_1 = true; $__currentLoopData = $po->approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="pb-4 mb-4 border-bottom border-gray-200 <?php echo e($loop->last ? 'border-0 pb-0 mb-0' : ''); ?>">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-gray-700 fw-semibold fs-7">Level <?php echo e($approval->level); ?></span>
                                <?php
                                    $approvalBadgeClass = match($approval->status) {
                                        'approved' => 'badge-light-success',
                                        'rejected' => 'badge-light-danger',
                                        default => 'badge-light-warning'
                                    };
                                ?>
                                <span class="badge <?php echo e($approvalBadgeClass); ?> fw-bold">
                                    <?php echo e(strtoupper($approval->status)); ?>

                                </span>
                            </div>
                            <div class="text-gray-900 fw-semibold fs-6 mb-1"><?php echo e($approval->approver?->name ?? 'System'); ?></div>
                            <p class="text-gray-600 fs-7 mb-0"><?php echo e($approval->actioned_at ? $approval->actioned_at->format('d/m H:i') : 'PENDING'); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-10">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                <span class="text-gray-600 fs-5">Belum ada riwayat approval.</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

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

<?php /**PATH C:\laragon\www\medikindo-po\resources\views/purchase-orders/show.blade.php ENDPATH**/ ?>