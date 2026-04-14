<?php
    $isAR = $type !== 'supplier';
    $typeLabel = $isAR ? 'Account Receivable (AR)' : 'Account Payable (AP)';
    $toTitle   = $isAR ? 'TAGIHAN KEPADA' : 'TAGIHAN KE SUPPLIER';
    $entity    = $isAR
        ? ($invoice->organization?->name ?? '—')
        : ($invoice->supplier?->name ?? '—');
    $entityAddress = $isAR
        ? ($invoice->organization?->address ?? null)
        : ($invoice->supplier?->address ?? null);
    $entityPhone = $isAR
        ? ($invoice->organization?->phone ?? null)
        : ($invoice->supplier?->phone ?? null);
?>

<?php $__env->startSection('title', 'Invoice ' . $invoice->invoice_number); ?>
<?php $__env->startSection('document_name', $isAR ? 'FAKTUR TAGIHAN' : 'BUKTI FAKTUR KEUANGAN'); ?>
<?php $__env->startSection('document_number', $invoice->invoice_number); ?>
<?php $__env->startSection('document_date', $invoice->created_at->format('d F Y')); ?>

<?php $__env->startSection('content'); ?>

    
    <table class="info-section">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="info-box">
                    <div class="info-title">Detail Invoice</div>
                    <strong>Klasifikasi: <?php echo e($typeLabel); ?></strong><br>
                    Tanggal Invoice: <strong><?php echo e($invoice->created_at->format('d F Y')); ?></strong><br>
                    Jatuh Tempo: <strong style="color: red;"><?php echo e($invoice->due_date?->format('d F Y') ?? '—'); ?></strong><br>
                    Status: <strong style="text-transform: uppercase;"><?php echo e($invoice->status); ?></strong>
                    <?php if($isAR && $invoice->goods_receipt_id): ?>
                        <br><span style="color: green; font-size: 10px;">✓ Berdasarkan Penerimaan Barang (GR)</span>
                    <?php endif; ?>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="info-box">
                    <div class="info-title"><?php echo e($toTitle); ?></div>
                    <strong style="font-size: 13px;"><?php echo e($entity); ?></strong>
                    <?php if($entityAddress): ?>
                        <br><span style="font-size: 10px; color: #555;"><?php echo e($entityAddress); ?></span>
                    <?php endif; ?>
                    <?php if($entityPhone): ?>
                        <br><span style="font-size: 10px; color: #555;">Telp: <?php echo e($entityPhone); ?></span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    
    <table class="data-table" style="margin-bottom: 15px;">
        <thead>
            <tr>
                <th colspan="4">Referensi Dokumen</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 25%; font-weight: bold; color: #555; font-size: 10px;">PO Internal</td>
                <td style="width: 25%;"><?php echo e($invoice->purchaseOrder?->po_number ?? '—'); ?></td>
                <td style="width: 25%; font-weight: bold; color: #555; font-size: 10px;">PO RS/Klinik</td>
                <td style="width: 25%;"><?php echo e($invoice->purchaseOrder?->external_po_number ?? '—'); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #555; font-size: 10px;">Nomor GR</td>
                <td><?php echo e($invoice->goodsReceipt?->gr_number ?? '—'); ?></td>
                <td style="font-weight: bold; color: #555; font-size: 10px;">Tanggal GR</td>
                <td><?php echo e($invoice->goodsReceipt?->received_at?->format('d M Y') ?? '—'); ?></td>
            </tr>
        </tbody>
    </table>

    
    <?php if($invoice->lineItems && $invoice->lineItems->count() > 0): ?>
    <div style="margin-bottom: 15px;">
        <h3 style="font-size: 12px; font-weight: bold; margin-bottom: 8px; border-bottom: 2px solid #333; padding-bottom: 4px; text-transform: uppercase;">
            Rincian Barang
        </h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%; text-align: center;">No</th>
                    <th style="width: 22%;">Nama Produk</th>
                    <th style="width: 12%; text-align: center;">No. Batch</th>
                    <th style="width: 11%; text-align: center;">Kadaluarsa</th>
                    <th style="width: 6%; text-align: center;">Qty</th>
                    <th style="width: 6%; text-align: center;">Sat.</th>
                    <th style="width: 14%; text-align: right;">Harga Satuan</th>
                    <th style="width: 8%; text-align: center;">Diskon</th>
                    <th style="width: 17%; text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $invoice->lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="text-align: center;"><?php echo e($index + 1); ?></td>
                    <td><strong><?php echo e($item->product_name); ?></strong></td>
                    <td style="text-align: center; font-size: 10px;"><?php echo e($item->batch_no ?? '—'); ?></td>
                    <td style="text-align: center; font-size: 10px;">
                        <?php echo e($item->expiry_date ? $item->expiry_date->format('d M Y') : '—'); ?>

                    </td>
                    <td style="text-align: center;"><?php echo e(number_format($item->quantity, 0, ',', '.')); ?></td>
                    <td style="text-align: center; font-size: 10px;"><?php echo e($item->unit ?? 'pcs'); ?></td>
                    <td style="text-align: right;">Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></td>
                    <td style="text-align: center;">
                        <?php echo e($item->discount_percentage > 0 ? number_format($item->discount_percentage, 1) . '%' : '—'); ?>

                    </td>
                    <td style="text-align: right; font-weight: bold;">Rp <?php echo e(number_format($item->line_total, 0, ',', '.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 15px; font-size: 10px; color: #555;">
                <?php if($isAR): ?>
                    <strong>Instruksi Pembayaran:</strong><br>
                    Mohon transfer sebesar nilai tagihan ke:<br>
                    <strong>Bank BCA: 0987654321</strong><br>
                    a.n PT Medikindo Sejahtera<br>
                    Cantumkan Nomor Invoice pada berita transfer.
                <?php endif; ?>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <tr>
                        <td style="padding: 5px 8px; border-bottom: 1px dotted #ccc;">Subtotal (Sebelum Diskon)</td>
                        <td style="padding: 5px 8px; text-align: right; border-bottom: 1px dotted #ccc;">
                            Rp <?php echo e(number_format($invoice->subtotal_amount ?? 0, 0, ',', '.')); ?>

                        </td>
                    </tr>
                    <?php if(($invoice->discount_amount ?? 0) > 0): ?>
                    <tr>
                        <td style="padding: 5px 8px; border-bottom: 1px dotted #ccc; color: #dc2626;">Total Diskon</td>
                        <td style="padding: 5px 8px; text-align: right; border-bottom: 1px dotted #ccc; color: #dc2626;">
                            - Rp <?php echo e(number_format($invoice->discount_amount, 0, ',', '.')); ?>

                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if(($invoice->tax_amount ?? 0) > 0): ?>
                    <tr>
                        <td style="padding: 5px 8px; border-bottom: 1px dotted #ccc;">PPN (11%)</td>
                        <td style="padding: 5px 8px; text-align: right; border-bottom: 1px dotted #ccc;">
                            Rp <?php echo e(number_format($invoice->tax_amount, 0, ',', '.')); ?>

                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr style="background-color: #1e293b; color: white;">
                        <td style="padding: 8px; font-weight: bold; font-size: 12px; text-transform: uppercase;">TOTAL TAGIHAN</td>
                        <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 14px;">
                            Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?>

                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 8px; border-bottom: 1px dotted #ccc; color: green;">Sudah Dibayar</td>
                        <td style="padding: 5px 8px; text-align: right; border-bottom: 1px dotted #ccc; color: green;">
                            Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?>

                        </td>
                    </tr>
                    <tr style="background-color: #fef2f2;">
                        <td style="padding: 8px; font-weight: bold; color: #dc2626; text-transform: uppercase;">Sisa Tagihan</td>
                        <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 13px; color: #dc2626;">
                            Rp <?php echo e(number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.')); ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    
    <div class="footer">
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <tr>
                <td style="width: 50%; text-align: center; padding: 0 20px; vertical-align: top;">
                    <div style="margin-bottom: 50px;">Diterbitkan Oleh,</div>
                    <div style="border-top: 1px solid #333; padding-top: 5px;">
                        <strong>Admin Keuangan</strong><br>
                        <span style="color: #666;">PT Medikindo Sejahtera</span><br>
                        <span style="color: #999; font-size: 10px;">Tanggal: _______________</span>
                    </div>
                </td>
                <?php if($isAR): ?>
                <td style="width: 50%; text-align: center; padding: 0 20px; vertical-align: top;">
                    <div style="margin-bottom: 50px;">Diterima Oleh,</div>
                    <div style="border-top: 1px solid #333; padding-top: 5px;">
                        <strong>Nama & Jabatan</strong><br>
                        <span style="color: #666;"><?php echo e($entity); ?></span><br>
                        <span style="color: #999; font-size: 10px;">Tanggal: _______________</span>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
        </table>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('pdf.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/pdf/invoice.blade.php ENDPATH**/ ?>