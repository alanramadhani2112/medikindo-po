# Implementation Plan: Bank Account

## Overview

Implementasi fitur manajemen rekening bank Medikindo mengikuti pola MVC existing. Urutan pengerjaan: migration & model → service → controller & request → views → integrasi CustomerInvoice → integrasi PaymentProof → permission & routing → sidebar.

## Tasks

- [-] 1. Migration dan Model BankAccount
  - [x] 1.1 Buat migration `create_bank_accounts_table`
    - Kolom: `id`, `bank_name` (varchar 100), `account_number` (varchar 30, unique), `account_holder_name` (varchar 100), `is_active` (boolean default true), `is_default` (boolean default false), `notes` (text nullable), `created_at`, `updated_at`
    - Tidak ada `deleted_at` (hard delete dengan proteksi referensi)
    - _Requirements: 1.1, 1.2_

  - [x] 1.2 Buat migration `add_bank_account_id_to_customer_invoices`
    - Tambah kolom `bank_account_id` (bigint unsigned nullable) ke tabel `customer_invoices`
    - Foreign key ke `bank_accounts.id` dengan `ON DELETE SET NULL`
    - _Requirements: 4.1_

  - [x] 1.3 Buat model `app/Models/BankAccount.php`
    - `$fillable`: `bank_name`, `account_number`, `account_holder_name`, `is_active`, `is_default`, `notes`
    - Cast `is_active` dan `is_default` ke boolean
    - Scope `active()`: filter `is_active = true`
    - Relasi `customerInvoices()`: `HasMany` ke `CustomerInvoice`
    - Tidak menggunakan trait `BelongsToOrganization` dan `SoftDeletes`
    - _Requirements: 1.1, 3.3_

  - [x] 1.4 Update model `app/Models/CustomerInvoice.php`
    - Tambah `bank_account_id` ke `$fillable`
    - Tambah relasi `bankAccount(): BelongsTo` ke `BankAccount`
    - _Requirements: 4.1, 4.4_

- [-] 2. BankAccountService
  - [x] 2.1 Buat `app/Services/BankAccountService.php`
    - Inject `AuditService` via constructor
    - Method `create(array $data): BankAccount` — simpan record, log audit action `bank_account_created`
    - Method `update(BankAccount $account, array $data): BankAccount` — update record, log audit action `bank_account_updated`
    - Method `delete(BankAccount $account): void` — cek referensi ke `customer_invoices`; jika ada lempar `\RuntimeException`; jika tidak ada hard delete, log audit action `bank_account_deleted`
    - Method `getActiveAccounts(): Collection` — query dengan scope `active()`
    - Method `getDefaultAccount(): ?BankAccount` — query `is_default = true` first or null
    - _Requirements: 1.3, 1.4, 1.5, 1.6, 6.6_

  - [x] 2.2 Tambah method `setDefault` dan `toggleActive` ke `BankAccountService`
    - Method `setDefault(BankAccount $account): void` — validasi `is_active = true` (lempar `\InvalidArgumentException` jika tidak); dalam `DB::transaction()`: set semua `is_default = false`, lalu set account ini `is_default = true`; log audit action `bank_account_set_default`
    - Method `deactivate(BankAccount $account): void` — set `is_active = false`, jika `is_default = true` juga set `is_default = false`; log audit action `bank_account_deactivated`
    - Method `activate(BankAccount $account): void` — set `is_active = true`; log audit action `bank_account_activated`
    - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.2, 6.6_

  - [ ]* 2.3 Tulis property test untuk Property 3: At most one default account
    - **Property 3: At most one default account at any time**
    - Generate N akun, panggil `setDefault()` pada akun acak, assert `BankAccount::where('is_default', true)->count() === 1`
    - **Validates: Requirements 2.1, 2.2**

  - [ ]* 2.4 Tulis property test untuk Property 4: Inactive accounts cannot be set as default
    - **Property 4: Inactive accounts cannot be set as default**
    - Generate akun dengan `is_active = false`, panggil `setDefault()`, assert `\InvalidArgumentException` dilempar
    - **Validates: Requirements 2.3**

  - [ ]* 2.5 Tulis property test untuk Property 5: Deactivation preserves record and clears default
    - **Property 5: Deactivation preserves record and clears default**
    - Generate akun aktif (dengan variasi `is_default`), panggil `deactivate()`, assert record masih ada + `is_active = false` + `is_default = false`
    - **Validates: Requirements 3.1, 3.2**

  - [ ]* 2.6 Tulis property test untuk Property 2: Deletion invariant based on invoice references
    - **Property 2: Deletion invariant based on invoice references**
    - Generate akun dengan/tanpa referensi invoice, assert delete berhasil hanya jika tidak ada referensi
    - **Validates: Requirements 1.5, 1.6**

- [-] 3. Form Request Validation
  - [x] 3.1 Buat `app/Http/Requests/StoreBankAccountRequest.php`
    - `authorize()`: return `$this->user()->can('manage_bank_accounts')`
    - Rules: `bank_name` (required, string, max:100), `account_number` (required, string, max:30, unique:bank_accounts), `account_holder_name` (required, string, max:100), `notes` (nullable, string)
    - Custom message untuk `account_number.unique`: "Nomor rekening sudah terdaftar"
    - _Requirements: 1.2, 6.1, 6.2, 6.3, 6.4_

  - [x] 3.2 Buat `app/Http/Requests/UpdateBankAccountRequest.php`
    - Rules sama dengan `StoreBankAccountRequest`, tapi `account_number` unique ignore self: `unique:bank_accounts,account_number,{$this->route('bank_account')->id}`
    - _Requirements: 1.2, 6.1, 6.2, 6.3, 6.4_

  - [ ]* 3.3 Tulis property test untuk Property 1: Uniqueness of account_number
    - **Property 1: Uniqueness of account_number**
    - Generate random `account_number`, insert dua kali, assert request kedua gagal validasi dengan pesan "Nomor rekening sudah terdaftar"
    - **Validates: Requirements 1.2, 6.1**

  - [ ]* 3.4 Tulis property test untuk Property 10: Field length validation
    - **Property 10: Field length validation**
    - Generate string dengan panjang > max (>100 untuk `bank_name`, >30 untuk `account_number`, >100 untuk `account_holder_name`), assert validasi menolak request
    - **Validates: Requirements 6.2, 6.3, 6.4**

- [-] 4. BankAccountWebController dan Routes
  - [x] 4.1 Buat `app/Http/Controllers/Web/BankAccountWebController.php`
    - Inject `BankAccountService` via constructor
    - Method `index()`: ambil semua bank accounts (paginate 15), return view `bank-accounts.index`
    - Method `create()`: return view `bank-accounts.create`
    - Method `store(StoreBankAccountRequest $request)`: panggil `service->create()`, redirect ke index dengan flash success
    - Method `edit(BankAccount $bankAccount)`: return view `bank-accounts.edit` dengan data akun
    - Method `update(UpdateBankAccountRequest $request, BankAccount $bankAccount)`: panggil `service->update()`, redirect ke index dengan flash success
    - Method `destroy(BankAccount $bankAccount)`: panggil `service->delete()`, tangkap `\RuntimeException` → redirect dengan flash error; sukses → redirect dengan flash success
    - Method `setDefault(BankAccount $bankAccount)`: panggil `service->setDefault()`, tangkap `\InvalidArgumentException` → redirect dengan flash error; sukses → redirect dengan flash success
    - Method `toggleActive(BankAccount $bankAccount)`: jika aktif panggil `deactivate()`, jika tidak aktif panggil `activate()`; redirect dengan flash success
    - _Requirements: 1.3, 1.4, 1.5, 1.6, 2.2, 2.3, 3.1, 6.5_

  - [x] 4.2 Daftarkan routes di `routes/web.php`
    - Tambah `use App\Http\Controllers\Web\BankAccountWebController;` di bagian import
    - Tambah route group `prefix('bank-accounts')->name('web.bank-accounts.')->middleware('can:manage_bank_accounts')` dengan:
      - `GET /` → `index` (name: `index`)
      - `GET /create` → `create` (name: `create`)
      - `POST /` → `store` (name: `store`)
      - `GET /{bankAccount}/edit` → `edit` (name: `edit`)
      - `PUT /{bankAccount}` → `update` (name: `update`)
      - `DELETE /{bankAccount}` → `destroy` (name: `destroy`)
      - `PATCH /{bankAccount}/set-default` → `setDefault` (name: `set-default`)
      - `PATCH /{bankAccount}/toggle-active` → `toggleActive` (name: `toggle-active`)
    - _Requirements: 6.5_

- [-] 5. Views Bank Account
  - [x] 5.1 Buat `resources/views/bank-accounts/index.blade.php`
    - Extend `x-index-layout` atau layout `layouts.app` dengan `@section('content')`
    - Tabel dengan kolom: Nama Bank, Nomor Rekening, Nama Pemilik, Status (badge aktif/nonaktif), Default (badge), Aksi
    - Tombol aksi per baris menggunakan komponen `x-table-action`: Edit, Set Default (jika aktif & bukan default), Toggle Active, Delete
    - Tombol "Tambah Rekening Bank" di header
    - Flash message success/error via SweetAlert (sudah ada di layout)
    - _Requirements: 1.7_

  - [x] 5.2 Buat `resources/views/bank-accounts/create.blade.php`
    - Form POST ke `route('web.bank-accounts.store')`
    - Field: `bank_name`, `account_number`, `account_holder_name`, `notes` (textarea)
    - Tampilkan validation errors per field
    - Tombol Submit dan Cancel (kembali ke index)
    - _Requirements: 1.3_

  - [x] 5.3 Buat `resources/views/bank-accounts/edit.blade.php`
    - Form PUT ke `route('web.bank-accounts.update', $bankAccount)`
    - Pre-fill semua field dari `$bankAccount`
    - Tampilkan validation errors per field
    - Tombol Update dan Cancel
    - _Requirements: 1.4_

- [x] 6. Checkpoint — Pastikan semua tests pass
  - Jalankan `php artisan migrate` untuk memastikan migration berjalan tanpa error
  - Pastikan semua tests pass, tanyakan ke user jika ada pertanyaan

- [-] 7. Integrasi CustomerInvoice
  - [ ] 7.1 Update `CustomerInvoiceWebController` untuk inject `BankAccountService`
    - Tambah `BankAccountService` ke constructor
    - Pada method yang menampilkan form create customer invoice: inject `$activeAccounts = $bankAccountService->getActiveAccounts()` dan `$defaultAccount = $bankAccountService->getDefaultAccount()` ke view
    - _Requirements: 4.2, 4.3_

  - [ ] 7.2 Update view form create Customer Invoice
    - Tambah dropdown `bank_account_id` yang hanya menampilkan active accounts
    - Pre-select default account jika ada (`$defaultAccount->id`)
    - Jika tidak ada active accounts, tampilkan pesan informatif
    - _Requirements: 4.2, 4.3_

  - [ ] 7.3 Update `StoreCustomerInvoiceRequest` untuk menerima `bank_account_id`
    - Tambah rule: `bank_account_id` (nullable, exists:bank_accounts,id)
    - Validasi tambahan: jika `bank_account_id` diisi, pastikan akun tersebut `is_active = true`
    - _Requirements: 4.3_

  - [x] 7.4 Update view show Customer Invoice dan PDF
    - Pada view show: tampilkan detail rekening (`bank_name`, `account_number`, `account_holder_name`) jika `$invoice->bankAccount` ada; tampilkan "Rekening belum ditentukan" jika null
    - Pada view PDF: tampilkan informasi rekening yang sama
    - _Requirements: 4.4, 4.5_

  - [ ]* 7.5 Tulis property test untuk Property 6: Active accounts list never contains inactive accounts
    - **Property 6: Active accounts list never contains inactive accounts**
    - Generate campuran akun aktif/nonaktif, assert `getActiveAccounts()` tidak mengandung akun dengan `is_active = false`
    - **Validates: Requirements 3.3, 4.3**

  - [ ]* 7.6 Tulis property test untuk Property 8: Default account pre-populates new invoices
    - **Property 8: Default account pre-populates new invoices**
    - Generate akun default aktif, buat CustomerInvoice tanpa `bank_account_id` eksplisit, assert `bank_account_id` invoice = id akun default
    - **Validates: Requirements 4.2**

  - [ ]* 7.7 Tulis property test untuk Property 9: Bank account association round-trip on invoice
    - **Property 9: Bank account association round-trip on invoice**
    - Generate akun aktif, simpan CustomerInvoice dengan `bank_account_id` tersebut, load relasi `bankAccount`, assert `bank_name`, `account_number`, `account_holder_name` sama
    - **Validates: Requirements 4.4, 5.1**

  - [ ]* 7.8 Tulis property test untuk Property 7: Deactivation does not affect existing invoice references
    - **Property 7: Deactivation does not affect existing invoice references**
    - Generate CustomerInvoice dengan `bank_account_id` non-null, nonaktifkan akun tersebut, assert `bank_account_id` invoice tidak berubah
    - **Validates: Requirements 3.4, 4.6_

- [-] 8. Integrasi PaymentProof View
  - [x] 8.1 Update view form create Payment Proof
    - Baca `$invoice->bankAccount` dari CustomerInvoice terkait
    - Tampilkan info rekening (`bank_name`, `account_number`, `account_holder_name`) sebagai read-only jika ada
    - Tampilkan "Rekening belum ditentukan — hubungi Medikindo" jika `bankAccount` null
    - Field bank account harus read-only (tidak bisa diubah oleh Healthcare User)
    - _Requirements: 5.1, 5.2, 5.3_

- [-] 9. Permission, Seeder, dan Sidebar
  - [x] 9.1 Update `RolePermissionSeeder` untuk menambah permission `manage_bank_accounts`
    - Tambah `'manage_bank_accounts'` ke array `$permissions`
    - Permission ini otomatis diberikan ke Super Admin karena seeder menggunakan `Permission::where('guard_name', $guard)->get()`
    - _Requirements: 6.5_

  - [x] 9.2 Update sidebar `resources/views/components/partials/sidebar.blade.php`
    - Tambah `'manage_bank_accounts'` ke directive `@canany` pada section Master Data
    - Tambah menu item "Bank Accounts" di dalam section Master Data, di-wrap dengan `@can('manage_bank_accounts')`
    - Gunakan icon `ki-outline ki-bank fs-2` dan link ke `route('web.bank-accounts.index')`
    - Active state: `request()->routeIs('web.bank-accounts.*') ? 'active' : ''`
    - _Requirements: 6.5_

  - [ ]* 9.3 Tulis property test untuk Property 11: All CRUD operations produce audit log entries
    - **Property 11: All CRUD operations produce audit log entries**
    - Untuk setiap operasi (create, update, deactivate, delete), assert entry di `audit_logs` dengan `action`, `entity_id`, dan `user_id` yang benar
    - **Validates: Requirements 6.6**

- [ ] 10. Final Checkpoint — Pastikan semua tests pass
  - Jalankan seluruh test suite, pastikan tidak ada regresi
  - Pastikan semua tests pass, tanyakan ke user jika ada pertanyaan

## Notes

- Tasks bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensikan requirements spesifik untuk traceability
- Property tests memvalidasi correctness properties yang didefinisikan di design document
- Super Admin mendapat `manage_bank_accounts` secara otomatis karena seeder menggunakan `Permission::all()`
- Model `BankAccount` tidak menggunakan `BelongsToOrganization` — rekening bersifat global milik Medikindo
