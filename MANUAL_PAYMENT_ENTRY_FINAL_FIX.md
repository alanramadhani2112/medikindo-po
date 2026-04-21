# Manual Payment Entry - Final Fix

## Date: April 21, 2026

## Problem Summary
The Manual Payment Entry form needed to be an EXACT copy of the Payment Proof form structure, with the addition of a "Bank Penerima (Medikindo)" field. Previous implementations had issues with:
1. Missing "Bayar Penuh" / "Bayar Sebagian" toggle
2. Confusing field structure with duplicate reference fields
3. Conditional fields not working properly

## Solution Implemented

### Form Structure (Exact Match with Payment Proof)
The form now follows this exact structure:

1. **Pilih Invoice AR** - Select unpaid customer invoice
2. **Jenis Pembayaran** - Toggle between "Bayar Penuh" and "Bayar Sebagian"
   - Bayar Penuh: Auto-fills full outstanding amount
   - Bayar Sebagian: User enters partial amount with validation
3. **Tanggal Pembayaran** - Payment date (defaults to today)
4. **Metode Pembayaran** - Payment method dropdown:
   - 🏦 Bank Transfer
   - 💳 Virtual Account
   - 📄 Giro/Cek
   - 💵 Cash (Tunai)

### Conditional Fields (Using `:style` with `:disabled`)

#### 5a. Bank Transfer / Virtual Account
- **Bank Pengirim (RS/Klinik)** - Dropdown from IndonesianBankSeeder (required)
- **Nomor Rekening Pengirim** - Text input (required)
- **No. Referensi Transfer** - Text input (required)

#### 5b. Giro/Cek
- **Nomor Giro/Cek** - Text input (required)
- **Tanggal Jatuh Tempo** - Date input (required)
- **Bank Penerbit Giro/Cek** - Dropdown from IndonesianBankSeeder (required)
- **No. Referensi Giro/Cek** - Text input (required)

#### 5c. Cash
- **Nomor Kwitansi** - Text input (required)

### Additional Fields (Medikindo-specific)

6. **Bank Penerima (Medikindo)** - REQUIRED dropdown
   - Shows all Medikindo bank accounts marked for receiving
   - Default account pre-selected
   - User must select which Medikindo bank receives the payment

7. **Catatan Tambahan** - Optional notes

8. **Upload Bukti Pembayaran** - REQUIRED file upload
   - Label changes based on payment method:
     - Bank Transfer/VA: "Upload Bukti Transfer"
     - Cash: "Upload Kwitansi"
     - Giro/Cek: "Upload Foto Giro/Cek"
   - Accepts: JPG, PNG, PDF (max 5MB)

## Key Technical Details

### Alpine.js State Management
```javascript
{
    invoiceId: '',
    paymentType: 'full',  // 'full' or 'partial'
    paymentMethod: '',
    outstanding: 0,
    partialAmount: '',
    
    // Computed properties
    showBankDropdown: ['Bank Transfer', 'Virtual Account'].includes(paymentMethod),
    showGiroFields: paymentMethod === 'Giro/Cek',
    showCashFields: paymentMethod === 'Cash',
    amount: paymentType === 'full' ? outstanding : parseFloat(partialAmount),
    isPartialValid: partialAmount > 0 && partialAmount < outstanding
}
```

### Conditional Field Pattern
```html
<div :style="!showBankDropdown ? 'display: none;' : ''">
    <input name="sender_bank_name" :disabled="!showBankDropdown">
</div>
```

**Why this pattern?**
- `:style` hides the field visually but keeps it in DOM
- `:disabled` prevents submission when hidden
- Browser submits disabled fields with empty values (not omitted)
- This ensures backend validation works correctly

### Backend Validation (StoreIncomingPaymentRequest)

**Conditional Required Fields:**
- Bank Transfer/VA: `sender_bank_name`, `sender_account_number`, `reference` (required)
- Giro/Cek: `giro_number`, `giro_due_date`, `issuing_bank`, `giro_reference` (required)
- Cash: `receipt_number` (required)
- All methods: `bank_account_id`, `payment_proof_file` (required)

### Database Schema (payments table)

Fields added for manual entry:
- `sender_bank_name` (varchar 100)
- `sender_account_number` (varchar 50)
- `giro_number` (varchar 50)
- `giro_due_date` (date)
- `issuing_bank` (varchar 100)
- `receipt_number` (varchar 50)
- `payment_proof_path` (varchar 255)

## Differences from Payment Proof Form

| Feature | Payment Proof | Manual Payment Entry |
|---------|--------------|---------------------|
| Bank Penerima field | ❌ No (auto-assigned from invoice) | ✅ Yes (user selects) |
| Giro bank field name | `sender_bank_name` (reused) | `issuing_bank` (separate) |
| Cash receipt field | Uses `bank_reference` | Uses `receipt_number` |
| Reference field | `bank_reference` (all methods) | `reference` (Bank/VA), `giro_reference` (Giro) |
| File upload field | `file` | `payment_proof_file` |
| Status after submit | SUBMITTED (needs approval) | COMPLETED (direct entry) |

## Testing Checklist

- [ ] Invoice selection auto-fills outstanding amount
- [ ] "Bayar Penuh" toggle shows full amount
- [ ] "Bayar Sebagian" toggle enables partial amount input
- [ ] Partial amount validation works (must be > 0 and < outstanding)
- [ ] Bank Transfer shows: bank dropdown + account number + reference
- [ ] Virtual Account shows: bank dropdown + account number + reference
- [ ] Giro/Cek shows: giro number + due date + issuing bank + giro reference
- [ ] Cash shows: receipt number only
- [ ] Bank Penerima (Medikindo) is required
- [ ] File upload is required
- [ ] Form submits correctly with all fields
- [ ] Backend validation works for all payment methods
- [ ] Payment is recorded in database
- [ ] Invoice status updates correctly (PAID or PARTIAL_PAID)

## Files Modified

1. `resources/views/payments/create_incoming.blade.php` - Complete form rewrite
2. `app/Http/Requests/StoreIncomingPaymentRequest.php` - Already correct
3. `app/Services/PaymentService.php` - Already handles all fields
4. `database/migrations/2026_04_21_132826_add_manual_entry_fields_to_payments_table.php` - Already executed

## Next Steps

1. Test the form end-to-end with all payment methods
2. Verify backend processing works correctly
3. Check that invoice status updates properly
4. Ensure payment ledger shows correct data
5. Test with partial payments to verify remaining balance calculation

## Notes

- This form is for MANUAL ENTRY ONLY (special cases: cash, cleared checks, corrections)
- If RS/Klinik already submitted payment proof, DO NOT use this form - approve via Payment Proofs menu instead
- The warning message at the top of the form reminds users of this distinction
