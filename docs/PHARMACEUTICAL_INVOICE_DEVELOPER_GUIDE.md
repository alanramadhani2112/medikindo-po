# Pharmaceutical-Grade Invoice Management - Developer Guide

## Overview

This guide provides comprehensive information for developers working with the pharmaceutical-grade invoice management system. It covers architecture, usage patterns, best practices, and common pitfalls.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Core Services](#core-services)
3. [Usage Patterns](#usage-patterns)
4. [Best Practices](#best-practices)
5. [Common Pitfalls](#common-pitfalls)
6. [Testing Strategies](#testing-strategies)
7. [API Reference](#api-reference)

---

## Architecture Overview

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                     Invoice Management System                │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   BCMath     │  │   Discount   │  │     Tax      │      │
│  │  Calculator  │  │  Validator   │  │  Calculator  │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                  │                  │              │
│         └──────────────────┼──────────────────┘              │
│                            │                                 │
│                   ┌────────▼────────┐                        │
│                   │    Invoice      │                        │
│                   │   Calculation   │                        │
│                   │    Service      │                        │
│                   └────────┬────────┘                        │
│                            │                                 │
│         ┌──────────────────┼──────────────────┐             │
│         │                  │                  │             │
│  ┌──────▼───────┐  ┌──────▼───────┐  ┌──────▼───────┐     │
│  │ Discrepancy  │  │ Immutability │  │    Audit     │     │
│  │  Detection   │  │    Guard     │  │   Service    │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Invoice Service (Orchestrator)          │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Supplier   │  │   Customer   │  │  Line Items  │      │
│  │   Invoice    │  │   Invoice    │  │    Models    │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

```
1. Issue Invoice Request
   ↓
2. InvoiceService::issueInvoice()
   ↓
3. Get Organization Defaults (tax rate, discount %)
   ↓
4. Prepare Line Items from Goods Receipt
   ↓
5. InvoiceCalculationService::calculateCompleteInvoice()
   ├─ For each line item:
   │  ├─ Calculate subtotal (quantity × unit_price)
   │  ├─ DiscountValidatorService::validate()
   │  ├─ TaxCalculatorService::calculate()
   │  └─ Calculate line total
   ├─ Sum all line items
   └─ Verify tolerance check (±0.01)
   ↓
6. DiscrepancyDetectionService::detect()
   ├─ Calculate expected total from PO
   ├─ Calculate variance (amount & percentage)
   └─ Flag if exceeds thresholds
   ↓
7. Create Invoice + Line Items
   ├─ Set status (issued or pending_approval)
   ├─ Store discrepancy details
   └─ Initialize version = 0
   ↓
8. AuditService::log() - Log all operations
   ↓
9. Return Invoice with Discrepancy Result
```

---

## Core Services

### 1. BCMathCalculatorService

**Purpose**: Pharmaceutical-grade precision arithmetic

**Key Methods**:
```php
$calculator = app(BCMathCalculatorService::class);

// Basic operations (all return strings)
$sum = $calculator->add('1000.00', '500.50');        // "1500.50"
$diff = $calculator->subtract('1000.00', '250.25');  // "749.75"
$product = $calculator->multiply('10.00', '3.14');   // "31.40"
$quotient = $calculator->divide('100.00', '3.00');   // "33.33"

// Rounding (HALF_UP / Banker's rounding)
$rounded = $calculator->round('2.505');              // "2.50"
$rounded = $calculator->round('3.515');              // "3.52"

// Comparison
$isEqual = $calculator->equals('100.00', '100.00');  // true
$isGreater = $calculator->greaterThan('100', '99');  // true

// Percentage
$percent = $calculator->percentage('1000', '10.5');  // "105.00"

// Sum array
$total = $calculator->sum(['100.00', '200.00', '300.00']); // "600.00"
```

**Important Rules**:
- ✅ Always pass strings, never floats
- ✅ Always use BCMath for monetary calculations
- ❌ Never use PHP arithmetic operators (+, -, *, /)
- ❌ Never use `number_format()` for calculations

**Example**:
```php
// ❌ WRONG - Float arithmetic loses precision
$total = 1000.10 + 500.20; // 1500.3000000000002

// ✅ CORRECT - BCMath preserves precision
$total = $calculator->add('1000.10', '500.20'); // "1500.30"
```

---

### 2. DiscountValidatorService

**Purpose**: Validate discount business rules

**Key Methods**:
```php
$validator = app(DiscountValidatorService::class);

// Validate discount percentage (0-100%)
$result = $validator->validatePercentage('10.50', '1000.00');
// Returns: ['is_valid' => true, 'discount_amount' => '105.00', ...]

// Validate discount amount (0 to subtotal)
$result = $validator->validateAmount('100.00', '1000.00');
// Returns: ['is_valid' => true, 'discount_percentage' => '10.00', ...]

// Validate discount (auto-detect type)
$result = $validator->validate([
    'discount_percentage' => '10.00',
    'subtotal' => '1000.00'
]);
```

**Business Rules**:
1. Percentage must be 0-100%
2. Amount must be 0 to subtotal
3. Cannot have both percentage AND amount
4. Bidirectional calculation (percentage ↔ amount)

---

### 3. TaxCalculatorService

**Purpose**: Calculate tax on taxable amounts

**Key Methods**:
```php
$taxCalc = app(TaxCalculatorService::class);

// Calculate tax on taxable amount
$result = $taxCalc->calculateTax('1000.00', '11.00');
// Returns: ['tax_amount' => '110.00', 'total_with_tax' => '1110.00', ...]

// Calculate tax on discounted amount
$result = $taxCalc->calculateTaxOnDiscountedAmount(
    subtotal: '1000.00',
    discountAmount: '100.00',
    taxRate: '11.00'
);
// Returns: ['taxable_amount' => '900.00', 'tax_amount' => '99.00', ...]

// Extract tax from tax-inclusive amount
$result = $taxCalc->extractTaxFromInclusive('1110.00', '11.00');
// Returns: ['tax_amount' => '110.00', 'amount_before_tax' => '1000.00', ...]
```

**Tax Calculation Formula**:
```
taxable_amount = subtotal - discount
tax_amount = taxable_amount × (tax_rate / 100)
tax_amount = round(tax_amount, HALF_UP)  // Banker's rounding
total = taxable_amount + tax_amount
```

---

### 4. InvoiceCalculationService

**Purpose**: Complete invoice calculation with line items

**Key Methods**:
```php
$invoiceCalc = app(InvoiceCalculationService::class);

// Calculate single line item
$lineItem = $invoiceCalc->calculateLineItem(
    quantity: '10.000',
    unitPrice: '100.00',
    discount: ['discount_percentage' => '10.00'],
    taxRate: '11.00'
);
// Returns: [
//     'line_subtotal' => '1000.00',
//     'discount_amount' => '100.00',
//     'taxable_amount' => '900.00',
//     'tax_amount' => '99.00',
//     'line_total' => '999.00'
// ]

// Calculate complete invoice
$invoice = $invoiceCalc->calculateCompleteInvoice([
    [
        'product_id' => 1,
        'product_name' => 'Product A',
        'quantity' => '10.000',
        'unit_price' => '100.00',
        'discount_percentage' => '10.00',
        'tax_rate' => '11.00'
    ],
    // ... more line items
]);
// Returns: [
//     'line_items' => [...],
//     'subtotal_amount' => '...',
//     'discount_amount' => '...',
//     'tax_amount' => '...',
//     'total_amount' => '...'
// ]
```

**Tolerance Check**:
```php
// Verify line totals sum to invoice total (±0.01)
$result = $invoiceCalc->verifyToleranceCheck(
    lineItemTotals: ['1000.00', '500.00'],
    invoiceTotal: '1500.00'
);
// Returns: ['passed' => true, 'difference' => '0.00', ...]
```

---

### 5. DiscrepancyDetectionService

**Purpose**: Detect variance between invoice and PO

**Key Methods**:
```php
$discrepancy = app(DiscrepancyDetectionService::class);

// Detect discrepancy
$result = $discrepancy->detect('1015.00', $purchaseOrder);
// Returns: [
//     'invoice_total' => '1015.00',
//     'expected_total' => '1000.00',
//     'variance_amount' => '15.00',
//     'variance_percentage' => '1.50',
//     'discrepancy_detected' => true,
//     'requires_approval' => true
// ]

// Get severity level
$severity = $discrepancy->getDiscrepancySeverity('15.00', '1.50');
// Returns: 'low' | 'medium' | 'high' | 'none'

// Format for display
$formatted = $discrepancy->formatForDisplay($result);
// Returns: [
//     'invoice_total' => 'Rp 1.015,00',
//     'variance_percentage' => '1.50%',
//     'severity' => 'low',
//     'message' => '...'
// ]
```

**Thresholds**:
- Percentage: > 1.00%
- Amount: > Rp 10,000.00
- Flag if EITHER threshold exceeded

---

### 6. ImmutabilityGuardService

**Purpose**: Enforce immutability rules

**Key Methods**:
```php
$guard = app(ImmutabilityGuardService::class);

// Check immutability
$result = $guard->checkImmutability($invoice, [
    'total_amount' => '2000.00'  // Attempted change
]);
// Returns: [
//     'is_valid' => false,
//     'violations' => ['total_amount' => [...]],
//     'message' => '...'
// ]

// Enforce (throws exception if violated)
try {
    $guard->enforce($invoice, ['total_amount' => '2000.00']);
} catch (ImmutabilityViolationException $e) {
    // Handle violation
    $violations = $e->getViolations();
}

// Check specific field
$isImmutable = $guard->isFieldImmutable('total_amount'); // true
$isMutable = $guard->isFieldMutable('status'); // true
```

**Immutable Fields**:
- `total_amount`, `subtotal_amount`, `discount_amount`, `tax_amount`
- `invoice_number`, `invoice_date`, `due_date`
- `purchase_order_id`, `goods_receipt_id`
- All line item fields (quantity, unit_price, etc.)

**Mutable Fields**:
- `status`, `paid_amount`, `payment_reference`, `payment_date`
- `verified_by`, `verified_at`, `notes`

---

## Usage Patterns

### Pattern 1: Issue Invoice with Line Items

```php
use App\Services\InvoiceService;

$invoiceService = app(InvoiceService::class);

$result = $invoiceService->issueInvoice(
    po: $purchaseOrder,
    gr: $goodsReceipt,
    actor: $user,
    dueDate: '2026-05-01'
);

// Result contains:
// - supplier_invoice: SupplierInvoice model
// - customer_invoice: CustomerInvoice model
// - discrepancy_result: Discrepancy detection result

if ($result['discrepancy_result']['discrepancy_detected']) {
    // Invoice is in 'pending_approval' status
    // Finance needs to approve/reject
    $variance = $result['discrepancy_result']['variance_percentage'];
    Log::info("Invoice flagged: {$variance}% variance");
}
```

### Pattern 2: Approve/Reject Discrepancy

```php
// Approve
$invoice = $invoiceService->approveDiscrepancy(
    invoice: $customerInvoice,
    actor: $user,
    approvalReason: 'Price increase approved by management'
);

// Reject
$invoice = $invoiceService->rejectDiscrepancy(
    invoice: $customerInvoice,
    actor: $user,
    rejectionReason: 'Variance too high, needs investigation'
);
```

### Pattern 3: Safe Invoice Updates

```php
// ✅ CORRECT - Only update mutable fields
$invoice->status = 'paid';
$invoice->paid_amount = '1000.00';
$invoice->payment_reference = 'PAY-001';
$invoice->save(); // Success

// ❌ WRONG - Attempting to modify immutable field
$invoice->total_amount = '2000.00';
$invoice->save(); // Throws ImmutabilityViolationException
```

### Pattern 4: Handle Concurrency

```php
use App\Exceptions\ConcurrencyException;

try {
    $invoice->status = 'paid';
    $invoice->save();
} catch (ConcurrencyException $e) {
    // Another user modified the invoice
    // Reload and retry
    $invoice = $invoice->fresh();
    $invoice->status = 'paid';
    $invoice->save();
}
```

### Pattern 5: Query Audit Trail

```php
$auditService = app(AuditService::class);

// Get all logs for invoice
$trail = $auditService->getInvoiceAuditTrail($invoiceId);

// Get calculation logs
$calculations = $auditService->getCalculationAuditTrail($invoiceId);

// Get flagged discrepancies
$discrepancies = $auditService->getDiscrepancyAuditTrail(flaggedOnly: true);

// Get immutability violations by user
$violations = $auditService->getImmutabilityViolations($userId);
```

---

## Best Practices

### 1. Always Use BCMath

```php
// ❌ WRONG
$total = $price * $quantity;
$discount = $total * 0.1;

// ✅ CORRECT
$calculator = app(BCMathCalculatorService::class);
$total = $calculator->multiply($price, $quantity);
$discount = $calculator->percentage($total, '10.00');
```

### 2. Store Values as Strings

```php
// ❌ WRONG
$lineItem = [
    'quantity' => 10,
    'unit_price' => 100.50,
];

// ✅ CORRECT
$lineItem = [
    'quantity' => '10.000',
    'unit_price' => '100.50',
];
```

### 3. Use Services, Not Direct Calculations

```php
// ❌ WRONG - Direct calculation
$tax = ($subtotal - $discount) * 0.11;

// ✅ CORRECT - Use service
$taxCalc = app(TaxCalculatorService::class);
$result = $taxCalc->calculateTaxOnDiscountedAmount(
    $subtotal, $discount, '11.00'
);
$tax = $result['tax_amount'];
```

### 4. Always Load Line Items

```php
// ❌ WRONG - N+1 query problem
$invoices = SupplierInvoice::all();
foreach ($invoices as $invoice) {
    $lineItems = $invoice->lineItems; // N+1 queries
}

// ✅ CORRECT - Eager loading
$invoices = SupplierInvoice::with('lineItems')->get();
```

### 5. Handle Exceptions Properly

```php
use App\Exceptions\ImmutabilityViolationException;
use App\Exceptions\ConcurrencyException;

try {
    $invoice->save();
} catch (ImmutabilityViolationException $e) {
    // Log violation
    Log::warning('Immutability violation', [
        'invoice_id' => $invoice->id,
        'violations' => $e->getViolations()
    ]);
    
    // Return user-friendly message
    return back()->with('error', $e->getMessage());
    
} catch (ConcurrencyException $e) {
    // Reload and retry
    $invoice = $invoice->fresh();
    // ... retry logic
}
```

---

## Common Pitfalls

### Pitfall 1: Float Arithmetic

```php
// ❌ WRONG - Precision loss
$total = 1000.10 + 500.20; // 1500.3000000000002

// ✅ CORRECT
$total = $calculator->add('1000.10', '500.20'); // "1500.30"
```

### Pitfall 2: Modifying Immutable Fields

```php
// ❌ WRONG - Will throw exception
$invoice->total_amount = '2000.00';
$invoice->save();

// ✅ CORRECT - Check if field is mutable first
$guard = app(ImmutabilityGuardService::class);
if ($guard->isFieldMutable('total_amount')) {
    $invoice->total_amount = '2000.00';
    $invoice->save();
}
```

### Pitfall 3: Ignoring Concurrency

```php
// ❌ WRONG - No concurrency handling
$invoice->status = 'paid';
$invoice->save(); // May fail silently

// ✅ CORRECT - Handle concurrency
try {
    $invoice->status = 'paid';
    $invoice->save();
} catch (ConcurrencyException $e) {
    // Handle conflict
}
```

### Pitfall 4: Not Using Transactions

```php
// ❌ WRONG - No transaction
$invoice->save();
foreach ($lineItems as $item) {
    $item->save(); // If this fails, invoice is already saved
}

// ✅ CORRECT - Use transaction
DB::transaction(function() use ($invoice, $lineItems) {
    $invoice->save();
    foreach ($lineItems as $item) {
        $item->save();
    }
});
```

### Pitfall 5: Forgetting Audit Logs

```php
// ❌ WRONG - No audit trail
$invoice->status = 'paid';
$invoice->save();

// ✅ CORRECT - Log the operation
$auditService = app(AuditService::class);
$auditService->log(
    action: 'invoice.status_changed',
    entityType: 'customer_invoice',
    entityId: $invoice->id,
    metadata: [
        'old_status' => $oldStatus,
        'new_status' => 'paid'
    ]
);
$invoice->status = 'paid';
$invoice->save();
```

---

## Testing Strategies

### Unit Testing

```php
use Tests\TestCase;
use App\Services\BCMathCalculatorService;

class BCMathCalculatorTest extends TestCase
{
    public function test_it_adds_correctly()
    {
        $calc = new BCMathCalculatorService();
        
        $result = $calc->add('1000.00', '500.50');
        
        $this->assertEquals('1500.50', $result);
    }
}
```

### Integration Testing

```php
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceIssuanceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_it_issues_invoice_with_line_items()
    {
        $po = PurchaseOrder::factory()->create();
        $gr = GoodsReceipt::factory()->create(['purchase_order_id' => $po->id]);
        
        $result = $this->invoiceService->issueInvoice($po, $gr, $user, '2026-05-01');
        
        $this->assertNotNull($result['customer_invoice']);
        $this->assertCount(2, $result['customer_invoice']->lineItems);
    }
}
```

### Property-Based Testing

```php
// Test: Addition is commutative (a + b = b + a)
public function test_addition_is_commutative()
{
    $calc = new BCMathCalculatorService();
    
    for ($i = 0; $i < 100; $i++) {
        $a = (string) rand(0, 999999) . '.' . rand(0, 99);
        $b = (string) rand(0, 999999) . '.' . rand(0, 99);
        
        $result1 = $calc->add($a, $b);
        $result2 = $calc->add($b, $a);
        
        $this->assertEquals($result1, $result2);
    }
}
```

---

## API Reference

See complete API documentation in:
- `docs/PHARMACEUTICAL_INVOICE_API.md`

---

## Troubleshooting

### Enable Debug Logging

```php
// In your service
Log::channel('invoice')->debug('Calculation', [
    'inputs' => $inputs,
    'output' => $output
]);
```

### Check Audit Trail

```php
// View all operations for invoice
$trail = AuditLog::where('entity_id', $invoiceId)
    ->where('entity_type', 'customer_invoice')
    ->orderBy('occurred_at', 'desc')
    ->get();
```

### Verify BCMath

```php
php -r "echo bcadd('1.1', '2.2', 2);" // Should output: 3.30
```

---

**Developer Guide Version**: 1.0  
**Last Updated**: April 13, 2026  
**For Questions**: Check audit logs, run tests, review code comments
