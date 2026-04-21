# ANALISIS: Partial Goods Receipt Status Flow

**Tanggal**: 21 April 2026  
**Status**: ANALISIS SELESAI + REKOMENDASI

---

## 📋 PERTANYAAN USER

> "Jika barang diterima sebagian dari sisi rumah sakit, maka statusnya di medikindo apa? Kemudian rekomendasimu apa untuk hal ini?"

---

## 🔍 ANALISIS SISTEM SAAT INI

### 1. Status Flow Purchase Order (PO)

```
draft → submitted → approved → completed
                  ↓
               rejected → draft
```

**Status PO yang tersedia:**
- `draft` - PO masih dalam draft
- `submitted` - PO sudah diajukan, menunggu approval
- `approved` - PO sudah disetujui, siap untuk pengiriman
- `rejected` - PO ditolak
- `completed` - PO selesai (semua barang sudah diterima)

**CATATAN PENTING**: 
- Status `shipped`, `delivered`, `partially_received` sudah DIHAPUS dalam refactoring sebelumnya (migration `2026_04_11_000001_refactor_po_status_states.php`)
- Alasan: Delivery tracking terjadi di LUAR sistem (handled by supplier)
- PO langsung transisi dari `approved` → `completed` via Goods Receipt

### 2. Status Flow Goods Receipt (GR)

```
partial | completed
```

**Status GR yang tersedia:**
- `partial` - Barang diterima sebagian
- `completed` - Barang diterima lengkap

### 3. Logic Saat Ini (dari `GoodsReceiptService.php`)

**Ketika rumah sakit menerima barang:**

```php
// Line 67-138: confirmReceipt()

1. Validasi: PO harus dalam status 'approved'
2. Hitung total yang sudah diterima sebelumnya
3. Validasi: qty yang diterima tidak boleh > qty yang tersisa
4. Tentukan status GR:
   - Jika semua item sudah diterima lengkap → GR status = 'completed'
   - Jika masih ada item yang kurang → GR status = 'partial'
5. Update inventory:
   - HANYA jika GR status = 'completed'
   - Jika GR status = 'partial' → inventory TIDAK diupdate
6. Update PO status:
   - HANYA jika GR status = 'completed' → PO status = 'completed'
   - Jika GR status = 'partial' → PO status tetap 'approved'
```

---

## ⚠️ MASALAH YANG TERIDENTIFIKASI

### MASALAH 1: Visibility di Level PO
**Situasi:**
- PO dengan status `approved` bisa berarti:
  - Belum ada barang yang diterima sama sekali
  - Sudah ada barang yang diterima sebagian (1 atau lebih GR partial)
  - Sedang menunggu pengiriman berikutnya

**Dampak:**
- ❌ Tidak ada cara cepat untuk tahu apakah PO sudah mulai diterima atau belum
- ❌ User harus buka detail PO dan cek GR untuk tahu progress
- ❌ Di list PO, semua PO yang "sedang berjalan" terlihat sama (status `approved`)

### MASALAH 2: Inventory Update Tertunda
**Situasi:**
- Inventory HANYA diupdate ketika GR status = `completed`
- Jika GR status = `partial`, inventory TIDAK diupdate

**Contoh Kasus:**
```
PO: 100 box Paracetamol
Pengiriman 1: 60 box diterima → GR status = 'partial'
  → Inventory TIDAK bertambah 60 box
  → Stock masih 0 di sistem, padahal fisik sudah ada 60 box

Pengiriman 2: 40 box diterima → GR status = 'completed'
  → Inventory baru bertambah 100 box sekaligus
  → Padahal 60 box sudah ada sejak pengiriman 1
```

**Dampak:**
- ❌ Stock di sistem tidak real-time
- ❌ Rumah sakit tidak bisa pakai barang yang sudah diterima sebagian
- ❌ Bisa terjadi double order karena sistem menunjukkan stock = 0

### MASALAH 3: Tidak Ada Tracking Progress
**Situasi:**
- Tidak ada field untuk track berapa % PO yang sudah diterima
- Tidak ada field untuk track berapa kali partial receipt sudah terjadi

**Dampak:**
- ❌ Sulit untuk monitoring PO yang "stuck" di partial receipt
- ❌ Tidak ada alert jika PO sudah lama tidak complete

---

## 💡 REKOMENDASI SOLUSI

### OPSI 1: Tambah Status `partially_received` di PO (RECOMMENDED)

**Perubahan:**
1. Tambah status baru: `partially_received`
2. Update status flow:
   ```
   approved → partially_received → completed
   ```
3. Update inventory SETIAP kali GR dibuat (baik partial maupun completed)

**Implementasi:**
```php
// PurchaseOrder.php
public const STATUS_PARTIALLY_RECEIVED = 'partially_received';

public const TRANSITIONS = [
    self::STATUS_DRAFT     => [self::STATUS_SUBMITTED],
    self::STATUS_SUBMITTED => [self::STATUS_APPROVED, self::STATUS_REJECTED],
    self::STATUS_APPROVED  => [self::STATUS_PARTIALLY_RECEIVED, self::STATUS_COMPLETED],
    self::STATUS_PARTIALLY_RECEIVED => [self::STATUS_COMPLETED],
    self::STATUS_REJECTED  => [self::STATUS_DRAFT],
    self::STATUS_COMPLETED => [],
];
```

**Logic Update:**
```php
// GoodsReceiptService.php - confirmReceipt()

// Setelah create GR items:
foreach ($grItems as $grItem) {
    // ... create GR item ...
    
    // UPDATE: Add to inventory IMMEDIATELY (baik partial maupun completed)
    $this->inventoryService->addStock(
        organizationId: $po->organization_id,
        productId: $grItem['po_item']->product_id,
        batchNo: $grItem['data']['batch_no'] ?? 'NO-BATCH',
        expiryDate: $grItem['data']['expiry_date'] ?? null,
        quantity: $grItem['data']['quantity_received'],
        unitCost: $grItem['po_item']->unit_price,
        referenceType: 'App\Models\GoodsReceiptItem',
        referenceId: $grItemRecord->id,
        createdBy: $actor->id,
    );
}

// Update PO status based on GR status
if ($grStatus === GoodsReceipt::STATUS_COMPLETED) {
    $po->update([
        'status' => PurchaseOrder::STATUS_COMPLETED,
        'completed_at' => now(),
    ]);
} elseif ($grStatus === GoodsReceipt::STATUS_PARTIAL) {
    // NEW: Update PO to partially_received
    $po->update([
        'status' => PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
    ]);
}
```

**Keuntungan:**
- ✅ Visibility jelas di level PO
- ✅ Inventory update real-time
- ✅ Mudah untuk filter PO yang sedang dalam partial receipt
- ✅ Konsisten dengan business logic

**Kekurangan:**
- ⚠️ Perlu migration untuk tambah status baru
- ⚠️ Perlu update UI untuk handle status baru
- ⚠️ Perlu update existing PO yang sudah ada partial GR

---

### OPSI 2: Tambah Field `fulfillment_percentage` di PO

**Perubahan:**
1. Tambah field: `fulfillment_percentage` (0-100)
2. Tambah field: `first_received_at` (timestamp)
3. Status tetap `approved`, tapi ada indicator progress
4. Update inventory SETIAP kali GR dibuat

**Implementasi:**
```php
// Migration
Schema::table('purchase_orders', function (Blueprint $table) {
    $table->decimal('fulfillment_percentage', 5, 2)->default(0)->after('status');
    $table->timestamp('first_received_at')->nullable()->after('approved_at');
});

// GoodsReceiptService.php
$po->update([
    'fulfillment_percentage' => $this->calculateFulfillmentPercentage($po),
    'first_received_at' => $po->first_received_at ?? now(),
]);

if ($grStatus === GoodsReceipt::STATUS_COMPLETED) {
    $po->update([
        'status' => PurchaseOrder::STATUS_COMPLETED,
        'completed_at' => now(),
        'fulfillment_percentage' => 100,
    ]);
}
```

**Keuntungan:**
- ✅ Tidak perlu tambah status baru
- ✅ Progress tracking lebih detail (0-100%)
- ✅ Inventory update real-time
- ✅ Bisa filter PO berdasarkan % fulfillment

**Kekurangan:**
- ⚠️ Status `approved` jadi ambigu (bisa 0% atau 50% atau 99%)
- ⚠️ Perlu logic tambahan untuk calculate percentage
- ⚠️ Tidak konsisten dengan state machine pattern

---

### OPSI 3: Keep Current System + Improve UI (MINIMAL CHANGE)

**Perubahan:**
1. Update inventory SETIAP kali GR dibuat (baik partial maupun completed)
2. Tambah badge/indicator di UI untuk show partial receipt count
3. Status tetap seperti sekarang

**Implementasi:**
```php
// GoodsReceiptService.php
// HANYA ubah inventory logic:

// OLD:
if ($grStatus === GoodsReceipt::STATUS_COMPLETED) {
    $this->inventoryService->addStock(...);
}

// NEW:
// ALWAYS update inventory (baik partial maupun completed)
$this->inventoryService->addStock(...);

// PO status update tetap sama:
if ($grStatus === GoodsReceipt::STATUS_COMPLETED) {
    $po->update([
        'status' => PurchaseOrder::STATUS_COMPLETED,
        'completed_at' => now(),
    ]);
}
// Jika partial, PO tetap 'approved'
```

**UI Enhancement:**
```blade
<!-- Di list PO, tambah badge -->
@if($po->status === 'approved' && $po->goodsReceipts()->where('status', 'partial')->exists())
    <span class="badge badge-warning">
        Diterima Sebagian ({{ $po->goodsReceipts()->count() }}x)
    </span>
@endif
```

**Keuntungan:**
- ✅ Minimal code change
- ✅ Inventory update real-time
- ✅ Tidak perlu migration
- ✅ Backward compatible

**Kekurangan:**
- ⚠️ Visibility masih kurang di level PO
- ⚠️ Tidak ada status yang jelas untuk "partially received"
- ⚠️ Sulit untuk query/filter PO yang sedang partial

---

## 🎯 REKOMENDASI FINAL

**Pilihan Terbaik: OPSI 1 (Tambah Status `partially_received`)**

**Alasan:**
1. **Clarity**: Status yang jelas dan eksplisit
2. **Consistency**: Konsisten dengan state machine pattern yang sudah ada
3. **Visibility**: Mudah untuk filter dan monitor PO yang sedang partial
4. **Real-time Inventory**: Stock update langsung saat barang diterima
5. **Business Logic**: Sesuai dengan real business process

**Implementasi Steps:**
1. Create migration untuk tambah status `partially_received`
2. Update `PurchaseOrder` model (constants + transitions)
3. Update `GoodsReceiptService` (inventory + status logic)
4. Update UI (badge, filter, status display)
5. Data migration untuk existing PO yang punya partial GR
6. Update tests

**Estimasi Effort:** 2-3 jam
**Risk Level:** LOW (backward compatible, clear rollback path)

---

## 📊 COMPARISON TABLE

| Aspek | Opsi 1 (Status Baru) | Opsi 2 (Percentage) | Opsi 3 (UI Only) |
|-------|---------------------|---------------------|------------------|
| **Clarity** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ |
| **Real-time Inventory** | ✅ | ✅ | ✅ |
| **Visibility** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐ |
| **Effort** | Medium | Medium | Low |
| **Risk** | Low | Low | Very Low |
| **Consistency** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Queryability** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐ |

---

## 🚀 NEXT STEPS

Jika user setuju dengan **OPSI 1**, saya akan:

1. ✅ Create migration file
2. ✅ Update `PurchaseOrder` model
3. ✅ Update `GoodsReceiptService`
4. ✅ Update UI components
5. ✅ Create data migration script
6. ✅ Update tests
7. ✅ Create documentation

**Apakah user ingin saya lanjutkan dengan implementasi OPSI 1?**

---

## 📝 CATATAN TAMBAHAN

### Tentang Inventory Update
**Current Logic (SALAH):**
- Inventory hanya update ketika GR = `completed`
- Barang yang diterima partial tidak masuk inventory

**Recommended Logic (BENAR):**
- Inventory update SETIAP kali GR dibuat (baik partial maupun completed)
- Setiap batch yang diterima langsung masuk inventory
- Stock real-time, bisa langsung dipakai

### Tentang Multiple Partial Receipts
**Scenario:**
```
PO: 100 box
GR-1: 30 box (partial) → PO status = 'partially_received'
GR-2: 40 box (partial) → PO status tetap 'partially_received'
GR-3: 30 box (completed) → PO status = 'completed'
```

Sistem akan support multiple partial receipts sampai semua barang diterima.

---

**END OF ANALYSIS**
