<?php $__env->startSection('content'); ?>
    
    <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-solid ki-check-circle fs-2 me-3"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Manajemen Supplier</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola data supplier dan distributor</p>
        </div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_suppliers')): ?>
            <a href="<?php echo e(route('web.suppliers.create')); ?>" class="btn btn-primary">
                <i class="ki-solid ki-plus fs-2"></i>
                Tambah Supplier
            </a>
        <?php endif; ?>
    </div>

    
    <div class="card mb-5">
        <div class="card-body">
            <form action="<?php echo e(route('web.suppliers.index')); ?>" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                
                
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-solid ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nama, kode, atau email...">
                    </div>
                </div>
                
                
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-solid ki-magnifier fs-2"></i>
                    Cari
                </button>
                
                
                <?php if(request()->filled('search')): ?>
                    <a href="<?php echo e(route('web.suppliers.index', ['status' => request('status')])); ?>" class="btn btn-light">
                        <i class="ki-solid ki-cross fs-2"></i>
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
                        '' => ['label' => 'Semua', 'icon' => 'ki-home-2'],
                        'active' => ['label' => 'Aktif', 'icon' => 'ki-check-circle'],
                        'inactive' => ['label' => 'Nonaktif', 'icon' => 'ki-cross-circle'],
                    ];
                    $currentTab = request('status', '');
                    $counts = [
                        '' => \App\Models\Supplier::count(),
                        'active' => \App\Models\Supplier::where('is_active', true)->count(),
                        'inactive' => \App\Models\Supplier::where('is_active', false)->count(),
                    ];
                ?>
                <?php $__currentLoopData = $tabOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $tabData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isActive = (string)$currentTab === (string)$val;
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('web.suppliers.index', array_merge(request()->except(['status', 'page']), ['status' => $val === '' ? null : $val]))); ?>" 
                           class="nav-link text-active-primary d-flex align-items-center <?php echo e($isActive ? 'active' : ''); ?>">
                            <i class="ki-solid <?php echo e($tabData['icon']); ?> fs-4 me-2"></i>
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
                            <th class="ps-4 min-w-250px rounded-start">Supplier / Kode</th>
                            <th class="min-w-200px">Kontak</th>
                            <th class="min-w-200px">Alamat</th>
                            <th class="min-w-100px">Status</th>
                            <th class="text-end min-w-100px pe-4 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px">
                                            <div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">
                                                <?php echo e(strtoupper(substr($supplier->name, 0, 2))); ?>

                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold"><?php echo e($supplier->name); ?></span>
                                            <span class="text-gray-500 fs-7"><?php echo e($supplier->code); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-semibold"><?php echo e($supplier->email ?? '—'); ?></span>
                                        <span class="text-gray-600 fs-7"><?php echo e($supplier->phone ?? '—'); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-gray-700"><?php echo e(Str::limit($supplier->address ?? '—', 50)); ?></span>
                                </td>
                                <td>
                                    <?php if($supplier->is_active): ?>
                                        <span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
                                    <?php else: ?>
                                        <span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ki-solid ki-dots-vertical fs-3"></i>
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="<?php echo e(route('web.suppliers.edit', $supplier)); ?>" class="dropdown-item">
                                                <i class="ki-solid ki-notepad-edit fs-4 me-2 text-primary"></i>
                                                Edit Supplier
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="<?php echo e(route('web.suppliers.toggle_status', $supplier)); ?>" 
                                                  onsubmit="return confirm('<?php echo e($supplier->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?> supplier ini?')" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="dropdown-item <?php echo e($supplier->is_active ? 'text-warning' : 'text-success'); ?>">
                                                    <i class="ki-solid ki-<?php echo e($supplier->is_active ? 'shield-cross' : 'shield-tick'); ?> fs-4 me-2"></i>
                                                    <?php echo e($supplier->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?> Supplier
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-solid ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum ada data supplier</span>
                                        <span class="text-gray-500 fs-6">Data supplier akan muncul setelah proses registrasi.</span>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_supplier')): ?>
                                            <a href="<?php echo e(route('web.suppliers.create')); ?>" class="btn btn-primary mt-5">
                                                <i class="ki-solid ki-plus fs-2"></i>
                                                Tambah Supplier
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($suppliers->hasPages()): ?>
                <div class="pagination-wrapper">
                    <?php echo e($suppliers->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/suppliers/index.blade.php ENDPATH**/ ?>