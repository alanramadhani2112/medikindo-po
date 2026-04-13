# PO Edit Syntax Error Fix

## Problem
ParseError di halaman Purchase Order Edit: `Unclosed '[' on line 35 does not match ')'`

## Root Cause
View `resources/views/purchase-orders/edit.blade.php` menggunakan field `total_price` yang tidak ada di model `PurchaseOrderItem`.

**Database Schema:**
- ✅ `subtotal` - field yang benar
- ❌ `total_price` - field tidak ada

## Solution
Changed field reference dari `total_price` ke `subtotal`:

```php
// BEFORE (Line 37)
'subtotal' => (int)$i->total_price

// AFTER
'subtotal' => (int)$i->subtotal
```

## Files Modified
- `resources/views/purchase-orders/edit.blade.php` ✅

## Verification
- ✅ Database schema confirmed: `subtotal` field exists
- ✅ Model fillable includes `subtotal`
- ✅ No `total_price` field in database
- ✅ Syntax error resolved

## Status: ✅ RESOLVED