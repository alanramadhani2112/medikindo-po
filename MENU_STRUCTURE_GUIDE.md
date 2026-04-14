# PANDUAN STRUKTUR MENU MEDIKINDO PO

**Last Updated**: April 14, 2026  
**Purpose**: Memudahkan user memahami menu dan business flow

---

## 📱 STRUKTUR MENU BARU (Sudah Disusun Ulang)

```
┌─────────────────────────────────────┐
│ 🏥 Medikindo                        │
├─────────────────────────────────────┤
│ 📊 Dashboard                        │
├─────────────────────────────────────┤
│ PROCUREMENT                         │
│ ├─ 🛒 Purchase Orders               │
│ ├─ ✅ Approvals                     │
│ └─ 📦 Goods Receipt                 │
├─────────────────────────────────────┤
│ INVOICING                           │
│ ├─ ⬆️ Tagihan ke RS/Klinik [AR]    │
│ └─ ⬇️ Hutang ke Supplier [AP]      │
├─────────────────────────────────────┤
│ PAYMENT                             │
│ ├─ 💰 Payments                      │
│ └─ 📈 Credit Control                │
├─────────────────────────────────────┤
│ MASTER DATA                         │
│ ├─ 🏦 Organizations                 │
│ ├─ 🚚 Suppliers                     │
│ ├─ 💊 Products                      │
│ └─ 👤 Users                         │
└─────────────────────────────────────┘
```

---

## 🔄 BUSINESS FLOW & MENU MAPPING

### Flow 1: Healthcare User (RS/Klinik)
```
1. 🛒 Purchase Orders
   └─ Buat PO untuk order barang
   
2. ⏳ Menunggu Approval
   └─ PO di-review oleh Approver
   
3. 📦 Goods Receipt
   └─ Terima barang, input batch & expiry
   
4. ⬆️ Tagihan ke RS/Klinik
   └─ Lihat invoice yang diterbitkan Medikindo
   
5. 💰 Payments
   └─ Konfirmasi pembayaran ke Medikindo
```

### Flow 2: Approver
```
1. ✅ Approvals
   └─ Review dan approve/reject PO dari RS/Klinik
```

### Flow 3: Finance / Admin Pusat
```
1. 📦 Goods Receipt
   └─ Cek GR yang sudah completed
   
2. ⬆️ Tagihan ke RS/Klinik [AR]
   └─ Terbitkan invoice ke RS/Klinik
   └─ Tombol: "Buat Tagihan ke RS/Klinik"
   
3. 💰 Payments
   └─ Catat Payment IN dari RS/Klinik
   
4. ⬇️ Hutang ke Supplier [AP]
   └─ Terbitkan invoice ke Supplier
   └─ Tombol: "Buat Invoice Pemasok"
   
5. 💰 Payments
   └─ Bayar Supplier (Payment OUT)
   └─ Validasi: Payment IN harus >= Payment OUT
```

---

## 📋 PENJELASAN SETIAP MENU

### 1. 📊 DASHBOARD
**Akses**: Semua role  
**Fungsi**: Overview sistem, statistik, quick actions

---

### 2. 🛒 PURCHASE ORDERS
**Akses**: Healthcare User, Admin Pusat, Approver (view), Finance (view), Super Admin  
**Fungsi**: 
- Healthcare User: Buat PO untuk order barang
- Admin Pusat: Buat dan manage PO
- Approver: View PO untuk approval context
- Finance: View PO untuk invoice context

**Tombol yang Muncul**:
- Healthcare User: "Buat PO", "Edit" (draft), "Submit"
- Admin Pusat: "Buat PO", "Edit" (draft), "Submit", "Delete"
- Approver: Tidak ada tombol create/edit
- Finance: Tidak ada tombol create/edit

---

### 3. ✅ APPROVALS
**Akses**: Approver, Admin Pusat, Super Admin  
**Fungsi**: Review dan approve/reject PO

**Tombol yang Muncul**:
- "Approve" (hijau)
- "Reject" (merah)
- Badge: Jumlah pending approvals

**Business Rule**:
- ❌ Tidak bisa approve PO yang dibuat sendiri
- ✅ Harus kasih notes saat reject

---

### 4. 📦 GOODS RECEIPT
**Akses**: Healthcare User, Admin Pusat, Finance (view), Super Admin  
**Fungsi**: 
- Healthcare User: Catat penerimaan barang saat barang tiba
- Admin Pusat: Catat penerimaan barang
- Finance: View GR untuk buat invoice

**Tombol yang Muncul**:
- Healthcare User: "Buat GR"
- Admin Pusat: "Buat GR"
- Finance: Tidak ada tombol (view only)

**Business Rule**:
- ✅ Hanya bisa buat GR dari PO yang sudah approved
- ✅ Wajib input batch number & expiry date
- ❌ Batch & expiry tidak bisa diubah setelah disimpan

---

### 5. ⬆️ TAGIHAN KE RS/KLINIK [AR]
**Akses**: Finance, Admin Pusat, Healthcare User (view), Super Admin  
**Fungsi**: 
- Finance/Admin Pusat: Terbitkan invoice ke RS/Klinik
- Healthcare User: Lihat invoice yang ditujukan ke mereka

**Tombol yang Muncul**:
- Finance/Admin Pusat: **"Buat Tagihan ke RS/Klinik"** (hijau)
- Healthcare User: Tidak ada tombol (view only)

**Business Rule**:
- ✅ Invoice HANYA bisa dibuat dari GR yang completed
- ✅ Batch & expiry otomatis dari GR (read-only)
- ✅ Quantity tidak boleh melebihi GR

**Cara Pakai**:
1. Klik menu "Tagihan ke RS/Klinik"
2. Klik tombol "Buat Tagihan ke RS/Klinik"
3. Pilih GR yang sudah completed
4. Pilih items yang mau di-invoice
5. Set due date (default 30 hari)
6. Submit → Invoice terbit: **INV-CUST-XXXXX**

---

### 6. ⬇️ HUTANG KE SUPPLIER [AP]
**Akses**: Finance, Admin Pusat, Healthcare User (view), Super Admin  
**Fungsi**: 
- Finance/Admin Pusat: Terbitkan invoice ke Supplier
- Healthcare User: View only

**Tombol yang Muncul**:
- Finance/Admin Pusat: **"Buat Invoice Pemasok"** (biru)
- Healthcare User: Tidak ada tombol (view only)

**Business Rule**:
- ✅ Invoice HANYA bisa dibuat dari GR yang completed
- ✅ Batch & expiry otomatis dari GR (read-only)
- ✅ Quantity tidak boleh melebihi GR

**Cara Pakai**:
1. Klik menu "Hutang ke Supplier"
2. Klik tombol "Buat Invoice Pemasok"
3. Pilih GR yang sudah completed
4. Pilih items yang mau di-invoice
5. Set due date (default 30 hari)
6. Submit → Invoice terbit: **INV-SUP-XXXXX**

---

### 7. 💰 PAYMENTS
**Akses**: Finance, Admin Pusat, Healthcare User (confirm only), Super Admin  
**Fungsi**: 
- Finance/Admin Pusat: Process Payment IN & OUT
- Healthcare User: Konfirmasi pembayaran mereka

**Tombol yang Muncul**:
- Finance/Admin Pusat: "Process Payment IN", "Process Payment OUT"
- Healthcare User: "Confirm Payment"

**Business Rule**:
- ✅ Payment OUT hanya bisa jika Payment IN sudah cukup
- ✅ Validasi: Total Payment IN >= Total Payment OUT
- ❌ Tidak bisa bayar supplier jika RS belum bayar

**Cara Pakai Payment OUT**:
1. Pastikan RS sudah bayar (Payment IN)
2. Klik "Process Payment OUT"
3. Pilih Supplier Invoice
4. Input amount
5. Submit → Sistem validasi Payment IN dulu

---

### 8. 📈 CREDIT CONTROL
**Akses**: Finance, Admin Pusat, Super Admin  
**Fungsi**: Monitor credit limit dan usage per organization

---

### 9. 🏦 ORGANIZATIONS
**Akses**: Super Admin only  
**Fungsi**: Manage RS/Klinik (nama, alamat, credit limit)

---

### 10. 🚚 SUPPLIERS
**Akses**: Super Admin only  
**Fungsi**: Manage data supplier/distributor

---

### 11. 💊 PRODUCTS
**Akses**: Super Admin only  
**Fungsi**: Manage data produk (obat, alkes)

---

### 12. 👤 USERS
**Akses**: Super Admin only  
**Fungsi**: Manage users dan assign roles

---

## 🎯 KENAPA URUTAN MENU SEPERTI INI?

### Urutan Mengikuti Business Flow:

1. **PROCUREMENT** (Order barang)
   - Purchase Orders → Approvals → Goods Receipt
   
2. **INVOICING** (Terbitkan tagihan)
   - **Tagihan ke RS/Klinik DULU** (ini yang diterbitkan pertama)
   - **Hutang ke Supplier KEDUA** (ini dibuat setelah terima payment dari RS)
   
3. **PAYMENT** (Kelola pembayaran)
   - Payments → Credit Control

4. **MASTER DATA** (Setup awal)
   - Organizations → Suppliers → Products → Users

### Mengapa "Tagihan ke RS/Klinik" di Atas?

**Alasan**:
1. ✅ Ini yang **diterbitkan PERTAMA** setelah GR
2. ✅ Medikindo harus **terima uang dari RS dulu**
3. ✅ Baru setelah itu bayar supplier
4. ✅ Urutan menu = urutan cashflow

**Flow yang Benar**:
```
GR → Invoice ke RS → Payment IN → Invoice ke Supplier → Payment OUT
     ⬆️ FIRST           ⬆️ FIRST      ⬇️ SECOND           ⬇️ SECOND
```

---

## 🔍 TIPS NAVIGASI PER ROLE

### Healthcare User (RS/Klinik)
**Menu yang Sering Dipakai**:
1. Purchase Orders - Buat PO
2. Goods Receipt - Terima barang
3. Tagihan ke RS/Klinik - Lihat invoice & bayar

**Ignore Menu**:
- Approvals (tidak ada akses)
- Hutang ke Supplier (view only, tidak relevan)
- Master Data (tidak ada akses)

---

### Approver
**Menu yang Sering Dipakai**:
1. Approvals - Approve/reject PO
2. Purchase Orders - Lihat detail PO

**Ignore Menu**:
- Semua menu lain (tidak ada akses)

---

### Finance
**Menu yang Sering Dipakai**:
1. Goods Receipt - Cek GR yang siap di-invoice
2. **Tagihan ke RS/Klinik** - Terbitkan invoice ke RS
3. Payments - Catat Payment IN dari RS
4. **Hutang ke Supplier** - Terbitkan invoice ke supplier
5. Payments - Bayar supplier (Payment OUT)

**Urutan Kerja**:
```
1. Cek GR completed
2. Buat Tagihan ke RS/Klinik
3. Tunggu RS bayar → Catat Payment IN
4. Buat Invoice ke Supplier
5. Bayar Supplier → Process Payment OUT
```

---

### Admin Pusat
**Menu yang Sering Dipakai**:
- Semua menu operational (kecuali Master Data)
- Bisa handle semua proses dari PO sampai Payment

---

### Super Admin
**Menu yang Sering Dipakai**:
- Master Data (Organizations, Suppliers, Products, Users)
- Semua menu lain jika diperlukan

---

## ❓ FAQ

### Q: Kenapa "Tagihan ke RS/Klinik" ada badge [AR]?
**A**: AR = Accounts Receivable (Piutang). Ini invoice yang Medikindo terbitkan ke RS/Klinik untuk **menagih** pembayaran.

### Q: Kenapa "Hutang ke Supplier" ada badge [AP]?
**A**: AP = Accounts Payable (Hutang). Ini invoice dari Supplier yang harus **dibayar** oleh Medikindo.

### Q: Kenapa tidak ada menu "Invoices" saja?
**A**: Dipisah agar lebih jelas:
- **Tagihan ke RS/Klinik** = Kita yang nagih (AR)
- **Hutang ke Supplier** = Kita yang bayar (AP)

### Q: Kenapa "Tagihan ke RS/Klinik" di atas "Hutang ke Supplier"?
**A**: Karena flow-nya:
1. Terbitkan invoice ke RS dulu
2. Terima payment dari RS
3. Baru terbitkan invoice ke supplier
4. Baru bayar supplier

### Q: Saya Finance, menu apa yang paling penting?
**A**: 
1. **Tagihan ke RS/Klinik** - Terbitkan invoice ke RS (ini FIRST!)
2. **Payments** - Catat payment IN dari RS
3. **Hutang ke Supplier** - Terbitkan invoice ke supplier (SECOND!)
4. **Payments** - Bayar supplier (payment OUT)

---

## 📞 BANTUAN

Jika masih bingung:
1. Lihat badge di menu (AR/AP)
2. Ikuti urutan menu dari atas ke bawah
3. Urutan menu = urutan business flow
4. Tanya Super Admin untuk training

---

**Dokumentasi ini akan diupdate jika ada perubahan menu atau business flow.**

