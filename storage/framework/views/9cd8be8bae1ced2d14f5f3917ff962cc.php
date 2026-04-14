<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div class="d-flex align-items-center gap-3">
            <h1 class="fs-2 fw-bold text-gray-900 mb-0"><?php echo e($invoice->invoice_number); ?></h1>
            <?php
                $statusColor = match($invoice->status) {
                    'paid' => 'success',
                    'unpaid' => 'warning',
                    'overdue' => 'danger',
                    'draft' => 'secondary',
                    default => 'primary'
                };
            ?>
            <span class="badge badge-<?php echo e($statusColor); ?>"><?php echo e(strtoupper($invoice->status)); ?></span>
        </div>
        <div class="d-flex gap-3">
            <button onclick="window.open('<?php echo e(route('web.invoices.supplier.pdf', $invoice)); ?>', '_blank')" 
                    class="btn btn-light-primary">
                <i class="ki-outline ki-file-down fs-2"></i>
                PDF
            </button>
            <a href="<?php echo e(route('web.invoices.supplier.index')); ?>" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-2"></i>
                Kembali
            </a>
        </div>
    </div>

    
    <div class="card bg-dark mb-7">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <span class="text-gray-400 fs-7 fw-bold text-uppercase">Total Nilai Tagihan AP</span>
                    <h2 class="text-white fs-2x fw-bold mt-2 mb-3">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-gray-400 fs-7">Tenggat:</span>
                        <span class="text-danger fw-bold"><?php echo e($invoice->due_date?->format('d M Y') ?? '—'); ?></span>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row justify-content-end">
                        <div class="col-auto text-end">
                            <span class="text-gray-500 fs-7 fw-bold">Sudah Dibayar</span>
                            <h4 class="text-primary fs-2 fw-bold mt-1">Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></h4>
                        </div>
                        <div class="col-auto text-end border-start border-gray-700 ps-5">
                            <span class="text-gray-500 fs-7 fw-bold">Sisa Tagihan (AP)</span>
                            <h4 class="text-danger fs-2 fw-bold mt-1">Rp <?php echo e(number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-4 mb-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-information-5 fs-2 me-2"></i>
                        Informasi Referensi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-7">
                        <span class="text-gray-600 fs-7 fw-bold">Supplier Penagih</span>
                        <h6 class="text-gray-900 fw-bold fs-5 mt-1"><?php echo e($invoice->supplier?->name ?? '—'); ?></h6>
                    </div>
                    
                    <div class="border-top pt-7">
                        <span class="text-gray-400 fs-8 fw-bold text-uppercase mb-5 d-block">Referensi Dokumen</span>
                        <div class="d-flex flex-column gap-3">
                            <?php if($invoice->goods_receipt_id): ?>
                                <a href="<?php echo e(route('web.goods-receipts.show', $invoice->goods_receipt_id)); ?>" 
                                   class="d-flex align-items-center gap-3 p-3 rounded bg-light-primary text-primary">
                                    <div class="symbol symbol-30px">
                                        <div class="symbol-label bg-white text-primary">
                                            <i class="ki-outline ki-package fs-4"></i>
                                        </div>
                                    </div>
                                    <span class="fs-7 fw-semibold"><?php echo e($invoice->goodsReceipt?->gr_number ?? 'Dokumen GR'); ?></span>
                                </a>
                            <?php else: ?>
                                <div class="d-flex align-items-center gap-3 p-3 rounded bg-light-secondary text-muted">
                                    <div class="symbol symbol-30px">
                                        <div class="symbol-label bg-white text-muted">
                                            <i class="ki-outline ki-package fs-4"></i>
                                        </div>
                                    </div>
                                    <span class="fs-7 fw-semibold">Goods Receipt belum tersedia</span>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo e(route('web.po.show', $invoice->purchase_order_id)); ?>" 
                               class="d-flex align-items-center gap-3 p-3 rounded bg-light-primary text-primary">
                                <div class="symbol symbol-30px">
                                    <div class="symbol-label bg-white text-primary">
                                        <i class="ki-outline ki-document fs-4"></i>
                                    </div>
                                </div>
                                <span class="fs-7 fw-semibold"><?php echo e($invoice->purchaseOrder?->po_number ?? 'Dokumen PO'); ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-wallet fs-2 me-2"></i>
                        Riwayat Pembayaran
                    </h3>
                    <?php if($invoice->status !== 'paid'): ?>
                        <div class="card-toolbar">
                            <a href="<?php echo e(route('web.payments.create.outgoing', ['invoice_id' => $invoice->id])); ?>" 
                               class="btn btn-sm btn-danger">
                                <i class="ki-outline ki-wallet fs-4"></i>
                                Catat Pembayaran Outgoing
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">Nomor Pembayaran / Ref</th>
                                    <th class="text-end">Total Terbayar</th>
                                    <th class="text-end pe-4 rounded-end">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $invoice->paymentAllocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold"><?php echo e($alloc->payment?->payment_number); ?></span>
                                                <span class="text-gray-600 fs-7"><?php echo e($alloc->payment?->payment_method); ?> - <?php echo e($alloc->payment?->reference ?? 'Tanpa Ref'); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-primary fw-bold">+ Rp <?php echo e(number_format($alloc->allocated_amount, 0, ',', '.')); ?></span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <i class="ki-outline ki-time fs-6 text-gray-400"></i>
                                                <span class="text-gray-600 fs-7"><?php echo e($alloc->created_at->format('d M Y H:i')); ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-wallet fs-3x text-gray-400 mb-3"></i>
                                                <span class="text-gray-500 fs-6">Belum ada catatan pembayaran riil terkait tagihan ini.</span>
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
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/invoices/show_supplier.blade.php ENDPATH**/ ?>