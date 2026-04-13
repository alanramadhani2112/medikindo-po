# Approval Page Fix - Complete Report

## Problem Summary
Halaman approval tidak menampilkan data Purchase Order yang sudah disubmit, meskipun backend mengembalikan data dengan benar.

## Root Cause Analysis

### Issue 1: OrganizationScope Global Scope
**File**: `app/Models/Scopes/OrganizationScope.php`

**Problem**:
- `OrganizationScope` adalah global scope yang otomatis diterapkan pada semua query `PurchaseOrder`
- Scope hanya mengecek role `'Super Admin'` dengan guard `'sanctum'`
- User dengan role `'Approver'` atau `'Admin Approver'` tetap difilter berdasarkan `organization_id`
- Karena approver memiliki `organization_id = NULL`, query menjadi: `WHERE organization_id = NULL`
- Tidak ada PO yang match karena semua PO memiliki `organization_id` yang valid

**Original Code**:
```php
public function apply(Builder $builder, Model $model): void
{
    if (Auth::check() && !Auth::user()->hasRole('Super Admin', 'sanctum')) {
        $builder->where($model->getTable() . '.organization_id', Auth::user()->organization_id);
    }
}
```

**Fixed Code**:
```php
public function apply(Builder $builder, Model $model): void
{
    if (Auth::check()) {
        $user = Auth::user();
        
        // Skip scope for users with approver roles (they can see all organizations)
        if ($user->hasAnyRole(['Super Admin', 'Approver', 'Admin Approver'])) {
            return;
        }
        
        // Apply organization filter for other users
        $builder->where($model->getTable() . '.organization_id', $user->organization_id);
    }
}
```

### Issue 2: Redundant Filter in Controller
**File**: `app/Http/Controllers/Web/ApprovalWebController.php`

**Problem**:
- Controller menambahkan filter organization secara manual
- Ini redundant karena `OrganizationScope` sudah handle filtering
- Setelah scope diperbaiki, filter manual tidak diperlukan lagi

**Changes**:
- Removed manual organization filtering from controller
- Simplified count queries
- OrganizationScope now handles all filtering automatically

## Files Modified

### 1. `app/Models/Scopes/OrganizationScope.php`
- Updated `apply()` method to check for approver roles
- Approvers (Super Admin, Approver, Admin Approver) can now see all POs regardless of organization
- Other users still filtered by their organization_id

### 2. `app/Http/Controllers/Web/ApprovalWebController.php`
- Removed redundant organization filtering logic
- Simplified query building
- OrganizationScope handles filtering automatically

## Testing

### Test Scripts Created:
1. `scripts/check-po-status.php` - Check PO status in database
2. `scripts/check-approval-permissions.php` - Verify user permissions
3. `scripts/list-approver-users.php` - List all approver users
4. `scripts/test-organization-scope-fix.php` - Test scope behavior
5. `scripts/test-with-sql-debug.php` - Debug SQL queries
6. `scripts/create-test-po.php` - Create test PO for approval
7. `public/test-approval-full.php` - Browser-based comprehensive test
8. `public/test-current-user.php` - Check current logged-in user

### Test Results:
✅ Super Admin can see all POs (organization filter skipped)
✅ Approver can see all POs (organization filter skipped)
✅ Admin Approver can see all POs (organization filter skipped)
✅ Healthcare User only sees their organization's POs (filter applied)
✅ Finance User only sees their organization's POs (filter applied)

## How to Verify

### 1. Create a Test PO:
```bash
php scripts/create-test-po.php
```

### 2. Login as Approver:
- Email: `siti.nurhaliza@medikindo.com`
- Or Email: `alanramadhani21@gmail.com` (Super Admin)

### 3. Navigate to Approval Page:
```
http://localhost/approvals
```

### 4. Expected Result:
- Approval page shows submitted POs
- Tab counts are correct
- Approvers can see POs from all organizations
- Action buttons (Setujui/Tolak) are functional

## Key Learnings

1. **Global Scopes**: Always check for global scopes when queries don't return expected results
2. **Role-Based Access**: Approver roles need special handling in organization-scoped models
3. **Guard Specification**: Don't specify guard in role checks unless necessary (e.g., `hasRole('Super Admin', 'sanctum')`)
4. **Redundant Filters**: Avoid manual filtering when global scopes already handle it

## Impact

### Before Fix:
- ❌ Approval page always empty for approvers
- ❌ Approvers couldn't see any POs
- ❌ Approval workflow blocked

### After Fix:
- ✅ Approval page shows all submitted POs
- ✅ Approvers can see POs from all organizations
- ✅ Approval workflow functional
- ✅ Organization filtering still works for non-approver users

## Related Files

### Models:
- `app/Models/PurchaseOrder.php` - Uses `BelongsToOrganization` trait
- `app/Traits/BelongsToOrganization.php` - Applies `OrganizationScope`

### Controllers:
- `app/Http/Controllers/Web/ApprovalWebController.php` - Approval page controller

### Views:
- `resources/views/approvals/index.blade.php` - Approval page view

### Services:
- `app/Services/ApprovalService.php` - Approval business logic
- `app/Services/POService.php` - PO submission logic

## Recommendations

1. **Document Global Scopes**: Add comments in models that use global scopes
2. **Test Role Access**: Always test with different roles when implementing access control
3. **Avoid Redundancy**: Don't duplicate filtering logic between scopes and controllers
4. **Use Consistent Role Checks**: Use `hasAnyRole()` consistently across the application

## Status: ✅ RESOLVED

The approval page now works correctly for all user roles. Approvers can see and process all submitted Purchase Orders regardless of organization.
