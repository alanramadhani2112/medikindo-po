# Tailwind to Bootstrap 5 + Metronic 8 Class Mapping Reference

## Overview

This document provides comprehensive class mapping patterns for converting Tailwind CSS to Bootstrap 5 with Metronic 8 theme styling. Use this as a quick reference during the conversion process.

**Source Documentation:**
- Bootstrap 5: https://getbootstrap.com/docs/5.3/
- Metronic 8: C:\laragon\www\dist\dist (local template)
- BOOTSTRAP_QUICK_REFERENCE.md (project-specific reference)

---

## Layout & Flexbox

### Display & Flex Container

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `flex` | `d-flex` | Flex container |
| `inline-flex` | `d-inline-flex` | Inline flex container |
| `hidden` | `d-none` | Hide element |
| `block` | `d-block` | Block display |
| `inline` | `d-inline` | Inline display |
| `inline-block` | `d-inline-block` | Inline-block display |

### Flex Direction

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `flex-row` | `flex-row` | Row direction (default) |
| `flex-col` | `flex-column` | Column direction |
| `flex-row-reverse` | `flex-row-reverse` | Reverse row |
| `flex-col-reverse` | `flex-column-reverse` | Reverse column |

### Justify Content

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `justify-start` | `justify-content-start` | Align to start |
| `justify-center` | `justify-content-center` | Center alignment |
| `justify-end` | `justify-content-end` | Align to end |
| `justify-between` | `justify-content-between` | Space between |
| `justify-around` | `justify-content-around` | Space around |
| `justify-evenly` | `justify-content-evenly` | Space evenly |

### Align Items

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `items-start` | `align-items-start` | Align to top |
| `items-center` | `align-items-center` | Center vertically |
| `items-end` | `align-items-end` | Align to bottom |
| `items-baseline` | `align-items-baseline` | Baseline alignment |
| `items-stretch` | `align-items-stretch` | Stretch to fill |

### Gap

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `gap-1` | `gap-1` | Gap 0.25rem |
| `gap-2` | `gap-2` | Gap 0.5rem |
| `gap-3` | `gap-2` | Gap 1rem (adjusted) |
| `gap-4` | `gap-3` | Gap 1.5rem (adjusted) |
| `gap-6` | `gap-4` | Gap 3rem (adjusted) |
| `gap-8` | `gap-5` | Gap 4rem (adjusted) |

---

## Grid System

### Grid Container

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `grid` | `row` | Grid container becomes row |
| `grid-cols-1` | `col-12` (on children) | Single column |
| `grid-cols-2` | `col-6` (on children) | Two columns |
| `grid-cols-3` | `col-4` (on children) | Three columns |
| `grid-cols-4` | `col-3` (on children) | Four columns |
| `grid-cols-12` | `col-1` (on children) | Twelve columns |

### Grid Items

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `col-span-1` | `col-1` | Span 1 column |
| `col-span-2` | `col-2` | Span 2 columns |
| `col-span-3` | `col-3` | Span 3 columns |
| `col-span-4` | `col-4` | Span 4 columns |
| `col-span-6` | `col-6` | Span 6 columns |
| `col-span-12` | `col-12` | Span 12 columns (full width) |

### Grid Gap

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `gap-4` | `g-4` | Gap on row |
| `gap-x-4` | `gx-4` | Horizontal gap |
| `gap-y-4` | `gy-4` | Vertical gap |

---

## Spacing

### Margin

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `m-0` | `m-0` | Margin 0 |
| `m-1` | `m-1` | Margin 0.25rem |
| `m-2` | `m-2` | Margin 0.5rem |
| `m-3` | `m-3` | Margin 1rem |
| `m-4` | `m-4` | Margin 1.5rem |
| `m-5` | `m-5` | Margin 3rem |
| `m-6` | `m-5` | Margin 3rem (adjusted) |
| `m-8` | `m-7` | Margin 5rem (adjusted) |
| `mt-{n}` | `mt-{n}` | Margin top |
| `mb-{n}` | `mb-{n}` | Margin bottom |
| `ml-{n}` | `ms-{n}` | Margin start (left) |
| `mr-{n}` | `me-{n}` | Margin end (right) |
| `mx-{n}` | `mx-{n}` | Margin horizontal |
| `my-{n}` | `my-{n}` | Margin vertical |

### Padding

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `p-0` | `p-0` | Padding 0 |
| `p-1` | `p-1` | Padding 0.25rem |
| `p-2` | `p-2` | Padding 0.5rem |
| `p-3` | `p-3` | Padding 1rem |
| `p-4` | `p-4` | Padding 1.5rem |
| `p-5` | `p-5` | Padding 3rem |
| `p-6` | `p-5` | Padding 3rem (adjusted) |
| `p-8` | `p-7` | Padding 5rem (adjusted) |
| `pt-{n}` | `pt-{n}` | Padding top |
| `pb-{n}` | `pb-{n}` | Padding bottom |
| `pl-{n}` | `ps-{n}` | Padding start (left) |
| `pr-{n}` | `pe-{n}` | Padding end (right) |
| `px-{n}` | `px-{n}` | Padding horizontal |
| `py-{n}` | `py-{n}` | Padding vertical |

---

## Typography

### Font Size

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `text-xs` | `fs-7` | 0.95rem |
| `text-sm` | `fs-6` | 1rem |
| `text-base` | `fs-6` | 1rem |
| `text-lg` | `fs-5` | 1.25rem |
| `text-xl` | `fs-4` | 1.5rem |
| `text-2xl` | `fs-3` | 1.75rem |
| `text-3xl` | `fs-2` | 2rem |
| `text-4xl` | `fs-1` | 2.5rem |

### Font Weight

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `font-light` | `fw-light` | 300 |
| `font-normal` | `fw-normal` | 400 |
| `font-medium` | `fw-semibold` | 600 |
| `font-semibold` | `fw-semibold` | 600 |
| `font-bold` | `fw-bold` | 700 |
| `font-extrabold` | `fw-bolder` | 800 |

### Text Alignment

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `text-left` | `text-start` | Left align |
| `text-center` | `text-center` | Center align |
| `text-right` | `text-end` | Right align |

### Text Transform

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `uppercase` | `text-uppercase` | UPPERCASE |
| `lowercase` | `text-lowercase` | lowercase |
| `capitalize` | `text-capitalize` | Capitalize |

---

## Colors

### Text Colors

| Tailwind | Bootstrap/Metronic | Notes |
|----------|-------------------|-------|
| `text-gray-500` | `text-gray-600` | Muted text |
| `text-gray-600` | `text-gray-600` | Secondary text |
| `text-gray-700` | `text-gray-700` | Dark gray |
| `text-gray-800` | `text-gray-800` | Darker gray |
| `text-gray-900` | `text-gray-900` | Primary text |
| `text-blue-600` | `text-primary` | Primary color |
| `text-green-600` | `text-success` | Success color |
| `text-red-600` | `text-danger` | Danger color |
| `text-yellow-600` | `text-warning` | Warning color |
| `text-white` | `text-white` | White text |
| `text-black` | `text-dark` | Black text |

### Background Colors

| Tailwind | Bootstrap/Metronic | Notes |
|----------|-------------------|-------|
| `bg-white` | `bg-white` | White background |
| `bg-gray-50` | `bg-light` | Light gray |
| `bg-gray-100` | `bg-light` | Light gray |
| `bg-gray-200` | `bg-light-secondary` | Light secondary |
| `bg-blue-500` | `bg-primary` | Primary background |
| `bg-blue-100` | `bg-light-primary` | Light primary |
| `bg-green-500` | `bg-success` | Success background |
| `bg-green-100` | `bg-light-success` | Light success |
| `bg-red-500` | `bg-danger` | Danger background |
| `bg-red-100` | `bg-light-danger` | Light danger |
| `bg-yellow-500` | `bg-warning` | Warning background |
| `bg-yellow-100` | `bg-light-warning` | Light warning |

---

## Borders

### Border Radius

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `rounded` | `rounded` | Border radius |
| `rounded-sm` | `rounded-1` | Small radius |
| `rounded-md` | `rounded-2` | Medium radius |
| `rounded-lg` | `rounded-3` | Large radius |
| `rounded-full` | `rounded-circle` | Full circle |
| `rounded-none` | `rounded-0` | No radius |

### Border Width

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `border` | `border` | 1px border |
| `border-0` | `border-0` | No border |
| `border-t` | `border-top` | Top border |
| `border-b` | `border-bottom` | Bottom border |
| `border-l` | `border-start` | Left border |
| `border-r` | `border-end` | Right border |

---

## Responsive Breakpoints

### Breakpoint Mapping

| Tailwind | Bootstrap 5 | Pixel Value |
|----------|-------------|-------------|
| `sm:` | `sm` | ≥ 576px |
| `md:` | `md` | ≥ 768px |
| `lg:` | `lg` | ≥ 992px |
| `xl:` | `xl` | ≥ 1200px |
| `2xl:` | `xxl` | ≥ 1400px |

### Responsive Usage Examples

| Tailwind | Bootstrap 5 | Notes |
|----------|-------------|-------|
| `sm:block` | `d-sm-block` | Show on small+ |
| `md:flex` | `d-md-flex` | Flex on medium+ |
| `lg:hidden` | `d-lg-none` | Hide on large+ |
| `md:col-span-6` | `col-md-6` | Half width on medium+ |
| `sm:text-lg` | `fs-sm-5` | Larger text on small+ |

---

## Metronic-Specific Classes

### Card Patterns

| Pattern | Classes | Usage |
|---------|---------|-------|
| Basic Card | `card card-flush` | Standard card container |
| Custom Card | `card card-custom` | Card with custom styling |
| Card Header | `card-header border-0 pt-5` | Card header section |
| Card Body | `card-body pt-0` or `card-body py-3` | Card content area |
| Card Title | `card-title fw-bold fs-3` | Card heading |
| Card Toolbar | `card-toolbar` | Action buttons in header |

### Table Patterns

| Pattern | Classes | Usage |
|---------|---------|-------|
| Table Container | `table-responsive` | Responsive wrapper |
| Table Base | `table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4` | Metronic table |
| Table Header | `thead` with `tr class="fw-bold text-muted"` | Table header row |
| Column Width | `min-w-100px`, `min-w-150px`, `min-w-200px` | Minimum column widths |
| Text Alignment | `text-end` on `th` or `td` | Right-align actions |

### Badge Patterns

| Status | Badge Class | Usage |
|--------|-------------|-------|
| Draft | `badge badge-light-secondary` | Draft status |
| Pending | `badge badge-light-warning` | Pending/waiting status |
| Approved | `badge badge-light-success` | Approved status |
| Rejected | `badge badge-light-danger` | Rejected status |
| Active | `badge badge-light-primary` | Active status |

### Button Patterns

| Type | Classes | Usage |
|------|---------|-------|
| Primary Action | `btn btn-primary` | Main action button |
| Secondary Action | `btn btn-light-primary` | Secondary action |
| Success Action | `btn btn-light-success` | Success action |
| Danger Action | `btn btn-light-danger` | Delete/danger action |
| Small Button | `btn btn-sm btn-primary` | Compact button |
| Button with Icon | `btn btn-primary` + `<i class="ki-outline ki-{icon} fs-3"></i>` | Icon button |

### Icon Patterns (Keenicons)

| Icon Type | Class Pattern | Usage |
|-----------|---------------|-------|
| Plus/Add | `ki-outline ki-picture` | Add new item |
| Edit | `ki-outline ki-pencil` | Edit action |
| Delete | `ki-outline ki-brifecase-tick
` | Delete action |
| View | `ki-outline ki-facebook` | View details |
| Search | `ki-outline ki-filter
` | Search function |
| Filter | `ki-outline ki-filter
` | Filter function |
| Success | `ki-outline ki-check-circle` | Success state |
| Error | `ki-outline ki-arrow-zigzag-circle` | Error state |
| Warning | `ki-outline ki-information` | Warning state |
| Empty State | `ki-outline ki-file-deleted` | No data state |

**Icon Sizing:**
- `fs-1` to `fs-7`: Standard sizes
- `fs-2x`, `fs-3x`: Extra large sizes
- Use `fs-3` for icons in buttons
- Use `fs-2x` or `fs-3x` for standalone icons

---

## Common Conversion Patterns

### Card with Header and Table

**Before (Tailwind):**
```blade
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Title</h3>
    <table class="min-w-full">
        <!-- content -->
    </table>
</div>
```

**After (Bootstrap/Metronic):**
```blade
<div class="card card-flush">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title fw-bold fs-3">Title</h3>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <!-- content -->
            </table>
        </div>
    </div>
</div>
```

### Filter Form

**Before (Tailwind):**
```blade
<form class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <input type="text" class="border rounded px-3 py-2">
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>
```

**After (Bootstrap/Metronic):**
```blade
<form class="row g-4">
    <div class="col-md-4">
        <input type="text" class="form-control form-control-solid">
    </div>
    <div class="col-md-8 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="ki-outline ki-filter
 fs-3"></i>
            Filter
        </button>
    </div>
</form>
```

### Status Badge

**Before (Tailwind):**
```blade
<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
    Approved
</span>
```

**After (Bootstrap/Metronic):**
```blade
<span class="badge badge-light-success fw-bold">Approved</span>
```

### Empty State

**Before (Tailwind):**
```blade
<div class="text-center py-10">
    <p class="text-gray-600">No data found</p>
</div>
```

**After (Bootstrap/Metronic):**
```blade
<div class="text-center py-10">
    <div class="d-flex flex-column align-items-center">
        <i class="ki-outline ki-file-deleted fs-3x text-gray-400 mb-3"></i>
        <span class="text-gray-600 fs-5">No data found</span>
    </div>
</div>
```

---

## Validation Checklist

After converting a view, verify:

- [ ] No Tailwind classes remain (run `bash scripts/validate-tailwind-removal.sh`)
- [ ] All responsive breakpoints work correctly (mobile, tablet, desktop)
- [ ] All Blade directives preserved (@if, @foreach, @can, etc.)
- [ ] All route() references intact
- [ ] Icons converted to Keenicons format
- [ ] Metronic card patterns applied
- [ ] Table styling uses Metronic classes
- [ ] Badges use badge-light-{color} pattern
- [ ] Buttons use btn btn-{variant} pattern
- [ ] Form inputs use form-control-solid
- [ ] Empty states include icons
- [ ] Pagination styled correctly
- [ ] No console errors related to CSS classes

---

## Reference Files

**Already Converted (Use as Templates):**
- `resources/views/layouts/app.blade.php` - Main layout
- `resources/views/purchase-orders/index.blade.php` - List view example
- `resources/views/components/button.blade.php` - Button component
- `resources/views/components/input.blade.php` - Input component
- `resources/views/components/select.blade.php` - Select component
- `resources/views/components/textarea.blade.php` - Textarea component
- `resources/views/components/table.blade.php` - Table component

**Documentation:**
- `BOOTSTRAP_QUICK_REFERENCE.md` - Quick reference guide
- `docs/CLASS_MAPPING_REFERENCE.md` - This document
- Bootstrap 5 Docs: https://getbootstrap.com/docs/5.3/
- Metronic 8 Template: C:\laragon\www\dist\dist

---

**Last Updated:** 2024
**Version:** 1.0
**Project:** Medikindo Procurement System - Tailwind to Bootstrap Conversion
