<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Manajemen Persetujuan','pageTitle' => 'Manajemen Persetujuan','breadcrumbs' => $breadcrumbs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Manajemen Persetujuan','pageTitle' => 'Manajemen Persetujuan','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($breadcrumbs)]); ?>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-solid ki-check-circle fs-2 me-3"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="card mb-5">
        <div class="card-body">
            <form action="<?php echo e(route('web.approvals.index')); ?>" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="tab" value="<?php echo e($tab ?? 'pending'); ?>">
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-solid ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nomor PO atau supplier...">
                    </div>
                </div>
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-solid ki-magnifier fs-2"></i>
                    Cari
                </button>
            </form>
        </div>
    </div>

    
    <div class="card mb-5">
        <div class="card-header border-0 pt-6 pb-2">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
                <?php
                    $tabOptions = [
                        'pending' => ['label' => 'Antrian Persetujuan', 'icon' => 'ki-time'],
                        'history' => ['label' => 'Riwayat Keputusan', 'icon' => 'ki-document'],
                    ];
                ?>
                <?php $__currentLoopData = $tabOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $tabData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php 
                        $isActive = ($tab ?? 'pending') === $val;
                        $count = $counts[$val] ?? 0;
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('web.approvals.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val]))); ?>" 
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

    
    <?php if(($tab ?? 'pending') === 'pending'): ?>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-solid ki-time fs-2 me-2"></i>
                    Antrian Persetujuan
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 rounded-start">Nomor PO</th>
                                <th>Informasi Transaksi</th>
                                <th>Status</th>
                                <th>Level Persetujuan</th>
                                <th class="text-end">Nilai PO</th>
                                <th class="text-center pe-4 rounded-end min-w-200px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $pendingApprovals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-4">
                                        <a href="<?php echo e(route('web.po.show', $po)); ?>" class="text-gray-900 text-hover-primary fw-bold fs-6">
                                            <?php echo e($po->po_number); ?>

                                        </a>
                                        <div class="text-muted fs-7 mt-1">
                                            <i class="ki-solid ki-time fs-7 me-1"></i>
                                            <?php echo e($po->created_at->diffForHumans()); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-gray-800 fs-6 mb-1"><?php echo e($po->organization?->name); ?></div>
                                        <div class="text-muted fs-7">
                                            <i class="ki-solid ki-arrow-right-left fs-7 me-1"></i>
                                            <?php echo e($po->supplier?->name); ?>

                                        </div>
                                        <div class="text-muted fs-8 mt-1">
                                            <i class="ki-solid ki-user fs-8 me-1"></i>
                                            <?php echo e($po->creator?->name); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                            $statusColor = match($po->status) {
                                                'draft' => 'secondary',
                                                'submitted' => 'warning',
                                                'approved' => 'success',
                                                'shipped' => 'primary',
                                                'delivered', 'completed' => 'success',
                                                'rejected', 'cancelled' => 'danger',
                                                default => 'primary'
                                            };
                                        ?>
                                        <span class="badge badge-<?php echo e($statusColor); ?>"><?php echo e(strtoupper($po->status)); ?></span>
                                        <?php if($po->has_narcotics): ?>
                                            <span class="badge badge-danger d-block mt-1">⚠ NARKOTIKA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $pendingApproval = $po->approvals->filter(fn($a) => $a->status === 'pending')->first(); ?>
                                        <?php if($pendingApproval): ?>
                                            <div class="badge badge-light-warning fs-7 fw-semibold">
                                                <span class="bullet bullet-dot bg-warning me-2"></span>
                                                Level <?php echo e($pendingApproval->level); ?>

                                            </div>
                                            <div class="text-muted fs-8 mt-1">
                                                <?php echo e($pendingApproval->level === 2 ? 'Verifikasi Narkotika' : 'Review Anggaran'); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-gray-900 fs-6">Rp <?php echo e(number_format($po->total_amount, 0, ',', '.')); ?></span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex flex-column gap-2">
                                            <input type="text" 
                                                   id="notes_<?php echo e($po->id); ?>"
                                                   placeholder="Catatan (opsional)..." 
                                                   class="form-control form-control-sm form-control-solid">
                                            <div class="d-flex gap-2">
                                                <form method="POST" action="<?php echo e(route('web.approvals.process', $po)); ?>" class="flex-fill">
                                                    <?php echo csrf_field(); ?>
                                                    <?php if($pendingApproval): ?> <input type="hidden" name="level" value="<?php echo e($pendingApproval->level); ?>"> <?php endif; ?>
                                                    <input type="hidden" name="decision" value="approved">
                                                    <input type="hidden" name="notes" id="notes_approved_<?php echo e($po->id); ?>">
                                                    <button type="submit" class="btn btn-sm btn-success w-100" 
                                                            onclick="document.getElementById('notes_approved_<?php echo e($po->id); ?>').value = document.getElementById('notes_<?php echo e($po->id); ?>').value;">
                                                        <i class="ki-solid ki-check fs-4"></i>
                                                        Setujui
                                                    </button>
                                                </form>
                                                <form method="POST" action="<?php echo e(route('web.approvals.process', $po)); ?>" class="flex-fill" 
                                                      onsubmit="return confirm('Yakin menolak pengajuan ini?');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php if($pendingApproval): ?> <input type="hidden" name="level" value="<?php echo e($pendingApproval->level); ?>"> <?php endif; ?>
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <input type="hidden" name="notes" id="notes_rejected_<?php echo e($po->id); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger w-100"
                                                            onclick="document.getElementById('notes_rejected_<?php echo e($po->id); ?>').value = document.getElementById('notes_<?php echo e($po->id); ?>').value;">
                                                        <i class="ki-solid ki-cross fs-4"></i>
                                                        Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-10">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ki-solid ki-check-circle fs-3x text-success mb-3"></i>
                                            <h3 class="fs-5 fw-bold text-gray-800 mb-1">Antrian Kosong</h3>
                                            <p class="text-muted fs-7">Tidak ada pengajuan yang memerlukan persetujuan Anda saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-solid ki-document fs-2 me-2"></i>
                    Riwayat Keputusan
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 rounded-start">Nomor PO</th>
                                <th>Informasi Transaksi</th>
                                <th>Status Akhir</th>
                                <th>Jejak Persetujuan</th>
                                <th class="text-end pe-4 rounded-end">Nilai PO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $pendingApprovals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-4">
                                        <a href="<?php echo e(route('web.po.show', $po)); ?>" class="text-gray-900 text-hover-primary fw-bold fs-6">
                                            <?php echo e($po->po_number); ?>

                                        </a>
                                        <div class="text-muted fs-7 mt-1">
                                            <i class="ki-solid ki-time fs-7 me-1"></i>
                                            <?php echo e($po->updated_at->format('d/m/Y H:i')); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-gray-800 fs-6 mb-1"><?php echo e($po->organization?->name); ?></div>
                                        <div class="text-muted fs-7">
                                            <i class="ki-solid ki-arrow-right-left fs-7 me-1"></i>
                                            <?php echo e($po->supplier?->name); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                            $statusColor = match($po->status) {
                                                'draft' => 'secondary',
                                                'submitted' => 'warning',
                                                'approved' => 'success',
                                                'shipped' => 'primary',
                                                'delivered', 'completed' => 'success',
                                                'rejected', 'cancelled' => 'danger',
                                                default => 'primary'
                                            };
                                        ?>
                                        <span class="badge badge-<?php echo e($statusColor); ?>"><?php echo e(strtoupper($po->status)); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <?php $__currentLoopData = $po->approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $appColor = match($appr->status) {
                                                        'approved' => 'badge-success',
                                                        'rejected' => 'badge-danger',
                                                        default => 'badge-warning'
                                                    };
                                                ?>
                                                <div class="badge <?php echo e($appColor); ?> me-2" 
                                                     data-bs-toggle="tooltip" 
                                                     title="Level <?php echo e($appr->level); ?>: <?php echo e($appr->approver?->name ?? 'System'); ?> (<?php echo e(strtoupper($appr->status)); ?>)">
                                                    <?php echo e($appr->level); ?>

                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="fw-bold text-gray-900 fs-6">Rp <?php echo e(number_format($po->total_amount, 0, ',', '.')); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-10">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ki-solid ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                            <span class="text-gray-500 fs-6">Belum ada riwayat keputusan.</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if($pendingApprovals->hasPages()): ?>
        <div class="d-flex flex-stack flex-wrap pt-7">
            <?php echo e($pendingApprovals->links()); ?>

        </div>
    <?php endif; ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $attributes = $__attributesOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__attributesOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $component = $__componentOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__componentOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/approvals/index.blade.php ENDPATH**/ ?>