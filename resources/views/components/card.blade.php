@props([
    'title'   => null,
    'icon'    => null,
    'footer'  => false,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || isset($actions))
    <div class="card-header border-0 pt-5">
        <h3 class="card-title fw-bold fs-3">
            @if($icon)
            <i class="ki-duotone ki-{{ $icon }} fs-2 me-2 text-primary"></i>
            @endif
            {{ $title }}
        </h3>
        @isset($actions)
        <div class="card-toolbar d-flex align-items-center gap-2">
            {{ $actions }}
        </div>
        @endisset
    </div>
    @endif
    <div class="card-body pt-0">
        {{ $slot }}
    </div>
    @isset($cardFooter)
    <div class="card-footer">{{ $cardFooter }}</div>
    @endisset
</div>
