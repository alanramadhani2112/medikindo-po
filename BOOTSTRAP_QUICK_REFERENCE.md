# Bootstrap 5 + Metronic 8 Quick Reference

## 🎨 Layout & Grid

### Container
```html
<div class="container">Fixed width</div>
<div class="container-fluid">Full width</div>
```

### Grid System
```html
<div class="row">
    <div class="col-12">Full width</div>
    <div class="col-md-6">Half on medium+</div>
    <div class="col-lg-4">Third on large+</div>
    <div class="col-xl-3">Quarter on extra large+</div>
</div>

<!-- With Gutters -->
<div class="row g-5">Gutter 5</div>
<div class="row g-xl-8">Gutter 8 on XL+</div>
<div class="row gx-5">Horizontal gutter</div>
<div class="row gy-5">Vertical gutter</div>
```

### Flexbox
```html
<!-- Direction -->
<div class="d-flex">Flex container</div>
<div class="d-flex flex-row">Row (default)</div>
<div class="d-flex flex-column">Column</div>

<!-- Justify Content -->
<div class="d-flex justify-content-start">Start</div>
<div class="d-flex justify-content-center">Center</div>
<div class="d-flex justify-content-end">End</div>
<div class="d-flex justify-content-between">Space between</div>

<!-- Align Items -->
<div class="d-flex align-items-start">Top</div>
<div class="d-flex align-items-center">Middle</div>
<div class="d-flex align-items-end">Bottom</div>
<div class="d-flex align-items-stretch">Stretch</div>

<!-- Gap -->
<div class="d-flex gap-3">Gap 3</div>
<div class="d-flex gap-5">Gap 5</div>
```

## 🎴 Cards

### Basic Card
```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
    </div>
    <div class="card-body">
        Content
    </div>
    <div class="card-footer">
        Footer
    </div>
</div>
```

### Metronic Card
```html
<div class="card card-custom">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">Title</span>
            <span class="text-muted fw-semibold fs-7">Subtitle</span>
        </h3>
        <div class="card-toolbar">
            <button class="btn btn-sm btn-primary">Action</button>
        </div>
    </div>
    <div class="card-body py-3">
        Content
    </div>
</div>
```

## 📊 Tables

### Basic Table
```html
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Column 1</th>
                <th>Column 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data 1</td>
                <td>Data 2</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Metronic Table
```html
<div class="table-responsive">
    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
        <thead>
            <tr class="fw-bold text-muted">
                <th class="min-w-150px">Column 1</th>
                <th class="min-w-100px">Column 2</th>
                <th class="min-w-100px text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <span class="text-dark fw-bold d-block fs-6">Data 1</span>
                </td>
                <td>
                    <span class="text-muted fw-semibold d-block fs-7">Data 2</span>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-light-primary">View</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

## 🔘 Buttons

### Basic Buttons
```html
<!-- Solid -->
<button class="btn btn-primary">Primary</button>
<button class="btn btn-secondary">Secondary</button>
<button class="btn btn-success">Success</button>
<button class="btn btn-danger">Danger</button>
<button class="btn btn-warning">Warning</button>
<button class="btn btn-info">Info</button>

<!-- Light Variants -->
<button class="btn btn-light-primary">Light Primary</button>
<button class="btn btn-light-success">Light Success</button>
<button class="btn btn-light-danger">Light Danger</button>
<button class="btn btn-light-warning">Light Warning</button>

<!-- Sizes -->
<button class="btn btn-sm btn-primary">Small</button>
<button class="btn btn-primary">Normal</button>
<button class="btn btn-lg btn-primary">Large</button>

<!-- With Icons -->
<button class="btn btn-primary">
    <i class="ki-outline ki-plus fs-3"></i>
    Add New
</button>
```

## 🏷️ Badges

```html
<!-- Solid -->
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Success</span>
<span class="badge badge-danger">Danger</span>
<span class="badge badge-warning">Warning</span>
<span class="badge badge-info">Info</span>

<!-- Light Variants -->
<span class="badge badge-light-primary">Primary</span>
<span class="badge badge-light-success">Success</span>
<span class="badge badge-light-danger">Danger</span>
<span class="badge badge-light-warning">Warning</span>

<!-- Sizes -->
<span class="badge badge-sm badge-primary">Small</span>
<span class="badge badge-primary">Normal</span>
<span class="badge badge-lg badge-primary">Large</span>
```

## 🔔 Alerts

```html
<div class="alert alert-primary d-flex align-items-center">
    <i class="ki-outline ki-information-5 fs-2 me-3"></i>
    <span>Primary alert message</span>
</div>

<div class="alert alert-success d-flex align-items-center">
    <i class="ki-outline ki-check-circle fs-2 me-3"></i>
    <span>Success alert message</span>
</div>

<div class="alert alert-danger d-flex align-items-center">
    <i class="ki-outline ki-cross-circle fs-2 me-3"></i>
    <span>Danger alert message</span>
</div>

<div class="alert alert-warning d-flex align-items-center">
    <i class="ki-outline ki-information fs-2 me-3"></i>
    <span>Warning alert message</span>
</div>
```

## 📝 Forms

### Form Groups
```html
<div class="mb-5">
    <label class="form-label required">Label</label>
    <input type="text" class="form-control" placeholder="Enter value">
    <div class="form-text">Helper text</div>
</div>

<div class="mb-5">
    <label class="form-label">Select</label>
    <select class="form-select">
        <option>Option 1</option>
        <option>Option 2</option>
    </select>
</div>

<div class="mb-5">
    <label class="form-label">Textarea</label>
    <textarea class="form-control" rows="3"></textarea>
</div>

<div class="form-check mb-5">
    <input class="form-check-input" type="checkbox" id="check1">
    <label class="form-check-label" for="check1">
        Checkbox label
    </label>
</div>
```

## 🎭 Symbols (Avatars)

```html
<!-- With Icon -->
<div class="symbol symbol-50px">
    <span class="symbol-label bg-light-primary">
        <i class="ki-outline ki-user fs-2x text-primary"></i>
    </span>
</div>

<!-- With Text -->
<div class="symbol symbol-50px">
    <span class="symbol-label fs-4 fw-bold bg-light-primary text-primary">
        AB
    </span>
</div>

<!-- With Image -->
<div class="symbol symbol-50px">
    <img src="avatar.jpg" alt="Avatar">
</div>

<!-- Sizes -->
<div class="symbol symbol-35px">Small</div>
<div class="symbol symbol-50px">Medium</div>
<div class="symbol symbol-75px">Large</div>
```

## 📏 Spacing

### Margin
```html
<!-- All sides: m-{0-10} -->
<div class="m-5">Margin 5 all sides</div>

<!-- Specific sides -->
<div class="mt-5">Margin top 5</div>
<div class="mb-5">Margin bottom 5</div>
<div class="ms-5">Margin start (left) 5</div>
<div class="me-5">Margin end (right) 5</div>
<div class="mx-5">Margin horizontal 5</div>
<div class="my-5">Margin vertical 5</div>

<!-- Responsive -->
<div class="mt-3 mt-md-5 mt-lg-7">Responsive margin</div>
```

### Padding
```html
<!-- All sides: p-{0-10} -->
<div class="p-5">Padding 5 all sides</div>

<!-- Specific sides -->
<div class="pt-5">Padding top 5</div>
<div class="pb-5">Padding bottom 5</div>
<div class="ps-5">Padding start (left) 5</div>
<div class="pe-5">Padding end (right) 5</div>
<div class="px-5">Padding horizontal 5</div>
<div class="py-5">Padding vertical 5</div>
```

## 🎨 Colors

### Text Colors
```html
<span class="text-primary">Primary</span>
<span class="text-secondary">Secondary</span>
<span class="text-success">Success</span>
<span class="text-danger">Danger</span>
<span class="text-warning">Warning</span>
<span class="text-info">Info</span>
<span class="text-dark">Dark</span>
<span class="text-muted">Muted</span>

<!-- Gray Scale -->
<span class="text-gray-600">Gray 600</span>
<span class="text-gray-700">Gray 700</span>
<span class="text-gray-800">Gray 800</span>
<span class="text-gray-900">Gray 900</span>
```

### Background Colors
```html
<div class="bg-primary">Primary</div>
<div class="bg-light-primary">Light Primary</div>
<div class="bg-success">Success</div>
<div class="bg-light-success">Light Success</div>
```

## 📝 Typography

### Font Sizes
```html
<span class="fs-1">Largest (2.5rem)</span>
<span class="fs-2">Larger (2rem)</span>
<span class="fs-3">Large (1.75rem)</span>
<span class="fs-4">Normal (1.5rem)</span>
<span class="fs-5">Small (1.25rem)</span>
<span class="fs-6">Smaller (1rem)</span>
<span class="fs-7">Smallest (0.95rem)</span>
```

### Font Weights
```html
<span class="fw-bold">Bold (700)</span>
<span class="fw-bolder">Bolder (800)</span>
<span class="fw-semibold">Semi Bold (600)</span>
<span class="fw-normal">Normal (400)</span>
<span class="fw-light">Light (300)</span>
```

### Text Alignment
```html
<div class="text-start">Left aligned</div>
<div class="text-center">Center aligned</div>
<div class="text-end">Right aligned</div>
```

### Text Transform
```html
<span class="text-lowercase">lowercase</span>
<span class="text-uppercase">UPPERCASE</span>
<span class="text-capitalize">Capitalize</span>
```

## 🎯 Display & Visibility

### Display
```html
<div class="d-none">Hidden</div>
<div class="d-block">Block</div>
<div class="d-inline">Inline</div>
<div class="d-inline-block">Inline Block</div>
<div class="d-flex">Flex</div>

<!-- Responsive -->
<div class="d-none d-md-block">Hidden on mobile, visible on tablet+</div>
<div class="d-block d-lg-none">Visible on mobile/tablet, hidden on desktop</div>
```

## 🔗 Separators

```html
<!-- Horizontal -->
<div class="separator"></div>
<div class="separator separator-dashed"></div>
<div class="separator my-5"></div>

<!-- With Content -->
<div class="separator separator-content my-5">
    <span class="text-muted">OR</span>
</div>
```

## 📱 Responsive Breakpoints

```
xs: < 576px   (Extra small - Mobile)
sm: ≥ 576px   (Small - Mobile landscape)
md: ≥ 768px   (Medium - Tablet)
lg: ≥ 992px   (Large - Desktop)
xl: ≥ 1200px  (Extra large - Large desktop)
xxl: ≥ 1400px (Extra extra large - Wide desktop)
```

### Usage Examples
```html
<!-- Columns -->
<div class="col-12 col-md-6 col-lg-4">
    Full on mobile, half on tablet, third on desktop
</div>

<!-- Display -->
<div class="d-none d-md-block">
    Hidden on mobile, visible on tablet+
</div>

<!-- Spacing -->
<div class="mt-3 mt-md-5 mt-lg-7">
    Responsive margin top
</div>
```

## 🎨 Metronic Specific Classes

### Min Width
```html
<th class="min-w-100px">Min width 100px</th>
<th class="min-w-150px">Min width 150px</th>
<th class="min-w-200px">Min width 200px</th>
```

### Hover Effects
```html
<a class="text-hover-primary">Hover primary color</a>
<button class="btn-active-color-primary">Active primary color</button>
```

### Border Utilities
```html
<div class="border-0">No border</div>
<div class="border-top-0">No top border</div>
<div class="border-bottom">Bottom border</div>
```

---

**Quick Tip:** Combine classes untuk hasil yang lebih baik!

```html
<div class="card card-custom mb-5">
    <div class="card-body d-flex align-items-center justify-content-between p-5">
        <div class="d-flex flex-column">
            <span class="text-gray-500 fw-bold fs-7 text-uppercase mb-2">Label</span>
            <span class="text-gray-900 fw-bolder fs-2x">Value</span>
        </div>
        <div class="symbol symbol-50px">
            <span class="symbol-label bg-light-primary">
                <i class="ki-outline ki-chart-line fs-2x text-primary"></i>
            </span>
        </div>
    </div>
</div>
```
