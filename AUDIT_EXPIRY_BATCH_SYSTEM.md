# AUDIT SISTEM EXPIRY DATE & BATCH MANAGEMENT
## Medikindo B2B Healthcare System
**Tanggal Audit**: 22 April 2026  
**Auditor**: System Analyst & Backend Architect  
**Tujuan**: Memastikan kesiapan sistem untuk compliance healthcare terkait batch tracking dan expiry management

---

## EXECUTIVE SUMMARY

### Klasifikasi Sistem: **LEVEL 2.5 - PARTIAL BATCH (TRANSISI KE BATCH-READY)**

Sistem Medikindo saat ini berada dalam fase transisi yang baik menuju batch-based system yang compliant. Struktur dasar sudah benar, namun masih ada gap kritis yang harus ditutup sebelum sistem dapat dianggap production-ready untuk healthcare compliance.

**Status**: ⚠️ **PARTIAL IMPLEMENTATION - NEEDS STRENGTHENING**

---

## STEP 1 — AUDIT SISTEM EXISTING

### A. STRUKTUR DATABASE

#### ✅ **POSITIF: Batch System Sudah Ada**

1. **Tabel `inventory_items`** (Batch-Based Inventory)
   ```sql
   - id
   - organization_id
   - product_id
   - batch_no (string, 100 chars)
   - expiry_date (date, nullable)
   - quantity_on_hand (integer)
   - quantity_reserved (integer)
   - unit_cost (decimal)
   - location (string, nullable)
   - timestamps
   
   UNIQUE KEY: (organization_id, product_id, batch_no)
   INDEX: expiry_date, product_id
   ```

2. **Tabel `goods_receipt_items`** (Batch Input Point)
   ```sql
   - batch_no (string, 100 chars, nullable)
   - expiry_date (date, nullable)
   - uom (string, 50 chars, nullable)
   
   INDEX: batch_no
   ```

3. **Tabel `inventory_movements`** (Transaction History)
   ```sql
   - inventory_item_id (FK to inventory_items)
   - movement_type (enum: in, out, adjustment)
   - quantity (integer)
   - reference_type (string)
   - reference_id (bigint)
   - notes (text)
   - created_by (FK to users)
   ```

#### ✅ **POSITIF: Evolusi Desain yang Benar**

Migration history menunjukkan evolusi yang tepat:

1. **2026_04_15_102136**: Awalnya expiry di `products` table (❌ SALAH)
2. **2026_04_21_132135**: **DIPERBAIKI** - expiry dihapus dari `products`, dipindah ke batch level (✅ BENAR)

**Komentar di migration**:
> "These fields should be per-batch (in goods_receipt_items), not in master product. Reason: Regulatory compliance - one product can have multiple batches with different expiry dates"

**Analisis**: Tim development sudah memahami prinsip batch-based system dengan benar.

---

### B. FLOW OPERASIONAL

#### 1. **Goods Receipt (GR) - Input Batch & Expiry**

**File**: `resources/views/goods-receipts/create.blade.php`

**Input Fields per Item**:
```html
- Jumlah Diterima (quantity_received) - REQUIRED
- Nomor Batch (batch_no) - REQUIRED
- Tgl Kadaluarsa (expiry_date) - REQUIRED (type="date")
- Kondisi Barang (condition) - Optional
- Catatan Kondisi (notes) - Optional
```

**Validasi** (`StoreGoodsReceiptRequest.php`):
```php
'items.*.batch_no'       => 'required|string|max:100',
'items.*.expiry_date'    => 'required|date',
```

✅ **POSITIF**:
- Batch dan expiry adalah **MANDATORY** saat GR
- UI form sudah enforce required fields
- Validasi backend sudah ada

⚠️ **GAP KRITIS**:
- **TIDAK ADA VALIDASI** `expiry_date > today`
- Sistem bisa menerima barang yang sudah expired!
- Tidak ada warning jika expiry < 90 hari (near expiry)

---

#### 2. **Inventory Management - Batch-Based Storage**

**File**: `app/Services/InventoryService.php`

**Method `addStock()`**:
```php
public function addStock(
    int $organizationId,
    int $productId,
    string $batchNo,
    ?string $expiryDate,  // ⚠️ NULLABLE!
    int $quantity,
    float $unitCost,
    ...
): InventoryItem
```

**Logic**:
```php
$inventoryItem = InventoryItem::firstOrCreate(
    [
        'organization_id' => $organizationId,
        'product_id' => $productId,
        'batch_no' => $batchNo,  // ✅ Batch-based unique key
    ],
    [
        'expiry_date' => $expiryDate,  // ⚠️ Bisa NULL
        'quantity_on_hand' => 0,
        ...
    ]
);
```

✅ **POSITIF**:
- Inventory sudah **batch-based**
- Unique constraint: `(organization_id, product_id, batch_no)`
- Satu produk bisa punya banyak batch dengan expiry berbeda

⚠️ **GAP**:
- `expiry_date` masih **NULLABLE** di service layer
- Tidak ada enforcement bahwa expiry WAJIB ada

---

#### 3. **Stock Reduction - FIFO vs FEFO**

**Method `reduceStock()`**:
```php
$inventoryItems = InventoryItem::where('organization_id', $organizationId)
    ->where('product_id', $productId)
    ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
    ->orderBy('created_at', 'asc')  // ❌ FIFO, bukan FEFO!
    ->get();
```

❌ **CRITICAL GAP**:
- Sistem menggunakan **FIFO** (First In First Out)
- Healthcare compliance membutuhkan **FEFO** (First Expiry First Out)
- Barang dengan expiry lebih dekat harus keluar duluan, bukan yang masuk duluan

**Method `getExpiringItems()`**:
```php
return InventoryItem::where('organization_id', $organizationId)
    ->whereNotNull('expiry_date')
    ->whereDate('expiry_date', '<=', now()->addDays($days))
    ->whereDate('expiry_date', '>=', now())
    ->with('product')
    ->orderBy('expiry_date', 'asc')  // ✅ Sudah ada sorting by expiry
    ->get();
```

✅ **POSITIF**:
- Ada method untuk tracking expiring items
- Sudah ada sorting by expiry date

---

### C. VALIDASI EXISTING

#### ✅ **Yang Sudah Ada**:

1. **Expiry Checking di Model** (`InventoryItem.php`):
   ```php
   public function isExpiringSoon(): bool {
       if (!$this->expiry_date) return false;
       return $this->expiry_date->diffInDays(now()) <= 60;
   }
   
   public function isExpired(): bool {
       if (!$this->expiry_date) return false;
       return $this->expiry_date->isPast();
   }
   ```

2. **Scopes untuk Query**:
   ```php
   public function scopeExpiringSoon($query) {
       return $query->whereNotNull('expiry_date')
           ->whereDate('expiry_date', '<=', now()->addDays(60));
   }
   
   public function scopeExpired($query) {
       return $query->whereNotNull('expiry_date')
           ->whereDate('expiry_date', '<', now());
   }
   ```

3. **UI Display** (View files):
   - Sudah ada visual indicator untuk expired items (red badge)
   - Sudah ada visual indicator untuk expiring soon (yellow badge)

#### ❌ **Yang BELUM Ada**:

1. **Validasi saat GR**:
   - Tidak ada validasi `expiry_date > today`
   - Tidak ada warning untuk near-expiry items
   - Tidak ada blocking untuk expired items

2. **FEFO Logic**:
   - Stock reduction masih FIFO
   - Tidak ada automatic batch selection based on expiry

3. **Expiry Alert System**:
   - Tidak ada notification untuk expiring items
   - Tidak ada dashboard alert
   - Tidak ada automated report

4. **Blocking Mechanism**:
   - Tidak ada blocking untuk jual barang expired
   - Tidak ada warning saat create invoice dengan batch expired

---

## STEP 2 — KLASIFIKASI KONDISI SISTEM

### **LEVEL 2.5 - PARTIAL BATCH (TRANSISI KE BATCH-READY)**

**Kriteria**:
- ✅ Batch system ada
- ✅ Expiry di batch level (bukan di product)
- ✅ Relasi jelas (product → batch → inventory)
- ⚠️ Expiry tidak fully mandatory (nullable di beberapa layer)
- ❌ FEFO belum implemented
- ❌ Validasi expiry belum lengkap
- ❌ Blocking mechanism belum ada

**Kesimpulan**: Sistem sudah 70% siap, butuh strengthening di 30% sisanya.

---

## STEP 3 — ANALISIS GAP

### **Bandingkan: EXISTING vs REQUIRED**

| Aspek | Status Existing | Required for Healthcare | Gap |
|-------|----------------|------------------------|-----|
| **Batch System** | ✅ Ada | ✅ Wajib | - |
| **Expiry di Batch Level** | ✅ Ada | ✅ Wajib | - |
| **Batch-Based Inventory** | ✅ Ada | ✅ Wajib | - |
| **Mandatory Expiry Input** | ⚠️ Required di form, nullable di service | ✅ Wajib di semua layer | **GAP: Enforce di service layer** |
| **Expiry > Today Validation** | ❌ Tidak ada | ✅ Wajib | **GAP: Tambah validasi** |
| **FEFO Logic** | ❌ Masih FIFO | ✅ Wajib | **GAP: Implement FEFO** |
| **Expired Item Blocking** | ❌ Tidak ada | ✅ Wajib | **GAP: Tambah blocking** |
| **Near-Expiry Warning** | ⚠️ Ada di UI, tidak di flow | ✅ Wajib | **GAP: Integrate ke flow** |
| **Expiry Alert System** | ❌ Tidak ada | ✅ Wajib | **GAP: Build alert system** |
| **Expiry Status Tracking** | ⚠️ Ada method, tidak digunakan | ✅ Wajib | **GAP: Integrate ke business logic** |

---

### **GAP DETAIL**

#### **GAP 1: Expiry Validation saat GR** (CRITICAL)

**Masalah**:
```php
// StoreGoodsReceiptRequest.php
'items.*.expiry_date' => 'required|date',  // ❌ Tidak ada 'after:today'
```

**Dampak**:
- Sistem bisa menerima barang yang sudah expired
- Compliance risk tinggi
- Bisa menyebabkan distribusi barang expired ke customer

**Solusi**:
```php
'items.*.expiry_date' => 'required|date|after:today',
```

**Tambahan**:
- Warning jika expiry < 90 hari (near expiry)
- Require approval untuk terima barang near expiry

---

#### **GAP 2: FEFO Implementation** (CRITICAL)

**Masalah**:
```php
// InventoryService.php - reduceStock()
->orderBy('created_at', 'asc')  // ❌ FIFO
```

**Dampak**:
- Barang dengan expiry lebih jauh keluar duluan
- Barang dengan expiry dekat menumpuk
- Risk expired stock meningkat
- Tidak compliant dengan healthcare best practice

**Solusi**:
```php
->orderBy('expiry_date', 'asc')  // ✅ FEFO
->orderBy('created_at', 'asc')   // Tie-breaker jika expiry sama
```

**Catatan**:
- Harus handle `expiry_date IS NULL` (legacy data)
- Prioritas: expiry date → created date

---

#### **GAP 3: Expired Item Blocking** (HIGH)

**Masalah**:
- Tidak ada checking saat create customer invoice
- Bisa jual barang yang sudah expired

**Dampak**:
- Compliance violation
- Legal risk
- Customer safety risk

**Solusi**:
- Add validation di `CustomerInvoiceService`
- Block invoice creation jika ada item expired
- Show clear error message

---

#### **GAP 4: Expiry Alert System** (MEDIUM)

**Masalah**:
- Tidak ada proactive notification
- User harus manual check inventory

**Dampak**:
- Barang bisa expired tanpa diketahui
- Waste meningkat
- Opportunity loss

**Solusi**:
- Daily cron job untuk check expiring items
- Email notification ke procurement team
- Dashboard widget untuk expiring items
- Weekly report

---

#### **GAP 5: Expiry Status Enforcement** (MEDIUM)

**Masalah**:
- `expiry_date` masih nullable di service layer
- Tidak ada enforcement di business logic

**Dampak**:
- Inconsistency data
- Bisa ada inventory tanpa expiry
- Compliance gap

**Solusi**:
- Make `expiry_date` NOT NULL di database
- Enforce di service layer
- Data migration untuk existing NULL values

---

## STEP 4 — REKOMENDASI STRATEGIS

### **PRIORITAS 1 - CRITICAL (HARUS SEGERA)**

#### 1. **Tambah Validasi Expiry > Today saat GR**
**File**: `app/Http/Requests/StoreGoodsReceiptRequest.php`

```php
'items.*.expiry_date' => [
    'required',
    'date',
    'after:today',  // ← TAMBAH INI
],
```

**Effort**: 5 menit  
**Impact**: HIGH - Prevent expired goods entry  
**Risk**: LOW - Simple validation rule

---

#### 2. **Implement FEFO di Stock Reduction**
**File**: `app/Services/InventoryService.php`

```php
// Method: reduceStock()
$inventoryItems = InventoryItem::where('organization_id', $organizationId)
    ->where('product_id', $productId)
    ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
    ->whereDate('expiry_date', '>=', now())  // ← TAMBAH: Exclude expired
    ->orderBy('expiry_date', 'asc')          // ← UBAH: FEFO
    ->orderBy('created_at', 'asc')           // ← KEEP: Tie-breaker
    ->get();
```

**Effort**: 30 menit  
**Impact**: HIGH - Compliance dengan healthcare standard  
**Risk**: MEDIUM - Perlu testing untuk ensure correct behavior

---

#### 3. **Block Expired Items di Invoice Creation**
**File**: `app/Services/CustomerInvoiceService.php` (atau equivalent)

```php
// Before creating invoice, check all items
foreach ($items as $item) {
    $inventoryItem = InventoryItem::find($item['inventory_item_id']);
    
    if ($inventoryItem->isExpired()) {
        throw new \DomainException(
            "Cannot create invoice: Item {$inventoryItem->product->name} " .
            "(Batch: {$inventoryItem->batch_no}) is expired."
        );
    }
}
```

**Effort**: 1 jam  
**Impact**: HIGH - Prevent selling expired goods  
**Risk**: LOW - Clear business rule

---

### **PRIORITAS 2 - HIGH (DALAM 1-2 MINGGU)**

#### 4. **Make Expiry Date NOT NULL**

**Migration**:
```php
Schema::table('inventory_items', function (Blueprint $table) {
    $table->date('expiry_date')->nullable(false)->change();
});

Schema::table('goods_receipt_items', function (Blueprint $table) {
    $table->date('expiry_date')->nullable(false)->change();
});
```

**Data Migration**:
```php
// Handle existing NULL values
InventoryItem::whereNull('expiry_date')
    ->update(['expiry_date' => now()->addYears(2)]);  // Default 2 tahun
```

**Effort**: 2 jam  
**Impact**: MEDIUM - Data consistency  
**Risk**: MEDIUM - Perlu handle existing data

---

#### 5. **Near-Expiry Warning saat GR**

**Logic**:
```php
// In StoreGoodsReceiptRequest or Service
foreach ($items as $item) {
    $expiryDate = Carbon::parse($item['expiry_date']);
    $daysUntilExpiry = $expiryDate->diffInDays(now());
    
    if ($daysUntilExpiry <= 90) {
        // Log warning
        // Require approval
        // Send notification
    }
}
```

**Effort**: 3 jam  
**Impact**: MEDIUM - Proactive management  
**Risk**: LOW - Additional check only

---

### **PRIORITAS 3 - MEDIUM (DALAM 1 BULAN)**

#### 6. **Expiry Alert System**

**Components**:
- Daily cron job
- Email notification
- Dashboard widget
- Weekly report

**Effort**: 1-2 hari  
**Impact**: MEDIUM - Proactive monitoring  
**Risk**: LOW - Independent feature

---

#### 7. **Expiry Status Enum**

**Add to InventoryItem**:
```php
public function getExpiryStatusAttribute(): string
{
    if (!$this->expiry_date) return 'unknown';
    
    $daysUntilExpiry = $this->expiry_date->diffInDays(now(), false);
    
    if ($daysUntilExpiry < 0) return 'expired';
    if ($daysUntilExpiry <= 30) return 'critical';
    if ($daysUntilExpiry <= 90) return 'warning';
    if ($daysUntilExpiry <= 180) return 'caution';
    return 'safe';
}
```

**Effort**: 2 jam  
**Impact**: MEDIUM - Better visibility  
**Risk**: LOW - Computed attribute

---

## STEP 5 — DESIGN FINAL (HIGH LEVEL)

### **A. STRUKTUR DATA FINAL**

```
┌─────────────────────────────────────────────────────────────┐
│                     PRODUCTS (Master)                       │
│  - id                                                       │
│  - name, sku, category                                      │
│  - supplier_id                                              │
│  - cost_price, selling_price                                │
│  - registration_number, registration_expiry                 │
│  - is_narcotic, requires_prescription                       │
│  ❌ NO expiry_date (removed)                                │
│  ❌ NO batch_no (removed)                                   │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ 1:N
                            ▼
┌─────────────────────────────────────────────────────────────┐
│              GOODS_RECEIPT_ITEMS (Batch Input)              │
│  - id                                                       │
│  - goods_receipt_id                                         │
│  - purchase_order_item_id                                   │
│  - product_id                                               │
│  - quantity_received                                        │
│  ✅ batch_no (REQUIRED, indexed)                            │
│  ✅ expiry_date (REQUIRED, NOT NULL)                        │
│  - uom                                                      │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ Triggers
                            ▼
┌─────────────────────────────────────────────────────────────┐
│            INVENTORY_ITEMS (Batch-Based Stock)              │
│  - id                                                       │
│  - organization_id                                          │
│  - product_id                                               │
│  ✅ batch_no (UNIQUE with org+product)                      │
│  ✅ expiry_date (NOT NULL, indexed)                         │
│  - quantity_on_hand                                         │
│  - quantity_reserved                                        │
│  - unit_cost                                                │
│  - location                                                 │
│  UNIQUE: (organization_id, product_id, batch_no)            │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ 1:N
                            ▼
┌─────────────────────────────────────────────────────────────┐
│          INVENTORY_MOVEMENTS (Transaction Log)              │
│  - id                                                       │
│  - inventory_item_id                                        │
│  - movement_type (in/out/adjustment)                        │
│  - quantity                                                 │
│  - reference_type, reference_id                             │
│  - created_by                                               │
└─────────────────────────────────────────────────────────────┘
```

---

### **B. FLOW DIAGRAM**

#### **1. Goods Receipt Flow (WITH EXPIRY VALIDATION)**

```
┌─────────────────────────────────────────────────────────────┐
│                    GOODS RECEIPT FLOW                       │
└─────────────────────────────────────────────────────────────┘

1. User Input GR Form
   ├─ Select PO
   ├─ Input Delivery Order Number
   └─ For each item:
      ├─ Quantity Received
      ├─ Batch Number (REQUIRED)
      └─ Expiry Date (REQUIRED)

2. Validation Layer
   ├─ ✅ Batch not empty
   ├─ ✅ Expiry not empty
   ├─ ✅ Expiry > today  ← NEW
   └─ ⚠️ Warning if expiry < 90 days  ← NEW

3. Service Layer (GoodsReceiptService)
   ├─ Create GoodsReceipt
   ├─ Create GoodsReceiptItems (with batch & expiry)
   └─ Trigger Inventory Update

4. Inventory Service
   ├─ Find or Create InventoryItem by (org, product, batch)
   ├─ Set expiry_date (NOT NULL)
   ├─ Increment quantity_on_hand
   └─ Log InventoryMovement (type: IN)

5. Post-Processing
   ├─ Check if near expiry → Send alert
   ├─ Update PO status
   └─ Generate GR document
```

---

#### **2. Stock Reduction Flow (WITH FEFO)**

```
┌─────────────────────────────────────────────────────────────┐
│                  STOCK REDUCTION FLOW (FEFO)                │
└─────────────────────────────────────────────────────────────┘

1. Customer Invoice Creation
   └─ Request: Reduce stock for Product X, Qty 100

2. Pre-Validation
   ├─ Check available stock >= requested qty
   └─ ✅ Check no expired batches selected  ← NEW

3. FEFO Selection
   Query InventoryItems:
   ├─ WHERE organization_id = X
   ├─ WHERE product_id = Y
   ├─ WHERE (quantity_on_hand - quantity_reserved) > 0
   ├─ WHERE expiry_date >= today  ← NEW (exclude expired)
   ├─ ORDER BY expiry_date ASC    ← FEFO (earliest expiry first)
   └─ ORDER BY created_at ASC     ← Tie-breaker

4. Allocation Loop
   FOR EACH batch (in FEFO order):
   ├─ Calculate available_qty
   ├─ Allocate min(remaining_qty, available_qty)
   ├─ Decrement quantity_on_hand
   ├─ Log InventoryMovement (type: OUT)
   └─ BREAK if remaining_qty = 0

5. Result
   └─ Return list of batches used with quantities
```

---

### **C. VALIDATION RULES**

#### **Goods Receipt Validation**

```php
// Request Level
'items.*.batch_no'       => 'required|string|max:100',
'items.*.expiry_date'    => 'required|date|after:today',

// Service Level
if ($expiryDate <= now()) {
    throw new \DomainException("Cannot receive expired goods");
}

if ($expiryDate->diffInDays(now()) <= 90) {
    // Log warning
    // Require approval (optional)
    event(new NearExpiryGoodsReceived($item));
}
```

---

#### **Stock Reduction Validation**

```php
// Before allocation
$expiredBatches = $inventoryItems->filter(fn($item) => $item->isExpired());

if ($expiredBatches->isNotEmpty()) {
    throw new \DomainException(
        "Cannot allocate stock: Some batches are expired"
    );
}

// During allocation (FEFO)
$inventoryItems = InventoryItem::where(...)
    ->whereDate('expiry_date', '>=', now())  // Exclude expired
    ->orderBy('expiry_date', 'asc')          // FEFO
    ->get();
```

---

#### **Invoice Creation Validation**

```php
// Before creating customer invoice
foreach ($lineItems as $item) {
    $batch = InventoryItem::find($item['inventory_item_id']);
    
    if ($batch->isExpired()) {
        throw new \DomainException(
            "Cannot invoice expired item: {$batch->product->name} " .
            "(Batch: {$batch->batch_no}, Expired: {$batch->expiry_date->format('d M Y')})"
        );
    }
}
```

---

### **D. EXPIRY STATUS POLICY**

```php
class ExpiryStatusPolicy
{
    const STATUS_SAFE     = 'safe';      // > 180 days
    const STATUS_CAUTION  = 'caution';   // 90-180 days
    const STATUS_WARNING  = 'warning';   // 30-90 days
    const STATUS_CRITICAL = 'critical';  // < 30 days
    const STATUS_EXPIRED  = 'expired';   // Past expiry date
    
    public static function getStatus(Carbon $expiryDate): string
    {
        $daysUntilExpiry = $expiryDate->diffInDays(now(), false);
        
        if ($daysUntilExpiry < 0) return self::STATUS_EXPIRED;
        if ($daysUntilExpiry <= 30) return self::STATUS_CRITICAL;
        if ($daysUntilExpiry <= 90) return self::STATUS_WARNING;
        if ($daysUntilExpiry <= 180) return self::STATUS_CAUTION;
        return self::STATUS_SAFE;
    }
    
    public static function getColor(string $status): string
    {
        return match($status) {
            self::STATUS_EXPIRED  => 'danger',
            self::STATUS_CRITICAL => 'danger',
            self::STATUS_WARNING  => 'warning',
            self::STATUS_CAUTION  => 'info',
            self::STATUS_SAFE     => 'success',
            default => 'secondary',
        };
    }
    
    public static function canSell(string $status): bool
    {
        return !in_array($status, [self::STATUS_EXPIRED]);
    }
    
    public static function requiresApproval(string $status): bool
    {
        return in_array($status, [self::STATUS_CRITICAL, self::STATUS_WARNING]);
    }
}
```

---

## KESIMPULAN & NEXT STEPS

### **KESIMPULAN AUDIT**

1. ✅ **Struktur Dasar Sudah Benar**
   - Batch system sudah ada
   - Expiry di batch level (bukan product)
   - Relasi database sudah tepat

2. ⚠️ **Perlu Strengthening**
   - Validasi expiry belum lengkap
   - FEFO belum implemented
   - Blocking mechanism belum ada

3. 🎯 **Sistem 70% Siap**
   - Tinggal 30% lagi untuk production-ready
   - Gap yang ada bisa ditutup dalam 1-2 minggu
   - Risk rendah karena struktur sudah benar

---

### **ROADMAP IMPLEMENTASI**

#### **Week 1: Critical Fixes**
- [ ] Day 1: Add expiry > today validation
- [ ] Day 2-3: Implement FEFO
- [ ] Day 4-5: Add expired item blocking
- [ ] Testing & QA

#### **Week 2: Data Consistency**
- [ ] Day 1-2: Make expiry NOT NULL
- [ ] Day 3: Data migration
- [ ] Day 4-5: Near-expiry warning system
- [ ] Testing & QA

#### **Week 3-4: Monitoring & Alerts**
- [ ] Expiry alert system
- [ ] Dashboard widgets
- [ ] Reports
- [ ] Documentation

---

### **METRICS TO TRACK**

1. **Compliance Metrics**
   - % of inventory with valid expiry date
   - Number of expired items in stock
   - Number of near-expiry items

2. **Operational Metrics**
   - FEFO adherence rate
   - Waste due to expiry
   - Average days to expiry at sale

3. **System Metrics**
   - Validation rejection rate
   - Alert response time
   - Inventory accuracy

---

### **FINAL RECOMMENDATION**

**PROCEED WITH IMPLEMENTATION**

Sistem Medikindo sudah memiliki fondasi yang solid untuk batch & expiry management. Gap yang ada bersifat tactical dan bisa ditutup dengan effort yang reasonable. Tidak perlu redesign besar-besaran.

**Priority**: Fokus pada 3 critical fixes di Week 1 untuk immediate compliance improvement.

**Risk Level**: LOW - Karena struktur sudah benar, hanya perlu strengthening logic.

**Timeline**: 2-4 minggu untuk full implementation.

---

**Prepared by**: System Analyst & Backend Architect  
**Date**: 22 April 2026  
**Status**: READY FOR REVIEW & APPROVAL
