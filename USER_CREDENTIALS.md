# User Credentials - Medikindo PO System
## Test Accounts (1 User Per Role)

**Last Updated**: 13 April 2026  
**Status**: ✅ Active

---

## 🔐 Login URL

```
http://localhost:8000/login
```

---

## 👥 User Accounts

### 1. Super Admin

```
👤 Name    : Alan Ramadhani
📧 Email   : alanramadhani21@gmail.com
🔑 Password: Medikindo@2026!
🏢 Org     : NULL (All Access)
📋 Role    : Super Admin
```

**Access**:
- ✅ Full access to ALL modules
- ✅ Can manage all organizations
- ✅ Can view/edit all data
- ✅ 29 permissions (ALL)

**Use Case**: System administration, user management, master data

---

### 2. Healthcare User

```
👤 Name    : Dr. Budi Santoso
📧 Email   : budi.santoso@testhospital.com
🔑 Password: Healthcare@2026!
🏢 Org     : Test Hospital
📋 Role    : Healthcare User
```

**Access**:
- ✅ Dashboard
- ✅ Purchase Orders (create, edit, submit)
- ✅ Goods Receipt (create, view)
- ✅ Confirm Payment
- ❌ Cannot access: Approvals, Invoices, Payments List, Credit Control, Master Data

**Use Case**: Hospital/clinic staff creating purchase orders and receiving goods

---

### 3. Approver

```
👤 Name    : Siti Nurhaliza
📧 Email   : siti.nurhaliza@medikindo.com
🔑 Password: Approver@2026!
🏢 Org     : NULL (All Organizations)
📋 Role    : Approver
```

**Access**:
- ✅ Dashboard
- ✅ Purchase Orders (view only)
- ✅ Approvals (approve/reject PO)
- ❌ Cannot access: Create PO, Goods Receipt, Invoices, Payments, Credit Control, Master Data

**Use Case**: Medikindo operations team approving and managing PO shipments

---

### 4. Finance

```
👤 Name    : Ahmad Hidayat
📧 Email   : ahmad.hidayat@medikindo.com
🔑 Password: Finance@2026!
🏢 Org     : NULL (All Organizations)
📋 Role    : Finance
```

**Access**:
- ✅ Dashboard
- ✅ Invoices (view, issue, approve discrepancy)
- ✅ Payments (view, create, verify)
- ✅ Credit Control (view, manage)
- ❌ Cannot access: Purchase Orders, Approvals, Goods Receipt, Master Data

**Use Case**: Finance department managing invoices, payments, and credit control

---

## 📊 Quick Comparison

| Feature | Super Admin | Healthcare User | Approver | Finance |
|---------|-------------|-----------------|----------|---------|
| **Dashboard** | ✅ | ✅ | ✅ | ✅ |
| **Purchase Orders** | ✅ Full | ✅ Create/Edit | ✅ View Only | ❌ |
| **Approvals** | ✅ | ❌ | ✅ | ❌ |
| **Goods Receipt** | ✅ | ✅ | ❌ | ❌ |
| **Invoices** | ✅ | ❌ | ❌ | ✅ |
| **Payments** | ✅ | ✅ Confirm | ❌ | ✅ Full |
| **Credit Control** | ✅ | ❌ | ❌ | ✅ |
| **Master Data** | ✅ | ❌ | ❌ | ❌ |
| **Permissions** | 29 (ALL) | 12 | 4 | 11 |

---

## 🔄 Complete Workflow Example

### Scenario: Hospital Orders Medical Supplies

**Step 1: Healthcare User Creates PO**
```
Login: budi.santoso@testhospital.com
Action: Create Purchase Order for medical supplies
Status: Draft → Submit for Approval
```

**Step 2: Approver Reviews & Approves**
```
Login: siti.nurhaliza@medikindo.com
Action: Review PO → Approve → Mark as Shipped → Mark as Delivered
Status: Submitted → Approved → Shipped → Delivered
```

**Step 3: Healthcare User Receives Goods**
```
Login: budi.santoso@testhospital.com
Action: Create Goods Receipt with actual quantities received
Status: PO Completed
```

**Step 4: Finance Issues Invoice**
```
Login: ahmad.hidayat@medikindo.com
Action: Issue Invoice from completed PO
Status: Invoice Issued (or Pending Approval if discrepancy detected)
```

**Step 5: Healthcare User Confirms Payment**
```
Login: budi.santoso@testhospital.com
Action: Upload payment proof and confirm payment
Status: Payment Confirmed
```

**Step 6: Finance Verifies Payment**
```
Login: ahmad.hidayat@medikindo.com
Action: Verify payment and update invoice status
Status: Invoice Paid
```

---

## 🔧 Management Commands

### Re-seed Users (Drop All & Create Clean)

```bash
php artisan db:seed --class=CleanUserSeeder
```

This will:
- 🗑️ Delete all existing users
- ✅ Create 1 user per role (4 users total)
- ✅ Create Test Hospital organization
- ✅ Assign correct roles and permissions

### Re-seed Everything

```bash
php artisan migrate:fresh --seed
```

⚠️ **WARNING**: This will drop ALL data including POs, invoices, etc.

### Re-seed Only Roles & Permissions

```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## 🧪 Testing Accounts

### Test Login for Each Role

```bash
# Test Super Admin
curl -X POST http://localhost:8000/login \
  -d "email=alanramadhani21@gmail.com" \
  -d "password=Medikindo@2026!"

# Test Healthcare User
curl -X POST http://localhost:8000/login \
  -d "email=budi.santoso@testhospital.com" \
  -d "password=Healthcare@2026!"

# Test Approver
curl -X POST http://localhost:8000/login \
  -d "email=siti.nurhaliza@medikindo.com" \
  -d "password=Approver@2026!"

# Test Finance
curl -X POST http://localhost:8000/login \
  -d "email=ahmad.hidayat@medikindo.com" \
  -d "password=Finance@2026!"
```

### Run Automated Tests

```bash
# Test all user logins
php artisan test tests/Feature/SuperAdminLoginTest.php

# Test RBAC access control
php artisan test tests/Feature/RBACAccessControlTest.php
```

---

## 🔐 Security Notes

### Password Policy

All default passwords follow the pattern: `[Role]@2026!`

- Super Admin: `Medikindo@2026!`
- Healthcare User: `Healthcare@2026!`
- Approver: `Approver@2026!`
- Finance: `Finance@2026!`

⚠️ **IMPORTANT**: 
- Change all passwords after first login
- Use strong passwords (min 12 characters)
- Include uppercase, lowercase, numbers, and symbols
- Don't share credentials via email/chat

### Multi-Tenant Isolation

- **Healthcare User**: Can only see data from their organization (Test Hospital)
- **Approver**: Can see all POs from all organizations (for approval)
- **Finance**: Can see all invoices/payments from all organizations
- **Super Admin**: Can see everything from all organizations

### Account Status

All accounts are **ACTIVE** by default. To deactivate:

```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'user@example.com')->first();
>>> $user->is_active = false;
>>> $user->save();
>>> exit
```

---

## 📞 Support

### If Login Fails

1. **Check credentials** - Copy-paste from this document
2. **Clear cache** - Run `php artisan cache:clear`
3. **Re-seed users** - Run `php artisan db:seed --class=CleanUserSeeder`
4. **Check diagnostic** - Run `php scripts/check-super-admin.php`

### If Access Denied (403)

1. **Clear permission cache** - Run `php artisan permission:cache-reset`
2. **Re-seed permissions** - Run `php artisan db:seed --class=RolePermissionSeeder`
3. **Check role assignment** - Verify user has correct role in database

### Documentation

- **RBAC Guide**: `docs/USER_ROLE_ACCESS_GUIDE.md`
- **Quick Reference**: `docs/ROLE_ACCESS_QUICK_REFERENCE.md`
- **Troubleshooting**: `docs/SUPER_ADMIN_LOGIN_TROUBLESHOOTING.md`

---

## 📋 Checklist for New Deployment

- [ ] Run migrations: `php artisan migrate`
- [ ] Seed roles & permissions: `php artisan db:seed --class=RolePermissionSeeder`
- [ ] Seed users: `php artisan db:seed --class=CleanUserSeeder`
- [ ] Clear all caches: `php artisan cache:clear` (and others)
- [ ] Test login for each role
- [ ] Change all default passwords
- [ ] Create additional organizations if needed
- [ ] Assign users to correct organizations
- [ ] Test complete workflow (PO → Approval → GR → Invoice → Payment)

---

**Created**: 13 April 2026  
**Version**: 1.0  
**Status**: ✅ Active & Tested
