<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'icon' => null,
    'error' => null,
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
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'icon' => null,
    'error' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($label): ?>
<div class="mb-5">
    <label for="<?php echo e($name); ?>" class="form-label <?php echo e($required ? 'required' : ''); ?>"><?php echo e($label); ?></label>
<?php endif; ?>
    <input 
        type="<?php echo e($type); ?>" 
        id="<?php echo e($name); ?>" 
        name="<?php echo e($name); ?>" 
        value="<?php echo e(old($name, $value)); ?>" 
        placeholder="<?php echo e($placeholder); ?>"
        <?php echo e($required ? 'required' : ''); ?>

        <?php echo e($attributes->merge(['class' => 'form-control form-control-solid'])); ?>

    />
    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="text-danger fs-7 mt-1"><?php echo e($message); ?></div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
<?php if($label): ?>
</div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/components/input.blade.php ENDPATH**/ ?>