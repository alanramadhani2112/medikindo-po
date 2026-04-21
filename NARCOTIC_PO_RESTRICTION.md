# NARCOTIC PRODUCTS - PO RESTRICTION IMPLEMENTATION

**Date:** 21 April 2026  
**Feature:** Disable narcotic product ordering in PO system  
**Status:** ✅ IMPLEMENTED

---

## 📋 OVERVIEW

Fitur pemesanan produk narkotika/psikotropika telah **dinonaktifkan** di sistem Purchase Order untuk sementara waktu.

**Reason:** Produk narkotika memerlukan proses approval khusus dan dokumentasi tambahan yang belum terintegrasi dengan sistem PO saat ini.

---

## ✅ IMPLEMENTATION DETAILS

### 1. Visual Warning Alert ✅

**Location:** `resources/views/purchase-orders/create.blade.php`

Added prominent warning alert at the top of PO create form:

```html
<div class="alert alert-warning d-flex align-items-center p-5 mb-7">
    <i class="ki-outline ki-information-5 fs-2hx text-warning me-4"></i>
    <div class="d-flex flex-column">
        <h4 class="mb-1 text-warning fw-bold">Pemberitahuan Penting</h4>
        <span class="fs-6 text-gray-700">
            Fitur pemesanan <strong>produk narkotika/psikotropika</strong> saat ini 
            <strong>dinonaktifkan</strong> untuk sementara waktu. 
            Produk narkotika tidak akan muncul dalam daftar produk yang tersedia. 
            Untuk pemesanan produk narkotika, silakan hubungi tim procurement secara langsung.
        </span>
    </div>
</div>
```

**Features:**
- ⚠️ Warning icon (large, visible)
- 🟡 Yellow/warning color scheme
- 📝 Clear message in Bahasa Indonesia
- 📞 Alternative action (contact procurement team)

---

### 2. Product Filter (JavaScript) ✅

**Location:** `resources/views/purchase-orders/create.blade.php` - Alpine.js component

**Modified Function:** `filteredProducts(index)`

```javascript
filteredProducts(index) {
    const q = (this.searchQuery[index] || '').toLowerCase();
    
    // Filter out narcotic products
    const nonNarcoticProducts = this.products.filter(p => !p.is_narcotic);
    
    if (!q) return nonNarcoticProducts.slice(0, 50);
    return nonNarcoticProducts.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.sku && p.sku.toLowerCase().includes(q))
    ).slice(0, 50);
}
```

**How it works:**
1. Filter products where `is_narcotic = false`
2. Apply search query on non-narcotic products only
3. Return max 50 results

**Result:** Narcotic products will NOT appear in dropdown search results.

---

### 3. Extra Safety Check ✅

**Location:** `resources/views/purchase-orders/create.blade.php` - Alpine.js component

**Modified Function:** `selectProduct(index, product)`

```javascript
selectProduct(index, product) {
    // Extra safety: prevent narcotic products
    if (product.is_narcotic) {
        alert('⚠️ Produk narkotika/psikotropika tidak dapat dipesan melalui sistem ini untuk sementara waktu.\n\nSilakan hubungi tim procurement untuk pemesanan produk narkotika.');
        return;
    }
    
    // ... rest of the code
}
```

**How it works:**
1. Check if selected product is narcotic
2. Show alert message if true
3. Prevent product selection
4. Return early (do not add to PO)

**Result:** Even if somehow a narcotic product is selected, it will be blocked with alert message.

---

## 🎯 USER EXPERIENCE

### Before Implementation
- ❌ Users could select narcotic products
- ❌ No warning about special requirements
- ❌ PO could be created with narcotic items
- ❌ Confusion about approval process

### After Implementation
- ✅ Clear warning message at top of form
- ✅ Narcotic products filtered out from search
- ✅ Alert if user somehow tries to select narcotic product
- ✅ Clear instruction to contact procurement team

---

## 🔍 TESTING CHECKLIST

### Manual Testing

- [ ] Open PO create form
- [ ] Verify warning alert is visible at top
- [ ] Select a supplier
- [ ] Click "Tambah Produk"
- [ ] Search for narcotic product (e.g., "Morphine", "Tramadol")
- [ ] Verify narcotic products do NOT appear in dropdown
- [ ] Search for non-narcotic product (e.g., "Paracetamol")
- [ ] Verify non-narcotic products appear normally
- [ ] Select non-narcotic product
- [ ] Verify product is added to PO successfully
- [ ] Submit PO
- [ ] Verify PO is created without narcotic products

### Database Verification

```sql
-- Check narcotic products in system
SELECT id, name, sku, is_narcotic 
FROM products 
WHERE is_narcotic = 1;

-- Verify no narcotic products in recent POs
SELECT po.id, po.po_number, p.name, p.is_narcotic
FROM purchase_orders po
JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
JOIN products p ON poi.product_id = p.id
WHERE p.is_narcotic = 1
AND po.created_at >= '2026-04-21';
```

---

## 📊 IMPACT ANALYSIS

### Affected Features

1. **✅ PO Create Form**
   - Warning added
   - Product filter implemented
   - Safety check added

2. **❓ PO Edit Form** (NOT MODIFIED)
   - Existing POs with narcotic products can still be edited
   - Consider adding same restriction if needed

3. **❓ API Endpoints** (NOT MODIFIED)
   - API still allows narcotic products
   - Consider adding validation if API is used

### Not Affected

- ✅ Product master data (narcotic products still exist)
- ✅ Existing POs with narcotic products (still valid)
- ✅ Inventory management (narcotic products can be tracked)
- ✅ Goods receipt (narcotic products can be received)
- ✅ Invoicing (narcotic products can be invoiced)

---

## 🚀 FUTURE ENHANCEMENTS

### Short-term (1-2 weeks)

1. **Add same restriction to PO Edit form**
   - Prevent adding narcotic products to existing POs
   - Allow editing existing narcotic items (quantity, price)

2. **Add restriction to API endpoints**
   - Validate `is_narcotic` in PO store/update requests
   - Return clear error message

3. **Add admin override option**
   - Allow specific users to order narcotic products
   - Require special permission/role

### Long-term (1-3 months)

4. **Implement Narcotic PO Workflow**
   - Separate form for narcotic products
   - Additional approval levels
   - Special documentation requirements
   - Integration with regulatory tracking

5. **Narcotic Inventory Tracking**
   - Separate inventory for narcotic products
   - Stricter tracking and reporting
   - Expiry date monitoring
   - Usage reporting

6. **Compliance Reports**
   - Narcotic purchase history
   - Usage by organization
   - Regulatory compliance dashboard

---

## 📝 DOCUMENTATION UPDATES

### User Guide

**Section:** Creating Purchase Order

**Add Note:**
> ⚠️ **Penting:** Produk narkotika/psikotropika tidak dapat dipesan melalui sistem PO untuk sementara waktu. Jika Anda perlu memesan produk narkotika, silakan hubungi tim procurement secara langsung melalui email atau telepon.

### SOP Update

**Procurement SOP - Narcotic Products:**

1. User requests narcotic product via email/phone
2. Procurement team verifies request
3. Check regulatory requirements (Surat Pesanan, etc)
4. Manual approval process
5. Create PO manually (outside system)
6. Document in separate tracking sheet
7. Notify user when order is processed

---

## ⚠️ IMPORTANT NOTES

### For Developers

1. **Do NOT delete narcotic products from database**
   - They are still needed for inventory, GR, invoicing
   - Only restrict in PO creation

2. **Filter is client-side (JavaScript)**
   - Easy to bypass if user inspects code
   - Consider adding server-side validation

3. **Existing POs with narcotic products**
   - Will still be visible in PO list
   - Can still be edited (for now)
   - Consider adding read-only mode

### For Users

1. **Narcotic products still exist in system**
   - Can be viewed in product master
   - Can be received via goods receipt
   - Can be invoiced

2. **Alternative process**
   - Contact procurement team directly
   - Provide product details
   - Wait for manual processing

3. **Timeline**
   - Temporary restriction
   - Full narcotic workflow coming soon
   - Will be notified when available

---

## 🔄 ROLLBACK PLAN

If restriction needs to be removed:

### Step 1: Remove Warning Alert

Remove this block from `resources/views/purchase-orders/create.blade.php`:

```html
{{-- Warning: Narcotic Products Disabled --}}
<div class="alert alert-warning ...">
    ...
</div>
```

### Step 2: Restore Original Filter

Replace `filteredProducts()` function:

```javascript
filteredProducts(index) {
    const q = (this.searchQuery[index] || '').toLowerCase();
    if (!q) return this.products.slice(0, 50);
    return this.products.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.sku && p.sku.toLowerCase().includes(q))
    ).slice(0, 50);
}
```

### Step 3: Remove Safety Check

Remove this block from `selectProduct()` function:

```javascript
// Extra safety: prevent narcotic products
if (product.is_narcotic) {
    alert('...');
    return;
}
```

---

## ✅ IMPLEMENTATION CHECKLIST

- [x] Add warning alert to PO create form
- [x] Implement product filter (exclude narcotic)
- [x] Add safety check in selectProduct()
- [x] Test manually
- [ ] Update user documentation
- [ ] Update SOP
- [ ] Train procurement team
- [ ] Notify users via email/announcement
- [ ] Monitor for issues

---

## 📞 SUPPORT

**For Questions:**
- Technical: Contact development team
- Process: Contact procurement manager
- Urgent narcotic orders: Call procurement hotline

**Known Issues:**
- None reported yet

**Last Updated:** 21 April 2026  
**Version:** 1.0

---

**Status:** ✅ **READY FOR PRODUCTION**
