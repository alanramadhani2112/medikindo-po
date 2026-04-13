# Invoice-GR Integration - Testing Guide

## Pre-Testing Checklist

### 1. Database Migrations
```bash
# Verify migrations are run
php artisan migrate:status

# Should show:
# ✓ 2026_04_14_000001_add_goods_receipt_to_invoices
# ✓ 2026_04_14_000002_add_batch_expiry_to_goods_receipt_items
```

### 2. Routes Verification
```bash
# Check routes are registered
php artisan route:list --name=web.invoices.supplier

# Should show:
# POST   /invoices/supplier
# GET    /invoices/supplier/create
# GET    /invoices/supplier/{invoice}
# GET    /invoices/supplier/{invoice}/pdf
```

### 3. Syntax Checks
All files passed syntax validation:
- ✅ `app/Http/Controllers/Web/InvoiceWebController.php`
- ✅ `app/Services/InvoiceFromGRService.php`
- ✅ `app/Http/Requests/StoreInvoiceFromGRRequest.php`
- ✅ `routes/web.php`

## Test Scenarios

### Scenario 1: Create Invoice from GR (Full Quantity)

**Prerequisites:**
- Have a completed Goods Receipt with batch/expiry data
- User has `create_invoices` permission

**Steps:**
1. Login as Finance user
2. Navigate to `/invoices?tab=supplier`
3. Click "Buat Invoice Pemasok" button
4. Select a Goods Receipt from dropdown
5. Verify:
   - GR info displays correctly
   - Items auto-load with batch/expiry (read-only)
   - Quantities default to remaining quantity
6. Fill in:
   - Supplier invoice number
   - Due date
   - Notes (optional)
7. Submit form

**Expected Result:**
- ✅ Invoice created successfully
- ✅ Redirected to invoice detail page
- ✅ Success message: "Invoice Pemasok {number} berhasil dibuat"
- ✅ Invoice shows GR reference
- ✅ Line items show batch and expiry from GR
- ✅ GR remaining quantity updated

**Database Verification:**
```sql
-- Check invoice created
SELECT * FROM supplier_invoices WHERE goods_receipt_id = [GR_ID];

-- Check line items have GR item reference
SELECT * FROM supplier_invoice_line_items 
WHERE supplier_invoice_id = [INVOICE_ID];

-- Verify batch and expiry copied from GR
SELECT 
    sili.batch_no as invoice_batch,
    sili.expiry_date as invoice_expiry,
    gri.batch_no as gr_batch,
    gri.expiry_date as gr_expiry
FROM supplier_invoice_line_items sili
JOIN goods_receipt_items gri ON sili.goods_receipt_item_id = gri.id
WHERE sili.supplier_invoice_id = [INVOICE_ID];
```

---

### Scenario 2: Create Invoice from GR (Partial Quantity)

**Prerequisites:**
- Have a completed Goods Receipt with multiple items
- User has `create_invoices` permission

**Steps:**
1. Navigate to `/invoices/supplier/create`
2. Select a Goods Receipt
3. Change invoice quantity to LESS than remaining quantity (e.g., 5 out of 10)
4. Submit form

**Expected Result:**
- ✅ Invoice created with partial quantity
- ✅ GR remaining quantity updated correctly
- ✅ GR still appears in dropdown for next invoice (has remaining qty)

**Verification:**
```sql
-- Check remaining quantity calculation
SELECT 
    gri.quantity_received,
    COALESCE(SUM(sili.quantity), 0) as invoiced_qty,
    gri.quantity_received - COALESCE(SUM(sili.quantity), 0) as remaining_qty
FROM goods_receipt_items gri
LEFT JOIN supplier_invoice_line_items sili ON gri.id = sili.goods_receipt_item_id
WHERE gri.goods_receipt_id = [GR_ID]
GROUP BY gri.id;
```

---

### Scenario 3: Create Multiple Invoices from Same GR

**Prerequisites:**
- Have a completed GR with remaining quantity
- Already created one partial invoice

**Steps:**
1. Navigate to `/invoices/supplier/create`
2. Select the SAME Goods Receipt used in Scenario 2
3. Verify:
   - "Already Invoiced" column shows correct quantity
   - "Remaining" column shows correct remaining quantity
   - Invoice qty defaults to remaining quantity
4. Submit form

**Expected Result:**
- ✅ Second invoice created successfully
- ✅ Both invoices reference same GR
- ✅ Total invoiced qty = GR received qty
- ✅ GR no longer appears in dropdown (fully invoiced)

---

### Scenario 4: Validation - Quantity Exceeds Remaining

**Steps:**
1. Navigate to `/invoices/supplier/create`
2. Select a Goods Receipt
3. Manually change invoice quantity to EXCEED remaining quantity
   - Use browser dev tools to bypass HTML max attribute
   - Or edit form data before submit
4. Submit form

**Expected Result:**
- ❌ Validation error
- ❌ Error message: "Jumlah invoice untuk [product] melebihi sisa yang tersedia"
- ❌ Form not submitted
- ❌ User stays on create page

---

### Scenario 5: Validation - GR Not Completed

**Steps:**
1. Create a GR with status 'partial' or 'pending'
2. Navigate to `/invoices/supplier/create`
3. Verify GR does NOT appear in dropdown

**Expected Result:**
- ✅ Only GRs with status 'completed' appear
- ✅ GRs with other statuses are filtered out

---

### Scenario 6: Validation - GR Fully Invoiced

**Steps:**
1. Create invoice for full quantity of a GR
2. Navigate to `/invoices/supplier/create`
3. Verify GR does NOT appear in dropdown

**Expected Result:**
- ✅ Fully invoiced GRs do not appear
- ✅ Only GRs with remaining quantity appear

---

### Scenario 7: Batch & Expiry Read-Only

**Steps:**
1. Navigate to `/invoices/supplier/create`
2. Select a Goods Receipt
3. Inspect batch and expiry fields in browser

**Expected Result:**
- ✅ Batch displayed as badge (not input field)
- ✅ Expiry displayed as text (not input field)
- ✅ No way to edit batch/expiry in UI
- ✅ Batch/expiry values match GR exactly

---

### Scenario 8: PDF Generation

**Steps:**
1. Create an invoice from GR
2. Navigate to invoice detail page
3. Click "Export PDF" button

**Expected Result:**
- ✅ PDF downloads successfully
- ✅ PDF shows GR reference number
- ✅ PDF shows line items table with:
  - Product name
  - Batch number
  - Expiry date
  - Quantity
  - Subtotal
- ✅ PDF shows financial summary

---

### Scenario 9: Pricing Calculation

**Prerequisites:**
- GR with items that have discount and tax

**Steps:**
1. Create invoice from GR
2. Verify calculations:
   - Subtotal = qty × unit_price
   - Discount applied correctly
   - Tax applied correctly
   - Total = subtotal - discount + tax

**Expected Result:**
- ✅ Pricing matches existing calculation logic
- ✅ No changes to pricing engine
- ✅ Calculations are accurate

---

### Scenario 10: Permission Check

**Steps:**
1. Login as user WITHOUT `create_invoices` permission
2. Navigate to `/invoices?tab=supplier`
3. Verify "Buat Invoice Pemasok" button does NOT appear
4. Try to access `/invoices/supplier/create` directly

**Expected Result:**
- ✅ Button hidden for users without permission
- ✅ Direct access returns 403 Forbidden
- ✅ Error message: "Akses Ditolak. Anda tidak memiliki izin untuk membuat invoice."

---

## Edge Cases

### Edge Case 1: GR with No Items
**Test**: Select GR with 0 items
**Expected**: Validation error or empty items array

### Edge Case 2: Deleted GR Item
**Test**: Delete GR item after GR created, try to invoice
**Expected**: Validation error - item not found

### Edge Case 3: Concurrent Invoice Creation
**Test**: Two users create invoice from same GR simultaneously
**Expected**: One succeeds, other gets validation error (qty exceeded)

### Edge Case 4: Invalid GR ID
**Test**: Submit form with non-existent goods_receipt_id
**Expected**: Validation error - GR not found

### Edge Case 5: GR Without Supplier
**Test**: GR with PO that has no supplier
**Expected**: GR filtered out from dropdown

---

## Performance Tests

### Test 1: Large GR (100+ items)
- Create GR with 100+ items
- Create invoice from this GR
- Verify page loads in reasonable time (<3 seconds)

### Test 2: Multiple Partial Invoices
- Create 10 partial invoices from same GR
- Verify remaining quantity calculation is accurate
- Verify no performance degradation

---

## Browser Compatibility

Test in:
- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Edge (latest)
- ✅ Safari (latest)

---

## Rollback Plan

If critical issues found:

1. **Disable Routes**:
```php
// In routes/web.php, comment out:
// Route::get('/supplier/create', ...)->name('supplier.create');
// Route::post('/supplier', ...)->name('supplier.store');
```

2. **Hide UI Button**:
```blade
{{-- In index_supplier.blade.php, comment out: --}}
{{-- <a href="{{ route('web.invoices.supplier.create') }}">Buat Invoice</a> --}}
```

3. **Rollback Migrations** (if needed):
```bash
php artisan migrate:rollback --step=2
```

---

## Success Criteria

All scenarios must pass:
- ✅ Invoice created from GR successfully
- ✅ Batch/expiry locked from GR
- ✅ Quantity validation enforced
- ✅ Partial invoicing works
- ✅ Multiple invoices from same GR works
- ✅ PDF shows batch/expiry
- ✅ Pricing calculations accurate
- ✅ Permissions enforced
- ✅ No syntax errors
- ✅ No database errors

---

## Test Execution Log

| Scenario | Status | Tester | Date | Notes |
|----------|--------|--------|------|-------|
| 1. Full Quantity | ⏳ | - | - | - |
| 2. Partial Quantity | ⏳ | - | - | - |
| 3. Multiple Invoices | ⏳ | - | - | - |
| 4. Qty Validation | ⏳ | - | - | - |
| 5. GR Status Filter | ⏳ | - | - | - |
| 6. Fully Invoiced Filter | ⏳ | - | - | - |
| 7. Read-Only Fields | ⏳ | - | - | - |
| 8. PDF Generation | ⏳ | - | - | - |
| 9. Pricing Calculation | ⏳ | - | - | - |
| 10. Permission Check | ⏳ | - | - | - |

---

**Status**: Ready for Testing ✅
**Last Updated**: April 14, 2026
