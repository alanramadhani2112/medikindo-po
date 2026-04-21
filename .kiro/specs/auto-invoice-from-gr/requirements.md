# Requirements Document

## Introduction

Sistem ini mengotomatisasi pembuatan invoice setelah Goods Receipt (GR) selesai. Ketika GR diterima lengkap oleh RS/Klinik, sistem akan otomatis membuat draft Supplier Invoice (AP) dan mengirim notifikasi spesifik ke Finance untuk melengkapi detail invoice. Setelah Supplier Invoice diverifikasi, sistem akan otomatis membuat draft Customer Invoice (AR) melalui MirrorGenerationService yang sudah ada, dan mengirim notifikasi ke Finance untuk menerbitkan invoice ke RS/Klinik.

## Glossary

- **GR (Goods_Receipt)**: Dokumen penerimaan barang dari supplier ke RS/Klinik
- **Supplier_Invoice (AP)**: Invoice hutang dari Medikindo ke supplier/distributor
- **Customer_Invoice (AR)**: Invoice tagihan dari Medikindo ke RS/Klinik
- **Finance**: User dengan role Finance atau Super Admin yang mengelola invoice
- **Draft_Invoice**: Invoice yang sudah dibuat sistem tapi belum lengkap/belum diterbitkan
- **MirrorGenerationService**: Service yang sudah ada untuk membuat AR dari AP yang terverifikasi
- **InvoiceFromGRService**: Service yang sudah ada untuk membuat invoice dari GR
- **Auto_Invoice_Generator**: Service baru yang mengotomatisasi pembuatan draft Supplier Invoice
- **Finance_Notification**: Notifikasi spesifik ke Finance dengan action link

## Requirements

### Requirement 1: Auto-Generate Supplier Invoice Draft

**User Story:** As a Finance user, I want the system to automatically create a draft Supplier Invoice when GR is completed, so that I don't have to manually navigate to the invoice creation form.

#### Acceptance Criteria

1. WHEN a GR status transitions to 'completed', THE Auto_Invoice_Generator SHALL create a draft Supplier_Invoice with all line items from GR items
2. THE Auto_Invoice_Generator SHALL pre-populate product_id, quantity, batch_no, expiry_date, and unit_price from GR items
3. THE Auto_Invoice_Generator SHALL set Supplier_Invoice status to 'draft'
4. THE Auto_Invoice_Generator SHALL leave distributor_invoice_number, distributor_invoice_date, and payment_proof fields empty for Finance to complete
5. THE Auto_Invoice_Generator SHALL link the Supplier_Invoice to the GR via goods_receipt_id
6. THE Auto_Invoice_Generator SHALL link the Supplier_Invoice to the PO via purchase_order_id
7. THE Auto_Invoice_Generator SHALL set due_date to 30 days from creation date
8. IF a draft Supplier_Invoice already exists for the GR, THEN THE Auto_Invoice_Generator SHALL skip creation and log a warning

### Requirement 2: Send Targeted Finance Notification for Supplier Invoice

**User Story:** As a Finance user, I want to receive a specific notification when a draft Supplier Invoice is auto-created, so that I know exactly what action I need to take.

#### Acceptance Criteria

1. WHEN a draft Supplier_Invoice is auto-created, THE Auto_Invoice_Generator SHALL send a Finance_Notification to all users with 'view_supplier_invoices' permission
2. THE Finance_Notification SHALL include the GR number, PO number, and Supplier_Invoice number
3. THE Finance_Notification SHALL include a direct action link to the Supplier_Invoice detail page
4. THE Finance_Notification SHALL specify that Finance needs to complete distributor invoice details and payment proof
5. THE Finance_Notification SHALL be stored in the database notifications table
6. THE Finance_Notification SHALL be sent via email to Finance users
7. THE Finance_Notification SHALL have notification type 'supplier_invoice_draft_created'

### Requirement 3: Maintain Existing Verification Flow

**User Story:** As a Finance user, I want the existing AP verification flow to remain unchanged, so that I can continue using the familiar process.

#### Acceptance Criteria

1. THE System SHALL preserve the existing Supplier_Invoice verification workflow
2. WHEN Finance verifies a Supplier_Invoice, THE MirrorGenerationService SHALL auto-create a draft Customer_Invoice as it currently does
3. THE System SHALL maintain all existing immutability rules for verified invoices
4. THE System SHALL maintain all existing anti-phantom billing guards
5. THE System SHALL maintain all existing duplicate mirror checks

### Requirement 4: Send Targeted Finance Notification for Customer Invoice

**User Story:** As a Finance user, I want to receive a specific notification when a draft Customer Invoice is auto-created from verified AP, so that I know I need to review and issue it to RS/Klinik.

#### Acceptance Criteria

1. WHEN MirrorGenerationService creates a draft Customer_Invoice, THE System SHALL send a Finance_Notification to all users with 'view_customer_invoices' permission
2. THE Finance_Notification SHALL include the Customer_Invoice number, Supplier_Invoice number, and customer organization name
3. THE Finance_Notification SHALL include a direct action link to the Customer_Invoice detail page
4. THE Finance_Notification SHALL specify that Finance needs to review and issue the invoice to RS/Klinik
5. THE Finance_Notification SHALL have notification type 'customer_invoice_draft_created'
6. THE Finance_Notification SHALL be stored in the database notifications table
7. THE Finance_Notification SHALL be sent via email to Finance users

### Requirement 5: Audit Trail for Auto-Generated Invoices

**User Story:** As a Finance manager, I want to see audit logs for all auto-generated invoices, so that I can track when and why invoices were created automatically.

#### Acceptance Criteria

1. WHEN a draft Supplier_Invoice is auto-created, THE Auto_Invoice_Generator SHALL log an audit entry with action 'supplier_invoice.auto_created_from_gr'
2. THE Audit_Log SHALL include GR id, GR number, PO id, PO number, Supplier_Invoice id, and total_amount
3. THE Audit_Log SHALL record the system user as the actor (or a designated system user id)
4. WHEN a draft Customer_Invoice is auto-created via MirrorGenerationService, THE System SHALL log an audit entry with action 'customer_invoice.auto_created_from_ap'
5. THE Audit_Log SHALL include Supplier_Invoice id, Customer_Invoice id, organization_id, and total_amount
6. THE Audit_Log SHALL be queryable via the existing AuditService

### Requirement 6: Handle Partial GR Deliveries

**User Story:** As a Finance user, I want the system to only auto-create Supplier Invoice when GR is fully completed, so that I don't receive premature invoice notifications for partial deliveries.

#### Acceptance Criteria

1. WHEN a GR status is 'partial', THE Auto_Invoice_Generator SHALL NOT create a draft Supplier_Invoice
2. WHEN a GR transitions from 'partial' to 'completed', THE Auto_Invoice_Generator SHALL create a draft Supplier_Invoice
3. THE Auto_Invoice_Generator SHALL include all items from all deliveries in the Supplier_Invoice line items
4. THE Auto_Invoice_Generator SHALL aggregate quantities from multiple deliveries for the same product

### Requirement 7: Preserve Manual Invoice Creation Capability

**User Story:** As a Finance user, I want to retain the ability to manually create invoices from the UI, so that I can handle exceptional cases that require manual intervention.

#### Acceptance Criteria

1. THE System SHALL preserve the existing manual invoice creation form at /invoices/supplier/create
2. THE System SHALL allow Finance users to manually create Supplier_Invoice from any completed GR
3. IF a draft Supplier_Invoice already exists (auto-created), THEN THE System SHALL display a warning message and prevent duplicate creation
4. THE System SHALL allow Finance users to manually issue Customer_Invoice from draft status
5. THE System SHALL maintain all existing validation rules for manual invoice creation

### Requirement 8: Error Handling and Resilience

**User Story:** As a system administrator, I want the auto-invoice generation to handle errors gracefully, so that a failure in invoice creation doesn't block GR completion.

#### Acceptance Criteria

1. IF Auto_Invoice_Generator fails to create a draft Supplier_Invoice, THEN THE System SHALL log the error and continue GR completion
2. IF Finance_Notification fails to send, THEN THE System SHALL log the error but not rollback the invoice creation
3. THE Auto_Invoice_Generator SHALL wrap invoice creation in a try-catch block
4. THE Auto_Invoice_Generator SHALL log all exceptions with full context (GR id, error message, stack trace)
5. IF a database constraint violation occurs, THEN THE System SHALL log the error and skip invoice creation
6. THE System SHALL expose error metrics for monitoring (e.g., failed auto-invoice count)

### Requirement 9: Notification Content and Formatting

**User Story:** As a Finance user, I want notifications to be clear and actionable, so that I can quickly understand what needs to be done.

#### Acceptance Criteria

1. THE Finance_Notification for Supplier_Invoice SHALL have title "Invoice Pemasok Perlu Dilengkapi"
2. THE Finance_Notification for Supplier_Invoice SHALL have message format: "Draft Invoice Pemasok {invoice_number} telah dibuat otomatis untuk GR {gr_number}. Silakan lengkapi nomor invoice distributor, tanggal invoice, dan bukti pembayaran."
3. THE Finance_Notification for Customer_Invoice SHALL have title "Tagihan ke RS/Klinik Siap Diterbitkan"
4. THE Finance_Notification for Customer_Invoice SHALL have message format: "Draft Tagihan {invoice_number} untuk {organization_name} telah dibuat otomatis. Silakan review dan terbitkan ke RS/Klinik."
5. THE Finance_Notification SHALL use icon 'info' for both notification types
6. THE Finance_Notification SHALL use type 'info' for database notifications

### Requirement 10: Integration with Existing Services

**User Story:** As a developer, I want the auto-invoice feature to reuse existing services, so that we maintain consistency and avoid code duplication.

#### Acceptance Criteria

1. THE Auto_Invoice_Generator SHALL use InvoiceFromGRService::createSupplierInvoiceFromGR() to create draft Supplier_Invoice
2. THE Auto_Invoice_Generator SHALL use AuditService::log() to record audit entries
3. THE System SHALL use the existing MirrorGenerationService::generateARFromAP() for Customer_Invoice creation
4. THE System SHALL use the existing NewInvoiceNotification class for Customer_Invoice notifications
5. THE Auto_Invoice_Generator SHALL use the existing InvoiceCalculationService for price calculations
6. THE Auto_Invoice_Generator SHALL respect all existing business rules in InvoiceFromGRService (quantity validation, batch/expiry consistency, etc.)
