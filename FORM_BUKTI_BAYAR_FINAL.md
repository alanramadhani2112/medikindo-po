# 📋 FORM SUBMIT BUKTI PEMBAYARAN - FINAL VERSION

**Tanggal:** 21 April 2026  
**Status:** ✅ COMPLETED  
**Versi:** 2.0 (Simplified & Compliant)

---

## 🎯 REQUIREMENT FINAL

### **Urutan Form:**
1. ✅ **Pilih Invoice** yang belum lunas
2. ✅ **Pilih Tanggal Pembayaran**
3. ✅ **Pilih Metode Pembayaran:**
   - Bank Transfer
   - Virtual Account
   - Giro/Cek
   - Cash
4. ✅ **Detail Pembayaran** (conditional berdasarkan metode)
5. ✅ **No. Referensi** / Bukti Transfer
6. ✅ **Catatan** (opsional)
7. ✅ **Upload Bukti** (WAJIB untuk semua metode)

---

## 📝 DETAIL PER METODE PEMBAYARAN

### **1. Bank Transfer / Virtual Account**

**Fields yang Muncul:**
- ✅ **Nama Bank** (dropdown dari database bank Indonesia)
  - Contoh: Bank BRI (002), Bank Mandiri (008), Bank BCA (014)
  - Total: 100+ bank Indonesia dengan kode bank
  - Required

**Regulasi:**
- Bukti transfer wajib (screenshot/foto slip transfer)
- No. referensi dari bank (opsional)

---

### **2. Giro/Cek**

**Fields yang Muncul:**
- ✅ **Nomor Giro/Cek** (required)
  - Input text untuk nomor seri giro/cek
- ✅ **Tanggal Jatuh Tempo** (required)
  - Date picker, harus tanggal di masa depan
- ✅ **Nama Bank Penerbit** (required)
  - Dropdown dari database bank Indonesia

**Regulasi Indonesia:**
- Giro/cek harus memiliki nomor seri unik
- Tanggal jatuh tempo wajib (tanggal giro dapat dicairkan)
- Bank penerbit harus jelas
- Foto giro/cek harus jelas dan terbaca

---

### **3. Cash (Tunai)**

**Fields yang Muncul:**
- ✅ **No. Kwitansi** (opsional)
  - Input text untuk nomor kwitansi

**Regulasi Indonesia:**
- Pembayaran tunai harus ada kwitansi
- Kwitansi harus bermaterai jika nominal > Rp 5.000.000
- Upload foto kwitansi wajib

---

## 🏦 BANK TUJUAN TRANSFER

### **TIDAK ADA PILIHAN BANK TUJUAN**

**Alasan:**
- ✅ Bank tujuan sudah **auto-assigned** dari invoice
- ✅ Bank tujuan sudah **tertera di PDF invoice**
- ✅ Tidak perlu user pilih lagi (mengurangi error)

**Implementasi:**
```php
'bank_account_id' => $invoice->bank_account_id ?? null, // Auto from invoice
```

**Display:**
- Jika invoice punya bank account → tampil di sidebar (info only)
- Tidak ada dropdown/pilihan

---

## 🎨 UI/UX IMPROVEMENTS

### **Yang DIHAPUS:**
- ❌ Card "Panduan Pembayaran" dengan shadow & border
- ❌ Dropdown "Bank Tujuan Transfer" (sudah auto)
- ❌ Field "Nomor Rekening Pengirim" (tidak perlu)

### **Yang DIPERTAHANKAN:**
- ✅ Card "Rekening Tujuan Transfer" (info only, jika ada)
- ✅ Card "Ringkasan Pembayaran" (live summary)
- ✅ Conditional fields berdasarkan metode

### **Styling:**
- Clean & minimal
- No excessive shadows
- No unnecessary borders
- Focus on functionality

---

## 📊 DATABASE CHANGES

### **1. Migration: Add Giro Fields**

**File:** `database/migrations/2026_04_21_121136_add_giro_fields_to_payment_proofs_table.php`

```php
Schema::table('payment_proofs', function (Blueprint $table) {
    $table->string('giro_number', 100)->nullable()->after('sender_account_number');
    $table->date('giro_due_date')->nullable()->after('giro_number');
});
```

**Status:** ✅ MIGRATED

---

### **2. Model Update**

**File:** `app/Models/PaymentProof.php`

**Tambah fillable:**
```php
protected $fillable = [
    // ... existing
    'giro_number',
    'giro_due_date',
];

protected $casts = [
    // ... existing
    'giro_due_date' => 'date',
];
```

---

### **3. Request Validation**

**File:** `app/Http/Requests/StorePaymentProofRequest.php`

**Rules:**
```php
'payment_method'  => 'required|string|in:Bank Transfer,Virtual Account,Giro/Cek,Cash',
'sender_bank_name' => 'nullable|string|max:200', // Increased for long bank names
'giro_number'     => 'nullable|string|max:100',
'giro_due_date'   => 'nullable|date|after:today',
```

**Conditional Validation:**
```php
// Bank name required for bank methods
if (in_array($paymentMethod, ['Bank Transfer', 'Virtual Account', 'Giro/Cek'])) {
    if (!$this->input('sender_bank_name')) {
        $validator->errors()->add('sender_bank_name', 'Nama bank wajib dipilih...');
    }
}

// Giro fields required for Giro/Cek
if ($paymentMethod === 'Giro/Cek') {
    if (!$this->input('giro_number')) {
        $validator->errors()->add('giro_number', 'Nomor giro/cek wajib diisi...');
    }
    if (!$this->input('giro_due_date')) {
        $validator->errors()->add('giro_due_date', 'Tanggal jatuh tempo wajib diisi...');
    }
}
```

---

### **4. Service Update**

**File:** `app/Services/PaymentProofService.php`

```php
$proof = PaymentProof::create([
    // ... existing fields
    'bank_account_id'        => $invoice->bank_account_id ?? null, // AUTO from invoice
    'sender_bank_name'       => $data['sender_bank_name'] ?? null,
    'giro_number'            => $data['giro_number'] ?? null,
    'giro_due_date'          => $data['giro_due_date'] ?? null,
    'bank_reference'         => $data['bank_reference'] ?? null,
    'notes'                  => $data['notes'] ?? null,
    'status'                 => PaymentProofStatus::SUBMITTED,
]);
```

---

## 🏦 INDONESIAN BANK DATABASE

### **Source:**
`database/seeders/IndonesianBankSeeder.php`

### **Total Banks:** 100+ bank

### **Categories:**
1. **Bank BUMN** (4 bank)
   - BRI (002), Mandiri (008), BNI (009), BTN (200)

2. **Bank Swasta Nasional** (30+ bank)
   - BCA (014), CIMB Niaga (022), Danamon (011), Permata (013), dll

3. **Bank Pembangunan Daerah** (20+ bank)
   - BJB (110), DKI (111), Jateng (113), Jatim (114), dll

4. **Bank Syariah** (10+ bank)
   - BSI (451), Muamalat (147), BCA Syariah (536), dll

5. **Bank Digital** (6 bank)
   - Allo Bank (567), Amar Bank (531), BNC (490), dll

6. **Bank Asing** (10+ bank)
   - HSBC (087), DBS (046), CTBC (949), dll

### **Format Dropdown:**
```
Bank BRI (Bank Rakyat Indonesia) (002)
Bank Mandiri (008)
Bank BNI (Bank Negara Indonesia) (009)
Bank BCA (Bank Central Asia) (014)
...
```

---

## 🧪 TESTING SCENARIOS

### **Test 1: Bank Transfer**
```
1. Pilih invoice: INV-001 (Rp 10.000.000)
2. Pilih: Bayar Penuh
3. Tanggal: 2026-04-21
4. Metode: Bank Transfer
5. Nama Bank: Bank BCA (Bank Central Asia) (014)
6. No. Referensi: TRX-12345678
7. Upload: bukti_transfer.jpg
8. Submit

Expected:
✅ Status: SUBMITTED
✅ sender_bank_name: "Bank BCA (Bank Central Asia)"
✅ bank_account_id: (auto from invoice)
✅ giro_number: NULL
✅ giro_due_date: NULL
```

---

### **Test 2: Giro/Cek**
```
1. Pilih invoice: INV-002 (Rp 5.000.000)
2. Pilih: Bayar Sebagian → Rp 2.000.000
3. Tanggal: 2026-04-21
4. Metode: Giro/Cek
5. Nomor Giro: GR-98765432
6. Tanggal Jatuh Tempo: 2026-05-21 (30 hari dari sekarang)
7. Bank Penerbit: Bank Mandiri (008)
8. No. Referensi: (kosong)
9. Upload: foto_giro.jpg
10. Submit

Expected:
✅ Status: SUBMITTED
✅ payment_type: partial
✅ amount: 2000000
✅ sender_bank_name: "Bank Mandiri"
✅ giro_number: "GR-98765432"
✅ giro_due_date: "2026-05-21"
✅ Invoice status tetap: ISSUED
✅ Setelah approved → PARTIAL_PAID
```

---

### **Test 3: Cash**
```
1. Pilih invoice: INV-003 (Rp 3.000.000)
2. Pilih: Bayar Penuh
3. Tanggal: 2026-04-21
4. Metode: Cash
5. No. Kwitansi: KWT-001/2026
6. Catatan: "Pembayaran tunai di kantor"
7. Upload: kwitansi.jpg
8. Submit

Expected:
✅ Status: SUBMITTED
✅ payment_method: "Cash"
✅ sender_bank_name: NULL
✅ giro_number: NULL
✅ bank_reference: "KWT-001/2026"
✅ File uploaded: kwitansi.jpg
```

---

### **Test 4: Validation - Giro Incomplete**
```
1. Pilih metode: Giro/Cek
2. Isi nomor giro: GR-123
3. JANGAN isi tanggal jatuh tempo
4. JANGAN pilih bank
5. Submit

Expected:
❌ Error: "Tanggal jatuh tempo giro/cek wajib diisi."
❌ Error: "Nama bank wajib dipilih untuk metode pembayaran Giro/Cek."
```

---

## 📋 FIELD MAPPING

### **Form Fields → Database Columns**

| Form Field | Database Column | Type | Required | Notes |
|------------|----------------|------|----------|-------|
| Invoice | customer_invoice_id | FK | Yes | Dropdown |
| Jenis Pembayaran | payment_type | enum | Yes | full/partial |
| Nominal | amount | decimal | Yes | Auto for full |
| Tanggal | payment_date | date | Yes | <= today |
| Metode | payment_method | string | Yes | 4 options |
| Nama Bank | sender_bank_name | string | Conditional | Required for bank methods |
| No. Giro | giro_number | string | Conditional | Required for Giro/Cek |
| Tgl Jatuh Tempo | giro_due_date | date | Conditional | Required for Giro/Cek, > today |
| No. Referensi | bank_reference | string | No | Optional |
| Catatan | notes | text | No | Optional |
| Upload | file → PaymentDocument | file | Yes | JPG/PNG/PDF, max 5MB |

---

## 🔄 STATUS FLOW

```
┌─────────────────────────────────────────────────────────┐
│  USER SUBMIT FORM                                       │
│  Status: SUBMITTED (Menunggu Review)                    │
│  Badge: Primary (biru)                                  │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│  FINANCE VERIFY                                         │
│  Status: VERIFIED (Terverifikasi)                       │
│  Badge: Info (biru muda)                                │
└─────────────────────────────────────────────────────────┘
                        ↓
        ┌───────────────┴───────────────┐
        ↓                               ↓
┌──────────────────┐          ┌──────────────────┐
│  APPROVED        │          │  REJECTED        │
│  Badge: Success  │          │  Badge: Danger   │
│  Invoice → PAID  │          │  Submit Ulang    │
└──────────────────┘          └──────────────────┘
```

---

## 📁 FILES MODIFIED

### **Database:**
1. ✅ `database/migrations/2026_04_21_120055_add_sender_account_number_to_payment_proofs_table.php`
2. ✅ `database/migrations/2026_04_21_121136_add_giro_fields_to_payment_proofs_table.php`

### **Models:**
3. ✅ `app/Models/PaymentProof.php`

### **Requests:**
4. ✅ `app/Http/Requests/StorePaymentProofRequest.php`

### **Services:**
5. ✅ `app/Services/PaymentProofService.php`

### **Views:**
6. ✅ `resources/views/payment-proofs/create.blade.php` (COMPLETE REBUILD)

### **Seeders (Reference):**
7. ✅ `database/seeders/IndonesianBankSeeder.php` (existing, used for dropdown)

**Total:** 7 files

---

## ✅ CHECKLIST FINAL

### **Form Structure:**
- [x] Pilih invoice yang belum lunas
- [x] Pilih tanggal pembayaran
- [x] Pilih metode pembayaran (4 opsi)
- [x] Conditional fields per metode
- [x] No. referensi/bukti
- [x] Catatan (opsional)
- [x] Upload bukti (wajib)

### **Bank Transfer / VA:**
- [x] Dropdown bank Indonesia (100+ bank)
- [x] Format: Nama Bank (Kode)
- [x] Required jika pilih metode ini

### **Giro/Cek:**
- [x] Input nomor giro/cek (required)
- [x] Tanggal jatuh tempo (required, > today)
- [x] Dropdown bank penerbit (required)
- [x] Sesuai regulasi Indonesia

### **Cash:**
- [x] No. kwitansi (opsional)
- [x] Upload kwitansi (wajib)
- [x] Sesuai regulasi Indonesia

### **Bank Tujuan:**
- [x] TIDAK ADA dropdown (auto from invoice)
- [x] Tampil di sidebar (info only)
- [x] Sudah tertera di PDF invoice

### **UI/UX:**
- [x] Hapus card "Panduan Pembayaran"
- [x] Clean design, no excessive shadow
- [x] Conditional fields dengan Alpine.js
- [x] Live summary di sidebar

### **Validation:**
- [x] Backend validation lengkap
- [x] Conditional validation per metode
- [x] Error messages dalam Bahasa Indonesia

### **Database:**
- [x] Migration giro fields
- [x] Model updated
- [x] Service updated
- [x] No diagnostics errors

---

## 🚀 DEPLOYMENT

### **1. Pull & Migrate**
```bash
git pull origin main
php artisan migrate
```

### **2. Clear Cache**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### **3. Test Form**
```
URL: /payment-proofs/create
Test semua 4 metode pembayaran
Verify conditional fields
Test validation
```

---

## 📝 CATATAN PENTING

### **1. Bank Tujuan Transfer**
- **TIDAK PERLU** user pilih
- Sudah **auto-assigned** dari invoice
- Sudah **tertera di PDF** invoice
- Mengurangi **human error**

### **2. Dropdown Bank Indonesia**
- Menggunakan data dari `IndonesianBankSeeder::$BANKS`
- Total 100+ bank dengan kode resmi
- Format: `Bank BCA (Bank Central Asia) (014)`
- Sesuai standar ATM Bersama/Prima

### **3. Regulasi Giro/Cek**
- Nomor giro wajib (unique identifier)
- Tanggal jatuh tempo wajib (kapan bisa dicairkan)
- Bank penerbit wajib (bank yang menerbitkan giro)
- Foto giro harus jelas

### **4. Regulasi Cash**
- Kwitansi wajib untuk pembayaran tunai
- Materai wajib jika > Rp 5.000.000
- Upload foto kwitansi wajib

### **5. Status Setelah Submit**
**SUBMITTED** (Menunggu Review)
- Invoice tetap ISSUED/PARTIAL_PAID
- Baru berubah setelah APPROVED

---

## ✅ KESIMPULAN

Form submit bukti pembayaran sudah **FINAL** dengan:

1. ✅ **Urutan form** sesuai requirement
2. ✅ **Dropdown bank** dari database Indonesia (100+ bank)
3. ✅ **Metode pembayaran** sesuai regulasi Indonesia
4. ✅ **Conditional fields** per metode (Bank/VA, Giro/Cek, Cash)
5. ✅ **Bank tujuan** auto dari invoice (tidak perlu pilih)
6. ✅ **UI clean** tanpa card berlebihan
7. ✅ **Validation lengkap** frontend & backend
8. ✅ **Upload wajib** untuk semua metode

**Status setelah submit:** `SUBMITTED` (Menunggu Review)

---

**FORM COMPLETED! 🎉**
