# Requirements Document

## Introduction

Modul AR Invoice (Accounts Receivable) untuk Medikindo PO System adalah sistem penagihan B2B yang memungkinkan Medikindo sebagai distributor farmasi untuk menerbitkan invoice kepada RS/Klinik atas barang yang sudah dikirim melalui Delivery Order (GR). Sistem ini mencakup empat sprint: (1) Database Foundation — upgrade skema tabel existing, (2) Service Layer — kalkulasi mixed-tax, immutability, dan state machine, (3) UI/UX — form invoice dengan Zero-Typing Policy, live calculation, dan AR Aging Dashboard, serta (4) PDF Template — invoice cetak berstandar farmasi dengan e-Meterai, barcode, dan tanda tangan.

Medikindo beroperasi dengan model bisnis **dropship/back-to-back order** — belum memiliki gudang fisik, sehingga distributor langsung mengirim ke RS/Klinik. Arsitektur sistem mengikuti **"The Mirror Model"**: tagihan Supplier (AP/SupplierInvoice) menjadi *single source of truth* untuk pergerakan fisik barang, dan secara otomatis "dipantulkan" menjadi draft AR (CustomerInvoice) menggunakan harga jual dari Master Price List.

Alur utama: `PO → GR/DO (Delivery Order) → SupplierInvoice (AP) → [Mirror] → CustomerInvoice (AR) → Pembayaran`.

## Glossary

- **AR_Invoice_System**: Modul sistem yang mengelola seluruh siklus invoice accounts receivable Medikindo
- **Customer**: Organisasi B2B (RS/Klinik) yang membeli produk dari Medikindo; direpresentasikan oleh model `Organization`
- **Delivery_Order (DO)**: Dokumen pengiriman barang; dalam sistem ini berfungsi ganda dengan `GoodsReceipt` (GR) karena belum ada gudang terpisah
- **GoodsReceipt**: Model existing (`goods_receipts`) yang diupgrade dengan kolom `do_number` dan `delivered_at` untuk berfungsi sebagai DO
- **SupplierInvoice**: Model existing (`supplier_invoices`) yang merepresentasikan tagihan dari distributor/supplier (AP — Accounts Payable); menjadi *single source of truth* untuk pergerakan fisik barang
- **CustomerInvoice**: Model existing (`customer_invoices`) yang diupgrade menjadi invoice AR lengkap; dibuat secara otomatis sebagai "pantulan" dari SupplierInvoice
- **CustomerInvoiceLineItem**: Baris item pada invoice dengan dukungan mixed-tax per baris
- **Organization**: Model existing (`organizations`) yang diupgrade dengan data fiskal (NPWP, NIK, customer_code, bank_accounts)
- **MirrorGenerationService**: Service baru yang mengotomatisasi pembuatan draft CustomerInvoice dari SupplierInvoice yang sudah diverifikasi
- **PriceList**: Tabel `price_lists` yang menyimpan harga jual per kombinasi organisasi (RS/Klinik) dan produk; jembatan logika antara harga beli AP dan harga jual AR
- **MarginProtectionService**: Service yang memvalidasi bahwa `selling_price >= cost_price` sebelum invoice di-ISSUED
- **TaxConfiguration**: Tabel `tax_configurations` yang menyimpan konfigurasi PPN rate dan threshold e-Meterai secara dinamis (tidak hardcode)
- **InvoiceCalculationService**: Service yang menghitung subtotal, diskon, pajak per baris, surcharge, e-Meterai, dan grand total
- **ImmutabilityGuardService**: Service yang memblokir perubahan pada invoice berstatus ISSUED/PAID kecuali melalui Credit Note
- **CreditNote**: Dokumen koreksi yang digunakan untuk membatalkan atau mengoreksi invoice yang sudah ISSUED/PAID
- **ARAgingDashboard**: Tampilan klasifikasi piutang berdasarkan umur (0-30, 31-60, >60 hari)
- **EMeterai**: Meterai elektronik senilai Rp 10.000 yang wajib ditambahkan jika grand total sebelum e-Meterai >= threshold yang dikonfigurasi di `TaxConfiguration` (default Rp 5.000.000)
- **PPN**: Pajak Pertambahan Nilai; rate diambil dari `TaxConfiguration` (default 11% untuk obat keras, 0% untuk alkes bebas PPN); mixed-tax per baris
- **Surcharge**: Biaya tambahan opsional pada level invoice (bukan per baris)
- **BCMathCalculatorService**: Service existing untuk kalkulasi finansial presisi tinggi menggunakan BCMath PHP
- **InvoiceFromGRService**: Service existing untuk membuat invoice dari GR
- **TerbilangService**: Service baru untuk mengkonversi angka ke teks terbilang Bahasa Indonesia
- **PDF_Generator**: Komponen yang menggunakan barryvdh/laravel-dompdf untuk mencetak invoice
- **Barcode**: Kode batang dokumen yang di-generate dari `barcode_serial` invoice
- **PrintLog**: Catatan sistem setiap kali invoice dicetak, menyimpan `print_count` dan `last_printed_at`

---

## Requirements

### Requirement 1: Database Foundation — Upgrade Tabel Organizations

**User Story:** As a finance staff, I want customer organizations to have complete fiscal data (NPWP, NIK, customer code, bank accounts), so that invoice documents can be populated automatically without manual entry.

#### Acceptance Criteria

1. THE `AR_Invoice_System` SHALL add column `npwp` (VARCHAR 20, nullable) to the `organizations` table to store Nomor Pokok Wajib Pajak customer
2. THE `AR_Invoice_System` SHALL add column `nik` (VARCHAR 16, nullable) to the `organizations` table to store Nomor Induk Kependudukan untuk customer perorangan
3. THE `AR_Invoice_System` SHALL add column `customer_code` (VARCHAR 50, nullable, unique) to the `organizations` table sebagai kode identifikasi customer internal
4. THE `AR_Invoice_System` SHALL add column `bank_accounts` (JSON, nullable) to the `organizations` table untuk menyimpan daftar rekening bank customer dalam format array objek `{bank_name, account_number, account_name}`
5. WHEN `customer_code` is provided, THE `AR_Invoice_System` SHALL enforce uniqueness of `customer_code` across all organizations

---

### Requirement 2: Database Foundation — Upgrade Tabel GoodsReceipts sebagai Delivery Order

**User Story:** As a warehouse staff, I want each goods receipt to have a Delivery Order number and delivery timestamp, so that invoices can reference the exact delivery document.

#### Acceptance Criteria

1. THE `AR_Invoice_System` SHALL add column `do_number` (VARCHAR 50, nullable, unique) to the `goods_receipts` table sebagai nomor Delivery Order
2. THE `AR_Invoice_System` SHALL add column `delivered_at` (TIMESTAMP, nullable) to the `goods_receipts` table untuk mencatat waktu pengiriman aktual
3. WHEN a `GoodsReceipt` has status `completed`, THE `AR_Invoice_System` SHALL allow `do_number` to be set or updated
4. IF `do_number` is provided, THEN THE `AR_Invoice_System` SHALL enforce uniqueness of `do_number` across all goods receipts

---

### Requirement 3: Database Foundation — Upgrade Tabel CustomerInvoices

**User Story:** As a finance manager, I want the customer invoice table to support the full AR lifecycle with surcharge, e-Meterai, payment terms, and print tracking, so that all billing information is captured accurately.

#### Acceptance Criteria

1. THE `AR_Invoice_System` SHALL replace the existing status enum with: `DRAFT`, `ISSUED`, `PARTIAL_PAID`, `PAID`, `VOID`
2. THE `AR_Invoice_System` SHALL add column `surcharge` (DECIMAL 15,2, default 0) untuk biaya tambahan level invoice
3. THE `AR_Invoice_System` SHALL add column `ematerai_fee` (DECIMAL 15,2, default 0) untuk biaya e-Meterai
4. THE `AR_Invoice_System` SHALL add column `payment_term` (VARCHAR 100, nullable) untuk syarat pembayaran (misal: "NET 30")
5. THE `AR_Invoice_System` SHALL add column `salesman` (VARCHAR 100, nullable) untuk nama salesman penanggung jawab
6. THE `AR_Invoice_System` SHALL add column `tax_number` (VARCHAR 50, nullable) untuk nomor faktur pajak
7. THE `AR_Invoice_System` SHALL add column `barcode_serial` (VARCHAR 100, nullable, unique) untuk nomor seri barcode dokumen
8. THE `AR_Invoice_System` SHALL add column `print_count` (INTEGER, default 0) untuk menghitung jumlah cetak
9. THE `AR_Invoice_System` SHALL add column `last_printed_at` (TIMESTAMP, nullable) untuk mencatat waktu cetak terakhir
10. THE `AR_Invoice_System` SHALL ensure all monetary columns (`total_amount`, `paid_amount`, `subtotal_amount`, `discount_amount`, `tax_amount`, `surcharge`, `ematerai_fee`) use DECIMAL(15,2)

---

### Requirement 4: Database Foundation — Upgrade Tabel CustomerInvoiceLineItems (Mixed-Tax)

**User Story:** As a finance staff, I want each invoice line item to have its own tax rate and tax amount, so that mixed-tax invoices (PPN 11% untuk obat keras, 0% untuk alkes) can be handled correctly.

#### Acceptance Criteria

1. THE `AR_Invoice_System` SHALL ensure column `tax_rate` (DECIMAL 5,2, default 0) exists per baris di `customer_invoice_line_items`
2. THE `AR_Invoice_System` SHALL ensure column `tax_amount` (DECIMAL 15,2, default 0) exists per baris di `customer_invoice_line_items`
3. THE `AR_Invoice_System` SHALL add column `goods_receipt_item_id` (FK nullable) to `customer_invoice_line_items` untuk traceability ke item GR asal
4. THE `AR_Invoice_System` SHALL add column `batch_number` (VARCHAR 50, nullable) untuk nomor batch obat
5. THE `AR_Invoice_System` SHALL add column `expiry_date` (DATE, nullable) untuk tanggal kadaluarsa batch
6. THE `AR_Invoice_System` SHALL add column `uom` (VARCHAR 20, nullable) untuk satuan unit (pcs, box, strip, dll)
7. WHEN a line item is saved, THE `AR_Invoice_System` SHALL store `tax_rate` and `tax_amount` as immutable snapshots per baris

---

### Requirement 5: Service Layer — InvoiceCalculationService dengan Mixed-Tax

**User Story:** As a finance staff, I want the system to automatically calculate taxes per line item with floor rounding and accumulate them correctly, so that the invoice total is always accurate and compliant with tax regulations.

#### Acceptance Criteria

1. WHEN calculating tax for a line item, THE `InvoiceCalculationService` SHALL apply the tax rate specified per baris (bukan satu rate untuk semua baris)
2. WHEN calculating tax amount per line item, THE `InvoiceCalculationService` SHALL use floor rounding (bukan round/ceil) untuk setiap baris
3. THE `InvoiceCalculationService` SHALL accumulate tax amounts dari semua baris untuk menghasilkan total pajak invoice
4. WHEN `(subtotal - discount + tax + surcharge) >= 5000000`, THE `InvoiceCalculationService` SHALL set `ematerai_fee` to `10000`
5. WHEN `(subtotal - discount + tax + surcharge) < 5000000`, THE `InvoiceCalculationService` SHALL set `ematerai_fee` to `0`
6. THE `InvoiceCalculationService` SHALL calculate `grand_total` as: `subtotal - discount + tax + surcharge + ematerai_fee`
7. THE `InvoiceCalculationService` SHALL use `BCMathCalculatorService` untuk semua operasi aritmatika finansial
8. FOR ALL valid invoice line item inputs, THE `InvoiceCalculationService` SHALL produce a `grand_total` that equals the sum of all line totals plus surcharge plus ematerai_fee (round-trip property)

---

### Requirement 6: Service Layer — ImmutabilityGuardService dengan Status VOID dan Credit Note

**User Story:** As a finance manager, I want issued and paid invoices to be protected from direct modification, so that financial data integrity is maintained and all corrections go through a proper audit trail.

#### Acceptance Criteria

1. WHEN an invoice has status `ISSUED` or `PAID`, THE `ImmutabilityGuardService` SHALL block any direct modification to financial fields
2. WHEN an invoice has status `DRAFT` or `PARTIAL_PAID`, THE `ImmutabilityGuardService` SHALL allow modification of financial fields
3. WHEN an invoice needs correction after `ISSUED`, THE `AR_Invoice_System` SHALL require creation of a Credit Note referencing the original invoice
4. THE `AR_Invoice_System` SHALL support status `VOID` sebagai terminal state untuk invoice yang dibatalkan via Credit Note
5. WHEN a Credit Note is created, THE `AR_Invoice_System` SHALL record the `credit_note_reference` on the original invoice
6. THE `ImmutabilityGuardService` SHALL add `ISSUED`, `PARTIAL_PAID`, `PAID`, `VOID` to the list of immutable statuses
7. IF a modification attempt is made on an immutable invoice, THEN THE `ImmutabilityGuardService` SHALL log the attempt and throw `ImmutabilityViolationException`

---

### Requirement 7: Service Layer — State Machine Invoice AR

**User Story:** As a finance staff, I want the invoice status to follow a defined state machine, so that invalid status transitions are prevented and the invoice lifecycle is predictable.

#### Acceptance Criteria

1. THE `AR_Invoice_System` SHALL enforce the following valid transitions:
   - `DRAFT` → `ISSUED`
   - `ISSUED` → `PARTIAL_PAID`
   - `ISSUED` → `PAID`
   - `ISSUED` → `VOID`
   - `PARTIAL_PAID` → `PAID`
   - `PARTIAL_PAID` → `VOID`
   - `PAID` → (terminal, tidak ada transisi keluar kecuali via Credit Note)
   - `VOID` → (terminal)
2. WHEN an invalid status transition is attempted, THE `AR_Invoice_System` SHALL reject the transition and return a descriptive error message
3. WHEN a payment is recorded that covers the full remaining balance, THE `AR_Invoice_System` SHALL automatically transition status from `PARTIAL_PAID` to `PAID`
4. WHEN a partial payment is recorded, THE `AR_Invoice_System` SHALL transition status to `PARTIAL_PAID` dan update `paid_amount`

---

### Requirement 8: Validasi Kritis — Invoice Wajib Berdasarkan SupplierInvoice Verified (Mirror Model)

**User Story:** As a finance manager, I want to ensure that AR invoices can only be created when there is a verified Supplier Invoice (AP) as the basis, so that we never bill customers for goods that have not been confirmed received and invoiced by the supplier.

#### Acceptance Criteria

1. WHEN creating a CustomerInvoice, THE `AR_Invoice_System` SHALL require a valid `supplier_invoice_id` referencing an existing SupplierInvoice
2. WHEN creating a CustomerInvoice, THE `AR_Invoice_System` SHALL verify that the referenced SupplierInvoice has status `verified` or `paid`
3. IF the referenced SupplierInvoice does not have status `verified` or `paid`, THEN THE `AR_Invoice_System` SHALL reject the invoice creation with error message "Invoice AR hanya dapat dibuat berdasarkan SupplierInvoice yang sudah diverifikasi"
4. IF `supplier_invoice_id` is null or references a non-existent SupplierInvoice, THEN THE `AR_Invoice_System` SHALL reject the invoice creation with a validation error
5. THE `AR_Invoice_System` SHALL prevent creating duplicate active CustomerInvoices for the same SupplierInvoice (one verified AP = one active AR)
6. THE `AR_Invoice_System` SHALL maintain traceability: the referenced SupplierInvoice MUST itself reference a completed GoodsReceipt, enforcing the full chain `PO → GR → SupplierInvoice (verified) → CustomerInvoice`

---

### Requirement 9: UI/UX — Form Invoice Baru dengan Zero-Typing Policy

**User Story:** As a finance staff, I want the invoice creation form to auto-populate all fields from the selected Delivery Order/GR, so that I can create an invoice with minimal manual data entry and reduce errors.

#### Acceptance Criteria

1. WHEN a user selects a GoodsReceipt/DO on the invoice form, THE `AR_Invoice_System` SHALL auto-populate: customer name, customer address, customer NPWP, PO number, DO number, delivery date, and all line items (product name, batch, expiry, qty, UoM, unit price)
2. THE `AR_Invoice_System` SHALL display only GoodsReceipts with status `completed` and without an existing active invoice in the DO selection dropdown
3. WHEN line items are auto-populated, THE `AR_Invoice_System` SHALL pre-fill `tax_rate` per baris berdasarkan `default_tax_rate` dari produk atau organisasi
4. THE `AR_Invoice_System` SHALL allow finance staff to override `discount_percentage`, `tax_rate`, `surcharge`, dan `payment_term` setelah auto-populate
5. WHEN the form is submitted, THE `AR_Invoice_System` SHALL validate all required fields before saving

---

### Requirement 10: UI/UX — Live Calculation Preview (Alpine.js)

**User Story:** As a finance staff, I want to see the invoice grand total update in real-time as I modify line items, discounts, and surcharge, so that I can verify the total before saving.

#### Acceptance Criteria

1. WHEN any line item quantity, unit price, discount, or tax rate is changed, THE `AR_Invoice_System` SHALL recalculate and display the updated subtotal, tax, and line total without page refresh
2. WHEN surcharge is changed, THE `AR_Invoice_System` SHALL recalculate and display the updated grand total without page refresh
3. WHEN the calculated pre-e-Meterai total reaches or exceeds Rp 5.000.000, THE `AR_Invoice_System` SHALL automatically display e-Meterai fee of Rp 10.000 in the summary
4. THE `AR_Invoice_System` SHALL display the live calculation summary: Total Amount, Discount, Surcharge, Nett, PPN, Biaya e-Meterai, Grand Total
5. THE `AR_Invoice_System` SHALL implement the live calculation using Alpine.js tanpa memerlukan page refresh atau AJAX call ke server

---

### Requirement 11: UI/UX — AR Aging Dashboard

**User Story:** As a finance manager, I want to see accounts receivable classified by aging buckets with color-coded badges, so that I can quickly identify overdue invoices and prioritize collection.

#### Acceptance Criteria

1. THE `ARAgingDashboard` SHALL classify outstanding invoices into three aging buckets: 0-30 hari (current), 31-60 hari (warning), >60 hari (overdue)
2. THE `ARAgingDashboard` SHALL display the 0-30 hari bucket dengan badge warna hijau
3. THE `ARAgingDashboard` SHALL display the 31-60 hari bucket dengan badge warna kuning
4. THE `ARAgingDashboard` SHALL display the >60 hari bucket dengan badge warna merah
5. THE `ARAgingDashboard` SHALL calculate aging berdasarkan selisih antara tanggal hari ini dan `due_date` invoice
6. THE `ARAgingDashboard` SHALL display total outstanding amount per aging bucket
7. WHEN an invoice has status `PAID` or `VOID`, THE `ARAgingDashboard` SHALL exclude it from aging calculations
8. THE `ARAgingDashboard` SHALL display the list of invoices per bucket dengan kolom: Invoice No., Customer, Due Date, Outstanding Amount, Status

---

### Requirement 12: PDF Template — Invoice Cetak Standar Farmasi

**User Story:** As a finance staff, I want to print a standardized invoice PDF with all required components (company info, customer info, line items, calculations, signatures, watermark, barcode), so that the printed invoice meets regulatory and business requirements.

#### Acceptance Criteria

1. THE `PDF_Generator` SHALL include header dengan: logo perusahaan, judul "INVOICE LOCAL", info vendor (nama, NPWP, lisensi PBF, alamat cabang, telp/fax)
2. THE `PDF_Generator` SHALL include info customer/sold-to: nama RS/Klinik, customer code, alamat, NPWP
3. THE `PDF_Generator` SHALL include metadata invoice: PO No., Payment Term, Tipe Pelayanan, Salesman, Invoice No., Tax No.
4. THE `PDF_Generator` SHALL include tabel line items dengan kolom: DO No., Deskripsi Material, Batch/ED, Qty, UoM, Price, Disc(%), Amount
5. THE `PDF_Generator` SHALL include info rekening bank (BRI, Danamon) dari data `bank_accounts` organisasi Medikindo
6. THE `PDF_Generator` SHALL include kalkulasi ringkasan: Total Amount → Disc → Surcharge → Nett → PPN → Biaya e-Meterai → Grand Total
7. THE `PDF_Generator` SHALL include teks terbilang (angka ke kata Bahasa Indonesia) untuk Grand Total
8. THE `PDF_Generator` SHALL include 4 kolom tanda tangan: Customer, Spv. Penjualan, Penanggung Jawab PBF, Branch Manager
9. THE `PDF_Generator` SHALL include watermark teks "ASLI UNTUK PENAGIHAN/CUSTOMER" pada body dokumen
10. THE `PDF_Generator` SHALL include barcode dokumen yang di-generate dari `barcode_serial` invoice
11. WHEN an invoice is printed, THE `AR_Invoice_System` SHALL increment `print_count` dan update `last_printed_at` pada record invoice
12. THE `PDF_Generator` SHALL display system print log dengan timestamp cetak terakhir dan jumlah cetak pada footer dokumen

---

### Requirement 13: TerbilangService — Konversi Angka ke Teks Indonesia

**User Story:** As a finance staff, I want the invoice PDF to display the grand total in Indonesian words (terbilang), so that the printed invoice meets standard Indonesian invoice requirements.

#### Acceptance Criteria

1. THE `TerbilangService` SHALL convert any positive integer amount to Indonesian words (e.g., 1500000 → "Satu Juta Lima Ratus Ribu Rupiah")
2. THE `TerbilangService` SHALL handle amounts up to 999.999.999.999 (ratusan miliar)
3. WHEN the amount has decimal cents, THE `TerbilangService` SHALL append the cents in words (e.g., "... Rupiah Lima Puluh Sen")
4. FOR ALL valid positive integer amounts, THE `TerbilangService` SHALL produce terbilang text that, when parsed back to a number, equals the original amount (round-trip property)
5. IF a negative amount is provided, THEN THE `TerbilangService` SHALL prepend "Minus" to the terbilang text

---

### Requirement 14: Keamanan dan Audit Trail

**User Story:** As a system administrator, I want all invoice actions (create, issue, void, print) to be logged in the audit trail, so that there is a complete record of all financial transactions.

#### Acceptance Criteria

1. WHEN a CustomerInvoice is created, THE `AR_Invoice_System` SHALL log the action to `audit_logs` dengan entity_type `customer_invoice`
2. WHEN a CustomerInvoice status changes, THE `AR_Invoice_System` SHALL log the old status, new status, dan user yang melakukan perubahan
3. WHEN an invoice is printed, THE `AR_Invoice_System` SHALL log the print action dengan timestamp dan user
4. WHEN an immutability violation is attempted, THE `ImmutabilityGuardService` SHALL log the attempt ke `invoice_modification_attempts` table
5. THE `AR_Invoice_System` SHALL associate all audit log entries dengan `organization_id` yang relevan

---

### Requirement 15: Database Foundation — Master Price List

**User Story:** As a finance manager, I want to define different selling prices per hospital/clinic for each product, so that each customer organization can have a negotiated price that is automatically applied when generating AR invoices.

#### Acceptance Criteria

1. THE `AR_Invoice_System` SHALL create table `price_lists` dengan kolom: `id`, `organization_id` (FK ke `organizations`, NOT NULL), `product_id` (FK ke `products`, NOT NULL), `selling_price` (DECIMAL 15,2, NOT NULL), `effective_date` (DATE, NOT NULL), `expiry_date` (DATE, nullable), `is_active` (BOOLEAN, default true), `created_at`, `updated_at`
2. THE `AR_Invoice_System` SHALL enforce a unique constraint pada kombinasi `(organization_id, product_id, effective_date)` di tabel `price_lists`
3. WHEN looking up a selling price for a given `organization_id` dan `product_id`, THE `AR_Invoice_System` SHALL select the `price_lists` record where `is_active = true`, `effective_date <= today`, dan `expiry_date IS NULL OR expiry_date >= today`, ordered by `effective_date DESC`, taking the first result
4. IF no active `price_lists` record exists for a given `organization_id` dan `product_id`, THEN THE `AR_Invoice_System` SHALL fall back to `selling_price` dari tabel `products` sebagai harga default
5. THE `AR_Invoice_System` SHALL allow finance staff to create, update, and deactivate price list entries melalui UI manajemen harga

---

### Requirement 16: Service Layer — AP-to-AR Mirror Generation (1-Click AR)

**User Story:** As a finance staff, I want the system to automatically generate a draft AR invoice when a Supplier Invoice is verified, so that I only need to review and issue the AR without manual data re-entry.

#### Acceptance Criteria

1. WHEN a SupplierInvoice status changes to `verified` or `paid`, THE `MirrorGenerationService` SHALL automatically create a draft CustomerInvoice dengan status `DRAFT` berdasarkan data SupplierInvoice tersebut
2. WHEN generating the draft CustomerInvoice, THE `MirrorGenerationService` SHALL populate `organization_id`, `purchase_order_id`, `goods_receipt_id`, dan `supplier_invoice_id` dari SupplierInvoice sumber
3. WHEN generating line items for the draft CustomerInvoice, THE `MirrorGenerationService` SHALL look up the selling price dari `price_lists` berdasarkan `organization_id` RS dan `product_id` setiap baris
4. WHEN generating line items for the draft CustomerInvoice, THE `MirrorGenerationService` SHALL copy `batch_number` dan `expiry_date` identik dari setiap baris SupplierInvoice ke baris CustomerInvoice yang bersesuaian
5. WHEN generating line items for the draft CustomerInvoice, THE `MirrorGenerationService` SHALL copy `quantity` dan `uom` identik dari setiap baris SupplierInvoice
6. THE `MirrorGenerationService` SHALL store `cost_price` (harga beli dari AP line item) pada setiap CustomerInvoiceLineItem untuk keperluan margin protection
7. IF a draft CustomerInvoice already exists for the same `supplier_invoice_id`, THEN THE `MirrorGenerationService` SHALL skip generation dan log a warning tanpa membuat duplikat
8. WHEN the draft CustomerInvoice is created, THE `AR_Invoice_System` SHALL notify finance staff melalui sistem notifikasi bahwa draft AR baru tersedia untuk review

---

### Requirement 17: Service Layer — Anti-Phantom Billing

**User Story:** As a finance manager, I want to ensure it is technically impossible to create an AR invoice without a verified Supplier Invoice as its basis, so that phantom billing (menagih barang yang tidak pernah diterima dari supplier) is prevented at the system level.

#### Acceptance Criteria

1. THE `AR_Invoice_System` SHALL add column `supplier_invoice_id` (BIGINT UNSIGNED, FK nullable ke `supplier_invoices`) to the `customer_invoices` table
2. WHEN a CustomerInvoice is created via any code path (API, service, atau seeder), THE `AR_Invoice_System` SHALL validate that `supplier_invoice_id` is not null dan references a SupplierInvoice dengan status `verified` atau `paid`
3. IF `supplier_invoice_id` is null at the time of CustomerInvoice creation, THEN THE `AR_Invoice_System` SHALL reject the operation dan return error "CustomerInvoice tidak dapat dibuat tanpa referensi SupplierInvoice yang valid"
4. IF `supplier_invoice_id` references a SupplierInvoice dengan status selain `verified` atau `paid`, THEN THE `AR_Invoice_System` SHALL reject the operation dan return error "SupplierInvoice belum diverifikasi"
5. THE `AR_Invoice_System` SHALL enforce the `supplier_invoice_id` foreign key constraint di level database migration

---

### Requirement 18: Service Layer — Margin Protection

**User Story:** As a finance manager, I want the system to block issuing an AR invoice if any line item's selling price is lower than its cost price, so that Medikindo is protected from accidentally billing below cost due to data entry errors.

#### Acceptance Criteria

1. WHEN finance staff attempts to transition a CustomerInvoice from `DRAFT` to `ISSUED`, THE `MarginProtectionService` SHALL check every line item: jika `selling_price < cost_price` pada baris manapun, blokir transisi dan return daftar baris yang bermasalah beserta selisih harganya
2. WHEN the margin check fails, THE `AR_Invoice_System` SHALL display a warning message yang menyebutkan nama produk, `selling_price`, `cost_price`, dan selisih negatif untuk setiap baris yang bermasalah
3. WHERE a finance manager role with permission `override_margin_protection` is authenticated, THE `AR_Invoice_System` SHALL allow the margin protection check to be bypassed dengan mencatat alasan override di audit log
4. WHEN a margin override is performed, THE `AR_Invoice_System` SHALL log the override action ke `audit_logs` dengan detail: user, invoice ID, baris yang di-override, `selling_price`, `cost_price`, dan alasan override
5. THE `MarginProtectionService` SHALL use `BCMathCalculatorService` untuk perbandingan `selling_price` vs `cost_price` guna menghindari floating-point error

---

### Requirement 19: Database Foundation — Dynamic Tax Configuration

**User Story:** As a system administrator, I want PPN rates and e-Meterai thresholds to be configurable in the database, so that tax rate changes (e.g., PPN 11% ke 12%) can be applied without code deployment.

#### Acceptance Criteria

1. THE `AR_Invoice_System` SHALL create table `tax_configurations` dengan kolom: `id`, `name` (VARCHAR 100, NOT NULL), `rate` (DECIMAL 5,2, NOT NULL), `is_default` (BOOLEAN, default false), `effective_date` (DATE, NOT NULL), `description` (TEXT, nullable), `created_at`, `updated_at`
2. THE `AR_Invoice_System` SHALL seed tabel `tax_configurations` dengan record default: `{name: "PPN Standard", rate: 11.00, is_default: true, effective_date: "2022-04-01"}` dan `{name: "PPN 12%", rate: 12.00, is_default: false, effective_date: "2025-01-01"}`
3. THE `AR_Invoice_System` SHALL store e-Meterai threshold sebagai record di `tax_configurations` dengan `name = "EMeterai_Threshold"` dan `rate` menyimpan nilai threshold dalam Rupiah (default 5000000)
4. WHEN `InvoiceCalculationService` needs the PPN rate, THE `AR_Invoice_System` SHALL read the active rate dari `tax_configurations` where `is_default = true` dan `effective_date <= today`, bukan dari hardcoded constant
5. WHEN `InvoiceCalculationService` needs the e-Meterai threshold, THE `AR_Invoice_System` SHALL read the threshold dari `tax_configurations` where `name = "EMeterai_Threshold"`, bukan dari hardcoded constant
6. IF no active `tax_configurations` record is found for PPN, THEN THE `AR_Invoice_System` SHALL fall back to rate 11.00 dan log a warning

---

### Requirement 20: Audit Trail Narkotika — Batch & Expiry Copy dari AP ke AR

**User Story:** As a compliance officer, I want batch numbers and expiry dates to be copied identically from the Supplier Invoice to the AR Invoice for narcotic and psychotropic products, so that BPOM/Kemenkes audit trails are complete and traceable.

#### Acceptance Criteria

1. WHEN `MirrorGenerationService` generates CustomerInvoice line items dari SupplierInvoice, THE `AR_Invoice_System` SHALL copy `batch_number` identik dari `supplier_invoice_line_items` ke `customer_invoice_line_items` tanpa modifikasi
2. WHEN `MirrorGenerationService` generates CustomerInvoice line items dari SupplierInvoice, THE `AR_Invoice_System` SHALL copy `expiry_date` identik dari `supplier_invoice_line_items` ke `customer_invoice_line_items` tanpa modifikasi
3. WHEN a CustomerInvoice line item references a product dengan `is_narcotic = true` atau `category IN ('Narkotika', 'Psikotropika')`, THE `AR_Invoice_System` SHALL enforce that `batch_number` is NOT NULL dan return validation error jika kosong
4. WHEN a CustomerInvoice line item references a product dengan `is_narcotic = true` atau `category IN ('Narkotika', 'Psikotropika')`, THE `AR_Invoice_System` SHALL enforce that `expiry_date` is NOT NULL dan return validation error jika kosong
5. THE `AR_Invoice_System` SHALL include `batch_number` dan `expiry_date` per line item pada PDF invoice cetak untuk keperluan audit BPOM/Kemenkes
6. FOR ALL CustomerInvoice line items yang di-generate via Mirror, THE `AR_Invoice_System` SHALL guarantee bahwa `batch_number` dan `expiry_date` pada AR identik dengan nilai pada AP sumber (immutable copy property)
