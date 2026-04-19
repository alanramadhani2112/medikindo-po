@props([
    'title'       => null,
    'description' => null,
])

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
    <div>
        @if($title)
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">{{ $title }}</h1>
        @endif
        @if($description)
        <p class="text-gray-600 fs-6 mb-0">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
    <div class="d-flex flex-wrap align-items-center gap-2">
        {{ $actions }}
    </div>
    @endisset
</div>
