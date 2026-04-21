# Design Document: Expiry & Batch System Strengthening

## Overview

This design implements three surgical fixes to strengthen the existing batch-based inventory system in the Medikindo B2B Healthcare platform. The system already has the correct database structure with batch-level expiry tracking (`inventory_items.expiry_date`, `goods_receipt_items.expiry_date`). These changes add business rule enforcement to prevent expired goods entry, implement FEFO allocation, and block expired items from customer invoices.

**Key Design Principles:**
- No database schema changes
- Backward compatible with existing data
- Minimal code changes (surgical fixes only)
- Leverage existing Laravel validation and query builder features

## Architecture

### System Context

The changes affect three layers of the application:

1. **Validation Layer** (`app/Http/Requests/StoreGoodsReceiptRequest.php`)
   - Add expiry date validation rule at goods receipt entry point
   - Reject past/current-day expiry dates before database writes

2. **Service Layer** (`app/Services/InventoryService.php`)
   - Modify `reduceStock()` method to change from FIFO to FEFO ordering
   - Add expiry date exclusion filter to prevent allocation of expired batches

3. **Invoice Generation Layer** (`app/Services/MirrorGenerationService.php`)
   - Add expiry validation in `generateARFromAP()` before customer invoice creation
   - Check all line items for expired batches and reject if found

### Data Flow

```
Goods Receipt Entry → Validation (expiry > today) → Create GR → Add to Inventory
                                    ↓
                            Supplier Invoice (AP) → Verify AP → Mirror Generation
                                                                        ↓
                                                    Expiry Check → Create Customer Invoice (AR)
                                                                        ↓
                                                    Stock Reduction (FEFO) → Inventory Movement
```

## Components and Interfaces

### Component 1: Expiry Date Validation (Requirement 1)

**File:** `app/Http/Requests/StoreGoodsReceiptRequest.php`

**Current State:**
```php
'items.*.expiry_date' => 'required|date',
```

**Modified State:**
```php
'items.*.expiry_date' => 'required|date|after:today',
```

**Error Message:**
```php
'items.*.expiry_date.after' => 'Cannot receive expired goods: expiry date must be after today (Batch: :batch_no)',
```

**Interface Contract:**
- **Input:** Array of items with `expiry_date`, `batch_no`, `product_id`
- **Output:** Validation passes (HTTP 200) or fails (HTTP 422 with error details)
- **Side Effects:** None (validation only)

**Validation Behavior:**
- Laravel's `after:today` rule validates that the date is strictly after the current date
- Validation runs before controller logic, preventing any database writes
- Error messages include batch number for traceability

### Component 2: FEFO Stock Allocation (Requirement 2)

**File:** `app/Services/InventoryService.php`

**Method:** `reduceStock()`

**Current Query (FIFO):**
```php
$inventoryItems = InventoryItem::where('organization_id', $organizationId)
    ->where('product_id', $productId)
    ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
    ->orderBy('created_at', 'asc')  // FIFO: oldest created first
    ->get();
```

**Modified Query (FEFO):**
```php
$inventoryItems = InventoryItem::where('organization_id', $organizationId)
    ->where('product_id', $productId)
    ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
    ->where(function ($query) {
        $query->whereNull('expiry_date')
              ->orWhereDate('expiry_date', '>', now());
    })
    ->orderBy('expiry_date', 'asc')      // FEFO: earliest expiry first
    ->orderBy('created_at', 'asc')       // Tie-breaker: oldest created first
    ->get();
```

**Interface Contract:**
- **Input:** 
  - `organizationId` (int)
  - `productId` (int)
  - `quantity` (int)
  - `referenceType` (string)
  - `referenceId` (int)
  - `createdBy` (int)
- **Output:** Array of `InventoryMovement` records
- **Side Effects:** 
  - Decrements `quantity_on_hand` on selected `InventoryItem` records
  - Creates `InventoryMovement` records with TYPE_OUT

**Allocation Logic:**
1. Query available batches (quantity_available > 0, not expired)
2. Order by `expiry_date ASC, created_at ASC`
3. Iterate through batches, allocating quantity until fulfilled
4. Record movements for each batch used

**Backward Compatibility:**
- Batches with `NULL` expiry_date are included (legacy data support)
- Existing inventory movements remain unchanged
- No migration required

### Component 3: Expired Item Blocking in Customer Invoices (Requirement 3)

**File:** `app/Services/MirrorGenerationService.php`

**Method:** `generateARFromAP()`

**Insertion Point:** After Guard 2 (anti-phantom billing check), before DB transaction

**New Validation Method:**
```php
/**
 * Validate that no line items contain expired batches
 * 
 * @param SupplierInvoice $apInvoice
 * @throws AntiPhantomBillingException
 */
private function validateNoExpiredItems(SupplierInvoice $apInvoice): void
{
    $apInvoice->loadMissing('lineItems.product');
    
    foreach ($apInvoice->lineItems as $lineItem) {
        if ($lineItem->expiry_date && $lineItem->expiry_date->isToday()) {
            throw new AntiPhantomBillingException(
                "Cannot invoice expired item: {$lineItem->product_name} " .
                "(Batch: {$lineItem->batch_no}, Expired: {$lineItem->expiry_date->format('Y-m-d')})"
            );
        }
        
        if ($lineItem->expiry_date && $lineItem->expiry_date->isPast()) {
            throw new AntiPhantomBillingException(
                "Cannot invoice expired item: {$lineItem->product_name} " .
                "(Batch: {$lineItem->batch_no}, Expired: {$lineItem->expiry_date->format('Y-m-d')})"
            );
        }
    }
}
```

**Integration:**
```php
public function generateARFromAP(SupplierInvoice $apInvoice, int $customerId): CustomerInvoice
{
    // Guard 1: duplicate check
    if ($this->draftExists($apInvoice->id)) { ... }
    
    // Guard 2: anti-phantom billing
    if (!in_array($statusValue, $allowedStatuses, true)) { ... }
    
    // Guard 3: expiry validation (NEW)
    $this->validateNoExpiredItems($apInvoice);
    
    // Continue with DB transaction...
}
```

**Interface Contract:**
- **Input:** `SupplierInvoice` with loaded `lineItems` relationship
- **Output:** Void (throws exception on failure)
- **Side Effects:** None (validation only)
- **Exception:** `AntiPhantomBillingException` with descriptive message

**Validation Behavior:**
- Checks each line item's `expiry_date` field
- Rejects if `expiry_date` is today or in the past
- Includes product name, batch number, and expiry date in error message
- Runs before any database writes (fail-fast principle)

## Data Models

### Existing Models (No Changes)

**InventoryItem**
```php
- id: bigint
- organization_id: bigint
- product_id: bigint
- batch_no: string
- expiry_date: date (nullable)  // Already exists
- quantity_on_hand: int
- quantity_reserved: int
- unit_cost: decimal
- location: string (nullable)
- created_at: timestamp
- updated_at: timestamp
```

**GoodsReceiptItem**
```php
- id: bigint
- goods_receipt_id: bigint
- purchase_order_item_id: bigint
- quantity_received: int
- batch_no: string
- expiry_date: date (nullable)  // Already exists
- condition: string (nullable)
- notes: text (nullable)
- created_at: timestamp
- updated_at: timestamp
```

**SupplierInvoiceLineItem**
```php
- id: bigint
- supplier_invoice_id: bigint
- goods_receipt_item_id: bigint
- product_id: bigint
- product_name: string
- batch_no: string
- expiry_date: date (nullable)  // Already exists
- quantity: decimal
- unit_price: decimal
- discount_percentage: decimal
- discount_amount: decimal
- tax_rate: decimal
- tax_amount: decimal
- line_total: decimal
- created_at: timestamp
- updated_at: timestamp
```

**CustomerInvoiceLineItem**
```php
- id: bigint
- customer_invoice_id: bigint
- supplier_invoice_item_id: bigint
- product_id: bigint
- product_name: string
- batch_no: string
- expiry_date: date (nullable)  // Already exists
- quantity: decimal
- unit_price: decimal
- cost_price: decimal
- discount_percentage: decimal
- discount_amount: decimal
- tax_rate: decimal
- tax_amount: decimal
- line_total: decimal
- uom: string (nullable)
- created_at: timestamp
- updated_at: timestamp
```

### Data Flow Through Models

1. **Goods Receipt Entry:**
   - User submits `expiry_date` in `StoreGoodsReceiptRequest`
   - Validation ensures `expiry_date > today`
   - `GoodsReceiptItem` created with validated `expiry_date`
   - `InventoryItem` created/updated with same `expiry_date`

2. **Supplier Invoice Creation:**
   - `SupplierInvoiceLineItem` copies `expiry_date` from `GoodsReceiptItem` (read-only)
   - No validation at this stage (goods already in inventory)

3. **Customer Invoice Generation:**
   - `MirrorGenerationService` validates `SupplierInvoiceLineItem.expiry_date`
   - Rejects if expired, otherwise creates `CustomerInvoiceLineItem`
   - `CustomerInvoiceLineItem` copies `expiry_date` from `SupplierInvoiceLineItem`

4. **Stock Reduction:**
   - `InventoryService::reduceStock()` queries `InventoryItem` with FEFO ordering
   - Excludes expired batches from allocation
   - Creates `InventoryMovement` records linking to selected batches

## Error Handling

### Error Scenarios and Responses

#### 1. Expired Goods Receipt Entry (Requirement 1)

**Trigger:** User submits goods receipt with `expiry_date <= today`

**Response:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "items.0.expiry_date": [
      "Cannot receive expired goods: expiry date must be after today (Batch: BATCH123)"
    ]
  }
}
```

**HTTP Status:** 422 Unprocessable Entity

**User Action:** Correct the expiry date or reject the goods receipt

#### 2. Insufficient Non-Expired Stock (Requirement 2)

**Trigger:** `reduceStock()` called but only expired batches available

**Response:**
```php
throw new \Exception("Insufficient stock. Available: 0, Required: {$quantity}");
```

**HTTP Status:** 500 Internal Server Error (caught by controller)

**User Action:** Check inventory, remove expired batches, or adjust order quantity

**Note:** This is existing behavior; FEFO change makes it more likely to occur if expired batches aren't removed

#### 3. Expired Item in Customer Invoice (Requirement 3)

**Trigger:** `generateARFromAP()` called with expired line items

**Response:**
```php
throw new AntiPhantomBillingException(
    "Cannot invoice expired item: Paracetamol 500mg " .
    "(Batch: BATCH123, Expired: 2024-01-15)"
);
```

**HTTP Status:** 400 Bad Request (caught by controller)

**User Action:** Remove expired batches from inventory before creating customer invoice

### Error Recovery Strategies

1. **Prevention at Entry Point:**
   - Validation at goods receipt prevents expired items from entering inventory
   - Reduces likelihood of downstream errors

2. **Graceful Degradation:**
   - FEFO allocation automatically skips expired batches
   - If no valid batches available, clear error message guides user action

3. **Fail-Fast Principle:**
   - Invoice validation runs before database transaction
   - No partial state created on validation failure

4. **Audit Trail:**
   - All validation failures logged in Laravel logs
   - Batch numbers and expiry dates included in error messages for traceability

## Testing Strategy

### Unit Tests

**Test File:** `tests/Unit/Services/InventoryServiceTest.php`

**Test Cases:**
1. **FEFO Ordering:**
   - Create multiple batches with different expiry dates
   - Call `reduceStock()`
   - Assert batches selected in expiry date order (earliest first)

2. **Expiry Date Tie-Breaker:**
   - Create multiple batches with same expiry date but different created_at
   - Call `reduceStock()`
   - Assert batches selected by created_at order (oldest first)

3. **Expired Batch Exclusion:**
   - Create batches with past expiry dates
   - Call `reduceStock()`
   - Assert expired batches not selected

4. **NULL Expiry Date Handling:**
   - Create batches with NULL expiry_date (legacy data)
   - Call `reduceStock()`
   - Assert NULL expiry batches included in allocation

5. **Insufficient Stock Error:**
   - Create only expired batches
   - Call `reduceStock()`
   - Assert exception thrown with correct message

**Test File:** `tests/Unit/Services/MirrorGenerationServiceTest.php`

**Test Cases:**
1. **Expired Item Rejection:**
   - Create SupplierInvoice with expired line item
   - Call `generateARFromAP()`
   - Assert `AntiPhantomBillingException` thrown

2. **Today Expiry Rejection:**
   - Create SupplierInvoice with today's expiry date
   - Call `generateARFromAP()`
   - Assert `AntiPhantomBillingException` thrown

3. **Valid Expiry Acceptance:**
   - Create SupplierInvoice with future expiry dates
   - Call `generateARFromAP()`
   - Assert CustomerInvoice created successfully

4. **NULL Expiry Handling:**
   - Create SupplierInvoice with NULL expiry_date
   - Call `generateARFromAP()`
   - Assert CustomerInvoice created successfully (legacy data support)

**Test File:** `tests/Unit/Requests/StoreGoodsReceiptRequestTest.php`

**Test Cases:**
1. **Past Expiry Date Rejection:**
   - Submit request with `expiry_date` = yesterday
   - Assert validation fails with correct error message

2. **Today Expiry Date Rejection:**
   - Submit request with `expiry_date` = today
   - Assert validation fails with correct error message

3. **Future Expiry Date Acceptance:**
   - Submit request with `expiry_date` = tomorrow
   - Assert validation passes

4. **Error Message Format:**
   - Submit request with invalid expiry date
   - Assert error message includes batch number

### Integration Tests

**Test File:** `tests/Feature/GoodsReceiptTest.php`

**Test Cases:**
1. **End-to-End Goods Receipt with Valid Expiry:**
   - POST to goods receipt endpoint with future expiry date
   - Assert HTTP 201 Created
   - Assert GoodsReceiptItem created with correct expiry_date
   - Assert InventoryItem created with correct expiry_date

2. **End-to-End Goods Receipt with Expired Date:**
   - POST to goods receipt endpoint with past expiry date
   - Assert HTTP 422 Unprocessable Entity
   - Assert no GoodsReceiptItem created
   - Assert no InventoryItem created

**Test File:** `tests/Feature/CustomerInvoiceTest.php`

**Test Cases:**
1. **End-to-End Invoice Generation with Valid Batches:**
   - Create verified SupplierInvoice with future expiry dates
   - Call mirror generation
   - Assert CustomerInvoice created
   - Assert stock reduced using FEFO

2. **End-to-End Invoice Generation with Expired Batches:**
   - Create verified SupplierInvoice with expired line items
   - Call mirror generation
   - Assert HTTP 400 Bad Request
   - Assert no CustomerInvoice created
   - Assert no stock reduced

3. **FEFO Stock Reduction Integration:**
   - Create multiple inventory batches with different expiry dates
   - Create and verify SupplierInvoice
   - Generate CustomerInvoice
   - Assert stock reduced from earliest expiry batch first
   - Assert InventoryMovement records created in correct order

### Manual Testing Checklist

1. **Goods Receipt Entry:**
   - [ ] Submit GR with past expiry date → Validation error displayed
   - [ ] Submit GR with today's expiry date → Validation error displayed
   - [ ] Submit GR with future expiry date → GR created successfully
   - [ ] Error message includes batch number and product name

2. **Stock Allocation:**
   - [ ] Create multiple batches with different expiry dates
   - [ ] Create customer invoice
   - [ ] Verify stock reduced from earliest expiry batch first
   - [ ] Check InventoryMovement records show correct batch allocation

3. **Customer Invoice Generation:**
   - [ ] Create SupplierInvoice with expired line item
   - [ ] Attempt to generate CustomerInvoice → Error displayed
   - [ ] Error message includes product name, batch number, expiry date
   - [ ] Create SupplierInvoice with valid expiry dates → CustomerInvoice created

4. **Backward Compatibility:**
   - [ ] Existing inventory items with NULL expiry_date still allocate correctly
   - [ ] Existing goods receipts with NULL expiry_date still process correctly
   - [ ] No errors on legacy data

## Implementation Notes

### Code Changes Summary

**File 1:** `app/Http/Requests/StoreGoodsReceiptRequest.php`
- **Lines Changed:** 1 line modified, 1 line added
- **Change:** Update validation rule and add error message

**File 2:** `app/Services/InventoryService.php`
- **Lines Changed:** 5 lines modified in `reduceStock()` method
- **Change:** Update query ordering and add expiry filter

**File 3:** `app/Services/MirrorGenerationService.php`
- **Lines Changed:** 1 method added (~20 lines), 1 line added in `generateARFromAP()`
- **Change:** Add `validateNoExpiredItems()` method and call it

**Total Impact:** ~30 lines of code changed across 3 files

### Deployment Considerations

1. **No Database Migration Required:**
   - All necessary columns already exist
   - No schema changes needed

2. **No Data Migration Required:**
   - Changes are forward-compatible
   - Existing data with NULL expiry_date handled gracefully

3. **Zero Downtime Deployment:**
   - Changes are additive (stricter validation)
   - No breaking changes to existing functionality

4. **Rollback Strategy:**
   - Simple git revert if issues arise
   - No database state to clean up

### Performance Considerations

1. **FEFO Query Performance:**
   - Existing index on `product_id` and `organization_id` sufficient
   - `ORDER BY expiry_date, created_at` uses existing columns
   - No additional indexes required

2. **Validation Performance:**
   - Laravel validation runs in-memory (no database queries)
   - Expiry check in MirrorGenerationService adds minimal overhead (single loop)

3. **Expected Load:**
   - Goods receipt entry: ~100-500 requests/day
   - Stock reduction: ~500-2000 operations/day
   - Invoice generation: ~50-200 invoices/day
   - All operations complete in <100ms

### Security Considerations

1. **Input Validation:**
   - Laravel validation prevents SQL injection
   - Date format validation prevents malformed input

2. **Authorization:**
   - Existing authorization middleware unchanged
   - No new permission requirements

3. **Audit Trail:**
   - All validation failures logged automatically by Laravel
   - InventoryMovement records provide full audit trail of stock allocation

### Monitoring and Observability

1. **Metrics to Track:**
   - Count of goods receipt validation failures (expiry date)
   - Count of customer invoice generation failures (expired items)
   - Distribution of stock allocation by batch age

2. **Alerts to Configure:**
   - Spike in goods receipt validation failures (may indicate supplier issue)
   - Repeated customer invoice generation failures (may indicate inventory cleanup needed)

3. **Logging:**
   - All exceptions logged with full context (batch number, product, expiry date)
   - Laravel's default logging sufficient (no custom logging required)
