<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title'       => null,
    'description' => null,
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
    'title'       => null,
    'description' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
    <div>
        <?php if($title): ?>
        <h1 class="fs-2 fw-bold text-gray-900 mb-2"><?php echo e($title); ?></h1>
        <?php endif; ?>
        <?php if($description): ?>
        <p class="text-gray-600 fs-6 mb-0"><?php echo e($description); ?></p>
        <?php endif; ?>
    </div>
    <?php if(isset($actions)): ?>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <?php echo e($actions); ?>

    </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/components/page-header.blade.php ENDPATH**/ ?>