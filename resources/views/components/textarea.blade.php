@props([
    'name',
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'rows' => 3,
])

@if($label)
<div class="mb-5">
    <label for="{{ $name }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
@endif
    <textarea 
        id="{{ $name }}" 
        name="{{ $name }}" 
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control form-control-solid']) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
    @enderror
@if($label)
</div>
@endif
