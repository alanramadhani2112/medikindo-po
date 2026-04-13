@props([
    'icon'    => 'information-5',
    'title'   => 'Tidak Ada Data',
    'message' => null,
])

<div class="text-center py-10">
    <div class="d-flex flex-column align-items-center">
        <i class="ki-outline ki-{{ $icon }} fs-3x text-gray-400 mb-3"></i>
        <h6 class="text-gray-800 fw-semibold fs-6 mb-1">{{ $title }}</h6>
        @if($message)
        <p class="text-gray-600 fs-7 mb-0">{{ $message }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
