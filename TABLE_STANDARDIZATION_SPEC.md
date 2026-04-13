# 📊 TABLE STANDARDIZATION SPECIFICATION
## Medikindo Procurement System - Global Table System

---

## 🎯 OBJECTIVE
Menstandarisasi SEMUA tabel di SEMUA modul menjadi SATU sistem terpadu dengan konsistensi 100%.

---

## 📐 GLOBAL STRUCTURE (WAJIB)

Setiap halaman dengan tabel HARUS mengikuti struktur ini:

```
1. Page Header (Title + Description + Action Button)
2. KPI Cards (Optional - untuk dashboard metrics)
3. Filter Bar (Search + Filters + Action Buttons)
4. Tabs Card (Optional - untuk kategorisasi data)
5. Table Card (Header + Table + Pagination)
```

---

## 🎨 TABLE STANDARD CLASSES

### Table Container
```html
<div class="table-responsive">
    <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
```

### Table Header
```html
<thead>
    <tr class="fw-bold text-muted bg-light">
        <th class="ps-4 rounded-start min-w-150px">Column 1</th>
        <th class="min-w-120px">Column 2</th>
        <th class="text-end pe-4 rounded-end min-w-100px">Actions</th>
    </tr>
</thead>
```

### Table Body
```html
<tbody>
    <tr>
        <td class="ps-4">Content</td>
        <td>Content</td>
        <td class="text-end pe-4">Actions</td>
    </tr>
</tbody>
```

---

## 🔍 FILTER BAR STANDARD

```html
<div class="card mb-5">
    <div class="card-body">
        <form action="{{ route('...') }}" method="GET" class="d-flex flex-wrap gap-3">
            <!-- Hidden Inputs -->
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
            
            <!-- LEFT: Search Input -->
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control form-control-solid ps-12" 
                           placeholder="Cari...">
                </div>
            </div>
            
            <!-- MIDDLE: Additional Filters -->
            <select name="filter1" class="form-select form-select-solid" style="max-width: 200px;">
                <option value="">All</option>
            </select>
            
            <!-- Search Button -->
            <button type="submit" class="btn btn-light-primary">
                <i class="ki-outline ki-magnifier fs-2"></i>
                Cari
            </button>
            
            <!-- Reset Button (conditional) -->
            @if(request()->filled('search'))
                <a href="{{ route('...') }}" class="btn btn-light">
                    <i class="ki-outline ki-cross fs-2"></i>
                    Reset
                </a>
            @endif
            
            <!-- RIGHT: Action Button -->
            <div class="ms-auto">
                <a href="{{ route('...create') }}" class="btn btn-primary">
                    <i class="ki-outline ki-plus fs-2"></i>
                    Create New
                </a>
            </div>
        </form>
    </div>
</div>
```

---

## 📑 TABS STANDARD

```html
<div class="card mb-7">
    <div class="card-header border-0 pt-6 pb-2">
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
            @php
                $tabOptions = [
                    'all' => ['label' => 'All', 'icon' => 'ki-element-11'],
                    'active' => ['label' => 'Active', 'icon' => 'ki-check-circle'],
                ];
            @endphp
            @foreach($tabOptions as $val => $tabData)
                @php 
                    $isActive = ($tab ?? 'all') === $val;
                    $count = $counts[$val] ?? 0;
                @endphp
                <li class="nav-item">
                    <a href="{{ route('...', array_merge(request()->except(['tab', 'page']), ['tab' => $val])) }}" 
                       class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                        <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-2"></i>
                        <span class="d-flex flex-column align-items-start">
                            <span class="fs-6 fw-bold">{{ $tabData['label'] }}</span>
                            <span class="fs-7 fw-semibold text-muted">{{ $count }} item</span>
                        </span>
                        <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-auto">
                            {{ $count }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
```

---

## 🎨 BUTTON SYSTEM (WAJIB)

### Primary Actions
```html
<!-- Create/Submit -->
<button class="btn btn-primary">
    <i class="ki-outline ki-plus fs-2"></i>
    Create
</button>

<!-- Approve/Confirm -->
<button class="btn btn-success">
    <i class="ki-outline ki-check fs-2"></i>
    Approve
</button>

<!-- Delete/Reject -->
<button class="btn btn-danger">
    <i class="ki-outline ki-trash fs-2"></i>
    Delete
</button>

<!-- View/Cancel -->
<button class="btn btn-light">
    <i class="ki-outline ki-eye fs-2"></i>
    View
</button>
```

### Small Actions (in table)
```html
<a href="..." class="btn btn-sm btn-light-primary">
    <i class="ki-outline ki-eye fs-4"></i>
    Lihat
</a>

<a href="..." class="btn btn-sm btn-light">
    <i class="ki-outline ki-pencil fs-4"></i>
    Edit
</a>

<button class="btn btn-sm btn-light-danger">
    <i class="ki-outline ki-trash fs-4"></i>
    Hapus
</button>
```

---

## 🏷️ BADGE SYSTEM (WAJIB)

```html
<!-- Success States -->
<span class="badge badge-success">APPROVED</span>
<span class="badge badge-success">PAID</span>
<span class="badge badge-success">COMPLETED</span>

<!-- Warning States -->
<span class="badge badge-warning">PENDING</span>
<span class="badge badge-warning">SUBMITTED</span>
<span class="badge badge-warning">UNPAID</span>

<!-- Danger States -->
<span class="badge badge-danger">REJECTED</span>
<span class="badge badge-danger">OVERDUE</span>
<span class="badge badge-danger">CANCELLED</span>

<!-- Secondary States -->
<span class="badge badge-secondary">DRAFT</span>
<span class="badge badge-secondary">INACTIVE</span>

<!-- Primary States -->
<span class="badge badge-primary">SHIPPED</span>
<span class="badge badge-primary">ACTIVE</span>
```

---

## 📱 RESPONSIVE RULES

### Desktop (>= 992px)
- Show ALL columns
- Full table width
- All actions visible

### Tablet (768px - 991px)
- Hide non-essential columns (add `d-none d-lg-table-cell`)
- Keep: ID, Name, Status, Actions
- Hide: Dates, Secondary info

### Mobile (< 768px)
- Consider card view (future enhancement)
- Show only critical info
- Stack actions vertically

---

## 📊 COLUMN STANDARDS

### Column Order (LEFT to RIGHT)
1. **ID/Number** (Primary identifier)
2. **Name/Title** (Main info)
3. **Related Info** (Organization, Supplier, etc.)
4. **Status** (Badge)
5. **Amount** (Right-aligned)
6. **Date** (Optional, hide on mobile)
7. **Actions** (Always last, right-aligned)

### Column Widths
```html
<th class="min-w-150px">Standard column</th>
<th class="min-w-200px">Wide column</th>
<th class="min-w-100px">Narrow column</th>
```

### Text Alignment
- **Left**: Text, Names, IDs
- **Right**: Numbers, Amounts, Actions
- **Center**: Status badges (optional)

---

## 🔢 PAGINATION STANDARD

```html
@if($items->hasPages())
<div class="d-flex justify-content-between align-items-center mt-7">
    <div class="text-muted fs-7">
        Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} data
    </div>
    <div>
        {{ $items->links() }}
    </div>
</div>
@endif
```

---

## 🚫 EMPTY STATE STANDARD

```html
<tr>
    <td colspan="X" class="text-center py-10">
        <div class="d-flex flex-column align-items-center">
            <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
            <h3 class="fs-5 fw-bold text-gray-800 mb-1">No Data Found</h3>
            <p class="text-muted fs-7">Description of empty state.</p>
        </div>
    </td>
</tr>
```

---

## ✅ VALIDATION CHECKLIST

Setiap tabel HARUS memenuhi:

- [ ] Menggunakan class table standard
- [ ] Header dengan bg-light dan rounded corners
- [ ] Kolom aksi di sebelah kanan
- [ ] Angka rata kanan
- [ ] Status menggunakan badge system
- [ ] Filter bar dengan search icon
- [ ] Tombol mengikuti button system
- [ ] Pagination dengan info count
- [ ] Empty state dengan icon
- [ ] Responsive classes applied
- [ ] Consistent spacing (gs-7 gy-4)

---

## 🎯 MODULE-SPECIFIC IMPLEMENTATIONS

### Purchase Orders
- Tabs: All, Draft, Submitted, Approved, Rejected, Completed
- Columns: PO Number, Organization, Supplier, Status, Total, Date, Actions
- Special: Narcotics badge

### Approvals
- Tabs: Pending, History
- Columns: PO Number, Transaction Info, Status, Approval Level, Amount, Actions
- Special: Inline approval forms

### Invoices (AR)
- Tabs: All, Unpaid, Paid, Overdue
- Columns: Invoice Number, Organization, Due Date, Amount, Status, Actions
- Special: KPI cards at top

### Invoices (AP)
- Tabs: All, Unpaid, Paid, Overdue
- Columns: Supplier, Invoice Number, PO Ref, Amount, Status, Actions

### Goods Receipt
- Tabs: Pending, Partial, Completed
- Columns: GR Number, PO Number, Supplier, Status, Date, Actions

### Payments
- Tabs: All, Pending, Confirmed
- Columns: Payment ID, Invoice, Amount, Method, Status, Date, Actions

### Credit Control
- No tabs
- Columns: Organization, Credit Limit, Used, Remaining, Status, Actions

### Organizations
- Tabs: All, Hospital, Clinic
- Columns: Name, Type, Contact, Status, Actions

### Suppliers
- No tabs (or All, Active, Inactive)
- Columns: Name, Contact, Email, Status, Actions

### Products
- Tabs: All, Narcotics, Non-Narcotics
- Columns: Name, Category, Narcotic, Price, Supplier, Actions

### Users
- Tabs: All, Active, Inactive
- Columns: User (avatar+name+email), Role, Organization, Status, Joined, Actions

### Notifications
- Tabs: All, Unread, Read
- Columns: Title, Type, Status, Date, Actions

---

## 🔄 IMPLEMENTATION PRIORITY

1. **Phase 1**: Core modules (PO, Approvals, Invoices)
2. **Phase 2**: Supporting modules (GR, Payments, Credit Control)
3. **Phase 3**: Master data (Organizations, Suppliers, Products, Users)
4. **Phase 4**: Secondary (Notifications, Reports)

---

**Status**: READY FOR IMPLEMENTATION
**Date**: 2024-04-13
**Version**: 1.0