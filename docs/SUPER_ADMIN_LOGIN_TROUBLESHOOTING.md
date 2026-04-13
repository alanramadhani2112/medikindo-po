# Super Admin Login - Troubleshooting Guide
## Medikindo PO System

**Versi**: 1.0  
**Tanggal**: 13 April 2026

---

## 🔐 Kredensial Super Admin

```
📧 Email   : alanramadhani21@gmail.com
🔑 Password: Medikindo@2026!
```

⚠️ **PENTING**: Segera ganti password setelah login pertama!

---

## ✅ Quick Fix - Jalankan Ini Dulu

Jika Super Admin tidak bisa login, jalankan perintah berikut secara berurutan:

```bash
# 1. Re-seed roles dan permissions
php artisan db:seed --class=RolePermissionSeeder

# 2. Re-seed Super Admin user
php artisan db:seed --class=SuperAdminSeeder

# 3. Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan permission:cache-reset

# 4. Restart web server (jika menggunakan php artisan serve)
# Ctrl+C kemudian jalankan lagi: php artisan serve
```

Setelah itu, coba login lagi dengan kredensial di atas.

---

## 🔍 Diagnostic Script

Untuk mengecek konfigurasi Super Admin, jalankan:

```bash
php scripts/check-super-admin.php
```

Script ini akan mengecek:
- ✅ User exists
- ✅ User is active
- ✅ Organization ID is NULL
- ✅ Role exists
- ✅ User has role
- ✅ Role has permissions
- ✅ Password hash exists
- ✅ Password verification
- ✅ Auth configuration

---

## 🐛 Common Problems & Solutions

### Problem 1: "Email atau password tidak valid"

**Kemungkinan Penyebab**:
- Password salah
- User belum di-seed
- Password hash tidak match

**Solusi**:
```bash
# Re-seed Super Admin
php artisan db:seed --class=SuperAdminSeeder

# Coba login dengan:
# Email: alanramadhani21@gmail.com
# Password: Medikindo@2026!
```

---

### Problem 2: "Akun Anda telah dinonaktifkan"

**Kemungkinan Penyebab**:
- Field `is_active` di database = false

**Solusi**:
```bash
# Jalankan seeder untuk mengaktifkan user
php artisan db:seed --class=SuperAdminSeeder

# Atau manual via tinker:
php artisan tinker
>>> $user = App\Models\User::where('email', 'alanramadhani21@gmail.com')->first();
>>> $user->is_active = true;
>>> $user->save();
>>> exit
```

---

### Problem 3: Redirect ke login terus-menerus

**Kemungkinan Penyebab**:
- Session tidak tersimpan
- Cookie blocked
- Cache issue

**Solusi**:
```bash
# 1. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan session:clear

# 2. Check .env file
# Pastikan APP_KEY sudah di-generate
php artisan key:generate

# 3. Check session configuration
# File: config/session.php
# Pastikan 'driver' => env('SESSION_DRIVER', 'file')

# 4. Clear browser cookies
# Buka browser → Settings → Clear browsing data → Cookies

# 5. Try incognito/private mode
```

---

### Problem 4: 403 Forbidden setelah login

**Kemungkinan Penyebab**:
- Role tidak memiliki permissions
- Permission cache outdated
- Guard mismatch

**Solusi**:
```bash
# 1. Re-seed permissions
php artisan db:seed --class=RolePermissionSeeder

# 2. Clear permission cache
php artisan permission:cache-reset

# 3. Re-assign role
php artisan db:seed --class=SuperAdminSeeder

# 4. Verify permissions
php artisan tinker
>>> $user = App\Models\User::where('email', 'alanramadhani21@gmail.com')->first();
>>> $user->roles->pluck('name'); // Should show: ["Super Admin"]
>>> $user->can('view_dashboard'); // Should return: true
>>> exit
```

---

### Problem 5: "User not found" atau "Role not found"

**Kemungkinan Penyebab**:
- Database belum di-seed
- Migration belum dijalankan

**Solusi**:
```bash
# 1. Run migrations
php artisan migrate

# 2. Run all seeders
php artisan db:seed

# Atau run specific seeders:
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=SuperAdminSeeder
```

---

### Problem 6: CSRF Token Mismatch

**Kemungkinan Penyebab**:
- Session expired
- Cache issue
- APP_KEY changed

**Solusi**:
```bash
# 1. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 2. Regenerate APP_KEY (HATI-HATI: ini akan logout semua user)
php artisan key:generate

# 3. Clear browser cache dan cookies

# 4. Refresh halaman login (Ctrl+F5)
```

---

## 🧪 Testing Login

### Manual Test via Browser

1. Buka browser (Chrome/Firefox/Edge)
2. Buka URL: `http://localhost:8000/login` (atau sesuai konfigurasi)
3. Masukkan kredensial:
   - Email: `alanramadhani21@gmail.com`
   - Password: `Medikindo@2026!`
4. Klik "Masuk Sekarang"
5. Seharusnya redirect ke dashboard

### Automated Test

```bash
# Run login tests
php artisan test tests/Feature/SuperAdminLoginTest.php

# Expected output:
# ✓ super admin can login with correct credentials
# ✓ super admin cannot login with wrong password
# ✓ inactive super admin cannot login
# ✓ super admin has all permissions
# ✓ super admin can access dashboard after login
```

---

## 📋 Verification Checklist

Gunakan checklist ini untuk memverifikasi konfigurasi:

- [ ] **Database Migration**: `php artisan migrate:status` (semua migrated)
- [ ] **Role Seeded**: Check di database table `roles` ada "Super Admin"
- [ ] **Permissions Seeded**: Check di database table `permissions` ada 29 permissions
- [ ] **User Seeded**: Check di database table `users` ada user dengan email `alanramadhani21@gmail.com`
- [ ] **User Active**: Field `is_active` = 1
- [ ] **User Has Role**: Check di table `model_has_roles` ada mapping user → role
- [ ] **Role Has Permissions**: Check di table `role_has_permissions` ada 29 permissions untuk Super Admin
- [ ] **Password Hash**: Field `password` tidak kosong
- [ ] **Organization NULL**: Field `organization_id` = NULL
- [ ] **Cache Cleared**: Semua cache sudah di-clear
- [ ] **Session Working**: Cookie `laravel_session` tersimpan di browser

---

## 🔧 Advanced Troubleshooting

### Check Database Directly

```sql
-- Check if user exists
SELECT id, name, email, is_active, organization_id 
FROM users 
WHERE email = 'alanramadhani21@gmail.com';

-- Check user roles
SELECT u.name, r.name as role_name, r.guard_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'alanramadhani21@gmail.com';

-- Check role permissions
SELECT r.name as role_name, COUNT(p.id) as permission_count
FROM roles r
LEFT JOIN role_has_permissions rhp ON r.id = rhp.role_id
LEFT JOIN permissions p ON rhp.permission_id = p.id
WHERE r.name = 'Super Admin'
GROUP BY r.id, r.name;
```

### Check Laravel Logs

```bash
# View latest logs
tail -f storage/logs/laravel.log

# Search for authentication errors
grep -i "auth" storage/logs/laravel.log
grep -i "login" storage/logs/laravel.log
grep -i "permission" storage/logs/laravel.log
```

### Check Web Server Logs

**Laragon**:
```
C:\laragon\logs\apache_error.log
C:\laragon\logs\nginx_error.log
```

**XAMPP**:
```
C:\xampp\apache\logs\error.log
```

### Enable Debug Mode

Edit `.env`:
```env
APP_DEBUG=true
APP_ENV=local
```

⚠️ **JANGAN enable debug mode di production!**

---

## 🆘 Still Not Working?

Jika masih tidak bisa login setelah mencoba semua solusi di atas:

### 1. Fresh Install (Nuclear Option)

```bash
# BACKUP DATABASE DULU!
php artisan db:backup # (jika ada command ini)

# Drop all tables dan re-migrate
php artisan migrate:fresh

# Re-seed semua data
php artisan db:seed

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan permission:cache-reset
```

### 2. Check System Requirements

- PHP >= 8.2
- Laravel >= 11.x
- MySQL/MariaDB >= 8.0
- Extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### 3. Check File Permissions

```bash
# Linux/Mac
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Windows (run as Administrator)
icacls storage /grant Users:F /t
icacls bootstrap\cache /grant Users:F /t
```

### 4. Contact Support

Jika masih bermasalah, kumpulkan informasi berikut:

- Output dari: `php scripts/check-super-admin.php`
- Output dari: `php artisan about`
- Laravel log: `storage/logs/laravel.log` (last 50 lines)
- Browser console errors (F12 → Console)
- Screenshot error message

---

## 📞 Quick Commands Reference

```bash
# Diagnostic
php scripts/check-super-admin.php

# Re-seed
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=SuperAdminSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan permission:cache-reset

# Test
php artisan test tests/Feature/SuperAdminLoginTest.php

# Check system
php artisan about
php artisan migrate:status
php artisan route:list | grep login
```

---

## ✅ Success Indicators

Setelah berhasil login, Anda seharusnya:

1. ✅ Redirect ke `/dashboard`
2. ✅ Melihat nama "Alan Ramadhani" di header
3. ✅ Melihat semua menu di sidebar:
   - Dashboard
   - Purchase Orders
   - Approvals
   - Goods Receipt
   - Invoices
   - Payments
   - Credit Control
   - Organizations
   - Suppliers
   - Products
   - Users
4. ✅ Bisa mengakses semua halaman tanpa 403 error

---

## 🔐 Security Reminder

Setelah berhasil login pertama kali:

1. **Ganti Password Immediately**
   - Pergi ke Profile/Settings
   - Ganti password dari `Medikindo@2026!` ke password yang kuat
   - Gunakan kombinasi huruf besar, kecil, angka, dan simbol

2. **Enable 2FA** (jika tersedia)

3. **Review Audit Log**
   - Check siapa saja yang pernah login
   - Monitor aktivitas mencurigakan

---

**Last Updated**: 13 April 2026  
**Version**: 1.0  
**Status**: ✅ Tested & Verified
