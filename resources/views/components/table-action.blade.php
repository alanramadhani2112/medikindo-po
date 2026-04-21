{{--
    Table Action Dropdown Component
    Usage:
    <x-table-action>
        <x-table-action.item href="..." icon="eye" label="Lihat Detail" />
        <x-table-action.item href="..." icon="pencil" label="Edit" color="warning" />
        <x-table-action.divider />
        <x-table-action.item icon="trash" label="Hapus" color="danger" :form="['method'=>'DELETE','action'=>route(...)]" confirm="Hapus item ini?" />
    </x-table-action>
--}}
@props(['id' => 'action-' . uniqid()])

<div class="action-menu-wrapper">
    <button type="button"
        class="btn btn-sm btn-light btn-active-light-primary"
        data-action-toggle="{{ $id }}">
        Aksi
        <i class="ki-outline ki-down fs-5 ms-1"></i>
    </button>
    <div class="action-dropdown-menu" id="{{ $id }}">
        {{ $slot }}
    </div>
</div>
