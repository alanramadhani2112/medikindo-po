# Testing Documentation

## Setup

Testing menggunakan **MySQL** (bukan SQLite) agar behavior database konsisten dengan production.

### Database Testing

Database khusus untuk testing: `medikindo_po_testing`

Buat database jika belum ada:
```bash
& "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u root -e "CREATE DATABASE medikindo_po_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Jalankan migration:
```bash
php artisan migrate:fresh --env=testing
```

### Konfigurasi

- `phpunit.xml` — konfigurasi PHPUnit, DB connection ke MySQL `medikindo_po_testing`
- `.env.testing` — environment variables khusus testing

### Menjalankan Test

```bash
# Semua test
php artisan test

# Test suite tertentu
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# File tertentu
php artisan test tests/Feature/PurchaseOrderTest.php

# Filter by method
php artisan test --filter=test_healthcare_user_can_create_po
```

---

## Coverage Summary

Total: **~250+ test cases** tersebar di Unit dan Feature tests.

---

## Unit Tests

### Properties (`tests/Unit/Properties/`)

Property-based tests yang memverifikasi invariant bisnis.

| File | Test Cases | Deskripsi |
|------|-----------|-----------|
| `ARAgingProperty11Test` | 2 | Aging bucket assignment & boundary values untuk AR aging report |
| `AntiPhantomProperty7Test` | 3 | Anti-phantom billing: throw untuk status draft/overdue, tidak throw untuk verified |
| `EMeteraiProperty3Test` | 3 | Threshold e-meterai Rp 5.000.000 (di bawah, di atas, tepat batas) |
| `ImmutabilityProperty10Test` | 5 | Guard throw untuk status issued/partial_paid/paid/void, tidak throw untuk draft |
| `InvoiceCalculationProperty1Test` | 1 | Grand total round-trip property |
| `InvoiceCalculationProperty2Test` | 1 | Tax floor rounding property |
| `MarginProtectionProperty5Test` | 3 | Deteksi jual di bawah HPP, sama HPP, di atas HPP |
| `MirrorBatchProperty4Test` | 3 | Batch & expiry identik antara supplier/customer invoice line item |
| `PriceListProperty6Test` | 4 | Prioritas harga customer-specific, fallback ke default, throw jika tidak ada harga |
| `PrintCountProperty12Test` | 5 | Print count monotonic increment, +1 per operasi, field exists |
| `StateMachineProperty8Test` | 4 | Valid/invalid transitions, throw exception pada transisi tidak valid |
| `TaxAccumulationProperty13Test` | 1 | Konsistensi akumulasi pajak |
| `TerbilangProperty9Test` | 3 | Output selalu mengandung "rupiah", non-empty string, nilai known |

### Services (`tests/Unit/Services/`)

Unit tests untuk service classes dengan isolasi penuh.

| File | Test Cases | Deskripsi |
|------|-----------|-----------|
| `AuditServiceInvoiceTest` | 18 | Log kalkulasi, validasi, discrepancy, immutability, concurrency; query audit trail dengan filter |
| `BCMathCalculatorServiceTest` | 22 | Add/subtract/multiply/divide, rounding half-up, percentage, sum array, sifat kommutatif & asosiatif |
| `DiscountValidatorServiceTest` | 22 | Validasi persentase & amount, reject invalid, kalkulasi diskon, skenario farmasi & bulk purchase |
| `DiscrepancyDetectionServiceTest` | 18 | Deteksi discrepancy PO vs invoice, severity (none/low/medium/high), breakdown, skenario farmasi |
| `ImmutabilityGuardServiceTest` | 26 | Block/allow field changes per status, mutable vs immutable fields, supplier & customer invoice, format pesan |
| `InvoiceCalculationServiceTest` | 18 | Kalkulasi line item, diskon, pajak, multi-item, tolerance check, integrity check, skenario farmasi |
| `TaxCalculatorServiceTest` | 24 | PPN Indonesia 11%, tax inclusive/exclusive, rounding, validasi rate, skenario farmasi |

---

## Feature Tests

Integration tests yang menyentuh database dan HTTP layer.

| File | Test Cases | Deskripsi |
|------|-----------|-----------|
| `ApiCriticalFlowTest` | 5 | Send to supplier, goods receipt confirm, customer invoice, outgoing payment, idempotency |
| `AuditLogTest` | 4 | Super admin & clinic admin bisa baca log, approver tidak bisa, filter by action |
| `AuthTest` | 7 | Register, login, login gagal, user inactive, logout, me endpoint, unauthenticated 401 |
| `DashboardRbacTest` | 7 | Payload dashboard per role, multi-tenancy isolation, approval flow, narcotic 2-level approval |
| `ExampleTest` | 1 | App returns HTTP 200 |
| `FinancialReconciliationTest` | 1 | Full financial lifecycle end-to-end (PO → GR → Invoice → Payment) |
| `InvoiceConcurrencyTest` | 13 | Version column, increment on update, deteksi concurrent modification, sequential updates |
| `InvoiceDataMigrationTest` | 12 | Dry run, migrasi ke line items, skip existing, by ID, discrepancy detection, batch processing |
| `InvoiceImmutabilityTest` | 13 | Block/allow field changes per status untuk supplier & customer invoice |
| `OrganizationTest` | 7 | List, search, create, update, deactivate, unique code validation |
| `ProductTest` | 6 | List, filter narcotic, create, approver tidak bisa create, unique SKU, deactivate |
| `PurchaseOrderTest` | 12 | Create PO, approval flow, narcotic 2-level approval, tenant isolation, credit limit |
| `RBACAccessControlTest` | 30 | Akses per role (healthcare/approver/finance/super admin) ke semua modul + sidebar menu |
| `ReportTest` | 5 | Dashboard super admin, status counts, scoped by org, PO summary, approver access |
| `SuperAdminLoginTest` | 5 | Login super admin, wrong password, inactive user, all permissions, dashboard access |
| `SupplierTest` | 6 | List, create, search, deactivate, show with products relation |
| `UserTest` | 8 | List users per role, view user, change role, deactivate, filter by role |

---

## Area Belum Ter-cover

Area berikut belum memiliki test dan akan ditambahkan:

- [ ] **Payment Proof** — submit, verify, approve, reject, resubmit workflow
- [ ] **Goods Receipt** — partial receipt, delivery items, multi-delivery
- [ ] **Inventory** — stock movement, inventory items
- [ ] **Bank Account** — CRUD, validasi
- [ ] **Price List** — CRUD, lookup per customer
- [ ] **Customer Invoice** — full lifecycle, surcharge, print count
- [ ] **Notification** — trigger notifikasi per event

---

## Browser Tests (Dusk)

Terdapat browser tests di `tests/Browser/` menggunakan Laravel Dusk, namun belum diintegrasikan ke pipeline utama.

| File | Deskripsi |
|------|-----------|
| `LoginTest` | Login flow via browser |
| `PurchaseOrderTest` | PO flow via browser |
| `InvoiceTest` | Invoice flow via browser |
| `PaymentProofTest` | Payment proof flow via browser |
| `AuthorizationTest` | Authorization checks via browser |

---

## E2E Tests

Terdapat e2e spec di `tests/e2e/medikindo-flow.spec.ts` (Playwright/TypeScript), belum diintegrasikan.
