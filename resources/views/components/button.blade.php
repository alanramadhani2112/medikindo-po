@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'href' => null,
    'type' => 'button',
    'outline' => false,
])

@php
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
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="ki-outline ki-{{ $icon }} fs-3"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="ki-outline ki-{{ $icon }} fs-3"></i>
        @endif
        {{ $slot }}
    </button>
@endif
