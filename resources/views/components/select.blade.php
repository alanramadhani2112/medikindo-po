@props([
    'name',
    'label' => null,
    'value' => '',
    'required' => false,
    'options' => [],
])

@if($label)
<div class="mb-5">
    <label for="{{ $name }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
@endif
    <select 
        id="{{ $name }}" 
        name="{{ $name }}" 
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-select form-select-solid']) }}
    >
        {{ $slot }}
    </select>
    @error($name)
        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
    @enderror
@if($label)
</div>
@endif
