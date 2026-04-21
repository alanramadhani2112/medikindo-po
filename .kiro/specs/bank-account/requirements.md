# Requirements Document

## Introduction

Fitur Bank Account (Akun Bank) pada sistem Medikindo PO memungkinkan Super Admin untuk mengelola daftar rekening bank milik Medikindo yang digunakan sebagai tujuan transfer pembayaran dari RS/Klinik. Setiap Customer Invoice dapat dikaitkan dengan satu rekening bank tertentu, sehingga RS/Klinik mengetahui dengan jelas ke rekening mana mereka harus mentransfer pembayaran. Rekening yang dinonaktifkan tidak dapat dipilih untuk invoice baru, dan satu rekening dapat ditandai sebagai default untuk mempercepat pembuatan invoice.

Alur utama: `Super Admin CRUD BankAccount → CustomerInvoice pilih BankAccount → RS/Klinik lihat nomor rekening saat submit PaymentProof`.

## Glossary

- **BankAccount**: Model baru (`bank_accounts`) yang menyimpan data rekening bank milik Medikindo, termasuk nama bank, nomor rekening, nama pemilik rekening, status aktif, dan flag default
- **Bank_Account_Manager**: Komponen sistem yang mengelola operasi CRUD pada entitas `BankAccount`; diakses eksklusif oleh Super Admin
- **CustomerInvoice**: Model existing (`customer_invoices`) yang diupgrade dengan kolom `bank_account_id` sebagai referensi rekening tujuan pembayaran
- **PaymentProof**: Model existing (`payment_proofs`) yang menampilkan informasi rekening bank dari `CustomerInvoice` terkait saat RS/Klinik mengisi bukti pembayaran
- **Super_Admin**: Pengguna dengan role `super_admin` yang memiliki akses penuh ke manajemen `BankAccount`
- **Healthcare_User**: Pengguna dari RS/Klinik yang dapat melihat informasi rekening bank pada invoice dan payment proof, namun tidak dapat mengelola data rekening
- **Default_Account**: Rekening bank yang secara otomatis dipilih saat membuat Customer Invoice baru jika tidak ada rekening yang dipilih secara eksplisit; hanya satu rekening yang dapat menjadi default pada satu waktu
- **Active_Account**: Rekening bank dengan status `is_active = true` yang dapat dipilih untuk Customer Invoice baru
- **Inactive_Account**: Rekening bank dengan status `is_active = false` yang tidak dapat dipilih untuk Customer Invoice baru, namun tetap ditampilkan pada invoice lama yang sudah menggunakannya

---

## Requirements

### Requirement 1: Manajemen Data Rekening Bank (CRUD)

**User Story:** As a Super Admin, I want to create, read, update, and delete bank accounts owned by Medikindo, so that I can maintain an accurate and up-to-date list of payment destination accounts.

#### Acceptance Criteria

1. THE `Bank_Account_Manager` SHALL store each bank account with the following fields: `bank_name` (string, required), `account_number` (string, required), `account_holder_name` (string, required), `is_active` (boolean, default true), `is_default` (boolean, default false), and `notes` (text, nullable)
2. THE `Bank_Account_Manager` SHALL enforce uniqueness of `account_number` across all bank account records
3. WHEN a `Super_Admin` creates a new bank account, THE `Bank_Account_Manager` SHALL save the record and display a success notification
4. WHEN a `Super_Admin` updates an existing bank account, THE `Bank_Account_Manager` SHALL save the changes and display a success notification
5. WHEN a `Super_Admin` attempts to delete a bank account that is referenced by one or more `CustomerInvoice` records, THE `Bank_Account_Manager` SHALL reject the deletion and display an error message indicating the account is in use
6. WHEN a `Super_Admin` deletes a bank account that is not referenced by any `CustomerInvoice`, THE `Bank_Account_Manager` SHALL permanently remove the record
7. THE `Bank_Account_Manager` SHALL display all bank accounts in a paginated table showing `bank_name`, `account_number`, `account_holder_name`, status aktif, dan flag default

---

### Requirement 2: Pengaturan Rekening Default

**User Story:** As a Super Admin, I want to designate one bank account as the default, so that new invoices are automatically pre-filled with the correct payment destination without manual selection every time.

#### Acceptance Criteria

1. THE `Bank_Account_Manager` SHALL ensure that at most one bank account has `is_default = true` at any given time
2. WHEN a `Super_Admin` sets a bank account as default, THE `Bank_Account_Manager` SHALL set `is_default = false` on all other bank accounts before setting `is_default = true` on the selected account
3. WHEN a `Super_Admin` sets an `Inactive_Account` as default, THE `Bank_Account_Manager` SHALL reject the operation and display an error message stating that only active accounts can be set as default
4. WHEN no bank account has `is_default = true`, THE `Bank_Account_Manager` SHALL allow `CustomerInvoice` creation without a pre-selected bank account

---

### Requirement 3: Nonaktifkan Rekening Bank

**User Story:** As a Super Admin, I want to deactivate a bank account without deleting it, so that old invoices retain their payment destination reference while the account is no longer selectable for new invoices.

#### Acceptance Criteria

1. WHEN a `Super_Admin` deactivates a bank account, THE `Bank_Account_Manager` SHALL set `is_active = false` on the record without deleting it
2. WHEN a bank account is deactivated and it was previously the `Default_Account`, THE `Bank_Account_Manager` SHALL also set `is_default = false` on that account
3. WHILE a bank account has `is_active = false`, THE `Bank_Account_Manager` SHALL exclude it from the list of selectable accounts on the `CustomerInvoice` form
4. WHILE a bank account has `is_active = false`, THE `Bank_Account_Manager` SHALL still display its data on existing `CustomerInvoice` records that previously referenced it

---

### Requirement 4: Pemilihan Rekening Bank pada Customer Invoice

**User Story:** As a finance staff, I want to select which Medikindo bank account should be shown on a customer invoice, so that the hospital or clinic knows exactly where to transfer their payment.

#### Acceptance Criteria

1. THE `CustomerInvoice` SHALL include a `bank_account_id` foreign key column referencing the `bank_accounts` table (nullable)
2. WHEN a new `CustomerInvoice` is created and a `Default_Account` exists, THE `CustomerInvoice` SHALL pre-populate `bank_account_id` with the `Default_Account`'s id
3. WHEN a finance staff selects a bank account on the `CustomerInvoice` form, THE `CustomerInvoice` SHALL only display `Active_Account` records in the dropdown
4. WHEN a `CustomerInvoice` is saved with a `bank_account_id`, THE `CustomerInvoice` SHALL store the association and display the bank account details on the invoice view and PDF
5. IF a `CustomerInvoice` is saved without a `bank_account_id`, THEN THE `CustomerInvoice` SHALL display a placeholder text "Rekening belum ditentukan" on the invoice view and PDF
6. WHEN a bank account is deactivated after being assigned to a `CustomerInvoice`, THE `CustomerInvoice` SHALL retain the existing `bank_account_id` and continue to display the bank account details

---

### Requirement 5: Tampilan Rekening Bank pada Payment Proof

**User Story:** As a healthcare user (RS/Klinik), I want to see the destination bank account details when submitting a payment proof, so that I can confirm I transferred to the correct account.

#### Acceptance Criteria

1. WHEN a `Healthcare_User` opens the payment proof submission form for a `CustomerInvoice`, THE `PaymentProof` form SHALL display the bank account details (`bank_name`, `account_number`, `account_holder_name`) from the associated `CustomerInvoice`
2. WHILE a `CustomerInvoice` has no associated `bank_account_id`, THE `PaymentProof` form SHALL display the text "Rekening belum ditentukan — hubungi Medikindo" in place of bank account details
3. THE `PaymentProof` form SHALL display bank account information as read-only; `Healthcare_User` SHALL NOT be able to modify the bank account selection

---

### Requirement 6: Validasi Integritas Data

**User Story:** As a system, I want to enforce data integrity rules on bank account operations, so that the system remains consistent and invoice payment information is always accurate.

#### Acceptance Criteria

1. WHEN a request to create or update a bank account is received with a duplicate `account_number`, THE `Bank_Account_Manager` SHALL return a validation error with the message "Nomor rekening sudah terdaftar"
2. WHEN a request to create or update a bank account is received with `bank_name` exceeding 100 characters, THE `Bank_Account_Manager` SHALL return a validation error
3. WHEN a request to create or update a bank account is received with `account_number` exceeding 30 characters, THE `Bank_Account_Manager` SHALL return a validation error
4. WHEN a request to create or update a bank account is received with `account_holder_name` exceeding 100 characters, THE `Bank_Account_Manager` SHALL return a validation error
5. IF a `Super_Admin` attempts to access the bank account management page without the `super_admin` role, THEN THE `Bank_Account_Manager` SHALL return an HTTP 403 Forbidden response
6. THE `Bank_Account_Manager` SHALL log all create, update, deactivate, and delete operations to the `audit_logs` table with the actor's user id and timestamp
