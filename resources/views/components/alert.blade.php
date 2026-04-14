@props(['variant' => 'primary'])
@php
$icons = ['success'=>'check-circle','danger'=>'cross-circle','warning'=>'information-5','info'=>'information'];
$icon = $icons[$variant] ?? 'information';
@endphp
<div {{ $attributes->merge(['class' => "alert alert-$variant d-flex align-items-center"]) }}>
    <i class="ki-duotone ki-{{ $icon }} fs-2 text-{{ $variant }} me-3"></i>
    <div>{{ $slot }}</div>
</div>
