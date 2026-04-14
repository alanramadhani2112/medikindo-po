@props([
    'title',
    'value',
    'icon'       => 'ki-solid ki-chart fs-3x text-primary',
    'trend'      => null,
    'trendValue' => null,
    'color'      => 'primary',
])
<div class="card bg-{{ $color }} hoverable">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <div class="text-inverse-{{ $color }} fw-bold fs-2 mb-2">{{ $value }}</div>
                <div class="text-inverse-{{ $color }} fw-semibold fs-7">{{ $title }}</div>
            </div>
            <div class="d-flex align-items-center ms-4">
                <i class="{{ str_contains($icon,'ki-') ? $icon : 'ki-solid ki-'.$icon.' fs-3x' }} opacity-75 text-white"></i>
            </div>
        </div>
        @if($trend && $trendValue)
        <div class="mt-3">
            <span class="badge badge-white fs-8 fw-bold">
                <i class="ki-solid ki-{{ $trend === 'up' ? 'arrow-up text-success' : 'arrow-down text-danger' }} fs-7 me-1"></i>
                {{ $trendValue }}
            </span>
        </div>
        @endif
    </div>
</div>
