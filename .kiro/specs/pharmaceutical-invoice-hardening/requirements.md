# Requirements Document: Pharmaceutical-Grade Invoice Management Hardening

## Introduction

This feature hardens the existing Laravel invoice management system to pharmaceutical-grade standards with zero tolerance for calculation errors. The system currently manages supplier invoices (accounts payable) and customer invoices (accounts receivable) for healthcare procurement. This hardening introduces BCMath-based precision arithmetic, immutability controls, discrepancy detection, and comprehensive audit trails to ensure financial accuracy meets regulatory compliance standards for pharmaceutical supply chains.

## Glossary

- **Invoice_System**: The Laravel-based invoice management subsystem handling both supplier and customer invoices
- **BCMath_Calculator**: A service component that performs all monetary calculations using PHP's BCMath extension with scale=2
- **Line_Item**: A single row in an invoice representing one product with quantity, unit price, discount, tax, and line total
- **Immutability_Guard**: A mechanism that prevents modification of financial amounts after invoice issuance
- **Discrepancy_Engine**: A component that compares invoice amounts with purchase order amounts and flags variances
- **Audit_Trail**: A comprehensive log of all calculations, validations, state transitions, and modification attempts
- **Optimistic_Lock**: A concurrency control mechanism using version numbers to detect simultaneous modifications
- **HALF_UP_Rounding**: Banker's rounding strategy that rounds to the nearest even number when equidistant
- **Tolerance_Check**: A validation that verifies line item totals sum to invoice total within acceptable precision bounds
- **Discount_Validator**: A component that enforces discount business rules (percentage vs amount, range validation)
- **Tax_Calculator**: A component that applies configurable tax rates to taxable amounts
- **Modification_Attempt_Log**: A record of all attempts to modify immutable invoice data, whether successful or blocked

## Requirements

### Requirement 1: BCMath Precision Arithmetic

**User Story:** As a finance manager, I want all invoice calculations to use BCMath with scale=2 precision, so that rounding errors are eliminated and calculations are deterministic.

#### Acceptance Criteria

1. THE BCMath_Calculator SHALL use bcadd() for all addition operations with scale=2
2. THE BCMath_Calculator SHALL use bcsub() for all subtraction operations with scale=2
3. THE BCMath_Calculator SHALL use bcmul() for all multiplication operations with scale=2
4. THE BCMath_Calculator SHALL use bcdiv() for all division operations with scale=2
5. WHEN performing rounding, THE BCMath_Calculator SHALL apply HALF_UP_Rounding (round to nearest even)
6. THE BCMath_Calculator SHALL accept string inputs and return string outputs to preserve precision
7. WHEN converting between string and decimal types, THE BCMath_Calculator SHALL maintain exactly 2 decimal places
8. THE Audit_Trail SHALL log all calculation inputs and outputs for traceability

### Requirement 2: Database Precision Upgrade

**User Story:** As a system administrator, I want monetary fields upgraded to decimal(18,2), so that the database can store larger amounts without precision loss.

#### Acceptance Criteria

1. THE Invoice_System SHALL store total_amount as decimal(18,2) in supplier_invoices table
2. THE Invoice_System SHALL store paid_amount as decimal(18,2) in supplier_invoices table
3. THE Invoice_System SHALL store total_amount as decimal(18,2) in customer_invoices table
4. THE Invoice_System SHALL store paid_amount as decimal(18,2) in customer_invoices table
5. THE Invoice_System SHALL store subtotal_amount as decimal(18,2) in both invoice tables
6. THE Invoice_System SHALL store discount_amount as decimal(18,2) in both invoice tables
7. THE Invoice_System SHALL store tax_amount as decimal(18,2) in both invoice tables
8. WHEN migrating existing data, THE Invoice_System SHALL preserve all existing decimal values without data loss

### Requirement 3: Invoice Line Items Storage

**User Story:** As a finance auditor, I want invoice line items stored separately from the invoice header, so that I can trace calculations at the product level.

#### Acceptance Criteria

1. THE Invoice_System SHALL create a supplier_invoice_line_items table with columns: id, supplier_invoice_id, product_id, product_name, quantity, unit_price, discount_percentage, discount_amount, tax_rate, tax_amount, line_total
2. THE Invoice_System SHALL create a customer_invoice_line_items table with columns: id, customer_invoice_id, product_id, product_name, quantity, unit_price, discount_percentage, discount_amount, tax_rate, tax_amount, line_total
3. THE Invoice_System SHALL store all monetary line item fields as decimal(18,2)
4. THE Invoice_System SHALL store quantity as decimal(10,3) to support fractional quantities
5. THE Invoice_System SHALL store discount_percentage as decimal(5,2) to support percentages like 12.50%
6. THE Invoice_System SHALL store tax_rate as decimal(5,2) to support tax rates like 11.00%
7. WHEN an invoice is issued, THE Invoice_System SHALL create line items from goods receipt items
8. THE Invoice_System SHALL establish foreign key relationships between line items and invoices with cascade delete

### Requirement 4: Discount Validation Rules

**User Story:** As a finance controller, I want discount validation enforced at the business logic level, so that invalid discounts are rejected before persisting to the database.

#### Acceptance Criteria

1. WHEN discount_percentage is provided, THE Discount_Validator SHALL verify it is between 0.00 and 100.00 inclusive
2. WHEN discount_amount is provided, THE Discount_Validator SHALL verify it is between 0.00 and subtotal_amount inclusive
3. IF both discount_percentage and discount_amount are provided, THEN THE Discount_Validator SHALL reject the operation with error "Cannot specify both discount percentage and discount amount"
4. WHEN discount_percentage is provided, THE Discount_Validator SHALL calculate discount_amount as (subtotal * discount_percentage / 100) using BCMath_Calculator
5. WHEN discount_amount exceeds subtotal_amount, THE Discount_Validator SHALL reject the operation with error "Discount amount cannot exceed subtotal"
6. WHEN discount_percentage is negative, THE Discount_Validator SHALL reject the operation with error "Discount percentage must be non-negative"
7. THE Audit_Trail SHALL log all discount validation failures with input values

### Requirement 5: Tax Calculation

**User Story:** As a finance manager, I want tax calculated on the discounted subtotal using configurable tax rates, so that tax compliance is automated and accurate.

#### Acceptance Criteria

1. THE Invoice_System SHALL store tax_rate as decimal(5,2) in the organizations table
2. WHEN an invoice is issued, THE Tax_Calculator SHALL retrieve the tax_rate from the organization record
3. THE Tax_Calculator SHALL calculate taxable_amount as (subtotal_amount - discount_amount) using BCMath_Calculator
4. THE Tax_Calculator SHALL calculate tax_amount as (taxable_amount * tax_rate / 100) using BCMath_Calculator with HALF_UP_Rounding
5. THE Tax_Calculator SHALL calculate total_amount as (taxable_amount + tax_amount) using BCMath_Calculator
6. WHEN tax_rate is NULL or 0.00, THE Tax_Calculator SHALL set tax_amount to 0.00
7. THE Invoice_System SHALL store calculated tax_amount in the invoice record
8. THE Audit_Trail SHALL log tax calculation inputs (subtotal, discount, tax_rate) and output (tax_amount)

### Requirement 6: Line Item Calculation Integrity

**User Story:** As a finance auditor, I want line item totals to sum exactly to the invoice total, so that I can verify calculation integrity.

#### Acceptance Criteria

1. WHEN calculating line_total for a line item, THE BCMath_Calculator SHALL compute (quantity * unit_price) using bcmul()
2. WHEN a line item has discount_percentage, THE BCMath_Calculator SHALL compute line_discount as (line_subtotal * discount_percentage / 100)
3. WHEN a line item has discount_amount, THE BCMath_Calculator SHALL use the provided discount_amount directly
4. THE BCMath_Calculator SHALL compute line_taxable as (line_subtotal - line_discount)
5. THE BCMath_Calculator SHALL compute line_tax as (line_taxable * tax_rate / 100) with HALF_UP_Rounding
6. THE BCMath_Calculator SHALL compute line_total as (line_taxable + line_tax)
7. WHEN issuing an invoice, THE Tolerance_Check SHALL verify that sum(line_total) equals invoice.total_amount within 0.01 tolerance
8. IF the tolerance check fails, THEN THE Invoice_System SHALL reject invoice issuance with error "Line item totals do not match invoice total"

### Requirement 7: Immutability Enforcement

**User Story:** As a compliance officer, I want invoice amounts to be immutable after issuance, so that financial records cannot be tampered with.

#### Acceptance Criteria

1. WHEN an invoice status is 'issued' or later, THE Immutability_Guard SHALL prevent updates to total_amount
2. WHEN an invoice status is 'issued' or later, THE Immutability_Guard SHALL prevent updates to subtotal_amount
3. WHEN an invoice status is 'issued' or later, THE Immutability_Guard SHALL prevent updates to discount_amount
4. WHEN an invoice status is 'issued' or later, THE Immutability_Guard SHALL prevent updates to tax_amount
5. WHEN an invoice status is 'issued' or later, THE Immutability_Guard SHALL prevent updates to line item quantities
6. WHEN an invoice status is 'issued' or later, THE Immutability_Guard SHALL prevent updates to line item unit_price
7. WHEN an invoice status is 'issued' or later, THE Immutability_Guard SHALL prevent updates to line item discounts
8. WHEN an invoice status is 'issued' or later, THE Immutability_Guard SHALL prevent updates to line item tax_rate
9. IF an immutability violation is attempted, THEN THE Immutability_Guard SHALL reject the operation with error "Cannot modify financial amounts after invoice issuance"
10. THE Modification_Attempt_Log SHALL record all immutability violation attempts with user_id, timestamp, attempted_changes, and rejection_reason

### Requirement 8: Discrepancy Detection Engine

**User Story:** As a procurement manager, I want invoice amounts compared with purchase order amounts automatically, so that pricing discrepancies are flagged for review.

#### Acceptance Criteria

1. WHEN an invoice is issued, THE Discrepancy_Engine SHALL calculate expected_total from purchase order line items using BCMath_Calculator
2. THE Discrepancy_Engine SHALL calculate variance_amount as (invoice.total_amount - expected_total) using BCMath_Calculator
3. THE Discrepancy_Engine SHALL calculate variance_percentage as (variance_amount / expected_total * 100) using BCMath_Calculator
4. WHEN variance_percentage exceeds 1.00% OR variance_amount exceeds 10000.00, THE Discrepancy_Engine SHALL flag the invoice as requiring approval
5. THE Invoice_System SHALL store discrepancy_detected as boolean in invoice records
6. THE Invoice_System SHALL store variance_amount as decimal(18,2) in invoice records
7. THE Invoice_System SHALL store variance_percentage as decimal(5,2) in invoice records
8. THE Invoice_System SHALL store expected_total as decimal(18,2) in invoice records for audit trail
9. WHEN a discrepancy is detected, THE Invoice_System SHALL set status to 'pending_approval' instead of 'issued'
10. THE Audit_Trail SHALL log all discrepancy detection results with calculation details

### Requirement 9: Optimistic Locking for Concurrency

**User Story:** As a system architect, I want optimistic locking on invoice records, so that concurrent modifications are detected and prevented.

#### Acceptance Criteria

1. THE Invoice_System SHALL add a version column (integer, default 0) to supplier_invoices table
2. THE Invoice_System SHALL add a version column (integer, default 0) to customer_invoices table
3. WHEN updating an invoice, THE Invoice_System SHALL include current version in the WHERE clause
4. WHEN an update affects 0 rows due to version mismatch, THE Invoice_System SHALL throw a concurrency exception
5. WHEN an update succeeds, THE Invoice_System SHALL increment the version column by 1
6. THE Invoice_System SHALL expose the current version in the invoice model
7. IF a concurrency conflict is detected, THEN THE Invoice_System SHALL return error "Invoice was modified by another user, please refresh and retry"
8. THE Audit_Trail SHALL log all concurrency conflicts with user_id, attempted_version, and current_version

### Requirement 10: Comprehensive Audit Trail

**User Story:** As a compliance auditor, I want all invoice calculations, validations, and state transitions logged, so that I can reconstruct the complete history of any invoice.

#### Acceptance Criteria

1. WHEN a calculation is performed, THE Audit_Trail SHALL log the operation name, input values, output value, and timestamp
2. WHEN a validation fails, THE Audit_Trail SHALL log the validation rule, input values, failure reason, and timestamp
3. WHEN an invoice status changes, THE Audit_Trail SHALL log the before_status, after_status, user_id, and timestamp
4. WHEN a discrepancy is detected, THE Audit_Trail SHALL log expected_total, actual_total, variance_amount, variance_percentage, and timestamp
5. WHEN an immutability violation is attempted, THE Audit_Trail SHALL log the attempted changes, rejection reason, user_id, and timestamp
6. WHEN a concurrency conflict occurs, THE Audit_Trail SHALL log the conflicting user_ids, attempted changes, and timestamp
7. THE Audit_Trail SHALL store all monetary values as strings to preserve BCMath precision
8. THE Audit_Trail SHALL be immutable (no updates or deletes allowed)
9. THE Audit_Trail SHALL support querying by invoice_id, user_id, action_type, and date_range
10. THE Audit_Trail SHALL retain records for at least 7 years for regulatory compliance

### Requirement 11: Invoice Issuance with Line Items

**User Story:** As a finance user, I want invoices issued with detailed line items from goods receipts, so that I can see product-level pricing and calculations.

#### Acceptance Criteria

1. WHEN issuing an invoice, THE Invoice_System SHALL iterate through goods receipt items to create line items
2. FOR EACH goods receipt item, THE Invoice_System SHALL create a line item with product_id, product_name, quantity, and unit_price from the purchase order item
3. THE Invoice_System SHALL apply organization-level discount_percentage to each line item if configured
4. THE Invoice_System SHALL apply organization-level tax_rate to each line item
5. THE Invoice_System SHALL calculate line_total for each line item using BCMath_Calculator
6. THE Invoice_System SHALL calculate invoice subtotal_amount as sum of all line subtotals using BCMath_Calculator
7. THE Invoice_System SHALL calculate invoice discount_amount as sum of all line discounts using BCMath_Calculator
8. THE Invoice_System SHALL calculate invoice tax_amount as sum of all line taxes using BCMath_Calculator
9. THE Invoice_System SHALL calculate invoice total_amount as sum of all line totals using BCMath_Calculator
10. THE Tolerance_Check SHALL verify sum(line_total) equals invoice.total_amount within 0.01 tolerance before persisting
11. IF tolerance check fails, THEN THE Invoice_System SHALL reject issuance with detailed error showing expected vs actual totals

### Requirement 12: Discount and Tax Configuration

**User Story:** As a system administrator, I want to configure default discount and tax rates at the organization level, so that invoices are automatically calculated with correct rates.

#### Acceptance Criteria

1. THE Invoice_System SHALL add default_discount_percentage column (decimal 5,2) to organizations table
2. THE Invoice_System SHALL add default_tax_rate column (decimal 5,2) to organizations table
3. WHEN default_discount_percentage is NULL, THE Invoice_System SHALL treat it as 0.00
4. WHEN default_tax_rate is NULL, THE Invoice_System SHALL treat it as 0.00
5. WHEN issuing an invoice, THE Invoice_System SHALL retrieve default_discount_percentage and default_tax_rate from the organization
6. THE Invoice_System SHALL allow override of discount and tax at the invoice level
7. THE Invoice_System SHALL allow override of discount and tax at the line item level
8. WHEN line item discount or tax is NULL, THE Invoice_System SHALL use invoice-level values
9. WHEN invoice-level discount or tax is NULL, THE Invoice_System SHALL use organization-level defaults

### Requirement 13: Modification Attempt Tracking

**User Story:** As a security officer, I want all attempts to modify immutable invoice data tracked, so that I can detect and investigate tampering attempts.

#### Acceptance Criteria

1. THE Invoice_System SHALL create a invoice_modification_attempts table with columns: id, invoice_type, invoice_id, user_id, attempted_at, attempted_changes (JSON), rejection_reason, ip_address
2. WHEN an immutability violation is attempted, THE Invoice_System SHALL insert a record into invoice_modification_attempts
3. THE Invoice_System SHALL capture the attempted field changes as JSON in attempted_changes column
4. THE Invoice_System SHALL capture the user's IP address in ip_address column
5. THE Invoice_System SHALL capture the rejection reason in rejection_reason column
6. THE Modification_Attempt_Log SHALL be queryable by invoice_id, user_id, and date_range
7. THE Modification_Attempt_Log SHALL be immutable (no updates or deletes allowed)
8. THE Invoice_System SHALL send alerts to finance managers when modification attempts are detected

### Requirement 14: Rounding Consistency Validation

**User Story:** As a finance manager, I want rounding applied consistently across all calculations, so that results are reproducible and auditable.

#### Acceptance Criteria

1. THE BCMath_Calculator SHALL implement a round() method that applies HALF_UP_Rounding
2. WHEN a value is exactly halfway between two integers (e.g., 2.5), THE BCMath_Calculator SHALL round to the nearest even number (e.g., 2.0)
3. WHEN a value is exactly halfway between two integers (e.g., 3.5), THE BCMath_Calculator SHALL round to the nearest even number (e.g., 4.0)
4. THE BCMath_Calculator SHALL apply rounding only at the final step of multi-step calculations
5. THE BCMath_Calculator SHALL preserve full precision in intermediate calculations
6. THE BCMath_Calculator SHALL round final monetary amounts to exactly 2 decimal places
7. THE Audit_Trail SHALL log all rounding operations with input value, output value, and rounding mode

### Requirement 15: Invoice Parser and Pretty Printer

**User Story:** As a developer, I want to parse invoice data from external formats and serialize back to the same format, so that I can integrate with external systems reliably.

#### Acceptance Criteria

1. THE Invoice_System SHALL provide an Invoice_Parser that parses JSON invoice data into Invoice objects
2. WHEN parsing JSON, THE Invoice_Parser SHALL validate all required fields are present
3. WHEN parsing JSON, THE Invoice_Parser SHALL validate all monetary amounts are valid decimal strings
4. WHEN parsing JSON, THE Invoice_Parser SHALL validate all discount and tax values meet business rules
5. IF parsing fails, THEN THE Invoice_Parser SHALL return descriptive error messages indicating which field failed validation
6. THE Invoice_System SHALL provide an Invoice_Pretty_Printer that formats Invoice objects into JSON
7. THE Invoice_Pretty_Printer SHALL format all monetary amounts as strings with exactly 2 decimal places
8. THE Invoice_Pretty_Printer SHALL format all percentage values as strings with exactly 2 decimal places
9. FOR ALL valid Invoice objects, parsing then printing then parsing SHALL produce an equivalent object (round-trip property)
10. THE Audit_Trail SHALL log all parsing failures with input data and error messages

### Requirement 16: Property-Based Testing for Calculations

**User Story:** As a quality assurance engineer, I want property-based tests for invoice calculations, so that edge cases and calculation properties are verified automatically.

#### Acceptance Criteria

1. THE Invoice_System SHALL include property-based tests verifying addition is associative: (a + b) + c = a + (b + c)
2. THE Invoice_System SHALL include property-based tests verifying addition is commutative: a + b = b + a
3. THE Invoice_System SHALL include property-based tests verifying subtraction inverse: a - b + b = a
4. THE Invoice_System SHALL include property-based tests verifying multiplication is associative: (a * b) * c = a * (b * c)
5. THE Invoice_System SHALL include property-based tests verifying multiplication is commutative: a * b = b * a
6. THE Invoice_System SHALL include property-based tests verifying discount validation rules hold for all valid inputs
7. THE Invoice_System SHALL include property-based tests verifying tax calculation produces non-negative results
8. THE Invoice_System SHALL include property-based tests verifying line item totals always sum to invoice total within tolerance
9. THE Invoice_System SHALL include property-based tests verifying rounding is consistent for the same input
10. THE Invoice_System SHALL include property-based tests verifying immutability guards prevent all modification attempts after issuance
11. THE Invoice_System SHALL include property-based tests verifying round-trip property for Invoice_Parser and Invoice_Pretty_Printer

### Requirement 17: Discrepancy Approval Workflow

**User Story:** As a finance manager, I want to review and approve invoices with discrepancies, so that legitimate price variations can be authorized while preventing errors.

#### Acceptance Criteria

1. WHEN a discrepancy is detected, THE Invoice_System SHALL set invoice status to 'pending_approval'
2. THE Invoice_System SHALL send notifications to users with 'approve_invoice_discrepancy' permission
3. THE Invoice_System SHALL provide an approve_discrepancy() method that transitions status from 'pending_approval' to 'issued'
4. THE Invoice_System SHALL provide a reject_discrepancy() method that transitions status from 'pending_approval' to 'rejected'
5. WHEN approving a discrepancy, THE Invoice_System SHALL require an approval_reason (text field)
6. WHEN rejecting a discrepancy, THE Invoice_System SHALL require a rejection_reason (text field)
7. THE Invoice_System SHALL store approved_by (user_id) and approved_at (timestamp) when discrepancy is approved
8. THE Invoice_System SHALL store rejected_by (user_id) and rejected_at (timestamp) when discrepancy is rejected
9. THE Audit_Trail SHALL log all discrepancy approval and rejection actions with reasons
10. WHEN an invoice is rejected, THE Invoice_System SHALL prevent further status transitions

### Requirement 18: Migration Safety and Rollback

**User Story:** As a database administrator, I want database migrations to be reversible and data-preserving, so that I can safely upgrade and rollback if needed.

#### Acceptance Criteria

1. THE Invoice_System SHALL provide a migration that adds new columns without dropping existing columns
2. THE Invoice_System SHALL provide a migration that upgrades decimal(15,2) to decimal(18,2) without data loss
3. THE Invoice_System SHALL provide a migration that creates line items tables with proper foreign keys
4. THE Invoice_System SHALL provide a migration that adds version columns with default value 0
5. THE Invoice_System SHALL provide a migration that adds discrepancy tracking columns
6. THE Invoice_System SHALL provide a migration that adds immutability tracking columns
7. THE Invoice_System SHALL provide a down() migration that reverses all schema changes
8. WHEN rolling back, THE Invoice_System SHALL preserve existing invoice data
9. THE Invoice_System SHALL provide a data migration script that populates line items from existing invoices
10. THE Invoice_System SHALL validate data integrity after migration using automated tests

### Requirement 19: Performance Optimization for BCMath

**User Story:** As a system architect, I want BCMath calculations optimized for performance, so that invoice processing remains fast even with precise arithmetic.

#### Acceptance Criteria

1. THE BCMath_Calculator SHALL cache frequently used values (e.g., "0.00", "100.00") to avoid repeated string conversions
2. THE BCMath_Calculator SHALL batch line item calculations to minimize function call overhead
3. THE BCMath_Calculator SHALL use bccomp() for equality checks instead of string comparison
4. THE BCMath_Calculator SHALL validate input strings are numeric before passing to BCMath functions
5. WHEN processing invoices with more than 100 line items, THE Invoice_System SHALL complete issuance within 5 seconds
6. THE Invoice_System SHALL use database transactions to batch line item inserts
7. THE Invoice_System SHALL use eager loading to minimize N+1 query problems when loading invoices with line items
8. THE Invoice_System SHALL index foreign key columns in line items tables for query performance

### Requirement 20: Error Handling and Recovery

**User Story:** As a finance user, I want clear error messages when invoice operations fail, so that I can understand and correct the problem.

#### Acceptance Criteria

1. WHEN a calculation error occurs, THE Invoice_System SHALL return error messages in format "Calculation failed: {operation} with inputs {values}"
2. WHEN a validation error occurs, THE Invoice_System SHALL return error messages in format "Validation failed: {rule} - {reason}"
3. WHEN a concurrency conflict occurs, THE Invoice_System SHALL return error message "Invoice was modified by another user, please refresh and retry"
4. WHEN an immutability violation occurs, THE Invoice_System SHALL return error message "Cannot modify {field} after invoice issuance"
5. WHEN a tolerance check fails, THE Invoice_System SHALL return error message "Line item totals ({actual}) do not match invoice total ({expected})"
6. WHEN a database transaction fails, THE Invoice_System SHALL rollback all changes and return the original error
7. THE Invoice_System SHALL log all errors to the application log with full stack traces
8. THE Invoice_System SHALL return HTTP 422 for validation errors with detailed field-level error messages
9. THE Invoice_System SHALL return HTTP 409 for concurrency conflicts
10. THE Invoice_System SHALL return HTTP 403 for immutability violations
