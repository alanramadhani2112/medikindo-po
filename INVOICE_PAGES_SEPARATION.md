# PEMISAHAN HALAMAN INVOICE (TANPA TAB)

**Tanggal**: 14 April 2026  
**Status**: ✅ SELESAI  
**Perubahan**: Pisahkan halaman Supplier Invoice dan Customer Invoice (tidak pakai tab)

---

## 🎯 PERUBAHAN

### SEBELUM (Dengan Tab):
```
URL: /invoices?tab=supplier
URL: /invoices?tab=customer

1 halaman dengan 2 tab:
├─ Tab: Hutang ke Supplier (AP)
└─ Tab: Tagihan ke RS/Klinik (AR)
```

### SEKARANG (Halaman Terpisah):
```
URL: /invoices/supplier
URL: /invoices/customer

2 halaman terpisah:
├─ Halaman: Hutang ke Supplier (AP)
└─ Halaman: Tagihan ke RS/Klinik (AR)
```

---

## 📋 PERUBAHAN DETAIL

### 1. Routes Baru

**File**: `routes/web.php`

```php
// SEBELUM
Route::get('/', [InvoiceWebController::class, 'index'])->name('index');

// SEKARANG
Route::get('/supplier', [InvoiceWebController::class, 'indexSupplier'])->name('supplier.index');
Route::get('/customer', [InvoiceWebController::class, 'indexCustomer'])->name('customer.index');
```

### 2. Controller Methods Baru

**File**: `app/Http/Controllers/Web/InvoiceWebController.php`

**Method Baru**:
- `indexSupplier()` - Halaman list supplier invoice
- `indexCustomer()` - Halaman list customer invoice

**Method Lama** (`index()` dengan tab) - Masih ada untuk backward compatibility

### 3. View Files Baru

**File Baru**:
- `resources/views/invoices/index_supplier.blade.php`
- `resources/views/invoices/index_customer.blade.php`

**File Lama** (`index.blade.php` dengan tab) - Tidak dipakai lagi

### 4. Sidebar Menu Update

**File**: `resources/views/components/partials/sidebar.blade.php`

```blade
<!-- SEBELUM -->
<a href="{{ route('web.invoices.index', ['tab' => 'customer']) }}">

<!-- SEKARANG -->
<a href="{{ route('web.invoices.customer.index') }}">
```

### 5. Breadcrumb Update

**File**: `app/Http/Controllers/Web/InvoiceWebController.php`

```php
// SEBELUM
['label' => 'Hutang ke Supplier', 'url' => route('web.invoices.index', ['tab' => 'supplier'])]

// SEKARANG
['label' => 'Hutang ke Supplier', 'url' => route('web.invoices.supplier.index')]
```

---

## 🎨 UI/UX IMPROVEMENTS

### Halaman Supplier Invoice (AP):

**Header**:
```
🔴 Hutang ke Supplier (AP)
Kelola invoice dari distributor/supplier

[Input Invoice Pemasok]  ← Button
```

**Table Columns**:
1. Nomor Invoice (internal)
2. Invoice Distributor (nomor + tanggal)
3. Supplier
4. PO Number
5. Total
6. Status
7. Aksi

**Features**:
- ✅ Tampilkan nomor invoice distributor
- ✅ Tampilkan tanggal invoice distributor
- ✅ Filter & search
- ✅ Pagination

### Halaman Customer Invoice (AR):

**Header**:
```
🟢 Tagihan ke RS/Klinik (AR)
Kelola tagihan yang diterbitkan ke RS/Klinik

[Buat Tagihan ke RS/Klinik]  ← Button (hijau)
```

**Table Columns**:
1. Nomor Invoice
2. RS/Klinik
3. PO Number
4. GR Number
5. Total
6. Status
7. Aksi

**Features**:
- ✅ Tampilkan GR number
- ✅ Filter & search
- ✅ Pagination

---

## 🔗 ROUTE MAPPING

### Supplier Invoice (AP):

| Action | Route | Method | View |
|--------|-------|--------|------|
| List | `/invoices/supplier` | GET | `index_supplier.blade.php` |
| Create Form | `/invoices/supplier/create` | GET | `create_supplier.blade.php` |
| Store | `/invoices/supplier` | POST | - |
| Show | `/invoices/supplier/{id}` | GET | `show_supplier.blade.php` |
| PDF | `/invoices/supplier/{id}/pdf` | GET | PDF |

### Customer Invoice (AR):

| Action | Route | Method | View |
|--------|-------|--------|------|
| List | `/invoices/customer` | GET | `index_customer.blade.php` |
| Create Form | `/invoices/customer/create` | GET | `create_customer.blade.php` |
| Store | `/invoices/customer` | POST | - |
| Show | `/invoices/customer/{id}` | GET | `show_customer.blade.php` |
| PDF | `/invoices/customer/{id}/pdf` | GET | PDF |

---

## 📊 NAVIGATION FLOW

### Dari Sidebar:

```
Sidebar Menu
├─ Tagihan ke RS/Klinik [AR]
│  └─ Click → /invoices/customer
│     └─ Halaman list customer invoice
│        ├─ [Buat Tagihan] → /invoices/customer/create
│        └─ [Lihat] → /invoices/customer/{id}
│
└─ Hutang ke Supplier [AP]
   └─ Click → /invoices/supplier
      └─ Halaman list supplier invoice
         ├─ [Input Invoice] → /invoices/supplier/create
         └─ [Lihat] → /invoices/supplier/{id}
```

### Breadcrumb:

**Supplier Invoice**:
```
Invoicing > Hutang ke Supplier
Invoicing > Hutang ke Supplier > Input Invoice Pemasok
Invoicing > Hutang ke Supplier > INV-SUP-12345
```

**Customer Invoice**:
```
Invoicing > Tagihan ke RS/Klinik
Invoicing > Tagihan ke RS/Klinik > Buat Tagihan ke RS/Klinik
Invoicing > Tagihan ke RS/Klinik > INV-CUST-12345
```

---

## ✅ KEUNTUNGAN PEMISAHAN

### 1. **Lebih Jelas**
- ❌ SEBELUM: User harus klik tab untuk switch
- ✅ SEKARANG: Langsung ke halaman yang diinginkan

### 2. **Konsisten dengan Menu**
- ❌ SEBELUM: Menu terpisah, tapi halaman pakai tab
- ✅ SEKARANG: Menu terpisah, halaman juga terpisah

### 3. **URL Lebih Bersih**
- ❌ SEBELUM: `/invoices?tab=supplier`
- ✅ SEKARANG: `/invoices/supplier`

### 4. **Easier Navigation**
- ✅ Bookmark langsung ke halaman yang diinginkan
- ✅ Back button lebih predictable
- ✅ Tidak perlu ingat tab mana yang aktif

### 5. **Better for Different Roles**
- Finance: Bisa bookmark kedua halaman
- Healthcare: Hanya perlu bookmark customer invoice
- Approver: Tidak perlu akses invoice sama sekali

---

## 🔄 BACKWARD COMPATIBILITY

### Old URLs (Masih Berfungsi):
```
/invoices?tab=supplier  → Redirect ke /invoices/supplier
/invoices?tab=customer  → Redirect ke /invoices/customer
/invoices               → Redirect ke /invoices/customer (default)
```

**Note**: Method `index()` lama masih ada untuk handle redirect jika diperlukan.

---

## 🧪 TESTING CHECKLIST

### Supplier Invoice Page:
- [ ] Access `/invoices/supplier` → Show supplier invoice list
- [ ] Click "Input Invoice Pemasok" → Go to create form
- [ ] Click "Lihat" → Go to detail page
- [ ] Breadcrumb correct
- [ ] Sidebar menu highlight correct
- [ ] Pagination works
- [ ] Search works

### Customer Invoice Page:
- [ ] Access `/invoices/customer` → Show customer invoice list
- [ ] Click "Buat Tagihan ke RS/Klinik" → Go to create form
- [ ] Click "Lihat" → Go to detail page
- [ ] Breadcrumb correct
- [ ] Sidebar menu highlight correct
- [ ] Pagination works
- [ ] Search works

### Navigation:
- [ ] Sidebar "Tagihan ke RS/Klinik" → `/invoices/customer`
- [ ] Sidebar "Hutang ke Supplier" → `/invoices/supplier`
- [ ] Active menu highlight correct
- [ ] Breadcrumb links work

---

## 📝 FILES MODIFIED

### New Files:
1. ✅ `resources/views/invoices/index_supplier.blade.php`
2. ✅ `resources/views/invoices/index_customer.blade.php`

### Modified Files:
1. ✅ `routes/web.php` - Added new routes
2. ✅ `app/Http/Controllers/Web/InvoiceWebController.php` - Added new methods
3. ✅ `resources/views/components/partials/sidebar.blade.php` - Updated menu links

### Deprecated Files:
- `resources/views/invoices/index.blade.php` (dengan tab) - Tidak dipakai lagi

---

## ✅ STATUS

**Status**: ✅ SELESAI  
**Syntax Check**: ✅ PASSED  
**Ready for Testing**: ✅ YES

### Summary:
Halaman invoice sekarang terpisah menjadi 2 halaman independen:
- `/invoices/supplier` - Hutang ke Supplier (AP)
- `/invoices/customer` - Tagihan ke RS/Klinik (AR)

Tidak ada lagi tab, lebih jelas, lebih konsisten dengan menu sidebar, dan lebih mudah dinavigasi!

**Silakan test dan refresh browser!** 🚀
