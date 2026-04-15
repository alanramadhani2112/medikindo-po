# Conversion Template Analysis

## Overview

This document analyzes the already-converted `resources/views/purchase-orders/index.blade.php` file to extract patterns and best practices for converting remaining views.

---

## File Structure Analysis

### 1. Layout Component Usage

```blade
<x-layout title="Purchase Orders" pageTitle="Purchase Orders" :breadcrumbs="[
    ['label' => 'Procurement'],
    ['label' => 'Purchase Orders']
]">
```

**Pattern:**
- Uses `<x-layout>` component wrapper
- Passes `title` (browser title), `pageTitle` (page heading), and `breadcrumbs` array
- All converted views should use this layout component

---

### 2. Page Header Section

```blade
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
    <div>
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Manajemen Purchase Order</h1>
        <p class="text-gray-600 fs-6 mb-0">Kelola dan pantau seluruh pesanan pengadaan dari satu tempat.</p>
    </div>
    @can('create_po')
    <a href="{{ route('web.po.create') }}" class="btn btn-primary">
        <i class="ki-outline ki-picture fs-2"></i>
        Buat PO Baru
    </a>
    @endcan
</div>
```

**Key Patterns:**

1. **Responsive Flex Layout:**
   - `d-flex flex-column flex-md-row` - Stacks on mobile, row on tablet+
   - `justify-content-between` - Spreads content apart
   - `align-items-start align-items-md-center` - Responsive alignment
   - `gap-4` - Spacing between elements
   - `mb-7` - Bottom margin

2. **Typography:**
   - Page title: `fs-2 fw-bold text-gray-900 mb-2`
   - Description: `text-gray-600 fs-6 mb-0`

3. **Action Button:**
   - Primary button: `btn btn-primary`
   - Icon: `ki-outline ki-picture fs-2`
   - Wrapped in `@can` directive for permission check

**Conversion Rule:**
- All list views should have this header structure
- Adjust permission check to match view's create permission
- Update route to match view's create route

---

### 3. Filter Form Section

```blade
<div class="card card-flush mb-7">
    <div class="card-body">
        <form action="{{ route('web.po.index') }}" method="GET" class="row g-4">
            <div class="col-md-4">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nomor PO atau organisasi..." 
                    class="form-control form-control-solid"
                />
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-solid">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <!-- more options -->
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-filter
 fs-3"></i>
                    Cari
                </button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('web.po.index') }}" class="btn btn-light">
                        <i class="ki-outline ki-arrow-zigzag fs-3"></i>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
```

**Key Patterns:**

1. **Card Container:**
   - `card card-flush mb-7` - Flush card with bottom margin
   - `card-body` - Content wrapper

2. **Form Layout:**
   - `row g-4` - Grid row with gap-4
   - `col-md-{n}` - Responsive columns (full width on mobile, sized on tablet+)

3. **Form Controls:**
   - Text input: `form-control form-control-solid`
   - Select: `form-select form-select-solid`
   - Maintains `value="{{ request('field') }}"` for persistence
   - Maintains `selected` state for dropdowns

4. **Action Buttons:**
   - Submit button: `btn btn-primary` with search icon
   - Reset button: `btn btn-light` with cross icon
   - Buttons wrapped in `d-flex gap-2`
   - Reset button conditionally shown with `@if(request()->filled(...))`

**Conversion Rule:**
- All views with filtering should use this pattern
- Adjust field names and options to match view's filter requirements
- Keep conditional reset button logic

---

### 4. Data Table Section

```blade
<div class="card card-flush">
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-150px">Nomor PO</th>
                        <th class="min-w-150px">Organisasi / Klinik</th>
                        <th class="min-w-120px">Total Amount</th>
                        <th class="min-w-100px">Status</th>
                        <th class="min-w-150px">Tanggal / Pembuat</th>
                        <th class="min-w-100px text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $order)
                        <!-- table rows -->
                    @empty
                        <!-- empty state -->
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($purchaseOrders->hasPages())
            <!-- pagination -->
        @endif
    </div>
</div>
```

**Key Patterns:**

1. **Card Container:**
   - `card card-flush` - Flush card
   - `card-body pt-0` - No top padding (table starts at top)

2. **Table Wrapper:**
   - `table-responsive` - Enables horizontal scroll on mobile

3. **Table Classes:**
   - `table` - Base Bootstrap table
   - `table-row-dashed` - Dashed row borders (Metronic)
   - `table-row-gray-300` - Gray row borders (Metronic)
   - `align-middle` - Vertical center alignment
   - `gs-0 gy-4` - Gutter spacing (Metronic)

4. **Table Header:**
   - `tr class="fw-bold text-muted"` - Bold, muted text
   - `th class="min-w-{n}px"` - Minimum column widths
   - `text-end` on action column - Right-align actions

5. **Loop Structure:**
   - `@forelse` instead of `@foreach` - Handles empty state
   - `@empty` section for no data

**Conversion Rule:**
- All data tables should use this exact structure
- Adjust column headers and widths to match data
- Always include empty state in `@empty` section

---

### 5. Table Row Content

```blade
<tr>
    <td>
        <a href="{{ route('web.po.show', $order) }}" class="text-gray-900 fw-bold text-hover-primary d-block fs-6">
            {{ $order->po_number }}
        </a>
    </td>
    <td>
        <span class="text-gray-800 fw-semibold d-block fs-6">
            {{ $order->organization->name ?? '-' }}
        </span>
    </td>
    <td>
        <span class="text-gray-900 fw-bold d-block fs-6">
            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
        </span>
    </td>
    <td>
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
    </td>
    <td>
        <div class="d-flex flex-column">
            <span class="text-gray-800 fw-semibold fs-7">
                {{ $order->created_at->format('d M Y') }}
            </span>
            <span class="text-gray-600 fs-8">
                {{ $order->user->name ?? '-' }}
            </span>
        </div>
    </td>
    <td class="text-end">
        <a href="{{ route('web.po.show', $order) }}" class="btn btn-sm btn-light-primary">
            Detail
        </a>
    </td>
</tr>
```

**Key Patterns:**

1. **Primary Data (Clickable):**
   - Link: `text-gray-900 fw-bold text-hover-primary d-block fs-6`
   - Uses `text-hover-primary` for hover effect

2. **Secondary Data:**
   - Span: `text-gray-800 fw-semibold d-block fs-6`
   - Less emphasis than primary data

3. **Monetary Values:**
   - Span: `text-gray-900 fw-bold d-block fs-6`
   - Uses `number_format()` for formatting

4. **Status Badges:**
   - Uses PHP `match()` expression for badge class mapping
   - Badge: `badge {{ $badgeClass }} fw-bold`
   - Status mapping:
     - draft → `badge-light-secondary`
     - pending/submitted → `badge-light-warning`
     - approved → `badge-light-success`
     - rejected → `badge-light-danger`
     - default → `badge-light-primary`

5. **Multi-Line Data:**
   - Wrapper: `d-flex flex-column`
   - Primary line: `text-gray-800 fw-semibold fs-7`
   - Secondary line: `text-gray-600 fs-8`

6. **Action Buttons:**
   - Cell: `text-end` class
   - Button: `btn btn-sm btn-light-primary`

**Conversion Rule:**
- Use these exact patterns for similar data types
- Adjust badge mapping to match view's status values
- Keep consistent typography hierarchy

---

### 6. Empty State

```blade
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
```

**Key Patterns:**

1. **Table Row:**
   - Single `<td>` with `colspan` matching total columns
   - `text-center py-10` - Centered with vertical padding

2. **Content Structure:**
   - Wrapper: `d-flex flex-column align-items-center`
   - Icon: `ki-outline ki-file-deleted fs-3x text-gray-400 mb-3`
   - Message: `text-gray-600 fs-5`

**Conversion Rule:**
- All tables should have this empty state structure
- Adjust `colspan` to match number of columns
- Update message text to match view context
- Use `ki-file-deleted` icon for "no data" states

---

### 7. Pagination Section

```blade
@if($purchaseOrders->hasPages())
<div class="d-flex justify-content-between align-items-center mt-5">
    <div class="text-gray-600 fs-7">
        Menampilkan {{ $purchaseOrders->firstItem() }} - {{ $purchaseOrders->lastItem() }} dari {{ $purchaseOrders->total() }} data
    </div>
    <div>
        {{ $purchaseOrders->links() }}
    </div>
</div>
@endif
```

**Key Patterns:**

1. **Conditional Display:**
   - Only show if `hasPages()` returns true

2. **Layout:**
   - Wrapper: `d-flex justify-content-between align-items-center mt-5`
   - Info on left, pagination links on right

3. **Record Count:**
   - Text: `text-gray-600 fs-7`
   - Shows range and total using Laravel pagination methods

4. **Pagination Links:**
   - Uses Laravel's `links()` method
   - Bootstrap styling applied automatically

**Conversion Rule:**
- All paginated views should use this exact structure
- Update variable name to match view's collection
- Keep the record count display format

---

## Complete Conversion Checklist

When converting a view, ensure:

### Structure
- [ ] Uses `<x-layout>` component with title, pageTitle, and breadcrumbs
- [ ] Has page header section with title, description, and action button
- [ ] Has filter form section (if applicable)
- [ ] Has data table section with proper card wrapper
- [ ] Has pagination section (if applicable)

### Styling
- [ ] All Tailwind classes replaced with Bootstrap equivalents
- [ ] Uses Metronic card classes (`card-flush`, `card-custom`)
- [ ] Uses Metronic table classes (`table-row-dashed`, `table-row-gray-300`)
- [ ] Uses proper typography classes (`fs-{n}`, `fw-{weight}`)
- [ ] Uses proper color classes (`text-gray-{n}`, `bg-light-{color}`)

### Components
- [ ] Icons use Keenicons format (`ki-outline ki-{icon}`)
- [ ] Buttons use Bootstrap classes (`btn btn-{variant}`)
- [ ] Badges use Metronic light variants (`badge-light-{color}`)
- [ ] Form controls use solid variants (`form-control-solid`, `form-select-solid`)

### Responsive
- [ ] Header uses responsive flex (`flex-column flex-md-row`)
- [ ] Filter form uses responsive columns (`col-md-{n}`)
- [ ] Table wrapped in `table-responsive`
- [ ] Proper breakpoint classes applied

### Functionality
- [ ] All Blade directives preserved (`@can`, `@if`, `@foreach`, `@forelse`)
- [ ] All route references intact
- [ ] Form values persist with `request()` helper
- [ ] Empty state displays correctly
- [ ] Pagination works correctly

### Accessibility
- [ ] Proper heading hierarchy
- [ ] Form labels associated with inputs
- [ ] Sufficient color contrast
- [ ] Keyboard navigation works

---

## Common Conversion Patterns Summary

### Page Header
```blade
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
    <div>
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">[Title]</h1>
        <p class="text-gray-600 fs-6 mb-0">[Description]</p>
    </div>
    @can('[permission]')
    <a href="{{ route('[route]') }}" class="btn btn-primary">
        <i class="ki-outline ki-picture fs-2"></i>
        [Action Text]
    </a>
    @endcan
</div>
```

### Filter Form
```blade
<div class="card card-flush mb-7">
    <div class="card-body">
        <form action="{{ route('[route]') }}" method="GET" class="row g-4">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="[Placeholder]" class="form-control form-control-solid" />
            </div>
            <div class="col-md-8 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-filter
 fs-3"></i>
                    Cari
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('[route]') }}" class="btn btn-light">
                        <i class="ki-outline ki-arrow-zigzag fs-3"></i>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
```

### Data Table
```blade
<div class="card card-flush">
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-150px">[Column]</th>
                        <th class="min-w-100px text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>[Content]</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="[n]" class="text-center py-10">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                    <span class="text-gray-600 fs-5">[Empty message]</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($items->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-gray-600 fs-7">
                Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} data
            </div>
            <div>{{ $items->links() }}</div>
        </div>
        @endif
    </div>
</div>
```

---

## Reference Files

**Primary Template:**
- `resources/views/purchase-orders/index.blade.php` - Complete list view example

**Supporting Components:**
- `resources/views/components/layout.blade.php` - Main layout
- `resources/views/components/button.blade.php` - Button component
- `resources/views/components/input.blade.php` - Input component
- `resources/views/components/select.blade.php` - Select component
- `resources/views/components/table.blade.php` - Table component

**Documentation:**
- `BOOTSTRAP_QUICK_REFERENCE.md` - Quick reference
- `docs/CLASS_MAPPING_REFERENCE.md` - Detailed class mappings
- `docs/BROWSER_TESTING_GUIDE.md` - Testing procedures

---

**Last Updated:** 2024
**Version:** 1.0
**Project:** Medikindo Procurement System - Tailwind to Bootstrap Conversion
