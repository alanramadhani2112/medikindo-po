# Metronic 8 Bootstrap Integration Guide

## ✅ Masalah yang Sudah Diperbaiki

### 1. Font Keenicons 404 Error
**Masalah:** File font keenicons-outline.ttf dan keenicons-outline.woff tidak ditemukan (404)

**Solusi:** Font sudah disalin dari `public/assets/metronic/plugins/keenicons/fonts/` ke `public/assets/metronic8/plugins/global/fonts/keenicons/`

**File yang disalin:**
- keenicons-duotone.svg, .ttf, .woff
- keenicons-filled.svg, .ttf, .woff
- keenicons-outline.svg, .ttf, .woff
- keenicons-solid.svg, .ttf, .woff

### 2. Tailwind CSS Conflict
**Masalah:** Tailwind CSS konflik dengan Bootstrap Metronic

**Solusi:** 
- ✅ Tailwind CSS sudah dihapus dari project
- ✅ Fokus 100% ke Bootstrap Metronic
- ✅ Dependencies sudah dibersihkan
- ✅ Vite config sudah diupdate
- ✅ CSS sudah dikonversi ke Bootstrap classes

## Struktur Asset Metronic 8

```
public/assets/metronic8/
├── css/
│   └── style.bundle.css          # Main Metronic styles
├── js/
│   ├── scripts.bundle.js         # Main Metronic scripts
│   └── widgets.bundle.js         # Widget scripts
├── media/
│   ├── auth/                     # Auth page images
│   ├── avatars/                  # Avatar images
│   ├── flags/                    # Country flags
│   ├── icons/                    # Icon sets
│   ├── logos/                    # Logo images
│   └── ... (dan lainnya)
└── plugins/
    ├── global/
    │   ├── fonts/
    │   │   └── keenicons/        # ✅ Font icons (FIXED)
    │   ├── plugins.bundle.css    # Plugin styles
    │   └── plugins.bundle.js     # Plugin scripts
    └── custom/
        └── datatables/           # DataTables plugin
```

## File Layout Utama

### resources/views/layouts/app.blade.php
Layout utama yang menggunakan Metronic 8 dengan struktur:
- Header (top navigation)
- Sidebar (menu navigasi)
- Toolbar (breadcrumb & page title)
- Content area
- Footer

### Partials
- `resources/views/components/partials/header.blade.php` - Top navigation bar
- `resources/views/components/partials/sidebar.blade.php` - Side menu
- `resources/views/components/partials/toolbar.blade.php` - Breadcrumb & title

## Asset yang Dimuat

### CSS (Loaded in Order)
```html
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

<!-- Metronic Plugins Bundle (Bootstrap, Icons, etc) -->
<link href="{{ asset('assets/metronic8/plugins/global/plugins.bundle.css') }}" />

<!-- Metronic Custom Styles -->
<link href="{{ asset('assets/metronic8/css/style.bundle.css') }}" />

<!-- Custom App Styles -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

### JavaScript (Loaded in Order)
```html
<!-- Metronic Plugins Bundle (jQuery, Bootstrap, etc) -->
<script src="{{ asset('assets/metronic8/plugins/global/plugins.bundle.js') }}"></script>

<!-- Metronic Custom Scripts -->
<script src="{{ asset('assets/metronic8/js/scripts.bundle.js') }}"></script>

<!-- Alpine.js for Interactivity -->
@vite(['resources/js/app.js'])
```

## Bootstrap Classes yang Digunakan

### Layout Classes
```html
<!-- Container -->
<div class="container-fluid">
<div class="container">

<!-- Grid System -->
<div class="row g-5 g-xl-8">
    <div class="col-xl-3 col-md-6">
    <div class="col-xl-8">
</div>

<!-- Flexbox -->
<div class="d-flex align-items-center justify-content-between">
<div class="d-flex flex-column">
```

### Card Components
```html
<!-- Basic Card -->
<div class="card card-custom">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <!-- Actions -->
        </div>
    </div>
    <div class="card-body">
        <!-- Content -->
    </div>
</div>
```

### Table Components
```html
<div class="table-responsive">
    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
        <thead>
            <tr class="fw-bold text-muted">
                <th class="min-w-150px">Column</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Button Components
```html
<!-- Primary Button -->
<a href="#" class="btn btn-primary">Button</a>
<a href="#" class="btn btn-sm btn-primary">Small Button</a>

<!-- Light Variants -->
<a href="#" class="btn btn-light-primary">Light Primary</a>
<a href="#" class="btn btn-light-success">Light Success</a>
<a href="#" class="btn btn-light-warning">Light Warning</a>
<a href="#" class="btn btn-light-danger">Light Danger</a>
```

### Badge Components
```html
<span class="badge badge-light-primary">Primary</span>
<span class="badge badge-light-success">Success</span>
<span class="badge badge-light-warning">Warning</span>
<span class="badge badge-light-danger">Danger</span>
<span class="badge badge-light-info">Info</span>
```

### Symbol/Avatar Components
```html
<div class="symbol symbol-50px">
    <span class="symbol-label bg-light-primary">
        <i class="ki-outline ki-user fs-2x text-primary"></i>
    </span>
</div>
```

## Icon Classes (Keenicons)

Metronic 8 menggunakan Keenicons dengan 3 style:

### Outline (Default - Recommended)
```html
<i class="ki-outline ki-home fs-2"></i>
<i class="ki-outline ki-user fs-2"></i>
<i class="ki-outline ki-document fs-2"></i>
<i class="ki-outline ki-chart-line fs-2"></i>
```

### Duotone (Two-tone)
```html
<i class="ki-duotone ki-home fs-2">
    <span class="path1"></span>
    <span class="path2"></span>
</i>
```

### Solid (Filled)
```html
<i class="ki-solid ki-home fs-2"></i>
<i class="ki-solid ki-user fs-2"></i>
```

### Icon Sizes
```html
<i class="ki-outline ki-home fs-1"></i>  <!-- Largest -->
<i class="ki-outline ki-home fs-2"></i>  <!-- Large -->
<i class="ki-outline ki-home fs-3"></i>  <!-- Medium -->
<i class="ki-outline ki-home fs-4"></i>  <!-- Small -->
<i class="ki-outline ki-home fs-5"></i>  <!-- Smallest -->
```

## Typography Classes

### Font Sizes
```html
<span class="fs-1">Largest</span>
<span class="fs-2">Larger</span>
<span class="fs-3">Large</span>
<span class="fs-4">Normal</span>
<span class="fs-5">Small</span>
<span class="fs-6">Smaller</span>
<span class="fs-7">Smallest</span>
```

### Font Weights
```html
<span class="fw-bold">Bold</span>
<span class="fw-bolder">Bolder</span>
<span class="fw-semibold">Semi Bold</span>
<span class="fw-normal">Normal</span>
```

### Text Colors
```html
<span class="text-primary">Primary</span>
<span class="text-success">Success</span>
<span class="text-warning">Warning</span>
<span class="text-danger">Danger</span>
<span class="text-info">Info</span>
<span class="text-dark">Dark</span>
<span class="text-muted">Muted</span>
<span class="text-gray-600">Gray 600</span>
<span class="text-gray-900">Gray 900</span>
```

## Spacing Utilities

### Margin & Padding
```html
<!-- Margin -->
<div class="m-5">Margin all sides</div>
<div class="mt-5">Margin top</div>
<div class="mb-5">Margin bottom</div>
<div class="ms-5">Margin start (left)</div>
<div class="me-5">Margin end (right)</div>
<div class="mx-5">Margin horizontal</div>
<div class="my-5">Margin vertical</div>

<!-- Padding -->
<div class="p-5">Padding all sides</div>
<div class="pt-5">Padding top</div>
<div class="pb-5">Padding bottom</div>
<div class="ps-5">Padding start (left)</div>
<div class="pe-5">Padding end (right)</div>
<div class="px-5">Padding horizontal</div>
<div class="py-5">Padding vertical</div>

<!-- Sizes: 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 -->
```

## Custom CSS (resources/css/app.css)

File ini berisi custom styles tambahan:
- Custom scrollbar styling
- Utility classes (.text-mono)
- Custom card styles
- Custom table styles
- Custom badge styles
- Responsive utilities
- Print styles

## Development Workflow

### 1. Install Dependencies
```bash
npm install
```

### 2. Development Mode (Watch for changes)
```bash
npm run dev
```

### 3. Build for Production
```bash
npm run build
```

### 4. Clear Laravel Cache (if needed)
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

## Troubleshooting

### Jika masih ada error 404 untuk asset:

1. **Periksa console browser** (F12) untuk melihat file apa yang tidak ditemukan
2. **Cari file di template asli** (`C:\laragon\www\dist\dist`)
3. **Salin ke lokasi yang sesuai** di `public/assets/metronic8/`
4. **Clear browser cache** (Ctrl + Shift + R)

### Jika styling tidak muncul:

1. **Build ulang assets:**
   ```bash
   npm run build
   ```

2. **Clear Laravel cache:**
   ```bash
   php artisan view:clear
   php artisan cache:clear
   ```

3. **Hard refresh browser:** Ctrl + Shift + R

### Jika icon tidak muncul:

1. **Periksa font sudah ada:**
   ```bash
   ls public/assets/metronic8/plugins/global/fonts/keenicons/
   ```

2. **Pastikan menggunakan class yang benar:**
   ```html
   <i class="ki-outline ki-home fs-2"></i>
   ```

## Komponen Metronic yang Sudah Digunakan

- ✅ App Layout (Header, Sidebar, Content, Footer)
- ✅ Cards & Card Headers
- ✅ Buttons (Primary, Light variants)
- ✅ Badges (Light variants)
- ✅ Tables (Dashed rows, Gray rows)
- ✅ Forms (akan diupdate)
- ✅ Alerts (Success, Danger, Warning, Info)
- ✅ Menus & Dropdowns
- ✅ Icons (Keenicons Outline)
- ✅ Symbols (Avatar placeholders)
- ✅ Grid System (Bootstrap 5)
- ✅ Flexbox Utilities
- ✅ Spacing Utilities
- ✅ Typography Utilities

## Referensi

- **Metronic Documentation:** https://preview.keenthemes.com/metronic8/laravel/docs/
- **Bootstrap 5 Documentation:** https://getbootstrap.com/docs/5.3/
- **Keenicons:** https://keenicons.com/

## Status Integrasi

- ✅ Font Keenicons - **FIXED**
- ✅ Tailwind CSS - **REMOVED**
- ✅ Bootstrap Metronic - **ACTIVE**
- ✅ Layout Structure - **COMPLETE**
- ✅ Dashboard Page - **CONVERTED TO BOOTSTRAP**
- ✅ Build Process - **WORKING**

---

**Last Updated:** 12 April 2026  
**Status:** ✅ Ready for Development  
**Framework:** Bootstrap 5 + Metronic 8

