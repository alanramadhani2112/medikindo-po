# Invoice Goods Receipt Link Fix

## Problem
Error `UrlGenerationException` terjadi di halaman Supplier Invoice dan Customer Invoice karena route `web.goods-receipts.show` dipanggil dengan parameter `goods_receipt_id` yang bernilai `null`.

## Root Cause
- Invoice views mencoba generate link ke goods receipt tanpa cek apakah `goods_receipt_id` ada
- Ketika `goods_receipt_id = null`, Laravel tidak bisa generate URL karena parameter required tidak tersedia
- Error: `Missing required parameter for [Route: web.goods-receipts.show] [URI: goods-receipts/{goodsReceipt}] [Missing parameter: goodsReceipt]`

## Solution
Added conditional checks di kedua view untuk hanya menampilkan link jika `goods_receipt_id` ada.

### Files Modified:

#### 1. `resources/views/invoices/show_supplier.blade.php`
```php
// BEFORE
<a href="{{ route('web.goods-receipts.show', $invoice->goods_receipt_id) }}">

// AFTER  
@if($invoice->goods_receipt_id)
    <a href="{{ route('web.goods-receipts.show', $invoice->goods_receipt_id) }}">
@else
    <div class="...">Goods Receipt belum tersedia</div>
@endif
```

#### 2. `resources/views/invoices/show_customer.blade.php`
```php
// BEFORE
<a href="{{ route('web.goods-receipts.show', $invoice->goods_receipt_id) }}">

// AFTER
@if($invoice->goods_receipt_id)
    <a href="{{ route('web.goods-receipts.show', $invoice->goods_receipt_id) }}">
@else
    <div class="...">Goods Receipt belum tersedia</div>
@endif
```

## Result
- ✅ Invoice pages load without errors
- ✅ Shows "Goods Receipt belum tersedia" when no GR linked
- ✅ Shows clickable GR link when GR exists
- ✅ Maintains proper UI styling for both states

## Status: ✅ RESOLVED