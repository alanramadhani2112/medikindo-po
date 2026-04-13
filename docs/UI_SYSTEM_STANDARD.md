# UI System Standard - ERP Medikindo

**Version:** 1.0  
**Date:** 2024  
**Status:** LOCKED - All future modules MUST follow this standard

---

## 🎯 Purpose

This document defines the **STANDARDIZED UI SYSTEM** for ERP Medikindo. All modules MUST use these components and patterns. NO raw HTML allowed in views.

---

## 📐 Global Page Structure

ALL pages MUST follow this exact structure:

```blade
<x-layout title="Page Title">
    
    {{-- 1. PAGE HEADER (Required) --}}
    <x-page-header 
        title="Main Title"
        description="Subtitle or description"
    >
        <x-slot name="actions">
            {{-- Action buttons --}}
        </x-slot>
    </x-page-header>

    {{-- 2. FILTER BAR (Optional - for index pages) --}}
    <x-filter-bar action="{{ route('...') }}">
        {{-- Filter inputs --}}
    </x-filter-bar>

    {{-- 3. MAIN CONTENT CARD --}}
    <x-card title="Section Title" icon="icon-name">
        <x-slot name="actions">
            {{-- Card actions --}}
        </x-slot>
        
        {{-- Content --}}
    </x-card>

</x-layout>
```

---

## 🧩 Component Library

### 1. Layout Component

**File:** `resources/views/components/layout.blade.php`

**Usage:**
```blade
<x-layout 
    title="Page Title"
    :breadcrumbs="[
        ['label' => 'Parent'],
        ['label' => 'Current Page']
    ]"
>
    {{-- Page content --}}
</x-layout>
```

**Props:**
- `title` (string, required) - Browser title
- `breadcrumbs` (array, optional) - Breadcrumb items

---

### 2. Page Header Component

**File:** `resources/views/components/page-header.blade.php`

**Standard Structure:**
```blade
<x-page-header 
    title="Main Page Title"
    description="Optional subtitle or description"
>
    <x-slot name="actions">
        <x-button variant="primary" icon="plus" href="{{ route('...') }}">
            Create New
        </x-button>
    </x-slot>
</x-page-header>
```

**Props:**
- `title` (string, required) - Main page title
- `description` (string, optional) - Subtitle/description
- `actions` (slot, optional) - Action buttons

**CSS Classes:**
- Container: `d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7`
- Title: `fs-2 fw-bold text-gray-900 mb-2`
- Description: `text-gray-600 fs-6 mb-0`
- Actions: `d-flex flex-wrap align-items-center gap-2`

---

### 3. Card Component

**File:** `resources/views/components/card.blade.php`

**Standard Structure:**
```blade
<x-card 
    title="Card Title" 
    icon="icon-name"
    class="card-flush mb-7"
>
    <x-slot name="actions">
        {{-- Card toolbar buttons --}}
    </x-slot>
    
    {{-- Card content --}}
</x-card>
```

**Props:**
- `title` (string, optional) - Card title
- `icon` (string, optional) - Keenicon name (without ki-outline prefix)
- `class` (string, optional) - Additional classes (default: `card`)
- `actions` (slot, optional) - Card toolbar actions

**CSS Classes:**
- Card: `card card-flush mb-5 mb-xl-8`
- Header: `card-header border-0 pt-5`
- Title: `card-title fw-bold fs-3`
- Body: `card-body pt-0`

**Variants:**
- Default: `card card-flush`
- Colored: `card card-flush bg-primary` (or bg-light-primary)

---

### 4. Filter Bar Component

**File:** `resources/views/components/filter-bar.blade.php`

**Standard Structure:**
```blade
<x-filter-bar action="{{ route('module.index') }}">
    <x-slot name="filters">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-solid" placeholder="Search...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-solid">
                <option value="">All Status</option>
            </select>
        </div>
    </x-slot>
</x-filter-bar>
```

**Props:**
- `action` (string, required) - Form action URL
- `method` (string, optional) - HTTP method (default: GET)
- `filters` (slot, required) - Filter inputs

**CSS Classes:**
- Container: `card card-flush mb-7`
- Form: `row g-4`
- Inputs: `form-control form-control-solid` or `form-select form-select-solid`
- Actions: `col-md-5 d-flex gap-2`

---

### 5. Data Table Component

**File:** `resources/views/components/data-table.blade.php`

**Standard Structure:**
```blade
<x-data-table 
    :headers="[
        ['label' => 'Column 1', 'class' => 'min-w-150px'],
        ['label' => 'Column 2', 'class' => 'min-w-120px'],
        ['label' => 'Actions', 'class' => 'min-w-100px text-end'],
    ]"
    :data="$items"
    :pagination="true"
>
    <x-slot name="row" :item="$item">
        <td>{{ $item->name }}</td>
        <td>{{ $item->status }}</td>
        <td class="text-end">
            <x-button variant="light-primary" size="sm" href="{{ route('...', $item) }}">
                Detail
            </x-button>
        </td>
    </x-slot>
    
    <x-slot name="empty">
        <x-empty-state 
            icon="file-deleted"
            title="No Data Found"
            message="Try adjusting your filters"
        />
    </x-slot>
</x-data-table>
```

**Props:**
- `headers` (array, required) - Table headers with label and class
- `data` (collection, required) - Data collection
- `pagination` (boolean, optional) - Show pagination (default: false)
- `row` (slot, required) - Row template
- `empty` (slot, optional) - Empty state

**CSS Classes:**
- Container: `card card-flush`
- Table: `table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4`
- Header: `fw-bold text-muted`
- Responsive: `table-responsive`

---

### 6. Button Component

**File:** `resources/views/components/button.blade.php`

**Standard Structure:**
```blade
<x-button 
    variant="primary"
    size="md"
    icon="plus"
    href="{{ route('...') }}"
>
    Button Text
</x-button>
```

**Props:**
- `variant` (string, optional) - Button variant (default: primary)
- `size` (string, optional) - Button size: sm, md, lg (default: md)
- `icon` (string, optional) - Keenicon name
- `href` (string, optional) - Link URL
- `type` (string, optional) - Button type: button, submit (default: button)

**Variants:**
- `primary` → `btn btn-primary` - Create/Submit actions
- `success` → `btn btn-success` - Approve actions
- `danger` → `btn btn-danger` - Delete actions
- `light` → `btn btn-light` - View/Secondary actions
- `light-primary` → `btn btn-light-primary` - Tertiary actions
- `secondary` → `btn btn-secondary` - Cancel/Back actions

**Icon Position:**
- Icons ALWAYS before text
- Icon size: `fs-2` for lg buttons, `fs-3` for md/sm buttons
- Icon spacing: `me-2` (margin-end 2)

---

### 7. Badge Component

**File:** `resources/views/components/badge.blade.php`

**Standard Structure:**
```blade
<x-badge variant="success">APPROVED</x-badge>
<x-badge variant="warning">PENDING</x-badge>
<x-badge variant="danger">REJECTED</x-badge>
```

**Props:**
- `variant` (string, required) - Badge variant
- `dot` (boolean, optional) - Show bullet dot (default: false)

**Variants & Mapping:**
- `success` / `approved` → `badge-light-success` (Green) - Approved, Paid, Delivered
- `warning` / `pending` → `badge-light-warning` (Yellow) - Pending, Submitted, Draft
- `danger` / `rejected` → `badge-light-danger` (Red) - Rejected, Cancelled, Overdue
- `primary` → `badge-light-primary` (Blue) - Active, In Progress
- `secondary` → `badge-light-secondary` (Gray) - Draft, Inactive
- `info` → `badge-light-info` (Cyan) - Information

**CSS Classes:**
- Base: `badge badge-light-{color} fw-bold`
- Text: UPPERCASE

---

### 8. Empty State Component

**File:** `resources/views/components/empty-state.blade.php`

**Standard Structure:**
```blade
<x-empty-state 
    icon="file-deleted"
    title="No Data Found"
    message="Try adjusting your filters or create a new item"
/>
```

**Props:**
- `icon` (string, required) - Keenicon name (without ki-outline prefix)
- `title` (string, required) - Empty state title
- `message` (string, optional) - Empty state message

**CSS Classes:**
- Container: `text-center py-10`
- Layout: `d-flex flex-column align-items-center`
- Icon: `ki-outline ki-{icon} fs-3x text-gray-400 mb-3`
- Title: `text-gray-800 fw-semibold fs-6 mb-1`
- Message: `text-gray-600 fs-7 mb-0`

**Common Icons:**
- `file-deleted` - Empty table/list
- `information-5` - No data available
- `package` - Empty items
- `search-list` - No search results

---

### 9. Form Input Components

#### Input Component
```blade
<x-input 
    name="field_name"
    label="Field Label"
    type="text"
    value="{{ old('field_name') }}"
    placeholder="Enter value..."
    required
/>
```

#### Select Component
```blade
<x-select 
    name="field_name"
    label="Field Label"
    required
>
    <option value="">-- Select Option --</option>
    <option value="1">Option 1</option>
</x-select>
```

#### Textarea Component
```blade
<x-textarea 
    name="field_name"
    label="Field Label"
    rows="4"
    placeholder="Enter text..."
/>
```

**CSS Classes:**
- Label: `form-label` (add `required` class if required)
- Input: `form-control form-control-solid`
- Select: `form-select form-select-solid`
- Helper: `form-text text-gray-600`
- Spacing: `mb-5` between form groups

---

## 🎨 Global UI Rules

### Page Structure Rules

1. **ALL pages MUST have:**
   - Page header with title and description
   - Consistent spacing (mb-7 between sections)
   - Responsive layout (mobile-first)

2. **Index pages MUST have:**
   - Page header with create button
   - Filter bar (if applicable)
   - Data table with pagination
   - Empty state

3. **Form pages MUST have:**
   - Page header with description
   - Form sections in cards
   - Submit/Cancel buttons at bottom
   - Proper spacing (mb-7 between cards)

4. **Detail pages MUST have:**
   - Page header with status badge
   - Action buttons (Edit, Delete, Back)
   - 2:1 grid layout (col-lg-8 + col-lg-4)
   - Related data in sidebar

---

### Table Rules

1. **Structure:**
   - ALWAYS wrap in `table-responsive`
   - Use `table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4`
   - Headers: `fw-bold text-muted`
   - Column widths: `min-w-{size}px`

2. **Content:**
   - Primary data: `text-gray-900 fw-bold d-block fs-6`
   - Secondary data: `text-gray-800 fw-semibold d-block fs-6`
   - Tertiary data: `text-gray-600 fw-semibold fs-7`
   - Actions column: `text-end` alignment

3. **Empty State:**
   - ALWAYS provide empty state
   - Use `<x-empty-state>` component
   - Centered with icon and message

---

### Filter Rules

1. **Layout:**
   - Use `<x-filter-bar>` component
   - Responsive grid: `col-md-4`, `col-md-3`, `col-md-5`
   - Search input ALWAYS first
   - Dropdowns after search
   - Action buttons last

2. **Inputs:**
   - Search: `form-control form-control-solid`
   - Dropdowns: `form-select form-select-solid`
   - Buttons: Primary for submit, Light for reset

3. **Behavior:**
   - Show reset button only when filters active
   - Preserve filter values in inputs
   - Use GET method for filters

---

### Button Rules

1. **Variants by Action:**
   - **Create/Add:** `btn btn-primary` with `ki-plus` icon
   - **Approve:** `btn btn-success` with `ki-check` icon
   - **Delete:** `btn btn-danger` or `btn btn-light-danger` with `ki-trash` icon
   - **View/Detail:** `btn btn-light-primary` with `ki-eye` icon
   - **Edit:** `btn btn-light-primary` with `ki-pencil` icon
   - **Back/Cancel:** `btn btn-secondary` with `ki-arrow-left` icon
   - **Download:** `btn btn-light` with `ki-cloud-download` icon

2. **Icon Rules:**
   - Icons ALWAYS before text
   - Size: `fs-2` for lg, `fs-3` for md/sm
   - Spacing: `me-2` after icon

3. **Button Groups:**
   - Use `d-flex gap-2` or `gap-3`
   - Primary action on right
   - Secondary actions on left

---

### Badge Rules

1. **Status Mapping (MUST follow):**
   - **Success (Green):** approved, paid, delivered, completed, active
   - **Warning (Yellow):** pending, submitted, processing, in_progress
   - **Danger (Red):** rejected, cancelled, overdue, failed, inactive
   - **Primary (Blue):** approved (alternative), shipped, confirmed
   - **Secondary (Gray):** draft, new, unknown

2. **Styling:**
   - ALWAYS use `badge-light-{color}`
   - ALWAYS add `fw-bold` class
   - Text ALWAYS UPPERCASE
   - Use PHP match() for dynamic mapping

3. **Example:**
```php
@php
    $badgeClass = match($status) {
        'draft' => 'badge-light-secondary',
        'pending', 'submitted' => 'badge-light-warning',
        'approved' => 'badge-light-success',
        'rejected', 'cancelled' => 'badge-light-danger',
        default => 'badge-light-primary'
    };
@endphp
<span class="badge {{ $badgeClass }} fw-bold">{{ strtoupper($status) }}</span>
```

---

### Typography Rules

1. **Hierarchy:**
   - Page title: `fs-2 fw-bold text-gray-900`
   - Section heading: `fs-3 fw-bold`
   - Card title: `fs-3 fw-bold` (in card-title)
   - Body text: `fs-6 text-gray-600`
   - Labels/metadata: `fs-7 text-gray-600`
   - Small text: `fs-8 text-gray-600`

2. **Weights:**
   - Bold: `fw-bold` - Primary emphasis (titles, values)
   - Semibold: `fw-semibold` - Medium emphasis (labels, secondary data)
   - Normal: `fw-normal` - Regular text (implied default)

3. **Colors:**
   - Primary: `text-gray-900` - Main content
   - Secondary: `text-gray-800` - Secondary content
   - Tertiary: `text-gray-600` - Labels, metadata
   - Muted: `text-gray-400` - Disabled, empty states
   - Semantic: `text-primary`, `text-success`, `text-danger`, `text-warning`

---

### Spacing Rules

1. **Between Sections:**
   - Page header to content: `mb-7`
   - Between cards: `mb-5 mb-xl-8`
   - Between form groups: `mb-5`
   - Card padding: `pt-5` (header), `pt-0` (body)

2. **Grid Spacing:**
   - Row gap: `g-5` or `g-xl-8`
   - Column gap: `gap-2`, `gap-3`, `gap-4`

3. **Internal Spacing:**
   - Card header: `border-0 pt-5`
   - Card body: `pt-0` (if has header), `py-4` (standalone)
   - Table cells: `px-5 py-4`

---

### Responsive Rules

1. **Breakpoints:**
   - Mobile: < 576px (col-12, flex-column)
   - Tablet: ≥ 768px (col-md-*, flex-md-row)
   - Desktop: ≥ 992px (col-lg-*, full layout)

2. **Layout Patterns:**
   - Header: `d-flex flex-column flex-md-row`
   - Alignment: `align-items-start align-items-md-center`
   - Grid: `col-12 col-md-6 col-lg-4`
   - Detail view: `col-lg-8` + `col-lg-4`

3. **Tables:**
   - ALWAYS wrap in `table-responsive`
   - Allow horizontal scroll on mobile
   - No column hiding

---

### Icon Rules

1. **Format:**
   - ALWAYS use Keenicons: `ki-outline ki-{icon-name}`
   - Size: `fs-2` (large), `fs-3` (medium), `fs-3x` (extra large)
   - Color: Inherit from parent or use semantic colors

2. **Common Icons:**
   - `ki-plus` - Add/Create
   - `ki-pencil` - Edit
   - `ki-trash` - Delete
   - `ki-eye` - View
   - `ki-magnifier` - Search
   - `ki-filter` - Filter
   - `ki-arrow-left` - Back
   - `ki-cloud-download` - Download
   - `ki-send` - Submit
   - `ki-check` - Approve
   - `ki-cross` - Reject/Cancel
   - `ki-information-5` - Empty state
   - `ki-file-deleted` - No data

---

## 🔒 Enforcement Rules

### MUST DO:
1. ✅ Use components for ALL UI elements
2. ✅ Follow exact CSS class patterns
3. ✅ Use Keenicons for ALL icons
4. ✅ Follow badge color mapping
5. ✅ Implement empty states
6. ✅ Use responsive classes
7. ✅ Follow typography hierarchy
8. ✅ Use proper spacing

### MUST NOT DO:
1. ❌ Write raw HTML for cards, tables, buttons
2. ❌ Use custom CSS classes
3. ❌ Use SVG icons
4. ❌ Skip empty states
5. ❌ Ignore responsive design
6. ❌ Use inconsistent spacing
7. ❌ Mix badge colors randomly
8. ❌ Skip page headers

---

## 📝 Module Checklist

Before marking a module as complete, verify:

- [ ] Uses `<x-page-header>` component
- [ ] Uses `<x-card>` for all sections
- [ ] Uses `<x-data-table>` or proper table structure
- [ ] Uses `<x-button>` for all buttons
- [ ] Uses `<x-badge>` with correct color mapping
- [ ] Has `<x-empty-state>` for empty data
- [ ] Uses `<x-filter-bar>` for filters (if applicable)
- [ ] All icons are Keenicons
- [ ] Responsive design implemented
- [ ] Typography hierarchy followed
- [ ] Spacing rules followed
- [ ] No raw HTML (except in components)

---

## 🎯 Example: Complete Index Page

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
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
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
                        <th class="min-w-100px text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <span class="text-gray-900 fw-bold d-block fs-6">{{ $item->name }}</span>
                            </td>
                            <td>
                                <x-badge variant="{{ $item->status }}">{{ strtoupper($item->status) }}</x-badge>
                            </td>
                            <td class="text-end">
                                <x-button variant="light-primary" size="sm" href="{{ route('module.show', $item) }}">
                                    Detail
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
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

---

## 🚀 Next Steps

1. **Refactor existing modules** (Dashboard, Purchase Orders) to use components
2. **Create missing components** (filter-bar, data-table)
3. **Update component library** to match standards
4. **Apply to all future modules**

---

**LOCKED:** This standard is now the foundation for all UI development.  
**NO EXCEPTIONS:** All modules MUST follow this system.
