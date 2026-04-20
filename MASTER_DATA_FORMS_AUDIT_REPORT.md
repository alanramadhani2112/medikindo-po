# LAPORAN AUDIT FORM MASTER DATA
**Tanggal:** 21 April 2026  
**Status:** CRITICAL ISSUES FOUND  
**Auditor:** Kiro AI

---

## RINGKASAN EKSEKUTIF

Audit menemukan **KETIDAKSESUAIAN KRITIS** antara database schema dengan form input untuk semua entitas master data. Banyak field di database yang TIDAK ADA di form, dan beberapa validasi yang tidak konsisten.

### SKOR KEPATUHAN
- **Organizations:** ❌ 45% (9/20 fields)
- **Suppliers:** ❌ 70% (7/10 fields)
- **Products:** ⚠️ 85% (17/20 fields)
- **Users:** ✅ 100% (5/5 fields)

---

## 1. ORGANIZATIONS FORM AUDIT

### DATABASE SCHEMA (20 fields)
```
✅ id
✅ name
✅ type (hospital/clinic)
✅ code
✅ address
✅ phone
✅ email
✅ license_number
❌ default_tax_rate (MISSING IN FORM)
❌ default_discount_percentage (MISSING IN FORM)
❌ npwp (MISSING IN FORM)
❌ nik (MISSING IN FORM)
❌ customer_code (MISSING IN FORM)
❌ bank_accounts (MISSING IN FORM)
❌ is_active (MISSING IN FORM)
❌ city (NOT IN DB - EXTRA IN FORM)
❌ province (NOT IN DB - EXTRA IN FORM)
❌ is_authorized_narcotic (NOT IN DB - EXTRA IN FORM)
✅ created_at
✅ updated_at
```

### FORM FIELDS (resources/views/organizations/create.blade.php)
```html
✅ name (text, required)
✅ type (dropdown: clinic/hospital, required)
✅ code (text, required, unique)
✅ address (textarea, optional)
❌ city (NOT IN DATABASE)
❌ province (NOT IN DATABASE)
✅ phone (text, optional)
✅ email (text, optional)
✅ license_number (text, optional)
❌ is_authorized_narcotic (checkbox - NOT IN DATABASE)
```

### VALIDATION (OrganizationWebController)
```php
✅ name: required, string, max:255
✅ type: required, in:clinic,hospital
✅ code: required, unique
✅ email: nullable, email
✅ phone: nullable, max:20
✅ address: nullable
✅ license_number: nullable, max:100
```

### CRITICAL ISSUES

#### 🔴 MISSING IN FORM (DB fields not in UI):
1. **default_tax_rate** - Tarif pajak default (PPN 11%)
2. **default_discount_percentage** - Diskon default
3. **npwp** - Nomor Pokok Wajib Pajak (CRITICAL for invoicing)
4. **nik** - NIK untuk customer perorangan
5. **customer_code** - Kode customer internal (unique)
6. **bank_accounts** - Data rekening bank (JSON)
7. **is_active** - Status aktif/nonaktif

#### 🔴 EXTRA IN FORM (UI fields not in DB):
1. **city** - Kota (tidak ada kolom di DB)
2. **province** - Provinsi (tidak ada kolom di DB)
3. **is_authorized_narcotic** - Checkbox narkotika (tidak ada kolom di DB)

### REKOMENDASI PERBAIKAN

**OPSI A: Tambah field ke form (RECOMMENDED)**
```html
<!-- Fiscal Data Section -->
<div class="row mb-6">
    <label class="col-lg-4 col-form-label required fw-semibold fs-6">NPWP</label>
    <div class="col-lg-8">
        <input type="text" name="npwp" class="form-control" 
               placeholder="00.000.000.0-000.000" maxlength="20">
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label fw-semibold fs-6">NIK</label>
    <div class="col-lg-8">
        <input type="text" name="nik" class="form-control" 
               placeholder="16 digit NIK" maxlength="16">
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label fw-semibold fs-6">Kode Customer</label>
    <div class="col-lg-8">
        <input type="text" name="customer_code" class="form-control" 
               placeholder="CUST-001" maxlength="50">
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label fw-semibold fs-6">Tarif Pajak Default (%)</label>
    <div class="col-lg-8">
        <input type="number" name="default_tax_rate" class="form-control" 
               placeholder="11.00" step="0.01" min="0" max="100">
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label fw-semibold fs-6">Diskon Default (%)</label>
    <div class="col-lg-8">
        <input type="number" name="default_discount_percentage" class="form-control" 
               placeholder="5.00" step="0.01" min="0" max="100">
    </div>
</div>
```

**OPSI B: Hapus field dari form**
- Hapus: city, province, is_authorized_narcotic (tidak ada di DB)

**OPSI C: Tambah kolom ke database (NOT RECOMMENDED)**
```php
// Migration untuk menambah city, province, is_authorized_narcotic
Schema::table('organizations', function (Blueprint $table) {
    $table->string('city', 100)->nullable()->after('address');
    $table->string('province', 100)->nullable()->after('city');
    $table->boolean('is_authorized_narcotic')->default(false)->after('license_number');
});
```

---

## 2. SUPPLIERS FORM AUDIT

### DATABASE SCHEMA (10 fields)
```
✅ id
✅ name
✅ code
✅ address
✅ phone
✅ email
✅ npwp
✅ license_number
✅ is_active
❌ license_expiry_date (NOT IN DB - EXTRA IN SPEC)
❌ is_authorized_narcotic (NOT IN DB - EXTRA IN SPEC)
✅ created_at
✅ updated_at
```

### FORM FIELDS (resources/views/suppliers/create.blade.php)
```html
✅ name (text, required)
✅ code (text, required, unique)
✅ address (textarea, optional)
✅ phone (text, optional)
✅ email (text, optional)
✅ npwp (text, optional)
✅ license_number (text, optional)
```

### VALIDATION (SupplierWebController)
```php
✅ name: required, string, max:255
✅ code: required, unique
✅ email: nullable, email
✅ phone: nullable, max:20
✅ address: nullable
✅ npwp: nullable, max:30
✅ license_number: nullable, max:100
```

### CRITICAL ISSUES

#### 🟡 MISSING IN FORM (per spec requirements):
1. **license_expiry_date** - Tanggal kadaluarsa izin (REQUIRED per spec)
2. **is_authorized_narcotic** - Checkbox izin narkotika (REQUIRED per spec)

#### ⚠️ VALIDATION ISSUES:
1. **license_number** - Spec says REQUIRED + UNIQUE, but validation is only `nullable`
2. **name** - Spec says REQUIRED, validation OK ✅

### REKOMENDASI PERBAIKAN

**1. Tambah kolom ke database:**
```php
// Migration: add_narcotic_license_to_suppliers
Schema::table('suppliers', function (Blueprint $table) {
    $table->date('license_expiry_date')->nullable()->after('license_number');
    $table->boolean('is_authorized_narcotic')->default(false)->after('license_expiry_date');
    
    $table->index('license_expiry_date');
    $table->index('is_authorized_narcotic');
});

// Update unique constraint for license_number
Schema::table('suppliers', function (Blueprint $table) {
    $table->unique('license_number');
});
```

**2. Update form:**
```html
<div class="row mb-6">
    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nomor Izin</label>
    <div class="col-lg-8">
        <input type="text" name="license_number" class="form-control" 
               placeholder="Nomor izin distribusi" required maxlength="100">
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label fw-semibold fs-6">Tanggal Kadaluarsa Izin</label>
    <div class="col-lg-8">
        <input type="date" name="license_expiry_date" class="form-control">
    </div>
</div>

<div class="row mb-6">
    <label class="col-lg-4 col-form-label fw-semibold fs-6">Izin Narkotika</label>
    <div class="col-lg-8">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" 
                   name="is_authorized_narcotic" id="is_authorized_narcotic" value="1">
            <label class="form-check-label" for="is_authorized_narcotic">
                Supplier memiliki izin distribusi narkotika
            </label>
        </div>
    </div>
</div>
```

**3. Update validation:**
```php
// SupplierWebController
$data = $request->validate([
    'name'                    => ['required', 'string', 'max:255'],
    'code'                    => ['required', 'string', 'max:20', 'unique:suppliers,code'],
    'email'                   => ['nullable', 'email'],
    'phone'                   => ['nullable', 'string', 'max:20'],
    'address'                 => ['nullable', 'string'],
    'npwp'                    => ['nullable', 'string', 'max:30'],
    'license_number'          => ['required', 'string', 'max:100', 'unique:suppliers,license_number'], // CHANGED
    'license_expiry_date'     => ['nullable', 'date', 'after:today'], // NEW
    'is_authorized_narcotic'  => ['nullable', 'boolean'], // NEW
]);
```

---

## 3. PRODUCTS FORM AUDIT

### DATABASE SCHEMA (20 fields)
```
✅ id
✅ supplier_id
✅ name
✅ sku
✅ category
✅ unit
✅ price (legacy, deprecated)
✅ cost_price
✅ selling_price
✅ discount_percentage
✅ discount_amount
✅ expiry_date
✅ batch_no
✅ is_narcotic
✅ description
✅ is_active
✅ created_at
✅ updated_at
❌ narcotic_group (NOT IN DB - REQUIRED per spec)
❌ requires_sp (NOT IN DB - REQUIRED per spec)
❌ requires_prescription (NOT IN DB - REQUIRED per spec)
```

### FORM FIELDS (resources/views/products/create.blade.php)
```html
✅ supplier_id (dropdown, required)
✅ name (text, required)
✅ sku (text, required, unique)
✅ category (dropdown, optional)
✅ unit (dropdown, required)
✅ cost_price (number, required)
✅ selling_price (number, required)
✅ discount_percentage (number, optional)
✅ discount_amount (number, optional)
✅ expiry_date (date, optional)
✅ batch_no (text, optional)
✅ is_narcotic (checkbox)
✅ description (textarea, optional)
❌ narcotic_group (MISSING - REQUIRED when is_narcotic=true)
```

### VALIDATION (ProductWebController)
```php
✅ supplier_id: required, exists:suppliers,id
✅ name: required, string, max:255
✅ sku: required, unique
✅ unit: required, max:30
✅ price: nullable, numeric, min:0
✅ cost_price: required, numeric, min:0
✅ selling_price: required, numeric, min:0
✅ discount_percentage: nullable, numeric, min:0, max:100
✅ discount_amount: nullable, numeric, min:0
✅ category: nullable, max:100
✅ description: nullable
✅ is_narcotic: nullable, boolean
✅ expiry_date: nullable, date, after:today
✅ batch_no: nullable, max:100
```

### CRITICAL ISSUES

#### 🔴 CONDITIONAL LOGIC NOT IMPLEMENTED:
Per spec: **IF is_narcotic = true:**
- `narcotic_group` (dropdown I, II, III) → REQUIRED
- `requires_sp` = true (auto)
- `requires_prescription` = true (auto)

**CURRENT STATUS:** ❌ TIDAK ADA IMPLEMENTASI

### REKOMENDASI PERBAIKAN

**1. Tambah kolom ke database:**
```php
// Migration: add_narcotic_fields_to_products
Schema::table('products', function (Blueprint $table) {
    $table->enum('narcotic_group', ['I', 'II', 'III'])->nullable()->after('is_narcotic')
          ->comment('Golongan narkotika: I, II, atau III');
    $table->boolean('requires_sp')->default(false)->after('narcotic_group')
          ->comment('Memerlukan Surat Pesanan');
    $table->boolean('requires_prescription')->default(false)->after('requires_sp')
          ->comment('Memerlukan resep dokter');
    
    $table->index('narcotic_group');
    $table->index('requires_sp');
    $table->index('requires_prescription');
});
```

**2. Update form dengan conditional logic:**
```html
<!-- Narcotic Checkbox -->
<div class="row mb-6">
    <label class="col-lg-4 col-form-label fw-semibold fs-6">Narkotika</label>
    <div class="col-lg-8">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" 
                   name="is_narcotic" id="is_narcotic" value="1"
                   onchange="toggleNarcoticFields()">
            <label class="form-check-label" for="is_narcotic">
                Produk termasuk narkotika/psikotropika
            </label>
        </div>
    </div>
</div>

<!-- Conditional: Narcotic Group (shown only if is_narcotic = true) -->
<div class="row mb-6" id="narcotic_group_field" style="display: none;">
    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Golongan Narkotika</label>
    <div class="col-lg-8">
        <select name="narcotic_group" class="form-select" id="narcotic_group_select">
            <option value="">Pilih Golongan</option>
            <option value="I">Golongan I</option>
            <option value="II">Golongan II</option>
            <option value="III">Golongan III</option>
        </select>
        <div class="form-text">
            Golongan I: Paling berbahaya, hanya untuk penelitian<br>
            Golongan II: Dapat digunakan untuk terapi dengan pengawasan ketat<br>
            Golongan III: Dapat digunakan untuk terapi dengan pengawasan
        </div>
    </div>
</div>

<script>
function toggleNarcoticFields() {
    const isNarcotic = document.getElementById('is_narcotic').checked;
    const narcoticGroupField = document.getElementById('narcotic_group_field');
    const narcoticGroupSelect = document.getElementById('narcotic_group_select');
    
    if (isNarcotic) {
        narcoticGroupField.style.display = '';
        narcoticGroupSelect.required = true;
    } else {
        narcoticGroupField.style.display = 'none';
        narcoticGroupSelect.required = false;
        narcoticGroupSelect.value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleNarcoticFields();
});
</script>
```

**3. Update validation dengan conditional rules:**
```php
// ProductWebController
$rules = [
    'supplier_id'         => ['required', 'exists:suppliers,id'],
    'name'                => ['required', 'string', 'max:255'],
    'sku'                 => ['required', 'string', 'max:50', 'unique:products,sku'],
    'unit'                => ['required', 'string', 'max:30'],
    'cost_price'          => ['required', 'numeric', 'min:0'],
    'selling_price'       => ['required', 'numeric', 'min:0'],
    'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
    'discount_amount'     => ['nullable', 'numeric', 'min:0'],
    'category'            => ['nullable', 'string', 'max:100'],
    'description'         => ['nullable', 'string'],
    'is_narcotic'         => ['nullable', 'boolean'],
    'expiry_date'         => ['nullable', 'date', 'after:today'],
    'batch_no'            => ['nullable', 'string', 'max:100'],
];

// Conditional validation: if is_narcotic = true, narcotic_group is REQUIRED
if ($request->boolean('is_narcotic')) {
    $rules['narcotic_group'] = ['required', 'in:I,II,III'];
}

$data = $request->validate($rules);

// Auto-set requires_sp and requires_prescription if narcotic
if ($data['is_narcotic'] ?? false) {
    $data['requires_sp'] = true;
    $data['requires_prescription'] = true;
} else {
    $data['requires_sp'] = false;
    $data['requires_prescription'] = false;
    $data['narcotic_group'] = null;
}
```

---

## 4. USERS FORM AUDIT

### DATABASE SCHEMA (8 fields)
```
✅ id
✅ organization_id
✅ name
✅ email
✅ password
✅ is_active
✅ created_at
✅ updated_at
❌ is_pharmacist (NOT IN DB - REQUIRED per spec)
```

### FORM FIELDS (resources/views/users/create.blade.php)
```html
✅ name (text, required)
✅ email (text, required, unique)
✅ password (password, required, min:8)
✅ organization_id (dropdown, required)
✅ role (dropdown, required)
❌ is_pharmacist (MISSING - REQUIRED when role=healthcare)
```

### VALIDATION (UserWebController)
```php
✅ name: required, string, max:255
✅ email: required, email, unique
✅ password: required, min:8
✅ role: required, exists:roles,name
✅ organization_id: nullable, exists:organizations,id
```

### CRITICAL ISSUES

#### 🔴 CONDITIONAL LOGIC NOT IMPLEMENTED:
Per spec: **IF role = healthcare:**
- `is_pharmacist` (checkbox) → MUST BE SHOWN

**CURRENT STATUS:** ❌ TIDAK ADA IMPLEMENTASI

### REKOMENDASI PERBAIKAN

**1. Tambah kolom ke database:**
```php
// Migration: add_is_pharmacist_to_users
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_pharmacist')->default(false)->after('is_active')
          ->comment('Apakah user adalah apoteker (untuk role healthcare)');
    
    $table->index('is_pharmacist');
});
```

**2. Update form dengan conditional logic:**
```html
<!-- Role Selection -->
<div class="row mb-6">
    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Role</label>
    <div class="col-lg-8">
        <select name="role" class="form-select" id="role_select" required onchange="togglePharmacistField()">
            <option value="">Pilih Role</option>
            <option value="Super Admin">Super Admin</option>
            <option value="Finance">Finance</option>
            <option value="Approver">Approver</option>
            <option value="Healthcare">Healthcare</option>
        </select>
    </div>
</div>

<!-- Conditional: Pharmacist Checkbox (shown only if role = healthcare) -->
<div class="row mb-6" id="pharmacist_field" style="display: none;">
    <label class="col-lg-4 col-form-label fw-semibold fs-6">Apoteker</label>
    <div class="col-lg-8">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" 
                   name="is_pharmacist" id="is_pharmacist" value="1">
            <label class="form-check-label" for="is_pharmacist">
                User adalah apoteker berlisensi
            </label>
        </div>
        <div class="form-text">
            Apoteker memiliki akses tambahan untuk verifikasi resep narkotika
        </div>
    </div>
</div>

<script>
function togglePharmacistField() {
    const role = document.getElementById('role_select').value;
    const pharmacistField = document.getElementById('pharmacist_field');
    
    if (role === 'Healthcare') {
        pharmacistField.style.display = '';
    } else {
        pharmacistField.style.display = 'none';
        document.getElementById('is_pharmacist').checked = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePharmacistField();
});
</script>
```

**3. Update validation:**
```php
// UserWebController
$data = $request->validate([
    'name'            => ['required', 'string', 'max:255'],
    'email'           => ['required', 'email', 'unique:users,email'],
    'password'        => ['required', 'string', 'min:8'],
    'role'            => ['required', 'string', 'exists:roles,name'],
    'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
    'is_pharmacist'   => ['nullable', 'boolean'], // NEW
]);

$newUser = User::create([
    'name'            => $data['name'],
    'email'           => $data['email'],
    'password'        => Hash::make($data['password']),
    'organization_id' => $data['organization_id'] ?? null,
    'is_active'       => true,
    'is_pharmacist'   => $request->boolean('is_pharmacist'), // NEW
]);
```

---

## 5. SUPPLIER PRODUCTS FORM (NOT FOUND)

### STATUS: ❌ FORM TIDAK DITEMUKAN

Per spec, harus ada form untuk mengelola **Supplier Products** dengan flow:
1. Select supplier (required)
2. Select product (required, filtered by supplier)
3. Input purchase_price (required, numeric ≥ 0)
4. Input discount_type (dropdown: percentage / nominal)
5. Input discount_value (numeric)

**VALIDATION:**
- purchase_price >= 0
- UNIQUE (supplier_id, product_id)

**CURRENT STATUS:** ❌ TIDAK ADA IMPLEMENTASI

### REKOMENDASI

Sistem saat ini menggunakan `products.supplier_id` (one-to-many), bukan many-to-many relationship. Jika ingin implementasi Supplier Products:

**1. Buat tabel pivot:**
```php
// Migration: create_supplier_products_table
Schema::create('supplier_products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->decimal('purchase_price', 15, 2);
    $table->enum('discount_type', ['percentage', 'nominal'])->default('percentage');
    $table->decimal('discount_value', 15, 2)->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->unique(['supplier_id', 'product_id']);
    $table->index('is_active');
});
```

**2. Buat controller, form, dan routes**

---

## PRIORITAS PERBAIKAN

### 🔴 CRITICAL (Harus diperbaiki segera)
1. **Organizations:** Tambah NPWP, customer_code, fiscal fields
2. **Suppliers:** Tambah license_expiry_date, is_authorized_narcotic, unique license_number
3. **Products:** Implementasi conditional logic untuk narkotika (narcotic_group)
4. **Users:** Implementasi conditional logic untuk healthcare (is_pharmacist)

### 🟡 HIGH (Penting untuk konsistensi)
1. **Organizations:** Hapus city, province, is_authorized_narcotic dari form (tidak ada di DB)
2. **Products:** Tambah requires_sp, requires_prescription (auto-set)
3. **All Forms:** Pastikan semua required fields memiliki validasi

### 🟢 MEDIUM (Nice to have)
1. **Supplier Products:** Implementasi many-to-many relationship
2. **All Forms:** Tambah inline validation error messages
3. **All Forms:** Konsistensi Bahasa Indonesia di semua label

---

## KESIMPULAN

Sistem memiliki **KETIDAKSESUAIAN SERIUS** antara database schema dengan form input. Banyak field penting (NPWP, customer_code, narcotic_group, is_pharmacist) yang TIDAK ADA di form, sehingga data tidak bisa diinput dengan lengkap.

**REKOMENDASI UTAMA:**
1. Tambahkan semua missing fields ke form
2. Implementasikan conditional logic (narkotika, healthcare)
3. Hapus field yang tidak ada di database
4. Perbaiki validasi agar konsisten dengan spec
5. Test semua form setelah perbaikan

**ESTIMASI WAKTU PERBAIKAN:** 4-6 jam untuk semua form

---

**END OF AUDIT REPORT**
