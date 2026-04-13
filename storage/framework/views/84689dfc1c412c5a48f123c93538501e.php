<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title'   => null,
    'icon'    => null,
    'footer'  => false,
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
    'title'   => null,
    'icon'    => null,
    'footer'  => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'card'])); ?>>
    <?php if($title || isset($actions)): ?>
    <div class="card-header border-0 pt-5">
        <h3 class="card-title fw-bold fs-3">
            <?php if($icon): ?>
            <i class="ki-outline ki-<?php echo e($icon); ?> fs-2 me-2 text-primary"></i>
            <?php endif; ?>
            <?php echo e($title); ?>

        </h3>
        <?php if(isset($actions)): ?>
        <div class="card-toolbar d-flex align-items-center gap-2">
            <?php echo e($actions); ?>

        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="card-body pt-0">
        <?php echo e($slot); ?>

    </div>
    <?php if(isset($cardFooter)): ?>
    <div class="card-footer"><?php echo e($cardFooter); ?></div>
    <?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/components/card.blade.php ENDPATH**/ ?>