# Spesifikasi Kebutuhan Perangkat Lunak (SRS)
# Medikindo PO System

**Versi:** 1.0.0  
**Tanggal:** 2025  
**Organisasi:** PT. Mentari Medika Indonesia  
**Status:** Final  

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Deskripsi Umum Sistem](#2-deskripsi-umum-sistem)
3. [Kebutuhan Fungsional](#3-kebutuhan-fungsional)
4. [Kebutuhan Non-Fungsional](#4-kebutuhan-non-fungsional)
5. [Batasan Sistem](#5-batasan-sistem)
6. [Diagram Alur Proses](#6-diagram-alur-proses)
7. [Matriks Peran dan Hak Akses](#7-matriks-peran-dan-hak-akses)

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen

Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS) ini disusun untuk mendefinisikan secara lengkap dan terstruktur seluruh kebutuhan fungsional dan non-fungsional dari sistem **Medikindo PO System** yang dikembangkan oleh PT. Mentari Medika Indonesia. Dokumen ini mengikuti standar IEEE 830 dan ditujukan kepada:

- Tim pengembang perangkat lunak
- Manajer proyek dan pemangku kepentingan
- Tim QA dan penguji sistem
- Pengguna akhir dan administrator sistem

### 1.2 Ruang Lingkup Sistem

**Medikindo PO System** adalah platform manajemen rantai pasok farmasi berbasis web yang dirancang untuk organisasi layanan kesehatan Indonesia (rumah sakit dan klinik). Sistem ini mencakup:

- Manajemen Purchase Order (PO) dengan alur persetujuan bertingkat
- Penerimaan barang (Goods Receipt) dengan pelacakan batch dan tanggal kedaluwarsa
- Pengelolaan faktur pemasok (Account Payable / AP) dan faktur pelanggan (Account Receivable / AR)
- Alur pembayaran dengan verifikasi bukti pembayaran
- Kontrol kredit dan batas kredit per organisasi
- Kepatuhan terhadap regulasi obat narkotika
- Pelaporan dan analitik keuangan

Sistem ini **tidak** mencakup:
- Manajemen inventaris gudang secara real-time
- Integrasi langsung dengan sistem BPJS atau SIRS rumah sakit
- Modul akuntansi umum (general ledger)

### 1.3 Definisi, Akronim, dan Singkatan

| Istilah | Definisi |
|---------|----------|
| PO | Purchase Order — dokumen pemesanan barang dari pelanggan ke pemasok |
| GR | Goods Receipt — dokumen penerimaan barang fisik |
| AP | Account Payable — faktur hutang kepada pemasok |
| AR | Account Receivable — faktur piutang dari pelanggan |
| SRS | Software Requirements Specification |
| RBAC | Role-Based Access Control |
| OTP | One-Time Password |
| e-Meterai | Meterai elektronik sesuai regulasi Indonesia |
| Narkotika | Obat-obatan yang termasuk dalam kategori narkotika sesuai regulasi BPOM |
| Level 1 Approval | Persetujuan standar untuk PO reguler |
| Level 2 Approval | Persetujuan tambahan khusus untuk PO yang mengandung item narkotika |
| Anti-Phantom Billing | Mekanisme pencegahan penagihan ganda dengan menghubungkan faktur ke GR spesifik |
| Optimistic Locking | Mekanisme kontrol konkurensi menggunakan kolom versi untuk mencegah konflik pembaruan |
| Credit Limit | Batas kredit maksimum yang diizinkan untuk satu organisasi pelanggan |
| Discrepancy | Selisih antara nilai faktur aktual dengan nilai PO yang melebihi ambang batas |

### 1.4 Referensi

- IEEE Std 830-1998: IEEE Recommended Practice for Software Requirements Specifications
- Peraturan BPOM tentang Pengelolaan Obat Narkotika dan Psikotropika
- Undang-Undang No. 10 Tahun 2020 tentang Bea Meterai (e-Meterai)
- Laravel 11 Documentation — https://laravel.com/docs/11.x
- Spatie Laravel-Permission Documentation
- MySQL 8.0 Reference Manual

### 1.5 Gambaran Umum Dokumen

Dokumen ini terdiri dari tujuh bagian utama. Bagian 1 memberikan konteks dan latar belakang. Bagian 2 mendeskripsikan sistem secara umum. Bagian 3 merinci seluruh kebutuhan fungsional per modul. Bagian 4 mendefinisikan kebutuhan non-fungsional. Bagian 5 menetapkan batasan sistem. Bagian 6 menyajikan diagram alur proses bisnis. Bagian 7 menyajikan matriks peran dan hak akses.

---

## 2. Deskripsi Umum Sistem

### 2.1 Perspektif Produk

Medikindo PO System adalah sistem mandiri berbasis web yang berfungsi sebagai jembatan antara organisasi layanan kesehatan (rumah sakit/klinik) sebagai pembeli dan PT. Mentari Medika Indonesia sebagai distributor farmasi. Sistem ini beroperasi sebagai aplikasi multi-tenant di mana setiap organisasi pelanggan memiliki data yang terisolasi.

```
+---------------------------+         +---------------------------+
|   Organisasi Pelanggan    |         |  PT. Mentari Medika       |
|  (Rumah Sakit / Klinik)   |         |  Indonesia (Medikindo)    |
|                           |         |                           |
|  Healthcare User          | <-----> |  Approver                 |
|  (Buat & Submit PO,       |         |  (Setujui/Tolak PO)       |
|   Terima Barang,          |         |                           |
|   Bayar Invoice)          |         |  Finance                  |
|                           |         |  (Kelola Invoice &        |
|                           |         |   Pembayaran)             |
|                           |         |                           |
|                           |         |  Admin Pusat              |
|                           |         |  (Operasional Penuh)      |
|                           |         |                           |
|                           |         |  Super Admin              |
|                           |         |  (Akses Penuh)            |
+---------------------------+         +---------------------------+
              |                                    |
              +------------------------------------+
                         Medikindo PO System
                    (Laravel 11 / MySQL 8.0)
```

### 2.2 Fungsi Produk Utama

Sistem menyediakan fungsi-fungsi utama berikut:

1. **Manajemen Purchase Order** — pembuatan, pengajuan, dan persetujuan PO dengan kontrol kredit
2. **Penerimaan Barang** — pencatatan penerimaan barang dengan pelacakan batch dan kedaluwarsa
3. **Manajemen Faktur AP** — pengelolaan faktur pemasok yang di-generate otomatis dari GR
4. **Manajemen Faktur AR** — pengelolaan faktur pelanggan dengan dukungan pembayaran parsial
5. **Manajemen Pembayaran** — verifikasi bukti pembayaran dan alokasi pembayaran otomatis
6. **Kontrol Kredit** — pemantauan batas kredit dan status piutang per organisasi
7. **Notifikasi** — pemberitahuan otomatis via database dan email untuk setiap event penting
8. **Pelaporan & Analitik** — dashboard, laporan AR aging, dan analitik produk

### 2.3 Karakteristik Pengguna

| Peran | Latar Belakang | Frekuensi Penggunaan | Keahlian Teknis |
|-------|---------------|---------------------|-----------------|
| Healthcare User | Staf pengadaan rumah sakit/klinik | Harian | Rendah–Menengah |
| Approver | Staf operasional Medikindo | Harian | Menengah |
| Finance | Tim keuangan Medikindo | Harian | Menengah–Tinggi |
| Admin Pusat | Admin operasional Medikindo | Harian | Tinggi |
| Super Admin | Administrator sistem Medikindo | Berkala | Tinggi |

### 2.4 Batasan Umum

- Sistem harus dapat diakses melalui browser web modern (Chrome, Firefox, Edge, Safari)
- Sistem dibangun menggunakan Laravel 11 dengan PHP 8.2+
- Database menggunakan MySQL 8.0
- Autentikasi berbasis sesi web (bukan token API untuk pengguna akhir)
- Semua transaksi keuangan menggunakan mata uang Rupiah Indonesia (IDR)
- Sistem harus mematuhi regulasi pengelolaan obat narkotika Indonesia

### 2.5 Asumsi dan Ketergantungan

- Server memiliki akses internet untuk pengiriman email notifikasi
- Pengguna memiliki akses ke browser web modern
- Data master (produk, pemasok, organisasi) dikelola oleh Super Admin sebelum sistem digunakan
- Setiap organisasi pelanggan telah memiliki batas kredit yang dikonfigurasi
- Harga jual produk selalu lebih besar atau sama dengan harga beli

---

## 3. Kebutuhan Fungsional

### 3.1 Modul Autentikasi dan Manajemen Pengguna

#### FR-AUTH-001: Login Pengguna
- Sistem harus menyediakan halaman login dengan input email dan password
- Sistem harus memvalidasi kredensial pengguna terhadap database
- Sistem harus mengarahkan pengguna ke dashboard sesuai perannya setelah login berhasil
- Sistem harus menampilkan pesan error yang sesuai jika login gagal
- Sistem harus mengimplementasikan proteksi terhadap brute force attack

#### FR-AUTH-002: Logout Pengguna
- Sistem harus menyediakan fungsi logout yang menghapus sesi pengguna
- Sistem harus mengarahkan pengguna ke halaman login setelah logout

#### FR-AUTH-003: Manajemen Pengguna (Super Admin)
- Super Admin dapat membuat akun pengguna baru dengan peran yang ditentukan
- Super Admin dapat mengubah data pengguna (nama, email, peran, organisasi)
- Super Admin dapat menonaktifkan atau mengaktifkan akun pengguna
- Super Admin dapat mereset password pengguna
- Setiap pengguna harus terhubung ke satu organisasi (kecuali pengguna internal Medikindo)

#### FR-AUTH-004: Kontrol Akses Berbasis Peran (RBAC)
- Sistem harus mengimplementasikan RBAC menggunakan Spatie Laravel-Permission
- Setiap endpoint harus diproteksi dengan permission yang sesuai
- Akses yang tidak diizinkan harus mengembalikan respons 403 Forbidden
- Sistem harus mendukung 5 peran: healthcare_user, approver, finance, admin_pusat, super_admin


### 3.2 Modul Purchase Order (PO)

#### FR-PO-001: Pembuatan Purchase Order
- Healthcare User dapat membuat PO baru dalam status `draft`
- PO harus mencantumkan: nomor PO (auto-generate), tanggal, organisasi, pemasok, dan daftar item
- Setiap item PO harus mencantumkan: produk, kuantitas, satuan, harga beli (cost_price), dan harga jual (selling_price)
- Sistem harus memvalidasi bahwa selling_price >= cost_price untuk setiap item
- Healthcare User dapat menyimpan PO sebagai draft sebelum disubmit

#### FR-PO-002: Pengeditan Purchase Order
- Healthcare User dapat mengedit PO yang masih berstatus `draft`
- PO yang sudah disubmit (status bukan `draft`) tidak dapat diedit
- Sistem harus mencatat perubahan pada PO melalui audit log

#### FR-PO-003: Pengajuan Purchase Order
- Healthcare User dapat mengajukan (submit) PO dari status `draft` ke `submitted`
- Sebelum submit, sistem harus melakukan pemeriksaan kredit:
  - Blokir jika organisasi memiliki faktur yang sudah jatuh tempo (overdue)
  - Blokir jika total PO melebihi sisa batas kredit organisasi
- Jika PO mengandung item narkotika, sistem harus menandai PO untuk Level 2 Approval
- Setelah submit, sistem harus mengirim notifikasi ke Approver, Super Admin, dan pembuat PO
- PO yang sudah disubmit tidak dapat diubah (immutable)

#### FR-PO-004: Persetujuan Purchase Order
- Approver dapat melihat daftar PO yang menunggu persetujuan
- Approver dapat menyetujui atau menolak PO
- Approver tidak dapat menyetujui PO yang dibuat oleh dirinya sendiri (self-approval prevention)
- PO yang mengandung narkotika memerlukan persetujuan Level 2 tambahan
- Jika disetujui: status PO berubah menjadi `approved`
- Jika ditolak: status PO berubah menjadi `rejected` dan Healthcare User dapat mengedit ulang (kembali ke `draft`)
- Sistem harus mengirim notifikasi hasil persetujuan ke pembuat PO

#### FR-PO-005: Status dan Alur PO
- Sistem harus mendukung status PO berikut: `draft`, `submitted`, `approved`, `partially_received`, `completed`, `rejected`
- Transisi status yang valid:
  - `draft` → `submitted` (oleh Healthcare User)
  - `submitted` → `approved` (oleh Approver)
  - `submitted` → `rejected` (oleh Approver)
  - `rejected` → `draft` (oleh Healthcare User setelah edit)
  - `approved` → `partially_received` (otomatis saat GR pertama dibuat)
  - `partially_received` → `completed` (otomatis saat semua item diterima)
  - `approved` → `completed` (otomatis jika semua item diterima sekaligus)

#### FR-PO-006: Tampilan dan Pencarian PO
- Semua peran yang memiliki izin dapat melihat daftar PO
- Sistem harus menyediakan filter berdasarkan: status, tanggal, organisasi, pemasok
- Sistem harus menyediakan pencarian berdasarkan nomor PO
- Detail PO harus menampilkan semua item, status persetujuan, dan riwayat aktivitas

### 3.3 Modul Goods Receipt (GR)

#### FR-GR-001: Pembuatan Goods Receipt
- Healthcare User dapat membuat GR untuk PO yang berstatus `approved` atau `partially_received`
- GR harus mencantumkan: nomor delivery order (delivery_order_number) dan foto pengiriman
- Setiap item GR harus mencantumkan: produk, kuantitas diterima, nomor batch (batch_no), dan tanggal kedaluwarsa (expiry_date)
- Sistem harus memvalidasi bahwa kuantitas yang diterima tidak melebihi sisa kuantitas yang belum diterima

#### FR-GR-002: Pengiriman Bertahap (Multiple Deliveries)
- Satu PO dapat memiliki beberapa GR (pengiriman bertahap)
- Sistem harus melacak total kuantitas yang sudah diterima vs yang dipesan per item
- Status PO harus diperbarui ke `partially_received` setelah GR pertama
- Status PO harus diperbarui ke `completed` setelah semua item diterima penuh

#### FR-GR-003: Pelacakan Batch dan Kedaluwarsa
- Sistem harus menyimpan nomor batch dan tanggal kedaluwarsa untuk setiap item yang diterima
- Data batch dan kedaluwarsa harus dapat ditelusuri untuk keperluan audit dan recall produk

#### FR-GR-004: Pemicu Invoice Otomatis
- Setelah GR dikonfirmasi, sistem harus secara otomatis membuat:
  - Satu SupplierInvoice (AP) berdasarkan cost_price dari PO
  - Satu CustomerInvoice (AR) berdasarkan selling_price dari Product
- CustomerInvoice harus mereferensikan SupplierInvoice yang baru dibuat (anti-phantom billing)
- Jika GR untuk PO+GR yang sama sudah ada invoicenya, sistem harus mengembalikan invoice yang sudah ada (idempotency)

#### FR-GR-005: Upload Dokumen
- Healthcare User harus mengunggah foto/dokumen surat jalan saat membuat GR
- Sistem harus menyimpan file dokumen dengan aman
- Format file yang didukung: JPG, PNG, PDF

### 3.4 Modul Invoice Pemasok / Account Payable (AP)

#### FR-AP-001: Pembuatan Invoice Pemasok
- SupplierInvoice dibuat secara otomatis dari GR menggunakan cost_price dari item PO
- Invoice harus mencantumkan: nomor invoice, tanggal, pemasok, daftar item, subtotal, pajak, dan total
- Sistem harus mendeteksi diskrepansi jika selisih nilai invoice vs PO > 1% ATAU > Rp 10.000

#### FR-AP-002: Status Invoice Pemasok
- Sistem harus mendukung status: `draft`, `verified`, `paid`, `overdue`
- Transisi status yang valid:
  - `draft` → `verified` (oleh Finance setelah verifikasi)
  - `verified` → `paid` (otomatis setelah pembayaran penuh)
  - `verified` → `overdue` (otomatis oleh scheduled job jika melewati jatuh tempo)

#### FR-AP-003: Immutabilitas Invoice
- Invoice yang sudah diterbitkan (status bukan `draft`) tidak dapat diubah
- Sistem harus menggunakan optimistic locking (kolom `version`) untuk mencegah konflik pembaruan
- Setiap percobaan modifikasi pada invoice yang sudah diterbitkan harus dicatat di `InvoiceModificationAttempt`

#### FR-AP-004: Verifikasi Diskrepansi
- Finance atau Admin Pusat dapat menyetujui diskrepansi invoice
- Sistem harus mencatat alasan persetujuan diskrepansi

#### FR-AP-005: Pembayaran Invoice Pemasok
- Payment OUT dibuat secara otomatis saat PaymentProof disetujui
- Payment OUT hanya dibuat jika SupplierInvoice berstatus `verified` atau `overdue`
- Sistem harus mengalokasikan pembayaran ke invoice pemasok yang sesuai

### 3.5 Modul Invoice Pelanggan / Account Receivable (AR)

#### FR-AR-001: Pembuatan Invoice Pelanggan
- CustomerInvoice dibuat secara otomatis dari GR menggunakan selling_price dari Product
- Invoice harus mencantumkan: nomor invoice, tanggal jatuh tempo, organisasi, daftar item, subtotal, surcharge, biaya e-meterai, dan total
- CustomerInvoice harus mereferensikan SupplierInvoice terkait (anti-phantom billing)

#### FR-AR-002: Status Invoice Pelanggan
- Sistem harus mendukung status: `draft`, `issued`, `partial_paid`, `paid`, `void`
- Transisi status yang valid:
  - `draft` → `issued` (oleh Finance saat menerbitkan invoice)
  - `issued` → `partial_paid` (otomatis saat ada pembayaran parsial)
  - `issued` / `partial_paid` → `paid` (otomatis saat pembayaran penuh)
  - `issued` → `void` (oleh Finance/Admin Pusat untuk pembatalan)

#### FR-AR-003: Pembayaran Parsial
- Sistem harus mendukung pembayaran parsial untuk CustomerInvoice
- Sistem harus melacak `paid_amount` dan `remaining_amount` per invoice
- Status invoice harus diperbarui secara otomatis berdasarkan jumlah pembayaran

#### FR-AR-004: Surcharge dan E-Meterai
- Sistem harus mendukung penambahan surcharge pada invoice
- Sistem harus mendukung penambahan biaya e-meterai sesuai regulasi Indonesia
- Nilai surcharge dan e-meterai harus ditampilkan secara terpisah pada invoice

#### FR-AR-005: Cetak Invoice
- Sistem harus menyediakan fungsi cetak invoice dalam format PDF menggunakan DomPDF
- Sistem harus melacak jumlah cetak (print_count) per invoice
- Invoice yang dicetak harus memiliki format yang sesuai standar dokumen keuangan Indonesia

#### FR-AR-006: AR Aging Report
- Sistem harus mengkategorikan piutang berdasarkan umur: Current, 1-30 hari, 31-60 hari, 61-90 hari, >90 hari
- Laporan harus dapat difilter per organisasi dan periode

### 3.6 Modul Bukti Pembayaran (Payment Proof)

#### FR-PP-001: Pengajuan Bukti Pembayaran
- Healthcare User dapat mengajukan bukti pembayaran untuk CustomerInvoice yang berstatus `issued` atau `partial_paid`
- Bukti pembayaran harus mencantumkan: jumlah pembayaran, tanggal pembayaran, bank pengirim, dan referensi transfer
- Healthcare User harus mengunggah dokumen bukti pembayaran (foto/PDF)
- Sistem mendukung pembayaran penuh maupun parsial

#### FR-PP-002: Verifikasi Bukti Pembayaran
- Finance dapat memverifikasi bukti pembayaran yang diajukan
- Finance harus mengkonfirmasi kesesuaian jumlah dan detail transfer
- Setelah verifikasi, status berubah dari `submitted` ke `verified`

#### FR-PP-003: Persetujuan Bukti Pembayaran
- Finance atau Admin Pusat dapat menyetujui bukti pembayaran yang sudah diverifikasi
- Saat disetujui, sistem secara otomatis:
  - Membuat Payment IN yang mengurangi `paid_amount` pada CustomerInvoice
  - Membuat Payment OUT ke pemasok jika SupplierInvoice sudah `verified`
  - Mengalokasikan pembayaran ke invoice yang sesuai
- Sistem harus mengirim notifikasi ke Healthcare User (submitter) dan Finance

#### FR-PP-004: Penolakan Bukti Pembayaran
- Finance dapat menolak bukti pembayaran dengan menyertakan alasan penolakan
- Status berubah ke `rejected`
- Sistem harus mengirim notifikasi penolakan ke Healthcare User (submitter)
- Healthcare User dapat mengajukan ulang (resubmit) bukti pembayaran yang ditolak

#### FR-PP-005: Penarikan Bukti Pembayaran
- Healthcare User dapat menarik kembali (recall) bukti pembayaran yang masih berstatus `submitted`
- Bukti pembayaran yang sudah diverifikasi atau disetujui tidak dapat ditarik

#### FR-PP-006: Koreksi oleh Super Admin
- Super Admin dapat mengoreksi bukti pembayaran yang sudah disetujui
- Koreksi harus dicatat dalam audit log dengan alasan yang jelas

#### FR-PP-007: Status dan Alur Bukti Pembayaran
- Status yang didukung: `submitted`, `verified`, `approved`, `rejected`, `resubmitted`, `recalled`
- Transisi status yang valid:
  - `submitted` → `verified` (oleh Finance)
  - `verified` → `approved` (oleh Finance/Admin Pusat)
  - `submitted` → `rejected` (oleh Finance)
  - `verified` → `rejected` (oleh Finance)
  - `rejected` → `resubmitted` (oleh Healthcare User)
  - `submitted` → `recalled` (oleh Healthcare User)


### 3.7 Modul Pembayaran (Payment IN / OUT)

#### FR-PAY-001: Payment IN (Pembayaran Masuk)
- Payment IN dibuat secara otomatis saat PaymentProof disetujui
- Payment IN harus mencantumkan: jumlah, tanggal, referensi, dan CustomerInvoice yang terkait
- Sistem harus mengurangi `paid_amount` pada CustomerInvoice sesuai jumlah Payment IN
- Sistem harus memperbarui status CustomerInvoice secara otomatis (partial_paid / paid)

#### FR-PAY-002: Payment OUT (Pembayaran Keluar)
- Payment OUT dibuat secara otomatis saat PaymentProof disetujui, jika SupplierInvoice sudah `verified` atau `overdue`
- Payment OUT harus mencantumkan: jumlah, tanggal, referensi, dan SupplierInvoice yang terkait
- Sistem harus mengurangi `paid_amount` pada SupplierInvoice sesuai jumlah Payment OUT
- Sistem harus memperbarui status SupplierInvoice secara otomatis

#### FR-PAY-003: Alokasi Pembayaran
- Sistem harus mengalokasikan pembayaran ke invoice yang tepat melalui tabel `PaymentAllocation`
- Satu pembayaran dapat dialokasikan ke beberapa invoice (untuk pembayaran parsial)
- Sistem harus mencegah alokasi ganda untuk invoice yang sama

#### FR-PAY-004: Aturan Cashflow
- Payment OUT hanya boleh dibuat jika SupplierInvoice berstatus `verified` atau `overdue`
- Sistem harus memvalidasi aturan ini sebelum membuat Payment OUT

### 3.8 Modul Notifikasi

#### FR-NOTIF-001: Notifikasi Event PO
- Saat PO disubmit: notifikasi dikirim ke Approver, Super Admin, dan pembuat PO (Database + Email)
- Saat PO disetujui: notifikasi dikirim ke pembuat PO, Healthcare User, dan Super Admin (Database + Email)
- Saat PO ditolak: notifikasi dikirim ke pembuat PO, Healthcare User, dan Super Admin (Database + Email)

#### FR-NOTIF-002: Notifikasi Event GR
- Saat barang diterima (GR dikonfirmasi): notifikasi dikirim ke Healthcare User, Finance, dan Super Admin (Database + Email)

#### FR-NOTIF-003: Notifikasi Event Invoice
- Saat invoice diterbitkan: notifikasi dikirim ke Healthcare User, Finance, dan Super Admin (Database)
- Saat invoice jatuh tempo (overdue): notifikasi dikirim ke Finance, Super Admin, dan Admin Pusat (Database)

#### FR-NOTIF-004: Notifikasi Event Bukti Pembayaran
- Saat bukti pembayaran diajukan: notifikasi dikirim ke Finance, Admin Pusat, dan Super Admin (Database)
- Saat bukti pembayaran disetujui: notifikasi dikirim ke Healthcare User (submitter) dan Finance (Database)
- Saat bukti pembayaran ditolak: notifikasi dikirim ke Healthcare User (submitter) (Database)

#### FR-NOTIF-005: Saluran Notifikasi
- Notifikasi Database: disimpan di tabel `notifications` dan ditampilkan di UI
- Notifikasi Email: dikirim melalui SMTP server yang dikonfigurasi
- Pengguna dapat menandai notifikasi sebagai sudah dibaca

### 3.9 Modul Laporan dan Analitik

#### FR-RPT-001: Dashboard Utama
- Setiap peran memiliki dashboard yang disesuaikan dengan data yang relevan
- Dashboard Healthcare User: ringkasan PO aktif, invoice outstanding, status pembayaran
- Dashboard Approver: daftar PO menunggu persetujuan, statistik persetujuan
- Dashboard Finance: ringkasan AR/AP, pembayaran tertunda, status kredit
- Dashboard Admin Pusat: overview operasional lengkap
- Dashboard Super Admin: overview sistem penuh termasuk data master

#### FR-RPT-002: Finance Dashboard
- Menampilkan AR aging summary (current, 1-30, 31-60, 61-90, >90 hari)
- Menampilkan status kredit per organisasi
- Menampilkan pembayaran yang sedang diproses
- Menampilkan invoice yang akan jatuh tempo dalam 7 hari ke depan

#### FR-RPT-003: AR Aging Report
- Laporan piutang berdasarkan umur dengan kategori: Current, 1-30 hari, 31-60 hari, 61-90 hari, >90 hari
- Dapat difilter berdasarkan organisasi dan periode tanggal
- Dapat diekspor dalam format yang sesuai

#### FR-RPT-004: Audit Dashboard
- Menampilkan semua aksi sistem yang tercatat di AuditLog
- Dapat difilter berdasarkan pengguna, tipe aksi, dan periode
- Hanya dapat diakses oleh Admin Pusat dan Super Admin

#### FR-RPT-005: Analitik Produk
- Menampilkan tren penjualan per produk
- Menampilkan performa produk (volume, nilai, frekuensi pemesanan)
- Dapat difilter berdasarkan periode dan kategori produk

### 3.10 Modul Master Data

#### FR-MD-001: Manajemen Organisasi (Super Admin)
- Super Admin dapat membuat, mengubah, dan menonaktifkan organisasi pelanggan
- Data organisasi mencakup: nama, alamat, kontak, NPWP, dan batas kredit
- Sistem harus mendukung konfigurasi batas kredit per organisasi

#### FR-MD-002: Manajemen Pemasok (Super Admin)
- Super Admin dapat membuat, mengubah, dan menonaktifkan data pemasok
- Data pemasok mencakup: nama, alamat, kontak, NPWP, dan rekening bank

#### FR-MD-003: Manajemen Produk (Super Admin)
- Super Admin dapat membuat, mengubah, dan menonaktifkan produk
- Data produk mencakup: nama, kode, kategori, satuan, harga beli, harga jual, dan status narkotika
- Sistem harus memvalidasi bahwa harga jual >= harga beli saat pembuatan/pembaruan produk

#### FR-MD-004: Manajemen Rekening Bank (Super Admin)
- Super Admin dapat mengelola rekening bank untuk keperluan pembayaran
- Data rekening mencakup: nama bank, nomor rekening, nama pemilik rekening

#### FR-MD-005: Manajemen Batas Kredit (Super Admin / Finance)
- Super Admin dan Finance dapat mengatur batas kredit per organisasi
- Sistem harus mencatat riwayat perubahan batas kredit

#### FR-MD-006: Kontrol Kredit
- Sistem harus memantau penggunaan kredit secara real-time
- Sistem harus memblokir pengajuan PO jika batas kredit terlampaui
- Sistem harus memblokir pengajuan PO jika ada invoice overdue


---

## 4. Kebutuhan Non-Fungsional

### 4.1 Performa

| ID | Kebutuhan | Target |
|----|-----------|--------|
| NFR-PERF-001 | Waktu respons halaman web | < 2 detik untuk 95% request |
| NFR-PERF-002 | Waktu respons API | < 500ms untuk operasi CRUD standar |
| NFR-PERF-003 | Pembuatan invoice otomatis | < 3 detik setelah GR dikonfirmasi |
| NFR-PERF-004 | Kapasitas pengguna bersamaan | Mendukung minimal 100 pengguna aktif bersamaan |
| NFR-PERF-005 | Kapasitas data | Mendukung minimal 100.000 PO dan 500.000 item per tahun |
| NFR-PERF-006 | Waktu generate laporan | < 10 detik untuk laporan AR aging |

### 4.2 Keamanan

| ID | Kebutuhan |
|----|-----------|
| NFR-SEC-001 | Semua komunikasi harus menggunakan HTTPS/TLS 1.2+ |
| NFR-SEC-002 | Password harus di-hash menggunakan bcrypt dengan cost factor minimal 10 |
| NFR-SEC-003 | Sistem harus mengimplementasikan proteksi CSRF pada semua form |
| NFR-SEC-004 | Sistem harus mengimplementasikan proteksi XSS pada semua output |
| NFR-SEC-005 | Sistem harus mengimplementasikan proteksi SQL Injection melalui Eloquent ORM |
| NFR-SEC-006 | Sesi pengguna harus kedaluwarsa setelah 8 jam tidak aktif |
| NFR-SEC-007 | Semua aksi sensitif harus dicatat dalam AuditLog |
| NFR-SEC-008 | File upload harus divalidasi tipe dan ukurannya (maks 10MB per file) |
| NFR-SEC-009 | Akses ke data organisasi harus dibatasi berdasarkan tenant (multi-tenant isolation) |
| NFR-SEC-010 | Sistem harus mencegah self-approval pada proses persetujuan PO |

### 4.3 Ketersediaan dan Keandalan

| ID | Kebutuhan | Target |
|----|-----------|--------|
| NFR-AVAIL-001 | Uptime sistem | >= 99.5% per bulan (tidak termasuk maintenance terjadwal) |
| NFR-AVAIL-002 | Recovery Time Objective (RTO) | < 4 jam setelah kegagalan sistem |
| NFR-AVAIL-003 | Recovery Point Objective (RPO) | < 1 jam (backup database harian minimum) |
| NFR-AVAIL-004 | Maintenance window | Maksimal 2 jam per minggu, di luar jam kerja |
| NFR-AVAIL-005 | Konsistensi data | Semua transaksi keuangan harus menggunakan database transaction (ACID) |

### 4.4 Skalabilitas

| ID | Kebutuhan |
|----|-----------|
| NFR-SCAL-001 | Arsitektur harus mendukung penambahan organisasi pelanggan tanpa perubahan kode |
| NFR-SCAL-002 | Database harus mendukung partisi data per organisasi jika diperlukan |
| NFR-SCAL-003 | Sistem harus mendukung horizontal scaling pada layer aplikasi |
| NFR-SCAL-004 | Antrian notifikasi harus menggunakan queue system (Laravel Queue) untuk mencegah bottleneck |

### 4.5 Pemeliharaan

| ID | Kebutuhan |
|----|-----------|
| NFR-MAINT-001 | Kode harus mengikuti PSR-12 coding standard untuk PHP |
| NFR-MAINT-002 | Setiap modul harus memiliki unit test dengan coverage minimal 80% |
| NFR-MAINT-003 | Sistem harus menyediakan logging yang memadai untuk debugging |
| NFR-MAINT-004 | Migrasi database harus menggunakan Laravel Migrations |
| NFR-MAINT-005 | Konfigurasi environment harus menggunakan file .env (tidak hardcoded) |
| NFR-MAINT-006 | Sistem harus menyediakan scheduled job untuk pembaruan status invoice overdue |

### 4.6 Kompatibilitas

| ID | Kebutuhan |
|----|-----------|
| NFR-COMPAT-001 | Browser yang didukung: Chrome 90+, Firefox 88+, Edge 90+, Safari 14+ |
| NFR-COMPAT-002 | Tampilan harus responsif untuk layar desktop (min 1024px) dan tablet (768px+) |
| NFR-COMPAT-003 | PHP versi 8.2 atau lebih baru |
| NFR-COMPAT-004 | MySQL versi 8.0 atau lebih baru |
| NFR-COMPAT-005 | Format tanggal menggunakan standar Indonesia (DD/MM/YYYY) pada tampilan UI |
| NFR-COMPAT-006 | Format mata uang menggunakan Rupiah Indonesia (Rp) dengan pemisah ribuan titik |

### 4.7 Audit dan Kepatuhan

| ID | Kebutuhan |
|----|-----------|
| NFR-AUDIT-001 | Semua perubahan data kritis harus dicatat dalam AuditLog (siapa, apa, kapan) |
| NFR-AUDIT-002 | AuditLog tidak dapat dihapus atau diubah oleh pengguna manapun |
| NFR-AUDIT-003 | Sistem harus mendukung pelacakan obat narkotika dari PO hingga penerimaan |
| NFR-AUDIT-004 | Invoice yang sudah diterbitkan tidak dapat diubah (immutability) |
| NFR-AUDIT-005 | Setiap percobaan modifikasi invoice yang sudah diterbitkan harus dicatat |

---

## 5. Batasan Sistem

### 5.1 Batasan Teknis

- Sistem dibangun di atas framework Laravel 11 dan tidak dapat dimigrasi ke framework lain tanpa pengembangan ulang
- Database yang didukung hanya MySQL 8.0; penggunaan database lain memerlukan pengujian kompatibilitas
- Pembuatan PDF menggunakan DomPDF; format cetak terbatas pada kemampuan DomPDF
- Sistem tidak menyediakan API publik untuk integrasi pihak ketiga (hanya web interface)
- Ukuran file upload dibatasi maksimal 10MB per file
- Sistem tidak mendukung multi-currency; semua transaksi dalam Rupiah Indonesia (IDR)

### 5.2 Batasan Regulasi

- Pengelolaan obat narkotika harus mematuhi Peraturan BPOM dan Kemenkes RI
- Penggunaan e-meterai harus sesuai dengan Undang-Undang No. 10 Tahun 2020
- Data pelanggan harus dikelola sesuai dengan regulasi perlindungan data yang berlaku di Indonesia
- Sistem tidak menggantikan kewajiban pelaporan narkotika ke instansi pemerintah

### 5.3 Batasan Operasional

- Sistem memerlukan koneksi internet untuk pengiriman notifikasi email
- Backup database adalah tanggung jawab tim infrastruktur dan tidak dikelola oleh aplikasi
- Pengguna harus memiliki akun yang dibuat oleh Super Admin; tidak ada registrasi mandiri
- Satu pengguna hanya dapat terhubung ke satu organisasi
- Perubahan harga produk tidak berlaku retroaktif pada PO yang sudah dibuat
- Sistem tidak mendukung pembatalan PO yang sudah disetujui; hanya dapat diselesaikan atau ditolak sebelum persetujuan

---

## 6. Diagram Alur Proses

### 6.1 Alur Purchase Order (PO)

```
Healthcare User                 Approver                    Sistem
      |                             |                          |
      |-- Buat PO (draft) --------->|                          |
      |                             |                          |
      |-- Edit PO (jika perlu) ---->|                          |
      |                             |                          |
      |-- Submit PO --------------->|                          |
      |         |                   |                          |
      |         +-- Cek Kredit ---->|                          |
      |         |   [BLOKIR jika overdue/limit terlampaui]     |
      |         |                   |                          |
      |         +-- Cek Narkotika ->|                          |
      |         |   [Tandai Level 2 jika ada item narkotika]   |
      |         |                   |                          |
      |         +-- Notifikasi ----->Approver, Super Admin     |
      |                             |                          |
      |                    Approver mereview PO                |
      |                             |                          |
      |                    [Setujui / Tolak]                   |
      |                             |                          |
      |<-- Notifikasi Hasil --------|                          |
      |                             |                          |
      |   [Jika Disetujui]          |                          |
      |                             +-- Status: approved ----->|
      |                             |                          |
      |   [Jika Ditolak]            |                          |
      |                             +-- Status: rejected ----->|
      |-- Edit & Resubmit --------->|                          |

Alur Status PO:
  draft --> submitted --> approved --> partially_received --> completed
                     \--> rejected --> draft (edit ulang)
```

### 6.2 Alur Goods Receipt (GR)

```
Healthcare User                    Sistem
      |                               |
      |-- Buat GR untuk PO approved ->|
      |   (delivery_order_number,     |
      |    foto, item + batch +        |
      |    expiry_date)               |
      |                               |
      |                    Validasi kuantitas
      |                    (tidak melebihi sisa PO)
      |                               |
      |                    Simpan GR  |
      |                               |
      |                    Update status PO:
      |                    - partially_received (jika belum semua)
      |                    - completed (jika semua item diterima)
      |                               |
      |                    AUTO-GENERATE INVOICE:
      |                    +-- SupplierInvoice (AP)
      |                    |   (cost_price dari PO)
      |                    |
      |                    +-- CustomerInvoice (AR)
      |                        (selling_price dari Product)
      |                        (referensi ke SupplierInvoice)
      |                               |
      |                    Kirim Notifikasi ke:
      |                    Healthcare User, Finance, Super Admin
      |<-- Konfirmasi GR berhasil ----|

Alur Status GR:
  [GR Pertama]  --> partial
  [GR Terakhir] --> completed
```

### 6.3 Alur Invoice (AP & AR)

```
                    GR Dikonfirmasi
                          |
                          v
              +---------------------------+
              |   Auto-Generate Invoice   |
              +---------------------------+
                    /              \
                   /                \
                  v                  v
    SupplierInvoice (AP)      CustomerInvoice (AR)
    [cost_price dari PO]      [selling_price dari Product]
           |                          |
           |                          |
    Cek Diskrepansi            Status: draft
    (>1% atau >Rp10.000)              |
           |                          v
    [Jika ada diskrepansi]     Finance menerbitkan
    Tandai untuk review        Status: issued
           |                          |
           v                          v
    Finance verifikasi         Notifikasi ke
    Status: verified           Healthcare User
           |
           v
    Menunggu pembayaran
    [Jika lewat jatuh tempo]
    Status: overdue

Status AP: draft --> verified --> paid
                              \-> overdue --> paid

Status AR: draft --> issued --> partial_paid --> paid
                           \--> void
```

### 6.4 Alur Bukti Pembayaran dan Pembayaran

```
Healthcare User          Finance/Admin Pusat          Sistem
      |                          |                       |
      |-- Submit Bukti Bayar --->|                       |
      |   (jumlah, tanggal,      |                       |
      |    bank, referensi,      |                       |
      |    dokumen upload)       |                       |
      |                          |                       |
      |                 Notifikasi ke Finance,           |
      |                 Admin Pusat, Super Admin         |
      |                          |                       |
      |                 Finance mereview                 |
      |                          |                       |
      |                 [Verifikasi]                     |
      |                 Status: verified                 |
      |                          |                       |
      |                 [Setujui]                        |
      |                 Status: approved                 |
      |                          |                       |
      |                          +-- AUTO-CREATE ------->|
      |                          |   Payment IN          |
      |                          |   (kurangi AR)        |
      |                          |                       |
      |                          +-- AUTO-CREATE ------->|
      |                          |   Payment OUT         |
      |                          |   (jika AP verified)  |
      |                          |   (kurangi AP)        |
      |                          |                       |
      |<-- Notifikasi Disetujui--|                       |
      |                          |                       |
      |   [Jika Ditolak]         |                       |
      |<-- Notifikasi Ditolak ---|                       |
      |-- Resubmit Bukti ------->|                       |

Status Bukti Pembayaran:
  submitted --> verified --> approved
           \--> rejected --> resubmitted --> verified --> approved
  submitted --> recalled

Aturan Cashflow:
  Payment OUT HANYA dibuat jika SupplierInvoice = verified/overdue
```

### 6.5 Alur Kontrol Kredit

```
Healthcare User mengajukan PO
          |
          v
  Sistem memeriksa kredit organisasi
          |
          +-- Apakah ada invoice OVERDUE? --> [YA] --> BLOKIR PO
          |
          +-- Apakah total PO > sisa kredit? --> [YA] --> BLOKIR PO
          |
          v
  [TIDAK ada masalah kredit]
          |
          v
  PO dapat dilanjutkan ke proses persetujuan

Komponen Kredit:
  Batas Kredit = CreditLimit.limit_amount
  Kredit Terpakai = total CustomerInvoice outstanding (issued + partial_paid)
  Sisa Kredit = Batas Kredit - Kredit Terpakai
```

---

## 7. Matriks Peran dan Hak Akses

### 7.1 Definisi Peran

| Peran | Kode | Deskripsi |
|-------|------|-----------|
| Healthcare User | healthcare_user | Staf pengadaan rumah sakit/klinik |
| Approver | approver | Staf operasional Medikindo untuk persetujuan PO |
| Finance | finance | Tim keuangan Medikindo |
| Admin Pusat | admin_pusat | Administrator operasional Medikindo |
| Super Admin | super_admin | Administrator sistem dengan akses penuh |

### 7.2 Matriks Hak Akses per Modul

#### Modul Dashboard

| Permission | Healthcare User | Approver | Finance | Admin Pusat | Super Admin |
|-----------|:--------------:|:--------:|:-------:|:-----------:|:-----------:|
| view_dashboard | ✓ | ✓ | ✓ | ✓ | ✓ |
| view_finance_dashboard | - | - | ✓ | ✓ | ✓ |
| view_audit_dashboard | - | - | - | ✓ | ✓ |

#### Modul Purchase Order

| Permission | Healthcare User | Approver | Finance | Admin Pusat | Super Admin |
|-----------|:--------------:|:--------:|:-------:|:-----------:|:-----------:|
| view_purchase_orders | ✓ | ✓ | - | ✓ | ✓ |
| create_purchase_orders | ✓ | - | - | - | ✓ |
| update_purchase_orders | ✓ | - | - | - | ✓ |
| submit_purchase_orders | ✓ | - | - | - | ✓ |
| approve_purchase_orders | - | ✓ | - | ✓ | ✓ |
| view_approvals | - | ✓ | - | ✓ | ✓ |

#### Modul Goods Receipt

| Permission | Healthcare User | Approver | Finance | Admin Pusat | Super Admin |
|-----------|:--------------:|:--------:|:-------:|:-----------:|:-----------:|
| view_goods_receipt | ✓ | ✓ | ✓ | ✓ | ✓ |
| confirm_receipt | ✓ | - | - | ✓ | ✓ |
| create_goods_receipt | ✓ | - | - | ✓ | ✓ |

#### Modul Invoice (AP & AR)

| Permission | Healthcare User | Approver | Finance | Admin Pusat | Super Admin |
|-----------|:--------------:|:--------:|:-------:|:-----------:|:-----------:|
| view_invoices | ✓ | - | ✓ | ✓ | ✓ |
| create_invoices | - | - | ✓ | ✓ | ✓ |
| verify_supplier_invoice | - | - | ✓ | ✓ | ✓ |
| approve_invoice_discrepancy | - | - | - | ✓ | ✓ |
| void_invoice | - | - | ✓ | ✓ | ✓ |
| print_invoice | ✓ | - | ✓ | ✓ | ✓ |

#### Modul Bukti Pembayaran

| Permission | Healthcare User | Approver | Finance | Admin Pusat | Super Admin |
|-----------|:--------------:|:--------:|:-------:|:-----------:|:-----------:|
| submit_payment_proof | ✓ | - | - | - | ✓ |
| upload_payment_document | ✓ | - | - | - | ✓ |
| view_payment_status | ✓ | - | ✓ | ✓ | ✓ |
| verify_payment_proof | - | - | ✓ | ✓ | ✓ |
| approve_payment | - | - | ✓ | ✓ | ✓ |
| reject_payment_proof | - | - | ✓ | ✓ | ✓ |
| correct_approved_proof | - | - | - | - | ✓ |

#### Modul Pembayaran

| Permission | Healthcare User | Approver | Finance | Admin Pusat | Super Admin |
|-----------|:--------------:|:--------:|:-------:|:-----------:|:-----------:|
| process_payments | - | - | ✓ | ✓ | ✓ |
| view_payments | - | - | ✓ | ✓ | ✓ |
| view_credit_control | - | - | ✓ | ✓ | ✓ |

#### Modul Laporan

| Permission | Healthcare User | Approver | Finance | Admin Pusat | Super Admin |
|-----------|:--------------:|:--------:|:-------:|:-----------:|:-----------:|
| view_reports | - | - | ✓ | ✓ | ✓ |
| view_ar_aging | - | - | ✓ | ✓ | ✓ |
| view_product_analytics | - | - | ✓ | ✓ | ✓ |
| view_audit | - | - | - | ✓ | ✓ |

#### Modul Master Data

| Permission | Healthcare User | Approver | Finance | Admin Pusat | Super Admin |
|-----------|:--------------:|:--------:|:-------:|:-----------:|:-----------:|
| manage_organizations | - | - | - | - | ✓ |
| manage_suppliers | - | - | - | - | ✓ |
| manage_products | - | - | - | - | ✓ |
| manage_users | - | - | - | - | ✓ |
| manage_bank_accounts | - | - | - | - | ✓ |
| manage_credit_limits | - | - | ✓ | - | ✓ |
| manage_price_lists | - | - | - | - | ✓ |

### 7.3 Ringkasan Hak Akses per Peran

#### Healthcare User
- Membuat, mengedit, dan mengajukan Purchase Order
- Membuat Goods Receipt (konfirmasi penerimaan barang)
- Melihat invoice yang ditujukan ke organisasinya
- Mengajukan dan mengelola bukti pembayaran
- Melihat status pembayaran

#### Approver
- Melihat semua Purchase Order
- Menyetujui atau menolak Purchase Order (kecuali milik sendiri)
- Melihat daftar persetujuan yang menunggu tindakan
- Melihat Goods Receipt

#### Finance
- Melihat dan mengelola semua invoice (AP & AR)
- Memverifikasi dan menyetujui bukti pembayaran
- Memproses pembayaran
- Mengelola batas kredit
- Mengakses semua laporan keuangan

#### Admin Pusat
- Semua hak akses Finance
- Menyetujui diskrepansi invoice
- Mengakses audit dashboard
- Akses operasional penuh (PO, GR, Invoice, Pembayaran)
- TIDAK dapat mengelola master data

#### Super Admin
- Semua hak akses Admin Pusat
- Mengelola semua master data (organisasi, pemasok, produk, pengguna, rekening bank)
- Mengoreksi bukti pembayaran yang sudah disetujui
- Akses penuh ke seluruh sistem

---

## 8. Model Data Utama

### 8.1 Entitas dan Relasi

```
Organization (1) ----< PurchaseOrder (1) ----< PurchaseOrderItem
                                |
                                v
                         GoodsReceipt (1) ----< GoodsReceiptItem
                                |              < GoodsReceiptDelivery
                                |                    |
                                |              < GoodsReceiptDeliveryItem
                                |
                    +-----------+-----------+
                    |                       |
                    v                       v
             SupplierInvoice          CustomerInvoice
             (AP)                     (AR)
             |                        |
             < SupplierInvoiceLineItem < CustomerInvoiceLineItem
             |                        |
             v                        v
          Payment (OUT)           Payment (IN)
             |                        |
             +--------+  +------------+
                      |  |
                      v  v
                 PaymentAllocation

PaymentProof (1) ----< PaymentDocument
     |
     +----> Payment IN (auto-create on approval)
     +----> Payment OUT (auto-create on approval)

User (1) ----< Approval
Organization (1) ----< CreditLimit
Organization (1) ----< CreditUsage
```

### 8.2 Tabel Utama dan Kolom Kunci

| Tabel | Kolom Kunci | Keterangan |
|-------|-------------|------------|
| purchase_orders | id, organization_id, status, total_amount, has_narcotic | PO header |
| purchase_order_items | po_id, product_id, quantity, cost_price, selling_price | Item PO |
| goods_receipts | id, po_id, status, delivery_order_number | GR header |
| goods_receipt_items | gr_id, product_id, quantity_received, batch_no, expiry_date | Item GR |
| supplier_invoices | id, gr_id, status, total_amount, version | AP invoice |
| customer_invoices | id, gr_id, supplier_invoice_id, status, total_amount, paid_amount, version | AR invoice |
| payment_proofs | id, customer_invoice_id, amount, status, submitted_by | Bukti bayar |
| payments | id, type (IN/OUT), amount, invoice_id | Pembayaran |
| payment_allocations | payment_id, invoice_id, allocated_amount | Alokasi bayar |
| approvals | id, po_id, approver_id, level, status, decision | Persetujuan |
| credit_limits | organization_id, limit_amount, used_amount | Batas kredit |
| audit_logs | id, user_id, action, model_type, model_id, changes | Audit trail |

---

## 9. Aturan Bisnis Kritis

### 9.1 Ringkasan Aturan Bisnis

| ID | Aturan | Modul | Dampak Pelanggaran |
|----|--------|-------|-------------------|
| BR-001 | Kontrol Kredit: Blokir PO jika ada invoice overdue | PO | PO tidak dapat disubmit |
| BR-002 | Kontrol Kredit: Blokir PO jika melebihi batas kredit | PO | PO tidak dapat disubmit |
| BR-003 | Level 2 Approval wajib untuk item narkotika | PO | PO tidak dapat disetujui tanpa Level 2 |
| BR-004 | Self-approval dilarang | PO | Exception/Error |
| BR-005 | PO immutable setelah disubmit | PO | Exception/Error |
| BR-006 | Invoice immutable setelah diterbitkan | Invoice | Exception + catat di InvoiceModificationAttempt |
| BR-007 | Anti-Phantom: CustomerInvoice harus referensi SupplierInvoice | Invoice | AntiPhantomBillingException |
| BR-008 | Diskrepansi invoice > 1% atau > Rp 10.000 harus ditandai | Invoice | Flagged untuk review |
| BR-009 | selling_price >= cost_price | Produk/PO | MarginViolationException |
| BR-010 | Idempotency: invoice duplikat untuk PO+GR yang sama dikembalikan | Invoice | Kembalikan invoice yang ada |
| BR-011 | Payment OUT hanya jika SupplierInvoice verified/overdue | Pembayaran | Payment OUT tidak dibuat |
| BR-012 | Hanya bukti yang ditolak yang dapat diajukan ulang | Bukti Bayar | Exception/Error |
| BR-013 | Hanya bukti yang submitted yang dapat ditarik | Bukti Bayar | Exception/Error |
| BR-014 | Optimistic locking pada invoice menggunakan kolom version | Invoice | ConcurrencyException |

---

*Dokumen ini disusun berdasarkan standar IEEE 830 dan mencerminkan kebutuhan sistem Medikindo PO System versi 1.0.*  
*Untuk pertanyaan atau klarifikasi, hubungi tim pengembang PT. Mentari Medika Indonesia.*
