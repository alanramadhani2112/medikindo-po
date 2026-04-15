# Implementation Plan: AR Invoice System

## Overview

Implementasi AR Invoice System untuk Medikindo menggunakan arsitektur Mirror Model (AP → AR). Bahasa implementasi: PHP/Laravel. Rencana dibagi menjadi 4 sprint: Database Foundation, Service Layer, UI/UX, dan PDF Template.

## Tasks

- [x] 1. Sprint 1 — Database Foundation

  - [x] 1.1 Buat migration untuk tabel `price_lists`
    - Buat file migration baru dengan schema sesuai design: `id`, `organization_id` FK, `product_id` FK, `selling_price` DECIMAL(15,2), `effective_date` DATE, `expiry_date` DATE nullable, `is_active` BOOLEAN default true, `timestamps`
    - Tambahkan unique constraint pada `(organization_id, product_id, effective_date)`
    - _Requirements: 15.1, 15.2_

  - [x] 1.2 Buat migration untuk tabel `tax_configurations`
    - Buat file migration baru dengan schema: `id`, `name` VARCHAR(100), `rate` DECIMAL(5,2), `is_default` BOOLEAN default false, `effective_date` DATE, `description` TEXT nullable, `timestamps`
    - _Requirements: 19.1_

  - [x] 1.3 Buat migration upgrade tabel `organizations`
    - Tambahkan kolom: `npwp` VARCHAR(20) nullable, `nik` VARCHAR(16) nullable, `customer_code` VARCHAR(50) nullable unique, `bank_accounts` JSON nullable
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [x] 1.4 Buat migration upgrade tabel `goods_receipts`
    - Tambahkan kolom: `do_number` VARCHAR(50) nullable unique, `delivered_at` TIMESTAMP nullable
    - _Requirements: 2.1, 2.2_

  - [x] 1.5 Buat migration upgrade tabel `customer_invoices`
    - Replace status enum dengan: `draft`, `issued`, `partial_paid`, `paid`, `void`
    - Tambahkan kolom: `supplier_invoice_id` BIGINT UNSIGNED FK nullable ke `supplier_invoices`, `surcharge` DECIMAL(15,2) default 0, `ematerai_fee` DECIMAL(15,2) default 0, `payment_term` VARCHAR(100) nullable, `salesman` VARCHAR(100) nullable, `tax_number` VARCHAR(50) nullable, `barcode_serial` VARCHAR(100) nullable unique, `print_count` INTEGER default 0, `last_printed_at` TIMESTAMP nullable
    - Pastikan semua kolom monetary menggunakan DECIMAL(15,2)
    - _Requirements: 3.1–3.10, 17.1_

  - [x] 1.6 Buat migration upgrade tabel `customer_invoice_line_items`
    - Tambahkan kolom: `supplier_invoice_item_id` BIGINT UNSIGNED FK NOT NULL ke `supplier_invoice_line_items`, `cost_price` DECIMAL(15,2), `batch_number` VARCHAR(50) nullable, `expiry_date` DATE nullable, `uom` VARCHAR(20) nullable, `tax_rate` DECIMAL(5,2) default 0, `tax_amount` DECIMAL(15,2) default 0, `goods_receipt_item_id` FK nullable
    - _Requirements: 4.1–4.7, 16.6_

  - [x] 1.7 Buat model `PriceList`
    - Buat `app/Models/PriceList.php` dengan relasi `organization()` BelongsTo dan `product()` BelongsTo
    - Tambahkan scope `scopeActiveForDate($query, Carbon $date)` yang filter `is_active=true`, `effective_date <= $date`, `expiry_date IS NULL OR expiry_date >= $date`
    - Tambahkan `$fillable` dan `$casts` yang sesuai
    - _Requirements: 15.1, 15.3_

  - [x] 1.8 Buat model `TaxConfiguration`
    - Buat `app/Models/TaxConfiguration.php` dengan static method `getActivePPNRate(): string` dan `getEMeteraiThreshold(): string`
    - `getActivePPNRate()` query `is_default=true`, `effective_date <= today`, order by `effective_date DESC`, fallback ke `'11.00'` jika tidak ada
    - `getEMeteraiThreshold()` query `name = 'EMeterai_Threshold'`, return `rate` sebagai string
    - _Requirements: 19.1, 19.4, 19.5, 19.6_

  - [x] 1.9 Upgrade model `CustomerInvoice`
    - Tambahkan status constants: `STATUS_DRAFT`, `STATUS_ISSUED`, `STATUS_PARTIAL_PAID`, `STATUS_PAID`, `STATUS_VOID`
    - Tambahkan array `TRANSITIONS` sesuai state machine di design
    - Tambahkan relasi `supplierInvoice()` BelongsTo dan `lineItems()` HasMany
    - Update `$fillable` dan `$casts` untuk kolom baru
    - _Requirements: 3.1, 7.1, 17.1_

  - [x] 1.10 Upgrade model `CustomerInvoiceLineItem`
    - Tambahkan relasi `supplierItem()` BelongsTo ke `SupplierInvoiceLineItem` via `supplier_invoice_item_id`
    - Update `$fillable` dan `$casts` untuk kolom baru (`cost_price`, `batch_number`, `expiry_date`, `uom`, `tax_rate`, `tax_amount`)
    - _Requirements: 4.1–4.7_

  - [x] 1.11 Upgrade model `Organization`
    - Update `$fillable` untuk kolom baru: `npwp`, `nik`, `customer_code`, `bank_accounts`
    - Tambahkan cast `bank_accounts` sebagai `array`
    - _Requirements: 1.1–1.4_

  - [x] 1.12 Upgrade model `GoodsReceipt`
    - Update `$fillable` untuk kolom baru: `do_number`, `delivered_at`
    - Tambahkan cast `delivered_at` sebagai `datetime`
    - _Requirements: 2.1, 2.2_

  - [x] 1.13 Buat seeder `TaxConfigurationSeeder`
    - Seed 3 record: `{name: "PPN Standard", rate: 11.00, is_default: true, effective_date: "2022-04-01"}`, `{name: "PPN 12%", rate: 12.00, is_default: false, effective_date: "2025-01-01"}`, `{name: "EMeterai_Threshold", rate: 5000000.00, is_default: false, effective_date: "2021-10-01"}`
    - Daftarkan di `DatabaseSeeder`
    - _Requirements: 19.2, 19.3_

- [x] 2. Checkpoint Sprint 1 — Jalankan semua migration dan seeder, pastikan tidak ada error.
  - Ensure all migrations run cleanly, ask the user if questions arise.

- [x] 3. Sprint 2 — Service Layer

  - [x] 3.1 Buat exception classes baru
    - Buat `app/Exceptions/PriceListNotFoundException.php`
    - Buat `app/Exceptions/AntiPhantomBillingException.php`
    - Buat `app/Exceptions/MarginViolationException.php`
    - Buat `app/Exceptions/InvalidStateTransitionException.php`
    - Buat `app/Exceptions/DuplicateMirrorException.php`
    - Setiap exception extend `\Exception` atau `\RuntimeException` dengan constructor yang menerima message
    - _Requirements: 8.3, 8.4, 7.2, 17.3, 17.4_

  - [x] 3.2 Upgrade `InvoiceCalculationService`
    - Tambahkan method `calculateTaxFloor(string $dpp, string $rate): string` menggunakan `BCMath` dengan `floor()` per baris
    - Tambahkan method `calculateGrandTotal(array $lineItems, string $surcharge): array` yang return `['subtotal', 'tax_total', 'ematerai_fee', 'grand_total']`
    - Tambahkan method `getActivePPNRate(): string` yang delegate ke `TaxConfiguration::getActivePPNRate()`
    - Tambahkan method `getEMeteraiThreshold(): string` yang delegate ke `TaxConfiguration::getEMeteraiThreshold()`
    - Logika e-Meterai: jika `(subtotal - discount + tax + surcharge) >= threshold` maka `ematerai_fee = 10000`, else `0`
    - _Requirements: 5.1–5.8_

  - [x]* 3.3 Tulis property test untuk Property 1: Grand Total Round-Trip
    - **Property 1: grand_total == sum(line_totals) + surcharge + ematerai_fee**
    - **Validates: Requirements 5.6, 5.8**
    - Buat `tests/Unit/Properties/InvoiceCalculationProperty1Test.php`
    - Generate random array line items (1–20 baris) dan random surcharge, verifikasi grand_total = sum(line_totals) + surcharge + ematerai_fee
    - Minimum 100 iterasi

  - [x]* 3.4 Tulis property test untuk Property 2: Tax Floor Rounding
    - **Property 2: tax_amount == floor(dpp * rate / 100)**
    - **Validates: Requirements 5.2**
    - Buat `tests/Unit/Properties/InvoiceCalculationProperty2Test.php`
    - Generate random (dpp, rate) pairs, verifikasi `tax_amount <= dpp * rate / 100` dan `tax_amount == floor(dpp * rate / 100)`
    - Minimum 100 iterasi

  - [x]* 3.5 Tulis property test untuk Property 3: E-Meterai Threshold Trigger
    - **Property 3: ematerai_fee == 10000 jika pre-total >= threshold, else 0**
    - **Validates: Requirements 5.4, 5.5**
    - Buat `tests/Unit/Properties/EMeteraiProperty3Test.php`
    - Generate random invoice totals di atas dan di bawah threshold, verifikasi ematerai_fee

  - [x]* 3.6 Tulis property test untuk Property 13: Tax Accumulation Consistency
    - **Property 13: invoice.tax_amount == sum(line_items.tax_amount)**
    - **Validates: Requirements 5.3**
    - Buat `tests/Unit/Properties/TaxAccumulationProperty13Test.php`
    - Generate random line items, verifikasi header tax_amount = sum baris

  - [x] 3.7 Upgrade `ImmutabilityGuardService`
    - Tambahkan `ISSUED`, `PARTIAL_PAID`, `PAID`, `VOID` ke daftar immutable statuses
    - Pastikan method guard melempar `ImmutabilityViolationException` dan mencatat ke `invoice_modification_attempts`
    - _Requirements: 6.1, 6.2, 6.6, 6.7_

  - [x]* 3.8 Tulis property test untuk Property 10: Immutability Guard
    - **Property 10: modifikasi financial fields pada ISSUED/PAID/VOID melempar ImmutabilityViolationException**
    - **Validates: Requirements 6.1, 6.7**
    - Buat `tests/Unit/Properties/ImmutabilityProperty10Test.php`
    - Generate random financial field values, verifikasi exception dilempar untuk setiap immutable status

  - [x] 3.9 Buat `PriceListService`
    - Buat `app/Services/PriceListService.php` dengan method `lookup(int $organizationId, int $productId): string`
    - Prioritas: `price_lists` aktif (is_active=true, effective_date <= today, expiry_date IS NULL OR >= today), order by effective_date DESC, ambil pertama
    - Fallback ke `products.selling_price` jika tidak ada price list
    - Lempar `PriceListNotFoundException` jika tidak ada harga sama sekali
    - _Requirements: 15.3, 15.4_

  - [x]* 3.10 Tulis property test untuk Property 6: Price List Customer-Specific Priority
    - **Property 6: PriceListService.lookup() mengembalikan harga customer-specific, bukan fallback**
    - **Validates: Requirements 15.3, 15.4**
    - Buat `tests/Unit/Properties/PriceListProperty6Test.php`
    - Seed kombinasi (org_id, product_id) dengan dan tanpa price list, verifikasi prioritas

  - [x] 3.11 Buat `MirrorGenerationService`
    - Buat `app/Services/MirrorGenerationService.php` dengan method `generateARFromAP(SupplierInvoice $apInvoice, int $customerId): CustomerInvoice` dan `draftExists(int $supplierInvoiceId): bool`
    - Implementasikan algoritma: guard draftExists → validasi status AP → DB::transaction → buat header CustomerInvoice → loop line items (lookup price, copy batch/expiry/qty/uom, hitung tax, simpan supplier_invoice_item_id dan cost_price) → hitung grand total → auto e-Meterai → commit → dispatch NewInvoiceNotification
    - Lempar `AntiPhantomBillingException` jika status AP bukan `verified`/`paid`
    - Lempar `DuplicateMirrorException` (log warning, return existing) jika draft sudah ada
    - _Requirements: 8.1, 8.2, 16.1–16.8, 17.2–17.5_

  - [x]* 3.12 Tulis property test untuk Property 4: Mirror Batch/Expiry Immutability
    - **Property 4: batch_number dan expiry_date pada CustomerInvoiceLineItem identik dengan SupplierInvoiceLineItem sumber**
    - **Validates: Requirements 16.4, 20.1, 20.2, 20.6**
    - Buat `tests/Unit/Properties/MirrorBatchProperty4Test.php`
    - Generate random SupplierInvoice dengan berbagai batch/expiry, verifikasi copy identik byte-for-byte

  - [x]* 3.13 Tulis property test untuk Property 7: Anti-Phantom Billing Enforcement
    - **Property 7: CustomerInvoice tidak dapat dibuat tanpa supplier_invoice_id valid atau AP berstatus non-verified**
    - **Validates: Requirements 8.1, 8.2, 17.2, 17.3, 17.4**
    - Buat `tests/Unit/Properties/AntiPhantomProperty7Test.php`
    - Generate random invalid supplier_invoice_id dan status, verifikasi AntiPhantomBillingException dilempar

  - [x] 3.14 Buat `MarginProtectionService`
    - Buat `app/Services/MarginProtectionService.php` dengan method `check(CustomerInvoice $invoice): array`, `canOverride(User $user): bool`, `logOverride(CustomerInvoice $invoice, User $user, string $reason): void`
    - `check()` return array violations `[['product_name', 'selling_price', 'cost_price', 'diff'], ...]` untuk setiap baris di mana `selling_price < cost_price`
    - Gunakan `BCMathCalculatorService` untuk perbandingan
    - _Requirements: 18.1–18.5_

  - [x]* 3.15 Tulis property test untuk Property 5: Margin Protection Blocks ISSUED Transition
    - **Property 5: DRAFT → ISSUED diblokir jika ada baris dengan selling_price < cost_price**
    - **Validates: Requirements 18.1**
    - Buat `tests/Unit/Properties/MarginProtectionProperty5Test.php`
    - Generate random line items dengan selling_price < cost_price, verifikasi violations array tidak kosong

  - [x] 3.16 Buat `TerbilangService`
    - Buat `app/Services/TerbilangService.php` dengan method `convert(int|float $amount): string`
    - Handle range 1 hingga 999.999.999.999 (ratusan miliar)
    - Handle desimal: append "Rupiah [sen] Sen"
    - Handle negatif: prepend "Minus"
    - _Requirements: 13.1–13.5_

  - [x]* 3.17 Tulis property test untuk Property 9: Terbilang Round-Trip
    - **Property 9: convert(amount) → parse back → equals original amount**
    - **Validates: Requirements 13.4**
    - Buat `tests/Unit/Properties/TerbilangProperty9Test.php`
    - Generate random integers [1, 999_999_999_999], convert ke terbilang, parse kembali, verifikasi sama

  - [x] 3.18 Implementasikan state machine transition guard di `CustomerInvoice` atau service
    - Buat method `transitionTo(string $newStatus): void` pada `CustomerInvoice` yang validasi via `TRANSITIONS` array
    - Lempar `InvalidStateTransitionException` jika transisi tidak valid
    - _Requirements: 7.1, 7.2_

  - [x]* 3.19 Tulis property test untuk Property 8: State Machine Valid Transitions Only
    - **Property 8: hanya transisi yang terdefinisi di TRANSITIONS yang berhasil**
    - **Validates: Requirements 7.1, 7.2**
    - Buat `tests/Unit/Properties/StateMachineProperty8Test.php`
    - Generate random (current_status, target_status) pairs, verifikasi valid transitions berhasil dan invalid melempar exception

  - [x]* 3.20 Tulis property test untuk Property 11: AR Aging Bucket Assignment
    - **Property 11: aging bucket assignment berdasarkan selisih today - due_date**
    - **Validates: Requirements 11.1, 11.5, 11.7**
    - Buat `tests/Unit/Properties/ARAgingProperty11Test.php`
    - Generate random due_date values, verifikasi bucket assignment: 0-30 → current, 31-60 → warning, >60 → overdue

  - [x]* 3.21 Tulis property test untuk Property 12: Print Count Monotonic Increment
    - **Property 12: setiap call ke print() increment print_count by exactly 1**
    - **Validates: Requirements 12.11**
    - Buat `tests/Unit/Properties/PrintCountProperty12Test.php`
    - Simulate N calls ke print(), verifikasi print_count = N dan last_printed_at diupdate

- [x] 4. Checkpoint Sprint 2 — Pastikan semua service dapat di-instantiate dan semua property test yang diimplementasikan lulus.
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Sprint 3 — UI/UX (Controllers, Routes, Views)

  - [x] 5.1 Buat `APVerificationController`
    - Buat `app/Http/Controllers/Web/APVerificationController.php` dengan method `verify(Request $request, SupplierInvoice $invoice, MirrorGenerationService $mirror): RedirectResponse`
    - Method `verify()`: ubah status SupplierInvoice ke `verified`, panggil `MirrorGenerationService::generateARFromAP()`, redirect dengan flash message sukses/error
    - Handle `AntiPhantomBillingException` dan `DuplicateMirrorException` dengan redirect + error message
    - _Requirements: 8.1, 8.2, 16.1_

  - [x] 5.2 Upgrade `CustomerInvoiceController` (atau buat baru di Web namespace)
    - Buat/upgrade `app/Http/Controllers/Web/CustomerInvoiceController.php` dengan methods: `index(Request $request): View`, `show(CustomerInvoice $invoice): View`, `issue(CustomerInvoice $invoice, MarginProtectionService $margin): RedirectResponse`, `void(Request $request, CustomerInvoice $invoice): RedirectResponse`, `print(CustomerInvoice $invoice): Response`
    - `issue()`: panggil `MarginProtectionService::check()`, jika violations → redirect dengan error list; jika OK → transisi ke ISSUED
    - `void()`: validasi Credit Note reference, transisi ke VOID
    - `print()`: generate PDF via dompdf, increment `print_count`, update `last_printed_at`, log audit
    - _Requirements: 6.3, 6.4, 7.1, 12.11, 14.3, 18.1, 18.2_

  - [x] 5.3 Buat `PriceListController`
    - Buat `app/Http/Controllers/Web/PriceListController.php` sebagai resource controller dengan methods: `index`, `create`, `store`, `edit`, `update`, `destroy`
    - `store()` dan `update()` validasi input: `organization_id`, `product_id`, `selling_price`, `effective_date`
    - `destroy()` deactivate (set `is_active = false`) bukan hard delete
    - _Requirements: 15.5_

  - [x] 5.4 Buat `ARAgingController`
    - Buat `app/Http/Controllers/Web/ARAgingController.php` dengan method `index(Request $request): View`
    - Query CustomerInvoice dengan status bukan `paid` dan `void`
    - Klasifikasikan ke bucket: 0-30 (current), 31-60 (warning), >60 (overdue) berdasarkan `today - due_date`
    - Pass data ke view: invoices per bucket, total outstanding per bucket
    - _Requirements: 11.1–11.8_

  - [x] 5.5 Daftarkan semua routes baru di `routes/web.php`
    - `GET /invoices/customer` → `CustomerInvoiceController@index` (name: `web.invoices.customer.index`)
    - `GET /invoices/customer/{invoice}` → `CustomerInvoiceController@show` (name: `web.invoices.customer.show`)
    - `POST /invoices/customer/{invoice}/issue` → `CustomerInvoiceController@issue` (name: `web.invoices.customer.issue`)
    - `POST /invoices/customer/{invoice}/void` → `CustomerInvoiceController@void` (name: `web.invoices.customer.void`)
    - `GET /invoices/customer/{invoice}/pdf` → `CustomerInvoiceController@print` (name: `web.invoices.customer.pdf`)
    - `POST /invoices/supplier/{invoice}/verify` → `APVerificationController@verify` (name: `web.invoices.supplier.verify`)
    - `Resource /price-lists` → `PriceListController` (names prefix: `web.price-lists`)
    - `GET /ar-aging` → `ARAgingController@index` (name: `web.ar-aging.index`)
    - _Requirements: 9.1, 11.1, 15.5_

  - [x] 5.6 Buat view customer invoice index (`resources/views/invoices/customer/index.blade.php`)
    - Tabel daftar CustomerInvoice dengan kolom: Invoice No., Customer, Invoice Date, Due Date, Grand Total, Status (badge warna), Actions
    - Filter by status dan date range
    - Link ke show page dan PDF
    - _Requirements: 9.1_

  - [x] 5.7 Buat view customer invoice show (`resources/views/invoices/customer/show.blade.php`)
    - Tampilkan header invoice: customer info, invoice metadata, supplier invoice reference
    - Tabel line items dengan kolom: Deskripsi, Batch/ED, Qty, UoM, Price, Tax Rate, Tax Amount, Amount
    - Ringkasan kalkulasi: Subtotal, Discount, Surcharge, Nett, PPN, e-Meterai, Grand Total
    - Tombol aksi: Issue (jika DRAFT), Void (jika ISSUED/PARTIAL_PAID), Print PDF
    - Tampilkan margin violation warnings jika ada
    - _Requirements: 9.1, 18.2_

  - [x] 5.8 Buat view AR Aging Dashboard (`resources/views/ar-aging/index.blade.php`)
    - 3 card summary: Current (hijau), Warning (kuning), Overdue (merah) dengan total outstanding per bucket
    - Tabel per bucket: Invoice No., Customer, Due Date, Outstanding Amount, Status
    - _Requirements: 11.1–11.8_

  - [x] 5.9 Buat view price list management (`resources/views/price-lists/index.blade.php`, `create.blade.php`, `edit.blade.php`)
    - Index: tabel price lists dengan filter by organization dan product, tombol create/edit/deactivate
    - Create/Edit: form dengan fields organization (select), product (select), selling_price, effective_date, expiry_date, is_active
    - _Requirements: 15.5_

  - [x] 5.10 Implementasikan live calculation preview dengan Alpine.js pada form invoice
    - Tambahkan Alpine.js component pada view create/edit invoice
    - Reactive data: array line items, surcharge, discount
    - Computed: subtotal per baris, tax per baris (floor rounding), total subtotal, total tax, nett, e-Meterai (auto-trigger >= 5jt), grand total
    - Display live summary: Total Amount, Discount, Surcharge, Nett, PPN, Biaya e-Meterai, Grand Total
    - _Requirements: 10.1–10.5_

  - [x] 5.11 Tambahkan tombol Verify ke supplier invoice show page
    - Edit view supplier invoice show (cari file existing di `resources/views/`)
    - Tambahkan tombol "Verifikasi & Buat AR" yang POST ke `web.invoices.supplier.verify`
    - Tampilkan hanya jika status SupplierInvoice adalah `issued` (belum verified)
    - _Requirements: 16.1_

- [x] 6. Checkpoint Sprint 3 — Pastikan semua routes terdaftar dan views dapat dirender tanpa error.
  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. Sprint 4 — PDF Template

  - [x] 7.1 Verifikasi/install dependency PDF dan barcode
    - Cek apakah `barryvdh/laravel-dompdf` sudah ada di `composer.json`; jika belum, tambahkan
    - Cek apakah library barcode (misal `picqer/php-barcode-generator` atau `milon/barcode`) sudah ada; jika belum, tambahkan
    - Pastikan service provider terdaftar di `config/app.php` atau auto-discovered
    - _Requirements: 12.1, 12.10_

  - [x] 7.2 Buat PDF template blade file
    - Buat `resources/views/pdf/customer_invoice.blade.php`
    - Layout A4 portrait dengan inline CSS untuk dompdf compatibility
    - _Requirements: 12.1–12.12_

  - [x] 7.3 Implementasikan header PDF
    - Logo perusahaan (img tag dengan path ke public/assets)
    - Judul "INVOICE LOCAL"
    - Info vendor: nama perusahaan, NPWP, nomor lisensi PBF, alamat cabang, telp/fax
    - _Requirements: 12.1_

  - [x] 7.4 Implementasikan section Sold To dan metadata invoice
    - Sold To: nama RS/Klinik, customer code, alamat, NPWP customer
    - Metadata: PO No., Payment Term, Tipe Pelayanan, Salesman, Invoice No., Tax No.
    - _Requirements: 12.2, 12.3_

  - [x] 7.5 Implementasikan tabel line items PDF
    - Kolom: DO No., Deskripsi Material, Batch/ED, Qty, UoM, Price, Disc(%), Amount
    - Loop `$invoice->lineItems` dengan format angka Rupiah
    - _Requirements: 12.4_

  - [x] 7.6 Implementasikan section kalkulasi ringkasan dan terbilang
    - Tampilkan: Total Amount, Discount, Surcharge, Nett, PPN, Biaya e-Meterai, Grand Total
    - Info rekening bank dari `bank_accounts` organisasi Medikindo
    - Teks terbilang via `TerbilangService::convert($invoice->grand_total)`
    - _Requirements: 12.5, 12.6, 12.7_

  - [x] 7.7 Implementasikan 4 kolom tanda tangan
    - 4 kolom: Customer, Spv. Penjualan, Penanggung Jawab PBF, Branch Manager
    - Setiap kolom: label di atas, garis tanda tangan di bawah
    - _Requirements: 12.8_

  - [x] 7.8 Implementasikan watermark diagonal
    - Teks "ASLI UNTUK PENAGIHAN/CUSTOMER" diagonal di body dokumen
    - Opacity 0.15 menggunakan CSS transform atau dompdf-compatible approach
    - _Requirements: 12.9_

  - [x] 7.9 Implementasikan barcode dan print log di footer
    - Generate barcode dari `$invoice->barcode_serial` menggunakan library barcode
    - Tampilkan serial number di bawah barcode
    - Print log: "Dicetak: [last_printed_at timestamp] | Cetak ke-[print_count]"
    - _Requirements: 12.10, 12.12_

  - [x] 7.10 Implementasikan print count increment di `CustomerInvoiceController::print()`
    - Setelah PDF di-generate, increment `print_count` dan update `last_printed_at = now()`
    - Log audit action print ke `audit_logs`
    - Return PDF response dengan header `Content-Type: application/pdf`
    - _Requirements: 12.11, 14.3_

- [x] 8. Checkpoint Final — Pastikan semua tests lulus dan PDF dapat di-generate dengan data lengkap.
  - Ensure all tests pass, ask the user if questions arise.
