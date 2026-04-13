@props([
    'variant' => 'primary',
    'size'    => 'md',
    'dot'     => false,
])

@php
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
@endphp

<span {{ $attributes->merge(['class' => $cls]) }}>
    @if($dot)<span class="bullet bullet-dot me-1"></span>@endif
    {{ $slot }}
</span>
