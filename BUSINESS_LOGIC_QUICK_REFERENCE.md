# Business Logic Quick Reference
## Medikindo PO System - Developer Guide

**Last Updated**: 13 April 2026  
**Status**: ✅ Production Ready

---

## 🚀 Quick Start

### Complete Workflow Overview

```
PO Creation → Submit → Approve → Ship → Deliver → Goods Receipt → Invoice → Payment
```

---

## 📊 State Machines

### Purchase Order States

```
draft → submitted → approved → shipped → delivered → completed
                  ↓
               rejected
```

**Rules**:
- ✅ Can only edit in `draft` state
- ✅ Must have items before submit
- ✅ Credit reserved on submit
- ✅ Credit billed on approve
- ✅ Credit reversed on reject
- ✅ No skipping states

### Invoice States

```
issued → payment_submitted → paid
  ↓
pending_approval (if discrepancy detected)
  ↓
issued (after approval)
```

**Rules**:
- ✅ Financial fields immutable after `issued`
- ✅ Healthcare User can confirm payment
- ✅ Finance can verify payment
- ✅ Discrepancy requires Finance approval

---

## 🔐 RBAC Quick Reference

### Healthcare User (12 permissions)
```php
✅ Create/Edit PO (draft only)
✅ Submit PO
✅ Create Goods Receipt
✅ Confirm Payment (own org only)
❌ Approve PO
❌ Issue Invoice
❌ Verify Payment
```

### Approver (4 permissions)
```php
✅ View PO (all orgs)
✅ Approve/Reject PO
✅ Mark Shipped/Delivered
❌ Create/Edit PO
❌ Issue Invoice
```

### Finance (11 permissions)
```php
✅ View/Issue Invoice
✅ Approve Discrepancy
✅ Verify Payment
✅ Manage Credit Control
❌ Create/Edit PO
❌ Approve PO
```

### Super Admin (29 permissions)
```php
✅ ALL PERMISSIONS
✅ Manage Master Data
✅ View Audit Log
```

---

## 💰 BCMath Usage

### Always Use BCMath for Money

```php
// ✅ CORRECT
$calculator = app(BCMathCalculatorService::class);
$total = $calculator->add('100.50', '50.25'); // "150.75"

// ❌ WRONG
$total = 100.50 + 50.25; // Float precision issues!
```

### Common Operations

```php
// Addition
$result = $calculator->add('100.00', '50.00'); // "150.00"

// Subtraction
$result = $calculator->subtract('100.00', '30.00'); // "70.00"

// Multiplication
$result = $calculator->multiply('10.00', '5.50'); // "55.00"

// Division
$result = $calculator->divide('100.00', '3.00'); // "33.33"

// Percentage
$result = $calculator->percentage('100.00', '10.00'); // "10.00" (10% of 100)

// Sum array
$result = $calculator->sum(['10.00', '20.00', '30.00']); // "60.00"

// Comparison
$isGreater = $calculator->greaterThan('100.00', '50.00'); // true
$isEqual = $calculator->equals('100.00', '100.00'); // true
```

---

## 🔒 Immutability Rules

### Immutable Fields (After Invoice Issued)

```php
// ❌ CANNOT MODIFY
- total_amount
- subtotal_amount
- discount_amount
- tax_amount
- invoice_number
- due_date
- purchase_order_id
- goods_receipt_id
- Line item: quantity, unit_price, discount, tax_rate

// ✅ CAN MODIFY
- status
- paid_amount
- payment_reference
- verified_by
- verified_at
- notes
```

### How It Works

```php
// Automatic enforcement via Observer
// No need to manually check in controllers

// Example: This will throw ImmutabilityViolationException
$invoice->total_amount = '999.99'; // ❌ Blocked by observer
$invoice->save();

// This is allowed
$invoice->status = 'paid'; // ✅ Allowed
$invoice->save();
```

---

## 🎯 Service Layer Usage

### Creating PO

```php
use App\Services\POService;

$poService = app(POService::class);

// Create PO
$po = $poService->createPO($user, [
    'supplier_id' => 1,
    'requested_date' => '2026-04-20',
    'notes' => 'Urgent order',
]);

// Add items
$poService->syncItems($po, [
    [
        'product_id' => 1,
        'quantity' => 10,
        'unit_price' => '50000.00', // Must match product price
    ],
]);

// Submit for approval
$poService->submitPO($po, $user);
```

### Issuing Invoice

```php
use App\Services\InvoiceService;

$invoiceService = app(InvoiceService::class);

// Issue invoice from completed PO
$result = $invoiceService->issueInvoice(
    $po,
    $goodsReceipt,
    $user,
    '2026-05-20' // due_date
);

// Check if discrepancy detected
if ($result['discrepancy_result']['discrepancy_detected']) {
    // Invoice status = 'pending_approval'
    // Finance needs to approve
}
```

### Approving Discrepancy

```php
// Finance approves discrepancy
$invoice = $invoiceService->approveDiscrepancy(
    $customerInvoice,
    $financeUser,
    'Price increase approved by management'
);

// Or reject
$invoice = $invoiceService->rejectDiscrepancy(
    $customerInvoice,
    $financeUser,
    'Price variance too high, needs renegotiation'
);
```

### Processing Payment

```php
// Healthcare User confirms payment
$invoice = $invoiceService->confirmPayment($customerInvoice, $user, [
    'paid_amount' => '1000000.00',
    'payment_reference' => 'TRX-123456',
]);

// Finance verifies payment
$invoice = $invoiceService->verifyPayment($customerInvoice, $financeUser);
```

---

## 🧮 Invoice Calculations

### Calculate Line Item

```php
use App\Services\InvoiceCalculationService;

$calcService = app(InvoiceCalculationService::class);

$lineItem = $calcService->calculateLineItem(
    quantity: '10.000',
    unitPrice: '50000.00',
    discountPercentage: '10.00', // 10%
    discountAmount: null,
    taxRate: '11.00' // 11% PPN
);

// Result:
// [
//     'line_subtotal' => '500000.00',
//     'discount_amount' => '50000.00',
//     'taxable_amount' => '450000.00',
//     'tax_amount' => '49500.00',
//     'line_total' => '499500.00',
// ]
```

### Calculate Complete Invoice

```php
$invoice = $calcService->calculateCompleteInvoice([
    [
        'product_id' => 1,
        'product_name' => 'Paracetamol',
        'quantity' => '10.000',
        'unit_price' => '50000.00',
        'discount_percentage' => '10.00',
        'tax_rate' => '11.00',
    ],
    // ... more items
]);

// Result includes:
// - line_items: array of calculated line items
// - invoice_totals: subtotal, discount, tax, total
// - tolerance_check: verification result
```

---

## 🔍 Discrepancy Detection

### How It Works

```php
use App\Services\DiscrepancyDetectionService;

$discrepancyService = app(DiscrepancyDetectionService::class);

$result = $discrepancyService->detect(
    invoiceTotal: '1100000.00',
    purchaseOrder: $po
);

// Flags if:
// - Variance percentage > 1.00%
// - OR variance amount > Rp 10,000.00

// Result:
// [
//     'discrepancy_detected' => true/false,
//     'expected_total' => '1000000.00',
//     'variance_amount' => '100000.00',
//     'variance_percentage' => '10.00',
//     'requires_approval' => true/false,
// ]
```

---

## 💳 Credit Control

### Check Available Credit

```php
use App\Services\CreditControlService;

$creditService = app(CreditControlService::class);

// Get available credit
$available = $creditService->getAvailableCredit($organizationId);

// Check if sufficient
try {
    $creditService->checkCreditAvailable($organizationId, $requestedAmount);
    // OK to proceed
} catch (DomainException $e) {
    // Insufficient credit
    return back()->withErrors(['credit' => $e->getMessage()]);
}
```

### Credit Workflow

```php
// 1. Reserve on PO submit
$creditService->reserveCredit($po); // status: reserved

// 2. Bill on PO approve
$creditService->billCredit($po); // status: billed

// 3. Release on payment
$creditService->releaseCreditByAmount($orgId, $po, $amount); // status: released

// 4. Reverse on PO reject
$creditService->reverseCredit($po); // status: released, amount: 0
```

---

## 📝 Validation

### Common Validations

```php
use App\Services\ValidationService;

$validationService = app(ValidationService::class);

// Ensure PO has items
$validationService->ensurePOHasItems($po);

// Ensure supplier is valid
$supplier = $validationService->ensureSupplierIsValid($supplierId);

// Ensure product is valid
$product = $validationService->ensureProductIsValid($productId, $supplierId);

// Ensure valid state transition
$validationService->ensureValidTransition($po, 'approved');
```

---

## 🔔 Notifications

### Automatic Notifications

```php
// PO Submitted → Approvers + Super Admin
// PO Approved/Rejected → Creator + Healthcare Users
// Invoice Issued → Healthcare User
// Invoice Discrepancy → Finance
// Payment Confirmed → Finance
// Payment Verified → Healthcare User
```

### Manual Notification

```php
use App\Notifications\NewInvoiceNotification;

$user->notify(new NewInvoiceNotification($invoice));
```

---

## 📊 Audit Trail

### Automatic Logging

```php
// All critical operations are automatically logged:
// - PO: created, submitted, approved, rejected, shipped, delivered, completed
// - Invoice: issued, discrepancy detected, approved, rejected, paid
// - Payment: incoming, outgoing, verified
// - Credit: reserved, billed, released
// - Calculations: line items, totals, tolerance checks
// - Immutability: violation attempts
```

### Manual Logging

```php
use App\Services\AuditService;

$auditService = app(AuditService::class);

$auditService->log(
    action: 'custom.action',
    entityType: MyModel::class,
    entityId: $model->id,
    metadata: ['key' => 'value'],
    userId: auth()->id()
);
```

---

## 🧪 Testing

### Run All Tests

```bash
# All tests
php artisan test

# Pharmaceutical invoice tests
php artisan test tests/Feature/PharmaceuticalInvoice

# RBAC tests
php artisan test tests/Feature/RBACAccessControlTest.php

# Specific test
php artisan test --filter testHealthcareUserCanCreatePO
```

---

## 🚨 Common Errors & Solutions

### 1. "Cannot modify financial amounts after invoice issuance"
**Cause**: Trying to update immutable field  
**Solution**: Only update allowed fields (status, paid_amount, etc.)

### 2. "Insufficient credit limit"
**Cause**: Organization credit limit exceeded  
**Solution**: Finance needs to increase credit limit or wait for payment

### 3. "Line item totals do not match invoice total"
**Cause**: Tolerance check failed (difference > 0.01)  
**Solution**: Recalculate using BCMath, check for rounding errors

### 4. "Only draft POs can be edited"
**Cause**: Trying to edit PO after submission  
**Solution**: PO can only be edited in draft state

### 5. "Invoice was modified by another user"
**Cause**: Optimistic locking conflict  
**Solution**: Refresh page and retry

---

## 📚 Key Files Reference

### Services
- `app/Services/POService.php` - PO operations
- `app/Services/InvoiceService.php` - Invoice operations
- `app/Services/PaymentService.php` - Payment operations
- `app/Services/BCMathCalculatorService.php` - Calculations
- `app/Services/InvoiceCalculationService.php` - Invoice calculations
- `app/Services/DiscrepancyDetectionService.php` - Discrepancy detection
- `app/Services/ImmutabilityGuardService.php` - Immutability enforcement
- `app/Services/CreditControlService.php` - Credit control
- `app/Services/ValidationService.php` - Validation
- `app/Services/AuditService.php` - Audit logging

### Models
- `app/Models/PurchaseOrder.php` - PO model
- `app/Models/SupplierInvoice.php` - Supplier invoice (AP)
- `app/Models/CustomerInvoice.php` - Customer invoice (AR)
- `app/Models/Payment.php` - Payment model
- `app/Models/GoodsReceipt.php` - Goods receipt model

### Controllers
- `app/Http/Controllers/Web/PurchaseOrderWebController.php`
- `app/Http/Controllers/Web/InvoiceWebController.php`
- `app/Http/Controllers/Web/PaymentWebController.php`
- `app/Http/Controllers/Web/ApprovalWebController.php`

### Observers
- `app/Observers/SupplierInvoiceObserver.php`
- `app/Observers/CustomerInvoiceObserver.php`

### Documentation
- `BUSINESS_LOGIC_AUDIT_REPORT.md` - Complete audit
- `docs/USER_ROLE_ACCESS_GUIDE.md` - RBAC guide
- `USER_CREDENTIALS.md` - Test accounts
- `.kiro/specs/pharmaceutical-invoice-hardening/requirements.md` - Requirements

---

## 🎯 Best Practices

### 1. Always Use BCMath for Money
```php
// ✅ CORRECT
$total = $calculator->add($subtotal, $tax);

// ❌ WRONG
$total = $subtotal + $tax;
```

### 2. Use Service Layer
```php
// ✅ CORRECT
$poService->submitPO($po, $user);

// ❌ WRONG
$po->status = 'submitted';
$po->save();
```

### 3. Check Permissions
```php
// ✅ CORRECT
if ($user->can('create_invoices')) {
    $invoiceService->issueInvoice(...);
}

// ❌ WRONG
$invoiceService->issueInvoice(...); // No permission check
```

### 4. Use Transactions
```php
// ✅ CORRECT
DB::transaction(function () {
    // Multiple operations
});

// ❌ WRONG
// Multiple operations without transaction
```

### 5. Log Important Operations
```php
// ✅ CORRECT
$auditService->log('action', Model::class, $id, $metadata);

// Service layer already logs automatically
```

---

## 📞 Support

**Documentation**:
- `BUSINESS_LOGIC_AUDIT_REPORT.md` - Complete audit
- `docs/USER_ROLE_ACCESS_GUIDE.md` - RBAC guide
- `.kiro/specs/pharmaceutical-invoice-hardening/requirements.md` - Requirements

**Tests**:
- Run `php artisan test` to verify everything works
- 237 tests should pass

**Questions**:
- Check audit report for detailed analysis
- Review service layer code for implementation details
- Run tests to understand expected behavior

---

**Last Updated**: 13 April 2026  
**Status**: ✅ Production Ready  
**Test Coverage**: 237 tests passing
