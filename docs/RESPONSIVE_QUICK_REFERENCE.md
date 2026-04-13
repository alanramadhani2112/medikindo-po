# RESPONSIVE DESIGN - QUICK REFERENCE 📱

**Quick guide untuk developer saat membuat view baru**

---

## 🎯 BREAKPOINTS

```css
/* Large Desktop */
>= 1400px

/* Desktop */
992px - 1399px

/* Tablet */
768px - 991px

/* Mobile */
576px - 767px

/* Small Mobile */
<= 575px
```

---

## 📋 BOOTSTRAP RESPONSIVE CLASSES

### Hide/Show Columns:

```html
<!-- Always visible -->
<th>Column Name</th>

<!-- Hide on mobile, show on tablet+ -->
<th class="d-none d-md-table-cell">Column Name</th>

<!-- Hide on tablet, show on desktop+ -->
<th class="d-none d-lg-table-cell">Column Name</th>

<!-- Hide on small mobile, show on mobile+ -->
<th class="d-none d-sm-table-cell">Column Name</th>
```

### Responsive Grid:

```html
<!-- Stack on mobile, 2 cols on tablet, 4 cols on desktop -->
<div class="row">
    <div class="col-12 col-md-6 col-xl-3">Card 1</div>
    <div class="col-12 col-md-6 col-xl-3">Card 2</div>
    <div class="col-12 col-md-6 col-xl-3">Card 3</div>
    <div class="col-12 col-md-6 col-xl-3">Card 4</div>
</div>
```

---

## 🎨 COLUMN PRIORITY GUIDE

### Priority 1 (Always Visible):
- Name/Title column
- Critical status
- Action buttons

### Priority 2 (Hide on Mobile):
- Secondary information
- Category/Type
- Dates

### Priority 3 (Hide on Tablet):
- Detailed information
- Additional metadata
- Long descriptions

---

## 📝 TEMPLATE EXAMPLES

### Standard Table:

```html
<div class="table-responsive">
    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
        <thead>
            <tr class="fw-bold text-muted bg-light">
                <th class="ps-4 min-w-250px rounded-start">Name</th>
                <th class="min-w-150px d-none d-md-table-cell">Category</th>
                <th class="min-w-200px d-none d-lg-table-cell">Details</th>
                <th class="min-w-100px">Status</th>
                <th class="text-end min-w-100px pe-4 rounded-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="ps-4">...</td>
                <td class="d-none d-md-table-cell">...</td>
                <td class="d-none d-lg-table-cell">...</td>
                <td>...</td>
                <td class="text-end pe-4">...</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Page Header:

```html
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Page Title</h1>
        <p class="text-gray-600 fs-6 mb-0">Description</p>
    </div>
    <a href="#" class="btn btn-primary">
        <i class="ki-outline ki-plus fs-2"></i>
        Add New
    </a>
</div>
```

### Filter Bar:

```html
<div class="card mb-5">
    <div class="card-body">
        <form method="GET" class="d-flex flex-wrap gap-3">
            <!-- Search -->
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="Search...">
                </div>
            </div>
            
            <!-- Filter -->
            <select name="filter" class="form-select form-select-solid" style="max-width: 200px;">
                <option value="">All</option>
            </select>
            
            <!-- Buttons -->
            <button type="submit" class="btn btn-light-primary">
                <i class="ki-outline ki-magnifier fs-2"></i>
                Search
            </button>
        </form>
    </div>
</div>
```

### Dashboard Cards:

```html
<div class="row g-5 g-xl-8">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column justify-content-between p-6">
                <div class="d-flex align-items-center justify-content-between mb-5">
                    <div class="d-flex flex-column flex-grow-1 me-3">
                        <span class="text-gray-500 fw-semibold fs-7 mb-2">Label</span>
                        <span class="text-gray-900 fw-bold fs-2x">Value</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-light-primary rounded" style="width:60px;height:60px;">
                        <i class="ki-outline ki-chart fs-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## ✅ TESTING CHECKLIST

### Before Committing:

```bash
# 1. Run responsive test
./scripts/test-responsive.ps1

# 2. Clear cache
php artisan view:clear

# 3. Hard refresh browser
Ctrl + Shift + R
```

### Manual Testing:

1. **Desktop (1920px)**
   - All columns visible
   - Proper spacing
   - No horizontal scroll

2. **Tablet (768px)**
   - Some columns hidden
   - Buttons wrap properly
   - Readable content

3. **Mobile (390px)**
   - Critical columns only
   - Stacked layout
   - Touch-friendly buttons

4. **Small Mobile (375px)**
   - Minimal columns
   - Icon-only buttons
   - No horizontal scroll

---

## 🚫 COMMON MISTAKES

### ❌ DON'T:

```html
<!-- Fixed width -->
<div style="width: 1200px;">...</div>

<!-- No responsive classes -->
<th>Less Important Column</th>

<!-- Tiny buttons on mobile -->
<button class="btn btn-sm">Action</button>
```

### ✅ DO:

```html
<!-- Flexible width -->
<div class="container-fluid">...</div>

<!-- Responsive classes -->
<th class="d-none d-md-table-cell">Less Important Column</th>

<!-- Touch-friendly buttons -->
<button class="btn btn-sm" style="min-height: 44px;">Action</button>
```

---

## 🎯 QUICK TIPS

1. **Always use `.table-responsive` wrapper**
2. **Hide less important columns on mobile**
3. **Stack buttons vertically on mobile**
4. **Use `flex-wrap` for button groups**
5. **Test on real devices**
6. **Minimum touch target: 44px**
7. **Use relative units (rem, %)**
8. **Avoid fixed widths**

---

## 📞 NEED HELP?

### Documentation:
- Full Guide: `RESPONSIVE_DESIGN_COMPLETE.md`
- Summary: `RESPONSIVE_IMPLEMENTATION_SUMMARY.md`
- This Guide: `docs/RESPONSIVE_QUICK_REFERENCE.md`

### Testing:
```bash
./scripts/test-responsive.ps1
```

### CSS File:
```
public/css/custom-layout.css
```

---

**Happy Coding! 🚀**
