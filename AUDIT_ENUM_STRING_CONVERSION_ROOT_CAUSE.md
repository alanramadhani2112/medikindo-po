# 🔍 AUDIT: ENUM STRING CONVERSION ROOT CAUSE

**Tanggal**: 21 April 2026  
**Error**: "Object of class App\Enums\SupplierInvoiceStatus could not be converted to string"  
**URL**: http://medikindo-po.test/invoices/supplier/8

---

## 🎯 ROOT CAUSE ANALYSIS

### Primary Issue Location
**File**: `app/Observers/SupplierInvoiceObserver.php`  
**Method**: `updating()`  
**Line**: 36-40

```php
\Log::info('SupplierInvoiceObserver::updating called', [
    'invoice_id' => $invoice->id,
    'status' => $statusValue,  // ✅ Already converted
    'dirty' => $invoice->getDirty(),  // ❌ PROBLEM HERE!
]);
```

### The Problem

When `$invoice->getDirty()` is called, it returns an array of changed attributes including:
```php
[
    'status' => SupplierInvoiceStatus::VERIFIED,  // Enum object
    'verified_at' => '2026-04-21 10:00:00',
    'verified_by' => 1,
]
```

When Laravel's Log facade tries to convert this array to JSON for logging, it encounters the enum object and tries to convert it to string, which fails.

### Why This Happens

1. **Model Casting**: `SupplierInvoice` model has `'status' => SupplierInvoiceStatus::class` in `$casts`
2. **getDirty() Returns Enum**: When status changes, `getDirty()` returns the enum object, not the string value
3. **Logging Attempts String Conversion**: `\Log::info()` tries to serialize the array to JSON
4. **Enum Cannot Be Stringified**: PHP 8.1+ enums don't have automatic string conversion

---

## 🔍 TRACE OF EXECUTION

### 1. User Action
User clicks "Verifikasi & Buat Tagihan RS" button on `/invoices/supplier/8`

### 2. Controller Action
`APVerificationController::verify()` is called:
```php
$invoice->update([
    'status' => \App\Enums\SupplierInvoiceStatus::VERIFIED,
    'verified_at' => now(),
    'verified_by' => $request->user()->id,
]);
```

### 3. Observer Triggered
`SupplierInvoiceObserver::updating()` is automatically called by Laravel

### 4. getDirty() Called
```php
$attemptedChanges = $invoice->getDirty();
// Returns: ['status' => SupplierInvoiceStatus::VERIFIED, 'verified_at' => ..., 'verified_by' => ...]
```

### 5. Logging Attempted
```php
\Log::info('...', [
    'dirty' => $attemptedChanges,  // Contains enum object
]);
```

### 6. JSON Serialization Fails
Laravel tries to convert array to JSON → encounters enum → tries to cast to string → **ERROR**

---

## 🐛 SECONDARY ISSUES FOUND

### Issue 1: ImmutabilityGuardService::logViolationAttempt()
**File**: `app/Services/ImmutabilityGuardService.php`  
**Line**: 280-290

```php
$this->auditService->log(
    action: 'invoice.immutability_violation',
    entityType: $invoiceType . '_invoice',
    entityId: $invoice->id,
    metadata: [
        'invoice_status' => $statusValue,  // ✅ Already sanitized
        'attempted_changes' => $sanitizedChanges,  // ✅ Already sanitized
        'violations' => $violations,  // ❌ May contain enums
    ],
);
```

### Issue 2: InvoiceModificationAttempt::create()
**File**: `app/Services/ImmutabilityGuardService.php`  
**Line**: 265-272

```php
InvoiceModificationAttempt::create([
    'attempted_changes' => $sanitizedChanges,  // ✅ Already sanitized
    'rejection_reason' => 'Immutability violation: ' . implode(', ', array_keys($violations)),  // ✅ OK
]);
```

---

## ✅ SOLUTION STRATEGY

### Fix 1: Sanitize getDirty() in Observer (PRIMARY FIX)
**File**: `app/Observers/SupplierInvoiceObserver.php`

```php
public function updating(SupplierInvoice $invoice): void
{
    // Get the changed attributes (dirty attributes)
    $attemptedChanges = $invoice->getDirty();

    // Skip if no changes
    if (empty($attemptedChanges)) {
        return;
    }

    // ✅ SANITIZE ENUMS BEFORE LOGGING
    $sanitizedChanges = $this->sanitizeEnums($attemptedChanges);

    \Log::info('SupplierInvoiceObserver::updating called', [
        'invoice_id' => $invoice->id,
        'status' => $invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->status,
        'dirty' => $sanitizedChanges,  // ✅ Now safe to log
    ]);

    // Resolve service from container
    $guard = $this->immutabilityGuard ?? app(ImmutabilityGuardService::class);

    // Check immutability and enforce rules
    $guard->enforce($invoice, $attemptedChanges);  // ✅ Guard already handles sanitization
}

private function sanitizeEnums(array $data): array
{
    $sanitized = [];
    foreach ($data as $key => $value) {
        if ($value instanceof \BackedEnum) {
            $sanitized[$key] = $value->value;
        } elseif ($value instanceof \UnitEnum) {
            $sanitized[$key] = $value->name;
        } elseif (is_array($value)) {
            $sanitized[$key] = $this->sanitizeEnums($value);
        } else {
            $sanitized[$key] = $value;
        }
    }
    return $sanitized;
}
```

### Fix 2: Apply Same Fix to CustomerInvoiceObserver
**File**: `app/Observers/CustomerInvoiceObserver.php`

Same sanitization logic needed.

---

## 📊 IMPACT ANALYSIS

### Affected Operations
1. ✅ **Invoice Verification** (AP → AR generation) - **PRIMARY IMPACT**
2. ✅ **Invoice Status Updates** (any status change)
3. ✅ **Invoice Modifications** (any field update)
4. ✅ **Payment Processing** (status changes to paid/partial_paid)

### Affected Models
1. ✅ `SupplierInvoice` - Has Observer
2. ✅ `CustomerInvoice` - Has Observer

### Not Affected
- ❌ View rendering (already fixed in previous commit)
- ❌ PDF generation (already fixed)
- ❌ Direct enum comparisons (already fixed)

---

## 🎯 FIX PRIORITY

### CRITICAL (Must Fix Now)
1. ✅ `SupplierInvoiceObserver::updating()` - Sanitize getDirty() before logging
2. ✅ `CustomerInvoiceObserver::updating()` - Same fix

### HIGH (Should Fix)
3. ✅ Add helper method `sanitizeEnums()` to both observers
4. ✅ Test invoice verification flow

### MEDIUM (Nice to Have)
5. ⚠️ Consider moving `sanitizeEnums()` to a trait for reusability
6. ⚠️ Add unit tests for observer enum handling

---

## 🧪 TESTING CHECKLIST

After fix, test these scenarios:

### Supplier Invoice
- [ ] Create supplier invoice from GR
- [ ] Verify supplier invoice (AP → AR generation) - **PRIMARY TEST**
- [ ] Update supplier invoice status
- [ ] Add payment to supplier invoice
- [ ] View supplier invoice detail page

### Customer Invoice
- [ ] Issue customer invoice (draft → issued)
- [ ] Add payment to customer invoice
- [ ] Update customer invoice status
- [ ] View customer invoice detail page

### Expected Results
- ✅ No "Object of class ... could not be converted to string" errors
- ✅ Logs show sanitized enum values (strings, not objects)
- ✅ Immutability guard works correctly
- ✅ All status transitions work smoothly

---

## 📝 PREVENTION MEASURES

### Code Review Checklist
1. ✅ Always sanitize enums before logging
2. ✅ Always sanitize enums before JSON serialization
3. ✅ Use `instanceof \BackedEnum` checks
4. ✅ Extract enum values with `->value` property
5. ✅ Test with actual enum objects, not strings

### Best Practices
```php
// ❌ BAD - Will fail
\Log::info('Status changed', ['status' => $invoice->status]);

// ✅ GOOD - Safe
\Log::info('Status changed', [
    'status' => $invoice->status instanceof \BackedEnum 
        ? $invoice->status->value 
        : $invoice->status
]);

// ✅ BETTER - Use helper
\Log::info('Status changed', [
    'status' => $this->sanitizeEnums(['status' => $invoice->status])
]);
```

---

## 🎉 CONCLUSION

**Root Cause**: Observer's `getDirty()` returns enum objects that cannot be serialized to JSON for logging.

**Solution**: Sanitize enum objects to their string values before logging or serialization.

**Impact**: Affects all invoice status updates and modifications.

**Priority**: CRITICAL - Blocks invoice verification feature.

---

**Audit Completed**: 21 April 2026  
**Next Action**: Implement fixes in both observers
