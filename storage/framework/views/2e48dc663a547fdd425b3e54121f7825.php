

<?php $__env->startSection('content'); ?>




<?php if($role === 'healthcare'): ?>
    <?php echo $__env->make('dashboard.partials.healthcare', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($role === 'approver'): ?>
    <?php echo $__env->make('dashboard.partials.approver', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($role === 'finance'): ?>
    <?php echo $__env->make('dashboard.partials.finance', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($role === 'superadmin'): ?>
    <?php echo $__env->make('dashboard.partials.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <?php echo $__env->make('dashboard.partials.basic', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\medikindo-po\resources\views/dashboard/role-based.blade.php ENDPATH**/ ?>