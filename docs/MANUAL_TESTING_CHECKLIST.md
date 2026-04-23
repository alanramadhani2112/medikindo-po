# Manual Testing Checklist — Medikindo PO System

**Tanggal Testing:** ___________  
**Tester:** ___________  
**Environment:** http://medikindo-po.test

---

## Akun Testing

| Role | Email | Password | Organisasi |
|------|-------|----------|-----------|
| Super Admin | alanramadhani21@gmail.com | password123 | — (semua org) |
| Healthcare User | budi.santoso@testhospital.com | password | Test Hospital (org 9) |
| Approver | siti.nurhaliza@medikindo.com | password | — (Medikindo) |
| Finance | ahmad.hidayat@medikindo.com | password | — (Medikindo) |

---

## Data Master yang Tersedia

- **Supplier:** PT Kimia Farma (id:1), PT Kalbe Farma (id:2), PT Sanbe Farma (id:3)
- **Produk:** Paracetamol 500mg (cost: 3.500, jual: 5.000), Amoxicillin 500mg (cost: 10.500, jual: 15.000)
- **Organisasi:** RS Umum Medika Utama (id:1), Test Hospital (id:9)
- **Bank Default Terima:** Bank BCA (id:2)
- **Bank Default Kirim:** Bank BCA (id:2)

---

## FLOW 1 — Purchase Order

### 1.1 Buat PO (Healthcare User)
- [ ] Login sebagai **Dr. Budi Santoso** (Healthcare User)
- [ ] Buka `/purchase-orders/create`
- [ ] Pilih supplier: PT Kimia Farma
- [ ] Tambah item: Paracetamol 500mg, qty 10
- [ ] Tambah item: Amoxicillin 500mg, qty 5
- [ ] Klik **Simpan sebagai Draft**
- [ ] **Verifikasi:** PO muncul di daftar dengan status `Draft`
- [ ] **Verifikasi notif:** Creator menerima notifikasi "PO Draft Berhasil Dibuat"

### 1.2 Submit PO
- [ ] Buka PO yang baru dibuat
- [ ] Klik **Ajukan ke Medikindo**
- [ ] **Verifikasi:** Status berubah ke `Submitted`
- [ ] **Verifikasi notif:** Approver (Siti) menerima notifikasi "PO Baru Menunggu Persetujuan"
- [ ] **Verifikasi notif:** Creator (Budi) menerima notifikasi "PO Berhasil Diajukan"

### 1.3 Approve PO (Approver)
- [ ] Login sebagai **Siti Nurhaliza** (Approver)
- [ ] Buka `/approvals`
- [ ] Cari PO yang baru diajukan
- [ ] Klik **Setujui**
- [ ] **Verifikasi:** Status PO berubah ke `Approved`
- [ ] **Verifikasi notif:** Healthcare User (Budi) menerima notifikasi "PO Disetujui"

### 1.4 Reject PO (opsional — test alur penolakan)
- [ ] Buat PO baru, submit
- [ ] Login sebagai Approver, klik **Tolak** dengan alasan
- [ ] **Verifikasi:** Status PO berubah ke `Rejected`
- [ ] **Verifikasi notif:** Healthcare User menerima notifikasi "PO Ditolak"
- [ ] Login kembali sebagai Healthcare User, buka PO yang ditolak
- [ ] Klik **Buka Kembali & Revisi** → status kembali ke `Draft`

---

## FLOW 2 — Goods Receipt

### 2.1 Konfirmasi Penerimaan Barang (Healthcare User)
- [ ] Login sebagai **Dr. Budi Santoso**
- [ ] Buka `/goods-receipts/create`
- [ ] Pilih PO yang sudah `Approved`
- [ ] Isi Nomor Surat Jalan (DO): `DO-TEST-001`
- [ ] Upload foto surat jalan (file gambar apapun)
- [ ] Isi item:
  - Paracetamol: qty diterima = 10, batch = `BATCH-001`, expiry = 1 tahun ke depan
  - Amoxicillin: qty diterima = 5, batch = `BATCH-002`, expiry = 1 tahun ke depan
- [ ] Klik **Konfirmasi Penerimaan**
- [ ] **Verifikasi:** GR muncul dengan status `Completed`
- [ ] **Verifikasi:** Status PO berubah ke `Completed`
- [ ] **Verifikasi notif:** Finance (Ahmad) menerima notifikasi "Invoice Supplier Siap Diverifikasi"
- [ ] **Verifikasi notif:** Finance menerima notifikasi "Barang Telah Diterima Penuh"

### 2.2 Penerimaan Sebagian (opsional)
- [ ] Buat PO baru, approve
- [ ] Buat GR dengan qty sebagian (misal 5 dari 10)
- [ ] **Verifikasi:** Status PO berubah ke `Partially Received`
- [ ] **Verifikasi:** Status GR = `Partial`
- [ ] **Verifikasi notif:** Finance menerima "Pengiriman Sebagian"
- [ ] Buat GR kedua untuk sisa qty
- [ ] **Verifikasi:** Status PO berubah ke `Completed`

---

## FLOW 3 — Supplier Invoice (AP)

### 3.1 Cek Invoice Supplier Otomatis
- [ ] Login sebagai **Ahmad Hidayat** (Finance)
- [ ] Buka `/invoices/supplier`
- [ ] **Verifikasi:** Invoice AP muncul dengan status `Draft`
- [ ] **Verifikasi:** Total invoice = qty × cost_price (Paracetamol: 10×3.500 + Amoxicillin: 5×10.500 = 87.500)

### 3.2 Verifikasi Invoice Supplier
- [ ] Buka detail invoice AP
- [ ] Klik **Verifikasi & Buat Tagihan RS**
- [ ] **Verifikasi:** Status SI berubah ke `Verified`
- [ ] **Verifikasi:** Customer Invoice (AR) otomatis dibuat dengan status `Draft`
- [ ] **Verifikasi notif:** Healthcare User (Budi) menerima notifikasi "Invoice Tagihan Diterbitkan — Harap Segera Bayar"

---

## FLOW 4 — Customer Invoice (AR)

### 4.1 Cek Invoice Customer
- [ ] Login sebagai **Dr. Budi Santoso** (Healthcare User)
- [ ] Buka `/invoices/customer`
- [ ] **Verifikasi:** Invoice AR muncul (status `Draft` atau `Issued`)
- [ ] **Verifikasi:** Total invoice = qty × selling_price (Paracetamol: 10×5.000 + Amoxicillin: 5×15.000 = 125.000)
- [ ] **Verifikasi:** Total AR > Total AP (ada profit untuk Medikindo)

### 4.2 Terbitkan Invoice (Finance)
- [ ] Login sebagai **Ahmad Hidayat** (Finance)
- [ ] Buka `/invoices/customer`
- [ ] Buka detail invoice AR yang masih `Draft`
- [ ] Klik **Terbitkan Invoice**
- [ ] **Verifikasi:** Status berubah ke `Issued`
- [ ] **Verifikasi notif:** Healthcare User menerima notifikasi "Invoice Diterbitkan"

---

## FLOW 5 — Payment Proof (Bukti Pembayaran)

### 5.1 Submit Bukti Pembayaran (Healthcare User)
- [ ] Login sebagai **Dr. Budi Santoso**
- [ ] Buka `/payment-proofs/create`
- [ ] Pilih invoice yang `Issued`
- [ ] Pilih tipe: **Penuh (Pelunasan)**
- [ ] Isi tanggal bayar: hari ini
- [ ] Pilih metode: Bank Transfer
- [ ] Isi nama bank pengirim: BCA
- [ ] Isi nomor rekening pengirim: 1234567890
- [ ] Isi referensi transfer: `TRF-TEST-001`
- [ ] Upload dokumen bukti (file gambar/PDF)
- [ ] Klik **Submit Bukti Pembayaran**
- [ ] **Verifikasi:** Status bukti = `Submitted`
- [ ] **Verifikasi notif:** Finance (Ahmad) menerima notifikasi "Bukti Pembayaran Baru Menunggu Review"

### 5.2 Verifikasi Bukti (Finance)
- [ ] Login sebagai **Ahmad Hidayat** (Finance)
- [ ] Buka `/payment-proofs`
- [ ] Cari bukti yang baru disubmit
- [ ] Klik **Verifikasi**
- [ ] **Verifikasi:** Status berubah ke `Verified`

### 5.3 Approve Bukti (Finance)
- [ ] Buka bukti yang sudah `Verified`
- [ ] Centang semua checklist verifikasi
- [ ] Klik **Setujui & Terima Pembayaran**
- [ ] **Verifikasi:** Status bukti = `Approved`
- [ ] **Verifikasi:** Customer Invoice status = `Paid`
- [ ] **Verifikasi:** Supplier Invoice status = `Paid`
- [ ] **Verifikasi notif:** Healthcare User menerima "Bukti Pembayaran Disetujui"
- [ ] **Verifikasi notif:** Finance menerima "Payment IN & OUT Otomatis Dicatat"

### 5.4 Reject Bukti (opsional)
- [ ] Submit bukti baru
- [ ] Finance klik **Tolak** dengan alasan
- [ ] **Verifikasi:** Status = `Rejected`
- [ ] **Verifikasi notif:** Healthcare User menerima "Bukti Pembayaran Ditolak"
- [ ] Healthcare User klik **Ajukan Ulang** dengan dokumen baru
- [ ] **Verifikasi:** Status = `Resubmitted`

---

## FLOW 6 — Payment Ledger & Bank Account

### 6.1 Cek Payment Ledger
- [ ] Login sebagai **Ahmad Hidayat** (Finance)
- [ ] Buka `/payments`
- [ ] **Verifikasi:** Tab "Semua Transaksi" menampilkan data
- [ ] **Verifikasi:** Ada Payment IN (incoming) dari Healthcare
- [ ] **Verifikasi:** Ada Payment OUT (outgoing) ke Supplier
- [ ] **Verifikasi:** Jumlah Payment IN > Payment OUT (profit)

### 6.2 Cek Bank Account Balance
- [ ] Buka `/bank-accounts`
- [ ] **Verifikasi:** Kolom "Saldo Saat Ini" menampilkan nilai (bukan "Belum ada transaksi")
- [ ] **Verifikasi:** Uang Masuk = total Payment IN
- [ ] **Verifikasi:** Uang Keluar = total Payment OUT
- [ ] **Verifikasi:** Net Cashflow = Masuk - Keluar (positif = profit)

---

## FLOW 7 — Notifikasi

### 7.1 Cek Bell Notifikasi
- [ ] Login sebagai Healthcare User → cek notif di bell icon
  - [ ] PO Draft dibuat
  - [ ] PO disetujui/ditolak
  - [ ] Invoice diterbitkan (harus bayar)
  - [ ] Bukti bayar disetujui/ditolak
  - [ ] Payment IN dicatat
- [ ] Login sebagai Finance → cek notif di bell icon
  - [ ] PO diajukan (untuk disetujui)
  - [ ] GR diterima (invoice siap diverifikasi)
  - [ ] Bukti bayar diajukan
  - [ ] Payment IN & OUT dicatat
- [ ] Login sebagai Approver → cek notif di bell icon
  - [ ] PO diajukan (untuk disetujui)

---

## FLOW 8 — RBAC & Akses

### 8.1 Healthcare User tidak bisa akses halaman Finance
- [ ] Login sebagai Healthcare User
- [ ] Coba akses `/payments` → **Verifikasi:** 403 Forbidden
- [ ] Coba akses `/financial-controls` → **Verifikasi:** 403 Forbidden
- [ ] Coba akses `/organizations` → **Verifikasi:** 403 Forbidden

### 8.2 Finance tidak bisa akses master data
- [ ] Login sebagai Finance
- [ ] Coba akses `/organizations` → **Verifikasi:** 403 Forbidden
- [ ] Coba akses `/suppliers` → **Verifikasi:** 403 Forbidden

### 8.3 Multi-tenancy isolation
- [ ] Login sebagai Healthcare User org 9 (Test Hospital)
- [ ] Buka `/purchase-orders` → **Verifikasi:** Hanya PO milik Test Hospital yang muncul
- [ ] Buka `/invoices/customer` → **Verifikasi:** Hanya invoice Test Hospital yang muncul

### 8.4 Finance melihat semua data
- [ ] Login sebagai Finance
- [ ] Buka `/invoices/supplier` → **Verifikasi:** Semua invoice dari semua org muncul
- [ ] Buka `/invoices/customer` → **Verifikasi:** Semua invoice dari semua org muncul

---

## FLOW 9 — Credit Control

### 9.1 Cek Credit Limit
- [ ] Login sebagai Finance
- [ ] Buka `/financial-controls`
- [ ] **Verifikasi:** Semua organisasi memiliki credit limit
- [ ] **Verifikasi:** Utilisasi kredit berkurang setelah payment diterima

### 9.2 Blokir PO jika melebihi kredit (opsional)
- [ ] Set credit limit organisasi ke nilai kecil (misal Rp 1.000)
- [ ] Login sebagai Healthcare User, buat PO dengan total > Rp 1.000
- [ ] Submit PO → **Verifikasi:** Error "Limit kredit akan terlampaui"

---

## FLOW 10 — Audit Log

### 10.1 Cek Audit Trail
- [ ] Login sebagai Super Admin
- [ ] Buka `/dashboard/audit`
- [ ] **Verifikasi:** Semua aksi tercatat (PO created, submitted, approved, GR confirmed, invoice issued, payment approved)
- [ ] **Verifikasi:** Setiap log memiliki: actor, timestamp, before_value, after_value, module

---

## Hasil Testing

| Flow | Status | Catatan |
|------|--------|---------|
| 1. Purchase Order | ⬜ Pass / ⬜ Fail | |
| 2. Goods Receipt | ⬜ Pass / ⬜ Fail | |
| 3. Supplier Invoice | ⬜ Pass / ⬜ Fail | |
| 4. Customer Invoice | ⬜ Pass / ⬜ Fail | |
| 5. Payment Proof | ⬜ Pass / ⬜ Fail | |
| 6. Payment Ledger & Bank | ⬜ Pass / ⬜ Fail | |
| 7. Notifikasi | ⬜ Pass / ⬜ Fail | |
| 8. RBAC & Akses | ⬜ Pass / ⬜ Fail | |
| 9. Credit Control | ⬜ Pass / ⬜ Fail | |
| 10. Audit Log | ⬜ Pass / ⬜ Fail | |

**Kesimpulan:** ⬜ Layak Deploy / ⬜ Perlu Perbaikan

**Bug yang ditemukan:**
1. 
2. 
3. 
