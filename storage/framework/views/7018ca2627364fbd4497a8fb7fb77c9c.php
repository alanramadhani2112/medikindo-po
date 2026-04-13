<?php $__env->startSection('content'); ?>
        
    <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-outline ki-check-circle fs-2 me-3"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="row g-5 mb-7">
        <div class="col-md-6">
            <div class="card bg-primary">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Total Fasilitas Kredit Aktif</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp <?php echo e(number_format($limits->where('is_active', true)->sum('max_limit'), 0, ',', '.')); ?></div>
                    <span class="badge badge-light-primary mt-2"><?php echo e($limits->where('is_active', true)->count()); ?> organisasi aktif</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-danger">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Total AR Berjalan (Piutang)</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp <?php echo e(number_format($limits->sum('total_active_ar'), 0, ',', '.')); ?></div>
                    <span class="badge badge-light-danger mt-2">Estimasi piutang aktif keseluruhan</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-shield-tick fs-2 me-2"></i>
                        Limit Kredit Per Organisasi
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-primary"><?php echo e($limits->count()); ?> organisasi dikonfigurasi</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start min-w-200px">Organisasi</th>
                                    <th class="text-end min-w-120px">Plafon Kredit</th>
                                    <th class="text-end min-w-120px">AR Berjalan</th>
                                    <th class="min-w-150px">Utilisasi</th>
                                    <th class="text-center pe-4 rounded-end min-w-100px">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $limits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $limit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $usagePercent = $limit->max_limit > 0
                                            ? ($limit->total_active_ar / $limit->max_limit) * 100
                                            : ($limit->total_active_ar > 0 ? 100 : 0);
                                        $barColor = $usagePercent > 90 ? 'bg-danger' : ($usagePercent > 70 ? 'bg-warning' : 'bg-primary');
                                        $textColor = $usagePercent > 90 ? 'text-danger' : ($usagePercent > 70 ? 'text-warning' : 'text-primary');
                                    ?>
                                    <tr class="<?php echo e($usagePercent > 90 ? 'bg-light-danger' : ''); ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="symbol symbol-40px">
                                                    <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                                        <?php echo e(strtoupper(substr($limit->organization?->name ?? 'O', 0, 2))); ?>

                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-gray-900 fs-6"><?php echo e($limit->organization?->name ?? '—'); ?></span>
                                                    <span class="text-muted fs-7"><?php echo e($limit->organization?->type ?? ''); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-primary fs-6">Rp <?php echo e(number_format($limit->max_limit, 0, ',', '.')); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold <?php echo e($usagePercent > 90 ? 'text-danger' : ($usagePercent > 70 ? 'text-warning' : 'text-primary')); ?> fs-6">
                                                Rp <?php echo e(number_format($limit->total_active_ar, 0, ',', '.')); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="flex-grow-1">
                                                    <div class="progress h-8px">
                                                        <div class="progress-bar <?php echo e($barColor); ?>" role="progressbar" 
                                                             style="width: <?php echo e(min(100, $usagePercent)); ?>%"></div>
                                                    </div>
                                                </div>
                                                <span class="fw-bold <?php echo e($textColor); ?> fs-7" style="min-width: 40px;"><?php echo e(number_format($usagePercent, 0)); ?>%</span>
                                            </div>
                                            <?php if($usagePercent > 90): ?>
                                                <span class="badge badge-light-danger fs-8 mt-1">⚠ Mendekati Batas!</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <form method="POST" action="<?php echo e(route('web.financial-controls.update', $limit)); ?>">
                                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                                <input type="hidden" name="max_limit" value="<?php echo e($limit->max_limit); ?>">
                                                <?php if($limit->is_active): ?>
                                                    <button type="submit" name="is_active" value="0" 
                                                            class="btn btn-sm btn-light-success">
                                                        <span class="bullet bullet-dot bg-success me-1"></span>
                                                        Aktif
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="is_active" value="1">
                                                    <button type="submit" class="btn btn-sm btn-light-secondary">
                                                        <span class="bullet bullet-dot bg-secondary me-1"></span>
                                                        Nonaktif
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-shield-tick fs-3x text-gray-400 mb-3"></i>
                                                <h3 class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Kebijakan Kredit</h3>
                                                <p class="text-muted fs-7">Terapkan limit kredit untuk setiap organisasi di sini.</p>
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

        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-plus fs-2 me-2"></i>
                        Terapkan Limit Kredit Baru
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('web.financial-controls.store')); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <div class="mb-5">
                            <label class="form-label required fw-semibold fs-6 mb-2">Pilih Organisasi</label>
                            <select name="organization_id" required class="form-select form-select-solid">
                                <option value="">— Pilih Organisasi —</option>
                                <?php $__currentLoopData = $organizations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organization): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($organization->id); ?>"><?php echo e($organization->name); ?> (<?php echo e(ucfirst($organization->type)); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['organization_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <div class="text-danger fs-7 mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-5">
                            <label class="form-label required fw-semibold fs-6 mb-2">Plafon Kredit Maksimum (Rp)</label>
                            <input type="number" name="max_limit" required min="1" 
                                   placeholder="Contoh: 50000000"
                                   class="form-control form-control-solid">
                            <?php $__errorArgs = ['max_limit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <div class="text-danger fs-7 mt-1"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-5 p-4 bg-light-warning rounded border border-warning">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" checked 
                                       id="is_active_check" class="form-check-input">
                                <label for="is_active_check" class="form-check-label fw-semibold text-gray-800">
                                    Aktifkan pemblokiran otomatis jika organisasi melebihi plafon kredit.
                                </label>
                            </div>
                        </div>

                        <div class="separator my-5"></div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ki-outline ki-check fs-3"></i>
                            Simpan Kebijakan Kredit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['pageTitle' => 'Kendali Finansial'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/financial-controls/index.blade.php ENDPATH**/ ?>