# Requirements Document

## Introduction

This document specifies the requirements for converting all Laravel Blade views in the Medikindo Procurement System from Tailwind CSS styling to Bootstrap 5 with Metronic 8 theme styling. The conversion aims to achieve visual consistency, maintain all existing functionality, and ensure responsive design across all device sizes while adhering to the Metronic 8 design system.

## Glossary

- **View_Converter**: The system component responsible for transforming Blade view files from Tailwind CSS to Bootstrap 5 styling
- **Style_Validator**: The system component that verifies no Tailwind CSS classes remain in converted files
- **Responsive_Checker**: The system component that validates responsive design across breakpoints
- **Component_Library**: The collection of reusable Bootstrap-based Blade components (button, input, select, textarea, table)
- **Metronic_Theme**: The Metronic 8 design system providing UI patterns and components
- **Keenicons**: The icon library used in Metronic 8 (ki-outline ki-{icon-name} format)
- **Layout_System**: The main layout structure defined in resources/views/components/layout.blade.php
- **Target_Views**: The 12 view categories requiring conversion (Dashboard, Purchase Orders, Approvals, Goods Receipts, Invoices, Payments, Financial Controls, Organizations, Suppliers, Products, Users, Notifications)

## Requirements

### Requirement 1: View File Conversion

**User Story:** As a developer, I want all Blade view files converted from Tailwind CSS to Bootstrap 5 styling, so that the application has consistent visual design using the Metronic 8 theme.

#### Acceptance Criteria

1. THE View_Converter SHALL convert all Tailwind CSS classes to equivalent Bootstrap 5 classes in all Target_Views
2. WHEN a view file contains Tailwind utility classes (e.g., "flex", "items-center", "justify-between"), THE View_Converter SHALL replace them with Bootstrap equivalents (e.g., "d-flex", "align-items-center", "justify-content-between")
3. WHEN a view file contains Tailwind spacing classes (e.g., "mt-4", "px-6", "gap-4"), THE View_Converter SHALL replace them with Bootstrap spacing utilities (e.g., "mt-4", "px-5", "gap-4")
4. WHEN a view file contains Tailwind color classes (e.g., "text-gray-600", "bg-blue-500"), THE View_Converter SHALL replace them with Metronic color classes (e.g., "text-gray-600", "bg-primary")
5. THE View_Converter SHALL preserve all Blade directives (@can, @cannot, @if, @foreach, @forelse)
6. THE View_Converter SHALL preserve all route references (route() helper calls)
7. THE View_Converter SHALL preserve all asset() helper calls
8. THE View_Converter SHALL maintain all permission checks without modification

### Requirement 2: Component Integration

**User Story:** As a developer, I want converted views to use existing Bootstrap Blade components, so that the UI is consistent and maintainable.

#### Acceptance Criteria

1. WHEN a view contains button elements, THE View_Converter SHALL use the Component_Library button component where appropriate
2. WHEN a view contains form inputs, THE View_Converter SHALL use Component_Library form components (input, select, textarea) where appropriate
3. WHEN a view contains data tables, THE View_Converter SHALL use Component_Library table component or Metronic table patterns
4. THE View_Converter SHALL use Metronic card patterns (card-flush, card-custom) for card-based layouts
5. THE View_Converter SHALL apply Metronic table styling (table-row-dashed, table-row-gray-300) to all data tables

### Requirement 3: Icon System Conversion

**User Story:** As a developer, I want all icons converted to Keenicons format, so that the icon system is consistent with the Metronic 8 theme.

#### Acceptance Criteria

1. WHEN a view contains SVG icons or icon references, THE View_Converter SHALL replace them with Keenicons using the ki-outline ki-{icon-name} format
2. THE View_Converter SHALL maintain appropriate icon sizing using Metronic font-size classes (fs-1 through fs-7, fs-2x, fs-3x)
3. WHEN an icon appears in a button, THE View_Converter SHALL use the pattern: `<i class="ki-outline ki-{icon-name} fs-3"></i>`
4. WHEN an icon appears standalone, THE View_Converter SHALL apply appropriate sizing and color classes

### Requirement 4: Responsive Design Preservation

**User Story:** As a user, I want the application to work correctly on all device sizes, so that I can access the system from mobile, tablet, or desktop devices.

#### Acceptance Criteria

1. THE Responsive_Checker SHALL verify all converted views render correctly at mobile breakpoint (< 576px)
2. THE Responsive_Checker SHALL verify all converted views render correctly at tablet breakpoint (≥ 768px)
3. THE Responsive_Checker SHALL verify all converted views render correctly at desktop breakpoint (≥ 992px)
4. WHEN a view uses responsive utilities, THE View_Converter SHALL use Bootstrap responsive classes (d-none d-md-block, col-12 col-md-6, etc.)
5. THE View_Converter SHALL maintain mobile-first approach in all responsive implementations
6. WHEN a layout changes between breakpoints, THE View_Converter SHALL use appropriate Bootstrap responsive utilities (flex-column flex-md-row, etc.)

### Requirement 5: Tailwind Class Elimination

**User Story:** As a developer, I want complete removal of Tailwind CSS classes, so that there are no styling conflicts or unused CSS dependencies.

#### Acceptance Criteria

1. THE Style_Validator SHALL verify zero Tailwind CSS classes remain in any converted view file
2. WHEN scanning converted files, THE Style_Validator SHALL detect and report any remaining Tailwind-specific patterns (e.g., arbitrary values like "w-[200px]", Tailwind-specific prefixes like "hover:", "focus:")
3. THE Style_Validator SHALL verify no Tailwind configuration files are referenced in converted views
4. THE Style_Validator SHALL confirm all custom Tailwind classes have been replaced with Bootstrap equivalents

### Requirement 6: Functional Integrity

**User Story:** As a user, I want all application features to work exactly as before, so that the styling conversion does not break any functionality.

#### Acceptance Criteria

1. WHEN a user interacts with buttons in converted views, THE application SHALL execute the same actions as before conversion
2. WHEN a user submits forms in converted views, THE application SHALL process data identically to pre-conversion behavior
3. WHEN a user navigates between pages, THE application SHALL maintain all routing functionality
4. THE View_Converter SHALL preserve all JavaScript event handlers and Alpine.js directives
5. THE View_Converter SHALL preserve all form validation attributes and error display logic
6. WHEN a view displays dynamic data, THE application SHALL render data correctly with Bootstrap styling

### Requirement 7: Card and Container Styling

**User Story:** As a developer, I want consistent card and container styling across all views, so that the UI follows Metronic 8 design patterns.

#### Acceptance Criteria

1. WHEN a view contains card-like containers, THE View_Converter SHALL use Metronic card classes (card, card-flush, card-custom)
2. THE View_Converter SHALL apply card-body class with appropriate padding (pt-0, py-3, p-5) based on content type
3. WHEN a card has a header, THE View_Converter SHALL use card-header with border-0 pt-5 classes
4. WHEN a card has a title, THE View_Converter SHALL use card-title with appropriate typography classes (fw-bold fs-3)
5. THE View_Converter SHALL use card-toolbar class for action buttons in card headers

### Requirement 8: Table Styling Standardization

**User Story:** As a user, I want consistent table styling across all data views, so that information is presented uniformly throughout the application.

#### Acceptance Criteria

1. WHEN a view contains a data table, THE View_Converter SHALL wrap it in table-responsive div
2. THE View_Converter SHALL apply table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 classes to table elements
3. WHEN a table has headers, THE View_Converter SHALL apply fw-bold text-muted classes to thead tr elements
4. THE View_Converter SHALL use min-w-{size}px classes on th elements for column width control
5. WHEN a table cell contains actions, THE View_Converter SHALL apply text-end class to align actions to the right
6. THE View_Converter SHALL use appropriate typography classes for table cell content (text-gray-900 fw-bold for primary data, text-gray-600 for secondary data)

### Requirement 9: Form Styling Consistency

**User Story:** As a user, I want consistent form styling across all input forms, so that data entry is intuitive and visually uniform.

#### Acceptance Criteria

1. WHEN a view contains form inputs, THE View_Converter SHALL apply form-control form-control-solid classes
2. WHEN a view contains select dropdowns, THE View_Converter SHALL apply form-select form-select-solid classes
3. WHEN a view contains textareas, THE View_Converter SHALL apply form-control form-control-solid classes
4. THE View_Converter SHALL use form-label class for all input labels
5. WHEN a label is required, THE View_Converter SHALL add required class to form-label
6. THE View_Converter SHALL use form-text class for helper text below inputs
7. THE View_Converter SHALL maintain proper spacing between form groups using mb-5 class

### Requirement 10: Button Styling Standardization

**User Story:** As a user, I want consistent button styling across all views, so that interactive elements are easily recognizable and follow design patterns.

#### Acceptance Criteria

1. WHEN a view contains primary action buttons, THE View_Converter SHALL use btn btn-primary classes
2. WHEN a view contains secondary action buttons, THE View_Converter SHALL use btn btn-light-{color} classes (e.g., btn-light-primary, btn-light-success)
3. THE View_Converter SHALL apply appropriate button sizes (btn-sm for small, btn-lg for large)
4. WHEN a button contains an icon, THE View_Converter SHALL place icon before text with appropriate spacing
5. THE View_Converter SHALL use btn-active-color-primary for hover states where appropriate
6. WHEN buttons are grouped, THE View_Converter SHALL use gap-2 or gap-3 classes for spacing

### Requirement 11: Badge and Status Indicator Styling

**User Story:** As a user, I want consistent status indicators and badges, so that I can quickly identify item states throughout the application.

#### Acceptance Criteria

1. WHEN a view displays status badges, THE View_Converter SHALL use badge badge-light-{color} classes
2. THE View_Converter SHALL map status values to appropriate badge colors (draft → badge-light-secondary, pending → badge-light-warning, approved → badge-light-success, rejected → badge-light-danger)
3. THE View_Converter SHALL apply fw-bold class to badge text for readability
4. WHEN a badge appears in a table cell, THE View_Converter SHALL ensure proper vertical alignment
5. THE View_Converter SHALL use appropriate badge sizes based on context

### Requirement 12: Priority-Based Conversion Sequence

**User Story:** As a project manager, I want views converted in priority order, so that the most critical features are updated first.

#### Acceptance Criteria

1. THE View_Converter SHALL convert Dashboard views (resources/views/dashboard/*) first
2. WHEN Dashboard conversion is complete, THE View_Converter SHALL convert Purchase Orders views (resources/views/purchase-orders/*)
3. WHEN Purchase Orders conversion is complete, THE View_Converter SHALL convert Approvals views (resources/views/approvals/*)
4. WHEN Approvals conversion is complete, THE View_Converter SHALL convert Goods Receipts views (resources/views/goods-receipts/*)
5. WHEN Goods Receipts conversion is complete, THE View_Converter SHALL convert Invoices views (resources/views/invoices/*)
6. WHEN Invoices conversion is complete, THE View_Converter SHALL convert Payments views (resources/views/payments/*)
7. WHEN Payments conversion is complete, THE View_Converter SHALL convert Financial Controls views (resources/views/financial-controls/*)
8. WHEN Financial Controls conversion is complete, THE View_Converter SHALL convert Organizations views (resources/views/organizations/*)
9. WHEN Organizations conversion is complete, THE View_Converter SHALL convert Suppliers views (resources/views/suppliers/*)
10. WHEN Suppliers conversion is complete, THE View_Converter SHALL convert Products views (resources/views/products/*)
11. WHEN Products conversion is complete, THE View_Converter SHALL convert Users views (resources/views/users/*)
12. WHEN Users conversion is complete, THE View_Converter SHALL convert Notifications views (resources/views/notifications/*)

### Requirement 13: Accessibility Compliance

**User Story:** As a user with accessibility needs, I want the application to maintain accessibility standards, so that I can use assistive technologies effectively.

#### Acceptance Criteria

1. THE View_Converter SHALL preserve all ARIA attributes in converted views
2. THE View_Converter SHALL maintain proper heading hierarchy (h1, h2, h3) in converted views
3. WHEN a view contains interactive elements, THE View_Converter SHALL ensure proper focus states using Bootstrap focus utilities
4. THE View_Converter SHALL maintain sufficient color contrast ratios in all text and background combinations
5. WHEN a view contains forms, THE View_Converter SHALL ensure all inputs have associated labels
6. THE View_Converter SHALL preserve alt text for images and meaningful icon labels

### Requirement 14: Empty State Handling

**User Story:** As a user, I want consistent empty state displays, so that I understand when no data is available.

#### Acceptance Criteria

1. WHEN a table or list has no data, THE View_Converter SHALL display an empty state with appropriate icon and message
2. THE View_Converter SHALL use Keenicons for empty state icons (e.g., ki-file-deleted, ki-information-5)
3. THE View_Converter SHALL center empty state content using d-flex flex-column align-items-center classes
4. THE View_Converter SHALL apply text-gray-600 class to empty state messages
5. THE View_Converter SHALL use appropriate icon sizing (fs-3x) for empty state icons

### Requirement 15: Pagination Styling

**User Story:** As a user, I want consistent pagination controls, so that I can navigate through large datasets easily.

#### Acceptance Criteria

1. WHEN a view contains pagination, THE View_Converter SHALL use Laravel's default pagination with Bootstrap styling
2. THE View_Converter SHALL wrap pagination in a container with d-flex justify-content-between align-items-center classes
3. THE View_Converter SHALL display record count information using text-gray-600 fs-7 classes
4. THE View_Converter SHALL ensure pagination links use Bootstrap pagination component styling
5. WHEN pagination is present, THE View_Converter SHALL add appropriate top margin (mt-5) for spacing

### Requirement 16: Alert and Notification Styling

**User Story:** As a user, I want consistent alert and notification displays, so that I can easily identify success, error, and informational messages.

#### Acceptance Criteria

1. WHEN a view displays success messages, THE View_Converter SHALL use alert alert-success d-flex align-items-center classes
2. WHEN a view displays error messages, THE View_Converter SHALL use alert alert-danger d-flex align-items-center classes
3. WHEN a view displays warning messages, THE View_Converter SHALL use alert alert-warning d-flex align-items-center classes
4. THE View_Converter SHALL include appropriate Keenicons in alerts (ki-check-circle for success, ki-cross-circle for error, ki-information for warning)
5. THE View_Converter SHALL add dismissible close buttons to alerts using btn-close class
6. THE View_Converter SHALL apply fw-semibold class to alert text for readability

### Requirement 17: Reference Documentation Compliance

**User Story:** As a developer, I want the conversion to follow documented Bootstrap and Metronic patterns, so that the implementation is consistent with established guidelines.

#### Acceptance Criteria

1. THE View_Converter SHALL reference BOOTSTRAP_QUICK_REFERENCE.md for Bootstrap class mappings
2. THE View_Converter SHALL follow patterns demonstrated in resources/views/purchase-orders/index.blade.php for converted views
3. THE View_Converter SHALL follow Layout_System structure defined in resources/views/components/layout.blade.php
4. WHEN uncertain about a pattern, THE View_Converter SHALL reference the Metronic template in C:\laragon\www\dist\dist
5. THE View_Converter SHALL maintain consistency with existing converted components (button.blade.php, input.blade.php, select.blade.php, textarea.blade.php, table.blade.php)

### Requirement 18: Console Error Prevention

**User Story:** As a developer, I want zero console errors related to CSS classes, so that the application runs cleanly without styling warnings.

#### Acceptance Criteria

1. WHEN a converted view is loaded in a browser, THE application SHALL produce zero console errors related to missing CSS classes
2. THE Style_Validator SHALL verify all referenced CSS classes exist in Bootstrap 5 or Metronic 8 stylesheets
3. WHEN a view uses custom classes, THE View_Converter SHALL ensure those classes are defined in the application's custom CSS
4. THE View_Converter SHALL remove any references to Tailwind-specific JavaScript or CSS files

### Requirement 19: Filter and Search Form Styling

**User Story:** As a user, I want consistent filter and search form styling, so that I can easily find and filter data across different views.

#### Acceptance Criteria

1. WHEN a view contains filter forms, THE View_Converter SHALL wrap them in card card-flush mb-7 containers
2. THE View_Converter SHALL use row g-4 for filter form layouts
3. THE View_Converter SHALL apply col-md-{size} classes for responsive filter field widths
4. WHEN filter forms have action buttons, THE View_Converter SHALL use d-flex gap-2 for button groups
5. THE View_Converter SHALL use form-control-solid and form-select-solid classes for filter inputs
6. WHEN a reset button is present, THE View_Converter SHALL use btn btn-light class

### Requirement 20: Typography Consistency

**User Story:** As a user, I want consistent typography across all views, so that content hierarchy and readability are maintained throughout the application.

#### Acceptance Criteria

1. WHEN a view contains page titles, THE View_Converter SHALL use fs-2 fw-bold text-gray-900 classes
2. WHEN a view contains section headings, THE View_Converter SHALL use fs-3 fw-bold classes
3. WHEN a view contains body text, THE View_Converter SHALL use fs-6 text-gray-600 classes
4. WHEN a view contains labels or metadata, THE View_Converter SHALL use fs-7 text-gray-600 classes
5. THE View_Converter SHALL use fw-bold for emphasis, fw-semibold for medium emphasis, and fw-normal for regular text
6. THE View_Converter SHALL maintain proper text color hierarchy (text-gray-900 for primary, text-gray-800 for secondary, text-gray-600 for tertiary)
