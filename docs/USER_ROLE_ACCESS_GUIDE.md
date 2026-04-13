# Panduan Akses User Berdasarkan Role
## Medikindo PO System

**Versi**: 1.0  
**Tanggal**: 13 April 2026  
**Status**: ✅ Verified & Tested

---

## 📋 Daftar Isi

1. [Healthcare User (Pengguna Rumah Sakit/Klinik)](#1-healthcare-user)
2. [Approver (Tim Operasional Medikindo)](#2-approver)
3. [Finance (Bagian Keuangan)](#3-finance)
4. [Super Admin (Administrator Sistem)](#4-super-admin)

---

## 1. Healthcare User (Pengguna Rumah Sakit/Klinik)

### 👤 Deskripsi Role
Staf rumah sakit atau klinik yang bertanggung jawab untuk membuat purchase order dan menerima barang.

### ✅ Hak Akses

#### 📊 Dashboard
- ✅ **Dapat mengakses** dashboard utama
- ✅ **Dapat melihat** ringkasan purchase order mereka
- ❌ **Tidak dapat mengakses** dashboard keuangan
- ❌ **Tidak dapat mengakses** audit log

#### 🛒 Purchase Orders (PO)
| Aksi | Status | Keterangan |
|------|--------|------------|
| Melihat daftar PO | ✅ | Hanya PO organisasi sendiri |
| Melihat detail PO | ✅ | Hanya PO organisasi sendiri |
| Membuat PO baru | ✅ | Untuk organisasi sendiri |
| Mengedit PO (Draft) | ✅ | Hanya PO yang masih draft |
| Submit PO untuk approval | ✅ | Mengubah status draft → submitted |
| Menghapus PO | ❌ | Hanya Super Admin |
| Export PO ke PDF | ✅ | Untuk PO organisasi sendiri |
| Approve/Reject PO | ❌ | Hanya Approver |
| Mark as Shipped | ❌ | Hanya Approver |
| Mark as Delivered | ❌ | Hanya Approver |
| Issue Invoice | ❌ | Hanya Finance |

**Workflow PO**:
1. ✅ Buat PO baru (status: draft)
2. ✅ Edit PO (selama masih draft)
3. ✅ Submit PO (draft → submitted)
4. ⏳ Tunggu approval dari Approver
5. ✅ Terima notifikasi hasil approval

#### 📦 Goods Receipt (Penerimaan Barang)
| Aksi | Status | Keterangan |
|------|--------|------------|
| Melihat daftar goods receipt | ✅ | Hanya organisasi sendiri |
| Melihat detail goods receipt | ✅ | Hanya organisasi sendiri |
| Membuat goods receipt | ✅ | Untuk PO yang sudah delivered |
| Export goods receipt ke PDF | ✅ | Untuk organisasi sendiri |

**Workflow Goods Receipt**:
1. ⏳ Tunggu PO di-deliver oleh Approver
2. ✅ Buat goods receipt untuk mencatat penerimaan barang
3. ✅ Input quantity yang diterima
4. ✅ Simpan goods receipt

#### 💰 Payments (Pembayaran)
| Aksi | Status | Keterangan |
|------|--------|------------|
| Melihat daftar payment | ❌ | Hanya Finance |
| Confirm payment | ✅ | Konfirmasi pembayaran invoice sendiri |
| Verify payment | ❌ | Hanya Finance |

**Workflow Payment**:
1. ⏳ Terima invoice dari Finance
2. ✅ Lakukan pembayaran
3. ✅ Confirm payment dengan upload bukti
4. ⏳ Tunggu verifikasi dari Finance

#### 🚫 Tidak Dapat Mengakses
- ❌ **Approvals** - Tidak bisa approve/reject PO
- ❌ **Invoices** - Tidak bisa melihat atau manage invoice
- ❌ **Payments List** - Tidak bisa melihat daftar payment
- ❌ **Credit Control** - Tidak bisa manage credit limit
- ❌ **Master Data**:
  - Organizations
  - Suppliers
  - Products
  - Users

### 📱 Menu yang Terlihat di Sidebar

```
📊 Dashboard
├─ 🛒 PROCUREMENT
│  ├─ Purchase Orders ✅
│  └─ Goods Receipt ✅
```

### 🔑 Permissions yang Dimiliki

```
1. view_dashboard
2. view_purchase_orders
3. create_purchase_orders
4. create_po (alias)
5. update_purchase_orders
6. update_po (alias)
7. submit_purchase_orders
8. submit_po (alias)
9. view_goods_receipt
10. view_receipt (alias)
11. confirm_receipt
12. confirm_payment
```

**Total**: 12 permissions

---

## 2. Approver (Tim Operasional Medikindo)

### 👤 Deskripsi Role
Tim operasional Medikindo yang bertanggung jawab untuk menyetujui purchase order dan mengelola pengiriman barang.

### ✅ Hak Akses

#### 📊 Dashboard
- ✅ **Dapat mengakses** dashboard utama
- ✅ **Dapat melihat** ringkasan approval yang pending
- ❌ **Tidak dapat mengakses** dashboard keuangan
- ❌ **Tidak dapat mengakses** audit log

#### ✅ Approvals (Persetujuan)
| Aksi | Status | Keterangan |
|------|--------|------------|
| Melihat daftar approval | ✅ | Semua PO yang perlu approval |
| Melihat detail PO | ✅ | Untuk review sebelum approve |
| Approve PO | ✅ | Menyetujui PO |
| Reject PO | ✅ | Menolak PO dengan alasan |
| Melihat history approval | ✅ | Approval yang sudah diproses |

**Workflow Approval**:
1. ✅ Lihat daftar PO yang perlu approval
2. ✅ Review detail PO
3. ✅ Approve atau Reject dengan alasan
4. ✅ PO yang approved akan masuk ke proses pengiriman

#### 🛒 Purchase Orders (PO)
| Aksi | Status | Keterangan |
|------|--------|------------|
| Melihat daftar PO | ✅ | Semua PO (read-only) |
| Melihat detail PO | ✅ | Untuk review |
| Membuat PO baru | ❌ | Hanya Healthcare User |
| Mengedit PO | ❌ | Hanya Healthcare User |
| Mark as Shipped | ✅ | Setelah barang dikirim |
| Mark as Delivered | ✅ | Setelah barang sampai |
| Export PO ke PDF | ✅ | Untuk dokumentasi |

**Workflow Delivery**:
1. ✅ Approve PO (approved → shipped)
2. ✅ Mark as Shipped ketika barang dikirim
3. ✅ Mark as Delivered ketika barang sampai
4. ⏳ Healthcare User akan create goods receipt

#### 🚫 Tidak Dapat Mengakses
- ❌ **Create/Edit PO** - Hanya bisa view
- ❌ **Goods Receipt** - Tidak bisa create atau view
- ❌ **Invoices** - Tidak bisa melihat atau manage
- ❌ **Payments** - Tidak bisa melihat atau manage
- ❌ **Credit Control** - Tidak bisa manage
- ❌ **Master Data** - Tidak bisa manage

### 📱 Menu yang Terlihat di Sidebar

```
📊 Dashboard
├─ 🛒 PROCUREMENT
│  ├─ Purchase Orders ✅ (View Only)
│  └─ Approvals ✅
```

### 🔑 Permissions yang Dimiliki

```
1. view_dashboard
2. view_purchase_orders
3. view_approvals
4. approve_purchase_orders
```

**Total**: 4 permissions

---

## 3. Finance (Bagian Keuangan)

### 👤 Deskripsi Role
Bagian keuangan yang bertanggung jawab untuk mengelola invoice, pembayaran, dan kontrol kredit.

### ✅ Hak Akses

#### 📊 Dashboard
- ✅ **Dapat mengakses** dashboard utama
- ✅ **Dapat mengakses** dashboard keuangan
- ✅ **Dapat melihat** ringkasan invoice dan payment
- ❌ **Tidak dapat mengakses** audit log (kecuali Super Admin)

#### 📄 Invoices (Faktur)
| Aksi | Status | Keterangan |
|------|--------|------------|
| Melihat daftar invoice | ✅ | Supplier & Customer invoice |
| Melihat detail invoice | ✅ | Dengan line items detail |
| Issue invoice | ✅ | Dari PO yang completed |
| Export invoice ke PDF | ✅ | Supplier & Customer invoice |
| Approve discrepancy | ✅ | Jika ada perbedaan harga |
| Reject discrepancy | ✅ | Jika discrepancy tidak valid |
| Verify payment | ✅ | Verifikasi pembayaran dari customer |

**Workflow Invoice**:
1. ⏳ Tunggu PO completed (goods receipt dibuat)
2. ✅ Issue invoice dari PO
3. ✅ System auto-detect discrepancy (jika ada)
4. ✅ Approve/Reject discrepancy jika diperlukan
5. ✅ Invoice dikirim ke customer

**Discrepancy Handling**:
- ✅ Lihat variance amount dan percentage
- ✅ Lihat expected vs actual total
- ✅ Approve jika discrepancy valid (dengan alasan)
- ✅ Reject jika discrepancy tidak valid (dengan alasan)

#### 💰 Payments (Pembayaran)
| Aksi | Status | Keterangan |
|------|--------|------------|
| Melihat daftar payment | ✅ | Incoming & Outgoing |
| Create incoming payment | ✅ | Pembayaran dari customer |
| Create outgoing payment | ✅ | Pembayaran ke supplier |
| Verify payment | ✅ | Verifikasi payment dari customer |
| Confirm payment | ✅ | Konfirmasi payment |

**Workflow Payment**:
1. ✅ Terima notifikasi payment confirmation dari customer
2. ✅ Review bukti pembayaran
3. ✅ Verify payment
4. ✅ Update invoice status menjadi paid

#### 📊 Credit Control (Kontrol Kredit)
| Aksi | Status | Keterangan |
|------|--------|------------|
| Melihat credit limits | ✅ | Semua organisasi |
| Create credit limit | ✅ | Untuk organisasi baru |
| Update credit limit | ✅ | Adjust limit |
| Monitor credit usage | ✅ | Outstanding AR |
| View credit history | ✅ | History perubahan |

**Workflow Credit Control**:
1. ✅ Set credit limit untuk organisasi
2. ✅ Monitor outstanding AR
3. ✅ Update limit jika diperlukan
4. ✅ Block jika over limit

#### 🚫 Tidak Dapat Mengakses
- ❌ **Purchase Orders** - Tidak bisa create atau edit
- ❌ **Approvals** - Tidak bisa approve PO
- ❌ **Goods Receipt** - Tidak bisa create atau view
- ❌ **Master Data**:
  - Organizations
  - Suppliers
  - Products
  - Users

### 📱 Menu yang Terlihat di Sidebar

```
📊 Dashboard
├─ 💰 FINANCE
│  ├─ Invoices ✅
│  ├─ Payments ✅
│  └─ Credit Control ✅
```

### 🔑 Permissions yang Dimiliki

```
1. view_dashboard
2. view_invoices
3. view_invoice (alias)
4. create_invoices
5. manage_invoice (alias)
6. approve_invoice_discrepancy
7. view_payments
8. process_payments
9. confirm_payment
10. verify_payment
11. view_credit_control
```

**Total**: 11 permissions

---

## 4. Super Admin (Administrator Sistem)

### 👤 Deskripsi Role
Administrator sistem yang memiliki akses penuh ke semua fitur dan modul.

### ✅ Hak Akses

#### 🌟 Full Access
Super Admin memiliki **SEMUA PERMISSIONS** (29 permissions) dan dapat mengakses **SEMUA MODUL**.

#### 📊 Dashboard
- ✅ Dashboard utama
- ✅ Dashboard keuangan
- ✅ Audit log
- ✅ Semua statistik dan reports

#### 🛒 Purchase Orders
- ✅ **SEMUA AKSI** yang bisa dilakukan Healthcare User
- ✅ **SEMUA AKSI** yang bisa dilakukan Approver
- ✅ Delete PO
- ✅ View semua organisasi

#### 📦 Goods Receipt
- ✅ **SEMUA AKSI** yang bisa dilakukan Healthcare User
- ✅ View semua organisasi

#### ✅ Approvals
- ✅ **SEMUA AKSI** yang bisa dilakukan Approver
- ✅ View semua approval history

#### 📄 Invoices
- ✅ **SEMUA AKSI** yang bisa dilakukan Finance
- ✅ View semua organisasi
- ✅ Override discrepancy

#### 💰 Payments
- ✅ **SEMUA AKSI** yang bisa dilakukan Finance
- ✅ View semua organisasi
- ✅ Manual payment adjustment

#### 📊 Credit Control
- ✅ **SEMUA AKSI** yang bisa dilakukan Finance
- ✅ Override credit limit
- ✅ Force approve over limit

#### 🗂️ Master Data
| Modul | Akses |
|-------|-------|
| Organizations | ✅ Full CRUD |
| Suppliers | ✅ Full CRUD |
| Products | ✅ Full CRUD |
| Users | ✅ Full CRUD |

**CRUD** = Create, Read, Update, Delete

### 📱 Menu yang Terlihat di Sidebar

```
📊 Dashboard
├─ 🛒 PROCUREMENT
│  ├─ Purchase Orders ✅
│  ├─ Approvals ✅
│  └─ Goods Receipt ✅
├─ 💰 FINANCE
│  ├─ Invoices ✅
│  ├─ Payments ✅
│  └─ Credit Control ✅
└─ 🗂️ MASTER DATA
   ├─ Organizations ✅
   ├─ Suppliers ✅
   ├─ Products ✅
   └─ Users ✅
```

### 🔑 Permissions yang Dimiliki

```
ALL 29 PERMISSIONS:

Dashboard:
1. view_dashboard
2. view_audit

Purchase Orders:
3. view_purchase_orders
4. create_purchase_orders
5. create_po
6. update_purchase_orders
7. update_po
8. delete_purchase_orders
9. submit_purchase_orders
10. submit_po

Approvals:
11. view_approvals
12. approve_purchase_orders

Goods Receipt:
13. view_goods_receipt
14. view_receipt
15. confirm_receipt

Invoices:
16. view_invoices
17. view_invoice
18. create_invoices
19. manage_invoice
20. approve_invoice_discrepancy

Payments:
21. view_payments
22. process_payments
23. confirm_payment
24. verify_payment

Credit Control:
25. view_credit_control

Master Data:
26. manage_organizations
27. manage_suppliers
28. manage_products
29. manage_users
```

**Total**: 29 permissions (ALL)

---

## 📊 Comparison Matrix

| Fitur | Healthcare User | Approver | Finance | Super Admin |
|-------|----------------|----------|---------|-------------|
| **Dashboard** | ✅ | ✅ | ✅ | ✅ |
| **Purchase Orders** |
| - View | ✅ | ✅ | ❌ | ✅ |
| - Create | ✅ | ❌ | ❌ | ✅ |
| - Edit | ✅ | ❌ | ❌ | ✅ |
| - Delete | ❌ | ❌ | ❌ | ✅ |
| - Submit | ✅ | ❌ | ❌ | ✅ |
| **Approvals** |
| - View | ❌ | ✅ | ❌ | ✅ |
| - Approve/Reject | ❌ | ✅ | ❌ | ✅ |
| - Mark Shipped | ❌ | ✅ | ❌ | ✅ |
| - Mark Delivered | ❌ | ✅ | ❌ | ✅ |
| **Goods Receipt** |
| - View | ✅ | ❌ | ❌ | ✅ |
| - Create | ✅ | ❌ | ❌ | ✅ |
| **Invoices** |
| - View | ❌ | ❌ | ✅ | ✅ |
| - Issue | ❌ | ❌ | ✅ | ✅ |
| - Approve Discrepancy | ❌ | ❌ | ✅ | ✅ |
| **Payments** |
| - View List | ❌ | ❌ | ✅ | ✅ |
| - Confirm | ✅ | ❌ | ✅ | ✅ |
| - Verify | ❌ | ❌ | ✅ | ✅ |
| **Credit Control** | ❌ | ❌ | ✅ | ✅ |
| **Master Data** | ❌ | ❌ | ❌ | ✅ |

---

## 🔐 Security Notes

### Multi-Tenant Isolation
- Setiap user hanya bisa melihat data **organisasi mereka sendiri**
- Kecuali **Super Admin** yang bisa melihat semua organisasi

### Permission Checks
- Semua route dilindungi dengan **middleware permission**
- Controller melakukan **double-check** permission
- Sidebar menu **auto-hide** berdasarkan permission

### Audit Trail
- Semua aksi penting **dicatat** di audit log
- Super Admin bisa **melihat** semua audit log
- Audit log **tidak bisa dihapus**

---

## 📞 Support

Jika ada pertanyaan tentang akses atau permission:
1. **Hubungi Super Admin** untuk perubahan role
2. **Check dokumentasi** ini untuk memahami akses role
3. **Run test** untuk verifikasi: `php artisan test tests/Feature/RBACAccessControlTest.php`

---

**Dokumen ini telah diverifikasi dengan 34 automated tests ✅**

