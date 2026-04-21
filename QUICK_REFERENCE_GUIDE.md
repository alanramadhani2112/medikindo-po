# QUICK REFERENCE GUIDE - Product Master Data Refactoring

## 🚀 Quick Start

### Run Migrations
```bash
php artisan migrate
```

### Seed Units
```bash
php artisan db:seed --class=UnitsSeeder
```

### Normalize Existing Products
```bash
php artisan db:seed --class=NormalizeProductUnitsSeeder
```

### Populate Unit IDs
```bash
php artisan db:seed --class=PopulateInventoryUnitsSeeder
php artisan db:seed --class=PopulatePOUnitsSeeder
```

---

## 📚 Code Examples

### 1. Unit Conversion

```php
use App\Services\UnitConversionService;

$service = new UnitConversionService();

// Convert 2 Box to Pcs
$pcs = $service->convert(
    productId: 1,
    quantity: 2,
    fromUnitId: 6, // Box
    toUnitId: 1    // Pcs
);

// Convert to base unit
$baseQty = $service->toBaseUnit(
    productId: 1,
    quantity: 5,
    unitId: 6 // Box
);

// Get available units
$units = $service->getAvailableUnits(productId: 1);
```

### 2. Product Model Helpers

```php
$product = Product::find(1);

// Get base unit
$baseUnit = $product->baseUnit;

// Get all units
$units = $product->units;

// Convert using model
$baseQty = $product->toBaseUnit(quantity: 3, unitId: 6);

// Check compliance
if ($product->requiresSpecialApproval()) {
    // High risk product
}

if ($product->isRegistrationExpired()) {
    // Cannot sell
}
```

### 3. Query Examples

```php
// Get products by type
$alkes = Product::where('product_type', 'ALKES')->get();

// Get high-risk products
$highRisk = Product::whereIn('risk_class', ['D', '3'])->get();

// Get products with packaging units
$multiUnit = Product::whereHas('productUnits', function($q) {
    $q->where('is_base_unit', false);
})->get();

// Get expiring registrations
$expiring = Product::whereNotNull('registration_expiry')
    ->whereDate('registration_expiry', '<=', now()->addDays(90))
    ->get();
```

---

## 🗂️ Database Schema

### units
```sql
id, name, symbol, type, description, is_active
```

### product_units
```sql
id, product_id, unit_id, conversion_to_base, 
is_base_unit, is_default_purchase, is_default_sales, barcode
```

### products (new fields)
```sql
-- Compliance
product_type, risk_class, intended_use, usage_method, target_user

-- Regulatory
registration_number, registration_date, registration_expiry,
manufacturer, country_of_origin, is_sterile, sterilization_method

-- Unit
base_unit_id

-- Stock
min_stock_level, max_stock_level, reorder_quantity,
storage_temperature, storage_condition, special_handling
```

---

## 🎯 Constants Reference

### Product Types
```php
'ALKES' => 'Alat Kesehatan'
'ALKES_DIV' => 'Alat Kesehatan Diagnostik In Vitro'
'PKRT' => 'Perbekalan Kesehatan Rumah Tangga'
```

### Risk Class (ALKES)
```php
'A' => 'Class A - Risiko Rendah'
'B' => 'Class B - Risiko Sedang-Rendah'
'C' => 'Class C - Risiko Sedang-Tinggi'
'D' => 'Class D - Risiko Tinggi'
```

### Risk Class (PKRT)
```php
'1' => 'Class 1 - Risiko Rendah'
'2' => 'Class 2 - Risiko Sedang'
'3' => 'Class 3 - Risiko Tinggi'
```

### Usage Methods
```php
'single_use' => 'Single Use (Sekali Pakai)'
'reusable' => 'Reusable (Dapat Digunakan Ulang)'
'sterilizable' => 'Sterilizable (Dapat Disterilkan)'
```

---

## 🔧 Troubleshooting

### Issue: Products without base_unit_id
```bash
php artisan db:seed --class=NormalizeProductUnitsSeeder
```

### Issue: Inventory items without unit_id
```bash
php artisan db:seed --class=PopulateInventoryUnitsSeeder
```

### Issue: PO items without unit_id
```bash
php artisan db:seed --class=PopulatePOUnitsSeeder
```

### Issue: Need to rollback
```bash
php artisan migrate:rollback --step=8
```

---

## 📖 Documentation Files

1. `AUDIT_PRODUCT_MASTER_DATA.md` - System audit
2. `GAP_ANALYSIS_PRODUCT_MASTER.md` - Gap analysis
3. `STEP3_MIGRATION_PLAN.md` - Migration plan
4. `STEP4_IMPLEMENTATION_COMPLETE.md` - Implementation results
5. `STEP5_VALIDATION_REPORT.md` - Test results
6. `STEP6_FORMS_CONTROLLERS_UPDATE.md` - Form update guide
7. `PRODUCT_MASTER_REFACTORING_COMPLETE.md` - Complete summary

---

## ✅ Checklist

### Deployment
- [ ] Run migrations
- [ ] Seed units
- [ ] Normalize products
- [ ] Populate unit IDs
- [ ] Validate data integrity

### Data Population
- [ ] Populate product_type (119 products)
- [ ] Populate risk_class (119 products)
- [ ] Populate intended_use
- [ ] Populate registration numbers (optional)

### UI Updates
- [ ] Update product create form
- [ ] Update product edit form
- [ ] Add JavaScript conditional logic
- [ ] Test form submission

### Testing
- [ ] Test unit conversion
- [ ] Test compliance validation
- [ ] Test inventory integration
- [ ] Test PO integration
- [ ] User acceptance testing

---

**Last Updated:** 21 April 2026  
**Version:** 1.0
