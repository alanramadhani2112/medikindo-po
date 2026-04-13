# Goods Receipt Validation Fix

## Problem
Validation error: `The items.0.quantity_received field is required` ketika submit goods receipt form.

## Root Cause
View menggunakan **Alpine.js CSP shorthand syntax** (`::name`, `::min`, `::max`) yang tidak didukung di **Alpine.js Standard Build**.

### Issue Details:
- System menggunakan Alpine.js Standard Build (bukan CSP build)
- View masih menggunakan CSP syntax: `::name`, `::min`, `::max`
- CSP shorthand tidak di-compile oleh standard build
- Attribute `name` tidak ter-render → field tidak terkirim ke server
- Server validation gagal karena field required tidak ada

## Solution
Changed dari CSP shorthand syntax ke standard Alpine.js syntax:

### Before (CSP Syntax - Not Working):
```html
<input ::name="`items[${index}][quantity_received]`" 
       ::min="1" 
       ::max="item.quantity" />

<select ::name="`items[${index}][condition]`"></select>

<input ::name="`items[${index}][notes]`" />
```

### After (Standard Syntax - Working):
```html
<input :name="'items[' + index + '][quantity_received]'" 
       :min="1" 
       :max="item.quantity" />

<select :name="'items[' + index + '][condition]'"></select>

<input :name="'items[' + index + '][notes]'" />
```

## Changes Made

### File: `resources/views/goods-receipts/create.blade.php`

1. **quantity_received input**:
   - Changed `::name` → `:name`
   - Changed template literals to string concatenation
   - Changed `::min` → `:min`
   - Changed `::max` → `:max`

2. **condition select**:
   - Changed `::name` → `:name`
   - Changed template literals to string concatenation

3. **notes input**:
   - Changed `::name` → `:name`
   - Changed template literals to string concatenation

## Business Logic Support

### Partial vs Full Receipt:
The form now properly supports:
- ✅ **Full Receipt**: User dapat terima semua barang (default: `quantity_received = quantity`)
- ✅ **Partial Receipt**: User dapat ubah `quantity_received` menjadi lebih kecil dari `quantity`
- ✅ **Validation**: `min="1"` dan `max="item.quantity"` enforce valid range
- ✅ **Condition Tracking**: Setiap item bisa punya kondisi berbeda (Good, Minor Damage, Damaged)
- ✅ **Notes**: Optional notes untuk setiap item

## Expected Behavior

### Form Submission:
```
POST /goods-receipts

Data:
{
  purchase_order_id: 1,
  items: [
    {
      purchase_order_item_id: 1,
      quantity_received: 10,  // ✅ Now properly sent
      condition: "Good",
      notes: "Dus penyok sedikit"
    }
  ]
}
```

### Validation:
- ✅ `quantity_received` is required
- ✅ `quantity_received` must be between 1 and ordered quantity
- ✅ `condition` defaults to "Good"
- ✅ `notes` is optional

## Testing

### Test Scenario 1: Full Receipt
1. Select PO with 26 items ordered
2. Keep default `quantity_received = 26`
3. Submit form
4. ✅ Should create GR with 26 items received

### Test Scenario 2: Partial Receipt
1. Select PO with 26 items ordered
2. Change `quantity_received` to 15
3. Submit form
4. ✅ Should create GR with 15 items received
5. ✅ PO status should reflect partial receipt

### Test Scenario 3: Multiple Items with Different Conditions
1. Select PO with multiple items
2. Set different `quantity_received` for each
3. Set different conditions (Good, Minor Damage, Damaged)
4. Add notes to some items
5. Submit form
6. ✅ Should create GR with all variations

## Status: ✅ RESOLVED

Form now properly submits all required fields and supports both full and partial goods receipt.