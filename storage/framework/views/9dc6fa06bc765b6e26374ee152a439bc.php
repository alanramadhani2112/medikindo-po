



<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Dashboard System Monitoring</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang, <?php echo e(auth()->user()->name); ?> - Monitoring sistem secara menyeluruh</p>
    </div>
    <a href="<?php echo e(route('web.users.index')); ?>" class="btn btn-primary">
        <i class="ki-outline ki-setting-2 fs-2"></i>
        Kelola Sistem
    </a>
</div>


<?php if(count($alerts) > 0): ?>
<div class="row g-5 mb-7">
    <div class="col-12">
        <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="alert alert-<?php echo e($alert['type']); ?> d-flex align-items-center p-5 mb-3">
            <i class="ki-outline <?php echo e($alert['icon']); ?> fs-2hx text-<?php echo e($alert['type']); ?> me-4"></i>
            <div class="d-flex flex-column flex-grow-1">
                <h4 class="mb-1 text-<?php echo e($alert['type']); ?> fw-bold"><?php echo e($alert['title']); ?></h4>
                <span class="fs-6"><?php echo e($alert['message']); ?></span>
            </div>
            <?php if(isset($alert['action'])): ?>
            <a href="<?php echo e($alert['action']); ?>" class="btn btn-<?php echo e($alert['type']); ?> btn-sm">
                Lihat Detail
                <i class="ki-outline ki-right fs-5 ms-1"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>


<?php $__currentLoopData = $cardGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="mb-7">
    
    <div class="d-flex align-items-center mb-5">
        <div class="symbol symbol-40px me-3">
            <div class="symbol-label bg-light-<?php echo e($group['color']); ?>">
                <i class="ki-outline <?php echo e($group['icon']); ?> fs-2 text-<?php echo e($group['color']); ?>"></i>
            </div>
        </div>
        <div>
            <h3 class="fs-2 fw-bold text-gray-900 mb-0"><?php echo e($group['title']); ?></h3>
        </div>
    </div>

    
    <div class="row g-5 g-xl-8">
        <?php $__currentLoopData = $group['cards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush h-100 <?php echo e(isset($card['alert']) && $card['alert'] ? 'border border-' . $card['color'] . ' border-2' : ''); ?>">
                <div class="card-body d-flex flex-column justify-content-between p-6">
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <div class="d-flex flex-column flex-grow-1 me-3">
                            <span class="text-gray-500 fw-semibold fs-7 mb-2"><?php echo e($card['label']); ?></span>
                            <span class="text-gray-900 fw-bold fs-2x"><?php echo e($card['value']); ?></span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center bg-light-<?php echo e($card['color']); ?> rounded" style="width:60px;height:60px;">
                            <i class="ki-outline <?php echo e($card['icon']); ?> fs-2x text-<?php echo e($card['color']); ?>"></i>
                        </div>
                    </div>
                    <?php if(isset($card['alert']) && $card['alert']): ?>
                    <div class="d-flex align-items-center">
                        <i class="ki-outline ki-information fs-5 text-<?php echo e($card['color']); ?> me-2"></i>
                        <span class="text-<?php echo e($card['color']); ?> fw-semibold fs-7">Memerlukan perhatian</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<div class="row g-5 g-xl-8 mb-7">
    
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Aktivitas Sistem Terbaru</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Log aktivitas pengguna dan sistem</span>
                </h3>
                <div class="card-toolbar">
                    <a href="<?php echo e(route('web.dashboard.audit')); ?>" class="btn btn-sm btn-light-primary">
                        Lihat Semua
                        <i class="ki-outline ki-right fs-5 ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-150px rounded-start">User</th>
                                <th class="min-w-200px">Aktivitas</th>
                                <th class="min-w-100px">Tipe</th>
                                <th class="text-end pe-4 min-w-120px rounded-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-6"><?php echo e($log->user->name ?? 'System'); ?></span>
                                    <span class="text-muted fw-semibold d-block fs-7"><?php echo e($log->user->email ?? '-'); ?></span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6"><?php echo e($log->description); ?></span>
                                </td>
                                <td>
                                    <?php
                                        $actionColor = match($log->action) {
                                            'create' => 'success',
                                            'update' => 'info',
                                            'delete' => 'danger',
                                            'error' => 'danger',
                                            default => 'primary'
                                        };
                                    ?>
                                    <span class="badge badge-light-<?php echo e($actionColor); ?> fs-7 fw-semibold"><?php echo e(strtoupper($log->action)); ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6"><?php echo e($log->occurred_at->format('d M Y H:i')); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold">Belum ada aktivitas tercatat</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="card-label fw-bold text-gray-900 fs-3">Aksi Cepat</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex flex-column gap-3">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_users')): ?>
                    <a href="<?php echo e(route('web.users.index')); ?>" class="btn btn-light-primary justify-content-start text-start">
                        <i class="ki-outline ki-profile-user fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Manage Users</div>
                            <div class="text-muted fs-7">Kelola pengguna sistem</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_products')): ?>
                    <a href="<?php echo e(route('web.products.index')); ?>" class="btn btn-light-success justify-content-start text-start">
                        <i class="ki-outline ki-package fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Manage Products</div>
                            <div class="text-muted fs-7">Kelola master produk</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_organizations')): ?>
                    <a href="<?php echo e(route('web.organizations.index')); ?>" class="btn btn-light-info justify-content-start text-start">
                        <i class="ki-outline ki-bank fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Manage Organizations</div>
                            <div class="text-muted fs-7">Kelola organisasi/faskes</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_suppliers')): ?>
                    <a href="<?php echo e(route('web.suppliers.index')); ?>" class="btn btn-light-warning justify-content-start text-start">
                        <i class="ki-outline ki-delivery fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Manage Suppliers</div>
                            <div class="text-muted fs-7">Kelola data supplier</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('web.dashboard.audit')); ?>" class="btn btn-light-dark justify-content-start text-start">
                        <i class="ki-outline ki-file fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6">Audit Logs</div>
                            <div class="text-muted fs-7">Lihat log sistem lengkap</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<?php if(count($auditLogs) > 0): ?>
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush border border-danger border-2">
            <div class="card-header border-0 pt-6 bg-light-danger">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-danger fs-3">
                        <i class="ki-outline ki-shield-cross fs-2 me-2"></i>
                        System Errors & Failed Transactions
                    </span>
                    <span class="text-danger mt-1 fw-semibold fs-7">Log error dan transaksi gagal yang memerlukan perhatian segera</span>
                </h3>
                <div class="card-toolbar">
                    <a href="<?php echo e(route('web.dashboard.audit')); ?>" class="btn btn-sm btn-danger">
                        Lihat Semua
                        <i class="ki-outline ki-right fs-5 ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-150px rounded-start">User</th>
                                <th class="min-w-200px">Error Description</th>
                                <th class="min-w-100px">Severity</th>
                                <th class="text-end pe-4 min-w-120px rounded-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-6"><?php echo e($log->user->name ?? 'System'); ?></span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6"><?php echo e($log->description); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-danger fs-7 fw-semibold">
                                        <i class="ki-outline ki-cross-circle fs-6 me-1"></i>
                                        ERROR
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6"><?php echo e($log->occurred_at->format('d M Y H:i')); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>



<?php if(isset($analytics)): ?>
<div class="mb-7">
    
    <div class="d-flex align-items-center mb-5">
        <div class="symbol symbol-40px me-3">
            <div class="symbol-label bg-light-primary">
                <i class="ki-outline ki-chart-simple fs-2 text-primary"></i>
            </div>
        </div>
        <div>
            <h3 class="fs-2 fw-bold text-gray-900 mb-0">Analytics & Insights</h3>
            <span class="text-muted fs-7">Data produk, supplier, dan rekomendasi</span>
        </div>
    </div>

    
    <div class="row g-5 g-xl-8 mb-7">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush bg-light-success h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-success fw-bold fs-7">TOTAL PEMBELIAN</span>
                        <i class="ki-outline ki-package fs-2x text-success"></i>
                    </div>
                    <div class="fw-bold fs-2x text-gray-900 mb-2"><?php echo e(number_format($analytics['purchaseSummary']['total_quantity'], 0, ',', '.')); ?></div>
                    <div class="text-gray-600 fs-7">Unit produk terbeli</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush bg-light-primary h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-primary fw-bold fs-7">NILAI PEMBELIAN</span>
                        <i class="ki-outline ki-wallet fs-2x text-primary"></i>
                    </div>
                    <div class="fw-bold fs-2x text-gray-900 mb-2">Rp <?php echo e(number_format($analytics['purchaseSummary']['total_value'], 0, ',', '.')); ?></div>
                    <div class="text-gray-600 fs-7">Total nilai transaksi</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush bg-light-info h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-info fw-bold fs-7">BULAN INI</span>
                        <i class="ki-outline ki-calendar fs-2x text-info"></i>
                    </div>
                    <div class="fw-bold fs-2x text-gray-900 mb-2"><?php echo e(number_format($analytics['purchaseSummary']['month_quantity'], 0, ',', '.')); ?></div>
                    <div class="text-gray-600 fs-7">Unit dibeli bulan ini</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-flush bg-light-warning h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-warning fw-bold fs-7">AVG ORDER VALUE</span>
                        <i class="ki-outline ki-chart-line-up fs-2x text-warning"></i>
                    </div>
                    <div class="fw-bold fs-2x text-gray-900 mb-2">Rp <?php echo e(number_format($analytics['purchaseSummary']['avg_order_value'], 0, ',', '.')); ?></div>
                    <div class="text-gray-600 fs-7">Rata-rata nilai PO</div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-5 g-xl-8 mb-7">
        
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">
                            <i class="ki-outline ki-arrow-up fs-3 text-success me-2"></i>
                            Top 10 Produk Terlaris
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Produk dengan pembelian tertinggi</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">#</th>
                                    <th class="min-w-200px">Produk</th>
                                    <th class="min-w-100px text-end">Qty</th>
                                    <th class="min-w-120px text-end">Nilai</th>
                                    <th class="text-end pe-4 rounded-end">Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $analytics['topProducts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge badge-light-primary fs-7 fw-bold"><?php echo e($index + 1); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold fs-6"><?php echo e($product->name); ?></span>
                                            <span class="text-gray-500 fs-7"><?php echo e($product->sku); ?></span>
                                            <?php if($product->is_narcotic): ?>
                                                <span class="badge badge-danger fs-8 mt-1" style="width: fit-content;">NARKOTIKA</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-900 fw-bold"><?php echo e(number_format($product->total_quantity, 0, ',', '.')); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-900 fw-semibold">Rp <?php echo e(number_format($product->total_value, 0, ',', '.')); ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="badge badge-light-info"><?php echo e($product->order_count); ?> PO</span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-10">
                                        <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                        <div class="text-gray-700 fs-6">Belum ada data pembelian</div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-6">
            <div class="card card-flush h-100">
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">
                            <i class="ki-outline ki-delivery fs-3 text-primary me-2"></i>
                            Top 10 Supplier Terpercaya
                        </span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Supplier dengan order terbanyak</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">#</th>
                                    <th class="min-w-200px">Supplier</th>
                                    <th class="min-w-100px text-end">Orders</th>
                                    <th class="text-end pe-4 rounded-end">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $analytics['topSuppliers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge badge-light-primary fs-7 fw-bold"><?php echo e($index + 1); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold fs-6"><?php echo e($supplier->name); ?></span>
                                            <span class="text-gray-500 fs-7"><?php echo e($supplier->phone); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-light-success fs-7 fw-bold"><?php echo e($supplier->order_count); ?> PO</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="text-gray-900 fw-semibold">Rp <?php echo e(number_format($supplier->total_value, 0, ',', '.')); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-10">
                                        <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                        <div class="text-gray-700 fs-6">Belum ada data supplier</div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-5 g-xl-8">
        
        <div class="col-xl-6">
            <div class="card card-flush h-100 border border-warning border-2">
                <div class="card-header border-0 pt-6 bg-light-warning">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-warning fs-3">
                            <i class="ki-outline ki-arrow-down fs-3 me-2"></i>
                            Produk Slow Moving
                        </span>
                        <span class="text-warning mt-1 fw-semibold fs-7">Produk dengan pembelian rendah (6 bulan terakhir)</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-200px rounded-start">Produk</th>
                                    <th class="min-w-80px text-center">Orders</th>
                                    <th class="text-end pe-4 min-w-120px rounded-end">Last Purchase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $analytics['slowMovingProducts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold fs-6"><?php echo e($product->name); ?></span>
                                            <span class="text-gray-500 fs-7"><?php echo e($product->sku); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-warning fs-7"><?php echo e($product->order_count); ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if($product->last_purchase_date): ?>
                                            <span class="text-gray-700 fs-7"><?php echo e(\Carbon\Carbon::parse($product->last_purchase_date)->format('d M Y')); ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400 fs-7 fst-italic">Belum pernah</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-10">
                                        <i class="ki-outline ki-check-circle fs-3x text-success mb-3"></i>
                                        <div class="text-gray-700 fs-6">Semua produk bergerak dengan baik</div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-6">
            <div class="card card-flush h-100 border border-info border-2">
                <div class="card-header border-0 pt-6 bg-light-info">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-info fs-3">
                            <i class="ki-outline ki-abstract-26 fs-3 me-2"></i>
                            Rekomendasi Smart
                        </span>
                        <span class="text-info mt-1 fw-semibold fs-7">Insight dan saran berdasarkan data</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <?php $__empty_1 = true; $__currentLoopData = $analytics['recommendations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recommendation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="alert alert-<?php echo e($recommendation['color']); ?> d-flex align-items-start p-4 mb-3">
                        <i class="ki-outline <?php echo e($recommendation['icon']); ?> fs-2x text-<?php echo e($recommendation['color']); ?> me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h5 class="mb-0 text-<?php echo e($recommendation['color']); ?> fw-bold"><?php echo e($recommendation['title']); ?></h5>
                                <span class="badge badge-<?php echo e($recommendation['color']); ?> ms-auto"><?php echo e(strtoupper($recommendation['priority'])); ?></span>
                            </div>
                            <p class="mb-0 text-gray-700 fs-6"><?php echo e($recommendation['message']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-10">
                        <i class="ki-outline ki-check-circle fs-3x text-success mb-3"></i>
                        <div class="text-gray-700 fs-6">Tidak ada rekomendasi saat ini</div>
                        <div class="text-gray-500 fs-7">Sistem berjalan dengan optimal</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/dashboard/partials/superadmin.blade.php ENDPATH**/ ?>