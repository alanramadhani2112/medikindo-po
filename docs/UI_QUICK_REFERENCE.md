# UI System - Quick Reference Card

**For:** Developers  
**Purpose:** Quick lookup for component usage and standards

---

## 🧩 Component Quick Reference

### Page Header
```blade
<x-page-header title="Page Title" description="Description">
    <x-slot name="actions">
        <x-button variant="primary" icon="plus" href="...">Create</x-button>
    </x-slot>
</x-page-header>
```

### Filter Bar
```blade
<x-filter-bar action="{{ route('...') }}">
    <x-slot name="filters">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-solid">
        </div>
    </x-slot>
</x-filter-bar>
```

### Card
```blade
<x-card title="Card Title" icon="icon-name" class="card-flush mb-7">
    <x-slot name="actions">...</x-slot>
    Content here
</x-card>
```

### Button
```blade
<x-button variant="primary" icon="plus" href="...">Text</x-button>
<x-button type="submit" variant="success" icon="check">Submit</x-button>
```

### Badge
```blade
<x-badge variant="success">APPROVED</x-badge>
<x-badge variant="warning">PENDING</x-badge>
<x-badge variant="danger">REJECTED</x-badge>
```

### Empty State
```blade
<x-empty-state 
    icon="file-deleted"
    title="No Data"
    message="Try adjusting filters"
/>
```

### Form Inputs
```blade
<x-input name="field" label="Label" type="text" required />
<x-select name="field" label="Label" required>...</x-select>
<x-textarea name="field" label="Label" rows="4" />
```

---

## 🎨 Badge Colors (LOCKED)

| Status | Variant | Color | Use For |
|--------|---------|-------|---------|
| Success | `success` / `approved` | Green | Approved, Paid, Delivered, Active |
| Warning | `warning` / `pending` | Yellow | Pending, Submitted, Processing |
| Danger | `danger` / `rejected` | Red | Rejected, Cancelled, Overdue, Failed |
| Primary | `primary` | Blue | Confirmed, Shipped |
| Secondary | `secondary` / `draft` | Gray | Draft, Inactive |

---

## 🔘 Button Variants (LOCKED)

| Action | Variant | Icon | Example |
|--------|---------|------|---------|
| Create | `primary` | `plus` | Create New |
| Approve | `success` | `check` | Approve |
| Delete | `danger` | `trash` | Delete |
| View | `light-primary` | `eye` | Detail |
| Edit | `light-primary` | `pencil` | Edit |
| Back | `secondary` | `arrow-left` | Back |
| Download | `light` | `cloud-download` | PDF |

---

## 📐 Typography (LOCKED)

| Element | Classes |
|---------|---------|
| Page Title | `fs-2 fw-bold text-gray-900` |
| Section Heading | `fs-3 fw-bold` |
| Card Title | `fs-3 fw-bold` (in card-title) |
| Body Text | `fs-6 text-gray-600` |
| Labels | `fs-7 text-gray-600` |
| Small Text | `fs-8 text-gray-600` |

---

## 📏 Spacing (LOCKED)

| Context | Class |
|---------|-------|
| Between sections | `mb-7` |
| Between cards | `mb-5 mb-xl-8` |
| Between form groups | `mb-5` |
| Card header | `border-0 pt-5` |
| Card body | `pt-0` |
| Button groups | `gap-2` or `gap-3` |

---

## 📱 Responsive Grid (LOCKED)

| Layout | Classes |
|--------|---------|
| Full width | `col-12` |
| Half on tablet | `col-12 col-md-6` |
| Third on desktop | `col-12 col-md-6 col-lg-4` |
| Detail page main | `col-lg-8` |
| Detail page sidebar | `col-lg-4` |
| Row spacing | `g-5` or `g-xl-8` |

---

## 🎯 Common Icons (Keenicons)

| Action | Icon Name |
|--------|-----------|
| Add/Create | `plus` |
| Edit | `pencil` |
| Delete | `trash` |
| View | `eye` |
| Search | `magnifier` |
| Filter | `filter` |
| Back | `arrow-left` |
| Download | `cloud-download` |
| Submit | `send` |
| Approve | `check` |
| Reject | `cross` |
| Empty state | `file-deleted` or `information-5` |
| Package | `package` |

---

## 📋 Table Structure (LOCKED)

```blade
<div class="table-responsive">
    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
        <thead>
            <tr class="fw-bold text-muted">
                <th class="min-w-150px">Column</th>
                <th class="min-w-100px text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>
                        <span class="text-gray-900 fw-bold d-block fs-6">{{ $item->name }}</span>
                    </td>
                    <td class="text-end">
                        <x-button variant="light-primary" size="sm">Detail</x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">
                        <x-empty-state icon="file-deleted" title="No Data" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
```

---

## ✅ Quick Checklist

Before committing, verify:

- [ ] Uses `<x-page-header>`
- [ ] Uses `<x-card>` for sections
- [ ] Uses `<x-button>` for buttons
- [ ] Uses `<x-badge>` with correct colors
- [ ] Has `<x-empty-state>`
- [ ] All icons are Keenicons
- [ ] Responsive classes applied
- [ ] No raw HTML for UI elements

---

## 🚫 Common Mistakes

### ❌ DON'T
```blade
<!-- Raw HTML card -->
<div class="card">
    <div class="card-header">
        <h3>Title</h3>
    </div>
</div>

<!-- Inline badge -->
<span class="badge badge-success">Status</span>

<!-- Raw button -->
<a href="..." class="btn btn-primary">Click</a>
```

### ✅ DO
```blade
<!-- Component card -->
<x-card title="Title">
    Content
</x-card>

<!-- Component badge -->
<x-badge variant="success">Status</x-badge>

<!-- Component button -->
<x-button variant="primary" href="...">Click</x-button>
```

---

## 📚 Full Documentation

- **UI_SYSTEM_STANDARD.md** - Complete specification
- **UI_SYSTEM_IMPLEMENTATION_GUIDE.md** - Step-by-step guide
- **UI_SYSTEM_SUMMARY.md** - Overview and summary

---

**Print this page and keep it handy!**
