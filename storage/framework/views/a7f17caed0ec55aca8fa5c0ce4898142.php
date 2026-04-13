<?php $__env->startSection('content'); ?>
        
    <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-outline ki-check-circle fs-2 me-3"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <div class="d-flex flex-column gap-5">
                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $isUnread = is_null($notification->read_at);
                        $data = $notification->data;
                        $icon = match($data['type'] ?? '') {
                            'po_submitted' => 'ki-document',
                            'po_approved' => 'ki-check-circle',
                            'po_rejected' => 'ki-cross-circle',
                            default => 'ki-notification-bing',
                        };
                        $badgeColor = match($data['type'] ?? '') {
                            'po_approved', 'po_submitted' => 'success',
                            'po_rejected' => 'danger',
                            default => 'primary',
                        };
                    ?>
                    
                    <div class="card <?php echo e($isUnread ? 'border-primary' : ''); ?>">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-4">
                                
                                <div class="symbol symbol-50px">
                                    <div class="symbol-label bg-light-<?php echo e($badgeColor); ?> text-<?php echo e($badgeColor); ?>">
                                        <i class="ki-outline <?php echo e($icon); ?> fs-2"></i>
                                    </div>
                                </div>
                                
                                
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="text-gray-900 fw-bold mb-1"><?php echo e($data['title'] ?? 'Notifikasi Sistem'); ?></h5>
                                        <span class="badge badge-light-secondary fs-7"><?php echo e($notification->created_at->diffForHumans()); ?></span>
                                    </div>
                                    
                                    <p class="text-gray-700 fs-6 mb-3"><?php echo e($data['message'] ?? ''); ?></p>
                                    
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if(isset($data['action_url'])): ?>
                                            <a href="<?php echo e($data['action_url']); ?>" class="btn btn-sm btn-light-primary">
                                                <i class="ki-outline ki-eye fs-4"></i>
                                                Buka Dokumen
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($isUnread): ?>
                                            <a href="<?php echo e(route('web.notifications.read', $notification->id)); ?>" 
                                               class="btn btn-sm btn-light">
                                                <i class="ki-outline ki-check fs-4"></i>
                                                Tandai Dibaca
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                
                                <?php if($isUnread): ?>
                                    <div class="w-10px h-10px rounded-circle bg-primary"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="card">
                        <div class="card-body text-center py-15">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ki-outline ki-notification-bing fs-3x text-gray-400 mb-5"></i>
                                <h3 class="text-gray-700 fs-3 fw-bold mb-2">Belum ada notifikasi</h3>
                                <p class="text-gray-500 fs-6">Seluruh pesan dan update aktivitas sistem akan muncul di sini.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                
                <?php if($notifications->hasPages()): ?>
                    <div class="d-flex flex-stack flex-wrap mt-7">
                        <div class="fs-6 fw-semibold text-gray-700">
                            Menampilkan <?php echo e($notifications->firstItem()); ?> - <?php echo e($notifications->lastItem()); ?> dari <?php echo e($notifications->total()); ?> notifikasi
                        </div>
                        <div>
                            <?php echo e($notifications->links()); ?>

                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/notifications/index.blade.php ENDPATH**/ ?>