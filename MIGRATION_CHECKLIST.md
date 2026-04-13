# 🗄️ DATABASE MIGRATION CHECKLIST

**Date**: April 14, 2026  
**Version**: 2.0  
**Purpose**: Ensure all database changes are applied correctly  
**Estimated Time**: 15-30 minutes

---

## 📋 MIGRATION OVERVIEW

### Migrations to Execute:
1. ✅ `2026_04_14_000001_add_goods_receipt_to_invoices.php`
2. ✅ `2026_04_14_000002_add_batch_expiry_to_goods_receipt_items.php`
3. ✅ `2026_04_14_100000_enforce_goods_receipt_requirement.php`

**Total Migrations**: 3

---

## 🔍 PRE-MIGRATION CHECKS

### Step 1: Verify Migration Files Exist
```bash
# Check if migration files exist
ls -la database/migrations/2026_04_14_*.php

# Expected output:
# 2026_04_14_000001_add_goods_receipt_to_invoices.php
# 2026_04_14_000002_add_batch_expiry_to_goods_receipt_items.php
# 2026_04_14_100000_enforce_goods_receipt_requirement.php
```

**Status**: [ ] VERIFIED

---

### Step 2: Check Current Migration Status
```bash
# Check which migrations have been run
php artisan migrate:status

# Look for the 3 new migrations
# They should show as "Pending"
```

**Status**: [ ] VERIFIED

---

### Step 3: Backup Database
```bash
# Create backup before migration
mysqldump -u username -p medikindo_po > backup_pre_migration_$(date +%Y%m%d_%H%M%S).sql

# Verify backup created
ls -lh backup_pre_migration_*.sql
```

**Backup File**: [ ]  
**Backup Size**: [ ]  
**Status**: [ ] COMPLETED

---

## 🚀 MIGRATION EXECUTION

### Step 1: Run Migrations
```bash
# Run all pending migrations
php artisan migrate --force

# Note: --force is required in production
```

**Expected Output**:
```
Migrating: 2026_04_14_000001_add_goods_receipt_to_invoices
Migrated:  2026_04_14_000001_add_goods_receipt_to_invoices (XX.XXms)

Migrating: 2026_04_14_000002_add_batch_expiry_to_goods_receipt_items
Migrated:  2026_04_14_000002_add_batch_expiry_to_goods_receipt_items (XX.XXms)

Migrating: 2026_04_14_100000_enforce_goods_receipt_requirement
Migrated:  2026_04_14_100000_enforce_goods_receipt_requirement (XX.XXms)
```

**Actual Output**: [ ]

**Status**: [ ] SUCCESS [ ] FAILED

---

### Step 2: Verify Migration Status
```bash
# Check migration status again
php artisan migrate:status

# All 3 migrations should show as "Ran"
```

**Status**: [ ] VERIFIED

---

## ✅ POST-MIGRATION VERIFICATION

### Verification 1: Check supplier_invoices Table
```sql
-- Connect to database
mysql -u username -p medikindo_po

-- Check table structure
DESCRIBE supplier_invoices;

-- Verify goods_receipt_id column exists and is NOT NULL
-- Expected: goods_receipt_id | bigint unsigned | NO | MUL | NULL |
```

**Expected Columns**:
- [x] `goods_receipt_id` exists
- [x] `goods_receipt_id` is BIGINT UNSIGNED
- [x] `goods_receipt_id` is NOT NULL
- [x] Foreign key constraint exists

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Verification 2: Check customer_invoices Table
```sql
-- Check table structure
DESCRIBE customer_invoices;

-- Verify goods_receipt_id column exists and is NOT NULL
```

**Expected Columns**:
- [x] `goods_receipt_id` exists
- [x] `goods_receipt_id` is BIGINT UNSIGNED
- [x] `goods_receipt_id` is NOT NULL
- [x] Foreign key constraint exists

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Verification 3: Check goods_receipt_items Table
```sql
-- Check table structure
DESCRIBE goods_receipt_items;

-- Verify batch_no and expiry_date columns exist
```

**Expected Columns**:
- [x] `batch_no` exists (VARCHAR 100, nullable)
- [x] `expiry_date` exists (DATE, nullable)

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Verification 4: Check supplier_invoice_line_items Table
```sql
-- Check table structure
DESCRIBE supplier_invoice_line_items;

-- Verify goods_receipt_item_id, batch_no, expiry_date columns exist
```

**Expected Columns**:
- [x] `goods_receipt_item_id` exists (BIGINT UNSIGNED, nullable)
- [x] `batch_no` exists (VARCHAR 100, nullable)
- [x] `expiry_date` exists (DATE, nullable)
- [x] Foreign key constraint exists

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Verification 5: Check customer_invoice_line_items Table
```sql
-- Check table structure
DESCRIBE customer_invoice_line_items;

-- Verify goods_receipt_item_id, batch_no, expiry_date columns exist
```

**Expected Columns**:
- [x] `goods_receipt_item_id` exists (BIGINT UNSIGNED, nullable)
- [x] `batch_no` exists (VARCHAR 100, nullable)
- [x] `expiry_date` exists (DATE, nullable)
- [x] Foreign key constraint exists

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Verification 6: Check Foreign Key Constraints
```sql
-- Check foreign keys on supplier_invoices
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE 
    TABLE_NAME = 'supplier_invoices'
    AND REFERENCED_TABLE_NAME = 'goods_receipts';

-- Expected: 1 row showing FK constraint
```

**Expected Result**:
```
CONSTRAINT_NAME: supplier_invoices_goods_receipt_id_foreign
TABLE_NAME: supplier_invoices
COLUMN_NAME: goods_receipt_id
REFERENCED_TABLE_NAME: goods_receipts
REFERENCED_COLUMN_NAME: id
```

**Actual Result**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Verification 7: Check Data Integrity
```sql
-- Check if any invoices have NULL goods_receipt_id (should be 0)
SELECT COUNT(*) as null_gr_count 
FROM supplier_invoices 
WHERE goods_receipt_id IS NULL;

-- Expected: 0
```

**Expected**: 0  
**Actual**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Verification 8: Test Insert with NULL goods_receipt_id (Should FAIL)
```sql
-- Try to insert invoice without goods_receipt_id
INSERT INTO supplier_invoices (
    invoice_number,
    organization_id,
    supplier_id,
    purchase_order_id,
    goods_receipt_id,
    status,
    total_amount,
    created_at,
    updated_at
) VALUES (
    'TEST-INV-001',
    1,
    1,
    1,
    NULL,  -- This should fail
    'issued',
    1000000,
    NOW(),
    NOW()
);

-- Expected: ERROR 1048 (23000): Column 'goods_receipt_id' cannot be null
```

**Expected**: ERROR (cannot be null)  
**Actual**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

### Verification 9: Test Insert with Valid goods_receipt_id (Should PASS)
```sql
-- Try to insert invoice with valid goods_receipt_id
INSERT INTO supplier_invoices (
    invoice_number,
    organization_id,
    supplier_id,
    purchase_order_id,
    goods_receipt_id,
    status,
    total_amount,
    created_at,
    updated_at
) VALUES (
    'TEST-INV-002',
    1,
    1,
    1,
    1,  -- Valid GR ID
    'issued',
    1000000,
    NOW(),
    NOW()
);

-- Expected: Query OK, 1 row affected

-- Clean up test data
DELETE FROM supplier_invoices WHERE invoice_number = 'TEST-INV-002';
```

**Expected**: SUCCESS  
**Actual**: [ ]

**Status**: [ ] PASS [ ] FAIL

---

## 🔄 ROLLBACK PLAN (If Needed)

### Step 1: Rollback Migrations
```bash
# Rollback last 3 migrations
php artisan migrate:rollback --step=3

# Verify rollback
php artisan migrate:status
```

**Status**: [ ] EXECUTED (if needed)

---

### Step 2: Restore Database Backup
```bash
# If rollback fails, restore from backup
mysql -u username -p medikindo_po < backup_pre_migration_YYYYMMDD_HHMMSS.sql

# Verify restoration
mysql -u username -p medikindo_po -e "SHOW TABLES;"
```

**Status**: [ ] EXECUTED (if needed)

---

## 📊 MIGRATION SUMMARY

### Migration Results:

| Migration | Status | Time | Notes |
|-----------|--------|------|-------|
| add_goods_receipt_to_invoices | [ ] | [ ] | [ ] |
| add_batch_expiry_to_goods_receipt_items | [ ] | [ ] | [ ] |
| enforce_goods_receipt_requirement | [ ] | [ ] | [ ] |

**Total Time**: [ ]

---

### Verification Results:

| Verification | Expected | Actual | Status |
|--------------|----------|--------|--------|
| supplier_invoices.goods_receipt_id | NOT NULL | [ ] | [ ] |
| customer_invoices.goods_receipt_id | NOT NULL | [ ] | [ ] |
| goods_receipt_items.batch_no | EXISTS | [ ] | [ ] |
| goods_receipt_items.expiry_date | EXISTS | [ ] | [ ] |
| Foreign key constraints | EXISTS | [ ] | [ ] |
| NULL goods_receipt_id count | 0 | [ ] | [ ] |
| Insert with NULL GR | FAIL | [ ] | [ ] |
| Insert with valid GR | SUCCESS | [ ] | [ ] |

**Overall Status**: [ ] PASS [ ] FAIL

---

## 🎯 ACCEPTANCE CRITERIA

### Must Pass (Blockers):
- [ ] All 3 migrations executed successfully
- [ ] goods_receipt_id column exists in invoices tables
- [ ] goods_receipt_id is NOT NULL
- [ ] Foreign key constraints exist
- [ ] Cannot insert invoice with NULL goods_receipt_id
- [ ] Can insert invoice with valid goods_receipt_id
- [ ] batch_no and expiry_date columns exist

### Should Pass (Important):
- [ ] No data loss during migration
- [ ] All existing data preserved
- [ ] Database backup created
- [ ] Migration time acceptable (< 5 minutes)

---

## 📝 MIGRATION LOG

### Execution Details:
- **Executed By**: [ ]
- **Date**: [ ]
- **Time**: [ ]
- **Environment**: [ ] Staging [ ] Production
- **Database**: [ ]
- **Backup File**: [ ]

### Issues Encountered:
```
Issue #1:
- Description: [ ]
- Resolution: [ ]
- Status: [ ]

Issue #2:
- Description: [ ]
- Resolution: [ ]
- Status: [ ]
```

### Notes:
```
[ ]
```

---

## ✅ FINAL SIGN-OFF

### Migration Completion:
- [ ] All migrations executed
- [ ] All verifications passed
- [ ] No data loss
- [ ] Backup created
- [ ] Documentation updated

**Executed By**: [ ]  
**Verified By**: [ ]  
**Date**: [ ]  
**Status**: [ ] APPROVED [ ] REJECTED

---

## 📞 SUPPORT

### If Migration Fails:

1. **DO NOT PANIC**
2. **DO NOT run migrations again**
3. **Contact Database Admin immediately**
4. **Provide**:
   - Error message
   - Migration log
   - Database backup location

### Contact:
- **Database Admin**: [Contact]
- **System Engineer**: [Contact]
- **Emergency**: [Contact]

---

## 📚 REFERENCES

### Migration Files:
- `database/migrations/2026_04_14_000001_add_goods_receipt_to_invoices.php`
- `database/migrations/2026_04_14_000002_add_batch_expiry_to_goods_receipt_items.php`
- `database/migrations/2026_04_14_100000_enforce_goods_receipt_requirement.php`

### Documentation:
- `DEPLOYMENT_GUIDE.md` - Full deployment guide
- `PRODUCTION_READINESS_CHECKLIST.md` - Pre-deployment checks
- `SYSTEM_AUDIT_REPORT.md` - Audit findings

---

**Prepared By**: System Engineer  
**Date**: April 14, 2026  
**Version**: 2.0

---

**END OF MIGRATION CHECKLIST**
