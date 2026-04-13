# RBAC Issues Found - Critical

**Date**: April 13, 2026  
**Severity**: 🔴 **CRITICAL**  
**Status**: ⚠️ **REQUIRES IMMEDIATE FIX**

---

## 🚨 Critical Issue: Permission Name Mismatch

### Problem
Controllers are using different permission names than what's defined in the RolePermissionSeeder. This causes **all permission checks to fail** because the permissions don't exist in the database.

### Impact
- **Healthcare Users cannot create Purchase Orders** (expects `create_po`, has `create_purchase_orders`)
- **Finance cannot manage invoices** (expects `manage_invoice`, has `create_invoices`)
- **All role-based access control is broken**

---

## 📋 Permission Mismatches Found

### Purchase Orders
| Controller Uses | Seeder Defines | Status |
|----------------|----------------|--------|
| `create_po` | `create_purchase_orders` | ❌ MISMATCH |
| `submit_po` | *(not defined)* | ❌ MISSING |
| `update_po` | `update_purchase_orders` | ❌ MISMATCH |

### Invoices
| Controller Uses | Seeder Defines | Status |
|----------------|----------------|--------|
| `manage_invoice` | `create_invoices` | ❌ MISMATCH |
| `view_invoice` | `view_invoices` | ❌ MISMATCH |
| `approve_invoice_discrepancy` | *(not defined)* | ❌ MISSING |

### Payments
| Controller Uses | Seeder Defines | Status |
|----------------|----------------|--------|
| `confirm_payment` | `process_payments` | ❌ MISMATCH |
| `verify_payment` | `process_payments` | ❌ MISMATCH |

### Goods Receipt
| Controller Uses | Seeder Defines | Status |
|----------------|----------------|--------|
| `view_receipt` | `view_goods_receipt` | ❌ MISMATCH |
| `confirm_receipt` | *(not defined)* | ❌ MISSING |

### Other
| Controller Uses | Seeder Defines | Status |
|----------------|----------------|--------|
| `view_audit` | *(not defined)* | ❌ MISSING |

---

## 🔧 Recommended Fix Strategy

### Option 1: Update RolePermissionSeeder (RECOMMENDED)
**Pros**:
- Controllers already use these permissions
- Less code changes
- Faster to implement

**Cons**:
- Permission names less descriptive

### Option 2: Update All Controllers
**Pros**:
- More consistent naming convention
- Better documentation

**Cons**:
- More files to change
- Higher risk of missing something

---

## ✅ Recommended Solution

**Update RolePermissionSeeder** to add missing permissions and create aliases for existing ones:

```php
// Add missing permissions
'submit_purchase_orders',
'confirm_payment',
'verify_payment',
'approve_invoice_discrepancy',
'view_audit',
'confirm_receipt',

// OR create permission aliases in seeder
Permission::firstOrCreate(['name' => 'create_po', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'submit_po', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'update_po', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'manage_invoice', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'view_invoice', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'confirm_payment', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'verify_payment', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'approve_invoice_discrepancy', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'view_receipt', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'confirm_receipt', 'guard_name' => $guard]);
Permission::firstOrCreate(['name' => 'view_audit', 'guard_name' => $guard]);
```

---

## 📝 Updated Role Assignments

### Healthcare User
```php
$healthcare->syncPermissions([
    'view_dashboard',
    'view_purchase_orders',
    'create_po',              // ADD
    'submit_po',              // ADD
    'update_po',              // ADD
    'view_receipt',           // ADD
    'confirm_receipt',        // ADD
    'confirm_payment',        // ADD (for confirming their own payments)
]);
```

### Approver
```php
$approver->syncPermissions([
    'view_dashboard',
    'view_approvals',
    'approve_purchase_orders',
    'view_purchase_orders',   // ADD (to see PO details)
]);
```

### Finance
```php
$finance->syncPermissions([
    'view_dashboard',
    'view_invoice',           // ADD
    'manage_invoice',         // ADD
    'create_invoices',        // KEEP
    'view_payments',
    'process_payments',
    'confirm_payment',        // ADD
    'verify_payment',         // ADD
    'approve_invoice_discrepancy', // ADD
    'view_credit_control',
]);
```

---

## 🎯 Action Items

1. ✅ **Identify all permission mismatches** - DONE
2. ⏳ **Update RolePermissionSeeder** - PENDING
3. ⏳ **Re-run database seeder** - PENDING
4. ⏳ **Run RBAC tests again** - PENDING
5. ⏳ **Manual testing with each role** - PENDING
6. ⏳ **Update documentation** - PENDING

---

## 📊 Test Results

### Before Fix
- ❌ Healthcare User: Cannot create PO (403 Forbidden)
- ❌ All roles: Permission checks failing
- ❌ RBAC completely broken

### After Fix (Expected)
- ✅ Healthcare User: Can create PO
- ✅ All roles: Proper access control
- ✅ RBAC working as designed

---

**Priority**: 🔴 **CRITICAL - FIX IMMEDIATELY**  
**Estimated Fix Time**: 30 minutes  
**Testing Time**: 1 hour

