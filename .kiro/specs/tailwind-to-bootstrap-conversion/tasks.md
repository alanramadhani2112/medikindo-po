# Implementation Plan: Tailwind to Bootstrap Conversion

## Overview

This implementation plan converts all Laravel Blade views from Tailwind CSS to Bootstrap 5 with Metronic 8 theme styling. The conversion follows a priority-based sequence across 12 view categories, ensuring visual consistency, functional integrity, and complete elimination of Tailwind CSS dependencies. Each task builds incrementally, with checkpoints to validate progress and ensure no regressions.

## Tasks

- [x] 1. Set up validation and reference infrastructure
  - Create CSS validation script to detect remaining Tailwind classes
  - Document class mapping patterns from BOOTSTRAP_QUICK_REFERENCE.md
  - Review existing converted views (purchase-orders/index.blade.php) as reference templates
  - Set up browser testing environment for responsive validation
  - _Requirements: 5.1, 5.2, 17.1, 17.2, 17.3_

- [-] 2. Convert Dashboard views
  - [x] 2.1 Convert resources/views/dashboard/index.blade.php
    - Replace all Tailwind utility classes with Bootstrap 5/Metronic 8 equivalents
    - Convert card layouts to Metronic card patterns (card-flush, card-custom)
    - Convert icons to Keenicons format (ki-outline ki-{icon-name})
    - Apply Metronic typography classes (fs-1 to fs-7, fw-bold, fw-semibold)
    - Ensure responsive design with Bootstrap breakpoints (col-12 col-md-6 col-lg-4)
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 3.1, 3.2, 7.1, 7.2, 12.1, 20.1, 20.2_
  
  - [x] 2.2 Validate Dashboard conversion
    - Run CSS validation script to verify zero Tailwind classes remain
    - Test responsive design at mobile (< 576px), tablet (≥ 768px), desktop (≥ 992px) breakpoints
    - Verify all dashboard widgets render correctly with Bootstrap styling
    - Test navigation and interactive elements for functional integrity
    - _Requirements: 4.1, 4.2, 4.3, 5.1, 6.1, 18.1_

- [x] 3. Checkpoint - Dashboard complete
  - Ensure all tests pass, ask the user if questions arise.

- [-] 4. Convert Purchase Orders views
  - [x] 4.1 Convert resources/views/purchase-orders/create.blade.php
    - Replace Tailwind form classes with Bootstrap form-control and form-select classes
    - Use existing Blade components (x-input, x-select, x-textarea, x-button) where appropriate
    - Apply Metronic form styling (form-control-solid, form-select-solid)
    - Convert form labels to form-label with required class where needed
    - Ensure proper form spacing with mb-5 classes
    - _Requirements: 1.1, 1.2, 2.1, 2.2, 9.1, 9.2, 9.3, 9.4, 9.5, 12.2_
  
  - [x] 4.2 Convert resources/views/purchase-orders/edit.blade.php
    - Apply same form conversion patterns as create.blade.php
    - Preserve all Blade directives (@if, @foreach, @can, @cannot)
    - Maintain route references and CSRF tokens
    - Convert action buttons to Bootstrap button patterns (btn btn-primary, btn btn-light)
    - _Requirements: 1.5, 1.6, 2.1, 2.2, 6.2, 6.5, 10.1, 10.2, 12.2_
  
  - [x] 4.3 Convert resources/views/purchase-orders/show.blade.php
    - Convert detail view layout to Metronic card patterns
    - Apply badge styling for status indicators (badge badge-light-{color})
    - Convert data display sections to Bootstrap typography classes
    - Ensure proper spacing and alignment with Metronic utilities
    - _Requirements: 1.1, 1.4, 7.1, 7.2, 11.1, 11.2, 11.3, 12.2, 20.3_
  
  - [x] 4.4 Validate Purchase Orders conversion
    - Run CSS validation script on all purchase-orders views
    - Test form submission functionality (create, edit)
    - Verify status badges display correctly with appropriate colors
    - Test responsive design across all breakpoints
    - _Requirements: 4.1, 4.2, 4.3, 5.1, 6.1, 6.2_

- [ ] 5. Checkpoint - Purchase Orders complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Convert Approvals views
  - [ ] 6.1 Convert resources/views/approvals/index.blade.php
    - Convert data table to Metronic table pattern (table table-row-dashed table-row-gray-300)
    - Apply table header styling (fw-bold text-muted)
    - Use min-w-{size}px classes for column width control
    - Convert action buttons in table cells (text-end alignment)
    - Wrap table in table-responsive div
    - _Requirements: 1.1, 1.2, 2.3, 2.5, 8.1, 8.2, 8.3, 8.4, 8.5, 12.3_
  
  - [ ] 6.2 Convert resources/views/approvals/show.blade.php
    - Convert approval detail view to Metronic card layout
    - Apply badge styling for approval status (pending, approved, rejected)
    - Convert action buttons (approve/reject) to Bootstrap button patterns
    - Ensure proper icon usage in buttons (ki-check, ki-cross)
    - _Requirements: 1.1, 3.3, 7.1, 10.1, 10.4, 11.1, 11.2, 12.3_
  
  - [ ] 6.3 Validate Approvals conversion
    - Run CSS validation script on approvals views
    - Test table rendering and responsive behavior
    - Verify approval/rejection functionality works correctly
    - Test permission checks (@can/@cannot) are preserved
    - _Requirements: 1.8, 4.1, 4.2, 5.1, 6.1, 6.3_

- [ ] 7. Checkpoint - Approvals complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 8. Convert Goods Receipts views
  - [ ] 8.1 Convert resources/views/goods-receipts/index.blade.php
    - Convert filter form to Metronic card pattern (card card-flush mb-7)
    - Apply row g-4 layout for filter fields
    - Use form-control-solid and form-select-solid for filter inputs
    - Convert filter buttons with appropriate spacing (d-flex gap-2)
    - Convert data table following Metronic table patterns
    - _Requirements: 1.1, 1.2, 2.5, 8.1, 8.2, 12.4, 19.1, 19.2, 19.3, 19.4, 19.5_
  
  - [ ] 8.2 Convert resources/views/goods-receipts/create.blade.php
    - Convert form layout to Bootstrap grid (row, col-md-{size})
    - Use Blade components for form inputs where appropriate
    - Apply Metronic form styling consistently
    - Convert item selection table to Bootstrap table pattern
    - _Requirements: 1.1, 2.1, 2.2, 9.1, 9.2, 9.7, 12.4_
  
  - [ ] 8.3 Convert resources/views/goods-receipts/show.blade.php
    - Convert detail view to Metronic card layout
    - Apply badge styling for receipt status
    - Convert items table to Metronic table pattern
    - Ensure proper typography hierarchy (fs-2, fs-3, fs-6)
    - _Requirements: 1.1, 7.1, 8.1, 11.1, 20.1, 20.2, 20.3, 12.4_
  
  - [ ] 8.4 Validate Goods Receipts conversion
    - Run CSS validation script on goods-receipts views
    - Test filter form functionality
    - Verify form submission and data display
    - Test responsive design across breakpoints
    - _Requirements: 4.1, 4.2, 5.1, 6.1, 6.2_

- [ ] 9. Checkpoint - Goods Receipts complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 10. Convert Invoices views
  - [ ] 10.1 Convert resources/views/invoices/index.blade.php
    - Convert filter form to Metronic card pattern
    - Convert invoices table to Metronic table pattern
    - Apply badge styling for invoice status (draft, pending, paid, overdue)
    - Map status values to badge colors (draft → secondary, pending → warning, paid → success, overdue → danger)
    - Add pagination styling with Bootstrap pagination component
    - _Requirements: 1.1, 2.5, 8.1, 8.2, 11.1, 11.2, 12.5, 15.1, 15.2, 15.3, 15.4_
  
  - [ ] 10.2 Convert resources/views/invoices/create.blade.php
    - Convert invoice form to Bootstrap grid layout
    - Use Blade components for form inputs
    - Convert line items table to Metronic table pattern
    - Apply proper form validation error display styling
    - _Requirements: 1.1, 2.1, 2.2, 6.5, 9.1, 9.2, 12.5_
  
  - [ ] 10.3 Convert resources/views/invoices/edit.blade.php
    - Apply same conversion patterns as create.blade.php
    - Preserve all Blade directives and route references
    - Convert action buttons to Bootstrap patterns
    - Ensure proper spacing and layout consistency
    - _Requirements: 1.5, 1.6, 2.1, 6.2, 10.1, 12.5_
  
  - [ ] 10.4 Convert resources/views/invoices/show.blade.php
    - Convert invoice detail view to Metronic card layout
    - Apply badge styling for invoice status
    - Convert line items table to Metronic table pattern
    - Add proper typography for amounts and totals (fw-bold, fs-3)
    - _Requirements: 1.1, 7.1, 8.1, 11.1, 20.1, 20.2, 12.5_
  
  - [ ] 10.5 Validate Invoices conversion
    - Run CSS validation script on invoices views
    - Test invoice creation and editing functionality
    - Verify status badges display with correct colors
    - Test pagination and filtering
    - _Requirements: 4.1, 5.1, 6.1, 6.2, 15.4_

- [ ] 11. Checkpoint - Invoices complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 12. Convert Payments views
  - [ ] 12.1 Convert resources/views/payments/index.blade.php
    - Convert filter form to Metronic card pattern
    - Convert payments table to Metronic table pattern
    - Apply badge styling for payment status and type
    - Add pagination styling
    - _Requirements: 1.1, 2.5, 8.1, 11.1, 12.6, 15.1, 19.1_
  
  - [ ] 12.2 Convert resources/views/payments/create.blade.php
    - Convert payment form to Bootstrap grid layout
    - Use Blade components for form inputs
    - Apply Metronic form styling (form-control-solid)
    - Convert payment allocation section to Bootstrap layout
    - _Requirements: 1.1, 2.1, 2.2, 9.1, 9.2, 12.6_
  
  - [ ] 12.3 Convert resources/views/payments/show.blade.php
    - Convert payment detail view to Metronic card layout
    - Apply badge styling for payment status
    - Convert allocation table to Metronic table pattern
    - Ensure proper typography for amounts
    - _Requirements: 1.1, 7.1, 8.1, 11.1, 20.3, 12.6_
  
  - [ ] 12.4 Validate Payments conversion
    - Run CSS validation script on payments views
    - Test payment creation functionality
    - Verify payment allocation display
    - Test responsive design
    - _Requirements: 4.1, 5.1, 6.1, 6.2_

- [ ] 13. Checkpoint - Payments complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 14. Convert Financial Controls views
  - [ ] 14.1 Convert resources/views/financial-controls/credit-limits.blade.php
    - Convert credit limits table to Metronic table pattern
    - Apply badge styling for limit status
    - Convert action buttons to Bootstrap patterns
    - Ensure proper responsive design
    - _Requirements: 1.1, 2.5, 8.1, 10.1, 11.1, 12.7_
  
  - [ ] 14.2 Convert resources/views/financial-controls/credit-usage.blade.php
    - Convert usage display to Metronic card layout
    - Apply progress bar styling for credit utilization
    - Convert usage table to Metronic table pattern
    - Use appropriate color coding (success, warning, danger)
    - _Requirements: 1.1, 7.1, 8.1, 11.2, 12.7_
  
  - [ ] 14.3 Validate Financial Controls conversion
    - Run CSS validation script on financial-controls views
    - Test credit limit management functionality
    - Verify credit usage calculations display correctly
    - Test responsive design
    - _Requirements: 4.1, 5.1, 6.1_

- [ ] 15. Checkpoint - Financial Controls complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 16. Convert Organizations views
  - [ ] 16.1 Convert resources/views/organizations/index.blade.php
    - Convert organizations table to Metronic table pattern
    - Apply badge styling for organization status
    - Convert action buttons to Bootstrap patterns
    - Add proper responsive design
    - _Requirements: 1.1, 2.5, 8.1, 10.1, 11.1, 12.8_
  
  - [ ] 16.2 Convert resources/views/organizations/create.blade.php
    - Convert organization form to Bootstrap grid layout
    - Use Blade components for form inputs
    - Apply Metronic form styling
    - Convert address fields section to proper layout
    - _Requirements: 1.1, 2.1, 2.2, 9.1, 9.2, 12.8_
  
  - [ ] 16.3 Convert resources/views/organizations/edit.blade.php
    - Apply same conversion patterns as create.blade.php
    - Preserve all Blade directives
    - Convert action buttons to Bootstrap patterns
    - _Requirements: 1.5, 1.6, 2.1, 10.1, 12.8_
  
  - [ ] 16.4 Convert resources/views/organizations/show.blade.php
    - Convert organization detail view to Metronic card layout
    - Apply proper typography hierarchy
    - Convert related data sections to Bootstrap layout
    - _Requirements: 1.1, 7.1, 20.1, 20.2, 12.8_
  
  - [ ] 16.5 Validate Organizations conversion
    - Run CSS validation script on organizations views
    - Test organization CRUD functionality
    - Verify responsive design
    - _Requirements: 4.1, 5.1, 6.1, 6.2_

- [ ] 17. Checkpoint - Organizations complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 18. Convert Suppliers views
  - [ ] 18.1 Convert resources/views/suppliers/index.blade.php
    - Convert filter form to Metronic card pattern
    - Convert suppliers table to Metronic table pattern
    - Apply badge styling for supplier status
    - Add pagination styling
    - _Requirements: 1.1, 2.5, 8.1, 11.1, 12.9, 15.1, 19.1_
  
  - [ ] 18.2 Convert resources/views/suppliers/create.blade.php
    - Convert supplier form to Bootstrap grid layout
    - Use Blade components for form inputs
    - Apply Metronic form styling
    - Convert contact information section to proper layout
    - _Requirements: 1.1, 2.1, 2.2, 9.1, 9.2, 12.9_
  
  - [ ] 18.3 Convert resources/views/suppliers/edit.blade.php
    - Apply same conversion patterns as create.blade.php
    - Preserve all Blade directives and route references
    - Convert action buttons to Bootstrap patterns
    - _Requirements: 1.5, 1.6, 2.1, 10.1, 12.9_
  
  - [ ] 18.4 Convert resources/views/suppliers/show.blade.php
    - Convert supplier detail view to Metronic card layout
    - Apply proper typography hierarchy
    - Convert related transactions table to Metronic pattern
    - _Requirements: 1.1, 7.1, 8.1, 20.1, 12.9_
  
  - [ ] 18.5 Validate Suppliers conversion
    - Run CSS validation script on suppliers views
    - Test supplier CRUD functionality
    - Verify filtering and pagination
    - Test responsive design
    - _Requirements: 4.1, 5.1, 6.1, 6.2, 15.4_

- [ ] 19. Checkpoint - Suppliers complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 20. Convert Products views
  - [ ] 20.1 Convert resources/views/products/index.blade.php
    - Convert filter form to Metronic card pattern
    - Convert products table to Metronic table pattern
    - Apply badge styling for product status
    - Add pagination styling
    - _Requirements: 1.1, 2.5, 8.1, 11.1, 12.10, 15.1, 19.1_
  
  - [ ] 20.2 Convert resources/views/products/create.blade.php
    - Convert product form to Bootstrap grid layout
    - Use Blade components for form inputs
    - Apply Metronic form styling
    - Convert pricing and inventory sections to proper layout
    - _Requirements: 1.1, 2.1, 2.2, 9.1, 9.2, 12.10_
  
  - [ ] 20.3 Convert resources/views/products/edit.blade.php
    - Apply same conversion patterns as create.blade.php
    - Preserve all Blade directives
    - Convert action buttons to Bootstrap patterns
    - _Requirements: 1.5, 1.6, 2.1, 10.1, 12.10_
  
  - [ ] 20.4 Convert resources/views/products/show.blade.php
    - Convert product detail view to Metronic card layout
    - Apply proper typography hierarchy
    - Convert product specifications to Bootstrap layout
    - _Requirements: 1.1, 7.1, 20.1, 20.2, 12.10_
  
  - [ ] 20.5 Validate Products conversion
    - Run CSS validation script on products views
    - Test product CRUD functionality
    - Verify filtering and pagination
    - Test responsive design
    - _Requirements: 4.1, 5.1, 6.1, 6.2, 15.4_

- [ ] 21. Checkpoint - Products complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 22. Convert Users views
  - [ ] 22.1 Convert resources/views/users/index.blade.php
    - Convert filter form to Metronic card pattern
    - Convert users table to Metronic table pattern
    - Apply badge styling for user roles and status
    - Add pagination styling
    - _Requirements: 1.1, 2.5, 8.1, 11.1, 12.11, 15.1, 19.1_
  
  - [ ] 22.2 Convert resources/views/users/create.blade.php
    - Convert user form to Bootstrap grid layout
    - Use Blade components for form inputs
    - Apply Metronic form styling
    - Convert role selection section to proper layout
    - _Requirements: 1.1, 2.1, 2.2, 9.1, 9.2, 12.11_
  
  - [ ] 22.3 Convert resources/views/users/edit.blade.php
    - Apply same conversion patterns as create.blade.php
    - Preserve all Blade directives and permission checks
    - Convert action buttons to Bootstrap patterns
    - _Requirements: 1.5, 1.6, 1.8, 2.1, 10.1, 12.11_
  
  - [ ] 22.4 Convert resources/views/users/show.blade.php
    - Convert user detail view to Metronic card layout
    - Apply badge styling for roles and permissions
    - Apply proper typography hierarchy
    - _Requirements: 1.1, 7.1, 11.1, 20.1, 12.11_
  
  - [ ] 22.5 Validate Users conversion
    - Run CSS validation script on users views
    - Test user CRUD functionality
    - Verify role and permission display
    - Test responsive design
    - _Requirements: 4.1, 5.1, 6.1, 6.2, 15.4_

- [ ] 23. Checkpoint - Users complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 24. Convert Notifications views
  - [ ] 24.1 Convert resources/views/notifications/index.blade.php
    - Convert notifications list to Metronic card pattern
    - Apply badge styling for notification types
    - Convert notification items to Bootstrap list group or card layout
    - Add proper icon usage (ki-bell, ki-information, ki-check-circle)
    - Ensure proper responsive design
    - _Requirements: 1.1, 3.1, 3.3, 7.1, 11.1, 12.12_
  
  - [ ] 24.2 Convert resources/views/notifications/show.blade.php
    - Convert notification detail view to Metronic card layout
    - Apply alert styling for notification content (alert alert-{type})
    - Add appropriate Keenicons in alerts
    - Apply proper typography hierarchy
    - _Requirements: 1.1, 3.4, 7.1, 16.1, 16.2, 16.3, 16.4, 12.12_
  
  - [ ] 24.3 Validate Notifications conversion
    - Run CSS validation script on notifications views
    - Test notification display and interaction
    - Verify alert styling and icons
    - Test responsive design
    - _Requirements: 4.1, 5.1, 6.1, 16.1, 16.2_

- [ ] 25. Checkpoint - Notifications complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 26. Handle empty states across all views
  - [ ] 26.1 Add empty state displays to all index views
    - Add empty state markup when tables/lists have no data
    - Use appropriate Keenicons (ki-file-deleted, ki-information-5)
    - Center empty state content (d-flex flex-column align-items-center)
    - Apply text-gray-600 class to empty state messages
    - Use fs-3x sizing for empty state icons
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5_
  
  - [ ] 26.2 Validate empty states
    - Test empty state display in all index views
    - Verify icon and message styling
    - Test responsive design of empty states
    - _Requirements: 14.1, 14.2, 14.3_

- [ ] 27. Standardize alert and notification displays
  - [ ] 27.1 Update success message displays
    - Apply alert alert-success d-flex align-items-center classes
    - Add ki-check-circle icon with fs-2 me-3 classes
    - Apply fw-semibold class to alert text
    - Add dismissible close buttons with btn-close class
    - _Requirements: 16.1, 16.4, 16.5, 16.6_
  
  - [ ] 27.2 Update error message displays
    - Apply alert alert-danger d-flex align-items-center classes
    - Add ki-cross-circle icon with fs-2 me-3 classes
    - Apply fw-semibold class to alert text
    - Add dismissible close buttons with btn-close class
    - _Requirements: 16.2, 16.4, 16.5, 16.6_
  
  - [ ] 27.3 Update warning message displays
    - Apply alert alert-warning d-flex align-items-center classes
    - Add ki-information icon with fs-2 me-3 classes
    - Apply fw-semibold class to alert text
    - Add dismissible close buttons with btn-close class
    - _Requirements: 16.3, 16.4, 16.5, 16.6_
  
  - [ ] 27.4 Validate alert displays
    - Test success, error, and warning alerts across all views
    - Verify icon display and styling
    - Test dismissible functionality
    - _Requirements: 16.1, 16.2, 16.3, 16.5_

- [ ] 28. Final validation and cleanup
  - [ ] 28.1 Run comprehensive CSS validation
    - Execute CSS validation script on all converted views
    - Generate validation report with any remaining Tailwind classes
    - Fix any detected Tailwind residue
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 18.1, 18.2_
  
  - [ ] 28.2 Verify accessibility compliance
    - Check all ARIA attributes are preserved
    - Verify heading hierarchy (h1, h2, h3) in all views
    - Test keyboard navigation and focus states
    - Verify all form inputs have associated labels
    - Check color contrast ratios
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6_
  
  - [ ] 28.3 Cross-browser testing
    - Test all converted views in Chrome, Firefox, Safari, Edge
    - Verify consistent rendering across browsers
    - Test critical workflows in each browser
    - _Requirements: 6.1, 6.2, 6.3, 6.4_
  
  - [ ] 28.4 Generate final validation report
    - Document all converted view files
    - List any manual adjustments needed
    - Provide responsive design test results
    - Include accessibility compliance summary
    - _Requirements: 4.1, 4.2, 4.3, 13.1, 18.1_

- [ ] 29. Final checkpoint - Conversion complete
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional validation tasks and can be skipped for faster implementation
- Each task references specific requirements for traceability
- Conversion follows priority order: Dashboard → Purchase Orders → Approvals → Goods Receipts → Invoices → Payments → Financial Controls → Organizations → Suppliers → Products → Users → Notifications
- Checkpoints ensure incremental validation and allow for user feedback
- All Blade directives, route references, and permission checks must be preserved
- Reference BOOTSTRAP_QUICK_REFERENCE.md for class mappings
- Use resources/views/purchase-orders/index.blade.php as a conversion template
- Maintain mobile-first responsive design approach
- Use Metronic 8 design patterns consistently (card-flush, table-row-dashed, badge-light-{color})
- All icons must use Keenicons format (ki-outline ki-{icon-name})
