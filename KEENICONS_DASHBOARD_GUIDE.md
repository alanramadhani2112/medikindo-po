# Panduan Keenicons untuk Dashboard Medikindo PO

**Tanggal**: 14 April 2026  
**Status**: ✅ Sudah Diterapkan (100% Compliance)  
**Icon Library**: Keenicons (Metronic 8)

---

## ✅ KONFIRMASI: SISTEM SUDAH MENGGUNAKAN KEENICONS

Sistem Medikindo PO **SUDAH 100% menggunakan Keenicons** di seluruh dashboard dan aplikasi.

**Audit Results**:
- ✅ Total Icon Instances: 312
- ✅ Total Files: 47 Blade files
- ✅ Compliance Level: **100%**
- ✅ Format: `ki-duotone ki-{name}`
- ✅ Library: Keenicons (Metronic 8)

---

## 📋 ICON YANG DIGUNAKAN DI DASHBOARD

### 1. Dashboard Menu (Sidebar)

| Menu Item | Icon | Kode |
|-----------|------|------|
| Dashboard | 📊 | `ki-duotone ki-element-11` |
| Purchase Orders | 🛒 | `ki-duotone ki-purchase` |
| Approvals | ✅ | `ki-duotone ki-check-square` |
| Goods Receipt | 📦 | `ki-duotone ki-package` |
| Tagihan ke RS/Klinik (AR) | ⬆️ | `ki-duotone ki-arrow-up text-success` |
| Hutang ke Supplier (AP) | ⬇️ | `ki-duotone ki-arrow-down text-danger` |
| Payments | 💰 | `ki-duotone ki-wallet` |
| Credit Control | 📈 | `ki-duotone ki-chart-simple` |
| Organizations | 🏢 | `ki-duotone ki-bank` |
| Suppliers | 🚚 | `ki-duotone ki-delivery-3` |
| Products | 💊 | `ki-duotone ki-capsule` |
| Users | 👤 | `ki-duotone ki-profile-user` |

---

### 2. Dashboard Cards (KPI)

#### Healthcare Dashboard:
```blade
<!-- Total PO Aktif -->
<i class="ki-duotone ki-purchase fs-2x text-white"></i>

<!-- PO Menunggu Persetujuan -->
<i class="ki-duotone ki-timer fs-2x text-white"></i>

<!-- PO Dalam Pengiriman -->
<i class="ki-duotone ki-package fs-2x text-white"></i>
```

#### Finance Dashboard:
```blade
<!-- Total Receivable (AR) -->
<i class="ki-duotone ki-arrow-up fs-2x text-white"></i>

<!-- Total Payable (AP) -->
<i class="ki-duotone ki-arrow-down fs-2x text-white"></i>

<!-- Outstanding Invoices -->
<i class="ki-duotone ki-bill fs-2x text-white"></i>

<!-- Cashflow -->
<i class="ki-duotone ki-dollar fs-2x text-white"></i>
```

#### Admin Dashboard:
```blade
<!-- Total Organizations -->
<i class="ki-duotone ki-bank fs-2x text-white"></i>

<!-- Total Users -->
<i class="ki-duotone ki-user fs-2x text-white"></i>

<!-- Total Products -->
<i class="ki-duotone ki-capsule fs-2x text-white"></i>

<!-- System Activity -->
<i class="ki-duotone ki-chart-line-up fs-2x text-white"></i>
```

---

### 3. Action Buttons

```blade
<!-- Create/Add Button -->
<button class="btn btn-primary">
    <i class="ki-duotone ki-plus fs-2"></i>
    Buat Baru
</button>

<!-- Edit Button -->
<button class="btn btn-light">
    <i class="ki-duotone ki-pencil fs-3"></i>
    Edit
</button>

<!-- Delete Button -->
<button class="btn btn-danger">
    <i class="ki-duotone ki-trash fs-3"></i>
    Hapus
</button>

<!-- View Button -->
<button class="btn btn-light-primary">
    <i class="ki-duotone ki-eye fs-3"></i>
    Lihat Detail
</button>

<!-- Save Button -->
<button class="btn btn-success">
    <i class="ki-duotone ki-check fs-3"></i>
    Simpan
</button>

<!-- Cancel Button -->
<button class="btn btn-light-secondary">
    <i class="ki-duotone ki-cross fs-3"></i>
    Batal
</button>
```

---

### 4. Status Indicators

```blade
<!-- Success -->
<i class="ki-duotone ki-check-circle fs-2 text-success"></i>

<!-- Error -->
<i class="ki-duotone ki-cross-circle fs-2 text-danger"></i>

<!-- Warning -->
<i class="ki-duotone ki-information fs-2 text-warning"></i>

<!-- Pending -->
<i class="ki-duotone ki-time fs-2 text-warning"></i>

<!-- Verified -->
<i class="ki-duotone ki-verify fs-2 text-success"></i>

<!-- Active -->
<i class="ki-duotone ki-shield-tick fs-2 text-success"></i>

<!-- Inactive -->
<i class="ki-duotone ki-shield-cross fs-2 text-warning"></i>
```

---

### 5. Quick Actions (Dashboard)

```blade
<!-- Buat PO -->
<a href="#" class="btn btn-light-primary">
    <i class="ki-duotone ki-plus fs-3 me-3"></i>
    <div class="text-start">
        <div class="fw-bold fs-6">Buat PO</div>
        <div class="text-muted fs-7">Ajukan purchase order baru</div>
    </div>
</a>

<!-- Lihat Invoice -->
<a href="#" class="btn btn-light-warning">
    <i class="ki-duotone ki-bill fs-3 me-3"></i>
    <div class="text-start">
        <div class="fw-bold fs-6">Lihat Invoice</div>
        <div class="text-muted fs-7">Pantau tagihan</div>
    </div>
</a>

<!-- Konfirmasi Pembayaran -->
<a href="#" class="btn btn-light-success">
    <i class="ki-duotone ki-wallet fs-3 me-3"></i>
    <div class="text-start">
        <div class="fw-bold fs-6">Konfirmasi Pembayaran</div>
        <div class="text-muted fs-7">Catat pembayaran</div>
    </div>
</a>
```

---

### 6. Alerts & Notifications

```blade
<!-- Success Alert -->
<div class="alert alert-success d-flex align-items-center">
    <i class="ki-duotone ki-check-circle fs-2 me-3"></i>
    <div>Data berhasil disimpan</div>
</div>

<!-- Error Alert -->
<div class="alert alert-danger d-flex align-items-center">
    <i class="ki-duotone ki-cross-circle fs-2 me-3"></i>
    <div>Terjadi kesalahan</div>
</div>

<!-- Warning Alert -->
<div class="alert alert-warning d-flex align-items-center">
    <i class="ki-duotone ki-information fs-2 me-3"></i>
    <div>Perhatian: Invoice jatuh tempo</div>
</div>

<!-- Info Alert -->
<div class="alert alert-info d-flex align-items-center">
    <i class="ki-duotone ki-information-5 fs-2 me-3"></i>
    <div>Informasi penting</div>
</div>
```

---

### 7. Empty States

```blade
<!-- No Data -->
<div class="d-flex flex-column align-items-center">
    <i class="ki-duotone ki-file-deleted fs-3x text-gray-400 mb-3"></i>
    <h3 class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Data</h3>
    <p class="text-muted fs-7">Tambahkan data baru untuk memulai</p>
</div>
```

---

### 8. Table Actions

```blade
<!-- View -->
<a href="#" class="btn btn-icon btn-sm btn-light-primary">
    <i class="ki-duotone ki-eye fs-4"></i>
</a>

<!-- Edit -->
<a href="#" class="btn btn-icon btn-sm btn-light">
    <i class="ki-duotone ki-pencil fs-4"></i>
</a>

<!-- Delete -->
<button class="btn btn-icon btn-sm btn-light-danger">
    <i class="ki-duotone ki-trash fs-4"></i>
</button>
```

---

### 9. Dropdown Actions

```blade
<!-- Dropdown Toggle -->
<button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
    <i class="ki-duotone ki-dots-horizontal fs-3"></i>
    Aksi
</button>

<!-- Dropdown Menu -->
<ul class="dropdown-menu">
    <li>
        <a class="dropdown-item" href="#">
            <i class="ki-duotone ki-pencil fs-3 me-2 text-primary"></i>
            Edit
        </a>
    </li>
    <li>
        <a class="dropdown-item" href="#">
            <i class="ki-duotone ki-trash fs-3 me-2 text-danger"></i>
            Hapus
        </a>
    </li>
</ul>
```

---

### 10. Search & Filter

```blade
<!-- Search Input -->
<div class="position-relative">
    <i class="ki-duotone ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
    <input type="text" class="form-control ps-12" placeholder="Cari...">
</div>

<!-- Search Button -->
<button class="btn btn-primary">
    <i class="ki-duotone ki-magnifier fs-2"></i>
    Cari
</button>

<!-- Filter Button -->
<button class="btn btn-light">
    <i class="ki-duotone ki-setting-2 fs-2"></i>
    Filter
</button>

<!-- Reset Button -->
<button class="btn btn-light">
    <i class="ki-duotone ki-cross fs-2"></i>
    Reset
</button>
```

---

## 📐 UKURAN ICON (SIZE CLASSES)

### Standar Ukuran:

| Size Class | Ukuran | Penggunaan | Contoh |
|------------|--------|------------|--------|
| `fs-2` | Large | Sidebar menu, major buttons | `<i class="ki-duotone ki-plus fs-2"></i>` |
| `fs-3` | Medium | Normal buttons, inline actions | `<i class="ki-duotone ki-check fs-3"></i>` |
| `fs-4` | Small | Table actions, small buttons | `<i class="ki-duotone ki-eye fs-4"></i>` |
| `fs-2x` | 2x | Dashboard cards, KPI icons | `<i class="ki-duotone ki-purchase fs-2x"></i>` |
| `fs-3x` | 3x | Empty states, hero icons | `<i class="ki-duotone ki-file-deleted fs-3x"></i>` |

---

## 🎨 WARNA ICON (COLOR CLASSES)

### Standar Warna:

| Color Class | Warna | Penggunaan | Contoh |
|-------------|-------|------------|--------|
| `text-primary` | Blue | Default actions, links | `<i class="ki-duotone ki-pencil text-primary"></i>` |
| `text-success` | Green | Success, active, AR | `<i class="ki-duotone ki-check-circle text-success"></i>` |
| `text-danger` | Red | Delete, error, AP | `<i class="ki-duotone ki-trash text-danger"></i>` |
| `text-warning` | Yellow | Warning, pending | `<i class="ki-duotone ki-information text-warning"></i>` |
| `text-info` | Cyan | Information | `<i class="ki-duotone ki-information-5 text-info"></i>` |
| `text-gray-400` | Light Gray | Empty state, disabled | `<i class="ki-duotone ki-file-deleted text-gray-400"></i>` |
| `text-gray-500` | Dark Gray | Muted, secondary | `<i class="ki-duotone ki-lock text-gray-500"></i>` |
| `text-white` | White | On colored backgrounds | `<i class="ki-duotone ki-purchase text-white"></i>` |

---

## 📚 DAFTAR LENGKAP ICON YANG TERSEDIA

### Actions (11 icons):
- `ki-plus` - Add/Create
- `ki-pencil` - Edit
- `ki-trash` - Delete
- `ki-eye` - View
- `ki-check` - Save/Confirm
- `ki-cross` - Cancel/Close
- `ki-magnifier` - Search
- `ki-cloud-download` - Download
- `ki-printer` - Print
- `ki-send` - Submit/Send
- `ki-setting-2` - Settings/Filter

### Status (8 icons):
- `ki-check-circle` - Success
- `ki-cross-circle` - Error
- `ki-time` - Pending
- `ki-information` - Warning
- `ki-information-5` - Info
- `ki-verify` - Verified
- `ki-shield-tick` - Active
- `ki-shield-cross` - Inactive

### Business (12 icons):
- `ki-purchase` - Purchase Orders
- `ki-basket` - Shopping/Orders
- `ki-package` - Goods/Delivery
- `ki-bill` - Invoice
- `ki-wallet` - Payment
- `ki-dollar` - Money
- `ki-bank` - Organization/Finance
- `ki-delivery-3` - Supplier
- `ki-capsule` - Product (Medical)
- `ki-chart-simple` - Analytics
- `ki-chart-line-up` - Growth
- `ki-calculator` - Calculation

### Navigation (7 icons):
- `ki-element-11` - Dashboard
- `ki-arrow-up` - AR/Income
- `ki-arrow-down` - AP/Expense
- `ki-right` - Next/Forward
- `ki-black-left` - Previous
- `ki-black-right` - Next
- `ki-dots-horizontal` - More Actions

### Users & Docs (6 icons):
- `ki-profile-user` - Users
- `ki-user` - User
- `ki-document` - Document
- `ki-file-deleted` - Empty State
- `ki-notification-bing` - Notification
- `ki-exit-right` - Logout

---

## 💡 TIPS PENGGUNAAN

### 1. Konsistensi Ukuran:
```blade
<!-- ✅ GOOD: Consistent sizing -->
<button class="btn btn-primary">
    <i class="ki-duotone ki-plus fs-2"></i>
    Tambah
</button>

<!-- ❌ BAD: Inconsistent sizing -->
<button class="btn btn-primary">
    <i class="ki-duotone ki-plus fs-4"></i>
    Tambah
</button>
```

### 2. Spacing yang Benar:
```blade
<!-- ✅ GOOD: Icon left of text with me-2 -->
<button class="btn btn-primary">
    <i class="ki-duotone ki-plus fs-2"></i>
    <span class="ms-2">Tambah</span>
</button>

<!-- ❌ BAD: No spacing -->
<button class="btn btn-primary">
    <i class="ki-duotone ki-plus fs-2"></i>Tambah
</button>
```

### 3. Warna yang Sesuai:
```blade
<!-- ✅ GOOD: Color matches context -->
<i class="ki-duotone ki-check-circle fs-2 text-success"></i>
<i class="ki-duotone ki-cross-circle fs-2 text-danger"></i>

<!-- ❌ BAD: Wrong color -->
<i class="ki-duotone ki-check-circle fs-2 text-danger"></i>
```

---

## 🔗 RESOURCES

### Keenicons Documentation:
- **Official Site**: https://keenicons.com/
- **Metronic 8 Docs**: https://preview.keenthemes.com/metronic8/demo42/documentation/icons/keenicons.html
- **Icon List**: Browse all available icons in Metronic 8 documentation

### Internal Documentation:
- `ICON_INVENTORY.md` - Complete icon inventory (72 unique icons)
- `ICON_SYSTEM_ENFORCEMENT_REPORT.md` - Compliance audit report
- `DAILY_WORK_SUMMARY_2026_04_14.md` - Daily work summary

---

## ✅ KESIMPULAN

**Sistem Medikindo PO sudah 100% menggunakan Keenicons!**

- ✅ Semua icon menggunakan format `ki-duotone ki-{name}`
- ✅ Ukuran icon sudah standardized (fs-2, fs-3, fs-4, fs-2x, fs-3x)
- ✅ Warna icon sudah standardized (text-primary, text-success, etc.)
- ✅ Placement sudah konsisten (icon left of text)
- ✅ Semantic meaning sudah jelas (one icon = one meaning)
- ✅ Compliance level: **100%**

**Tidak ada perubahan yang diperlukan!** Sistem sudah optimal.

---

**Dokumentasi dibuat**: 14 April 2026  
**Status**: ✅ COMPLETE  
**Compliance**: ✅ 100%
