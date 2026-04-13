# UI System Implementation Guide

**Version:** 1.0  
**Date:** 2024  
**Purpose:** Step-by-step guide to refactor existing modules and implement new ones

---

## 📋 Overview

This guide provides practical steps to:
1. Refactor existing modules (Dashboard, Purchase Orders) to use the standardized component system
2. Implement new modules following the UI System Standard
3. Validate compliance with the standard

---

## 🔄 Phase 1: Refactor Existing Modules

### Step 1: Refactor Dashboard (resources/views/dashboard/index.blade.php)

**Current State:** Partially uses components, but has raw HTML for cards and tables

**Refactoring Tasks:**

#### 1.1 Replace Page Header
```blade
{{-- BEFORE --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
    <div>
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Dasbor ERP Medikindo</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang kembali, {{ auth()->user()->name }}...</p>
    </div>
    @can('create_po')
    <a href="{{ route('web.po.create') }}" class="btn btn-primary">
        <i class="ki-outline ki-plus fs-2"></i>
        Buat PO Baru
    </a>
    @endcan
</div>

{{-- AFTER --}}
<x-page-header 
    title="Dasbor ERP Medikindo"
    description="Selamat datang kembali, {{ auth()->user()->name }}. Ini adalah ringkasan sistem Anda hari ini."
>
    <x-slot name="actions">
        @can('create_po')
        <x-button variant="primary" icon="plus" href="{{ route('web.po.create') }}">
            Buat PO Baru
        </x-button>
        @endcan
    </x-slot>
</x-page-header>
```

#### 1.2 Replace Card Components
```blade
{{-- BEFORE --}}
<div class="card card-flush mb-5 mb-xl-8">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title fw-bold fs-3">Antrean Persetujuan Terbaru</h3>
    </div>
    <div class="card-body pt-0">
        {{-- Content --}}
    </div>
</div>

{{-- AFTER --}}
<x-card title="Antrean Persetujuan Terbaru" class="card-flush mb-5 mb-xl-8">
    {{-- Content --}}
</x-card>
```

#### 1.3 Replace Empty States
```blade
{{-- BEFORE --}}
@empty
    <div class="text-center py-10">
        <div class="d-flex flex-column align-items-center">
            <i class="ki-outline ki-information-5 fs-3x text-gray-400 mb-3"></i>
            <span class="text-gray-600 fs-5">Belum ada aktivitas tercatat.</span>
        </div>
    </div>
@endforelse

{{-- AFTER --}}
@empty
    <x-empty-state 
        icon="information-5"
        title="Belum Ada Aktivitas"
        message="Belum ada aktivitas tercatat."
    />
@endforelse
```

---

### Step 2: Refactor Purchase Orders Index (resources/views/purchase-orders/index.blade.php)

**Current State:** Uses some components, but filter bar and table are raw HTML

#### 2.1 Replace Page Header
```blade
{{-- BEFORE --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
    <div>
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Manajemen Purchase Order</h1>
        <p class="text-gray-600 fs-6 mb-0">Kelola dan pantau seluruh pesanan pengadaan dari satu tempat.</p>
    </div>
    @can('create_po')
    <a href="{{ route('web.po.create') }}" class="btn btn-primary">
        <i class="ki-outline ki-plus fs-2"></i>
        Buat PO Baru
    </a>
    @endcan
</div>

{{-- AFTER --}}
<x-page-header 
    title="Manajemen Purchase Order"
    description="Kelola dan pantau seluruh pesanan pengadaan dari satu tempat."
>
    <x-slot name="actions">
        @can('create_po')
        <x-button variant="primary" icon="plus" href="{{ route('web.po.create') }}">
            Buat PO Baru
        </x-button>
        @endcan
    </x-slot>
</x-page-header>
```

#### 2.2 Replace Filter Bar
```blade
{{-- BEFORE --}}
<div class="card card-flush mb-7">
    <div class="card-body">
        <form action="{{ route('web.po.index') }}" method="GET" class="row g-4">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nomor PO atau organisasi..." 
                       class="form-control form-control-solid" />
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-solid">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                    Cari
                </button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('web.po.index') }}" class="btn btn-light">
                        <i class="ki-outline ki-cross fs-3"></i>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- AFTER --}}
<x-filter-bar action="{{ route('web.po.index') }}">
    <x-slot name="filters">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari nomor PO atau organisasi..." 
                   class="form-control form-control-solid" />
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-solid">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>
    </x-slot>
</x-filter-bar>
```

#### 2.3 Replace Badges
```blade
{{-- BEFORE --}}
@php
    $badgeClass = match($order->status) {
        'draft' => 'badge-light-secondary',
        'pending', 'submitted' => 'badge-light-warning',
        'approved' => 'badge-light-success',
        'rejected' => 'badge-light-danger',
        default => 'badge-light-primary',
    };
@endphp
<span class="badge {{ $badgeClass }} fw-bold">
    {{ strtoupper($order->status) }}
</span>

{{-- AFTER --}}
<x-badge variant="{{ $order->status }}">
    {{ strtoupper($order->status) }}
</x-badge>
```

#### 2.4 Replace Empty State
```blade
{{-- BEFORE --}}
@empty
    <tr>
        <td colspan="6" class="text-center py-10">
            <div class="d-flex flex-column align-items-center">
                <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                <span class="text-gray-600 fs-5">Tidak ada purchase order ditemukan.</span>
            </div>
        </td>
    </tr>
@endforelse

{{-- AFTER --}}
@empty
    <tr>
        <td colspan="6">
            <x-empty-state 
                icon="file-deleted"
                title="Tidak Ada Data"
                message="Tidak ada purchase order ditemukan."
            />
        </td>
    </tr>
@endforelse
```

---

### Step 3: Refactor Purchase Orders Create/Edit

#### 3.1 Replace Page Header
```blade
{{-- AFTER --}}
<x-page-header 
    title="Buat Purchase Order Baru"
    description="Mohon lengkapi informasi organisasi, pemasok, dan item produk secara cermat."
/>
```

#### 3.2 Replace Card Components
```blade
{{-- AFTER --}}
<x-card title="Informasi Pemesanan" icon="information" class="card-flush mb-7">
    <x-slot name="actions">
        {{-- Optional actions --}}
    </x-slot>
    
    {{-- Form fields --}}
</x-card>
```

#### 3.3 Replace Empty State in Table
```blade
{{-- AFTER --}}
<template x-if="items.length === 0">
    <tr>
        <td colspan="5">
            <x-empty-state 
                icon="package"
                title="Daftar Item Kosong"
                message="Pilih supplier utama, kemudian tekan 'Tambah Produk'."
            />
        </td>
    </tr>
</template>
```

---

### Step 4: Refactor Purchase Orders Show

#### 4.1 Replace Page Header with Badge
```blade
{{-- AFTER --}}
<x-page-header 
    title="{{ $po->po_number }}"
    description="Pesanan diterbitkan pada {{ $po->created_at->format('d M Y, H:i') }} oleh {{ $po->creator?->name ?? 'System' }}"
>
    <x-slot name="actions">
        <x-badge variant="{{ $po->status }}">{{ strtoupper($po->status) }}</x-badge>
        
        @if($po->isDraft())
            @can('submit_po')
                <form method="POST" action="{{ route('web.po.submit', $po) }}">
                    @csrf
                    <x-button type="submit" variant="primary" icon="send">
                        Submit
                    </x-button>
                </form>
            @endcan
        @endif
        
        <x-button variant="light" icon="cloud-download" onclick="window.open('{{ route('web.po.pdf', $po) }}', '_blank')">
            PDF
        </x-button>
        
        <x-button variant="secondary" icon="arrow-left" href="{{ route('web.po.index') }}">
            Kembali
        </x-button>
    </x-slot>
</x-page-header>
```

#### 4.2 Replace Card Components
```blade
{{-- AFTER --}}
<x-card title="Rincian Item Pengadaan" class="card-flush mb-5 mb-xl-8">
    {{-- Table content --}}
</x-card>

@if($po->notes)
<x-card title="Catatan Internal" class="card-flush mb-5 mb-xl-8">
    <p class="text-gray-600 fs-6 mb-0 lh-lg">{{ $po->notes }}</p>
</x-card>
@endif
```

#### 4.3 Replace Approval Badges
```blade
{{-- AFTER --}}
<x-badge variant="{{ $approval->status }}">
    {{ strtoupper($approval->status) }}
</x-badge>
```

---

## 🆕 Phase 2: Implement New Modules

### Template: Index Page

```blade
<x-layout title="Module Name" :breadcrumbs="[
    ['label' => 'Parent'],
    ['label' => 'Module Name']
]">

    {{-- Page Header --}}
    <x-page-header 
        title="Module Management"
        description="Manage and monitor all module items"
    >
        <x-slot name="actions">
            @can('create_module')
            <x-button variant="primary" icon="plus" href="{{ route('module.create') }}">
                Create New
            </x-button>
            @endcan
        </x-slot>
    </x-page-header>

    {{-- Filter Bar --}}
    <x-filter-bar action="{{ route('module.index') }}">
        <x-slot name="filters">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search..." class="form-control form-control-solid">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-solid">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </x-slot>
    </x-filter-bar>

    {{-- Data Table --}}
    <x-card class="card-flush">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-150px">Name</th>
                        <th class="min-w-120px">Status</th>
                        <th class="min-w-150px">Date</th>
                        <th class="min-w-100px text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('module.show', $item) }}" class="text-gray-900 fw-bold text-hover-primary d-block fs-6">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td>
                                <x-badge variant="{{ $item->status }}">{{ strtoupper($item->status) }}</x-badge>
                            </td>
                            <td>
                                <span class="text-gray-800 fw-semibold fs-7">{{ $item->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="text-end">
                                <x-button variant="light-primary" size="sm" href="{{ route('module.show', $item) }}">
                                    Detail
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state 
                                    icon="file-deleted"
                                    title="No Data Found"
                                    message="Try adjusting your filters"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($items->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-gray-600 fs-7">
                Showing {{ $items->firstItem() }} - {{ $items->lastItem() }} of {{ $items->total() }}
            </div>
            <div>{{ $items->links() }}</div>
        </div>
        @endif
    </x-card>

</x-layout>
```

### Template: Create/Edit Page

```blade
<x-layout title="Create Module">

    <x-page-header 
        title="Create New Module"
        description="Fill in the form to create a new module"
    />

    <form method="POST" action="{{ route('module.store') }}">
        @csrf

        <x-card title="Basic Information" icon="information" class="card-flush mb-7">
            <div class="row g-5">
                <div class="col-md-6">
                    <x-input 
                        name="name"
                        label="Module Name"
                        type="text"
                        value="{{ old('name') }}"
                        placeholder="Enter module name..."
                        required
                    />
                </div>
                <div class="col-md-6">
                    <x-select name="status" label="Status" required>
                        <option value="">-- Select Status --</option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </x-select>
                </div>
                <div class="col-12">
                    <x-textarea 
                        name="description"
                        label="Description"
                        rows="4"
                        placeholder="Enter description..."
                    />
                </div>
            </div>
        </x-card>

        <div class="d-flex align-items-center justify-content-end gap-3 pt-4">
            <x-button variant="secondary" href="{{ route('module.index') }}">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary">
                Save
            </x-button>
        </div>

    </form>

</x-layout>
```

### Template: Show/Detail Page

```blade
<x-layout title="Module Detail">

    <x-page-header 
        title="{{ $item->name }}"
        description="Created on {{ $item->created_at->format('d M Y, H:i') }}"
    >
        <x-slot name="actions">
            <x-badge variant="{{ $item->status }}">{{ strtoupper($item->status) }}</x-badge>
            
            @can('update_module')
            <x-button variant="light-primary" icon="pencil" href="{{ route('module.edit', $item) }}">
                Edit
            </x-button>
            @endcan
            
            @can('delete_module')
            <form method="POST" action="{{ route('module.destroy', $item) }}" onsubmit="return confirm('Are you sure?')">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" icon="trash">
                    Delete
                </x-button>
            </form>
            @endcan
            
            <x-button variant="secondary" icon="arrow-left" href="{{ route('module.index') }}">
                Back
            </x-button>
        </x-slot>
    </x-page-header>

    <div class="row g-5 g-xl-8">
        {{-- Main Content --}}
        <div class="col-lg-8">
            <x-card title="Module Details" class="card-flush mb-5 mb-xl-8">
                <div class="row g-5">
                    <div class="col-12">
                        <div class="mb-5">
                            <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Name</span>
                            <div class="text-gray-900 fw-bold fs-4">{{ $item->name }}</div>
                        </div>
                        <div class="mb-5">
                            <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Description</span>
                            <p class="text-gray-600 fs-6 mb-0">{{ $item->description ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <x-card title="Information" class="card-flush bg-light-primary mb-5 mb-xl-8">
                <div class="mb-5">
                    <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Status</span>
                    <x-badge variant="{{ $item->status }}">{{ strtoupper($item->status) }}</x-badge>
                </div>
                <div class="mb-5">
                    <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Created At</span>
                    <div class="text-gray-900 fw-semibold fs-6">{{ $item->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div>
                    <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Updated At</span>
                    <div class="text-gray-900 fw-semibold fs-6">{{ $item->updated_at->format('d M Y, H:i') }}</div>
                </div>
            </x-card>
        </div>
    </div>

</x-layout>
```

---

## ✅ Validation Checklist

Before marking a module as complete, verify:

### Component Usage
- [ ] Uses `<x-page-header>` for page title and actions
- [ ] Uses `<x-filter-bar>` for filters (index pages)
- [ ] Uses `<x-card>` for all content sections
- [ ] Uses `<x-button>` for all buttons
- [ ] Uses `<x-badge>` for all status indicators
- [ ] Uses `<x-empty-state>` for empty data
- [ ] Uses `<x-input>`, `<x-select>`, `<x-textarea>` for forms

### Styling
- [ ] All icons are Keenicons (`ki-outline ki-{name}`)
- [ ] Badge colors follow status mapping
- [ ] Typography hierarchy followed
- [ ] Spacing rules followed (mb-7, mb-5, etc.)
- [ ] Responsive classes applied

### Structure
- [ ] No raw HTML for cards, tables, buttons
- [ ] Proper grid layout (col-lg-8 + col-lg-4 for detail pages)
- [ ] Consistent class patterns
- [ ] Empty states implemented

### Functionality
- [ ] All Blade directives preserved
- [ ] Route references correct
- [ ] Permission checks maintained
- [ ] Forms submit correctly
- [ ] Pagination works

---

## 🚀 Next Steps

1. **Refactor Dashboard** - Apply component replacements
2. **Refactor Purchase Orders** - Apply component replacements
3. **Test refactored modules** - Verify functionality
4. **Document changes** - Update validation reports
5. **Lock standard** - No further changes to component system

---

**IMPORTANT:** After refactoring, all future modules MUST use this component system from the start. No exceptions.
