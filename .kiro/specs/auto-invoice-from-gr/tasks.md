# Implementation Plan: Auto-Invoice from Goods Receipt

## Overview

This implementation automates invoice generation after Goods Receipt completion. The system will auto-create draft Supplier Invoices (AP) when GR is completed, send targeted notifications to Finance users, and enhance the existing MirrorGenerationService to send specific notifications when Customer Invoices (AR) are auto-created from verified AP.

## Tasks

- [ ] 1. Create AutoInvoiceGeneratorService
  - [ ] 1.1 Create service class with dependency injection
    - Create `app/Services/AutoInvoiceGeneratorService.php`
    - Inject `InvoiceFromGRService` and `AuditService` dependencies
    - _Requirements: 10.1, 10.2_
  
  - [ ] 1.2 Implement generateSupplierInvoiceFromGR() method
    - Implement guard checks: draft exists, GR completed status
    - Call `prepareLineItemsFromGR()` to prepare line items
    - Use `InvoiceFromGRService::createSupplierInvoiceFromGR()` to create draft invoice
    - Set due_date to 30 days, add auto-generation notes
    - Wrap in try-catch for non-blocking error handling
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 8.1, 8.3, 8.4_
  
  - [ ] 1.3 Implement draftExists() method
    - Query `supplier_invoices` table for existing draft with matching `goods_receipt_id`
    - Return boolean result
    - _Requirements: 1.8_
  
  - [ ] 1.4 Implement prepareLineItemsFromGR() method
    - Extract line items from GR items
    - Map product_id, quantity, batch_no, expiry_date, unit_price
    - Handle partial deliveries by aggregating quantities
    - _Requirements: 1.2, 6.3, 6.4_
  
  - [ ] 1.5 Implement notifyFinanceUsers() method
    - Query users with 'view_supplier_invoices' permission
    - Send `SupplierInvoiceDraftCreatedNotification` to Finance users
    - Wrap in try-catch to prevent notification failures from blocking invoice creation
    - _Requirements: 2.1, 8.2_
  
  - [ ] 1.6 Add audit logging in generateSupplierInvoiceFromGR()
    - Log action 'supplier_invoice.auto_created_from_gr' using AuditService
    - Include GR id, GR number, PO id, PO number, invoice id, total_amount, line_items_count
    - Use system user (ID 1) as actor
    - _Requirements: 5.1, 5.2, 5.3_
  
  - [ ] 1.7 Add comprehensive error logging
    - Log warnings for duplicate drafts and invalid GR status
    - Log errors for invoice creation failures with full context
    - Include GR id, GR number, error message, stack trace
    - _Requirements: 8.4, 8.5_

- [ ] 2. Create GoodsReceiptObserver
  - [ ] 2.1 Create observer class
    - Create `app/Observers/GoodsReceiptObserver.php`
    - Inject `AutoInvoiceGeneratorService` dependency
    - _Requirements: 1.1_
  
  - [ ] 2.2 Implement updated() event handler
    - Detect status transition to 'completed' using `wasChanged('status')`
    - Check if new status is `GoodsReceipt::STATUS_COMPLETED`
    - Call `AutoInvoiceGeneratorService::generateSupplierInvoiceFromGR()`
    - _Requirements: 1.1, 6.1, 6.2_

- [ ] 3. Create SupplierInvoiceDraftCreatedNotification
  - [ ] 3.1 Create notification class
    - Create `app/Notifications/SupplierInvoiceDraftCreatedNotification.php`
    - Accept `SupplierInvoice` and `GoodsReceipt` in constructor
    - Use `Queueable` trait for async processing
    - _Requirements: 2.1_
  
  - [ ] 3.2 Implement via() method
    - Return channels: `['database', 'mail']`
    - _Requirements: 2.5, 2.6_
  
  - [ ] 3.3 Implement toMail() method
    - Set subject: "Invoice Pemasok Perlu Dilengkapi — GR #{gr_number}"
    - Include greeting with user name
    - Include invoice number, GR number, PO number, total amount
    - Add action button linking to supplier invoice detail page
    - Use Indonesian language for message content
    - _Requirements: 2.2, 2.3, 2.4, 2.6, 9.1, 9.2_
  
  - [ ] 3.4 Implement toArray() method
    - Include invoice_id, invoice_number, gr_id, gr_number, po_number
    - Set title: "Invoice Pemasok Perlu Dilengkapi"
    - Set message with Indonesian format
    - Include URL to supplier invoice detail page
    - Set icon: 'info', type: 'info', notification_type: 'supplier_invoice_draft_created'
    - _Requirements: 2.2, 2.3, 2.5, 2.7, 9.1, 9.2, 9.5, 9.6_

- [ ] 4. Create CustomerInvoiceDraftCreatedNotification
  - [ ] 4.1 Create notification class
    - Create `app/Notifications/CustomerInvoiceDraftCreatedNotification.php`
    - Accept `CustomerInvoice` and `SupplierInvoice` in constructor
    - Use `Queueable` trait for async processing
    - _Requirements: 4.1_
  
  - [ ] 4.2 Implement via() method
    - Return channels: `['database', 'mail']`
    - _Requirements: 4.6, 4.7_
  
  - [ ] 4.3 Implement toMail() method
    - Set subject: "Tagihan ke RS/Klinik Siap Diterbitkan — {organization_name}"
    - Include greeting with user name
    - Include invoice number, customer name, supplier invoice number, total amount
    - Add action button linking to customer invoice detail page
    - Use Indonesian language for message content
    - _Requirements: 4.2, 4.3, 4.4, 4.7, 9.3, 9.4_
  
  - [ ] 4.4 Implement toArray() method
    - Include invoice_id, invoice_number, supplier_invoice_id, supplier_invoice_number, organization_name
    - Set title: "Tagihan ke RS/Klinik Siap Diterbitkan"
    - Set message with Indonesian format
    - Include URL to customer invoice detail page
    - Set icon: 'info', type: 'info', notification_type: 'customer_invoice_draft_created'
    - _Requirements: 4.2, 4.3, 4.5, 4.6, 9.3, 9.4, 9.5, 9.6_

- [ ] 5. Enhance MirrorGenerationService
  - [ ] 5.1 Update notifyFinanceStaff() method
    - Replace `NewInvoiceNotification` with `CustomerInvoiceDraftCreatedNotification`
    - Pass `CustomerInvoice` and `SupplierInvoice` to notification constructor
    - Query users with 'view_customer_invoices' permission
    - Wrap in try-catch to prevent notification failures from blocking AR creation
    - _Requirements: 4.1, 10.4_
  
  - [ ] 5.2 Add audit logging for AR auto-creation
    - Log action 'customer_invoice.auto_created_from_ap' using AuditService
    - Include supplier_invoice_id, customer_invoice_id, organization_id, total_amount
    - Use system user as actor
    - _Requirements: 5.4, 5.5_

- [ ] 6. Register observer in EventServiceProvider
  - [ ] 6.1 Add GoodsReceiptObserver to $observers array
    - Open `app/Providers/EventServiceProvider.php`
    - Add `GoodsReceipt::class => [GoodsReceiptObserver::class]` to $observers
    - _Requirements: 1.1_

- [ ] 7. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ]* 8. Write unit tests for AutoInvoiceGeneratorService
  - [ ]* 8.1 Test generateSupplierInvoiceFromGR() with completed GR
    - Create completed GR with items
    - Call service method
    - Assert draft Supplier Invoice created with correct data
    - Assert line items match GR items
    - Assert audit log created
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 5.1, 5.2_
  
  - [ ]* 8.2 Test generateSupplierInvoiceFromGR() skips if draft exists
    - Create completed GR
    - Create existing draft Supplier Invoice for GR
    - Call service method
    - Assert returns null
    - Assert warning logged
    - _Requirements: 1.8_
  
  - [ ]* 8.3 Test generateSupplierInvoiceFromGR() skips if GR not completed
    - Create partial GR
    - Call service method
    - Assert returns null
    - Assert warning logged
    - _Requirements: 6.1_
  
  - [ ]* 8.4 Test generateSupplierInvoiceFromGR() handles errors gracefully
    - Mock InvoiceFromGRService to throw exception
    - Call service method
    - Assert returns null (doesn't throw)
    - Assert error logged with context
    - _Requirements: 8.1, 8.3, 8.4_
  
  - [ ]* 8.5 Test draftExists() returns true when draft exists
    - Create GR with existing draft invoice
    - Call draftExists()
    - Assert returns true
    - _Requirements: 1.8_
  
  - [ ]* 8.6 Test draftExists() returns false when no draft exists
    - Create GR without draft invoice
    - Call draftExists()
    - Assert returns false
    - _Requirements: 1.8_
  
  - [ ]* 8.7 Test prepareLineItemsFromGR() maps GR items correctly
    - Create GR with multiple items
    - Call prepareLineItemsFromGR()
    - Assert array contains product_id, quantity, batch_no, expiry_date, unit_price
    - _Requirements: 1.2, 6.3_
  
  - [ ]* 8.8 Test prepareLineItemsFromGR() aggregates partial deliveries
    - Create GR with multiple deliveries for same product
    - Call prepareLineItemsFromGR()
    - Assert quantities aggregated correctly
    - _Requirements: 6.3, 6.4_
  
  - [ ]* 8.9 Test notifyFinanceUsers() sends notifications
    - Create Finance users with 'view_supplier_invoices' permission
    - Create invoice and GR
    - Call notifyFinanceUsers()
    - Assert notifications sent to Finance users
    - _Requirements: 2.1_
  
  - [ ]* 8.10 Test notifyFinanceUsers() handles notification failures
    - Mock Notification facade to throw exception
    - Call notifyFinanceUsers()
    - Assert doesn't throw (graceful handling)
    - Assert warning logged
    - _Requirements: 8.2_

- [ ]* 9. Write unit tests for GoodsReceiptObserver
  - [ ]* 9.1 Test updated() triggers auto-invoice on status change to completed
    - Create partial GR
    - Update status to 'completed'
    - Assert AutoInvoiceGeneratorService called
    - _Requirements: 1.1, 6.2_
  
  - [ ]* 9.2 Test updated() doesn't trigger on other status changes
    - Create GR
    - Update status to 'partial'
    - Assert AutoInvoiceGeneratorService not called
    - _Requirements: 6.1_
  
  - [ ]* 9.3 Test updated() doesn't trigger on non-status updates
    - Create completed GR
    - Update notes field
    - Assert AutoInvoiceGeneratorService not called
    - _Requirements: 1.1_

- [ ]* 10. Write unit tests for SupplierInvoiceDraftCreatedNotification
  - [ ]* 10.1 Test toMail() returns correct MailMessage
    - Create notification with invoice and GR
    - Call toMail()
    - Assert subject contains GR number
    - Assert body contains invoice number, GR number, PO number, total amount
    - Assert action link points to supplier invoice detail page
    - _Requirements: 2.2, 2.3, 2.4, 2.6, 9.1, 9.2_
  
  - [ ]* 10.2 Test toArray() returns correct notification data
    - Create notification with invoice and GR
    - Call toArray()
    - Assert contains invoice_id, invoice_number, gr_id, gr_number, po_number
    - Assert title and message in Indonesian
    - Assert notification_type is 'supplier_invoice_draft_created'
    - _Requirements: 2.2, 2.3, 2.5, 2.7, 9.1, 9.2, 9.5, 9.6_
  
  - [ ]* 10.3 Test via() returns correct channels
    - Create notification
    - Call via()
    - Assert returns ['database', 'mail']
    - _Requirements: 2.5, 2.6_

- [ ]* 11. Write unit tests for CustomerInvoiceDraftCreatedNotification
  - [ ]* 11.1 Test toMail() returns correct MailMessage
    - Create notification with customer invoice and supplier invoice
    - Call toMail()
    - Assert subject contains organization name
    - Assert body contains invoice number, customer name, supplier invoice number, total amount
    - Assert action link points to customer invoice detail page
    - _Requirements: 4.2, 4.3, 4.4, 4.7, 9.3, 9.4_
  
  - [ ]* 11.2 Test toArray() returns correct notification data
    - Create notification with invoices
    - Call toArray()
    - Assert contains invoice_id, invoice_number, supplier_invoice_id, supplier_invoice_number, organization_name
    - Assert title and message in Indonesian
    - Assert notification_type is 'customer_invoice_draft_created'
    - _Requirements: 4.2, 4.3, 4.5, 4.6, 9.3, 9.4, 9.5, 9.6_
  
  - [ ]* 11.3 Test via() returns correct channels
    - Create notification
    - Call via()
    - Assert returns ['database', 'mail']
    - _Requirements: 4.6, 4.7_

- [ ]* 12. Write unit tests for enhanced MirrorGenerationService
  - [ ]* 12.1 Test notifyFinanceStaff() uses CustomerInvoiceDraftCreatedNotification
    - Create customer invoice with supplier invoice
    - Call notifyFinanceStaff()
    - Assert CustomerInvoiceDraftCreatedNotification sent (not NewInvoiceNotification)
    - Assert sent to users with 'view_customer_invoices' permission
    - _Requirements: 4.1, 10.4_
  
  - [ ]* 12.2 Test AR auto-creation audit logging
    - Create verified supplier invoice
    - Trigger AR generation
    - Assert audit log created with action 'customer_invoice.auto_created_from_ap'
    - Assert includes supplier_invoice_id, customer_invoice_id, organization_id, total_amount
    - _Requirements: 5.4, 5.5_

- [ ]* 13. Write integration tests
  - [ ]* 13.1 Test complete flow: GR completion → AP draft → Notification
    - Create partial GR with items
    - Update GR status to 'completed'
    - Assert draft Supplier Invoice created in database
    - Assert notification sent to Finance users
    - Assert audit log created
    - _Requirements: 1.1, 2.1, 5.1_
  
  - [ ]* 13.2 Test complete flow: AP verification → AR draft → Notification
    - Create draft Supplier Invoice
    - Update status to 'verified'
    - Assert draft Customer Invoice created in database
    - Assert CustomerInvoiceDraftCreatedNotification sent to Finance users
    - Assert audit log created
    - _Requirements: 3.2, 4.1, 5.4_
  
  - [ ]* 13.3 Test end-to-end flow: GR → AP → AR
    - Create partial GR
    - Complete GR
    - Verify AP
    - Assert both AP and AR created
    - Assert both notifications sent
    - Assert both audit logs created
    - _Requirements: 1.1, 3.2, 4.1_
  
  - [ ]* 13.4 Test duplicate prevention
    - Create completed GR
    - Manually create draft Supplier Invoice for GR
    - Trigger observer again
    - Assert no duplicate invoice created
    - Assert warning logged
    - _Requirements: 1.8_
  
  - [ ]* 13.5 Test partial GR doesn't trigger auto-invoice
    - Create partial GR
    - Assert no Supplier Invoice created
    - Assert no notification sent
    - _Requirements: 6.1_
  
  - [ ]* 13.6 Test error handling doesn't block GR completion
    - Mock InvoiceFromGRService to throw exception
    - Complete GR
    - Assert GR status is 'completed' (not rolled back)
    - Assert error logged
    - _Requirements: 8.1_
  
  - [ ]* 13.7 Test manual invoice creation still works
    - Create completed GR
    - Manually create Supplier Invoice via UI/API
    - Assert invoice created successfully
    - Assert existing validation rules applied
    - _Requirements: 7.1, 7.2, 7.5_
  
  - [ ]* 13.8 Test notification failure doesn't rollback invoice
    - Mock Notification facade to throw exception
    - Complete GR
    - Assert Supplier Invoice created in database
    - Assert error logged
    - _Requirements: 8.2_

- [ ] 14. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- The design reuses existing services (InvoiceFromGRService, MirrorGenerationService, AuditService) to maintain consistency
- Error handling is non-blocking to ensure GR completion is never blocked by invoice generation failures
- Notifications are targeted to Finance users only with specific action links
- All auto-generation events are audited for compliance tracking
