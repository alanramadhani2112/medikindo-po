# 🐛 ERROR FIX: Undefined Constant STATUS_PAID

## Error Details

**Error**: `Undefined constant App\Models\PurchaseOrder::STATUS_PAID`
**Type**: Fatal Error
**Location**: `app/Http/Controllers/Web/PurchaseOrderWebController.php:70`
**Impact**: Purchase Orders index page crashed

---

## Root Cause

The `PurchaseOrder` model does not have a `STATUS_PAID` constant. The correct status constants are:

```php
// Correct constants in PurchaseOrder model
public const STATUS_DRAFT     = 'draft';
public const STATUS_SUBMITTED = 'submitted';
public const STATUS_APPROVED  = 'approved';
public const STATUS_REJECTED  = 'rejected';
public const STATUS_SHIPPED   = 'shipped';
public const STATUS_DELIVERED = 'delivered';
public const STATUS_COMPLETED = 'completed';  // ✅ This is the correct one
```

**Note**: `STATUS_PAID` exists in `CustomerInvoice` and `SupplierInvoice` models, but NOT in `PurchaseOrder`.

---

## Files Fixed

### 1. Controller: `app/Http/Controllers/Web/PurchaseOrderWebController.php`

#### Fix 1: Line 37 (Status Map)
**Before**:
```php
'completed' => [PurchaseOrder::STATUS_DELIVERED, PurchaseOrder::STATUS_PAID],
```

**After**:
```php
'completed' => [PurchaseOrder::STATUS_DELIVERED, PurchaseOrder::STATUS_COMPLETED],
```

#### Fix 2: Line 70 (Counts Array)
**Before**:
```php
'completed' => (clone $countsQuery)->whereIn('status', [PurchaseOrder::STATUS_DELIVERED, PurchaseOrder::STATUS_PAID])->count(),
```

**After**:
```php
'completed' => (clone $countsQuery)->whereIn('status', [PurchaseOrder::STATUS_DELIVERED, PurchaseOrder::STATUS_COMPLETED])->count(),
```

---

### 2. View: `resources/views/purchase-orders/index.blade.php`

**Before**:
```php
$statusColor = match($order->status) {
    'draft' => 'secondary',
    'submitted' => 'warning',
    'approved' => 'success',
    'shipped' => 'primary',
    'delivered', 'paid' => 'success',  // ❌ Wrong
    'rejected', 'cancelled' => 'danger',
    default => 'primary'
};
```

**After**:
```php
$statusColor = match($order->status) {
    'draft' => 'secondary',
    'submitted' => 'warning',
    'approved' => 'success',
    'shipped' => 'primary',
    'delivered', 'completed' => 'success',  // ✅ Correct
    'rejected', 'cancelled' => 'danger',
    default => 'primary'
};
```

---

### 3. View: `resources/views/purchase-orders/show.blade.php`

**Before**:
```php
$badgeClass = match($po->status) {
    'draft' => 'badge-light-secondary',
    'submitted' => 'badge-light-warning',
    'approved' => 'badge-light-primary',
    'shipped' => 'badge-light-primary',
    'delivered', 'paid' => 'badge-light-success',  // ❌ Wrong
    'rejected', 'cancelled' => 'badge-light-danger',
    default => 'badge-light-secondary'
};
```

**After**:
```php
$badgeClass = match($po->status) {
    'draft' => 'badge-light-secondary',
    'submitted' => 'badge-light-warning',
    'approved' => 'badge-light-primary',
    'shipped' => 'badge-light-primary',
    'delivered', 'completed' => 'badge-light-success',  // ✅ Correct
    'rejected', 'cancelled' => 'badge-light-danger',
    default => 'badge-light-secondary'
};
```

---

### 4. View: `resources/views/approvals/index.blade.php` (2 occurrences)

**Before** (Line 128 and 250):
```php
$statusColor = match($po->status) {
    'draft' => 'secondary',
    'submitted' => 'warning',
    'approved' => 'success',
    'shipped' => 'primary',
    'delivered', 'paid' => 'success',  // ❌ Wrong
    'rejected', 'cancelled' => 'danger',
    default => 'primary'
};
```

**After**:
```php
$statusColor = match($po->status) {
    'draft' => 'secondary',
    'submitted' => 'warning',
    'approved' => 'success',
    'shipped' => 'primary',
    'delivered', 'completed' => 'success',  // ✅ Correct
    'rejected', 'cancelled' => 'danger',
    default => 'primary'
};
```

---

## Summary of Changes

| File | Lines Changed | Type |
|------|---------------|------|
| `PurchaseOrderWebController.php` | 2 | Controller |
| `purchase-orders/index.blade.php` | 1 | View |
| `purchase-orders/show.blade.php` | 1 | View |
| `approvals/index.blade.php` | 2 | View |
| **Total** | **6** | **Mixed** |

---

## Status Constants Reference

### PurchaseOrder Model
```php
STATUS_DRAFT      = 'draft'
STATUS_SUBMITTED  = 'submitted'
STATUS_APPROVED   = 'approved'
STATUS_REJECTED   = 'rejected'
STATUS_SHIPPED    = 'shipped'
STATUS_DELIVERED  = 'delivered'
STATUS_COMPLETED  = 'completed'  // ✅ Use this, not STATUS_PAID
```

### CustomerInvoice & SupplierInvoice Models
```php
STATUS_ISSUED             = 'issued'
STATUS_PAYMENT_SUBMITTED  = 'payment_submitted'
STATUS_PAID               = 'paid'  // ✅ Only exists here
STATUS_OVERDUE            = 'overdue'
```

---

## Testing Checklist

- [x] Purchase Orders index page loads without error
- [x] All tabs work (All, Draft, Submitted, Approved, Rejected, Completed)
- [x] Status badges display correctly
- [x] Completed tab shows delivered and completed orders
- [x] Purchase Order show page displays correct status
- [x] Approvals page displays correct status badges
- [x] No console errors
- [x] No undefined constant errors

---

## Prevention

To prevent similar issues in the future:

1. **Always check model constants** before using them
2. **Use IDE autocomplete** to verify constant existence
3. **Run tests** after making status-related changes
4. **Document status workflows** clearly
5. **Use consistent naming** across models

---

## Related Models

If you need to work with payment status:
- Use `CustomerInvoice::STATUS_PAID` for customer invoices
- Use `SupplierInvoice::STATUS_PAID` for supplier invoices
- Use `PurchaseOrder::STATUS_COMPLETED` for purchase orders

---

**Status**: ✅ FIXED
**Date**: 2024-04-13
**Severity**: HIGH (Caused page crash)
**Resolution Time**: 5 minutes