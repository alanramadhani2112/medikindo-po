# Requirements Document: Expiry & Batch System Strengthening

## Introduction

The Medikindo B2B Healthcare System currently has a batch-based inventory structure (70% complete) that requires strengthening to meet healthcare compliance standards. This feature focuses on three critical fixes to prevent expired goods entry, implement First Expiry First Out (FEFO) allocation, and block expired items from customer invoices. The system already has the correct database structure with batch-level expiry tracking; these requirements add essential business rule enforcement without breaking changes.

## Glossary

- **Goods_Receipt_System**: The subsystem responsible for receiving goods from suppliers and recording batch numbers and expiry dates
- **Inventory_Service**: The service layer component that manages stock allocation and reduction operations
- **Invoice_Service**: The service layer component that creates customer invoices from goods receipts
- **Batch**: A specific lot of product identified by a unique batch number with its own expiry date
- **FEFO**: First Expiry First Out - inventory allocation strategy that prioritizes items with the earliest expiry date
- **FIFO**: First In First Out - inventory allocation strategy that prioritizes items received earliest (current implementation)
- **Expiry_Date**: The date after which a product batch should not be sold or used

## Requirements

### Requirement 1: Expiry Date Validation at Goods Receipt

**User Story:** As a warehouse manager, I want the system to reject goods with past or current-day expiry dates during goods receipt, so that expired products never enter our inventory.

#### Acceptance Criteria

1. WHEN a goods receipt is submitted with an expiry_date that is today or earlier, THE Goods_Receipt_System SHALL reject the submission with error message "Cannot receive expired goods: expiry date must be after today"
2. WHEN a goods receipt is submitted with an expiry_date that is after today, THE Goods_Receipt_System SHALL accept the submission and create the goods receipt record
3. THE Goods_Receipt_System SHALL validate expiry_date for all items in the goods receipt before creating any inventory records
4. WHEN validation fails for any item, THE Goods_Receipt_System SHALL return the batch_no and product name in the error message

### Requirement 2: FEFO Stock Allocation Implementation

**User Story:** As a compliance officer, I want the system to allocate inventory using First Expiry First Out (FEFO) instead of First In First Out (FIFO), so that products with earlier expiry dates are sold first to minimize waste and ensure compliance.

#### Acceptance Criteria

1. WHEN the Inventory_Service reduces stock for a product, THE Inventory_Service SHALL select batches ordered by expiry_date ascending (earliest expiry first)
2. WHEN multiple batches have the same expiry_date, THE Inventory_Service SHALL use created_at ascending as a tie-breaker
3. WHEN the Inventory_Service selects batches for allocation, THE Inventory_Service SHALL exclude batches where expiry_date is today or earlier
4. WHEN the Inventory_Service allocates stock from multiple batches, THE Inventory_Service SHALL record which batches were used and their quantities in the inventory movement log
5. FOR ALL stock reduction operations, applying FEFO allocation then checking the selected batches SHALL show that batches with earlier expiry dates are selected before batches with later expiry dates (FEFO property)

### Requirement 3: Expired Item Blocking in Customer Invoices

**User Story:** As a sales manager, I want the system to prevent creating customer invoices that contain expired items, so that we never sell expired products to customers.

#### Acceptance Criteria

1. WHEN the Invoice_Service creates a customer invoice, THE Invoice_Service SHALL check the expiry_date of all inventory items before creating the invoice
2. IF any inventory item has an expiry_date that is today or earlier, THEN THE Invoice_Service SHALL reject the invoice creation with error message "Cannot invoice expired item: [product_name] (Batch: [batch_no], Expired: [expiry_date])"
3. WHEN all inventory items have expiry_date after today, THE Invoice_Service SHALL proceed with invoice creation
4. THE Invoice_Service SHALL perform expiry validation before any database writes for the invoice
