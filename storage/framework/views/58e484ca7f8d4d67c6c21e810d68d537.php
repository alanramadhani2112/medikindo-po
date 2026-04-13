<?php $__env->startSection('content'); ?>
    
    <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-outline ki-check-circle fs-2 me-3"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Manajemen Produk</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola katalog produk dan obat-obatan</p>
        </div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_products')): ?>
            <a href="<?php echo e(route('web.products.create')); ?>" class="btn btn-primary">
                <i class="ki-outline ki-plus fs-2"></i>
                Tambah Produk
            </a>
        <?php endif; ?>
    </div>

    
    <div class="card mb-5">
        <div class="card-body">
            <form action="<?php echo e(route('web.products.index')); ?>" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="type" value="<?php echo e(request('type')); ?>">
                
                
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nama atau SKU...">
                    </div>
                </div>
                
                
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-outline ki-magnifier fs-2"></i>
                    Cari
                </button>
                
                
                <?php if(request()->filled('search')): ?>
                    <a href="<?php echo e(route('web.products.index', ['type' => request('type')])); ?>" class="btn btn-light">
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
                        '' => ['label' => 'Semua', 'icon' => 'ki-element-11'],
                        'non-narcotic' => ['label' => 'Non-Narkotika', 'icon' => 'ki-shield-tick'],
                        'narcotic' => ['label' => 'Narkotika', 'icon' => 'ki-shield-cross'],
                    ];
                    $currentTab = request('type', '');
                    $counts = [
                        '' => \App\Models\Product::count(),
                        'non-narcotic' => \App\Models\Product::where('is_narcotic', false)->count(),
                        'narcotic' => \App\Models\Product::where('is_narcotic', true)->count(),
                    ];
                ?>
                <?php $__currentLoopData = $tabOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $tabData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isActive = (string)$currentTab === (string)$val;
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('web.products.index', array_merge(request()->except(['type', 'page']), ['type' => $val === '' ? null : $val]))); ?>" 
                           class="nav-link text-active-primary d-flex align-items-center <?php echo e($isActive ? 'active' : ''); ?>">
                            <i class="ki-outline <?php echo e($tabData['icon']); ?> fs-4 me-2"></i>
                            <span class="fs-6 fw-bold"><?php echo e($tabData['label']); ?></span>
                            <span class="badge <?php echo e($isActive ? 'badge-primary' : 'badge-light-secondary'); ?> ms-auto">
                                <?php echo e($counts[$val]); ?>

                            </span>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <div class="card-body pt-6">
            
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 min-w-250px rounded-start">Produk</th>
                            <th class="min-w-125px d-none d-md-table-cell">Kategori</th>
                            <th class="min-w-120px">Klasifikasi</th>
                            <th class="min-w-120px d-none d-xl-table-cell">Harga Beli</th>
                            <th class="min-w-120px d-none d-xl-table-cell">Harga Jual</th>
                            <th class="min-w-120px d-none d-lg-table-cell">Laba Bersih</th>
                            <th class="min-w-100px d-none d-sm-table-cell">Status</th>
                            <th class="text-end min-w-100px pe-4 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px">
                                            <div class="symbol-label fs-7 fw-bold bg-light text-gray-400">
                                                IMG
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold"><?php echo e($product->name); ?></span>
                                            <span class="text-gray-500 fs-7"><?php echo e($product->sku ?? 'NO-SKU'); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge badge-light-primary">
                                        <?php echo e(strtoupper($product->category ?? 'General')); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($product->is_narcotic): ?>
                                        <span class="badge badge-danger fs-7 fw-bold">
                                            <i class="ki-outline ki-shield-cross fs-6 me-1"></i>
                                            NARKOTIKA
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-light-success fs-7 fw-semibold">
                                            <i class="ki-outline ki-shield-tick fs-6 me-1"></i>
                                            NON-NARKOTIKA
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <span class="text-gray-600 fs-7">
                                        Rp <?php echo e(number_format($product->cost_price, 0, ',', '.')); ?>

                                    </span>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <span class="text-gray-800 fw-semibold">
                                        Rp <?php echo e(number_format($product->selling_price, 0, ',', '.')); ?>

                                    </span>
                                    <?php if($product->discount_percentage > 0): ?>
                                        <span class="badge badge-light-warning fs-8 ms-1">-<?php echo e(number_format($product->discount_percentage, 0)); ?>%</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div class="d-flex flex-column">
                                        <span class="text-<?php echo e($product->net_profit >= 0 ? 'success' : 'danger'); ?> fw-bold">
                                            Rp <?php echo e(number_format($product->net_profit, 0, ',', '.')); ?>

                                        </span>
                                        <span class="text-gray-500 fs-8">
                                            Margin: <?php echo e(number_format($product->net_profit_margin, 1)); ?>%
                                        </span>
                                    </div>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <?php if($product->is_active): ?>
                                        <span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
                                    <?php else: ?>
                                        <span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_products')): ?>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ki-outline ki-dots-vertical fs-3"></i>
                                                Aksi
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="<?php echo e(route('web.products.edit', $product)); ?>" class="dropdown-item">
                                                    <i class="ki-outline ki-notepad-edit fs-4 me-2 text-primary"></i>
                                                    Edit Produk
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form method="POST" action="<?php echo e(route('web.products.destroy', $product)); ?>" 
                                                      onsubmit="return confirm('Hapus produk ini? Data akan dihapus secara permanen.')" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ki-outline ki-trash fs-4 me-2"></i>
                                                        Hapus Produk
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum ada produk terdaftar</span>
                                        <span class="text-gray-500 fs-6">Mulai dengan menambahkan produk baru ke katalog.</span>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_products')): ?>
                                            <a href="<?php echo e(route('web.products.create')); ?>" class="btn btn-primary mt-5">
                                                <i class="ki-outline ki-plus fs-2"></i>
                                                Tambah Produk
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($products->hasPages()): ?>
                <div class="pagination-wrapper">
                    <?php echo e($products->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/products/index.blade.php ENDPATH**/ ?>