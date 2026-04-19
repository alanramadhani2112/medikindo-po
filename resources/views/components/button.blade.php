@props([
    'variant' => 'primary',
    'color' => null,
    'size' => 'md',
    'icon' => null,
    'label' => null,
    'href' => null,
    'type' => 'button',
    'outline' => false,
])

@php
    $baseClasses = 'btn';
    $resolvedVariant = $color ?? $variant;

    // Variant classes
    $variantClasses = match ($resolvedVariant) {
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
    $sizeClasses = match ($size) {
        'xs' => 'btn-sm',
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
        'xl' => 'btn-lg',
        default => '',
    };

    $classes = trim("$baseClasses $variantClasses $sizeClasses");
    $slotText = trim((string) $slot);
    $buttonText = $slotText !== '' ? $slot : $label;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="ki-outline ki-{{ $icon }} fs-3"></i>
        @endif
        {{ $buttonText }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="ki-outline ki-{{ $icon }} fs-3"></i>
        @endif
        {{ $buttonText }}
    </button>
@endif
