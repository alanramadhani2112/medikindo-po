



<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Dashboard Procurement</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang, <?php echo e(auth()->user()->name); ?></p>
    </div>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_purchase_orders')): ?>
    <a href="<?php echo e(route('web.po.create')); ?>" class="btn btn-primary">
        <i class="ki-outline ki-plus fs-2"></i>
        Buat PO Baru
    </a>
    <?php endif; ?>
</div>


<div class="row g-5 g-xl-8 mb-7">
    <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-12 col-md-6 col-xl-<?php echo e(count($cards) > 4 ? '3' : (12 / count($cards))); ?>">
        <div class="card card-flush h-100 bg-<?php echo e($card['color']); ?>">
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div class="d-flex flex-column">
                        <span class="text-white opacity-75 fw-semibold fs-7 mb-2"><?php echo e($card['label']); ?></span>
                        <span class="text-white fw-bold fs-2x"><?php echo e($card['value']); ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded" style="width:60px;height:60px;">
                        <i class="ki-outline <?php echo e($card['icon']); ?> fs-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="row g-5 g-xl-8 mb-7">
    
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Purchase Order Terbaru</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Aktivitas pengadaan terkini</span>
                </h3>
                <div class="card-toolbar">
                    <a href="<?php echo e(route('web.po.index')); ?>" class="btn btn-sm btn-light-primary">
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
                                <th class="ps-4 min-w-150px rounded-start">Nomor PO</th>
                                <th class="min-w-150px">Supplier</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-120px">Total</th>
                                <th class="text-end pe-4 min-w-100px rounded-end">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentPOs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $badgeColor = match($po->status) {
                                    'approved', 'shipped', 'delivered', 'completed' => 'success',
                                    'submitted' => 'warning',
                                    'rejected' => 'danger',
                                    default => 'primary'
                                };
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <a href="<?php echo e(route('web.po.show', $po)); ?>" class="text-gray-900 fw-bold text-hover-primary fs-6">
                                        <?php echo e($po->po_number); ?>

                                    </a>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold fs-6"><?php echo e($po->supplier->name ?? '-'); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-light-<?php echo e($badgeColor); ?> fs-7 fw-semibold"><?php echo e(strtoupper($po->status)); ?></span>
                                </td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-6">Rp <?php echo e(number_format($po->total_amount, 0, ',', '.')); ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-gray-700 fw-semibold fs-6"><?php echo e($po->created_at->format('d M Y')); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold">Belum ada purchase order</span>
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
        
        <div class="card card-flush mb-5 mb-xl-8">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="card-label fw-bold text-gray-900 fs-3">Aksi Cepat</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex flex-column gap-3">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_purchase_orders')): ?>
                    <a href="<?php echo e(route('web.po.create')); ?>" class="btn btn-light-primary justify-content-start">
                        <i class="ki-outline ki-plus fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Buat PO</div>
                            <div class="text-muted fs-7">Ajukan purchase order baru</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_invoices')): ?>
                    <a href="<?php echo e(route('web.invoices.supplier.index')); ?>" class="btn btn-light-warning justify-content-start">
                        <i class="ki-outline ki-bill fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Lihat Invoice</div>
                            <div class="text-muted fs-7">Pantau tagihan supplier</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_payment')): ?>
                    <a href="<?php echo e(route('web.payments.index')); ?>" class="btn btn-light-success justify-content-start">
                        <i class="ki-outline ki-wallet fs-3 me-3"></i>
                        <div class="text-start">
                            <div class="fw-bold fs-6">Konfirmasi Pembayaran</div>
                            <div class="text-muted fs-7">Catat pembayaran ke supplier</div>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if(count($alerts) > 0): ?>
        <div class="card card-flush">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title">
                    <span class="card-label fw-bold text-gray-900 fs-3">Notifikasi</span>
                </h3>
            </div>
            <div class="card-body pt-3">
                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="alert alert-<?php echo e($alert['type']); ?> d-flex align-items-center p-5 mb-5">
                    <i class="ki-outline <?php echo e($alert['icon']); ?> fs-2hx text-<?php echo e($alert['type']); ?> me-4"></i>
                    <div class="d-flex flex-column flex-grow-1">
                        <h4 class="mb-1 text-<?php echo e($alert['type']); ?> fw-bold"><?php echo e($alert['title']); ?></h4>
                        <span class="fs-6"><?php echo e($alert['message']); ?></span>
                        <?php if(isset($alert['action'])): ?>
                        <a href="<?php echo e($alert['action']); ?>" class="fw-bold mt-2">Lihat Detail →</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


<?php if(count($outstandingInvoices) > 0): ?>
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Invoice Outstanding</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Tagihan yang belum dibayar</span>
                </h3>
                <div class="card-toolbar">
                    <a href="<?php echo e(route('web.invoices.supplier.index')); ?>" class="btn btn-sm btn-light-primary">
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
                                <th class="ps-4 min-w-150px rounded-start">Nomor Invoice</th>
                                <th class="min-w-120px">Jumlah</th>
                                <th class="min-w-100px">Jatuh Tempo</th>
                                <th class="text-end pe-4 min-w-100px rounded-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $outstandingInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isOverdue = $invoice->due_date < now();
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-6"><?php echo e($invoice->invoice_number); ?></span>
                                    <span class="text-muted fw-semibold d-block fs-7"><?php echo e($invoice->supplier->name ?? '-'); ?></span>
                                </td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-6">Rp <?php echo e(number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.')); ?></span>
                                </td>
                                <td>
                                    <span class="text-gray-700 fw-semibold fs-6"><?php echo e($invoice->due_date->format('d M Y')); ?></span>
                                    <?php if($isOverdue): ?>
                                    <span class="badge badge-light-danger fs-8 fw-semibold d-block mt-1">Overdue</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="badge badge-light-<?php echo e($invoice->status === 'paid' ? 'success' : 'warning'); ?> fs-7 fw-semibold">
                                        <?php echo e(strtoupper($invoice->status)); ?>

                                    </span>
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
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/dashboard/partials/healthcare.blade.php ENDPATH**/ ?>