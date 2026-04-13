<?php $__env->startSection('content'); ?>
        
    <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-outline ki-check-circle fs-2 me-3"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="card mb-5">
        <div class="card-body">
            <form action="<?php echo e(route('web.goods-receipts.index')); ?>" method="GET" class="d-flex flex-wrap gap-3">
                
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nomor GR atau nomor PO...">
                    </div>
                </div>
                
                
                <select name="status" class="form-select form-select-solid" style="max-width: 200px;">
                    <option value="">Semua Status</option>
                    <option value="partial" <?php echo e(request('status') === 'partial' ? 'selected' : ''); ?>>Partial</option>
                    <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed</option>
                </select>
                
                
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-outline ki-magnifier fs-2"></i>
                    Cari
                </button>
                
                
                <?php if(request()->filled('search') || request()->filled('status')): ?>
                    <a href="<?php echo e(route('web.goods-receipts.index')); ?>" class="btn btn-light">
                        <i class="ki-outline ki-cross fs-2"></i>
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    
    <div class="card mb-5">
        <div class="card-header border-0 pt-6 pb-2">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
                <?php
                    $tabOptions = [
                        'all' => ['label' => 'Semua', 'icon' => 'ki-element-11'],
                        'partial' => ['label' => 'Partial', 'icon' => 'ki-information-5'],
                        'completed' => ['label' => 'Selesai', 'icon' => 'ki-check-circle'],
                    ];
                    $tab = request('tab', 'all');
                    $counts = [
                        'all' => $receipts->total(),
                        'partial' => $receipts->where('status', 'partial')->count(),
                        'completed' => $receipts->where('status', 'completed')->count(),
                    ];
                ?>
                <?php $__currentLoopData = $tabOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $tabData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php 
                        $isActive = $tab === $val;
                        $count = $counts[$val] ?? 0;
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('web.goods-receipts.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val]))); ?>" 
                           class="nav-link text-active-primary d-flex align-items-center <?php echo e($isActive ? 'active' : ''); ?>">
                            <i class="ki-outline <?php echo e($tabData['icon']); ?> fs-4 me-2"></i>
                                <span class="fs-6 fw-bold"><?php echo e($tabData['label']); ?></span>
                            <span class="badge <?php echo e($isActive ? 'badge-primary' : 'badge-light-secondary'); ?> ms-auto">
                                <?php echo e($count); ?>

                            </span>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="ki-outline ki-delivery fs-2 me-2"></i>
                Daftar Penerimaan Barang
            </h3>
            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_goods_receipt')): ?>
            <div>
                <a href="<?php echo e(route('web.goods-receipts.create')); ?>" class="btn btn-primary">
                    <i class="ki-outline ki-plus fs-2"></i>
                    Rekam Penerimaan Barang
                </a>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start min-w-150px">Nomor GR</th>
                            <th class="min-w-150px">Referensi PO</th>
                            <th class="min-w-200px">Supplier / Organisasi</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-150px">Tanggal / Penerima</th>
                            <th class="text-end pe-4 rounded-end min-w-120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receipt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4">
                                    <a href="<?php echo e(route('web.goods-receipts.show', $receipt)); ?>" 
                                       class="text-gray-900 text-hover-primary fw-bold fs-6">
                                        <?php echo e($receipt->gr_number); ?>

                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('web.po.show', $receipt->purchase_order_id)); ?>" 
                                       class="text-primary text-hover-primary fw-bold fs-6">
                                        <?php echo e($receipt->purchaseOrder?->po_number ?? '—'); ?>

                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-6 mb-1"><?php echo e($receipt->purchaseOrder?->supplier?->name ?? '—'); ?></div>
                                    <div class="text-muted fs-7">
                                        <i class="ki-outline ki-office-bag fs-7 me-1"></i>
                                        <?php echo e($receipt->purchaseOrder?->organization?->name ?? '—'); ?>

                                    </div>
                                </td>
                                <td>
                                    <?php
                                        $statusColor = match($receipt->status) {
                                            'completed' => 'success',
                                            'partial' => 'warning',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <span class="badge badge-<?php echo e($statusColor); ?>"><?php echo e(strtoupper($receipt->status)); ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-6"><?php echo e($receipt->received_date->format('d/m/Y')); ?></div>
                                    <div class="text-muted fs-7 mt-1">
                                        <i class="ki-outline ki-user fs-7 me-1"></i>
                                        <?php echo e($receipt->receivedBy?->name ?? '—'); ?>

                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ki-outline ki-dots-vertical fs-3"></i>
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="<?php echo e(route('web.goods-receipts.show', $receipt)); ?>" class="dropdown-item">
                                                <i class="ki-outline ki-eye fs-4 me-2 text-primary"></i>
                                                Lihat Detail
                                            </a>
                                            <a href="<?php echo e(route('web.goods-receipts.pdf', $receipt)); ?>" class="dropdown-item" target="_blank">
                                                <i class="ki-outline ki-file-down fs-4 me-2 text-info"></i>
                                                Download PDF
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-package fs-3x text-gray-400 mb-3"></i>
                                        <h3 class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Penerimaan Barang</h3>
                                        <p class="text-muted fs-7">Data penerimaan barang akan muncul setelah proses konfirmasi penerimaan.</p>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('confirm_receipt')): ?>
                                            <a href="<?php echo e(route('web.goods-receipts.create')); ?>" class="btn btn-primary mt-3">
                                                <i class="ki-outline ki-plus fs-2"></i>
                                                Rekam Penerimaan
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            
            <?php if($receipts->hasPages()): ?>
            <div class="d-flex flex-stack flex-wrap pt-7">
                <div class="text-muted fs-7">
                    Menampilkan <?php echo e($receipts->firstItem()); ?> - <?php echo e($receipts->lastItem()); ?> dari <?php echo e($receipts->total()); ?> data
                </div>
                <div>
                    <?php echo e($receipts->links()); ?>

                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', ['pageTitle' => 'Penerimaan Barang'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/goods-receipts/index.blade.php ENDPATH**/ ?>