# Changelog: Credit Limit Setup & Feature Removal

## Tanggal: 22 April 2026

### ✅ Fitur yang Dihapus

#### 1. **Price List Management**
- ❌ Menu "Price Lists" dihapus dari sidebar
- ❌ Routes `/price-lists/*` dinonaktifkan
- ℹ️ Model, migration, dan controller masih ada di codebase (tidak dihapus untuk backward compatibility)
- ℹ️ Jika diperlukan di masa depan, bisa diaktifkan kembali dengan uncomment routes

#### 2. **Credit Notes**
- ❌ Menu "Credit Notes" dihapus dari sidebar  
- ❌ Routes `/credit-notes/*` dinonaktifkan
- ℹ️ Model, migration, dan controller masih ada di codebase (tidak dihapus untuk backward compatibility)
- ℹ️ Jika diperlukan di masa depan, bisa diaktifkan kembali dengan uncomment routes

---

### ✨ Fitur Baru: Auto Credit Limit Setup

#### Setup Plafon Kredit Otomatis

Sistem sekarang otomatis membuat credit limit untuk setiap organisasi berdasarkan tipe:

| Tipe Organisasi | Plafon Kredit Default |
|-----------------|----------------------|
| **Hospital / RS** | Rp 20.000.000.000 (20 Miliar) |
| **Clinic / Klinik** | Rp 500.000.000 (500 Juta) |
| **Default** | Rp 500.000.000 (500 Juta) |

#### Implementasi

1. **Migration**: `2026_04_21_181200_add_default_credit_limits_to_organizations.php`
   - Membuat credit limit untuk semua organisasi existing
   - Menggunakan SQL INSERT dengan CASE statement untuk set limit berdasarkan type

2. **Observer**: `OrganizationObserver.php`
   - Auto-create credit limit saat organisasi baru dibuat
   - Menggunakan match expression untuk determine default limit

3. **Registration**: `AppServiceProvider.php`
   - Observer di-register di boot method

#### Cara Kerja

**Untuk Organisasi Existing:**
```bash
php artisan migrate
```
Migration akan otomatis membuat credit limit untuk semua organisasi yang belum punya.

**Untuk Organisasi Baru:**
Saat membuat organisasi baru (via form atau seeder), credit limit otomatis dibuat dengan plafon sesuai tipe.

#### Contoh

```php
// Membuat RS baru
$hospital = Organization::create([
    'name' => 'RS Harapan Sehat',
    'type' => 'hospital',
    // ... fields lainnya
]);

// Credit limit otomatis dibuat dengan max_limit = 20.000.000.000

// Membuat Klinik baru
$clinic = Organization::create([
    'name' => 'Klinik Sehat Sentosa',
    'type' => 'clinic',
    // ... fields lainnya
]);

// Credit limit otomatis dibuat dengan max_limit = 500.000.000
```

#### Catatan Penting

- ✅ Credit limit bisa diubah manual via Credit Control menu
- ✅ Default limit hanya applied saat create, tidak override existing limits
- ✅ Backward compatible - organisasi lama yang sudah punya custom limit tidak terpengaruh
- ✅ Case-insensitive matching untuk type ('Hospital', 'hospital', 'HOSPITAL' semua valid)

---

## Files Modified

### Deleted/Disabled Features
- `resources/views/components/partials/sidebar.blade.php` - Removed Price Lists & Credit Notes menu
- `routes/web.php` - Commented out Price Lists & Credit Notes routes

### New Features
- `database/migrations/2026_04_21_181200_add_default_credit_limits_to_organizations.php` - Migration
- `app/Observers/OrganizationObserver.php` - Auto-create credit limits
- `app/Providers/AppServiceProvider.php` - Register observer

---

## Testing

### Test Credit Limit Auto-Creation

```bash
# Test via tinker
php artisan tinker

# Create new hospital
$hospital = Organization::create([
    'name' => 'Test Hospital',
    'type' => 'hospital',
    'code' => 'TST-HOSP',
    'is_active' => true
]);

# Check credit limit
$hospital->creditLimit; // Should show max_limit = 20000000000.00

# Create new clinic
$clinic = Organization::create([
    'name' => 'Test Clinic',
    'type' => 'clinic',
    'code' => 'TST-CLIN',
    'is_active' => true
]);

# Check credit limit
$clinic->creditLimit; // Should show max_limit = 500000000.00
```

---

## Rollback (if needed)

Jika perlu rollback:

```bash
# Rollback migration
php artisan migrate:rollback --step=1

# Uncomment routes di routes/web.php
# Uncomment menu items di sidebar.blade.php
```
