# Super Admin - Quick Reference Card
## Medikindo PO System

---

## 🔐 Login Credentials

```
📧 Email   : alanramadhani21@gmail.com
🔑 Password: Medikindo@2026!
🌐 URL     : http://localhost:8000/login
```

⚠️ **Ganti password setelah login pertama!**

---

## ⚡ Quick Commands

### If Login Fails

```bash
# 1. Re-seed database
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=SuperAdminSeeder

# 2. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan permission:cache-reset

# 3. Check configuration
php scripts/check-super-admin.php

# 4. Run tests
php artisan test tests/Feature/SuperAdminLoginTest.php
```

---

## 🎯 Super Admin Access

### ✅ Full Access To:

**Procurement**:
- ✅ Purchase Orders (view, create, edit, delete, approve)
- ✅ Approvals (view, approve, reject)
- ✅ Goods Receipt (view, create)

**Finance**:
- ✅ Invoices (view, issue, approve discrepancy)
- ✅ Payments (view, create, verify)
- ✅ Credit Control (view, manage)

**Master Data**:
- ✅ Organizations (full CRUD)
- ✅ Suppliers (full CRUD)
- ✅ Products (full CRUD)
- ✅ Users (full CRUD + role assignment)

**System**:
- ✅ Dashboard (all widgets)
- ✅ Audit Log (view all)
- ✅ Reports (all reports)

**Total Permissions**: 29 (ALL)

---

## 🚨 Troubleshooting

### Problem: Cannot Login

**Quick Fix**:
```bash
php artisan db:seed --class=SuperAdminSeeder
php artisan cache:clear
php artisan permission:cache-reset
```

### Problem: 403 Forbidden

**Quick Fix**:
```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan permission:cache-reset
```

### Problem: Redirect Loop

**Quick Fix**:
```bash
php artisan cache:clear
php artisan config:clear
# Clear browser cookies
```

---

## 📚 Documentation

- **Full Guide**: `docs/SUPER_ADMIN_LOGIN_TROUBLESHOOTING.md`
- **Fix Report**: `SUPER_ADMIN_LOGIN_FIX_REPORT.md`
- **User Access Guide**: `docs/USER_ROLE_ACCESS_GUIDE.md`
- **RBAC Audit**: `RBAC_AUDIT_COMPLETE_SUMMARY.md`

---

## 🔧 Diagnostic Tools

```bash
# Check Super Admin configuration
php scripts/check-super-admin.php

# Run login tests
php artisan test tests/Feature/SuperAdminLoginTest.php

# Check system info
php artisan about

# Check migrations
php artisan migrate:status

# Check routes
php artisan route:list | grep login
```

---

## 📞 Quick Help

**Q: Lupa password?**  
A: Default password: `Medikindo@2026!` atau run seeder lagi

**Q: Tidak bisa akses menu tertentu?**  
A: Run `php artisan permission:cache-reset`

**Q: Error 403 Forbidden?**  
A: Re-seed permissions dan clear cache

**Q: Session expired terus?**  
A: Clear browser cookies dan cache

---

## ✅ Verification Checklist

After login, you should see:

- [x] Name "Alan Ramadhani" in header
- [x] All menu items in sidebar (10+ items)
- [x] Dashboard with all widgets
- [x] No 403 errors when accessing pages
- [x] Can create/edit/delete all resources

---

## 🔐 Security Best Practices

1. ✅ Change password after first login
2. ✅ Use strong password (min 12 chars)
3. ✅ Don't share credentials
4. ✅ Monitor audit logs regularly
5. ✅ Review user permissions monthly
6. ✅ Backup database before major changes

---

**Last Updated**: 13 April 2026  
**Version**: 1.0  
**Status**: ✅ Verified & Tested
