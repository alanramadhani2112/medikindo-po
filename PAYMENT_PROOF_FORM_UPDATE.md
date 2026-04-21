# 📋 UPDATE FORM SUBMIT BUKTI PEMBAYARAN

**Tanggal:** 21 April 2026  
**Status:** ✅ COMPLETED  
**Tipe:** Feature Enhancement

---

## 🎯 TUJUAN UPDATE

Memperbaiki dan menyempurnakan form submit bukti pembayaran dari RS/Klinik dengan:
1. ✅ Menambahkan pilihan jenis bank dari database
2. ✅ Menambahkan input nomor rekening pengirim
3. ✅ Menyeragamkan metode pembayaran di seluruh sistem
4. ✅ Menambahkan metode pembayaran Cash
5. ✅ Conditional fields berdasarkan metode pembayaran

---

## 📝 REQUIREMENT LENGKAP

### **Form Flow:**
```
1. Pilih Invoice Pelanggan (dropdown)
   ↓
2. Pilih Jenis Pembayaran:
   - Bayar Penuh (full)
   - Bayar Sebagian (partial) → input nominal
   ↓
3. Input Tanggal Pembayaran
   ↓
4. Pilih Metode Pembayaran:
   - Bank Transfer
   - Virtual Account
   - Giro/Cek
   - Cash
   ↓
5. [CONDITIONAL] Jika metode = Bank Transfer/VA/Giro/Cek:
   - Bank Tujuan Transfer (Medikindo) → auto dari invoice atau pilih
   - Jenis Bank Pengirim (RS/Klinik) → dropdown bank
   - Nomor Rekening Pengirim → input text (opsional)
   ↓
6. Input No. Referensi/Bukti Transfer
   ↓
7. Input Catatan (opsional)
   ↓
8. Upload Bukti Pembayaran (wajib)
   ↓
9. Submit → Status: SUBMITTED
```

---

## 🔧 PERUBAHAN YANG DILAKUKAN

### **1. Database Migration**

#### **File:** `database/migrations/2026_04_21_120055_add_sender_account_number_to_payment_proofs_table.php`

```php
Schema::table('payment_proofs', function (Blueprint $table) {
    $table->string('sender_account_number', 50)->nullable()->after('sender_bank_name');
});
```

**Status:** ✅ MIGRATED

---

### **2. Model Update**

#### **File:** `app/Models/PaymentProof.php`

**Tambah fillable:**
```php
protected $fillable = [
    // ... existing fields
    'sender_account_number',  // NEW
];
```

---

### **3. Request Validation Update**

#### **File:** `app/Http/Requests/StorePaymentProofRequest.php`

**Update rules:**
```php
public function rules(): array
{
    return [
        'customer_invoice_id'    => 'required|exists:customer_invoices,id',
        'payment_type'           => 'required|in:full,partial',
        'amount'                 => 'required|numeric|min:0.01',
        'payment_date'           => 'required|date|before_or_equal:today',
        'payment_method'         => 'required|string|in:Bank Transfer,Virtual Account,Giro/Cek,Cash',
        'bank_account_id'        => 'nullable|exists:bank_accounts,id',
        'sender_bank_name'       => 'nullable|string|max:100',
        'sender_account_number'  => 'nullable|string|max:50',  // NEW
        'bank_reference'         => 'nullable|string|max:100',
        'notes'                  => 'nullable|string|max:500',
        'file'                   => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ];
}
```

**Tambah conditional validation:**
```php
public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        // ... existing validations
        
        // NEW: Validate bank fields required for bank methods
        $bankMethods = ['Bank Transfer', 'Virtual Account', 'Giro/Cek'];
        if (in_array($paymentMethod, $bankMethods)) {
            if (!$this->input('bank_account_id')) {
                $validator->errors()->add('bank_account_id', 
                    'Bank tujuan transfer wajib dipilih untuk metode pembayaran ' . $paymentMethod . '.');
            }
            if (!$this->input('sender_bank_name')) {
                $validator->errors()->add('sender_bank_name', 
                    'Jenis bank pengirim wajib dipilih untuk metode pembayaran ' . $paymentMethod . '.');
            }
        }
    });
}
```

---

### **4. Service Update**

#### **File:** `app/Services/PaymentProofService.php`

**Update submitPaymentProof:**
```php
$proof = PaymentProof::create([
    'customer_invoice_id'    => $invoice->id,
    'submitted_by'           => $actor->id,
    'amount'                 => $amount,
    'payment_type'           => $paymentType,
    'payment_date'           => $data['payment_date'],
    'payment_method'         => $data['payment_method'] ?? 'Bank Transfer',
    'bank_account_id'        => $data['bank_account_id'] ?? null,
    'sender_bank_name'       => $data['sender_bank_name'] ?? null,
    'sender_account_number'  => $data['sender_account_number'] ?? null,  // NEW
    'bank_reference'         => $data['bank_reference'] ?? null,
    'notes'                  => $data['notes'] ?? null,
    'status'                 => PaymentProofStatus::SUBMITTED,
]);
```

---

### **5. View Update**

#### **File:** `resources/views/payment-proofs/create.blade.php`

**A. Alpine.js Data Update:**
```javascript
x-data="{
    // ... existing data
    senderBankName: '',      // NEW
    accountNumber: '',       // NEW
    
    get showBankFields() {
        return ['Bank Transfer', 'Virtual Account', 'Giro/Cek'].includes(this.paymentMethod);
    },
}"
```

**B. Metode Pembayaran (Standardized):**
```html
<select name="payment_method" x-model="paymentMethod" required>
    <option value="">— Pilih Metode —</option>
    <option value="Bank Transfer">🏦 Bank Transfer</option>
    <option value="Virtual Account">💳 Virtual Account</option>
    <option value="Giro/Cek">📄 Giro/Cek</option>
    <option value="Cash">💵 Cash (Tunai)</option>
</select>
```

**C. Bank Tujuan Transfer (Improved):**
```html
<label class="form-label fw-bold" :class="showBankFields ? 'required' : ''">
    Bank Tujuan Transfer (Medikindo)
</label>
<select name="bank_account_id" :required="showBankFields">
    <option value="">— Pilih Rekening Medikindo —</option>
    @foreach(\App\Models\BankAccount::active()->forReceive()->orderBy('default_priority','desc')->get() as $bank)
        <option value="{{ $bank->id }}" {{ $bank->default_for_receive ? 'selected' : '' }}>
            {{ $bank->bank_name }} — {{ $bank->account_number }}
            @if($bank->default_for_receive) ⭐ Default @endif
        </option>
    @endforeach
</select>
```

**D. Jenis Bank Pengirim (NEW - Dropdown):**
```html
<label class="form-label fw-bold" :class="showBankFields ? 'required' : ''">
    Jenis Bank Pengirim
</label>
<select name="sender_bank_name" x-model="senderBankName" :required="showBankFields">
    <option value="">— Pilih Bank —</option>
    <option value="BCA">BCA</option>
    <option value="Mandiri">Mandiri</option>
    <option value="BNI">BNI</option>
    <option value="BRI">BRI</option>
    <option value="CIMB Niaga">CIMB Niaga</option>
    <option value="Permata">Permata</option>
    <option value="Danamon">Danamon</option>
    <option value="BTN">BTN</option>
    <option value="BSI">BSI (Bank Syariah Indonesia)</option>
    <option value="Muamalat">Muamalat</option>
    <option value="OCBC NISP">OCBC NISP</option>
    <option value="Lainnya">Lainnya</option>
</select>
```

**E. Nomor Rekening Pengirim (NEW):**
```html
<label class="form-label fw-bold">
    Nomor Rekening Pengirim
</label>
<input type="text" name="sender_account_number" 
       x-model="accountNumber"
       placeholder="Contoh: 1234567890">
<div class="form-text text-muted">Nomor rekening RS/Klinik (opsional)</div>
```

**F. Upload Bukti (Dynamic Label):**
```html
<label class="required form-label fw-bold">
    <span x-show="showBankFields">Upload Bukti Transfer</span>
    <span x-show="!showBankFields">Upload Bukti Pembayaran</span>
</label>
<input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
<div class="text-muted fs-7 mt-2">
    <span x-show="showBankFields">Upload screenshot/foto bukti transfer bank.</span>
    <span x-show="!showBankFields">Upload foto kwitansi atau bukti pembayaran.</span>
</div>
```

---

### **6. Standardisasi Metode Pembayaran**

**Files Updated:**
- ✅ `resources/views/payment-proofs/create.blade.php`
- ✅ `resources/views/payments/create_incoming.blade.php`
- ✅ `resources/views/payments/create_outgoing.blade.php`

**Metode Pembayaran Standar:**
```
1. Bank Transfer
2. Virtual Account
3. Giro/Cek
4. Cash
```

**Removed:**
- ❌ QRIS (digabung ke Virtual Account)
- ❌ Giro & Cek terpisah (digabung jadi Giro/Cek)

---

## 📊 STATUS PAYMENT PROOF

### **Lifecycle Status:**

```
┌─────────────────────────────────────────────────────────┐
│  1. SUBMITTED (Menunggu Review)                         │
│     - Status awal setelah RS/Klinik submit              │
│     - Badge: Primary (biru)                             │
│     - Bisa di-recall oleh submitter                     │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│  2. VERIFIED (Terverifikasi)                            │
│     - Finance sudah cek dokumen                         │
│     - Badge: Info (biru muda)                           │
│     - Menunggu approval final                           │
└─────────────────────────────────────────────────────────┘
                        ↓
        ┌───────────────┴───────────────┐
        ↓                               ↓
┌──────────────────┐          ┌──────────────────┐
│  3a. APPROVED    │          │  3b. REJECTED    │
│  (Disetujui)     │          │  (Ditolak)       │
│  Badge: Success  │          │  Badge: Danger   │
│  Invoice PAID    │          │  Submit ulang    │
└──────────────────┘          └──────────────────┘
```

### **Status Setelah Submit:**
**Jawaban:** Status payment proof setelah submit adalah **SUBMITTED** (Menunggu Review)

**Detail:**
- Status invoice tetap: **ISSUED** atau **PARTIAL_PAID**
- Status payment proof: **SUBMITTED**
- Invoice baru berubah status setelah payment proof **APPROVED**

---

## 🎨 UI/UX IMPROVEMENTS

### **1. Conditional Fields**
- Field bank hanya muncul jika metode = Bank Transfer/VA/Giro/Cek
- Field bank REQUIRED jika metode memerlukan bank
- Field bank HIDDEN jika metode = Cash

### **2. Bank Selection**
- Dropdown bank pengirim (bukan free text)
- 11 bank populer + "Lainnya"
- Mudah untuk reporting dan analytics

### **3. Dynamic Labels**
- "Upload Bukti Transfer" untuk metode bank
- "Upload Bukti Pembayaran" untuk metode cash
- "No. Referensi Transfer" vs "No. Referensi Pembayaran"

### **4. Auto-fill Bank Tujuan**
- Jika invoice sudah punya bank account → auto-fill & locked
- Jika belum → dropdown dengan default bank marked ⭐

---

## 🧪 TESTING CHECKLIST

### **Test Case 1: Submit dengan Bank Transfer**
```
✅ Pilih invoice
✅ Pilih "Bayar Penuh"
✅ Pilih metode "Bank Transfer"
✅ Field bank muncul (required)
✅ Pilih bank tujuan Medikindo
✅ Pilih jenis bank pengirim (BCA)
✅ Input nomor rekening pengirim (1234567890)
✅ Input no. referensi
✅ Upload bukti transfer
✅ Submit → Status: SUBMITTED
✅ Data tersimpan lengkap di database
```

### **Test Case 2: Submit dengan Cash**
```
✅ Pilih invoice
✅ Pilih "Bayar Sebagian" → input nominal
✅ Pilih metode "Cash"
✅ Field bank TIDAK muncul
✅ Input no. referensi (nomor kwitansi)
✅ Upload foto kwitansi
✅ Submit → Status: SUBMITTED
✅ bank_account_id = NULL
✅ sender_bank_name = NULL
```

### **Test Case 3: Validation - Bank Required**
```
✅ Pilih metode "Bank Transfer"
✅ JANGAN pilih bank tujuan
✅ JANGAN pilih bank pengirim
✅ Submit
✅ Error: "Bank tujuan transfer wajib dipilih"
✅ Error: "Jenis bank pengirim wajib dipilih"
```

### **Test Case 4: Partial Payment**
```
✅ Pilih invoice dengan outstanding Rp 10.000.000
✅ Pilih "Bayar Sebagian"
✅ Input nominal Rp 5.000.000
✅ Submit
✅ Status: SUBMITTED
✅ Setelah approved:
   - Invoice status: PARTIAL_PAID
   - Paid amount: Rp 5.000.000
   - Outstanding: Rp 5.000.000
```

---

## 📁 FILES MODIFIED

### **Database:**
- ✅ `database/migrations/2026_04_21_120055_add_sender_account_number_to_payment_proofs_table.php` (NEW)

### **Models:**
- ✅ `app/Models/PaymentProof.php` (updated fillable)

### **Requests:**
- ✅ `app/Http/Requests/StorePaymentProofRequest.php` (updated rules + conditional validation)

### **Services:**
- ✅ `app/Services/PaymentProofService.php` (updated create payload)

### **Views:**
- ✅ `resources/views/payment-proofs/create.blade.php` (major update)
- ✅ `resources/views/payments/create_incoming.blade.php` (standardized methods)
- ✅ `resources/views/payments/create_outgoing.blade.php` (standardized methods)

**Total Files:** 7 files

---

## 🚀 DEPLOYMENT STEPS

### **1. Pull Latest Code**
```bash
git pull origin main
```

### **2. Run Migration**
```bash
php artisan migrate
```

### **3. Clear Cache**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### **4. Test Form**
- Buka: `/payment-proofs/create`
- Test semua metode pembayaran
- Verify conditional fields
- Test validation

---

## 📝 CATATAN PENTING

### **1. Metode Pembayaran Standar**
Gunakan EXACT string ini di seluruh sistem:
- `Bank Transfer`
- `Virtual Account`
- `Giro/Cek`
- `Cash`

### **2. Bank Account Selection**
- Gunakan scope `forReceive()` untuk bank penerima
- Gunakan scope `forSend()` untuk bank pengirim
- Default bank ditandai dengan `default_for_receive = true`

### **3. Conditional Validation**
- Bank fields REQUIRED jika metode = Bank Transfer/VA/Giro/Cek
- Bank fields OPTIONAL jika metode = Cash
- Validation di backend dan frontend (Alpine.js)

### **4. Status Flow**
```
SUBMITTED → VERIFIED → APPROVED → Invoice PAID
         ↘ REJECTED (submit ulang)
         ↘ RECALLED (tarik kembali)
```

---

## ✅ KESIMPULAN

Form submit bukti pembayaran sudah diperbaiki dengan:
1. ✅ Dropdown jenis bank pengirim (11 bank populer)
2. ✅ Input nomor rekening pengirim (opsional)
3. ✅ Metode pembayaran standar (4 metode)
4. ✅ Conditional fields berdasarkan metode
5. ✅ Validation lengkap (frontend + backend)
6. ✅ Dynamic labels untuk UX lebih baik
7. ✅ Auto-fill bank tujuan dari invoice

**Status setelah submit:** `SUBMITTED` (Menunggu Review)

**Next Step:** Finance Medikindo akan verify dan approve payment proof.

---

**UPDATE COMPLETED! 🎉**
