# ANALISIS DAMPAK PERUBAHAN EXPIRY & BATCH SYSTEM
## Medikindo B2B Healthcare System

**Tanggal**: 22 April 2026  
**Scope**: Impact Analysis untuk 3 Critical Fixes + Strengthening

---

## EXECUTIVE SUMMARY

### ⚠️ **DAMPAK: MEDIUM-HIGH**

Perubahan expiry & batch system akan mempengaruhi **8 fitur utama** dan **15+ file**. Namun, karena struktur sudah benar, dampaknya bersifat **ENHANCEMENT** bukan **BREAKING CHANGE**.

**Good News**: 
- ✅ Tidak ada breaking change pada data structure
- ✅ Tidak perlu migration besar-besaran
- ✅ Backward compatible dengan data existing
- ✅ Perubahan bersifat additive (tambah validasi & logic)

**Perlu Perhatian**:
- ⚠️ Testing harus menyeluruh (8 fitur affected)
- ⚠️ User training untuk near-expiry warning
- ⚠️ Monitoring perlu ditingkatkan

---

## FITUR YANG TERPENGARUH

### 1. **GOODS RECEIPT (GR)** - 🔴 HIGH IMPACT

**File Affected**:
- `app/Http/Requests/StoreGoodsReceiptRequest.php`
- `app/Services/GoodsReceiptService.php`
- `resources/views/goods-receipts/create.blade.php`
- `resources/views/goods-receipts/show.blade.php`

**Perubahan**:

#### A. Validasi Expiry > Today
```php
// BEFORE
'items.*.expiry_date' => 'required|date',

// AFTER
'items.*.expiry_date' => [
    'required',
    'date',
    'after:today',  // ← NEW
],
```

**Dampak**:
- ✅ **Positif**: Prevent expired goods entry
- ⚠️ **User Impact**: User tidak bisa input barang expired (ini yang diinginkan)
- ⚠️ **Edge Case**: Jika ada kebutuhan terima barang expired untuk disposal, perlu approval khusus

#### B. Near-Expiry Warning
```php
// NEW LOGIC in GoodsReceiptService
foreach ($items as $item) {
    $expiryDate = Carbon::parse($item['expiry_date']);
    $daysUntilExpiry = $expiryDate->diffInDays(now());
    
    if ($daysUntilExpiry <= 90) {
        // Log warning
        Log::warning("Near-expiry goods received", [
            'batch_no' => $item['batch_no'],
            'expiry_date' => $expiryDate,
            'days_until_expiry' => $daysUntilExpiry,
        ]);
        
        // Send notification
        event(new NearExpiryGoodsReceived($item));
        
        // Optional: Require approval
        if ($daysUntilExpiry <= 30) {
            // Require manager approval
        }
    }
}
```

**Dampak**:
- ✅ **Positif**: Proactive alert untuk barang near expiry
- ⚠️ **User Impact**: User akan dapat notification (perlu training)
- ⚠️ **Process Change**: Mungkin perlu approval workflow untuk near-expiry items

#### C. UI Changes
```blade
{{-- NEW: Warning badge for near-expiry --}}
@if($item->expiry_date && $item->expiry_date->diffInDays(now()) <= 90)
    <span class="badge badge-warning">
        <i class="ki-outline ki-warning fs-7"></i>
        Expiry dalam {{ $item->expiry_date->diffInDays(now()) }} hari
    </span>
@endif
```

**Dampak**:
- ✅ **Positif**: Better visibility
- ⚠️ **User Impact**: UI lebih informatif, perlu training

---

### 2. **INVENTORY MANAGEMENT** - 🔴 HIGH IMPACT

**File Affected**:
- `app/Services/InventoryService.php`
- `app/Models/InventoryItem.php`
- `app/Http/Controllers/Web/InventoryWebController.php`
- `resources/views/inventory/*.blade.php`

**Perubahan**:

#### A. FEFO Implementation (CRITICAL)
```php
// BEFORE - reduceStock()
$inventoryItems = InventoryItem::where('organization_id', $organizationId)
    ->where('product_id', $productId)
    ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
    ->orderBy('created_at', 'asc')  // ❌ FIFO
    ->get();

// AFTER
$inventoryItems = InventoryItem::where('organization_id', $organizationId)
    ->where('product_id', $productId)
    ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
    ->whereDate('expiry_date', '>=', now())  // ← NEW: Exclude expired
    ->orderBy('expiry_date', 'asc')          // ← CHANGED: FEFO
    ->orderBy('created_at', 'asc')           // ← KEEP: Tie-breaker
    ->get();
```

**Dampak**:
- ✅ **Positif**: Compliance dengan healthcare standard
- ✅ **Positif**: Reduce waste dari expired stock
- ⚠️ **Behavior Change**: Batch yang keluar akan berbeda dari sebelumnya
- ⚠️ **Testing**: Perlu test menyeluruh untuk ensure correct allocation

**Contoh Skenario**:

**BEFORE (FIFO)**:
```
Product A:
- Batch 1: Received 2026-01-01, Expiry 2027-12-31, Qty: 100
- Batch 2: Received 2026-02-01, Expiry 2026-06-30, Qty: 50

Sale 30 units → Ambil dari Batch 1 (oldest received)
Result: Batch 2 (near expiry) menumpuk ❌
```

**AFTER (FEFO)**:
```
Product A:
- Batch 1: Received 2026-01-01, Expiry 2027-12-31, Qty: 100
- Batch 2: Received 2026-02-01, Expiry 2026-06-30, Qty: 50

Sale 30 units → Ambil dari Batch 2 (earliest expiry)
Result: Batch 2 keluar duluan, minimize waste ✅
```

#### B. Expired Item Exclusion
```php
// NEW: Automatic exclusion of expired items
->whereDate('expiry_date', '>=', now())
```

**Dampak**:
- ✅ **Positif**: Tidak bisa jual barang expired
- ⚠️ **Stock Availability**: Available stock bisa berkurang jika ada expired items
- ⚠️ **Reporting**: Perlu separate report untuk expired stock

---

### 3. **CUSTOMER INVOICE (AR)** - 🔴 HIGH IMPACT

**File Affected**:
- `app/Services/InvoiceFromGRService.php`
- `app/Http/Controllers/Web/CustomerInvoiceWebController.php`
- `resources/views/invoices/customer/*.blade.php`

**Perubahan**:

#### A. Expired Item Blocking
```php
// NEW VALIDATION in createCustomerInvoiceFromGR()
foreach ($lineItems as $item) {
    $batch = InventoryItem::find($item['inventory_item_id']);
    
    if ($batch->isExpired()) {
        throw new \DomainException(
            "Cannot invoice expired item: {$batch->product->name} " .
            "(Batch: {$batch->batch_no}, Expired: {$batch->expiry_date->format('d M Y')})"
        );
    }
    
    // NEW: Warning for near-expiry
    if ($batch->isExpiringSoon()) {
        Log::warning("Invoicing near-expiry item", [
            'product' => $batch->product->name,
            'batch' => $batch->batch_no,
            'expiry' => $batch->expiry_date,
            'days_until_expiry' => $batch->expiry_date->diffInDays(now()),
        ]);
    }
}
```

**Dampak**:
- ✅ **Positif**: Compliance - tidak bisa jual barang expired
- ⚠️ **User Impact**: Invoice creation bisa gagal jika ada expired items
- ⚠️ **Process**: User perlu clear expired stock dulu sebelum invoice

#### B. Batch Selection Logic
```php
// Karena FEFO, batch yang dipilih otomatis adalah yang expiry paling dekat
// User tidak perlu manual pilih batch
```

**Dampak**:
- ✅ **Positif**: Automatic optimal batch selection
- ⚠️ **User Impact**: User tidak bisa override batch selection (by design)

---

### 4. **SUPPLIER INVOICE (AP)** - 🟡 MEDIUM IMPACT

**File Affected**:
- `app/Services/InvoiceFromGRService.php`
- `resources/views/invoices/show_supplier.blade.php`

**Perubahan**:

#### A. Batch & Expiry Display
```php
// Already READ-ONLY from GR, no change needed
// But add visual indicators for expiry status
```

**Dampak**:
- ✅ **Positif**: Better visibility of expiry status
- ⚠️ **UI Change**: Tambah badge untuk expiry status

---

### 5. **INVENTORY REPORTS** - 🟡 MEDIUM IMPACT

**File Affected**:
- `app/Http/Controllers/Web/InventoryWebController.php`
- `resources/views/inventory/expiring.blade.php`
- `resources/views/inventory/low-stock.blade.php`

**Perubahan**:

#### A. Expiring Items Report Enhancement
```php
// BEFORE
$expiringItems = $this->inventoryService->getExpiringItems($organizationId, 60);

// AFTER - Add status categorization
$expiringItems = $this->inventoryService->getExpiringItems($organizationId, 60)
    ->map(function($item) {
        $item->expiry_status = ExpiryStatusPolicy::getStatus($item->expiry_date);
        $item->expiry_color = ExpiryStatusPolicy::getColor($item->expiry_status);
        return $item;
    });
```

**Dampak**:
- ✅ **Positif**: Better categorization (critical/warning/caution)
- ⚠️ **UI Change**: Perlu update view untuk show status

#### B. New Report: Expired Stock
```php
// NEW REPORT
public function expiredStock(Request $request)
{
    $expiredItems = $this->inventoryService->getExpiredItems($organizationId);
    return view('inventory.expired', compact('expiredItems'));
}
```

**Dampak**:
- ✅ **Positif**: Visibility untuk expired stock
- ⚠️ **New Feature**: Perlu create view baru

---

### 6. **DASHBOARD & ALERTS** - 🟡 MEDIUM IMPACT

**File Affected**:
- `app/Http/Controllers/Web/DashboardController.php`
- `resources/views/dashboard/*.blade.php`

**Perubahan**:

#### A. Dashboard Widget: Expiring Items
```php
// NEW WIDGET
$expiringCount = InventoryItem::where('organization_id', $orgId)
    ->whereNotNull('expiry_date')
    ->whereDate('expiry_date', '<=', now()->addDays(30))
    ->whereDate('expiry_date', '>=', now())
    ->count();

$expiredCount = InventoryItem::where('organization_id', $orgId)
    ->expired()
    ->count();
```

**Dampak**:
- ✅ **Positif**: Proactive monitoring
- ⚠️ **UI Change**: Perlu tambah widget di dashboard

#### B. Alert System (NEW)
```php
// Daily cron job
Schedule::daily()->at('08:00')->call(function () {
    $organizations = Organization::all();
    
    foreach ($organizations as $org) {
        $expiringItems = InventoryItem::where('organization_id', $org->id)
            ->expiringSoon()
            ->get();
        
        if ($expiringItems->isNotEmpty()) {
            // Send email to procurement team
            Mail::to($org->procurement_email)
                ->send(new ExpiringItemsAlert($expiringItems));
        }
    }
});
```

**Dampak**:
- ✅ **Positif**: Proactive notification
- ⚠️ **New Feature**: Perlu setup email template & cron

---

### 7. **AUDIT & COMPLIANCE** - 🟢 LOW IMPACT

**File Affected**:
- `app/Services/AuditService.php`
- `app/Models/AuditLog.php`

**Perubahan**:

#### A. Enhanced Audit Logging
```php
// Log expiry-related events
$this->auditService->log(
    action: 'inventory.near_expiry_received',
    entityType: GoodsReceiptItem::class,
    entityId: $grItem->id,
    metadata: [
        'batch_no' => $grItem->batch_no,
        'expiry_date' => $grItem->expiry_date,
        'days_until_expiry' => $grItem->expiry_date->diffInDays(now()),
        'product_name' => $grItem->product->name,
    ],
    userId: $actor->id,
);
```

**Dampak**:
- ✅ **Positif**: Better audit trail
- ⚠️ **Storage**: Audit log akan bertambah

---

### 8. **TESTING & PROPERTY-BASED TESTS** - 🟡 MEDIUM IMPACT

**File Affected**:
- `tests/Unit/Properties/MirrorBatchProperty4Test.php`
- `tests/Feature/InvoiceDataMigrationTest.php`

**Perubahan**:

#### A. Update Existing Tests
```php
// BEFORE
$expiryDate = Carbon::today()->addDays(mt_rand(30, 730));

// AFTER - Ensure expiry > today
$expiryDate = Carbon::today()->addDays(mt_rand(1, 730));  // Min 1 day
```

**Dampak**:
- ✅ **Positif**: Tests akan lebih realistic
- ⚠️ **Test Update**: Perlu update semua tests yang generate expiry date

#### B. New Tests Required
```php
// Test FEFO logic
test('inventory reduces stock using FEFO', function() {
    // Create 2 batches with different expiry
    // Reduce stock
    // Assert: batch with earlier expiry is used first
});

// Test expired item blocking
test('cannot create invoice with expired items', function() {
    // Create expired batch
    // Try to create invoice
    // Assert: throws DomainException
});

// Test near-expiry warning
test('logs warning when receiving near-expiry goods', function() {
    // Receive goods with expiry < 90 days
    // Assert: warning logged
    // Assert: notification sent
});
```

**Dampak**:
- ✅ **Positif**: Better test coverage
- ⚠️ **Effort**: Perlu write new tests

---

## SUMMARY DAMPAK PER FITUR

| Fitur | Impact Level | Breaking Change? | User Training? | Testing Effort |
|-------|-------------|------------------|----------------|----------------|
| **Goods Receipt** | 🔴 HIGH | ❌ No | ✅ Yes | HIGH |
| **Inventory Management** | 🔴 HIGH | ❌ No | ✅ Yes | HIGH |
| **Customer Invoice** | 🔴 HIGH | ❌ No | ✅ Yes | HIGH |
| **Supplier Invoice** | 🟡 MEDIUM | ❌ No | ⚠️ Minimal | MEDIUM |
| **Inventory Reports** | 🟡 MEDIUM | ❌ No | ⚠️ Minimal | MEDIUM |
| **Dashboard & Alerts** | 🟡 MEDIUM | ❌ No | ✅ Yes | MEDIUM |
| **Audit & Compliance** | 🟢 LOW | ❌ No | ❌ No | LOW |
| **Testing** | 🟡 MEDIUM | ❌ No | ❌ No | HIGH |

---

## BACKWARD COMPATIBILITY

### ✅ **FULLY BACKWARD COMPATIBLE**

1. **Data Structure**: Tidak ada perubahan schema
2. **API**: Tidak ada breaking change di API
3. **Existing Data**: Tetap valid dan bisa diproses

### ⚠️ **BEHAVIOR CHANGES**

1. **FIFO → FEFO**: Batch allocation berbeda
2. **Expiry Validation**: Tidak bisa terima barang expired
3. **Stock Availability**: Expired items excluded dari available stock

---

## MIGRATION STRATEGY

### **Phase 1: Critical Fixes (Week 1)**

**Day 1-2: Expiry Validation**
- Update `StoreGoodsReceiptRequest.php`
- Add unit tests
- Deploy to staging
- User acceptance testing

**Day 3-4: FEFO Implementation**
- Update `InventoryService.php`
- Add integration tests
- Test with real data
- Deploy to staging

**Day 5: Expired Item Blocking**
- Update invoice services
- Add validation tests
- Deploy to staging
- End-to-end testing

**Weekend: Production Deployment**
- Deploy to production
- Monitor closely
- Rollback plan ready

### **Phase 2: Enhancements (Week 2-3)**

**Week 2: Data Consistency**
- Make expiry NOT NULL
- Data migration
- Near-expiry warning system

**Week 3: Monitoring & Alerts**
- Dashboard widgets
- Email alerts
- Reports

---

## RISK MITIGATION

### **Risk 1: FEFO Changes Batch Allocation**

**Mitigation**:
- ✅ Extensive testing with real data
- ✅ Parallel run (log both FIFO & FEFO results, compare)
- ✅ Gradual rollout (staging → pilot org → all orgs)

### **Risk 2: User Confusion with New Validations**

**Mitigation**:
- ✅ Clear error messages
- ✅ User training materials
- ✅ Help documentation
- ✅ Support team briefing

### **Risk 3: Expired Stock Blocking Operations**

**Mitigation**:
- ✅ Proactive alerts (before expiry)
- ✅ Clear process for expired stock disposal
- ✅ Override mechanism for special cases (with approval)

### **Risk 4: Performance Impact**

**Mitigation**:
- ✅ Database indexes already in place (`expiry_date` indexed)
- ✅ Query optimization
- ✅ Performance testing before deployment

---

## USER TRAINING REQUIREMENTS

### **Training Topics**:

1. **Goods Receipt**
   - Expiry date validation (must be future date)
   - Near-expiry warning interpretation
   - What to do when receiving near-expiry goods

2. **Inventory Management**
   - FEFO concept explanation
   - Why batch allocation changed
   - How to monitor expiring items

3. **Invoice Creation**
   - Why invoice might fail (expired items)
   - How to check expiry before invoicing
   - Process for handling expired stock

4. **Reports & Alerts**
   - How to read expiring items report
   - Email alert interpretation
   - Action items when receiving alerts

### **Training Materials Needed**:
- ✅ User guide (PDF)
- ✅ Video tutorial (5-10 minutes)
- ✅ FAQ document
- ✅ Quick reference card

---

## MONITORING & METRICS

### **Metrics to Track Post-Deployment**:

1. **Operational Metrics**
   - Number of GR rejections due to expiry validation
   - Number of near-expiry warnings triggered
   - Number of invoice failures due to expired items
   - FEFO adherence rate

2. **Business Metrics**
   - Waste reduction (expired stock value)
   - Average days to expiry at sale
   - Inventory turnover rate
   - Compliance score

3. **System Metrics**
   - Query performance (FEFO queries)
   - Alert delivery rate
   - Error rate
   - User support tickets

---

## ROLLBACK PLAN

### **If Critical Issues Found**:

1. **Immediate Rollback** (< 1 hour)
   ```bash
   # Revert to previous version
   git revert <commit-hash>
   php artisan migrate:rollback
   php artisan cache:clear
   ```

2. **Partial Rollback** (Keep some features)
   - Keep expiry validation (low risk)
   - Rollback FEFO (if allocation issues)
   - Rollback blocking (if too disruptive)

3. **Data Integrity Check**
   ```sql
   -- Check for any data inconsistencies
   SELECT * FROM inventory_items WHERE expiry_date IS NULL;
   SELECT * FROM inventory_items WHERE expiry_date < CURDATE();
   ```

---

## CONCLUSION

### **Overall Assessment**: ✅ **SAFE TO PROCEED**

**Reasons**:
1. ✅ No breaking changes to data structure
2. ✅ Backward compatible
3. ✅ Additive changes (enhancements, not rewrites)
4. ✅ Clear rollback plan
5. ✅ Comprehensive testing strategy

**Recommendation**:
- **Proceed with implementation**
- **Follow phased rollout**
- **Invest in user training**
- **Monitor closely post-deployment**

**Timeline**: 2-3 weeks for full implementation

**Effort**: 
- Development: 3-4 days
- Testing: 3-4 days
- Training: 2-3 days
- Deployment & Monitoring: 2-3 days

**Total**: ~2 weeks for critical fixes + 1 week for enhancements

---

**Prepared by**: System Analyst & Backend Architect  
**Date**: 22 April 2026  
**Status**: READY FOR STAKEHOLDER REVIEW
