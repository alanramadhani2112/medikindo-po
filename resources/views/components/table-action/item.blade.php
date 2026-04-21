{{--
    Table Action Item
    @prop href       - URL for link items
    @prop icon       - ki-outline icon name (without ki-outline prefix)
    @prop label      - Display text
    @prop color      - Text color: primary (default), warning, danger, success, info
    @prop form       - Array: ['method'=>'DELETE','action'=>'url'] for form-submit items
    @prop confirm    - SweetAlert confirmation message (optional)
    @prop modalTarget - Bootstrap modal target ID (optional, e.g. '#editModal1')
    @prop disabled   - Disable the item
--}}
@props([
    'href'        => null,
    'icon'        => 'arrow-right',
    'label'       => '',
    'color'       => 'default',
    'form'        => null,
    'confirm'     => null,
    'modalTarget' => null,
    'disabled'    => false,
    'target'      => '_self',
])

@php
    $colorClass = match($color) {
        'danger'  => 'text-danger',
        'warning' => 'text-warning',
        'success' => 'text-success',
        'info'    => 'text-info',
        'primary' => 'text-primary',
        default   => 'text-gray-700',
    };
    $disabledClass = $disabled ? 'opacity-50 pe-none' : '';
@endphp

@if($form)
    {{-- Form-submit item (DELETE, PATCH, etc.) --}}
    <form method="POST" action="{{ $form['action'] }}" class="m-0">
        @csrf
        @if(isset($form['method']) && strtoupper($form['method']) !== 'POST')
            @method($form['method'])
        @endif
        @if(isset($form['fields']))
            @foreach($form['fields'] as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
        @endif
        <button type="submit"
            class="{{ $colorClass }} {{ $disabledClass }} {{ $confirm ? 'action-confirm' : '' }}"
            @if($confirm) data-confirm="{{ $confirm }}" @endif
            {{ $disabled ? 'disabled' : '' }}>
            <i class="ki-outline ki-{{ $icon }} fs-5 me-2"></i>
            {{ $label }}
        </button>
    </form>
@elseif($modalTarget)
    {{-- Modal trigger item --}}
    <button type="button"
        class="{{ $colorClass }} {{ $disabledClass }}"
        data-bs-toggle="modal"
        data-bs-target="{{ $modalTarget }}">
        <i class="ki-outline ki-{{ $icon }} fs-5 me-2"></i>
        {{ $label }}
    </button>
@else
    {{-- Link item --}}
    <a href="{{ $href ?? '#' }}"
        target="{{ $target }}"
        class="{{ $colorClass }} {{ $disabledClass }}">
        <i class="ki-outline ki-{{ $icon }} fs-5 me-2"></i>
        {{ $label }}
    </a>
@endif
