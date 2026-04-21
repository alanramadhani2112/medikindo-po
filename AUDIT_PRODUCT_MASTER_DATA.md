# AUDIT PRODUCT MASTER DATA SYSTEM
**Tanggal Audit:** 21 April 2026  
**Status:** STEP 1 - AUDIT SISTEM (READ-ONLY) ✅ SELESAI

---

## 📋 EXECUTIVE SUMMARY

Sistem Product Master Data saat ini **TIDAK COMPLIANT** dengan standar regulasi healthcare.

**TEMUAN KRITIS:**
- ❌ Tidak ada klasifikasi product_type (ALKES/ALKES_DIV/PKRT)
- ❌ Tidak ada risk_class (A-D / 1-3)
- ❌ Tidak ada intended_use dan usage_method
- ❌ Unit handling masih string sederhana (tidak ada conversion system)
- ⚠️ Potensi duplikasi produk karena unit berbeda (contoh: "Masker Box" vs "Masker Pcs")

**LEVEL RISIKO:** 🔴 **HIGH**
- Data tidak siap untuk compliance audit
- Tidak ada traceability untuk medical device classification
- Sistem unit tidak mendukung multi-unit conversion

---

## 1️⃣ DATABASE SCHEMA ANALYSIS

### 1.1 Tabel `products` - Struktur Lengkap

**Base Migration:** `2026_04_09_100002_create_products_table.php`

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    supplier_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    category VARCHAR(100) NULL,
    unit VARCHAR(30) NOT NULL,                    -- ⚠️ STRING SEDERHANA
    price DECIMAL(15,2) DEFAULT 0,
    is_narcotic BOOLEAN DEFAULT FALSE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    -- Indexes
    INDEX idx_supplier_id (supplier_id),
    INDEX idx_is_narcotic (is_narcotic),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT
);
```

**Additional Migrations:**

1. **`2026_04_13_000001_add_profit_fields_to_products_table.php`**
   ```sql
   ALTER TABLE products ADD COLUMN (
       cost_price DECIMAL(15,2) DEFAULT 0,
       selling_price DECIMAL(15,2) DEFAULT 0,
       discount_percentage DECIMAL(5,2) DEFAULT 0,
       discount_amount DECIMAL(15,2) DEFAULT 0,
       INDEX idx_cost_price (cost_price),
       INDEX idx_selling_price (selling_price)
   );
   ```

2. **`2026_04_15_102136_add_expiry_fields_to_products_table.php`**
   ```sql
   ALTER TABLE products ADD COLUMN (
       expiry_date DATE NULL,
       batch_no VARCHAR(100) NULL
   );
   ```

3. **`2026_04_21_000002_add_narcotic_fields_to_products.php`**
   ```sql
   ALTER TABLE products ADD COLUMN (
       narcotic_group ENUM('I','II','III') NULL,
       requires_sp BOOLEAN DEFAULT FALSE,
       requires_prescription BOOLEAN DEFAULT FALSE,
       INDEX idx_narcotic_group (narcotic_group),
       INDEX idx_requires_sp (requires_sp),
       INDEX idx_requires_prescription (requires_prescription)
   );
   ```

**TOTAL KOLOM SAAT INI:** 20 kolom

---

### 1.2 Tabel `inventory_items` - Struktur

**Migration:** `2026_04_15_091756_create_inventory_tables.php`

```sql
CREATE TABLE inventory_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    batch_no VARCHAR(100) NOT NULL,
    expiry_date DATE NULL,
    quantity_on_hand INT DEFAULT 0,
    quantity_reserved INT DEFAULT 0,
    unit_cost DECIMAL(15,2) NOT NULL,
    location VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_inventory (organization_id, product_id, batch_no),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_product_id (product_id),
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

**⚠️ MASALAH:** Tidak ada kolom `unit` di inventory_items
- Quantity disimpan sebagai integer tanpa informasi unit
- Asumsi: menggunakan unit dari tabel products
- **RISIKO:** Jika produk punya multi-unit, tidak ada cara track unit mana yang digunakan

---

### 1.3 Tabel `purchase_order_items` - Struktur

**Model:** `app/Models/PurchaseOrderItem.php`

```php
protected $fillable = [
    'purchase_order_id',
    'product_id',
    'quantity',        // INTEGER - tidak ada unit info
    'unit_price',
    'subtotal',
    'notes',
];
```

**⚠️ MASALAH:** Sama seperti inventory - tidak ada kolom unit
- Quantity disimpan sebagai integer
- Asumsi: menggunakan unit dari products.unit

---

## 2️⃣ MODEL & BUSINESS LOGIC ANALYSIS

### 2.1 Model `Product.php`

**Constants:**
```php
public const CATEGORIES = [
    'Obat Umum',
    'Obat Keras',
    'Narkotika',
    'Psikotropika',
    'Alat Kesehatan',    // ⚠️ Tidak ada sub-klasifikasi
    'BMHP'
];

public const UNITS = [
    'Box',
    'Botol',
    'Tablet',
    'Strip',
    'Ampul',
    'Vial',
    'PCS'
];
```

**Fillable Fields (20 fields):**
```php
protected $fillable = [
    'supplier_id',
    'name',
    'sku',
    'category',
    'unit',                      // STRING - tidak ada relasi
    'price',
    'cost_price',
    'selling_price',
    'discount_percentage',
    'discount_amount',
    'is_narcotic',
    'narcotic_group',
    'requires_sp',
    'requires_prescription',
    'description',
    'is_active',
    'expiry_date',
    'batch_no',
];
```

**Business Logic:**
- ✅ Profit calculation (gross profit, net profit, margins)
- ✅ Expiry date tracking (isExpired, isExpiringSoon)
- ✅ Narcotic handling (is_narcotic, narcotic_group)
- ❌ **TIDAK ADA** unit conversion logic
- ❌ **TIDAK ADA** product type classification
- ❌ **TIDAK ADA** risk class validation

**Relationships:**
```php
belongsTo: Supplier
hasMany: PurchaseOrderItem
```

---

### 2.2 Controller `ProductWebController.php`

**Validation Rules (store/update):**
```php
$rules = [
    'supplier_id'         => ['required', 'exists:suppliers,id'],
    'name'                => ['required', 'string', 'max:255'],
    'sku'                 => ['required', 'string', 'max:50', 'unique:products,sku'],
    'unit'                => ['required', 'string', 'max:30'],  // ⚠️ STRING BEBAS
    'cost_price'          => ['required', 'numeric', 'min:0'],
    'selling_price'       => ['required', 'numeric', 'min:0'],
    'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
    'category'            => ['nullable', 'string', 'max:100'],
    'is_narcotic'         => ['nullable', 'boolean'],
    'expiry_date'         => ['nullable', 'date', 'after:today'],
    'batch_no'            => ['nullable', 'string', 'max:100'],
];

// Conditional validation
if ($request->boolean('is_narcotic')) {
    $rules['narcotic_group'] = ['required', 'in:I,II,III'];
}
```

**⚠️ MASALAH:**
- Unit validation hanya max:30 characters - tidak ada enum/foreign key
- Tidak ada validasi untuk product_type
- Tidak ada validasi untuk risk_class
- Tidak ada validasi untuk intended_use / usage_method

**Auto-set Logic:**
```php
if ($data['is_narcotic']) {
    $data['requires_sp'] = true;
    $data['requires_prescription'] = true;
} else {
    $data['requires_sp'] = false;
    $data['requires_prescription'] = false;
    $data['narcotic_group'] = null;
}
```

---

## 3️⃣ FORM UI ANALYSIS

### 3.1 Create Form (`resources/views/products/create.blade.php`)

**Unit Input:**
```html
<select name="unit" required class="form-select">
    <option value="">— Pilih Satuan —</option>
    @foreach($units as $u)
        <option value="{{ $u }}">{{ $u }}</option>
    @endforeach
</select>
```

**⚠️ MASALAH:**
- Dropdown sederhana dari constant array
- Tidak ada input untuk base unit vs packaging unit
- Tidak ada input untuk conversion ratio
- User tidak bisa define custom unit

**Fields yang ADA:**
- ✅ Supplier
- ✅ Name, SKU
- ✅ Unit (dropdown)
- ✅ Cost Price, Selling Price
- ✅ Discount Percentage/Amount
- ✅ Category (dropdown)
- ✅ Expiry Date, Batch No
- ✅ Description
- ✅ is_narcotic (checkbox)
- ✅ narcotic_group (conditional)

**Fields yang TIDAK ADA:**
- ❌ product_type (ALKES/ALKES_DIV/PKRT)
- ❌ risk_class (A-D / 1-3)
- ❌ intended_use
- ❌ usage_method
- ❌ Multi-unit configuration
- ❌ Unit conversion setup

---

## 4️⃣ UNIT HANDLING ANALYSIS

### 4.1 Current Implementation

**Storage:**
```
products.unit = VARCHAR(30)
```

**Examples dari Seeder:**
```
'Strip (10 tablet)'
'Box (50 pcs)'
'Box (100 pcs)'
'Botol (30 tablet)'
'Ampul'
'Vial'
'Pcs'
'Set'
'Roll'
```

**⚠️ MASALAH KRITIS:**

1. **Tidak Ada Normalisasi**
   - "Box (50 pcs)" vs "Box (100 pcs)" = 2 unit berbeda
   - Tidak bisa konversi otomatis

2. **Tidak Ada Base Unit**
   - Sistem tidak tahu unit terkecil apa
   - Tidak bisa breakdown "1 Box" = "50 Pcs"

3. **Potensi Duplikasi Produk**
   - Contoh: "Masker Medis 3 Ply" bisa ada 2x:
     - SKU: MED-MASK-3P-BOX (unit: Box 50 pcs)
     - SKU: MED-MASK-3P-PCS (unit: Pcs)
   - Seharusnya 1 produk dengan 2 unit

4. **Inventory Tracking Tidak Akurat**
   - Jika beli dalam Box, jual dalam Pcs → tidak bisa track
   - Harus manual input conversion

5. **Reporting Sulit**
   - Tidak bisa aggregate "total Pcs" jika ada yang dalam Box
   - Tidak bisa compare harga per base unit

---

### 4.2 Inventory Impact

**InventoryItem Model:**
```php
protected $fillable = [
    'organization_id',
    'product_id',
    'batch_no',
    'expiry_date',
    'quantity_on_hand',      // INTEGER - unit dari products.unit
    'quantity_reserved',
    'unit_cost',
    'location',
];
```

**Contoh Skenario Bermasalah:**

```
Produk: Masker Medis 3 Ply
Unit di products: "Box (50 pcs)"

Inventory:
- quantity_on_hand = 10  (maksudnya 10 Box = 500 Pcs)

Jika customer mau beli 100 Pcs:
- Sistem tidak bisa auto-convert
- Harus manual hitung: 100 Pcs = 2 Box
- Risiko: salah hitung, stok tidak akurat
```

---

## 5️⃣ PURCHASE ORDER IMPACT

**PurchaseOrderItem:**
```php
protected $fillable = [
    'purchase_order_id',
    'product_id',
    'quantity',        // INTEGER
    'unit_price',
    'subtotal',
    'notes',
];
```

**Contoh Skenario:**

```
PO Item:
- Product: Masker Medis (unit: Box 50 pcs)
- Quantity: 10
- Unit Price: Rp 50,000

Masalah:
- Apakah quantity=10 itu 10 Box atau 10 Pcs?
- Asumsi: mengikuti products.unit (10 Box)
- Tapi tidak ada validasi/enforcement
```

---

## 6️⃣ DATA SEEDER ANALYSIS

**ProductSeeder.php** - Total 28 produk template

**Contoh Unit yang Digunakan:**
```php
'Strip (10 tablet)'      // 5 produk
'Strip (10 kapsul)'      // 2 produk
'Box (100 pcs)'          // 3 produk (Sarung Tangan, Spuit)
'Box (50 pcs)'           // 1 produk (Masker)
'Botol (30 tablet)'      // 3 produk (Vitamin)
'Ampul'                  // 3 produk
'Vial'                   // 2 produk
'Botol'                  // 6 produk (Infus, Antiseptik)
'Pcs'                    // 1 produk (Kateter)
'Set'                    // 1 produk (Infus Set)
'Roll'                   // 2 produk (Perban, Plester)
'Pack (10 pcs)'          // 1 produk (Kasa)
```

**⚠️ POTENSI DUPLIKASI:**

Tidak ditemukan duplikasi eksplisit dalam seeder, TAPI:
- Jika user manual input "Masker Medis" dengan unit "Pcs" → duplikasi
- Tidak ada constraint untuk prevent ini

---

## 7️⃣ GAP ANALYSIS - STANDAR vs REALITA

### 7.1 Field yang WAJIB Ada (Compliance)

| Field | Status | Keterangan |
|-------|--------|------------|
| `product_type` | ❌ TIDAK ADA | Wajib: ALKES / ALKES_DIV / PKRT |
| `risk_class` | ❌ TIDAK ADA | Wajib: A-D (ALKES) / 1-3 (PKRT) |
| `intended_use` | ❌ TIDAK ADA | Wajib: Tujuan penggunaan produk |
| `usage_method` | ❌ TIDAK ADA | Wajib: Cara penggunaan |
| `registration_number` | ❌ TIDAK ADA | Opsional tapi penting (No. Izin Edar) |
| `manufacturer` | ❌ TIDAK ADA | Opsional (Produsen) |

### 7.2 Unit System yang WAJIB Ada

| Komponen | Status | Keterangan |
|----------|--------|------------|
| Tabel `units` | ❌ TIDAK ADA | Master unit (Pcs, Box, Strip, dll) |
| Tabel `product_units` | ❌ TIDAK ADA | Pivot: produk bisa punya multi-unit |
| Field `base_unit_id` | ❌ TIDAK ADA | Setiap produk harus punya base unit |
| Field `conversion_to_base` | ❌ TIDAK ADA | Ratio konversi ke base unit |
| Logic conversion | ❌ TIDAK ADA | Auto-convert antar unit |

---

## 8️⃣ RISK ASSESSMENT

### 8.1 Data Integrity Risk: 🔴 HIGH

**Risiko:**
1. **Duplikasi Produk Tidak Terdeteksi**
   - Produk sama dengan unit berbeda = 2 SKU berbeda
   - Tidak ada constraint untuk prevent ini
   - Impact: Laporan tidak akurat, stok terpecah

2. **Inventory Tracking Tidak Akurat**
   - Conversion manual = prone to human error
   - Tidak ada audit trail untuk unit conversion
   - Impact: Stok fisik vs sistem bisa beda

3. **Pricing Inconsistency**
   - Harga per Box vs per Pcs tidak ter-enforce
   - Bisa jadi harga per Pcs lebih murah dari per Box
   - Impact: Revenue loss

### 8.2 Compliance Risk: 🔴 HIGH

**Risiko:**
1. **Tidak Ada Product Classification**
   - Tidak bisa identify ALKES vs PKRT
   - Tidak bisa enforce approval workflow berdasarkan risk class
   - Impact: Gagal audit BPOM/Kemenkes

2. **Tidak Ada Traceability**
   - Tidak ada registration_number
   - Tidak ada manufacturer info
   - Impact: Tidak bisa trace jika ada product recall

3. **Narcotic Handling Incomplete**
   - Sudah ada is_narcotic + narcotic_group ✅
   - Tapi tidak ada link ke approval workflow
   - Impact: Compliance gap untuk controlled substances

### 8.3 Operational Risk: 🟡 MEDIUM

**Risiko:**
1. **Manual Conversion = Slow Process**
   - Staff harus manual hitung conversion
   - Prone to error saat busy
   - Impact: Operational inefficiency

2. **Reporting Complexity**
   - Tidak bisa auto-aggregate multi-unit
   - Report harus manual consolidate
   - Impact: Decision making lambat

---

## 9️⃣ BACKWARD COMPATIBILITY ANALYSIS

### 9.1 Data yang Sudah Ada

**Asumsi:** Sistem sudah production dengan data:
- ✅ Products dengan unit string (contoh: "Box (50 pcs)")
- ✅ Inventory items dengan quantity integer
- ✅ Purchase orders dengan quantity integer
- ✅ Invoices yang reference products

### 9.2 Strategi Migrasi (SAFE MODE)

**ATURAN KETAT:**
1. ❌ **JANGAN** hapus kolom `products.unit` (VARCHAR)
2. ❌ **JANGAN** ubah tipe data existing columns
3. ✅ **TAMBAHKAN** kolom baru dengan `nullable`
4. ✅ **BUAT** tabel baru (units, product_units)
5. ✅ **MIGRASI** data bertahap dengan script

**Fase Migrasi:**
```
FASE 1: Add new structure (nullable)
  ├─ Create table: units
  ├─ Create table: product_units
  ├─ Add column: products.product_type (nullable)
  ├─ Add column: products.risk_class (nullable)
  ├─ Add column: products.intended_use (nullable)
  ├─ Add column: products.usage_method (nullable)
  └─ Add column: products.base_unit_id (nullable FK to units)

FASE 2: Data normalization
  ├─ Parse existing products.unit string
  ├─ Create units records
  ├─ Create product_units records
  ├─ Set base_unit_id
  └─ Validate data integrity

FASE 3: Enforcement (setelah data clean)
  ├─ Make product_type NOT NULL
  ├─ Make risk_class NOT NULL
  ├─ Make base_unit_id NOT NULL
  └─ Update validation rules

FASE 4: Deprecation (optional, jauh ke depan)
  └─ Mark products.unit as deprecated (tapi JANGAN hapus)
```

---

## 🔟 RECOMMENDATIONS

### 10.1 Immediate Actions (CRITICAL)

1. **Freeze Product Creation**
   - Sementara stop user create produk baru
   - Sampai struktur baru ready
   - Prevent data inconsistency

2. **Data Audit**
   - Export semua products saat ini
   - Identify duplikasi manual
   - Prepare data cleaning script

### 10.2 Short-term Actions (1-2 Minggu)

1. **Implement New Structure**
   - Create migrations (STEP 3)
   - Create models & relationships
   - Update forms & validation

2. **Data Migration**
   - Parse existing unit strings
   - Normalize ke tabel units
   - Create product_units records

3. **Testing**
   - Test backward compatibility
   - Test conversion logic
   - Test reporting

### 10.3 Long-term Actions (1-3 Bulan)

1. **Compliance Enhancement**
   - Add registration_number field
   - Add manufacturer field
   - Link to approval workflow

2. **User Training**
   - Train staff on new unit system
   - Document conversion rules
   - Create SOP

3. **Monitoring**
   - Monitor data quality
   - Track conversion errors
   - Continuous improvement

---

## ✅ AUDIT CONCLUSION

**STATUS SISTEM:** 🔴 **NOT COMPLIANT - REQUIRES IMMEDIATE ACTION**

**CRITICAL GAPS:**
1. ❌ No product type classification (ALKES/PKRT)
2. ❌ No risk class system
3. ❌ No unit conversion system
4. ❌ High risk of data duplication

**NEXT STEP:** Proceed to **STEP 2 - GAP ANALYSIS** untuk detail comparison

**ESTIMATED EFFORT:**
- Development: 3-5 hari
- Data Migration: 1-2 hari
- Testing: 2-3 hari
- **TOTAL: 6-10 hari kerja**

**RISK LEVEL:** 🔴 HIGH - Tapi MANAGEABLE dengan controlled refactor

---

**Prepared by:** Kiro AI System Architect  
**Date:** 21 April 2026  
**Document Version:** 1.0
