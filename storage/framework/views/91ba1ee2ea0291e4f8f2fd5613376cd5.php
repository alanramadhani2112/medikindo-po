<?php $__env->startSection('title', 'Purchase Order ' . $po->po_number); ?>
<?php $__env->startSection('document_name', 'PURCHASE ORDER'); ?>
<?php $__env->startSection('document_number', $po->po_number); ?>
<?php $__env->startSection('document_date', $po->created_at->format('d F Y')); ?>

<?php $__env->startSection('content'); ?>

    <table class="info-section">
        <tr>
            <td>
                <div class="info-box">
                    <div class="info-title">Pihak Pemesan (Organisasi)</div>
                    <strong><?php echo e($po->organization?->name ?? '—'); ?></strong><br>
                    Tlp: <?php echo e($po->organization?->phone ?? '—'); ?><br>
                    Alamat: <?php echo e($po->organization?->address ?? '—'); ?>

                </div>
            </td>
            <td>
                <div class="info-box">
                    <div class="info-title">Ditujukan Kepada (Distributor)</div>
                    <strong><?php echo e($po->supplier?->name ?? '—'); ?></strong><br>
                    Tlp: <?php echo e($po->supplier?->contact_phone ?? '—'); ?><br>
                    Alamat: <?php echo e($po->supplier?->address ?? '—'); ?>

                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 45%">Deskripsi Barang (SKU)</th>
                <th style="width: 15%" class="text-center">Kuantitas</th>
                <th style="width: 15%" class="text-right">Harga Satuan</th>
                <th style="width: 20%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $po->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="text-center"><?php echo e($index + 1); ?></td>
                <td>
                    <strong><?php echo e($item->product?->name ?? '—'); ?></strong><br>
                    <span style="font-size: 10px; color: #666;">SKU: <?php echo e($item->product?->sku ?? '—'); ?></span>
                </td>
                <td class="text-center"><?php echo e($item->quantity); ?> <?php echo e($item->product?->unit ?? 'Unit'); ?></td>
                <td class="text-right">Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></td>
                <td class="text-right">Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right font-bold text-lg">TOTAL BIAYA:</td>
                <td class="text-right font-bold text-lg text-blue">Rp <?php echo e(number_format($po->total_amount, 0, ',', '.')); ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px;">
        <strong>Catatan Tambahan:</strong><br>
        <span style="color: #666;"><?php echo e($po->notes ?? 'Tidak ada catatan.'); ?></span>
    </div>

    <div class="footer">
        <div style="float: left; width: 300px; padding-top: 15px;">
            <p style="font-size: 11px;">
                Dicetak pada: <?php echo e(now()->format('d M Y H:i')); ?><br>
                Status Transaksi: <strong style="text-transform: uppercase;"><?php echo e(str_replace('_', ' ', $po->status)); ?></strong>
            </p>
        </div>
        
        <div class="signature-box">
            <div style="font-size: 11px; margin-bottom: 50px;">Otorisasi Pemesanan,</div>
            <div class="signature-line"></div>
            <div style="font-size: 12px; font-weight: bold;"><?php echo e($po->createdBy?->name ?? 'System Admin'); ?></div>
            <div style="font-size: 10px; color: #666;">Medikindo Procurement Officer</div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('pdf.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/pdf/purchase_order.blade.php ENDPATH**/ ?>