<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">
                <i class="ki-outline ki-arrow-down fs-2 text-danger me-2"></i>
                Hutang ke Supplier (AP)
            </h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola invoice dari distributor/supplier</p>
        </div>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_invoices')): ?>
        <div>
            <a href="<?php echo e(route('web.invoices.supplier.create')); ?>" class="btn btn-primary">
                <i class="ki-outline ki-plus fs-3"></i>
                Input Invoice Pemasok
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
                            <th>Invoice Distributor</th>
                            <th>Supplier</th>
                            <th>PO Number</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4 rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $supplierInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-4">
                                    <a href="<?php echo e(route('web.invoices.supplier.show', $invoice)); ?>" 
                                       class="fw-bold text-gray-900 text-hover-primary">
                                        <?php echo e($invoice->invoice_number); ?>

                                    </a>
                                    <div class="text-muted fs-7 mt-1"><?php echo e($invoice->created_at->format('d M Y')); ?></div>
                                </td>
                                <td>
                                    <?php if($invoice->distributor_invoice_number): ?>
                                        <span class="fw-semibold text-primary"><?php echo e($invoice->distributor_invoice_number); ?></span>
                                        <?php if($invoice->distributor_invoice_date): ?>
                                            <div class="text-muted fs-7 mt-1"><?php echo e($invoice->distributor_invoice_date->format('d M Y')); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold text-gray-700"><?php echo e($invoice->supplier?->name ?? '—'); ?></span>
                                </td>
                                <td>
                                    <span class="text-gray-600"><?php echo e($invoice->purchaseOrder?->po_number ?? '—'); ?></span>
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
                                    <a href="<?php echo e(route('web.invoices.supplier.show', $invoice)); ?>" 
                                       class="btn btn-sm btn-light btn-active-light-primary">
                                        <i class="ki-outline ki-eye fs-4"></i>
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-document fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Invoice Pemasok</span>
                                        <span class="text-gray-500 fs-6">Invoice akan muncul setelah diinput dari distributor.</span>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_invoices')): ?>
                                        <a href="<?php echo e(route('web.invoices.supplier.create')); ?>" class="btn btn-sm btn-primary mt-4">
                                            <i class="ki-outline ki-plus fs-4"></i>
                                            Input Invoice Pertama
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($supplierInvoices->hasPages()): ?>
                <div class="d-flex justify-content-center mt-7">
                    <?php echo e($supplierInvoices->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/invoices/index_supplier.blade.php ENDPATH**/ ?>