<?php $__env->startSection('content'); ?>

<div class="card mb-5">
    <div class="card-body">
        <form action="<?php echo e(route('web.po.index')); ?>" method="GET" class="d-flex flex-wrap gap-3">
            <input type="hidden" name="tab" value="<?php echo e($tab ?? 'all'); ?>">
            
            
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                           class="form-control form-control-solid ps-12" 
                           placeholder="Cari nomor PO, organisasi, atau supplier...">
                </div>
            </div>
            
            
            <?php if(auth()->user()->hasRole('Super Admin')): ?>
            <select name="organization" class="form-select form-select-solid" style="max-width: 200px;">
                <option value="">Semua Organisasi</option>
                <?php $__currentLoopData = $organizations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($org->id); ?>" <?php echo e(request('organization') == $org->id ? 'selected' : ''); ?>>
                        <?php echo e($org->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php endif; ?>
            
            
            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" 
                   class="form-control form-control-solid" style="max-width: 180px;" 
                   placeholder="Dari Tanggal">
            <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" 
                   class="form-control form-control-solid" style="max-width: 180px;" 
                   placeholder="Sampai Tanggal">
            
            
            <button type="submit" class="btn btn-dark">
                <i class="ki-duotone ki-magnifier fs-2"></i>
                Filter
            </button>
            
            
            <?php if(request()->filled('search') || request()->filled('organization') || request()->filled('date_from')): ?>
                <a href="<?php echo e(route('web.po.index', ['tab' => $tab ?? 'all'])); ?>" class="btn btn-light">
                    <i class="ki-duotone ki-cross fs-2"></i>
                    Reset
                </a>
            <?php endif; ?>
            
            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_purchase_orders')): ?>
            <div class="ms-auto">
                <a href="<?php echo e(route('web.po.create')); ?>" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    Buat PO Baru
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>


<div class="card mb-5">
    <div class="card-header border-0 pt-6 pb-2">
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
            <?php
                $tabOptions = [
                    'all' => ['label' => 'Semua', 'icon' => 'ki-home-2'],
                    'draft' => ['label' => 'Draft', 'icon' => 'ki-document'],
                    'submitted' => ['label' => 'Diajukan', 'icon' => 'ki-send'],
                    'approved' => ['label' => 'Disetujui', 'icon' => 'ki-check-circle'],
                    'rejected' => ['label' => 'Ditolak', 'icon' => 'ki-cross-circle'],
                    'completed' => ['label' => 'Selesai', 'icon' => 'ki-verify'],
                ];
            ?>
            <?php $__currentLoopData = $tabOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $tabData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php 
                    $isActive = ($tab ?? 'all') === $val;
                    $count = $counts[$val] ?? 0;
                ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('web.po.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val]))); ?>" 
                       class="nav-link text-active-primary d-flex align-items-center <?php echo e($isActive ? 'active' : ''); ?>">
                        <i class="ki-duotone <?php echo e($tabData['icon']); ?> fs-4 me-2"></i>
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                <thead>
                    <tr class="fw-bold text-muted bg-light">
                        <th class="ps-4 rounded-start min-w-150px">Nomor PO</th>
                        <th class="min-w-150px">Organisasi</th>
                        <th class="min-w-150px">Supplier</th>
                        <th class="min-w-100px">Status</th>
                        <th class="text-end min-w-120px">Total Amount</th>
                        <th class="min-w-120px d-none d-md-table-cell">Tanggal</th>
                        <th class="text-end pe-4 rounded-end min-w-150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $purchaseOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-4">
                                <a href="<?php echo e(route('web.po.show', $order)); ?>" class="text-gray-900 text-hover-primary fw-bold fs-6">
                                    <?php echo e($order->po_number); ?>

                                </a>
                                <div class="text-muted fs-7 mt-1">
                                    <i class="ki-duotone ki-user fs-7 me-1"></i>
                                    <?php echo e($order->creator->name ?? '-'); ?>

                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-gray-800 fs-6"><?php echo e($order->organization->name ?? '-'); ?></div>
                                <div class="text-muted fs-7"><?php echo e($order->organization->type ?? '-'); ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-gray-800 fs-6"><?php echo e($order->supplier->name ?? '-'); ?></div>
                                <div class="text-muted fs-7"><?php echo e($order->supplier->contact ?? '-'); ?></div>
                            </td>
                            <td>
                                <?php
                                    $statusColor = match($order->status) {
                                        'draft' => 'secondary',
                                        'submitted' => 'warning',
                                        'approved' => 'success',
                                        'shipped' => 'primary',
                                        'delivered', 'completed' => 'success',
                                        'rejected', 'cancelled' => 'danger',
                                        default => 'primary'
                                    };
                                ?>
                                <span class="badge badge-<?php echo e($statusColor); ?>"><?php echo e(strtoupper($order->status)); ?></span>
                                <?php if($order->has_narcotics): ?>
                                    <span class="badge badge-danger d-block mt-1">⚠ NARKOTIKA</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-gray-900 fs-6">Rp <?php echo e(number_format($order->total_amount, 0, ',', '.')); ?></span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <div class="text-gray-800 fw-semibold fs-7"><?php echo e($order->created_at->format('d/m/Y')); ?></div>
                                <div class="text-muted fs-8"><?php echo e($order->created_at->diffForHumans()); ?></div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ki-duotone ki-dots-vertical fs-3"></i>
                                        Aksi
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="<?php echo e(route('web.po.show', $order)); ?>" class="dropdown-item">
                                            <i class="ki-duotone ki-eye fs-4 me-2 text-primary"></i>
                                            Lihat Detail
                                        </a>
                                        <?php if($order->status === 'draft'): ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update_purchase_orders')): ?>
                                            <a href="<?php echo e(route('web.po.edit', $order)); ?>" class="dropdown-item">
                                                <i class="ki-duotone ki-notepad-edit fs-4 me-2 text-primary"></i>
                                                Edit PO
                                            </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <a href="<?php echo e(route('web.po.pdf', $order)); ?>" class="dropdown-item" target="_blank">
                                            <i class="ki-duotone ki-file-down fs-4 me-2 text-info"></i>
                                            Download PDF
                                        </a>
                                        <?php if($order->status === 'draft'): ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_purchase_orders')): ?>
                                            <div class="dropdown-divider"></div>
                                            <form action="<?php echo e(route('web.po.destroy', $order)); ?>" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Yakin ingin menghapus PO ini?');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ki-duotone ki-trash fs-4 me-2"></i>
                                                    Hapus PO
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-10">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ki-duotone ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                    <h3 class="fs-5 fw-bold text-gray-800 mb-1">Tidak Ada Data</h3>
                                    <p class="text-muted fs-7">Belum ada purchase order yang tersedia saat ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        
        <?php if($purchaseOrders->hasPages()): ?>
        <div class="d-flex flex-stack flex-wrap pt-7">
            <div class="fs-6 fw-semibold text-gray-700">
                Menampilkan <?php echo e($purchaseOrders->firstItem()); ?> - <?php echo e($purchaseOrders->lastItem()); ?> dari <?php echo e($purchaseOrders->total()); ?> data
            </div>
            <div>
                <?php echo e($purchaseOrders->links()); ?>

            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['pageTitle' => 'Purchase Orders'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/purchase-orders/index.blade.php ENDPATH**/ ?>