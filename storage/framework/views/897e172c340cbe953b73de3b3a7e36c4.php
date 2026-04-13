<?php $__env->startSection('title', 'Tanda Terima ' . $goodsReceipt->gr_number); ?>
<?php $__env->startSection('document_name', 'SURAT JALAN & TANDA TERIMA FAKTUR (GR)'); ?>
<?php $__env->startSection('document_number', $goodsReceipt->gr_number); ?>
<?php $__env->startSection('document_date', $goodsReceipt->received_date->format('d F Y')); ?>

<?php $__env->startSection('content'); ?>

    <table class="info-section">
        <tr>
            <td>
                <div class="info-box">
                    <div class="info-title">Distributor (Pengirim)</div>
                    <strong><?php echo e($goodsReceipt->purchaseOrder?->supplier?->name ?? '—'); ?></strong><br>
                    Tlp: <?php echo e($goodsReceipt->purchaseOrder?->supplier?->contact_phone ?? '—'); ?>

                </div>
            </td>
            <td>
                <div class="info-box">
                    <div class="info-title">Referensi Pemesanan Terkait</div>
                    <strong>P.O. Number: <?php echo e($goodsReceipt->purchaseOrder?->po_number ?? '—'); ?></strong><br>
                    Lokasi: <?php echo e($goodsReceipt->purchaseOrder?->organization?->name ?? 'Gudang Pusat Medikindo'); ?><br>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 40%">Informasi Logistik Produk</th>
                <th style="width: 15%" class="text-center">Kuantitas Dipesan (PO)</th>
                <th style="width: 15%" class="text-center">Kuantitas Masuk Gudang (GR)</th>
                <th style="width: 25%" class="text-center">Kondisi & Catatan Verifikasi Fisik</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $goodsReceipt->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="text-center"><?php echo e($index + 1); ?></td>
                <td>
                    <strong><?php echo e($item->purchaseOrderItem?->product?->name ?? '—'); ?></strong>
                </td>
                <td class="text-center" style="color: #64748b;"><?php echo e($item->purchaseOrderItem?->quantity ?? 0); ?> <?php echo e($item->purchaseOrderItem?->product?->unit ?? 'Unit'); ?></td>
                <td class="text-center font-bold"><?php echo e($item->quantity_received); ?> <?php echo e($item->purchaseOrderItem?->product?->unit ?? 'Unit'); ?></td>
                <td>
                    <strong style="<?php echo e($item->condition !== 'Good' ? 'color: red;' : 'color: green;'); ?>">[<?php echo e(strtoupper($item->condition)); ?>]</strong><br>
                    <span style="font-size: 10px; color: #666;"><?php echo e($item->notes ?? '-'); ?></span>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="footer">
        <div style="float: left; width: 400px; padding-top: 15px;">
            <p style="font-size: 11px;">
                <strong>Keterangan Validasi:</strong><br>
                Barang telah diperiksa kesesuaiannya dengan faktur pengiriman distributor. Tanda terima ini sah dan dapat dilanjutkan ke proses piutang akuntansi (Accounts Payable / Receivable).
            </p>
        </div>
        
        <div class="signature-box">
            <div style="font-size: 11px; margin-bottom: 50px;">Diperiksa & Diterima Oleh,</div>
            <div class="signature-line"></div>
            <div style="font-size: 12px; font-weight: bold;"><?php echo e($goodsReceipt->receivedBy?->name ?? 'Admin Gudang'); ?></div>
            <div style="font-size: 10px; color: #666;">Medikindo Logistics Dept.</div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('pdf.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/pdf/goods_receipt.blade.php ENDPATH**/ ?>