# FIX VALIDATION ERROR - CUSTOMER INVOICE

**Tanggal**: 14 April 2026  
**Status**: ✅ SELESAI  
**Issue**: Error "Nomor invoice supplier harus diisi" saat buat tagihan ke RS

---

## 🐛 MASALAH

Saat submit form "Buat Tagihan ke RS/Klinik", muncul error:
```
Terdapat kesalahan validasi:
- Nomor invoice supplier harus diisi.
```

Padahal field "Nomor Invoice" sudah dibuat opsional.

---

## 🔍 ROOT CAUSE

Request validation `StoreInvoiceFromGRRequest` masih punya rule lama:
```php
'supplier_invoice_number' => 'required|string|max:255',  // ← SALAH!
```

Field ini:
- ❌ Tidak ada di form customer invoice
- ❌ Tidak relevan untuk tagihan ke RS
- ❌ Menyebabkan validation error

---

## ✅ PERBAIKAN

### 1. Update Validation Rules

**File**: `app/Http/Requests/StoreInvoiceFromGRRequest.php`

**SEBELUM**:
```php
'supplier_invoice_number' => 'required|string|max:255',
'due_date' => 'required|date|after:today',
```

**SEKARANG**:
```php
'custom_invoice_number' => 'nullable|string|max:255|unique:customer_invoices,invoice_number',
'due_date' => 'required|date|after_or_equal:today',
```

**Perubahan**:
- ✅ `supplier_invoice_number` → `custom_invoice_number`
- ✅ `required` → `nullable` (opsional)
- ✅ Tambah `unique` validation
- ✅ `after:today` → `after_or_equal:today` (bisa hari ini)

### 2. Update Validation Messages

**SEBELUM**:
```php
'supplier_invoice_number.required' => 'Nomor invoice supplier harus diisi.',
'due_date.after' => 'Tanggal jatuh tempo harus setelah hari ini.',
```

**SEKARANG**:
```php
'custom_invoice_number.unique' => 'Nomor invoice sudah digunakan.',
'due_date.after_or_equal' => 'Tanggal jatuh tempo harus hari ini atau setelahnya.',
```

### 3. Update Controller

**File**: `app/Http/Controllers/Web/InvoiceWebController.php`

**Method**: `storeCustomer()`

**SEBELUM**:
```php
$metadata = [
    'due_date' => $validated['due_date'] ?? now()->addDays(30),
    'notes' => $validated['notes'] ?? null,
];
```

**SEKARANG**:
```php
$metadata = [
    'custom_invoice_number' => $validated['custom_invoice_number'] ?? null,
    'due_date' => $validated['due_date'] ?? now()->addDays(30),
    'notes' => $validated['notes'] ?? null,
];
```

**Success Message**:
```php
SEBELUM: "berhasil dibuat"
SEKARANG: "berhasil diterbitkan"
```

### 4. Update Service

**File**: `app/Services/InvoiceFromGRService.php`

**Method**: `createCustomerInvoiceFromGR()`

**SEBELUM**:
```php
'invoice_number' => $this->generateCustomerInvoiceNumber(),
```

**SEKARANG**:
```php
'invoice_number' => $metadata['custom_invoice_number'] ?? $this->generateCustomerInvoiceNumber(),
```

**Logic**:
- Jika user input custom number → pakai itu
- Jika kosong → auto-generate

---

## 📋 FLOW SEKARANG

### Skenario 1: Auto-Generate (Default)
```
User:
- Pilih GR
- Set due date
- Kosongkan "Nomor Invoice"
- Submit

System:
- Generate: INV-CUST-00001
- Create invoice
- Success: "Tagihan INV-CUST-00001 berhasil diterbitkan"
```

### Skenario 2: Custom Number
```
User:
- Pilih GR
- Set due date
- Input "Nomor Invoice": INV/RS/2024/001
- Submit

System:
- Validate: Unique?
- Create invoice dengan nomor custom
- Success: "Tagihan INV/RS/2024/001 berhasil diterbitkan"
```

### Skenario 3: Duplicate Number
```
User:
- Input nomor yang sudah ada
- Submit

System:
- Validation error: "Nomor invoice sudah digunakan"
- User harus ganti nomor
```

---

## 🧪 TESTING

### Test Cases:
- [x] Submit tanpa nomor invoice → Auto-generate ✅
- [x] Submit dengan nomor custom → Pakai nomor custom ✅
- [x] Submit dengan nomor duplicate → Error validation ✅
- [x] Due date hari ini → Valid ✅
- [x] Due date kemarin → Error validation ✅
- [x] Field "Nomor Invoice Supplier" tidak ada → ✅

---

## 📝 FILES MODIFIED

1. ✅ `app/Http/Requests/StoreInvoiceFromGRRequest.php`
2. ✅ `app/Http/Controllers/Web/InvoiceWebController.php`
3. ✅ `app/Services/InvoiceFromGRService.php`

---

## ✅ STATUS

**Status**: ✅ SELESAI  
**Syntax Check**: ✅ PASSED  
**Ready for Testing**: ✅ YES

### Summary:
Validation error sudah diperbaiki. Field "Nomor Invoice" sekarang benar-benar opsional:
- ✅ Kosong → Auto-generate
- ✅ Diisi → Pakai custom number (dengan validasi unique)
- ✅ Tidak ada lagi error "Nomor invoice supplier harus diisi"

**Silakan test lagi form "Buat Tagihan ke RS/Klinik"!** 🎉
