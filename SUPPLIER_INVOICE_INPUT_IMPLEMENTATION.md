# IMPLEMENTASI INPUT INVOICE PEMASOK (DISTRIBUTOR)

**Tanggal**: 14 April 2026  
**Status**: ✅ SELESAI  
**Tipe**: Business Logic Correction

---

## 🎯 MASALAH YANG DIPERBAIKI

### Alur Bisnis SALAH (Sebelumnya):
```
❌ Admin Medikindo "BUAT" invoice ke supplier dari GR
❌ Sistem generate invoice number
❌ Harga otomatis dari PO
```

**Masalah**: Ini tidak sesuai dengan realita bisnis distributor medis!

### Alur Bisnis BENAR (Sekarang):
```
✅ Distributor kirim invoice (fisik/PDF) ke Medikindo
✅ Admin Medikindo INPUT data invoice distributor ke sistem
✅ Harga bisa beda dari harga jual Medikindo ke RS
✅ Nomor invoice dari distributor disimpan untuk audit
```

---

## 📋 ALUR BISNIS LENGKAP (CORRECTED)

```
1. RS/Klinik buat PO di sistem
   - Harga: HARGA JUAL MEDIKINDO ke RS
   ↓
2. Medikindo approve PO
   ↓
3. Medikindo order ke Distributor (di luar sistem)
   - Harga: HARGA BELI dari Distributor
   ↓
4. Distributor kirim barang LANGSUNG ke RS (di luar sistem)
   ↓
5. RS terima barang → INPUT GR di sistem
   - Batch & Expiry dicatat
   ↓
6. Distributor kirim INVOICE (fisik/PDF) ke Medikindo
   - Nomor: INV-DIST-2024-001 (contoh)
   - Tanggal: 14 April 2026
   - Harga: HARGA BELI dari Distributor
   ↓
7. Admin Medikindo INPUT invoice distributor ke sistem ← PERUBAHAN INI!
   - Input nomor invoice distributor
   - Input tanggal invoice distributor
   - Input harga distributor (bisa beda dari PO)
   - Link ke GR (untuk validasi batch/expiry)
   ↓
8. Medikindo BUAT invoice ke RS (AR)
   - Dari GR yang sama
   - Harga: HARGA JUAL MEDIKINDO (dari PO)
   - Margin = Harga Jual - Harga Beli
   ↓
9. RS bayar ke Medikindo
   ↓
10. Medikindo bayar Distributor
```

---

## 🔧 PERUBAHAN YANG DILAKUKAN

### 1. Database Schema

**Migration**: `2026_04_14_051715_add_distributor_invoice_fields_to_supplier_invoices_table.php`

**Field Baru di `supplier_invoices`**:
```php
$table->string('distributor_invoice_number')->nullable();
$table->date('distributor_invoice_date')->nullable();
$table->index('distributor_invoice_number');
```

**Tujuan**:
- Simpan nomor invoice asli dari distributor
- Simpan tanggal invoice distributor
- Index untuk pencarian cepat

### 2. Model Update

**File**: `app/Models/SupplierInvoice.php`

**Perubahan**:
```php
protected $casts = [
    // ... existing casts
    'distributor_invoice_date' => 'date',  // ← BARU
];
```

### 3. Form Input Invoice

**File**: `resources/views/invoices/create_supplier.blade.php`

**Perubahan Utama**:

#### A. Judul & Deskripsi
```
SEBELUM: "Buat Invoice Pemasok"
SEKARANG: "Input Invoice Pemasok"

SEBELUM: "Buat invoice berdasarkan penerimaan barang"
SEKARANG: "Input invoice yang diterima dari distributor"
```

#### B. Field Baru
```html
<!-- Nomor Invoice Distributor -->
<input type="text" name="distributor_invoice_number" required>

<!-- Tanggal Invoice Distributor -->
<input type="date" name="distributor_invoice_date" required>

<!-- Nomor Invoice Internal (Opsional) -->
<input type="text" name="internal_invoice_number">
```

#### C. Harga Editable
```html
SEBELUM: Harga read-only dari PO
SEKARANG: Harga editable (input manual)

<input type="number" name="items[${index}][unit_price]" 
       placeholder="Harga dari invoice distributor">
```

#### D. Alert & Petunjuk
```html
<div class="alert alert-warning">
    <strong>Penting:</strong> Pilih GR yang sesuai dengan invoice fisik 
    yang diterima dari distributor. Batch dan expiry harus match.
</div>

<div class="alert alert-info">
    <strong>Petunjuk:</strong> Input data sesuai dengan invoice 
    fisik/PDF yang diterima dari distributor.
</div>

<div class="alert alert-light-primary">
    <strong>Catatan Harga:</strong> Harga yang diinput adalah 
    HARGA BELI dari distributor. Harga jual ke RS sudah tercatat di PO.
</div>
```

### 4. Request Validation

**File**: `app/Http/Requests/StoreSupplierInvoiceRequest.php`

**Rules Baru**:
```php
'distributor_invoice_number'    => 'required|string|max:255',
'distributor_invoice_date'      => 'required|date',
'due_date'                      => 'required|date|after_or_equal:distributor_invoice_date',
'internal_invoice_number'       => 'nullable|string|max:255|unique:supplier_invoices,invoice_number',
'items.*.unit_price'            => 'required|numeric|min:0',  // ← Editable!
'items.*.discount_percent'      => 'nullable|numeric|min:0|max:100',
```

**Error Messages (Indonesian)**:
```php
'distributor_invoice_number.required' => 'Nomor invoice distributor wajib diisi.',
'distributor_invoice_date.required'   => 'Tanggal invoice distributor wajib diisi.',
'due_date.after_or_equal'             => 'Tanggal jatuh tempo harus sama atau setelah tanggal invoice.',
```

### 5. Service Layer

**File**: `app/Services/InvoiceFromGRService.php`

**Method**: `createSupplierInvoiceFromGR()`

**Perubahan**:
```php
SupplierInvoice::create([
    'invoice_number'             => $metadata['internal_invoice_number'] ?? $this->generateInvoiceNumber(),
    'distributor_invoice_number' => $metadata['distributor_invoice_number'] ?? null,  // ← BARU
    'distributor_invoice_date'   => $metadata['distributor_invoice_date'] ?? null,    // ← BARU
    'notes'                      => $metadata['notes'] ?? null,                        // ← BARU
    // ... rest of fields
]);
```

### 6. Controller

**File**: `app/Http/Controllers/Web/InvoiceWebController.php`

**Method**: `storeSupplier()`

**Perubahan**:
```php
$metadata = [
    'distributor_invoice_number' => $validated['distributor_invoice_number'],  // ← BARU
    'distributor_invoice_date'   => $validated['distributor_invoice_date'],    // ← BARU
    'internal_invoice_number'    => $validated['internal_invoice_number'] ?? null,
    'due_date'                   => $validated['due_date'],
    'notes'                      => $validated['notes'] ?? null,
];
```

**Success Message**:
```php
SEBELUM: "Invoice Pemasok {$invoice->invoice_number} berhasil dibuat."
SEKARANG: "Invoice Pemasok {$invoice->invoice_number} berhasil disimpan."
```

### 7. UI Labels

**File**: `resources/views/invoices/index.blade.php`

**Perubahan**:
```html
SEBELUM: "Buat Invoice Pemasok"
SEKARANG: "Input Invoice Pemasok"

SEBELUM: "Buat Invoice Pertama"
SEKARANG: "Input Invoice Pertama"
```

---

## 💰 PRICING LOGIC (CRITICAL)

### Dua Harga Berbeda:

#### 1. Harga Beli (dari Distributor) - SUPPLIER INVOICE (AP)
```
Disimpan di: supplier_invoices.line_items.unit_price
Sumber: Input manual dari invoice distributor
Digunakan untuk: Pembayaran ke distributor
```

#### 2. Harga Jual (ke RS/Klinik) - CUSTOMER INVOICE (AR)
```
Disimpan di: purchase_orders.items.unit_price
Sumber: PO yang dibuat RS
Digunakan untuk: Tagihan ke RS/Klinik
```

### Margin Calculation:
```
Margin per item = (Harga Jual - Harga Beli) × Quantity
Total Margin = Sum of all item margins
```

### Contoh:
```
Produk: Paracetamol 500mg
Quantity: 100 box

Harga Beli dari Distributor: Rp 50,000 / box
Harga Jual ke RS: Rp 65,000 / box

Margin per box: Rp 15,000
Total Margin: Rp 1,500,000

Invoice ke Distributor (AP): Rp 5,000,000
Invoice ke RS (AR): Rp 6,500,000
Profit: Rp 1,500,000
```

---

## ✅ VALIDASI YANG TETAP DITERAPKAN

### 1. GR Requirement
- ✅ Invoice HARUS link ke GR
- ✅ GR status harus 'completed'
- ✅ Database constraint: `goods_receipt_id NOT NULL`

### 2. Batch & Expiry Validation
- ✅ Batch & expiry otomatis dari GR (read-only)
- ✅ Tidak bisa diubah saat input invoice
- ✅ Untuk traceability dan audit

### 3. Quantity Validation
- ✅ Quantity tidak boleh melebihi remaining quantity di GR
- ✅ Validasi di service layer

### 4. Supplier Validation
- ✅ GR harus punya supplier
- ✅ Link ke PO yang valid

---

## 📊 DATA FLOW

### Input Invoice Distributor (AP):
```
Form Input
  ↓
Validation (StoreSupplierInvoiceRequest)
  ↓
Controller (storeSupplier)
  ↓
Service (InvoiceFromGRService::createSupplierInvoiceFromGR)
  ↓
  - Validate GR status
  - Validate quantities
  - Validate batch/expiry
  - Calculate totals (dengan harga distributor)
  - Create SupplierInvoice
  - Create SupplierInvoiceLineItems
  - Audit log
  ↓
Database
  ↓
Redirect ke show page
```

### Buat Invoice ke RS (AR):
```
Form Input
  ↓
Validation
  ↓
Controller (storeCustomer)
  ↓
Service (InvoiceFromGRService::createCustomerInvoiceFromGR)
  ↓
  - Validate GR status
  - Validate quantities
  - Validate batch/expiry
  - Calculate totals (dengan harga jual Medikindo)
  - Create CustomerInvoice
  - Create CustomerInvoiceLineItems
  - Audit log
  ↓
Database
  ↓
Redirect ke show page
```

---

## 🎨 USER EXPERIENCE

### Sebelum (Confusing):
```
1. Admin pilih GR
2. Sistem "buat" invoice
3. Harga otomatis dari PO
4. Tidak ada tempat input nomor invoice distributor
5. Tidak jelas ini invoice dari distributor atau ke distributor
```

### Sekarang (Clear):
```
1. Admin terima invoice fisik dari distributor
2. Admin buka form "Input Invoice Pemasok"
3. Admin pilih GR yang sesuai
4. Admin input:
   - Nomor invoice distributor (dari dokumen)
   - Tanggal invoice distributor
   - Harga per item (dari dokumen)
   - Diskon (jika ada)
5. Sistem validasi batch/expiry dengan GR
6. Sistem simpan invoice dengan referensi lengkap
7. Clear: Ini adalah INPUT invoice dari distributor
```

---

## 📝 AUDIT TRAIL

### Data yang Tercatat:
```
supplier_invoices:
  - invoice_number (internal Medikindo)
  - distributor_invoice_number (dari distributor) ← BARU
  - distributor_invoice_date (dari distributor) ← BARU
  - goods_receipt_id (link ke GR)
  - purchase_order_id (link ke PO)
  - supplier_id (distributor)
  - total_amount (berdasarkan harga distributor)
  - created_at, updated_at
  - issued_by (user yang input)

supplier_invoice_line_items:
  - goods_receipt_item_id (link ke GR item)
  - product_id
  - batch_no (dari GR)
  - expiry_date (dari GR)
  - quantity
  - unit_price (harga distributor) ← EDITABLE
  - discount_percentage
  - line_total
```

### Traceability:
```
Distributor Invoice
  ↓ (distributor_invoice_number)
Supplier Invoice (sistem)
  ↓ (goods_receipt_id)
Goods Receipt
  ↓ (purchase_order_id)
Purchase Order
  ↓ (organization_id)
RS/Klinik
```

---

## 🧪 TESTING CHECKLIST

### Functional Tests:
- [ ] Input invoice dengan nomor distributor valid
- [ ] Input invoice dengan tanggal distributor valid
- [ ] Input harga distributor berbeda dari PO
- [ ] Validasi batch/expiry match dengan GR
- [ ] Validasi quantity tidak melebihi GR
- [ ] Generate nomor invoice internal otomatis jika kosong
- [ ] Simpan nomor invoice internal manual jika diisi
- [ ] Validasi due date >= distributor invoice date
- [ ] Error handling untuk data invalid

### UI/UX Tests:
- [ ] Label "Input Invoice Pemasok" (bukan "Buat")
- [ ] Alert & petunjuk jelas
- [ ] Field harga editable
- [ ] Field batch/expiry read-only
- [ ] Success message setelah simpan
- [ ] Redirect ke show page

### Business Logic Tests:
- [ ] Harga distributor tersimpan dengan benar
- [ ] Margin calculation correct (jika ada report)
- [ ] Audit log tercatat
- [ ] Nomor invoice distributor tersimpan
- [ ] Tanggal invoice distributor tersimpan

---

## 📚 DOKUMENTASI TERKAIT

### Files Modified:
1. ✅ `database/migrations/2026_04_14_051715_add_distributor_invoice_fields_to_supplier_invoices_table.php`
2. ✅ `app/Models/SupplierInvoice.php`
3. ✅ `app/Http/Requests/StoreSupplierInvoiceRequest.php`
4. ✅ `app/Http/Controllers/Web/InvoiceWebController.php`
5. ✅ `app/Services/InvoiceFromGRService.php`
6. ✅ `resources/views/invoices/create_supplier.blade.php`
7. ✅ `resources/views/invoices/index.blade.php`

### Documentation Created:
- ✅ `SUPPLIER_INVOICE_INPUT_IMPLEMENTATION.md` (this file)

### Related Documentation:
- `BUSINESS_RULES_IMPLEMENTATION.md` - Perlu update
- `MENU_STRUCTURE_GUIDE.md` - Perlu update
- `RINGKASAN_PERBAIKAN_FINAL.md` - Perlu update

---

## ⚠️ BREAKING CHANGES

### API Changes:
```
SEBELUM:
POST /invoices/supplier
{
  "goods_receipt_id": 1,
  "supplier_invoice_number": "INV-001",  // ← Field ini dihapus
  "due_date": "2026-05-14"
}

SEKARANG:
POST /invoices/supplier
{
  "goods_receipt_id": 1,
  "distributor_invoice_number": "INV-DIST-001",  // ← BARU (required)
  "distributor_invoice_date": "2026-04-14",      // ← BARU (required)
  "internal_invoice_number": "INV-MED-001",      // ← BARU (optional)
  "due_date": "2026-05-14",
  "items": [
    {
      "goods_receipt_item_id": 1,
      "quantity": 100,
      "unit_price": 50000,        // ← Sekarang editable!
      "discount_percent": 5
    }
  ]
}
```

### Database Changes:
- ✅ Migration sudah dijalankan
- ✅ Field baru nullable (backward compatible)
- ✅ Existing data tidak terpengaruh

---

## 🚀 DEPLOYMENT NOTES

### Pre-Deployment:
- ✅ Migration file created
- ✅ All code changes committed
- ✅ Syntax validation passed
- ✅ No diagnostics errors

### Deployment Steps:
```bash
# 1. Pull latest code
git pull origin main

# 2. Run migration
php artisan migrate

# 3. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 4. Test in staging
# - Input invoice distributor
# - Verify data saved correctly
# - Check show page displays correctly
```

### Post-Deployment:
- [ ] Test input invoice distributor
- [ ] Verify nomor invoice distributor tersimpan
- [ ] Verify harga distributor editable
- [ ] Train users on new flow
- [ ] Update user documentation

---

## ✅ COMPLETION STATUS

**Status**: ✅ SELESAI  
**Date**: 14 April 2026  
**Migration**: ✅ Executed  
**Syntax Check**: ✅ Passed  
**Ready for Testing**: ✅ YES

### Summary:
Invoice Pemasok (Supplier/Distributor) sekarang adalah **INPUT** form, bukan "BUAT" form. Admin Medikindo input data dari invoice fisik yang diterima dari distributor, dengan:
- ✅ Nomor invoice distributor
- ✅ Tanggal invoice distributor
- ✅ Harga distributor (editable, bisa beda dari PO)
- ✅ Link ke GR untuk validasi batch/expiry
- ✅ Audit trail lengkap

**Alur bisnis sekarang sudah benar dan sesuai dengan realita bisnis distributor medis!**
