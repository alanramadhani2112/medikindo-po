<?php $__env->startSection('content'); ?>
    
    <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-outline ki-check-circle fs-2 me-3"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Manajemen Organisasi</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola data klinik dan rumah sakit</p>
        </div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_organizations')): ?>
            <a href="<?php echo e(route('web.organizations.create')); ?>" class="btn btn-primary">
                <i class="ki-outline ki-picture fs-2"></i>
                Tambah Organisasi
            </a>
        <?php endif; ?>
    </div>

    
    <div class="card mb-5">
        <div class="card-body">
            <form action="<?php echo e(route('web.organizations.index')); ?>" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="tab" value="<?php echo e($tab ?? 'all'); ?>">
                
                
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-outline ki-chart
 fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nama atau kode...">
                    </div>
                </div>
                
                
                <select name="status" class="form-select form-select-solid" style="max-width: 180px;">
                    <option value="">Semua Status</option>
                    <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Aktif</option>
                    <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Nonaktif</option>
                </select>
                
                
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-outline ki-chart
 fs-2"></i>
                    Filter
                </button>
                
                
                <?php if(request()->filled('search') || request()->filled('status')): ?>
                    <a href="<?php echo e(route('web.organizations.index', ['tab' => $tab ?? 'all'])); ?>" class="btn btn-light">
                        <i class="ki-outline ki-arrow-zigzag fs-2"></i>
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
                        'all' => ['label' => 'Semua', 'icon' => 'ki-home'],
                        'hospital' => ['label' => 'Rumah Sakit', 'icon' => 'ki-hospital'],
                        'clinic' => ['label' => 'Klinik', 'icon' => 'ki-office-bag'],
                    ];
                    $tab = request('tab', 'all');
                    $counts = [
                        'all' => $organizations->total(),
                        'hospital' => $organizations->where('type', 'hospital')->count(),
                        'clinic' => $organizations->where('type', 'clinic')->count(),
                    ];
                ?>
                <?php $__currentLoopData = $tabOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $tabData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php 
                        $isActive = $tab === $val;
                        $count = $counts[$val] ?? 0;
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('web.organizations.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val]))); ?>" 
                           class="nav-link text-active-primary d-flex align-items-center <?php echo e($isActive ? 'active' : ''); ?>">
                            <i class="ki-outline <?php echo e($tabData['icon']); ?> fs-4 me-3"></i>
                            <span class="fs-6 fw-bold me-3"><?php echo e($tabData['label']); ?></span>
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
        <div class="card-header">
            <h3 class="card-title">
                Daftar Organisasi
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start min-w-250px">Organisasi / Kode</th>
                            <th class="min-w-120px">Tipe</th>
                            <th class="min-w-200px">Kontak</th>
                            <th class="min-w-150px">Izin Operasional</th>
                            <th class="min-w-100px">Status</th>
                            <th class="text-end pe-4 rounded-end min-w-120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $organizations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px">
                                            <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                                <?php echo e(strtoupper(substr($org->name, 0, 2))); ?>

                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-gray-900 fs-6"><?php echo e($org->name); ?></span>
                                            <span class="text-muted fs-7"><?php echo e($org->code); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-info"><?php echo e(strtoupper($org->type)); ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-6 mb-1"><?php echo e($org->phone ?? '—'); ?></div>
                                    <div class="text-muted fs-7"><?php echo e($org->email ?? '—'); ?></div>
                                </td>
                                <td>
                                    <div class="text-gray-800 fw-semibold fs-7"><?php echo e($org->license_number ?? '—'); ?></div>
                                </td>
                                <td>
                                    <?php if($org->is_active): ?>
                                        <span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
                                    <?php else: ?>
                                        <span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="action-menu-wrapper">
                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary" data-action-menu>
                                            <i class="ki-outline ki-dots-horizontal fs-3"></i>
                                            Aksi
                                        </button>
                                        <div class="action-dropdown-menu" style="display: none;">
                                            <div class="menu-item px-3">
                                                <a href="<?php echo e(route('web.organizations.edit', $org)); ?>" class="menu-link px-3">
                                                    <i class="ki-outline ki-parcel fs-4 me-2 text-warning"></i>
                                                    Edit Organisasi
                                                </a>
                                            </div>
                                            <div class="separator my-2"></div>
                                            <div class="menu-item px-3">
                                                <form method="POST" action="<?php echo e(route('web.organizations.toggle_status', $org)); ?>" class="d-inline w-100">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PATCH'); ?>
                                                    <button type="submit" class="menu-link px-3 w-100 text-start toggle-status-confirm" 
                                                            data-name="<?php echo e($org->name); ?>" 
                                                            data-status="<?php echo e($org->is_active ? 'active' : 'inactive'); ?>"
                                                            style="background: none; border: none;">
                                                        <?php if($org->is_active): ?>
                                                            <i class="ki-outline ki-arrow-zigzag-circle fs-4 me-2 text-danger"></i>
                                                            Nonaktifkan Organisasi
                                                        <?php else: ?>
                                                            <i class="ki-outline ki-check-circle fs-4 me-2 text-success"></i>
                                                            Aktifkan Organisasi
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <h3 class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Data Organisasi</h3>
                                        <p class="text-muted fs-7">Tambahkan organisasi untuk mulai mengelola data lintas fasilitas.</p>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_organizations')): ?>
                                            <a href="<?php echo e(route('web.organizations.create')); ?>" class="btn btn-primary mt-3">
                                                <i class="ki-outline ki-picture fs-2"></i>
                                                Registrasi Organisasi
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            
            <?php if($organizations->hasPages()): ?>
            <div class="d-flex flex-stack flex-wrap pt-7">
                <div class="text-muted fs-7">
                    Menampilkan <?php echo e($organizations->firstItem()); ?> - <?php echo e($organizations->lastItem()); ?> dari <?php echo e($organizations->total()); ?> data
                </div>
                <div>
                    <?php echo e($organizations->links()); ?>

                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['pageTitle' => 'Organizations'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/organizations/index.blade.php ENDPATH**/ ?>