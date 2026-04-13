# Pharmaceutical-Grade Invoice Management - Migration Guide

## Overview

This guide provides step-by-step instructions for migrating your existing invoice system to the new pharmaceutical-grade invoice management system with BCMath precision, immutability controls, and comprehensive audit trails.

**Migration Complexity**: Medium  
**Estimated Time**: 2-4 hours (depending on data volume)  
**Downtime Required**: Yes (recommended: 30-60 minutes)

---

## Pre-Migration Checklist

### 1. System Requirements
- [ ] PHP 8.3+ with BCMath extension enabled
- [ ] MySQL 8.0+ or MariaDB 10.5+
- [ ] Laravel 11.x
- [ ] Minimum 2GB RAM for migration process
- [ ] Database backup completed

### 2. Verify BCMath Extension
```bash
php -m | grep bcmath
```
If not installed:
```bash
# Ubuntu/Debian
sudo apt-get install php8.3-bcmath

# CentOS/RHEL
sudo yum install php83-bcmath
```

### 3. Backup Strategy
```bash
# Database backup
php artisan db:backup

# Or manual backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Code backup
git commit -am "Pre-migration backup"
git tag pre-pharmaceutical-invoice-migration
```

---

## Migration Steps

### Step 1: Run Database Migrations

Execute migrations in order:

```bash
# 1. Upgrade invoice precision (decimal 15,2 → 18,2)
php artisan migrate --path=database/migrations/2026_04_13_074014_upgrade_invoice_precision.php

# 2. Create invoice line items tables
php artisan migrate --path=database/migrations/2026_04_13_074703_create_invoice_line_items_tables.php

# 3. Add discrepancy tracking columns
php artisan migrate --path=database/migrations/2026_04_13_074902_add_discrepancy_tracking_to_invoices.php

# 4. Create modification attempts tracking
php artisan migrate --path=database/migrations/2026_04_13_074945_create_invoice_modification_attempts_table.php

# 5. Add tax & discount configuration
php artisan migrate --path=database/migrations/2026_04_13_075032_add_tax_discount_to_organizations.php
```

Or run all at once:
```bash
php artisan migrate
```

### Step 2: Configure Organization Defaults

Set default tax rate and discount percentage for each organization:

```bash
php artisan tinker
```

```php
// Set Indonesian PPN (11%)
$organizations = App\Models\Organization::all();
foreach ($organizations as $org) {
    $org->update([
        'default_tax_rate' => '11.00',
        'default_discount_percentage' => '0.00',
    ]);
}
```

### Step 3: Migrate Existing Invoice Data (Optional)

If you have existing invoices without line items, you can migrate them:

**Note**: This step is optional. New invoices will automatically use the new system.

```bash
# Run data migration command (if you create it)
php artisan invoice:migrate-to-line-items --dry-run

# Review output, then run actual migration
php artisan invoice:migrate-to-line-items
```

### Step 4: Update Permissions

Add new permissions for discrepancy approval:

```bash
php artisan tinker
```

```php
// Create permission
$permission = Spatie\Permission\Models\Permission::create([
    'name' => 'approve_invoice_discrepancy',
    'guard_name' => 'web'
]);

// Assign to Finance and Super Admin roles
$financeRole = Spatie\Permission\Models\Role::findByName('Finance');
$financeRole->givePermissionTo('approve_invoice_discrepancy');

$superAdminRole = Spatie\Permission\Models\Role::findByName('Super Admin');
$superAdminRole->givePermissionTo('approve_invoice_discrepancy');
```

### Step 5: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
```

### Step 6: Verify Migration

Run verification checks:

```bash
# Check database structure
php artisan migrate:status

# Run tests
php artisan test --filter=Invoice

# Check BCMath calculator
php artisan tinker
```

```php
$calc = app(App\Services\BCMathCalculatorService::class);
echo $calc->add('1000.00', '500.50'); // Should output: 1500.50
echo $calc->multiply('10.00', '3.14'); // Should output: 31.40
```

---

## Post-Migration Verification

### 1. Test Invoice Issuance

Create a test invoice to verify the new system:

1. Navigate to a completed Purchase Order
2. Click "Issue Invoice"
3. Verify:
   - [ ] Line items are created
   - [ ] Calculations are correct (subtotal, discount, tax, total)
   - [ ] Discrepancy detection works (if variance exists)
   - [ ] Audit logs are created

### 2. Test Immutability

Try to modify an issued invoice:

1. Open an issued invoice
2. Attempt to change `total_amount` via Tinker:
```php
$invoice = App\Models\SupplierInvoice::where('status', 'issued')->first();
$invoice->total_amount = '9999.99';
$invoice->save(); // Should throw ImmutabilityViolationException
```

3. Verify violation is logged in `invoice_modification_attempts` table

### 3. Test Concurrency Control

Simulate concurrent updates:

```php
// User A loads invoice
$invoiceA = App\Models\SupplierInvoice::find(1);

// User B loads and updates invoice
$invoiceB = App\Models\SupplierInvoice::find(1);
$invoiceB->status = 'paid';
$invoiceB->save(); // Success, version = 1

// User A tries to update
$invoiceA->paid_amount = '1000.00';
$invoiceA->save(); // Should throw ConcurrencyException
```

### 4. Verify Audit Trail

Check audit logs are being created:

```php
$auditService = app(App\Services\AuditService::class);

// Check invoice audit trail
$trail = $auditService->getInvoiceAuditTrail(1);
dd($trail);

// Check discrepancy logs
$discrepancies = $auditService->getDiscrepancyAuditTrail(flaggedOnly: true);
dd($discrepancies);
```

---

## Rollback Procedure

If you need to rollback:

### 1. Rollback Migrations

```bash
# Rollback in reverse order
php artisan migrate:rollback --step=5
```

### 2. Restore Database Backup

```bash
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql
```

### 3. Restore Code

```bash
git reset --hard pre-pharmaceutical-invoice-migration
```

---

## Common Issues & Solutions

### Issue 1: BCMath Extension Not Found

**Error**: `Call to undefined function bcadd()`

**Solution**:
```bash
# Install BCMath extension
sudo apt-get install php8.3-bcmath
sudo systemctl restart php8.3-fpm
```

### Issue 2: Decimal Precision Loss

**Error**: Calculations showing incorrect results

**Solution**:
- Verify database columns are `decimal(18,2)`
- Check BCMath is being used (not float arithmetic)
- Run: `php artisan migrate:fresh` (on test environment)

### Issue 3: Immutability Violations

**Error**: Cannot update invoice after issuance

**Solution**:
- This is expected behavior for financial fields
- Only update mutable fields: `status`, `paid_amount`, `payment_reference`
- Check `ImmutabilityGuardService::getMutableFields()` for allowed fields

### Issue 4: Concurrency Conflicts

**Error**: `ConcurrencyException: Concurrent modification detected`

**Solution**:
- Reload the invoice: `$invoice = $invoice->fresh()`
- Retry the operation with updated data
- This is expected behavior to prevent lost updates

### Issue 5: Missing Line Items

**Error**: Invoices don't have line items

**Solution**:
- Only new invoices (issued after migration) will have line items
- Old invoices remain unchanged
- Run data migration script if needed (optional)

---

## Performance Considerations

### Database Indexes

Ensure proper indexes exist:

```sql
-- Invoice line items
CREATE INDEX idx_supplier_invoice_line_items_invoice_id 
ON supplier_invoice_line_items(supplier_invoice_id);

CREATE INDEX idx_customer_invoice_line_items_invoice_id 
ON customer_invoice_line_items(customer_invoice_id);

-- Discrepancy tracking
CREATE INDEX idx_supplier_invoices_discrepancy 
ON supplier_invoices(discrepancy_detected, status);

CREATE INDEX idx_customer_invoices_discrepancy 
ON customer_invoices(discrepancy_detected, status);

-- Audit logs
CREATE INDEX idx_audit_logs_invoice_entity 
ON audit_logs(entity_type, entity_id, occurred_at);
```

### Query Optimization

For large datasets, consider:

1. **Eager Loading**: Always load line items with invoices
```php
$invoice = SupplierInvoice::with('lineItems')->find($id);
```

2. **Pagination**: Use pagination for invoice lists
```php
$invoices = SupplierInvoice::with('lineItems')->paginate(15);
```

3. **Caching**: Cache frequently accessed data
```php
Cache::remember("invoice.{$id}", 3600, function() use ($id) {
    return SupplierInvoice::with('lineItems')->find($id);
});
```

---

## Monitoring & Maintenance

### 1. Monitor Discrepancies

Check for flagged discrepancies regularly:

```php
$flaggedCount = SupplierInvoice::where('discrepancy_detected', true)
    ->where('status', 'pending_approval')
    ->count();
```

### 2. Monitor Immutability Violations

Check violation attempts:

```php
$violations = App\Models\InvoiceModificationAttempt::whereDate('attempted_at', today())->count();
```

### 3. Monitor Concurrency Conflicts

Check audit logs for conflicts:

```php
$conflicts = App\Models\AuditLog::where('action', 'invoice.concurrency_conflict')
    ->whereDate('occurred_at', today())
    ->count();
```

### 4. Database Maintenance

```bash
# Optimize tables monthly
php artisan db:optimize

# Clean old audit logs (optional, after 1 year)
php artisan audit:clean --days=365
```

---

## Support & Troubleshooting

### Debug Mode

Enable detailed logging:

```php
// config/logging.php
'channels' => [
    'invoice' => [
        'driver' => 'daily',
        'path' => storage_path('logs/invoice.log'),
        'level' => 'debug',
        'days' => 14,
    ],
],
```

### Health Check Command

Create a health check command:

```bash
php artisan invoice:health-check
```

Should verify:
- [ ] BCMath extension enabled
- [ ] Database precision correct
- [ ] Observers registered
- [ ] Permissions configured
- [ ] Audit logging working

---

## Migration Checklist

- [ ] Backup database and code
- [ ] Verify BCMath extension installed
- [ ] Run all migrations
- [ ] Configure organization defaults
- [ ] Update permissions
- [ ] Clear all caches
- [ ] Test invoice issuance
- [ ] Test immutability protection
- [ ] Test concurrency control
- [ ] Verify audit trail
- [ ] Monitor for 24 hours
- [ ] Document any issues
- [ ] Train users on new features

---

## Next Steps

After successful migration:

1. **User Training**: Train Finance staff on discrepancy approval workflow
2. **Documentation**: Share API documentation with developers
3. **Monitoring**: Set up alerts for discrepancies and violations
4. **Optimization**: Monitor performance and optimize queries
5. **Backup**: Establish regular backup schedule

---

## Contact & Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Run tests: `php artisan test --filter=Invoice`
- Review audit trail: Check `audit_logs` table
- Consult developer guide: `docs/PHARMACEUTICAL_INVOICE_DEVELOPER_GUIDE.md`

---

**Migration Guide Version**: 1.0  
**Last Updated**: April 13, 2026  
**Compatibility**: Laravel 11.x, PHP 8.3+
