# MIGRATION PLAN - PRODUCT MASTER DATA REFACTORING
**Tanggal:** 21 April 2026  
**Status:** STEP 3 - MIGRATION PLAN (SAFE MODE) ✅

---

## 📋 EXECUTIVE SUMMARY

Dokumen ini berisi **RENCANA MIGRASI LENGKAP** untuk refactoring Product Master Data dengan prinsip:
- ✅ **ZERO DOWNTIME** - sistem tetap jalan
- ✅ **BACKWARD COMPATIBLE** - fitur lama tidak rusak
- ✅ **SAFE MODE** - nullable first, enforce later
- ✅ **ROLLBACK READY** - bisa rollback jika ada masalah

**TOTAL MIGRATIONS:** 8 migration files  
**ESTIMATED TIME:** 2-3 jam untuk run semua migrations  
**DATA MIGRATION:** 1-2 jam untuk normalisasi data existing

---

## 1️⃣ MIGRATION STRATEGY

### 1.1 Prinsip SAFE MODE

```
FASE 1: ADD NEW STRUCTURE (nullable)
├─ Semua kolom baru = nullable
├─ Tidak ada constraint ketat
├─ Sistem lama tetap jalan
└─ Testing parallel

FASE 2: DATA NORMALIZATION
├─ Parse existing data
├─ Populate kolom baru
├─ Validate data integrity
└─ Fix inconsistencies

FASE 3: GRADUAL ENFORCEMENT
├─ Make fields NOT NULL (bertahap)
├─ Add constraints
├─ Update validation rules
└─ Deprecate old fields (tapi JANGAN hapus)

FASE 4: MONITORING
├─ Monitor data quality
├─ Track errors
├─ User feedback
└─ Continuous improvement
```

### 1.2 Rollback Strategy

Setiap migration HARUS punya `down()` method yang berfungsi:
```php
public function down(): void
{
    // Rollback changes
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['product_type', 'risk_class']);
    });
}
```

---

## 2️⃣ MIGRATION FILES

### Migration 1: Create Units Table
**File:** `2026_04_21_100001_create_units_table.php`  
**Purpose:** Master table untuk semua units  
**Risk:** 🟢 LOW (tabel baru, tidak affect existing)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Nama unit: Pcs, Box, Strip, dll');
            $table->string('symbol', 10)->nullable()->comment('Simbol: pcs, box, strip');
            $table->enum('type', ['base', 'packaging', 'volume', 'weight', 'bundle'])
                  ->comment('Tipe unit untuk grouping');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
```

---

### Migration 2: Create Product Units Pivot Table
**File:** `2026_04_21_100002_create_product_units_table.php`  
**Purpose:** Many-to-many relationship products <-> units  
**Risk:** 🟢 LOW (tabel baru, tidak affect existing)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('conversion_to_base', 10, 4)->default(1.0000)
                  ->comment('Conversion ratio ke base unit. Contoh: 1 Box = 50 Pcs → 50.0000');
            $table->boolean('is_base_unit')->default(false)
                  ->comment('Flag untuk base unit (hanya 1 per product)');
            $table->boolean('is_default_purchase')->default(false)
                  ->comment('Unit default untuk pembelian');
            $table->boolean('is_default_sales')->default(false)
                  ->comment('Unit default untuk penjualan');
            $table->string('barcode', 100)->nullable()
                  ->comment('Barcode khusus untuk unit ini (optional)');
            $table->timestamps();
            
            // Unique constraint: 1 product tidak bisa punya unit yang sama 2x
            $table->unique(['product_id', 'unit_id'], 'unique_product_unit');
            
            $table->index('product_id');
            $table->index('unit_id');
            $table->index('is_base_unit');
            $table->index('is_default_purchase');
            $table->index('is_default_sales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
```

---

### Migration 3: Add Compliance Fields to Products
**File:** `2026_04_21_100003_add_compliance_fields_to_products.php`  
**Purpose:** Product type, risk class, intended use, usage method  
**Risk:** 🟡 MEDIUM (ubah tabel existing, tapi nullable)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Product Classification
            $table->enum('product_type', ['ALKES', 'ALKES_DIV', 'PKRT'])
                  ->nullable()
                  ->after('category')
                  ->comment('Tipe produk: ALKES / ALKES_DIV / PKRT');
            
            $table->string('risk_class', 10)
                  ->nullable()
                  ->after('product_type')
                  ->comment('Risk class: A-D (ALKES) atau 1-3 (PKRT)');
            
            // Intended Use & Usage Method
            $table->text('intended_use')
                  ->nullable()
                  ->after('risk_class')
                  ->comment('Tujuan penggunaan produk');
            
            $table->enum('usage_method', ['single_use', 'reusable', 'sterilizable'])
                  ->nullable()
                  ->after('intended_use')
                  ->comment('Metode penggunaan');
            
            $table->string('target_user', 50)
                  ->nullable()
                  ->after('usage_method')
                  ->comment('Target pengguna: healthcare_professional / consumer / both');
            
            // Indexes
            $table->index('product_type');
            $table->index('risk_class');
            $table->index('usage_method');
            $table->index('target_user');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['product_type']);
            $table->dropIndex(['risk_class']);
            $table->dropIndex(['usage_method']);
            $table->dropIndex(['target_user']);
            
            $table->dropColumn([
                'product_type',
                'risk_class',
                'intended_use',
                'usage_method',
                'target_user',
            ]);
        });
    }
};
```

---

### Migration 4: Add Regulatory Fields to Products
**File:** `2026_04_21_100004_add_regulatory_fields_to_products.php`  
**Purpose:** Registration number, manufacturer, sterilization  
**Risk:** 🟢 LOW (kolom baru, nullable)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Registration Information
            $table->string('registration_number', 50)
                  ->nullable()
                  ->unique()
                  ->after('sku')
                  ->comment('Nomor Izin Edar (NIE): AKL/AKD/AKP');
            
            $table->date('registration_date')
                  ->nullable()
                  ->after('registration_number')
                  ->comment('Tanggal izin edar diterbitkan');
            
            $table->date('registration_expiry')
                  ->nullable()
                  ->after('registration_date')
                  ->comment('Tanggal kadaluarsa izin edar');
            
            // Manufacturer Information
            $table->string('manufacturer', 255)
                  ->nullable()
                  ->after('supplier_id')
                  ->comment('Nama produsen/manufacturer');
            
            $table->string('country_of_origin', 100)
                  ->nullable()
                  ->after('manufacturer')
                  ->comment('Negara asal produk');
            
            // Sterilization
            $table->boolean('is_sterile')
                  ->default(false)
                  ->after('is_narcotic')
                  ->comment('Apakah produk steril');
            
            $table->enum('sterilization_method', ['ETO', 'Steam', 'Radiation', 'Other', 'None'])
                  ->nullable()
                  ->after('is_sterile')
                  ->comment('Metode sterilisasi');
            
            // Indexes
            $table->index('registration_number');
            $table->index('registration_expiry');
            $table->index('country_of_origin');
            $table->index('is_sterile');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['registration_number']);
            $table->dropIndex(['registration_expiry']);
            $table->dropIndex(['country_of_origin']);
            $table->dropIndex(['is_sterile']);
            
            $table->dropColumn([
                'registration_number',
                'registration_date',
                'registration_expiry',
                'manufacturer',
                'country_of_origin',
                'is_sterile',
                'sterilization_method',
            ]);
        });
    }
};
```

---

### Migration 5: Add Base Unit Reference to Products
**File:** `2026_04_21_100005_add_base_unit_to_products.php`  
**Purpose:** Foreign key ke units table untuk base unit  
**Risk:** 🟡 MEDIUM (FK baru, nullable)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('base_unit_id')
                  ->nullable()
                  ->after('unit')
                  ->constrained('units')
                  ->restrictOnDelete()
                  ->comment('Base unit untuk produk ini (FK ke units table)');
            
            $table->index('base_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['base_unit_id']);
            $table->dropIndex(['base_unit_id']);
            $table->dropColumn('base_unit_id');
        });
    }
};
```

---

### Migration 6: Add Stock Management Fields to Products
**File:** `2026_04_21_100006_add_stock_management_to_products.php`  
**Purpose:** Min/max stock, reorder quantity, storage requirements  
**Risk:** 🟢 LOW (kolom baru, nullable)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Stock Management
            $table->decimal('min_stock_level', 10, 2)
                  ->nullable()
                  ->after('is_active')
                  ->comment('Minimum stock level (reorder point) dalam base unit');
            
            $table->decimal('max_stock_level', 10, 2)
                  ->nullable()
                  ->after('min_stock_level')
                  ->comment('Maximum stock level dalam base unit');
            
            $table->decimal('reorder_quantity', 10, 2)
                  ->nullable()
                  ->after('max_stock_level')
                  ->comment('Quantity untuk reorder dalam base unit');
            
            // Storage Requirements
            $table->string('storage_temperature', 50)
                  ->nullable()
                  ->after('reorder_quantity')
                  ->comment('Suhu penyimpanan: 2-8°C, 15-25°C, dll');
            
            $table->text('storage_condition')
                  ->nullable()
                  ->after('storage_temperature')
                  ->comment('Kondisi penyimpanan: dry, cool, protected from light');
            
            $table->text('special_handling')
                  ->nullable()
                  ->after('storage_condition')
                  ->comment('Handling khusus: fragile, hazardous, dll');
            
            // Indexes
            $table->index('min_stock_level');
            $table->index('storage_temperature');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['min_stock_level']);
            $table->dropIndex(['storage_temperature']);
            
            $table->dropColumn([
                'min_stock_level',
                'max_stock_level',
                'reorder_quantity',
                'storage_temperature',
                'storage_condition',
                'special_handling',
            ]);
        });
    }
};
```

---

### Migration 7: Add Unit Tracking to Inventory Items
**File:** `2026_04_21_100007_add_unit_to_inventory_items.php`  
**Purpose:** Track unit yang digunakan di inventory  
**Risk:** 🟡 MEDIUM (ubah tabel existing, nullable)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('units')
                  ->restrictOnDelete()
                  ->comment('Unit yang digunakan untuk quantity (FK ke units)');
            
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
```

---

### Migration 8: Add Unit Tracking to Purchase Order Items
**File:** `2026_04_21_100008_add_unit_to_purchase_order_items.php`  
**Purpose:** Track unit yang digunakan di PO  
**Risk:** 🟡 MEDIUM (ubah tabel existing, nullable)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('units')
                  ->restrictOnDelete()
                  ->comment('Unit yang digunakan untuk quantity (FK ke units)');
            
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
```

---

## 3️⃣ DATA SEEDER

### Seeder 1: Units Seeder
**File:** `database/seeders/UnitsSeeder.php`  
**Purpose:** Populate master units

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // Base Units
            ['name' => 'Pcs', 'symbol' => 'pcs', 'type' => 'base', 'description' => 'Piece - satuan terkecil'],
            ['name' => 'Tablet', 'symbol' => 'tab', 'type' => 'base', 'description' => 'Tablet obat'],
            ['name' => 'Kapsul', 'symbol' => 'kap', 'type' => 'base', 'description' => 'Kapsul obat'],
            ['name' => 'Ampul', 'symbol' => 'amp', 'type' => 'base', 'description' => 'Ampul injeksi'],
            ['name' => 'Vial', 'symbol' => 'vial', 'type' => 'base', 'description' => 'Vial injeksi'],
            
            // Packaging Units
            ['name' => 'Box', 'symbol' => 'box', 'type' => 'packaging', 'description' => 'Box/kotak kemasan'],
            ['name' => 'Strip', 'symbol' => 'strip', 'type' => 'packaging', 'description' => 'Strip blister'],
            ['name' => 'Botol', 'symbol' => 'btl', 'type' => 'packaging', 'description' => 'Botol'],
            ['name' => 'Pack', 'symbol' => 'pack', 'type' => 'packaging', 'description' => 'Pack/paket'],
            ['name' => 'Karton', 'symbol' => 'ctn', 'type' => 'packaging', 'description' => 'Karton besar'],
            
            // Volume Units
            ['name' => 'Liter', 'symbol' => 'L', 'type' => 'volume', 'description' => 'Liter'],
            ['name' => 'Mililiter', 'symbol' => 'mL', 'type' => 'volume', 'description' => 'Mililiter'],
            
            // Weight Units
            ['name' => 'Gram', 'symbol' => 'g', 'type' => 'weight', 'description' => 'Gram'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight', 'description' => 'Kilogram'],
            
            // Bundle Units
            ['name' => 'Set', 'symbol' => 'set', 'type' => 'bundle', 'description' => 'Set lengkap'],
            ['name' => 'Roll', 'symbol' => 'roll', 'type' => 'bundle', 'description' => 'Roll/gulungan'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->insert([
                'name' => $unit['name'],
                'symbol' => $unit['symbol'],
                'type' => $unit['type'],
                'description' => $unit['description'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ Units seeded successfully: ' . count($units) . ' units');
    }
}
```

---

## 4️⃣ DATA NORMALIZATION SCRIPT

### Script: Normalize Existing Product Units
**File:** `database/seeders/NormalizeProductUnitsSeeder.php`  
**Purpose:** Parse existing products.unit string dan populate product_units table

```php
<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NormalizeProductUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting product units normalization...');
        
        $products = Product::all();
        $totalProcessed = 0;
        $totalErrors = 0;

        foreach ($products as $product) {
            try {
                $this->normalizeProductUnit($product);
                $totalProcessed++;
            } catch (\Exception $e) {
                $this->command->error("Error processing product {$product->id}: {$e->getMessage()}");
                $totalErrors++;
            }
        }

        $this->command->info("✓ Normalization completed:");
        $this->command->info("  - Processed: {$totalProcessed} products");
        $this->command->info("  - Errors: {$totalErrors} products");
    }

    private function normalizeProductUnit(Product $product): void
    {
        $unitString = $product->unit; // Contoh: "Box (50 pcs)", "Strip (10 tablet)"
        
        // Parse unit string
        $parsed = $this->parseUnitString($unitString);
        
        // Get or create base unit
        $baseUnit = DB::table('units')
            ->where('name', $parsed['base_unit_name'])
            ->first();
        
        if (!$baseUnit) {
            $this->command->warn("Base unit not found: {$parsed['base_unit_name']} for product {$product->id}");
            return;
        }
        
        // Update product.base_unit_id
        $product->update(['base_unit_id' => $baseUnit->id]);
        
        // Create product_units record for base unit
        DB::table('product_units')->insert([
            'product_id' => $product->id,
            'unit_id' => $baseUnit->id,
            'conversion_to_base' => 1.0000,
            'is_base_unit' => true,
            'is_default_purchase' => true,
            'is_default_sales' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // If packaging unit exists, create record
        if ($parsed['packaging_unit_name'] && $parsed['conversion_ratio'] > 1) {
            $packagingUnit = DB::table('units')
                ->where('name', $parsed['packaging_unit_name'])
                ->first();
            
            if ($packagingUnit) {
                DB::table('product_units')->insert([
                    'product_id' => $product->id,
                    'unit_id' => $packagingUnit->id,
                    'conversion_to_base' => $parsed['conversion_ratio'],
                    'is_base_unit' => false,
                    'is_default_purchase' => true,
                    'is_default_sales' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function parseUnitString(string $unitString): array
    {
        // Pattern 1: "Box (50 pcs)" → packaging: Box, base: Pcs, ratio: 50
        if (preg_match('/^(\w+)\s*\((\d+)\s*(\w+)\)$/i', $unitString, $matches)) {
            return [
                'packaging_unit_name' => ucfirst(strtolower($matches[1])),
                'conversion_ratio' => (float) $matches[2],
                'base_unit_name' => ucfirst(strtolower($matches[3])),
            ];
        }
        
        // Pattern 2: "Strip (10 tablet)" → packaging: Strip, base: Tablet, ratio: 10
        if (preg_match('/^(\w+)\s*\((\d+)\s*(\w+)\)$/i', $unitString, $matches)) {
            return [
                'packaging_unit_name' => ucfirst(strtolower($matches[1])),
                'conversion_ratio' => (float) $matches[2],
                'base_unit_name' => ucfirst(strtolower($matches[3])),
            ];
        }
        
        // Pattern 3: "Pcs", "Ampul", "Vial" → base unit only
        return [
            'packaging_unit_name' => null,
            'conversion_ratio' => 1.0,
            'base_unit_name' => ucfirst(strtolower($unitString)),
        ];
    }
}
```

---

## 5️⃣ EXECUTION PLAN

### Phase 1: Run Migrations (30 menit)

```bash
# Backup database first!
php artisan db:backup

# Run migrations
php artisan migrate

# Expected output:
# Migrating: 2026_04_21_100001_create_units_table
# Migrated:  2026_04_21_100001_create_units_table (45.67ms)
# Migrating: 2026_04_21_100002_create_product_units_table
# Migrated:  2026_04_21_100002_create_product_units_table (52.34ms)
# ... (8 migrations total)
```

### Phase 2: Seed Units (5 menit)

```bash
php artisan db:seed --class=UnitsSeeder

# Expected output:
# ✓ Units seeded successfully: 16 units
```

### Phase 3: Normalize Existing Data (1-2 jam)

```bash
php artisan db:seed --class=NormalizeProductUnitsSeeder

# Expected output:
# Starting product units normalization...
# ✓ Normalization completed:
#   - Processed: 150 products
#   - Errors: 0 products
```

### Phase 4: Validate Data (30 menit)

```bash
# Check data integrity
php artisan tinker

# Run validation queries:
>>> Product::whereNull('base_unit_id')->count()
=> 0  // Should be 0

>>> DB::table('product_units')->where('is_base_unit', true)->count()
=> 150  // Should equal total products

>>> DB::table('product_units')->whereRaw('conversion_to_base < 1')->count()
=> 0  // Should be 0 (conversion must be >= 1)
```

---

## 6️⃣ BACKWARD COMPATIBILITY

### 6.1 Old Code Still Works

**Existing code:**
```php
// Old way - masih jalan
$product->unit; // "Box (50 pcs)"
```

**New code:**
```php
// New way - lebih powerful
$product->baseUnit->name; // "Pcs"
$product->productUnits; // Collection of units
```

### 6.2 Deprecation Strategy

```php
// products.unit field = DEPRECATED tapi TIDAK DIHAPUS
// Add comment di migration:
$table->string('unit', 30)
      ->comment('DEPRECATED: Use base_unit_id and product_units table instead');
```

---

## 7️⃣ ROLLBACK PLAN

### If Something Goes Wrong:

```bash
# Rollback all migrations
php artisan migrate:rollback --step=8

# Restore database from backup
mysql -u root -p medikindo_po < backup_before_migration.sql

# Verify data
php artisan tinker
>>> Product::count()
>>> InventoryItem::count()
```

---

## 8️⃣ TESTING CHECKLIST

### Pre-Migration Tests
- [ ] Backup database berhasil
- [ ] All existing tests passing
- [ ] No pending migrations

### Post-Migration Tests
- [ ] All migrations ran successfully
- [ ] Units table populated (16 units)
- [ ] All products have base_unit_id
- [ ] All products have product_units records
- [ ] No data loss (product count sama)
- [ ] Existing features still work
- [ ] Rollback works

---

## ✅ MIGRATION PLAN CONCLUSION

**TOTAL MIGRATIONS:** 8 files  
**TOTAL SEEDERS:** 2 files  
**ESTIMATED TIME:** 2-3 jam  
**RISK LEVEL:** 🟡 MEDIUM (manageable dengan backup)

**NEXT STEP:** Proceed to **STEP 4 - IMPLEMENTATION**

---

**Prepared by:** Kiro AI System Architect  
**Date:** 21 April 2026  
**Document Version:** 1.0
