# Rencana Pengembangan: Manual Payment Entry (Redesign)

## 📋 ANALISIS MASALAH SAAT INI

### 1. **Masalah Teknis**
- ❌ Conditional fields menggunakan `:style` dengan `:disabled` → Field tetap di DOM tapi disabled
- ❌ Browser tidak submit field yang disabled → Data hilang saat submit
- ❌ Field `sender_bank_name` duplikat (Bank Transfer & Giro/Cek)
- ❌ Field `reference` dan `giro_reference` terpisah → Membingungkan
- ❌ Autofill amount tidak reliable (Alpine.js `selectInvoice()`)

### 2. **Masalah UX/UI**
- ❌ Form terlalu panjang dengan banyak conditional fields
- ❌ User harus scroll untuk melihat semua field
- ❌ Tidak ada visual feedback saat pilih metode pembayaran
- ❌ Tidak ada validasi real-time untuk amount vs outstanding
- ❌ Upload file di tengah form (seharusnya di akhir)

### 3. **Masalah Business Logic**
- ❌ Tidak ada pembedaan jelas antara "Payment Proof" (dari RS) vs "Manual Entry" (dari Medikindo)
- ❌ Duplikasi logic dengan Payment Proof form
- ❌ Tidak ada audit trail untuk manual entry
- ❌ Tidak ada approval workflow untuk manual entry

### 4. **Masalah Data Structure**
- ❌ Payment model tidak punya field `source` (proof vs manual)
- ❌ Tidak ada field `entered_by` untuk tracking siapa yang input manual
- ❌ Field `reference` dan `giro_reference` tidak konsisten

---

## 🎯 TUJUAN REDESIGN

1. **Simplifikasi Form** - Buat form lebih sederhana dan mudah digunakan
2. **Fix Technical Issues** - Pastikan semua field ter-submit dengan benar
3. **Improve UX** - Buat flow yang lebih intuitif dan user-friendly
4. **Add Audit Trail** - Track semua manual entry untuk compliance
5. **Consistent Data Model** - Standardisasi field names dan structure

---

## 🏗️ RENCANA PENGEMBANGAN

### **FASE 1: Database Schema Update**

#### 1.1 Update `payments` table
```php
// Migration: add_source_and_audit_fields_to_payments_table.php
Schema::table('payments', function (Blueprint $table) {
    // Source tracking
    $table->enum('source', ['payment_proof', 'manual_entry'])->default('manual_entry')->after('status');
    $table->foreignId('entered_by_user_id')->nullable()->after('source')->constrained('users')->nullOnDelete();
    
    // Consolidate reference fields
    $table->dropColumn('giro_reference'); // Hapus field duplikat
    // Keep only 'reference' field for all payment methods
    
    // Add approval workflow for manual entry
    $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('status');
    $table->foreignId('approved_by_user_id')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
    $table->text('approval_notes')->nullable()->after('approved_at');
});
```

#### 1.2 Update Payment model
```php
// app/Models/Payment.php
protected $fillable = [
    // ... existing fields
    'source',
    'entered_by_user_id',
    'approval_status',
    'approved_by_user_id',
    'approved_at',
    'approval_notes',
];

protected $casts = [
    // ... existing casts
    'approved_at' => 'datetime',
];

// Relationships
public function enteredBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'entered_by_user_id');
}

public function approvedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'approved_by_user_id');
}

// Scopes
public function scopeManualEntry($query)
{
    return $query->where('source', 'manual_entry');
}

public function scopePendingApproval($query)
{
    return $query->where('approval_status', 'pending');
}
```

---

### **FASE 2: Form Redesign (Frontend)**

#### 2.1 Struktur Form Baru
```
┌─────────────────────────────────────────────────────────────┐
│ 1. Invoice Selection (Dropdown)                             │
│    → Auto-populate: Outstanding amount, Organization        │
├─────────────────────────────────────────────────────────────┤
│ 2. Payment Details (Always Visible)                         │
│    - Amount (Autofill, editable)                            │
│    - Payment Date                                            │
│    - Payment Method (Dropdown)                               │
├─────────────────────────────────────────────────────────────┤
│ 3. Method-Specific Fields (Dynamic)                         │
│    → Bank Transfer/VA:                                       │
│      - Bank Pengirim (Dropdown)                             │
│      - No. Rekening Pengirim                                │
│      - No. Referensi Transfer                               │
│    → Giro/Cek:                                              │
│      - No. Giro/Cek                                         │
│      - Tanggal Jatuh Tempo                                  │
│      - Bank Penerbit (Dropdown)                             │
│      - No. Referensi                                        │
│    → Cash:                                                   │
│      - No. Kwitansi                                         │
├─────────────────────────────────────────────────────────────┤
│ 4. Bank Penerima (Medikindo) - WAJIB                       │
│    → Auto-select default bank                                │
├─────────────────────────────────────────────────────────────┤
│ 5. Upload Bukti Pembayaran - WAJIB                         │
│    → Dynamic label based on payment method                   │
├─────────────────────────────────────────────────────────────┤
│ 6. Catatan (Optional)                                       │
├─────────────────────────────────────────────────────────────┤
│ [Batal] [Simpan & Kirim untuk Approval]                    │
└─────────────────────────────────────────────────────────────┘
```

#### 2.2 Solusi Conditional Fields
**GUNAKAN: Hidden inputs + JavaScript show/hide**

```blade
{{-- Bank Transfer / VA Fields --}}
<div id="bankTransferFields" style="display: none;">
    <input type="hidden" name="payment_method_type" value="bank_transfer">
    <div class="mb-8">
        <label class="form-label fw-bold required">Bank Pengirim</label>
        <select name="sender_bank_name" class="form-select" required>
            {{-- Options --}}
        </select>
    </div>
    {{-- Other fields --}}
</div>

{{-- Giro/Cek Fields --}}
<div id="giroFields" style="display: none;">
    <input type="hidden" name="payment_method_type" value="giro">
    {{-- Fields --}}
</div>

{{-- Cash Fields --}}
<div id="cashFields" style="display: none;">
    <input type="hidden" name="payment_method_type" value="cash">
    {{-- Fields --}}
</div>

<script>
// Pure JavaScript (no Alpine.js for conditional fields)
document.getElementById('paymentMethodSelect').addEventListener('change', function(e) {
    // Hide all
    document.getElementById('bankTransferFields').style.display = 'none';
    document.getElementById('giroFields').style.display = 'none';
    document.getElementById('cashFields').style.display = 'none';
    
    // Show selected
    const method = e.target.value;
    if (['Bank Transfer', 'Virtual Account'].includes(method)) {
        document.getElementById('bankTransferFields').style.display = 'block';
        // Enable fields
        document.querySelectorAll('#bankTransferFields input, #bankTransferFields select').forEach(el => {
            el.disabled = false;
        });
    } else if (method === 'Giro/Cek') {
        document.getElementById('giroFields').style.display = 'block';
        document.querySelectorAll('#giroFields input, #giroFields select').forEach(el => {
            el.disabled = false;
        });
    } else if (method === 'Cash') {
        document.getElementById('cashFields').style.display = 'block';
        document.querySelectorAll('#cashFields input').forEach(el => {
            el.disabled = false;
        });
    }
});
</script>
```

#### 2.3 Autofill Amount (Reliable)
```javascript
// Vanilla JS - More reliable than Alpine.js
document.getElementById('invoiceSelect').addEventListener('change', function(e) {
    const selectedOption = e.target.options[e.target.selectedIndex];
    const outstanding = selectedOption.dataset.outstanding;
    
    if (outstanding) {
        document.getElementById('amountInput').value = outstanding;
        // Show info
        document.getElementById('outstandingInfo').textContent = 
            'Sisa tagihan: Rp ' + parseInt(outstanding).toLocaleString('id-ID');
    }
});
```

---

### **FASE 3: Backend Update**

#### 3.1 Update Request Validation
```php
// app/Http/Requests/StoreManualPaymentRequest.php (Rename from StoreIncomingPaymentRequest)
public function rules()
{
    $rules = [
        'customer_invoice_id'   => 'required|exists:customer_invoices,id',
        'amount'                => 'required|numeric|min:0.01',
        'payment_date'          => 'required|date|before_or_equal:today',
        'payment_method'        => 'required|string|in:Bank Transfer,Virtual Account,Giro/Cek,Cash',
        'bank_account_id'       => 'required|exists:bank_accounts,id',
        'reference'             => 'nullable|string|max:100', // Single reference field for all methods
        'notes'                 => 'nullable|string|max:1000',
        'payment_proof_file'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        
        // Conditional fields
        'sender_bank_name'      => 'nullable|string|max:100',
        'sender_account_number' => 'nullable|string|max:50',
        'giro_number'           => 'nullable|string|max:50',
        'giro_due_date'         => 'nullable|date',
        'issuing_bank'          => 'nullable|string|max:100',
        'receipt_number'        => 'nullable|string|max:50',
    ];

    // Conditional validation based on payment method
    if (in_array($this->payment_method, ['Bank Transfer', 'Virtual Account'])) {
        $rules['sender_bank_name'] = 'required|string|max:100';
        $rules['sender_account_number'] = 'required|string|max:50';
        $rules['reference'] = 'required|string|max:100';
    }

    if ($this->payment_method === 'Giro/Cek') {
        $rules['giro_number'] = 'required|string|max:50';
        $rules['giro_due_date'] = 'required|date|after:today';
        $rules['issuing_bank'] = 'required|string|max:100';
        $rules['reference'] = 'required|string|max:100';
    }

    if ($this->payment_method === 'Cash') {
        $rules['receipt_number'] = 'required|string|max:50';
    }

    return $rules;
}
```

#### 3.2 Update PaymentService
```php
// app/Services/PaymentService.php
public function processManualPayment(array $data, CustomerInvoice $invoice, User $user): Payment
{
    return DB::transaction(function () use ($data, $invoice, $user) {
        // Validate amount
        $amount = (float) $data['amount'];
        $outstanding = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        
        if ($amount > $outstanding) {
            throw new DomainException("Jumlah melebihi sisa tagihan.");
        }

        // Handle file upload
        $paymentProofPath = null;
        if (isset($data['payment_proof_file'])) {
            $file = $data['payment_proof_file'];
            $filename = 'manual_' . now()->format('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $paymentProofPath = $file->storeAs('payment_proofs/manual', $filename, 'public');
        }

        // Create payment with source tracking
        $payment = Payment::create([
            'payment_number'        => 'PAY-MANUAL-' . now()->format('YmdHis'),
            'type'                  => 'incoming',
            'source'                => 'manual_entry',
            'entered_by_user_id'    => $user->id,
            'organization_id'       => $invoice->organization_id,
            'amount'                => $amount,
            'payment_date'          => $data['payment_date'],
            'payment_method'        => $data['payment_method'],
            'sender_bank_name'      => $data['sender_bank_name'] ?? null,
            'sender_account_number' => $data['sender_account_number'] ?? null,
            'giro_number'           => $data['giro_number'] ?? null,
            'giro_due_date'         => $data['giro_due_date'] ?? null,
            'issuing_bank'          => $data['issuing_bank'] ?? null,
            'receipt_number'        => $data['receipt_number'] ?? null,
            'payment_proof_path'    => $paymentProofPath,
            'bank_account_id'       => $data['bank_account_id'],
            'reference'             => $data['reference'] ?? null,
            'notes'                 => $data['notes'] ?? null,
            'status'                => 'completed',
            'approval_status'       => 'pending', // Require approval
        ]);

        // Create allocation
        $payment->allocations()->create([
            'customer_invoice_id' => $invoice->id,
            'allocated_amount'    => $amount,
        ]);

        // DON'T update invoice yet - wait for approval
        
        // Audit log
        $this->auditService->log(
            'payment.manual_entry.created',
            Payment::class,
            $payment->id,
            [
                'amount' => $amount,
                'invoice_id' => $invoice->id,
                'entered_by' => $user->id,
                'requires_approval' => true,
            ]
        );

        return $payment;
    });
}

public function approveManualPayment(Payment $payment, User $approver, ?string $notes = null): void
{
    DB::transaction(function () use ($payment, $approver, $notes) {
        // Update payment
        $payment->update([
            'approval_status' => 'approved',
            'approved_by_user_id' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        // NOW update invoice
        $allocation = $payment->allocations()->first();
        $invoice = $allocation->customerInvoice;
        
        $invoice->paid_amount = (float) $invoice->paid_amount + (float) $payment->amount;
        $invoice->status = $invoice->paid_amount >= (float) $invoice->total_amount
            ? CustomerInvoiceStatus::PAID
            : CustomerInvoiceStatus::PARTIAL_PAID;
        $invoice->save();

        // Release credit
        try {
            app(CreditControlService::class)->releaseCreditByAmount(
                $invoice->organization_id,
                clone $invoice->purchaseOrder,
                (float) $payment->amount
            );
        } catch (\Exception $e) {
            Log::warning('Credit release failed: ' . $e->getMessage());
        }

        // Audit log
        $this->auditService->log(
            'payment.manual_entry.approved',
            Payment::class,
            $payment->id,
            [
                'approved_by' => $approver->id,
                'invoice_updated' => true,
            ]
        );
    });
}
```

---

### **FASE 4: Approval Workflow**

#### 4.1 Create Approval Page
```
Route: /payments/manual/pending
View: resources/views/payments/manual_approval.blade.php

Features:
- List semua manual payment yang pending approval
- Show payment details + uploaded proof
- Approve / Reject buttons
- Add approval notes
```

#### 4.2 Permissions
```php
// database/seeders/RolePermissionSeeder.php
'create_manual_payment' => ['Finance Staff', 'Finance Manager'],
'approve_manual_payment' => ['Finance Manager', 'Super Admin'],
```

---

### **FASE 5: Testing & Validation**

#### 5.1 Test Cases
1. ✅ Submit manual payment dengan Bank Transfer
2. ✅ Submit manual payment dengan Giro/Cek
3. ✅ Submit manual payment dengan Cash
4. ✅ Autofill amount works correctly
5. ✅ Conditional fields show/hide correctly
6. ✅ All fields submit correctly (no data loss)
7. ✅ File upload works
8. ✅ Approval workflow works
9. ✅ Invoice status updates after approval
10. ✅ Audit trail recorded correctly

---

## 📊 PRIORITAS IMPLEMENTASI

### **HIGH PRIORITY (Must Have)**
1. ✅ Fix conditional fields (use hidden inputs + JS)
2. ✅ Fix autofill amount
3. ✅ Consolidate reference fields (single field)
4. ✅ Add source tracking (payment_proof vs manual_entry)
5. ✅ Add entered_by tracking

### **MEDIUM PRIORITY (Should Have)**
6. ✅ Add approval workflow
7. ✅ Create approval page
8. ✅ Add audit trail
9. ✅ Improve UX (visual feedback, validation)

### **LOW PRIORITY (Nice to Have)**
10. ⚪ Add bulk approval
11. ⚪ Add export to Excel
12. ⚪ Add email notification for approval

---

## 🚀 TIMELINE ESTIMASI

- **FASE 1 (Database)**: 2 jam
- **FASE 2 (Frontend)**: 4 jam
- **FASE 3 (Backend)**: 3 jam
- **FASE 4 (Approval)**: 3 jam
- **FASE 5 (Testing)**: 2 jam

**TOTAL**: ~14 jam (2 hari kerja)

---

## ✅ CHECKLIST IMPLEMENTASI

### Database
- [ ] Create migration for new fields
- [ ] Update Payment model
- [ ] Run migration
- [ ] Test database changes

### Frontend
- [ ] Rewrite form with hidden inputs
- [ ] Implement vanilla JS for conditional fields
- [ ] Fix autofill amount
- [ ] Add visual feedback
- [ ] Test form submission

### Backend
- [ ] Rename request class
- [ ] Update validation rules
- [ ] Update PaymentService
- [ ] Add approval methods
- [ ] Test backend logic

### Approval Workflow
- [ ] Create approval page
- [ ] Add routes
- [ ] Add permissions
- [ ] Test approval flow

### Testing
- [ ] Test all payment methods
- [ ] Test conditional fields
- [ ] Test approval workflow
- [ ] Test audit trail
- [ ] UAT with user

---

## 📝 NOTES

1. **Jangan gunakan Alpine.js untuk conditional fields** - Gunakan vanilla JavaScript yang lebih reliable
2. **Jangan gunakan `:disabled`** - Field disabled tidak ter-submit
3. **Gunakan hidden inputs** - Lebih reliable untuk conditional fields
4. **Single `reference` field** - Jangan duplikat field
5. **Add approval workflow** - Manual entry harus di-approve dulu sebelum update invoice
6. **Track everything** - Source, entered_by, approved_by untuk audit trail

---

**Apakah rencana ini sudah jelas? Mau saya mulai implementasi dari FASE 1?**
