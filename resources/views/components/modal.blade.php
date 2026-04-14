@props([
    'id',
    'title'  => 'Modal',
    'size'   => 'md', // sm, md, lg, xl
])
@php $sizes = ['sm'=>'mw-400px','md'=>'mw-650px','lg'=>'mw-900px','xl'=>'mw-1100px']; @endphp
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog {{ $sizes[$size] ?? 'mw-650px' }}">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-18 pt-0 pb-15">
                <div class="text-center mb-10">
                    <h1 class="mb-3">{{ $title }}</h1>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
