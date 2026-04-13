@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'icon' => null,
    'error' => null,
])

@if($label)
<div class="mb-5">
    <label for="{{ $name }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
@endif
    <input 
        type="{{ $type }}" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        value="{{ old($name, $value) }}" 
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control form-control-solid']) }}
    />
    @error($name)
        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
    @enderror
@if($label)
</div>
@endif
