<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary',
    'size'    => 'md',
    'dot'     => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'variant' => 'primary',
    'size'    => 'md',
    'dot'     => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$variants = [
    'success'   => 'badge-light-success',
    'danger'    => 'badge-light-danger',
    'warning'   => 'badge-light-warning',
    'info'      => 'badge-light-info',
    'primary'   => 'badge-light-primary',
    'secondary' => 'badge-light-secondary',
    'dark'      => 'badge-dark',
    // Status aliases
    'pending'   => 'badge-light-warning',
    'approved'  => 'badge-light-success',
    'rejected'  => 'badge-light-danger',
    'draft'     => 'badge-light-secondary',
    'active'    => 'badge-light-success',
    'inactive'  => 'badge-light-secondary',
];

$cls = 'badge fw-bold ' . ($variants[$variant] ?? 'badge-light-primary');
?>

<span <?php echo e($attributes->merge(['class' => $cls])); ?>>
    <?php if($dot): ?><span class="bullet bullet-dot me-1"></span><?php endif; ?>
    <?php echo e($slot); ?>

</span>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/components/badge.blade.php ENDPATH**/ ?>