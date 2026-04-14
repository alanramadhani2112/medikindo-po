<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">
                <i class="ki-solid ki-arrow-up fs-2 text-success me-2"></i>
                Tagihan ke RS/Klinik (AR)
            </h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola tagihan yang diterbitkan ke RS/Klinik</p>
        </div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_invoices')): ?>
        <div>
            <a href="<?php echo e(route('web.invoices.customer.create')); ?>" class="btn btn-success">
                <i class="ki-solid ki-plus fs-3"></i>
                Buat Tagihan ke RS/Klinik
            </a>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start">Nomor Invoice</th>
                            <th>RS/Klinik</th>
                            <th>PO Number</th>
                            <th>GR Number</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $customerInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4">
                                    <a href="<?php echo e(route('web.invoices.customer.show', $invoice)); ?>" 
                                       class="fw-bold text-gray-900 text-hover-primary">
                                        <?php echo e($invoice->invoice_number); ?>

                                    </a>
                                    <div class="text-muted fs-7 mt-1"><?php echo e($invoice->created_at->format('d M Y')); ?></div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-gray-700"><?php echo e($invoice->organization?->name ?? '—'); ?></span>
                                </td>
                                <td>
                                    <span class="text-gray-600"><?php echo e($invoice->purchaseOrder?->po_number ?? '—'); ?></span>
                                </td>
                                <td>
                                    <span class="text-primary"><?php echo e($invoice->goodsReceipt?->gr_number ?? '—'); ?></span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-gray-900">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></span>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $statusColor = match($invoice->status) {
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            default => 'warning'
                                        };
                                    ?>
                                    <span class="badge badge-<?php echo e($statusColor); ?>"><?php echo e(strtoupper($invoice->status)); ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?php echo e(route('web.invoices.customer.show', $invoice)); ?>" 
                                       class="btn btn-sm btn-light btn-active-light-primary">
                                        <i class="ki-solid ki-eye fs-4"></i>
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-solid ki-document fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Tagihan ke RS/Klinik</span>
                                        <span class="text-gray-500 fs-6">Tagihan akan muncul setelah dibuat dari Goods Receipt.</span>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_invoices')): ?>
                                        <a href="<?php echo e(route('web.invoices.customer.create')); ?>" class="btn btn-sm btn-success mt-4">
                                            <i class="ki-solid ki-plus fs-4"></i>
                                            Buat Tagihan Pertama
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($customerInvoices->hasPages()): ?>
                <div class="d-flex justify-content-center mt-7">
                    <?php echo e($customerInvoices->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/invoices/index_customer.blade.php ENDPATH**/ ?>