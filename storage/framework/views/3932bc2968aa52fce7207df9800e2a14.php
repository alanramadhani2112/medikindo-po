<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'href' => null,
    'type' => 'button',
    'outline' => false,
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
    'size' => 'md',
    'icon' => null,
    'href' => null,
    'type' => 'button',
    'outline' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $baseClasses = 'btn';
    
    // Variant classes
    $variantClasses = match($variant) {
        'primary' => $outline ? 'btn-outline btn-outline-primary' : 'btn-primary',
        'secondary' => $outline ? 'btn-outline btn-outline-secondary' : 'btn-secondary',
        'success' => $outline ? 'btn-outline btn-outline-success' : 'btn-success',
        'danger' => $outline ? 'btn-outline btn-outline-danger' : 'btn-danger',
        'warning' => $outline ? 'btn-outline btn-outline-warning' : 'btn-warning',
        'info' => $outline ? 'btn-outline btn-outline-info' : 'btn-info',
        'light' => 'btn-light',
        'dark' => 'btn-dark',
        default => $outline ? 'btn-outline btn-outline-primary' : 'btn-primary',
    };
    
    // Size classes
    $sizeClasses = match($size) {
        'xs' => 'btn-sm',
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
        'xl' => 'btn-lg',
        default => '',
    };
    
    $classes = trim("$baseClasses $variantClasses $sizeClasses");
?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => $classes])); ?>>
        <?php if($icon): ?>
            <i class="ki-outline ki-<?php echo e($icon); ?> fs-3"></i>
        <?php endif; ?>
        <?php echo e($slot); ?>

    </a>
<?php else: ?>
    <button type="<?php echo e($type); ?>" <?php echo e($attributes->merge(['class' => $classes])); ?>>
        <?php if($icon): ?>
            <i class="ki-outline ki-<?php echo e($icon); ?> fs-3"></i>
        <?php endif; ?>
        <?php echo e($slot); ?>

    </button>
<?php endif; ?>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/components/button.blade.php ENDPATH**/ ?>