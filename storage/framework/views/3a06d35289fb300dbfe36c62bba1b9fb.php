<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    
    <div class="mb-7">
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Ubah Produk</h1>
        <p class="text-gray-600 fs-6 mb-0">Form ubah data produk</p>
    </div>

    
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-note-2 fs-2 me-2"></i>
                        Ubah Data Produk
                    </h3>
                </div>
                <div class="card-body">
                    
                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger d-flex align-items-start mb-5">
                            <i class="ki-outline ki-information-5 fs-2 me-3"></i>
                            <div>
                                <ul class="mb-0">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('web.products.update', $product)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row">
                            
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold required">Supplier</label>
                                <select name="supplier_id" required class="form-select form-select-solid <?php $__errorArgs = ['supplier_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">— Pilih Supplier —</option>
                                    <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($s->id); ?>" <?php echo e((old('supplier_id', $product->supplier_id) == $s->id) ? 'selected' : ''); ?>>
                                            <?php echo e($s->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['supplier_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6">
                                
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Nama Produk</label>
                                    <input type="text" name="name" value="<?php echo e(old('name', $product->name)); ?>" required
                                           placeholder="Contoh: Amoxicillin 500mg"
                                           class="form-control form-control-solid <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Satuan</label>
                                    <select name="unit" required class="form-select form-select-solid <?php $__errorArgs = ['unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="">— Pilih Satuan —</option>
                                        <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($u); ?>" <?php echo e(old('unit', $product->unit) == $u ? 'selected' : ''); ?>><?php echo e($u); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">SKU / Kode Produk</label>
                                    <input type="text" name="sku" value="<?php echo e(old('sku', $product->sku)); ?>" required maxlength="50"
                                           placeholder="AMX-001"
                                           class="form-control form-control-solid <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            
                            <div class="col-12">
                                <div class="separator separator-dashed my-7"></div>
                                <h3 class="fs-5 fw-bold text-gray-900 mb-5">
                                    <i class="ki-outline ki-chart-line-up fs-3 text-success me-2"></i>
                                    Perhitungan Harga & Profit
                                </h3>
                            </div>

                            <div class="col-md-6">
                                
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Harga Beli (Cost Price)</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="cost_price" id="cost_price" value="<?php echo e(old('cost_price', (int)$product->cost_price)); ?>" 
                                               required min="0" step="1" placeholder="0"
                                               class="form-control <?php $__errorArgs = ['cost_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    </div>
                                    <div class="form-text text-muted">Harga beli dari supplier</div>
                                    <?php $__errorArgs = ['cost_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Diskon (%)</label>
                                    <div class="input-group input-group-solid">
                                        <input type="number" name="discount_percentage" id="discount_percentage" 
                                               value="<?php echo e(old('discount_percentage', $product->discount_percentage)); ?>" 
                                               min="0" max="100" step="0.01" placeholder="0"
                                               class="form-control <?php $__errorArgs = ['discount_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text text-muted">Persentase diskon yang dapat diberikan</div>
                                    <?php $__errorArgs = ['discount_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Harga Jual (Selling Price)</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="selling_price" id="selling_price" value="<?php echo e(old('selling_price', (int)$product->selling_price)); ?>" 
                                               required min="0" step="1" placeholder="0"
                                               class="form-control <?php $__errorArgs = ['selling_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    </div>
                                    <div class="form-text text-muted">Harga jual ke customer</div>
                                    <?php $__errorArgs = ['selling_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Diskon Nominal</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="discount_amount" id="discount_amount" 
                                               value="<?php echo e(old('discount_amount', (int)$product->discount_amount)); ?>" 
                                               readonly
                                               class="form-control bg-light">
                                    </div>
                                    <div class="form-text text-muted">Otomatis dihitung dari persentase diskon</div>
                                </div>
                            </div>

                            
                            <div class="col-12 mb-5">
                                <div class="card bg-light-success border border-success border-dashed">
                                    <div class="card-body p-5">
                                        <div class="row g-5">
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">LABA KOTOR</span>
                                                    <span class="text-success fs-2 fw-bold" id="gross_profit_display">Rp <?php echo e(number_format($product->gross_profit, 0, ',', '.')); ?></span>
                                                    <span class="text-gray-600 fs-8" id="gross_margin_display">Margin: <?php echo e(number_format($product->gross_profit_margin, 2)); ?>%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">HARGA SETELAH DISKON</span>
                                                    <span class="text-primary fs-2 fw-bold" id="final_price_display">Rp <?php echo e(number_format($product->final_price, 0, ',', '.')); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">LABA BERSIH</span>
                                                    <span class="text-success fs-2 fw-bold" id="net_profit_display">Rp <?php echo e(number_format($product->net_profit, 0, ',', '.')); ?></span>
                                                    <span class="text-gray-600 fs-8" id="net_margin_display">Margin: <?php echo e(number_format($product->net_profit_margin, 2)); ?>%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-600 fs-7 fw-semibold mb-2">STATUS PROFIT</span>
                                                    <span class="badge badge-<?php echo e($product->profit_status_color); ?> fs-6 fw-bold" id="profit_status_badge">
                                                        <?php if($product->net_profit_margin >= 20): ?>
                                                            PROFIT TINGGI
                                                        <?php elseif($product->net_profit_margin >= 10): ?>
                                                            PROFIT BAIK
                                                        <?php elseif($product->net_profit_margin >= 5): ?>
                                                            PROFIT RENDAH
                                                        <?php elseif($product->net_profit_margin > 0): ?>
                                                            PROFIT MINIMAL
                                                        <?php else: ?>
                                                            RUGI / NO PROFIT
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="separator separator-dashed my-7"></div>
                            </div>

                            
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold">Kategori Produk</label>
                                <select name="category" class="form-select form-select-solid <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">— Pilih Kategori —</option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($c); ?>" <?php echo e(old('category', $product->category) == $c ? 'selected' : ''); ?>><?php echo e($c); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold">Deskripsi Produk</label>
                                <textarea name="description" rows="2" placeholder="Keterangan tambahan produk..."
                                          class="form-control form-control-solid <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $product->description)); ?></textarea>
                                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            
                            <div class="col-12 mb-5">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" name="is_narcotic" value="1" id="is_narcotic" 
                                           <?php echo e(old('is_narcotic', $product->is_narcotic) ? 'checked' : ''); ?>

                                           class="form-check-input">
                                    <label class="form-check-label" for="is_narcotic">
                                        <span class="fw-bold text-primary">Produk Narkotika / Psikotropika</span>
                                        <span class="d-block text-gray-600 fs-7 mt-1">Centang jika produk ini memerlukan persetujuan approval 2 level khusus.</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        
                        <div class="d-flex justify-content-end gap-3 mt-7 pt-7 border-top">
                            <a href="<?php echo e(route('web.products.index')); ?>" class="btn btn-light">
                                <i class="ki-outline ki-cross fs-2"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-check fs-2"></i>
                                Perbarui Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const costPrice = document.getElementById('cost_price');
    const sellingPrice = document.getElementById('selling_price');
    const discountPercentage = document.getElementById('discount_percentage');
    const discountAmount = document.getElementById('discount_amount');
    
    const grossProfitDisplay = document.getElementById('gross_profit_display');
    const grossMarginDisplay = document.getElementById('gross_margin_display');
    const finalPriceDisplay = document.getElementById('final_price_display');
    const netProfitDisplay = document.getElementById('net_profit_display');
    const netMarginDisplay = document.getElementById('net_margin_display');
    const profitStatusBadge = document.getElementById('profit_status_badge');

    function formatRupiah(number) {
        return 'Rp ' + Math.round(number).toLocaleString('id-ID');
    }

    function calculateProfit() {
        const cost = parseFloat(costPrice.value) || 0;
        const selling = parseFloat(sellingPrice.value) || 0;
        const discountPct = parseFloat(discountPercentage.value) || 0;

        // Calculate discount amount
        const discount = (selling * discountPct) / 100;
        discountAmount.value = Math.round(discount);

        // Calculate gross profit
        const grossProfit = selling - cost;
        const grossMargin = selling > 0 ? (grossProfit / selling) * 100 : 0;

        // Calculate final price after discount
        const finalPrice = selling - discount;

        // Calculate net profit
        const netProfit = finalPrice - cost;
        const netMargin = finalPrice > 0 ? (netProfit / finalPrice) * 100 : 0;

        // Update displays
        grossProfitDisplay.textContent = formatRupiah(grossProfit);
        grossMarginDisplay.textContent = 'Margin: ' + grossMargin.toFixed(2) + '%';
        finalPriceDisplay.textContent = formatRupiah(finalPrice);
        netProfitDisplay.textContent = formatRupiah(netProfit);
        netMarginDisplay.textContent = 'Margin: ' + netMargin.toFixed(2) + '%';

        // Update profit status badge
        let statusText = '';
        let statusColor = '';
        
        if (netMargin >= 20) {
            statusText = 'PROFIT TINGGI';
            statusColor = 'success';
        } else if (netMargin >= 10) {
            statusText = 'PROFIT BAIK';
            statusColor = 'primary';
        } else if (netMargin >= 5) {
            statusText = 'PROFIT RENDAH';
            statusColor = 'warning';
        } else if (netMargin > 0) {
            statusText = 'PROFIT MINIMAL';
            statusColor = 'info';
        } else {
            statusText = 'RUGI / NO PROFIT';
            statusColor = 'danger';
        }

        profitStatusBadge.textContent = statusText;
        profitStatusBadge.className = 'badge badge-' + statusColor + ' fs-6 fw-bold';
    }

    // Attach event listeners
    costPrice.addEventListener('input', calculateProfit);
    sellingPrice.addEventListener('input', calculateProfit);
    discountPercentage.addEventListener('input', calculateProfit);

    // Initial calculation
    calculateProfit();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/products/edit.blade.php ENDPATH**/ ?>