# 🧪 TESTING GUIDE - MEDIKINDO PO SYSTEM v2.0

**Date**: April 14, 2026  
**Version**: 2.0 (Post-Critical Fixes)  
**Purpose**: Verify all critical fixes work correctly  
**Estimated Time**: 2-3 hours

---

## 📋 TESTING OVERVIEW

### Test Categories:
1. ✅ **Payment Validation** (CRITICAL)
2. ✅ **Invoice Creation** (CRITICAL)
3. ✅ **Goods Receipt Flow** (HIGH)
4. ✅ **Purchase Order Flow** (HIGH)
5. ✅ **Old Routes Removal** (MEDIUM)
6. ✅ **Edge Cases** (MEDIUM)

---

## 🔴 TEST SUITE 1: PAYMENT VALIDATION (CRITICAL)

### Test 1.1: Payment OUT Without Payment IN (Should FAIL)
**Objective**: Verify system blocks payment to supplier before receiving payment from RS

**Prerequisites**:
- 1 completed GR
- 1 supplier invoice created from GR
- NO customer invoice or payment IN

**Steps**:
1. Login as Finance user
2. Navigate to Supplier Invoice detail page
3. Click "Bayar Pemasok" (Pay Supplier)
4. Enter payment amount: Rp 1,000,000
5. Submit payment

**Expected Result**: ❌ ERROR
```
Error Message: "Pembayaran ke supplier tidak dapat dilakukan. 
RS/Klinik belum melakukan pembayaran untuk invoice ini."
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 1.2: Payment OUT Less Than Payment IN (Should PASS)
**Objective**: Verify system allows payment OUT when sufficient payment IN received

**Prerequisites**:
- 1 completed GR
- 1 supplier invoice: Rp 1,000,000
- 1 customer invoice: Rp 1,000,000
- Payment IN from RS: Rp 1,000,000

**Steps**:
1. Login as Finance user
2. Navigate to Supplier Invoice detail page
3. Click "Bayar Pemasok"
4. Enter payment amount: Rp 500,000
5. Submit payment

**Expected Result**: ✅ SUCCESS
```
Success Message: "Pembayaran ke supplier berhasil diproses."
Invoice paid_amount: Rp 500,000
Invoice status: partial
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 1.3: Payment OUT Equal to Payment IN (Should PASS)
**Objective**: Verify system allows full payment when full payment IN received

**Prerequisites**:
- 1 completed GR
- 1 supplier invoice: Rp 1,000,000
- 1 customer invoice: Rp 1,000,000
- Payment IN from RS: Rp 1,000,000

**Steps**:
1. Login as Finance user
2. Navigate to Supplier Invoice detail page
3. Click "Bayar Pemasok"
4. Enter payment amount: Rp 1,000,000
5. Submit payment

**Expected Result**: ✅ SUCCESS
```
Success Message: "Pembayaran ke supplier berhasil diproses."
Invoice paid_amount: Rp 1,000,000
Invoice status: paid
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 1.4: Payment OUT Greater Than Payment IN (Should FAIL)
**Objective**: Verify system blocks payment OUT exceeding payment IN

**Prerequisites**:
- 1 completed GR
- 1 supplier invoice: Rp 1,000,000
- 1 customer invoice: Rp 1,000,000
- Payment IN from RS: Rp 500,000

**Steps**:
1. Login as Finance user
2. Navigate to Supplier Invoice detail page
3. Click "Bayar Pemasok"
4. Enter payment amount: Rp 1,000,000
5. Submit payment

**Expected Result**: ❌ ERROR
```
Error Message: "Pembayaran ke supplier tidak dapat dilakukan. 
RS/Klinik baru membayar Rp 500,000 dari total Rp 1,000,000."
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

## 🔴 TEST SUITE 2: INVOICE CREATION (CRITICAL)

### Test 2.1: Create Invoice from Completed GR (Should PASS)
**Objective**: Verify invoice can be created from completed GR

**Prerequisites**:
- 1 approved PO with items
- 1 completed GR (all items received)

**Steps**:
1. Login as user with invoice permission
2. Navigate to "Invoice Pemasok" → "Buat Invoice"
3. Select completed GR from dropdown
4. Verify items auto-populated with:
   - Product name
   - Batch number (read-only from GR)
   - Expiry date (read-only from GR)
   - Quantity (max = remaining GR quantity)
   - Unit price (from PO item)
5. Enter supplier invoice number
6. Enter due date
7. Submit invoice

**Expected Result**: ✅ SUCCESS
```
Success Message: "Invoice Pemasok [INV-NUMBER] berhasil dibuat."
Invoice created with:
- goods_receipt_id: NOT NULL
- batch_no: from GR
- expiry_date: from GR
- unit_price: from PO item
- status: issued
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 2.2: Create Invoice from Partial GR (Should FAIL)
**Objective**: Verify invoice cannot be created from partial GR

**Prerequisites**:
- 1 approved PO with items
- 1 partial GR (some items not fully received)

**Steps**:
1. Login as user with invoice permission
2. Navigate to "Invoice Pemasok" → "Buat Invoice"
3. Check GR dropdown

**Expected Result**: ❌ GR NOT IN LIST
```
Partial GR should NOT appear in dropdown
Only completed GRs should be available
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 2.3: Create Invoice Without GR (Should FAIL)
**Objective**: Verify old invoice route is removed

**Prerequisites**:
- 1 approved PO

**Steps**:
1. Try to access old route: `POST /purchase-orders/{id}/issue-invoice`
2. Or try to find "Issue Invoice" button on PO detail page

**Expected Result**: ❌ 404 NOT FOUND
```
Route should not exist
Button should not exist on PO page
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 2.4: Invoice Quantity Exceeds GR Remaining (Should FAIL)
**Objective**: Verify invoice quantity validation

**Prerequisites**:
- 1 completed GR with item quantity = 100
- 1 existing invoice using 60 units (remaining = 40)

**Steps**:
1. Login as user with invoice permission
2. Navigate to "Invoice Pemasok" → "Buat Invoice"
3. Select same GR
4. Enter quantity: 50 (exceeds remaining 40)
5. Submit invoice

**Expected Result**: ❌ ERROR
```
Error Message: "Quantity (50) exceeds remaining quantity (40) for [Product Name]."
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 2.5: Partial Invoicing (Should PASS)
**Objective**: Verify multiple invoices can be created from one GR

**Prerequisites**:
- 1 completed GR with item quantity = 100

**Steps**:
1. Create first invoice with quantity = 60
2. Verify success
3. Create second invoice with quantity = 40
4. Verify success
5. Try to create third invoice

**Expected Result**: 
```
First invoice: ✅ SUCCESS (remaining = 40)
Second invoice: ✅ SUCCESS (remaining = 0)
Third invoice: ❌ GR not in dropdown (fully invoiced)
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 2.6: Price Manipulation Attempt (Should FAIL)
**Objective**: Verify user cannot manipulate price

**Prerequisites**:
- 1 completed GR
- PO item unit_price = Rp 10,000

**Steps**:
1. Open browser developer tools
2. Navigate to invoice creation form
3. Try to modify unit_price field in HTML
4. Submit invoice
5. Check created invoice

**Expected Result**: ✅ PRICE FROM PO
```
Invoice line item unit_price = Rp 10,000 (from PO)
User input ignored
No price manipulation possible
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

## 🟠 TEST SUITE 3: GOODS RECEIPT FLOW (HIGH)

### Test 3.1: Create GR from Approved PO (Should PASS)
**Objective**: Verify GR can be created from approved PO

**Prerequisites**:
- 1 approved PO with items

**Steps**:
1. Login as user with GR permission
2. Navigate to "Penerimaan Barang" → "Rekam Penerimaan Barang"
3. Select approved PO from dropdown
4. Verify items auto-populated
5. Enter batch number for each item
6. Enter expiry date for each item
7. Enter quantity received (full or partial)
8. Submit GR

**Expected Result**: ✅ SUCCESS
```
Success Message: "Goods Receipt [GR-NUMBER] berhasil dibuat."
GR status: completed (if all full) or partial (if any partial)
PO status: completed
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 3.2: GR Status Determination (Should PASS)
**Objective**: Verify GR status is determined correctly

**Test 3.2a: Full Receipt**
- PO item quantity: 100
- Quantity received: 100
- Expected GR status: completed ✅

**Test 3.2b: Partial Receipt**
- PO item quantity: 100
- Quantity received: 60
- Expected GR status: partial ✅

**Test 3.2c: Multiple Items Mixed**
- Item 1: 100/100 (full)
- Item 2: 60/100 (partial)
- Expected GR status: partial ✅

**Actual Results**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 3.3: No Pending Status (Should PASS)
**Objective**: Verify GR never has "pending" status

**Prerequisites**:
- 1 approved PO

**Steps**:
1. Create GR with any quantity
2. Check GR status immediately after creation
3. Check GR index page filters

**Expected Result**: ✅ NO PENDING
```
GR status: completed or partial (never pending)
Index page filters: All, Partial, Completed (no Pending tab)
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 3.4: PO Status After GR (Should PASS)
**Objective**: Verify PO status changes to completed after GR

**Prerequisites**:
- 1 approved PO

**Steps**:
1. Check PO status: approved
2. Create GR (full or partial)
3. Check PO status again

**Expected Result**: ✅ PO COMPLETED
```
PO status before GR: approved
PO status after GR: completed
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

## 🟠 TEST SUITE 4: PURCHASE ORDER FLOW (HIGH)

### Test 4.1: PO Workflow (Should PASS)
**Objective**: Verify simplified PO workflow

**Steps**:
1. Create PO (status: draft)
2. Submit PO (status: submitted)
3. Approve PO (status: approved)
4. Create GR (PO status: completed)

**Expected Result**: ✅ WORKFLOW CORRECT
```
draft → submitted → approved → completed
No shipped or delivered status
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 4.2: No Shipped/Delivered Status (Should PASS)
**Objective**: Verify shipped and delivered status removed

**Steps**:
1. Check PO model constants
2. Check PO detail page
3. Check PO index page filters

**Expected Result**: ✅ NO SHIPPED/DELIVERED
```
PO statuses: draft, submitted, approved, completed, cancelled
No shipped or delivered status
No "Mark as Shipped" button
No "Mark as Delivered" button
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 4.3: Old Delivery Routes (Should FAIL)
**Objective**: Verify delivery routes removed

**Steps**:
1. Try to access: `POST /purchase-orders/{id}/mark-shipped`
2. Try to access: `POST /purchase-orders/{id}/mark-delivered`

**Expected Result**: ❌ 404 NOT FOUND
```
Both routes should return 404
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

## 🟡 TEST SUITE 5: OLD ROUTES REMOVAL (MEDIUM)

### Test 5.1: Old Invoice Route (Should FAIL)
**Objective**: Verify old invoice route removed

**Steps**:
```bash
curl -X POST http://medikindo-po.test/purchase-orders/1/issue-invoice
```

**Expected Result**: ❌ 404 NOT FOUND

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 5.2: Old Delivery Routes (Should FAIL)
**Objective**: Verify delivery routes removed

**Steps**:
```bash
curl -X POST http://medikindo-po.test/purchase-orders/1/mark-shipped
curl -X POST http://medikindo-po.test/purchase-orders/1/mark-delivered
```

**Expected Result**: ❌ 404 NOT FOUND (both)

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

## 🟡 TEST SUITE 6: EDGE CASES (MEDIUM)

### Test 6.1: Multiple Partial Invoices
**Objective**: Verify complex partial invoicing scenario

**Scenario**:
- GR with 3 items: A (100), B (200), C (150)
- Invoice 1: A (50), B (100), C (75)
- Invoice 2: A (30), B (50), C (50)
- Invoice 3: A (20), B (50), C (25)

**Expected Result**: ✅ ALL SUCCESS
```
After Invoice 1: Remaining A=50, B=100, C=75
After Invoice 2: Remaining A=20, B=50, C=25
After Invoice 3: Remaining A=0, B=0, C=0
GR fully invoiced, not in dropdown anymore
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 6.2: Concurrent Invoice Creation
**Objective**: Verify race condition handling

**Scenario**:
- GR with item quantity = 100
- User A creates invoice with quantity = 80 (remaining = 20)
- User B tries to create invoice with quantity = 50 (should fail)

**Expected Result**: 
```
User A: ✅ SUCCESS (remaining = 20)
User B: ❌ ERROR "Quantity (50) exceeds remaining quantity (20)"
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 6.3: Payment Sequence Validation
**Objective**: Verify complex payment scenario

**Scenario**:
- Supplier invoice: Rp 1,000,000
- Customer invoice: Rp 1,000,000
- Payment IN 1: Rp 300,000
- Payment OUT 1: Rp 300,000 (should pass)
- Payment OUT 2: Rp 100,000 (should fail - insufficient funds)
- Payment IN 2: Rp 400,000
- Payment OUT 2: Rp 400,000 (should pass)

**Expected Result**: 
```
Payment OUT 1: ✅ SUCCESS (balance = 0)
Payment OUT 2 (first attempt): ❌ FAIL (insufficient)
Payment IN 2: ✅ SUCCESS (balance = 400,000)
Payment OUT 2 (second attempt): ✅ SUCCESS (balance = 0)
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Test 6.4: Batch/Expiry Immutability
**Objective**: Verify batch and expiry cannot be modified in invoice

**Scenario**:
- GR item: batch = "BATCH001", expiry = "2025-12-31"
- Create invoice from GR
- Try to modify batch/expiry in invoice

**Expected Result**: ✅ READ-ONLY
```
Batch and expiry fields are read-only in invoice form
Values come from GR (cannot be changed)
Invoice line item has correct batch/expiry from GR
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

## 📊 TEST SUMMARY

### Test Results:

| Test Suite | Total Tests | Passed | Failed | Status |
|------------|-------------|--------|--------|--------|
| Payment Validation | 4 | [ ] | [ ] | [ ] |
| Invoice Creation | 6 | [ ] | [ ] | [ ] |
| Goods Receipt Flow | 4 | [ ] | [ ] | [ ] |
| Purchase Order Flow | 3 | [ ] | [ ] | [ ] |
| Old Routes Removal | 2 | [ ] | [ ] | [ ] |
| Edge Cases | 4 | [ ] | [ ] | [ ] |
| **TOTAL** | **23** | **[ ]** | **[ ]** | **[ ]** |

---

## 🎯 ACCEPTANCE CRITERIA

### Must Pass (Blockers):
- [ ] All Payment Validation tests pass
- [ ] All Invoice Creation tests pass
- [ ] Old routes return 404
- [ ] No critical errors in logs

### Should Pass (Important):
- [ ] All Goods Receipt tests pass
- [ ] All Purchase Order tests pass
- [ ] Edge cases handled correctly

### Nice to Have:
- [ ] Performance acceptable
- [ ] User experience smooth
- [ ] No UI glitches

---

## 📝 TEST EXECUTION LOG

### Tester Information:
- **Name**: [ ]
- **Date**: [ ]
- **Environment**: [ ] Staging [ ] Production
- **Browser**: [ ]
- **Database**: [ ]

### Test Execution:
- **Start Time**: [ ]
- **End Time**: [ ]
- **Duration**: [ ]
- **Total Tests**: 23
- **Passed**: [ ]
- **Failed**: [ ]
- **Blocked**: [ ]

### Issues Found:
```
Issue #1:
- Test: [ ]
- Description: [ ]
- Severity: [ ] Critical [ ] High [ ] Medium [ ] Low
- Status: [ ]

Issue #2:
- Test: [ ]
- Description: [ ]
- Severity: [ ] Critical [ ] High [ ] Medium [ ] Low
- Status: [ ]
```

---

## 🔧 TROUBLESHOOTING

### Common Issues:

#### Issue: Payment validation not working
**Solution**: 
1. Check `PaymentService::processOutgoingPayment()` method
2. Verify customer invoice exists
3. Check payment IN amount

#### Issue: Invoice creation fails
**Solution**:
1. Check GR status (must be completed)
2. Verify goods_receipt_id not null
3. Check remaining quantity

#### Issue: Old routes still accessible
**Solution**:
1. Clear route cache: `php artisan route:clear`
2. Verify routes/web.php
3. Check route list: `php artisan route:list`

---

## ✅ SIGN-OFF

### Test Completion:
- [ ] All critical tests passed
- [ ] All issues documented
- [ ] Test report submitted
- [ ] System approved for deployment

**Tested By**: [ ]  
**Date**: [ ]  
**Signature**: [ ]

---

**END OF TESTING GUIDE**
