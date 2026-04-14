<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0"><?php echo e($invoice->invoice_number); ?></h1>
                <?php
                    $statusColor = match($invoice->status) {
                        'paid' => 'success',
                        'overdue' => 'danger',
                        default => 'warning'
                    };
                ?>
                <span class="badge badge-<?php echo e($statusColor); ?>"><?php echo e(strtoupper($invoice->status)); ?></span>
                
                <?php if($invoice->goods_receipt_id): ?>
                    <span class="badge badge-light-success">
                        <i class="ki-outline ki-verify fs-7 me-1"></i>
                        Berdasarkan Penerimaan Barang
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-gray-600 fs-6 mb-0">Tagihan Kepada: <span class="text-gray-900 fw-semibold"><?php echo e($invoice->organization?->name ?? '—'); ?></span></p>
        </div>
        <div class="d-flex gap-3">
            <button onclick="window.open('<?php echo e(route('web.invoices.customer.pdf', $invoice)); ?>', '_blank')"
                    class="btn btn-light-primary">
                <i class="ki-outline ki-printer fs-2"></i>
                Cetak PDF
            </button>
            <a href="<?php echo e(route('web.invoices.customer.index')); ?>" class="btn btn-light">
                <i class="ki-outline ki-arrow-left fs-2"></i>
                Kembali
            </a>
        </div>
    </div>

    
    <div class="row mb-7">
        <div class="col-md-4">
            <div class="card bg-dark">
                <div class="card-body">
                    <span class="text-gray-400 fs-8 fw-bold text-uppercase">Total Penagihan</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></div>
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <span class="text-gray-500 fs-8 fw-bold">JATUH TEMPO</span>
                        <span class="badge badge-light-danger"><?php echo e($invoice->due_date?->format('d M Y') ?? '—'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <span class="text-gray-600 fs-7 fw-bold">Terbayar</span>
                    <div class="text-success fs-2 fw-bold mt-2">Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></div>
                    <div class="mt-4">
                        <?php $percent = $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0; ?>
                        <div class="progress h-6px">
                            <div class="progress-bar bg-success" style="width: <?php echo e($percent); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <span class="text-gray-600 fs-7 fw-bold">Sisa Tagihan</span>
                    <div class="text-gray-900 fs-2 fw-bold mt-2">Rp <?php echo e(number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.')); ?></div>
                    <div class="mt-4">
                        <div class="progress h-6px">
                            <div class="progress-bar <?php if($invoice->status === 'overdue'): ?> bg-danger <?php else: ?> bg-gray-300 <?php endif; ?>" style="width: <?php echo e(100 - $percent); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-7">
        
        <div class="col-lg-6 mb-5 mb-lg-0">
            <div class="card border-primary h-100">
                <div class="card-header bg-light-primary">
                    <h3 class="card-title text-primary fw-bold">
                        <i class="ki-outline ki-hospital fs-2 me-2"></i>
                        TAGIHAN KEPADA
                    </h3>
                </div>
                <div class="card-body">
                    <div class="fs-4 fw-bold text-gray-900 mb-2"><?php echo e($invoice->organization?->name ?? '—'); ?></div>
                    <?php if($invoice->organization?->address): ?>
                        <div class="text-gray-600 fs-6 mb-1">
                            <i class="ki-outline ki-geolocation fs-6 me-1"></i>
                            <?php echo e($invoice->organization->address); ?>

                        </div>
                    <?php endif; ?>
                    <?php if($invoice->organization?->phone): ?>
                        <div class="text-gray-600 fs-6 mb-1">
                            <i class="ki-outline ki-phone fs-6 me-1"></i>
                            <?php echo e($invoice->organization->phone); ?>

                        </div>
                    <?php endif; ?>
                    <?php if($invoice->organization?->email): ?>
                        <div class="text-gray-600 fs-6">
                            <i class="ki-outline ki-sms fs-6 me-1"></i>
                            <?php echo e($invoice->organization->email); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-document fs-2 me-2"></i>
                        Dokumen Referensi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        
                        <?php if($invoice->goods_receipt_id): ?>
                            <a href="<?php echo e(route('web.goods-receipts.show', $invoice->goods_receipt_id)); ?>"
                               class="d-flex align-items-center justify-content-between p-3 rounded bg-light-success">
                                <div class="d-flex flex-column">
                                    <span class="text-gray-600 fs-7">Nomor Penerimaan Barang (GR)</span>
                                    <span class="text-gray-900 fw-bold"><?php echo e($invoice->goodsReceipt?->gr_number ?? '—'); ?></span>
                                </div>
                                <i class="ki-outline ki-arrow-right fs-4 text-success"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo e(route('web.po.show', $invoice->purchase_order_id)); ?>"
                           class="d-flex align-items-center justify-content-between p-3 rounded bg-light-primary">
                            <div class="d-flex flex-column">
                                <span class="text-gray-600 fs-7">Nomor PO Internal</span>
                                <span class="text-gray-900 fw-bold"><?php echo e($invoice->purchaseOrder?->po_number ?? '—'); ?></span>
                            </div>
                            <i class="ki-outline ki-arrow-right fs-4 text-primary"></i>
                        </a>
                        
                        <?php if($invoice->purchaseOrder?->external_po_number): ?>
                            <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light-info">
                                <div class="d-flex flex-column">
                                    <span class="text-gray-600 fs-7">Nomor PO RS/Klinik</span>
                                    <span class="text-gray-900 fw-bold"><?php echo e($invoice->purchaseOrder->external_po_number); ?></span>
                                </div>
                                <i class="ki-outline ki-information fs-4 text-info"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light">
                            <div class="d-flex flex-column">
                                <span class="text-gray-600 fs-7">Tanggal Invoice</span>
                                <span class="text-gray-900 fw-bold"><?php echo e($invoice->created_at->format('d M Y')); ?></span>
                            </div>
                            <i class="ki-outline ki-calendar fs-4 text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-package fs-2 me-2"></i>
                Rincian Barang Tertagih
            </h3>
            <div class="card-toolbar">
                <span class="badge badge-light-success fs-7">
                    <i class="ki-outline ki-verify fs-7 me-1"></i>
                    Data dari Penerimaan Barang (GR)
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                    <thead class="bg-light">
                        <tr class="fw-bold text-muted fs-7 text-uppercase">
                            <th class="ps-5 w-40px">No</th>
                            <th>Nama Produk</th>
                            <th class="text-center">No. Batch</th>
                            <th class="text-center">Tgl. Kadaluarsa</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-center">Diskon</th>
                            <th class="text-end pe-5">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $invoice->lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-5 text-gray-600"><?php echo e($index + 1); ?></td>
                                <td>
                                    <span class="text-gray-900 fw-bold"><?php echo e($item->product_name); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light-primary"><?php echo e($item->batch_no ?? '—'); ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if($item->expiry_date): ?>
                                        <?php
                                            $isExpiringSoon = $item->expiry_date->diffInDays(now()) <= 90 && $item->expiry_date->isFuture();
                                            $isExpired = $item->expiry_date->isPast();
                                        ?>
                                        <span class="badge badge-light-<?php echo e($isExpired ? 'danger' : ($isExpiringSoon ? 'warning' : 'success')); ?>">
                                            <?php echo e($item->expiry_date->format('d M Y')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold"><?php echo e(number_format($item->quantity, 0, ',', '.')); ?></td>
                                <td class="text-center text-gray-600"><?php echo e($item->unit ?? 'pcs'); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></td>
                                <td class="text-center">
                                    <?php if($item->discount_percentage > 0): ?>
                                        <span class="badge badge-light-warning"><?php echo e(number_format($item->discount_percentage, 1)); ?>%</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-5 fw-bold text-gray-900">
                                    Rp <?php echo e(number_format($item->line_total, 0, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-package fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">Tidak Ada Item</span>
                                        <span class="text-gray-500 fs-6">Tidak ada rincian barang untuk invoice ini.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-7 mb-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-wallet fs-2 me-2"></i>
                        Riwayat Alokasi Pembayaran
                    </h3>
                    <?php if($invoice->status !== 'paid'): ?>
                        <div class="card-toolbar">
                            <a href="<?php echo e(route('web.payments.create.incoming', ['invoice_id' => $invoice->id])); ?>"
                               class="btn btn-sm btn-primary">
                                <i class="ki-outline ki-plus fs-4"></i>
                                Input Pembayaran
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start">Nomor Ref</th>
                                    <th>Metode</th>
                                    <th class="text-end">Jumlah Terbayar</th>
                                    <th class="text-end pe-4 rounded-end">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $invoice->paymentAllocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-gray-800 fw-bold"><?php echo e($alloc->payment?->payment_number); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-secondary"><?php echo e(strtoupper($alloc->payment?->payment_method)); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold">+ Rp <?php echo e(number_format($alloc->allocated_amount, 0, ',', '.')); ?></span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="text-gray-600"><?php echo e($alloc->created_at->format('d/m/Y')); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-wallet fs-3x text-gray-400 mb-3"></i>
                                                <span class="text-gray-700 fs-5 fw-semibold mb-2">Belum Ada Pembayaran</span>
                                                <span class="text-gray-500 fs-6">Pembayaran untuk invoice ini belum diterima.</span>
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

        
        <div class="col-lg-5 mb-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-calculator fs-2 me-2"></i>
                        Ringkasan Harga
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-gray-200">
                            <span class="text-gray-600 fs-6">Subtotal (Sebelum Diskon)</span>
                            <span class="text-gray-900 fw-semibold">Rp <?php echo e(number_format($invoice->subtotal_amount ?? 0, 0, ',', '.')); ?></span>
                        </div>
                        <?php if(($invoice->discount_amount ?? 0) > 0): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-gray-200">
                            <span class="text-gray-600 fs-6">Total Diskon</span>
                            <span class="text-danger fw-semibold">- Rp <?php echo e(number_format($invoice->discount_amount, 0, ',', '.')); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if(($invoice->tax_amount ?? 0) > 0): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-gray-200">
                            <span class="text-gray-600 fs-6">PPN (11%)</span>
                            <span class="text-gray-900 fw-semibold">Rp <?php echo e(number_format($invoice->tax_amount, 0, ',', '.')); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center py-3 px-4 rounded bg-light-primary mt-2">
                            <span class="text-primary fw-bold fs-5">TOTAL TAGIHAN</span>
                            <span class="text-primary fw-bold fs-4">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></span>
                        </div>
                        <div class="separator my-2"></div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-gray-600 fs-6">Sudah Dibayar</span>
                            <span class="text-success fw-semibold">Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-3 px-4 rounded <?php echo e(($invoice->total_amount - $invoice->paid_amount) > 0 ? 'bg-light-danger' : 'bg-light-success'); ?>">
                            <span class="fw-bold fs-6 <?php echo e(($invoice->total_amount - $invoice->paid_amount) > 0 ? 'text-danger' : 'text-success'); ?>">Sisa Tagihan</span>
                            <span class="fw-bold fs-5 <?php echo e(($invoice->total_amount - $invoice->paid_amount) > 0 ? 'text-danger' : 'text-success'); ?>">
                                Rp <?php echo e(number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.')); ?>

                            </span>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if($invoice->notes): ?>
            <div class="card mt-5">
                <div class="card-body">
                    <span class="text-gray-600 fs-7 fw-bold">Catatan Invoice</span>
                    <p class="text-gray-700 fs-6 mt-2 fst-italic">"<?php echo e($invoice->notes); ?>"</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/invoices/show_customer.blade.php ENDPATH**/ ?>