# Master Data Seeding Guide
## Medikindo PO System

**Last Updated**: 13 April 2026  
**Status**: ✅ Ready to Use

---

## 📋 Overview

Panduan ini menjelaskan cara menambahkan data dummy untuk master data sistem Medikindo PO, termasuk:
- **Organizations** (Rumah Sakit, Klinik, Puskesmas)
- **Suppliers** (Perusahaan Farmasi)
- **Products** (Obat-obatan dan Alat Kesehatan)

---

## 🎯 What Will Be Created

### Organizations (8 entries)
1. **RS Umum Medika Utama** (RSU-MU) - Hospital
2. **Klinik Sehat Sentosa** (KLN-SS) - Clinic
3. **RS Harapan Bunda** (RSU-HB) - Hospital
4. **Klinik Pratama Husada** (KLN-PH) - Clinic
5. **RS Ibu dan Anak Permata** (RSIA-PM) - Hospital
6. **Puskesmas Cempaka Putih** (PKM-CP) - Puskesmas
7. **Klinik Spesialis Jantung Sehat** (KLN-JS) - Clinic
8. **RS Ortopedi Prima** (RSO-PR) - Hospital

**Features**:
- ✅ Complete contact information
- ✅ Default tax rate (11%)
- ✅ Default discount percentage (0-7%)
- ✅ Active status

### Suppliers (12 entries)
1. **PT Kimia Farma Trading & Distribution** (KFTD)
2. **PT Kalbe Farma Tbk** (KLBF)
3. **PT Sanbe Farma** (SNBF)
4. **PT Indofarma Global Medika** (INAF)
5. **PT Tempo Scan Pacific Tbk** (TSPC)
6. **PT Dexa Medica** (DXMD)
7. **PT Pharos Indonesia** (PHAR)
8. **PT Merck Indonesia** (MERK)
9. **PT Novartis Indonesia** (NOVA)
10. **PT Sanofi-Aventis Indonesia** (SNFI)
11. **PT Bayer Indonesia** (BAYR)
12. **PT Pfizer Indonesia** (PFIZ)

**Features**:
- ✅ Real pharmaceutical company names
- ✅ Complete contact information
- ✅ Tax ID (NPWP)
- ✅ Active status

### Products (100+ entries)

#### Categories:
1. **Obat-obatan Umum** (15 items)
   - Paracetamol, Amoxicillin, Omeprazole, Cetirizine, Metformin, dll.

2. **Obat Narkotika** (2 items)
   - Morphine Sulfate, Tramadol (marked as narcotic)

3. **Alat Kesehatan** (5 items)
   - Sarung Tangan, Masker, Spuit, Infus Set, Kateter

4. **Cairan Infus** (3 items)
   - NaCl 0.9%, Ringer Laktat, Dextrose 5%

5. **Antiseptik & Desinfektan** (3 items)
   - Alkohol 70%, Betadine, Hand Sanitizer

6. **Perban & Plester** (3 items)
   - Kasa Steril, Perban Elastis, Plester Micropore

7. **Vitamin & Suplemen** (3 items)
   - Vitamin C, Vitamin B Complex, Multivitamin

8. **Obat Injeksi** (3 items)
   - Ceftriaxone, Dexamethasone, Ranitidine

**Features**:
- ✅ Realistic product names and SKUs
- ✅ Detailed descriptions
- ✅ Price range: Rp 5,000 - Rp 75,000
- ✅ Unit specifications
- ✅ Narcotic flag for controlled substances
- ✅ Each supplier gets 10 products

---

## 🚀 How to Run

### Method 1: Run All Seeders (Recommended)

This will seed users, roles, permissions, AND master data:

```bash
php artisan migrate:fresh --seed
```

⚠️ **WARNING**: This will drop all tables and recreate them!

### Method 2: Run Master Data Only

If you already have users and want to add master data only:

```bash
php artisan db:seed --class=MasterDataSeeder
```

### Method 3: Run Individual Seeders

```bash
# Organizations only
php artisan db:seed --class=OrganizationSeeder

# Suppliers only
php artisan db:seed --class=SupplierSeeder

# Products only (requires suppliers)
php artisan db:seed --class=ProductSeeder
```

### Method 4: Use Scripts

**Windows (PowerShell)**:
```powershell
.\scripts\seed-master-data.ps1
```

**Linux/Mac (Bash)**:
```bash
chmod +x scripts/seed-master-data.sh
./scripts/seed-master-data.sh
```

---

## 🧪 Verification

### Check Data Count

```bash
php artisan tinker
```

Then in tinker:
```php
App\Models\Organization::count();  // Should be 8
App\Models\Supplier::count();      // Should be 12
App\Models\Product::count();       // Should be 100+
```

### View Sample Data

```php
// View organizations
App\Models\Organization::select('name', 'code', 'type')->get();

// View suppliers
App\Models\Supplier::select('name', 'code')->get();

// View products for a supplier
$supplier = App\Models\Supplier::first();
$supplier->products()->select('name', 'sku', 'price')->get();
```

---

## 📊 Database Structure

### Organizations Table
```
- id
- name (e.g., "RS Umum Medika Utama")
- code (e.g., "RSU-MU")
- type (hospital/clinic/puskesmas)
- address
- phone
- email
- contact_person
- is_active
- default_tax_rate (11.00)
- default_discount_percentage (0-7%)
```

### Suppliers Table
```
- id
- name (e.g., "PT Kimia Farma Trading & Distribution")
- code (e.g., "KFTD")
- address
- phone
- email
- contact_person
- tax_id (NPWP)
- is_active
```

### Products Table
```
- id
- supplier_id (foreign key)
- name (e.g., "Paracetamol 500mg")
- sku (e.g., "MED-PARA-500-KFTD")
- description
- price (decimal 18,2)
- unit (e.g., "Strip (10 tablet)")
- is_narcotic (boolean)
- is_active
```

---

## 🔄 Re-seeding

### If Data Already Exists

The seeders are **idempotent** - they check if data exists before creating:
- Organizations: Checked by `code`
- Suppliers: Checked by `code`
- Products: Checked by `supplier_id` + `sku`

Running the seeder multiple times will:
- ✅ Skip existing records
- ✅ Create only new records
- ✅ Show summary of created vs skipped

### Force Re-seed (Delete All)

```bash
# Delete all master data
php artisan tinker
```

```php
App\Models\Product::truncate();
App\Models\Supplier::truncate();
App\Models\Organization::truncate();
exit
```

Then run seeder again:
```bash
php artisan db:seed --class=MasterDataSeeder
```

---

## 🎯 Usage Example

### Create Purchase Order with Dummy Data

1. **Login as Healthcare User**:
   - Email: `budi.santoso@testhospital.com`
   - Password: `Healthcare@2026!`

2. **Navigate to**: `/purchase-orders/create`

3. **Select Organization**: Test Hospital

4. **Select Supplier**: PT Kimia Farma Trading & Distribution

5. **Click "Tambah Produk"**

6. **Select Product**: Paracetamol 500mg
   - Unit price will auto-fill: Rp 5,000
   - Enter quantity: 10
   - Subtotal: Rp 50,000

7. **Add more products** as needed

8. **Submit PO**

---

## 📝 Customization

### Add More Organizations

Edit `database/seeders/OrganizationSeeder.php`:

```php
[
    'name' => 'Your Hospital Name',
    'code' => 'YOUR-CODE',
    'type' => 'hospital', // or 'clinic', 'puskesmas'
    'address' => 'Your Address',
    'phone' => '021-1234567',
    'email' => 'info@yourhospital.com',
    'contact_person' => 'Dr. Your Name',
    'is_active' => true,
    'default_tax_rate' => 11.00,
    'default_discount_percentage' => 5.00,
],
```

### Add More Suppliers

Edit `database/seeders/SupplierSeeder.php`:

```php
[
    'name' => 'PT Your Supplier Name',
    'code' => 'YOUR',
    'address' => 'Your Address',
    'phone' => '021-1234567',
    'email' => 'info@yoursupplier.com',
    'contact_person' => 'Your Contact',
    'tax_id' => '01.234.567.8-901.000',
    'is_active' => true,
],
```

### Add More Products

Edit `database/seeders/ProductSeeder.php`:

```php
[
    'name' => 'Your Product Name',
    'sku' => 'YOUR-SKU',
    'description' => 'Product description',
    'price' => 10000.00,
    'unit' => 'Box',
    'is_narcotic' => false,
    'is_active' => true,
],
```

---

## 🐛 Troubleshooting

### Error: "No suppliers found"

**Solution**: Run SupplierSeeder first:
```bash
php artisan db:seed --class=SupplierSeeder
```

### Error: "Duplicate entry for key 'code'"

**Solution**: Data already exists. Either:
1. Skip (seeder will handle it)
2. Delete existing data first
3. Change the code in seeder

### Products Not Showing in PO Form

**Checklist**:
1. ✅ Supplier has products: `Supplier::find(1)->products()->count()`
2. ✅ Products are active: `Product::where('is_active', true)->count()`
3. ✅ Clear cache: `php artisan cache:clear`
4. ✅ Hard refresh browser: `Ctrl + Shift + R`

---

## 📚 Related Documentation

- `USER_CREDENTIALS.md` - User accounts for testing
- `BUSINESS_LOGIC_AUDIT_REPORT.md` - System business logic
- `PO_BUTTON_FIX_FINAL.md` - Purchase Order form fixes

---

## ✅ Summary

### Quick Start
```bash
# 1. Run all seeders (includes master data)
php artisan migrate:fresh --seed

# 2. Verify
php artisan tinker
App\Models\Organization::count();  // 8
App\Models\Supplier::count();      // 12
App\Models\Product::count();       // 100+

# 3. Login and test
# Email: budi.santoso@testhospital.com
# Password: Healthcare@2026!
```

### What You Get
- ✅ 8 Organizations (hospitals, clinics, puskesmas)
- ✅ 12 Suppliers (real pharmaceutical companies)
- ✅ 100+ Products (medicines, medical supplies)
- ✅ Realistic data for testing
- ✅ Ready to create Purchase Orders

---

**Status**: ✅ Ready to Use  
**Last Updated**: 13 April 2026  
**Tested**: ✅ Verified Working

**Selamat menggunakan data dummy! 🎉**
