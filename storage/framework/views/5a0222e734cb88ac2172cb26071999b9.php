<?php $__env->startSection('content'); ?>
        
    <div class="row g-5 mb-7">
        <div class="col-md-4">
            <div class="card bg-success">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Total Kas Masuk</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp <?php echo e(number_format($stats['total_incoming'] ?? 0, 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Total Kas Keluar</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp <?php echo e(number_format($stats['total_outgoing'] ?? 0, 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Saldo Netto</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp <?php echo e(number_format(($stats['total_incoming'] ?? 0) - ($stats['total_outgoing'] ?? 0), 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-5">
        <div class="card-body">
            <form action="<?php echo e(route('web.payments.index')); ?>" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="tab" value="<?php echo e($tab ?? 'all'); ?>">
                
                
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-solid ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari deskripsi atau referensi...">
                    </div>
                </div>
                
                
                <select name="type" class="form-select form-select-solid" style="max-width: 180px;">
                    <option value="">Semua Tipe</option>
                    <option value="incoming" <?php echo e(request('type') === 'incoming' ? 'selected' : ''); ?>>Incoming</option>
                    <option value="outgoing" <?php echo e(request('type') === 'outgoing' ? 'selected' : ''); ?>>Outgoing</option>
                </select>
                
                
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" 
                       class="form-control form-control-solid" style="max-width: 180px;" 
                       placeholder="Dari Tanggal">
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" 
                       class="form-control form-control-solid" style="max-width: 180px;" 
                       placeholder="Sampai Tanggal">
                
                
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-solid ki-magnifier fs-2"></i>
                    Cari
                </button>
                
                
                <?php if(request()->filled('search') || request()->filled('type') || request()->filled('date_from')): ?>
                    <a href="<?php echo e(route('web.payments.index', ['tab' => $tab ?? 'all'])); ?>" class="btn btn-light">
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
                        'all' => ['label' => 'Semua Transaksi', 'icon' => 'ki-home-2'],
                        'incoming' => ['label' => 'Kas Masuk', 'icon' => 'ki-arrow-down'],
                        'outgoing' => ['label' => 'Kas Keluar', 'icon' => 'ki-arrow-up'],
                        'pending' => ['label' => 'Pending', 'icon' => 'ki-time'],
                        'confirmed' => ['label' => 'Confirmed', 'icon' => 'ki-check-circle'],
                    ];
                    $tab = request('tab', 'all');
                    $counts = [
                        'all' => $payments->total(),
                        'incoming' => $payments->where('type', 'incoming')->count(),
                        'outgoing' => $payments->where('type', 'outgoing')->count(),
                        'pending' => $payments->where('status', 'pending')->count(),
                        'confirmed' => $payments->where('status', 'confirmed')->count(),
                    ];
                ?>
                <?php $__currentLoopData = $tabOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $tabData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php 
                        $isActive = $tab === $val;
                        $count = $counts[$val] ?? 0;
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('web.payments.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val]))); ?>" 
                           class="nav-link text-active-primary d-flex align-items-center <?php echo e($isActive ? 'active' : ''); ?>">
                            <i class="ki-solid <?php echo e($tabData['icon']); ?> fs-4 me-2"></i>
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
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-solid ki-wallet fs-2 me-2"></i>
                Riwayat Transaksi
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start min-w-120px">Payment ID</th>
                            <th class="min-w-150px">Tanggal</th>
                            <th class="min-w-250px">Deskripsi / Referensi</th>
                            <th class="min-w-120px">Metode</th>
                            <th class="min-w-100px">Tipe</th>
                            <th class="text-end min-w-150px">Amount</th>
                            <th class="min-w-100px">Status</th>
                            <th class="text-end pe-4 rounded-end min-w-100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $payments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="text-gray-900 fw-bold fs-6"><?php echo e($payment->payment_number ?? 'PAY-' . $payment->id); ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-6"><?php echo e($payment->payment_date->format('d/m/Y')); ?></div>
                                    <div class="text-muted fs-7"><?php echo e($payment->payment_date->format('H:i')); ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-6 mb-1"><?php echo e($payment->description ?? 'Tanpa deskripsi'); ?></div>
                                    <div class="text-muted fs-7">
                                        <i class="ki-solid ki-document fs-7 me-1"></i>
                                        Ref: <?php echo e($payment->reference_number ?? '—'); ?>

                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-info"><?php echo e(strtoupper($payment->payment_method)); ?></span>
                                </td>
                                <td>
                                    <?php
                                        $typeColor = $payment->type === 'incoming' ? 'success' : 'danger';
                                        $typeIcon = $payment->type === 'incoming' ? 'ki-arrow-down' : 'ki-arrow-up';
                                    ?>
                                    <span class="badge badge-<?php echo e($typeColor); ?>">
                                        <i class="ki-solid <?php echo e($typeIcon); ?> fs-7 me-1"></i>
                                        <?php echo e(strtoupper($payment->type)); ?>

                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold fs-6 <?php echo e($payment->type === 'incoming' ? 'text-success' : 'text-danger'); ?>">
                                        <?php echo e($payment->type === 'incoming' ? '+' : '-'); ?> Rp <?php echo e(number_format($payment->amount, 0, ',', '.')); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $statusColor = match($payment->status ?? 'confirmed') {
                                            'confirmed' => 'success',
                                            'pending' => 'warning',
                                            'cancelled' => 'danger',
                                            default => 'primary'
                                        };
                                    ?>
                                    <span class="badge badge-<?php echo e($statusColor); ?>"><?php echo e(strtoupper($payment->status ?? 'CONFIRMED')); ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ki-solid ki-dots-vertical fs-3"></i>
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#paymentDetailModal<?php echo e($payment->id); ?>">
                                                <i class="ki-solid ki-eye fs-4 me-2 text-primary"></i>
                                                Lihat Detail
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-solid ki-wallet fs-3x text-gray-400 mb-3"></i>
                                        <h3 class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Transaksi</h3>
                                        <p class="text-muted fs-7">Transaksi pembayaran akan muncul setelah proses penerimaan atau pengeluaran tercatat.</p>
                                        <div class="d-flex gap-2 mt-3">
                                            <a href="<?php echo e(route('web.payments.create.incoming')); ?>" class="btn btn-success">
                                                <i class="ki-solid ki-arrow-down fs-2"></i>
                                                Catat Kas Masuk
                                            </a>
                                            <a href="<?php echo e(route('web.payments.create.outgoing')); ?>" class="btn btn-primary">
                                                <i class="ki-solid ki-arrow-up fs-2"></i>
                                                Catat Kas Keluar
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            
            <?php if(isset($payments) && $payments->hasPages()): ?>
            <div class="d-flex flex-stack flex-wrap pt-7">
                <div class="text-muted fs-7">
                    Menampilkan <?php echo e($payments->firstItem()); ?> - <?php echo e($payments->lastItem()); ?> dari <?php echo e($payments->total()); ?> data
                </div>
                <div>
                    <?php echo e($payments->links()); ?>

                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['pageTitle' => 'Payment Ledger'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/payments/index.blade.php ENDPATH**/ ?>