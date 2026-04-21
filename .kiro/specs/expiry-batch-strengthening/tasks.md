# Implementation Plan: Expiry & Batch System Strengthening

## Overview

This implementation adds three surgical fixes to strengthen the existing batch-based inventory system. The changes are minimal (~30 lines across 3 files) and require no database schema changes. All necessary columns already exist in the database.

**Implementation Language:** PHP (Laravel Framework)

**Key Files to Modify:**
1. `app/Http/Requests/StoreGoodsReceiptRequest.php` - Add expiry validation
2. `app/Services/InventoryService.php` - Change FIFO to FEFO ordering
3. `app/Services/MirrorGenerationService.php` - Add expiry validation before invoice creation

## Tasks

- [x] 1. Implement expiry date validation at goods receipt
  - [x] 1.1 Update validation rule in StoreGoodsReceiptRequest
    - Modify `items.*.expiry_date` validation rule from `'required|date'` to `'required|date|after:today'`
    - Add custom error message: `'items.*.expiry_date.after' => 'Cannot receive expired goods: expiry date must be after today (Batch: :batch_no)'`
    - _Requirements: 1.1, 1.2, 1.3, 1.4_
  
  - [ ]* 1.2 Write unit tests for expiry date validation
    - Test past expiry date rejection
    - Test today's expiry date rejection
    - Test future expiry date acceptance
    - Test error message format includes batch number
    - _Requirements: 1.1, 1.2, 1.4_

- [x] 2. Implement FEFO stock allocation
  - [x] 2.1 Modify reduceStock() method in InventoryService
    - Add expiry date filter: `where(function ($query) { $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>', now()); })`
    - Change ordering from `orderBy('created_at', 'asc')` to `orderBy('expiry_date', 'asc')->orderBy('created_at', 'asc')`
    - Ensure NULL expiry dates are included (backward compatibility)
    - _Requirements: 2.1, 2.2, 2.3, 2.4_
  
  - [ ]* 2.2 Write unit tests for FEFO allocation
    - Test FEFO ordering (earliest expiry selected first)
    - Test expiry date tie-breaker (oldest created_at wins)
    - Test expired batch exclusion
    - Test NULL expiry date handling (legacy data)
    - Test insufficient stock error when only expired batches available
    - _Requirements: 2.1, 2.2, 2.3, 2.5_

- [x] 3. Checkpoint - Verify core implementation
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Implement expired item blocking in customer invoices
  - [x] 4.1 Add validateNoExpiredItems() method to MirrorGenerationService
    - Create private method that checks all line items for expired batches
    - Throw `AntiPhantomBillingException` if any item has expiry_date today or earlier
    - Include product name, batch number, and expiry date in error message
    - _Requirements: 3.1, 3.2, 3.4_
  
  - [x] 4.2 Integrate expiry validation into generateARFromAP() method
    - Call `validateNoExpiredItems()` after Guard 2 (anti-phantom billing check)
    - Ensure validation runs before database transaction begins
    - _Requirements: 3.1, 3.4_
  
  - [ ]* 4.3 Write unit tests for expiry validation in invoice generation
    - Test expired item rejection
    - Test today's expiry rejection
    - Test valid future expiry acceptance
    - Test NULL expiry handling (legacy data)
    - _Requirements: 3.1, 3.2, 3.3_

- [ ] 5. Integration testing
  - [ ]* 5.1 Write end-to-end goods receipt tests
    - Test goods receipt with valid expiry date (HTTP 201, records created)
    - Test goods receipt with expired date (HTTP 422, no records created)
    - _Requirements: 1.1, 1.2, 1.3_
  
  - [ ]* 5.2 Write end-to-end customer invoice tests
    - Test invoice generation with valid batches (invoice created, stock reduced via FEFO)
    - Test invoice generation with expired batches (HTTP 400, no invoice created)
    - Test FEFO stock reduction integration (verify earliest expiry batch used first)
    - _Requirements: 2.1, 2.4, 3.1, 3.2, 3.3_

- [x] 6. Final checkpoint - Complete testing and validation
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- All code changes are backward compatible with existing data (NULL expiry dates supported)
- No database migrations required - all columns already exist
- Zero downtime deployment possible
- Total implementation: ~30 lines of code across 3 files
