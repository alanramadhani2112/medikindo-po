# Quick Commands Reference
## Medikindo PO System

**Last Updated**: 13 April 2026

---

## 🚀 Setup & Installation

### Fresh Install (Complete Reset)
```bash
# Drop all tables, recreate, and seed
php artisan migrate:fresh --seed
```

### Step-by-Step Install
```bash
# 1. Run migrations
php artisan migrate

# 2. Seed roles and permissions
php artisan db:seed --class=RolePermissionSeeder

# 3. Seed users (4 users, 1 per role)
php artisan db:seed --class=CleanUserSeeder

# 4. Seed master data (organizations, suppliers, products)
php artisan db:seed --class=MasterDataSeeder
```

---

## 🗄️ Database Commands

### Seeding
```bash
# Seed everything
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=MasterDataSeeder
php artisan db:seed --class=OrganizationSeeder
php artisan db:seed --class=SupplierSeeder
php artisan db:seed --class=ProductSeeder
```

### Reset Database
```bash
# Fresh migration (drops all tables)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset
```

---

## 🧹 Cache Commands

### Clear All Caches
```bash
# Clear all caches at once
php artisan optimize:clear
```

### Clear Specific Caches
```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear compiled classes
php artisan clear-compiled
```

### Create Caches (Production)
```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

---

## 🧪 Testing Commands

### Run All Tests
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/SuperAdminLoginTest.php

# Run specific test method
php artisan test --filter testSuperAdminCanLogin
```

### Test Categories
```bash
# Pharmaceutical invoice tests
php artisan test tests/Feature/PharmaceuticalInvoice

# RBAC tests
php artisan test tests/Feature/RBACAccessControlTest.php

# Login tests
php artisan test tests/Feature/SuperAdminLoginTest.php
```

---

## 🔍 Debugging Commands

### Tinker (Interactive Shell)
```bash
# Start tinker
php artisan tinker

# Quick checks in tinker
App\Models\User::count();
App\Models\Organization::count();
App\Models\Supplier::count();
App\Models\Product::count();
App\Models\PurchaseOrder::count();

# Check user
$user = App\Models\User::where('email', 'alanramadhani21@gmail.com')->first();
$user->roles;
$user->permissions;

# Check products for supplier
$supplier = App\Models\Supplier::first();
$supplier->products()->count();
```

### Logs
```bash
# View logs (Linux/Mac)
tail -f storage/logs/laravel.log

# View logs (Windows PowerShell)
Get-Content storage/logs/laravel.log -Wait -Tail 50

# Clear logs
echo "" > storage/logs/laravel.log
```

---

## 👥 User Management

### Check Users
```bash
php artisan tinker
```

```php
// List all users
App\Models\User::select('name', 'email')->get();

// Check user role
$user = App\Models\User::where('email', 'alanramadhani21@gmail.com')->first();
$user->getRoleNames();

// Check user permissions
$user->getAllPermissions()->pluck('name');
```

### Reset User Password
```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'alanramadhani21@gmail.com')->first();
$user->password = bcrypt('NewPassword123!');
$user->save();
```

---

## 📊 Data Verification

### Check Data Counts
```bash
php artisan tinker
```

```php
// Users
echo "Users: " . App\Models\User::count();

// Organizations
echo "Organizations: " . App\Models\Organization::count();

// Suppliers
echo "Suppliers: " . App\Models\Supplier::count();

// Products
echo "Products: " . App\Models\Product::count();

// Purchase Orders
echo "POs: " . App\Models\PurchaseOrder::count();

// Invoices
echo "Supplier Invoices: " . App\Models\SupplierInvoice::count();
echo "Customer Invoices: " . App\Models\CustomerInvoice::count();
```

### View Sample Data
```bash
php artisan tinker
```

```php
// View organizations
App\Models\Organization::select('name', 'code', 'type')->get();

// View suppliers
App\Models\Supplier::select('name', 'code')->get();

// View products
App\Models\Product::select('name', 'sku', 'price')->limit(10)->get();

// View POs
App\Models\PurchaseOrder::select('po_number', 'status', 'total_amount')->get();
```

---

## 🔐 Permission Commands

### Reset Permissions
```bash
# Clear permission cache
php artisan permission:cache-reset

# Re-seed permissions
php artisan db:seed --class=RolePermissionSeeder
```

### Check Permissions
```bash
php artisan tinker
```

```php
// List all roles
Spatie\Permission\Models\Role::with('permissions')->get();

// List all permissions
Spatie\Permission\Models\Permission::all()->pluck('name');

// Check user permissions
$user = App\Models\User::find(1);
$user->getAllPermissions()->pluck('name');
```

---

## 🌐 Server Commands

### Development Server
```bash
# Start development server
php artisan serve

# Start on specific port
php artisan serve --port=8080

# Start on specific host
php artisan serve --host=0.0.0.0 --port=8000
```

### Queue Workers (if using queues)
```bash
# Start queue worker
php artisan queue:work

# Start with specific queue
php artisan queue:work --queue=high,default

# Process one job
php artisan queue:work --once
```

---

## 📝 Maintenance Commands

### Maintenance Mode
```bash
# Enable maintenance mode
php artisan down

# Enable with secret bypass
php artisan down --secret="bypass-token"

# Disable maintenance mode
php artisan up
```

### Storage Link
```bash
# Create storage link
php artisan storage:link
```

---

## 🔧 Custom Scripts

### Windows (PowerShell)
```powershell
# Seed master data
.\scripts\seed-master-data.ps1

# Seed products only
.\scripts\seed-products.ps1

# Validate Tailwind removal
.\scripts\validate-tailwind-removal.ps1
```

### Linux/Mac (Bash)
```bash
# Make scripts executable
chmod +x scripts/*.sh

# Seed master data
./scripts/seed-master-data.sh

# Seed products only
./scripts/seed-products.sh
```

---

## 🐛 Troubleshooting Commands

### Fix Common Issues
```bash
# Clear all caches
php artisan optimize:clear

# Regenerate autoload files
composer dump-autoload

# Clear compiled views
php artisan view:clear

# Clear application cache
php artisan cache:clear

# Reset permission cache
php artisan permission:cache-reset
```

### Database Issues
```bash
# Check database connection
php artisan tinker
DB::connection()->getPdo();

# Run migrations
php artisan migrate

# Fresh start
php artisan migrate:fresh --seed
```

### Permission Issues
```bash
# Fix storage permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Fix storage permissions (Windows)
# Run as Administrator
icacls storage /grant Users:F /t
icacls bootstrap/cache /grant Users:F /t
```

---

## 📦 Composer Commands

### Install Dependencies
```bash
# Install all dependencies
composer install

# Install without dev dependencies (production)
composer install --no-dev --optimize-autoloader

# Update dependencies
composer update

# Update specific package
composer update vendor/package
```

### Autoload
```bash
# Regenerate autoload files
composer dump-autoload

# Optimize autoload (production)
composer dump-autoload --optimize
```

---

## 🎯 Production Deployment

### Pre-Deployment
```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Fix permissions
chmod -R 775 storage bootstrap/cache
```

### Post-Deployment
```bash
# 1. Restart queue workers (if using)
php artisan queue:restart

# 2. Check logs
tail -f storage/logs/laravel.log

# 3. Test critical features
php artisan test
```

---

## 📊 Monitoring Commands

### Check System Status
```bash
php artisan tinker
```

```php
// Check database
DB::connection()->getPdo();

// Check cache
Cache::get('test');

// Check queue
Queue::size();

// Check storage
Storage::disk('local')->exists('test.txt');
```

### Performance
```bash
# Check route list
php artisan route:list

# Check event list
php artisan event:list

# Check schedule
php artisan schedule:list
```

---

## 🔑 Quick Login Credentials

```
Super Admin:
  Email: alanramadhani21@gmail.com
  Password: Medikindo@2026!

Healthcare User:
  Email: budi.santoso@testhospital.com
  Password: Healthcare@2026!

Approver:
  Email: siti.nurhaliza@medikindo.com
  Password: Approver@2026!

Finance:
  Email: ahmad.hidayat@medikindo.com
  Password: Finance@2026!
```

---

## 📚 Documentation Links

- `USER_CREDENTIALS.md` - User accounts
- `MASTER_DATA_SEEDING_GUIDE.md` - Seeding guide
- `BUSINESS_LOGIC_AUDIT_REPORT.md` - System audit
- `SESSION_SUMMARY_COMPLETE.md` - Session summary

---

**Last Updated**: 13 April 2026  
**Status**: ✅ Ready to Use
