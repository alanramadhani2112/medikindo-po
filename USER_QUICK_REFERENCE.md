# 📖 PANDUAN CEPAT PENGGUNA - MEDIKINDO PO SYSTEM v2.0

**Tanggal**: 14 April 2026  
**Versi**: 2.0  
**Untuk**: Semua Pengguna Sistem

---

## 🎯 APA YANG BERUBAH?

### ✅ Perubahan Penting:

1. **Keamanan Pembayaran Ditingkatkan**
   - Sistem sekarang memvalidasi pembayaran
   - Tidak bisa bayar supplier sebelum terima uang dari RS/Klinik

2. **Invoice Wajib dari Goods Receipt**
   - Invoice hanya bisa dibuat dari Goods Receipt
   - Batch number dan expiry date otomatis dari GR (tidak bisa diubah)

3. **Workflow Lebih Sederhana**
   - Tidak ada lagi status "shipped" dan "delivered"
   - Workflow: Draft → Submitted → Approved → Completed

---

## 📋 ALUR KERJA LENGKAP

### 1️⃣ BUAT PURCHASE ORDER (PO)

**Siapa**: User dengan permission `create_purchase_orders`

**Langkah**:
1. Login ke sistem
2. Menu: **Purchase Orders** → **Buat PO**
3. Isi data PO:
   - Pilih supplier
   - Pilih produk
   - Isi quantity
   - Harga otomatis dari master produk
4. Klik **Simpan sebagai Draft**
5. Klik **Submit untuk Approval**

**Status PO**: Draft → Submitted

---

### 2️⃣ APPROVE PURCHASE ORDER

**Siapa**: Approver (Super Admin, Approver, Admin Approver)

**Langkah**:
1. Login ke sistem
2. Menu: **Approvals** → **Antrian Persetujuan**
3. Lihat daftar PO yang menunggu approval
4. Klik **Detail** pada PO yang ingin di-approve
5. Review data PO
6. Klik **Approve** atau **Reject**
7. Isi alasan (jika reject)

**Status PO**: Submitted → Approved (atau Rejected)

---

### 3️⃣ DELIVERY (DI LUAR SISTEM)

**Siapa**: Supplier

**Proses**:
- Supplier mengirim barang ke lokasi
- Proses ini **TIDAK DICATAT** di sistem
- Tidak ada status "shipped" atau "delivered"

**Status PO**: Tetap Approved

---

### 4️⃣ REKAM PENERIMAAN BARANG (GOODS RECEIPT)

**Siapa**: User dengan permission `view_goods_receipt`

**Langkah**:
1. Login ke sistem
2. Menu: **Penerimaan Barang** → **Rekam Penerimaan Barang**
3. Pilih PO yang sudah approved
4. Untuk setiap item:
   - **Batch Number**: Isi nomor batch dari kemasan
   - **Expiry Date**: Isi tanggal kadaluarsa dari kemasan
   - **Quantity Received**: Isi jumlah yang diterima
     - Jika lengkap: isi sesuai PO
     - Jika tidak lengkap: isi sesuai yang diterima
5. Klik **Konfirmasi Penerimaan**

**Status GR**: 
- **Completed**: Jika semua item diterima lengkap
- **Partial**: Jika ada item yang tidak lengkap

**Status PO**: Approved → Completed

---

### 5️⃣ BUAT INVOICE PEMASOK

**Siapa**: User dengan permission `create_invoices`

**Langkah**:
1. Login ke sistem
2. Menu: **Invoice Pemasok** → **Buat Invoice**
3. Pilih Goods Receipt yang sudah completed
4. Data otomatis muncul:
   - ✅ Batch number (dari GR, tidak bisa diubah)
   - ✅ Expiry date (dari GR, tidak bisa diubah)
   - ✅ Harga (dari PO, tidak bisa diubah)
   - ✏️ Quantity (bisa diubah, max = sisa GR)
5. Isi data tambahan:
   - Nomor invoice supplier
   - Tanggal jatuh tempo
   - Catatan (opsional)
6. Klik **Buat Invoice**

**Status Invoice**: Issued

**Catatan Penting**:
- ⚠️ Batch dan expiry **TIDAK BISA DIUBAH** (otomatis dari GR)
- ⚠️ Harga **TIDAK BISA DIUBAH** (otomatis dari PO)
- ✅ Bisa buat invoice sebagian (partial invoicing)
- ✅ Bisa buat beberapa invoice dari 1 GR

---

### 6️⃣ TERIMA PEMBAYARAN DARI RS/KLINIK (PAYMENT IN)

**Siapa**: Finance dengan permission `create_payments`

**Langkah**:
1. Login ke sistem
2. Menu: **Piutang & Tagihan Klien**
3. Klik invoice yang sudah dibayar RS
4. Klik **Rekam Pembayaran**
5. Isi data pembayaran:
   - Jumlah pembayaran
   - Metode pembayaran
   - Referensi pembayaran
   - Tanggal pembayaran
6. Klik **Simpan**

**Status Invoice**: Partial atau Paid

---

### 7️⃣ BAYAR SUPPLIER (PAYMENT OUT)

**Siapa**: Finance dengan permission `create_payments`

**Langkah**:
1. Login ke sistem
2. Menu: **Hutang Pemasok**
3. Klik invoice supplier yang akan dibayar
4. Klik **Bayar Pemasok**
5. Isi data pembayaran:
   - Jumlah pembayaran
   - Metode pembayaran
   - Referensi pembayaran
   - Tanggal pembayaran
6. Klik **Simpan**

**VALIDASI OTOMATIS**:
- ✅ Sistem cek: Apakah RS sudah bayar?
- ✅ Sistem cek: Apakah pembayaran IN ≥ pembayaran OUT?
- ❌ Jika belum cukup: **DITOLAK OTOMATIS**

**Status Invoice**: Partial atau Paid

---

## ⚠️ PESAN ERROR UMUM

### Error 1: "Pembayaran ke supplier tidak dapat dilakukan"
**Penyebab**: RS/Klinik belum membayar atau pembayaran tidak cukup

**Solusi**:
1. Cek invoice customer (piutang RS)
2. Pastikan RS sudah bayar
3. Pastikan jumlah pembayaran IN ≥ pembayaran OUT yang akan dilakukan

---

### Error 2: "Goods Receipt must be 'completed'"
**Penyebab**: GR masih berstatus "partial"

**Solusi**:
1. Tunggu sampai GR completed
2. Atau buat GR baru untuk item yang sudah lengkap

---

### Error 3: "Quantity exceeds remaining quantity"
**Penyebab**: Quantity invoice melebihi sisa quantity di GR

**Solusi**:
1. Cek sisa quantity di GR
2. Kurangi quantity invoice
3. Atau buat invoice terpisah

---

### Error 4: "Goods Receipt tidak ditemukan"
**Penyebab**: GR sudah fully invoiced atau tidak ada GR completed

**Solusi**:
1. Cek apakah GR sudah fully invoiced
2. Cek apakah ada GR dengan status completed
3. Buat GR baru jika perlu

---

## 🔒 KEAMANAN & VALIDASI

### ✅ Yang Dilindungi Sistem:

1. **Batch & Expiry**
   - Tidak bisa diubah di invoice
   - Otomatis dari GR
   - Menjamin traceability

2. **Harga**
   - Tidak bisa diubah di invoice
   - Otomatis dari PO
   - Mencegah manipulasi harga

3. **Pembayaran**
   - Tidak bisa bayar supplier sebelum terima uang dari RS
   - Validasi otomatis
   - Mencegah kerugian finansial

4. **Quantity**
   - Tidak bisa melebihi sisa GR
   - Validasi otomatis
   - Mencegah over-invoicing

---

## 📊 STATUS & ARTINYA

### Status Purchase Order:
- **Draft**: PO baru dibuat, belum disubmit
- **Submitted**: PO menunggu approval
- **Approved**: PO sudah disetujui, menunggu barang datang
- **Completed**: Barang sudah diterima (via GR)
- **Cancelled**: PO dibatalkan
- **Rejected**: PO ditolak approver

### Status Goods Receipt:
- **Partial**: Barang diterima tidak lengkap
- **Completed**: Barang diterima lengkap

### Status Invoice:
- **Issued**: Invoice sudah dibuat, belum dibayar
- **Partial**: Invoice sudah dibayar sebagian
- **Paid**: Invoice sudah lunas
- **Overdue**: Invoice lewat jatuh tempo, belum lunas

### Status Payment:
- **Pending**: Pembayaran menunggu verifikasi
- **Completed**: Pembayaran selesai
- **Failed**: Pembayaran gagal

---

## 💡 TIPS & BEST PRACTICES

### Tip 1: Partial Invoicing
Jika barang datang bertahap, buat invoice bertahap:
1. GR pertama → Invoice pertama
2. GR kedua → Invoice kedua
3. Dan seterusnya

### Tip 2: Cek Sisa Quantity
Sebelum buat invoice, cek sisa quantity di GR:
- Lihat di detail GR
- Kolom "Remaining Quantity"

### Tip 3: Batch & Expiry
Pastikan input batch dan expiry dengan benar di GR:
- Batch: Sesuai kemasan
- Expiry: Format YYYY-MM-DD
- Data ini akan muncul di invoice (tidak bisa diubah)

### Tip 4: Payment Sequence
Urutan pembayaran yang benar:
1. Buat invoice customer (piutang RS)
2. Terima pembayaran dari RS (payment IN)
3. Baru bayar supplier (payment OUT)

### Tip 5: Approval Queue
Approver: Cek antrian approval secara berkala
- Menu: Approvals → Antrian Persetujuan
- Notifikasi email juga dikirim

---

## 🆘 BANTUAN & SUPPORT

### Jika Mengalami Masalah:

1. **Cek Panduan Ini**
   - Baca bagian error yang relevan
   - Ikuti solusi yang disarankan

2. **Hubungi Tim IT**
   - Email: it@medikindo.com
   - Telepon: [PHONE]
   - WhatsApp: [WHATSAPP]

3. **Informasi yang Perlu Disiapkan**:
   - Screenshot error
   - Langkah yang dilakukan
   - User yang login
   - Waktu kejadian

---

## 📚 REFERENSI TAMBAHAN

### Dokumen Teknis:
- `SYSTEM_AUDIT_REPORT.md` - Laporan audit sistem
- `CRITICAL_FIX_COMPLETE.md` - Detail perbaikan
- `EXECUTIVE_SUMMARY.md` - Ringkasan untuk manajemen

### Video Tutorial:
- [Coming Soon] Tutorial Buat PO
- [Coming Soon] Tutorial Goods Receipt
- [Coming Soon] Tutorial Invoice
- [Coming Soon] Tutorial Payment

---

## 🎯 RINGKASAN CEPAT

```
┌─────────────────────────────────────────────────────────────┐
│                    ALUR KERJA SINGKAT                        │
└─────────────────────────────────────────────────────────────┘

1. Buat PO → Submit → Approve
2. [Supplier kirim barang - di luar sistem]
3. Rekam GR (input batch, expiry, quantity)
4. Buat Invoice dari GR (batch/expiry otomatis)
5. Terima pembayaran dari RS (payment IN)
6. Bayar supplier (payment OUT) - validasi otomatis

✅ Batch & expiry: Otomatis dari GR
✅ Harga: Otomatis dari PO
✅ Payment OUT: Hanya jika payment IN cukup
```

---

**Versi**: 2.0  
**Terakhir Diperbarui**: 14 April 2026  
**Tim IT Medikindo**

---

**END OF USER GUIDE**
