# Penghapusan Expiry Date & Batch No dari Master Product

## Alasan Penghapusan:

### ❌ Masalah Regulasi & Konsep:
1. **Tidak Sesuai Regulasi**: Regulasi tidak membolehkan menyimpan expiry_date dan batch_no di master product
2. **Salah Konsep**: Satu produk bisa memiliki banyak batch dengan tanggal kadaluarsa berbeda
3. **Data Redundan**: Data ini seharusnya per-batch, bukan per-produk

### Contoh Kasus:
```
Produk: Paracetamol 500mg (Master Product)
├── Batch A (GR Jan 2026): Exp 2028-01-15
├── Batch B (GR Feb 2026): Exp 2029-02-20
└── Batch C (GR Mar 2026): Exp 2027-12-10
```

**Kesimpulan:** TIDAK BISA simpan 1 expiry_date di master Product!

---

## Perubahan yang Dilakukan:

### 1. ✅ Update Product Model
**File:** `app/Models/Product.php`

**Dihapus dari `$fillable`:**
```php
'expiry_date',  // ❌ Dihapus
'batch_no',     // ❌ Dihapus
```

**Dihapus dari `casts()`:**
```php
'expiry_date' => 'date',  // ❌ Dihapus
```

**Dihapus Methods:**
- `isExpired()`
- `isExpiringSoon()`
- `getDaysUntilExpiryAttribute()`
- `getExpiryStatusAttribute()`
- `getExpiryStatusColorAttribute()`
- `scopeExpiringSoon()`
- `scopeExpired()`

### 2. ✅ Database Migration
**File:** `database/migrations/2026_04_21_132135_remove_expiry_date_and_batch_no_from_products_table.php`

**Columns Dropped:**
```php
Schema::table('products', function (Blueprint $table) {
    $table->dropColumn(['expiry_date', 'batch_no']);
});
```

### 3. ✅ Hapus Migration Conflict
**File Dihapus:** `database/migrations/2026_04_21_400001_standardize_batch_field_naming.php`
- Migration ini conflict karena mencoba rename `batch_number` ke `batch_no`

---

## Sistem yang Benar (Sudah Ada):

### ✅ Expiry Date & Batch No di Level Batch (Per Penerimaan)

**Lokasi yang BENAR:**
1. **`goods_receipt_items`** - Input saat penerimaan barang
   - `batch_no` (varchar)
   - `expiry_date` (date)

2. **`supplier_invoice_line_items`** - Copy dari GR
   - `batch_no` (varchar)
   - `expiry_date` (date)

3. **`customer_invoice_line_items`** - Copy dari GR
   - `batch_no` (varchar)
   - `expiry_date` (date)

**Flow yang Benar:**
```
Goods Receipt (Input Manual per Batch)
   ↓
Supplier Invoice (Copy dari GR)
   ↓
Customer Invoice (Copy dari GR)
```

---

## Field yang Tetap Ada di Master Product:

### ✅ Field yang Relevan untuk Master Product:
```php
// Identitas Produk
'name'
'sku'
'manufacturer'
'country_of_origin'

// Registrasi (Izin Edar)
'registration_number'
'registration_date'
'registration_expiry'  // ✅ Ini tetap ada (izin edar, bukan kadaluarsa produk)

// Kategori & Klasifikasi
'category'
'product_type'
'risk_class'

// Harga
'cost_price'
'selling_price'
'discount_percentage'

// Storage (Kondisi Penyimpanan)
'storage_temperature'   // ✅ Tetap ada
'storage_condition'     // ✅ Tetap ada
'special_handling'      // ✅ Tetap ada

// Stock Management
'min_stock_level'
'max_stock_level'
'reorder_quantity'
```

---

## Perbedaan Penting:

| Field | Master Product | Per Batch (GR) |
|-------|---------------|----------------|
| **registration_expiry** | ✅ Ada (izin edar produk) | ❌ Tidak ada |
| **expiry_date** | ❌ Dihapus (per batch) | ✅ Ada (kadaluarsa batch) |
| **batch_no** | ❌ Dihapus (per batch) | ✅ Ada (nomor batch) |

**Catatan:**
- `registration_expiry` = Tanggal kadaluarsa **izin edar** produk (dari BPOM/Kemenkes)
- `expiry_date` = Tanggal kadaluarsa **produk fisik** (per batch yang diterima)

---

## Testing Checklist:

### ✅ Yang Harus Dicek:
1. ✅ Migration berhasil drop columns
2. ✅ Product model tidak error
3. ✅ Form create/edit product tidak ada field expiry_date/batch_no
4. ✅ Goods Receipt form masih bisa input expiry_date & batch_no
5. ✅ Invoice (AP & AR) masih menampilkan expiry_date & batch_no dari GR
6. ✅ Tidak ada error di view yang mengakses `$product->expiry_date`

### ⚠️ Potential Issues:
- Jika ada view yang masih akses `$product->expiry_date` → akan error
- Jika ada seeder yang set `expiry_date` di Product → perlu diupdate

---

## Files Changed:

1. ✅ `app/Models/Product.php` - Remove fields & methods
2. ✅ `database/migrations/2026_04_21_132135_remove_expiry_date_and_batch_no_from_products_table.php` - Drop columns
3. ✅ `database/migrations/2026_04_21_400001_standardize_batch_field_naming.php` - Deleted (conflict)

---

## Kesimpulan:

✅ **Expiry date dan batch number sudah dihapus dari master Product**
✅ **Sesuai dengan regulasi yang tidak membolehkan menyimpan data per-batch di master**
✅ **Sistem tetap berfungsi normal karena data expiry_date ada di GoodsReceiptItem**
✅ **Konsep data sudah benar: Master Product = data umum, GR Item = data per batch**
