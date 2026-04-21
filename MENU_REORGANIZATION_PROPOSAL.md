# Proposal Reorganisasi Menu Sistem Medikindo

## Masalah Saat Ini:

### Menu "PAYMENT" Section (Membingungkan):
```
PAYMENT
├── Payment Ledger (buku kas - semua transaksi)
├── Payment Proofs (bukti bayar dari RS)
├── Credit Control (limit kredit RS)
└── Payment Out (coming soon)
```

**Kenapa Membingungkan:**
1. **Payment Ledger** = hasil akhir (buku kas) - seharusnya di bawah
2. **Payment Proofs** = proses approve bukti bayar - seharusnya di atas
3. Tidak jelas mana untuk **uang masuk** vs **uang keluar**
4. Tidak jelas hubungannya dengan **Invoice**

---

## Proposal Reorganisasi (Berdasarkan Flow Bisnis):

### Opsi 1: **PISAHKAN BERDASARKAN AR & AP** (Recommended)

```
📊 DASHBOARD

📦 PROCUREMENT
├── Purchase Orders
├── Approvals
└── Goods Receipt

💰 ACCOUNTS RECEIVABLE (AR) - Piutang/Tagihan ke RS
├── Customer Invoices (tagihan ke RS)
├── Payment Proofs (bukti bayar dari RS) 🔥 PINDAH KE SINI
├── AR Aging (umur piutang)
├── Credit Notes (retur/koreksi)
└── Credit Control (limit kredit RS)

💸 ACCOUNTS PAYABLE (AP) - Hutang/Bayar ke Supplier
├── Supplier Invoices (tagihan dari supplier)
└── Payment Out (bayar ke supplier) - coming soon

📒 CASH & BANK
├── Payment Ledger (buku kas - semua transaksi) 🔥 PINDAH KE SINI
├── Bank Accounts (rekening bank)
└── Cash Flow Report - coming soon

📦 INVENTORY
└── Inventory (coming soon)

⚙️ MASTER DATA
├── Organizations
├── Suppliers
├── Products
├── Price Lists
└── Users
```

---

### Opsi 2: **PISAHKAN BERDASARKAN UANG MASUK & KELUAR** (Alternatif)

```
📊 DASHBOARD

📦 PROCUREMENT
├── Purchase Orders
├── Approvals
└── Goods Receipt

💵 UANG MASUK (Incoming)
├── Customer Invoices (tagihan ke RS)
├── Payment Proofs (bukti bayar dari RS) 🔥
├── Incoming Payments (rekam penerimaan manual) 🔥 SUB-MENU BARU
├── AR Aging (umur piutang)
├── Credit Notes (retur/koreksi)
└── Credit Control (limit kredit RS)

💸 UANG KELUAR (Outgoing)
├── Supplier Invoices (tagihan dari supplier)
├── Outgoing Payments (bayar ke supplier) 🔥 SUB-MENU BARU
└── Payment Out (coming soon)

📒 BUKU KAS
├── Payment Ledger (semua transaksi) 🔥
└── Bank Accounts (rekening bank)

📦 INVENTORY
└── Inventory (coming soon)

⚙️ MASTER DATA
├── Organizations
├── Suppliers
├── Products
├── Price Lists
└── Users
```

---

### Opsi 3: **GABUNG INVOICE & PAYMENT** (Paling Sederhana)

```
📊 DASHBOARD

📦 PROCUREMENT
├── Purchase Orders
├── Approvals
└── Goods Receipt

💰 PIUTANG (AR - Accounts Receivable)
├── 📄 Customer Invoices (tagihan ke RS)
├── 📋 Payment Proofs (bukti bayar dari RS) 🔥
│   ├── Submit Bukti Bayar (untuk RS)
│   └── Review & Approve (untuk Finance)
├── 💵 Manual Payment Entry (penerimaan khusus) 🔥 SUB-MENU BARU
├── 📊 AR Aging (umur piutang)
├── 📝 Credit Notes (retur/koreksi)
└── 🛡️ Credit Control (limit kredit)

💸 HUTANG (AP - Accounts Payable)
├── 📄 Supplier Invoices (tagihan dari supplier)
└── 💳 Payment Out (bayar ke supplier) - coming soon

📒 KAS & BANK
├── 📖 Payment Ledger (buku kas) 🔥
├── 🏦 Bank Accounts (rekening bank)
└── 📊 Cash Flow - coming soon

📦 INVENTORY
└── Inventory (coming soon)

⚙️ MASTER DATA
├── Organizations
├── Suppliers
├── Products
├── Price Lists
└── Users
```

---

## Rekomendasi Saya: **OPSI 3** (Paling Jelas)

### Alasan:
1. ✅ **Jelas flow bisnis**: Piutang (AR) vs Hutang (AP)
2. ✅ **Payment Proofs** ada di section AR (karena memang untuk tagihan RS)
3. ✅ **Manual Payment Entry** jadi sub-menu terpisah dengan label jelas
4. ✅ **Payment Ledger** pindah ke section "KAS & BANK" (karena ini buku kas)
5. ✅ **Tidak membingungkan** antara proses vs hasil

### Perubahan Detail:

#### Section "PIUTANG (AR)":
```
💰 PIUTANG (AR)
├── Customer Invoices (tagihan ke RS)
├── Payment Proofs (bukti bayar dari RS)
│   └── Halaman index dengan tab:
│       - Submitted (menunggu review)
│       - Verified (sudah diverifikasi)
│       - Approved (sudah disetujui)
│       - Rejected (ditolak)
│   └── Button "Submit Bukti Bayar" (untuk RS)
│   └── Button "Review & Approve" (untuk Finance)
├── Manual Payment Entry 🆕 (penerimaan khusus)
│   └── Form input manual untuk:
│       - Pembayaran cash
│       - Cek/giro yang sudah cair
│       - Koreksi pembayaran
│       - Pembayaran tanpa bukti
│   └── Warning: "Jangan input ulang jika RS sudah submit bukti"
├── AR Aging (umur piutang)
├── Credit Notes (retur/koreksi)
└── Credit Control (limit kredit)
```

#### Section "KAS & BANK":
```
📒 KAS & BANK
├── Payment Ledger (buku kas - semua transaksi)
│   └── Tab: All, Incoming, Outgoing
│   └── Read-only (hasil dari approve payment proof atau manual entry)
├── Bank Accounts (rekening bank)
└── Cash Flow Report (coming soon)
```

---

## Perubahan Route & Controller:

### Route Baru:
```php
// Manual Payment Entry (khusus untuk kasus tertentu)
Route::prefix('ar')->name('web.ar.')->group(function () {
    Route::get('/manual-payment', [ARPaymentController::class, 'createManual'])
        ->name('manual-payment.create');
    Route::post('/manual-payment', [ARPaymentController::class, 'storeManual'])
        ->name('manual-payment.store');
});
```

### Atau Tetap Pakai Route Lama dengan Label Baru:
```php
// Ubah label di sidebar saja, route tetap sama
Route::get('/payments/incoming', ...) // Label: "Manual Payment Entry"
```

---

## Perubahan Sidebar:

### Before (Membingungkan):
```
PAYMENT
├── Payment Ledger
├── Payment Proofs
├── Credit Control
└── Payment Out (soon)
```

### After (Jelas):
```
PIUTANG (AR)
├── Customer Invoices
├── Payment Proofs
├── Manual Payment Entry 🆕
├── AR Aging
├── Credit Notes
└── Credit Control

HUTANG (AP)
├── Supplier Invoices
└── Payment Out (soon)

KAS & BANK
├── Payment Ledger
├── Bank Accounts
└── Cash Flow (soon)
```

---

## Benefit Reorganisasi:

1. ✅ **Flow bisnis jelas**: AR (piutang) → AP (hutang) → Kas & Bank
2. ✅ **Tidak ada duplikasi**: Manual entry jelas untuk kasus khusus
3. ✅ **Mudah dipahami**: Finance tahu kapan pakai payment proof vs manual entry
4. ✅ **Sesuai standar akuntansi**: AR, AP, Cash & Bank terpisah
5. ✅ **Scalable**: Mudah tambah fitur baru (cash flow, payment out, dll)

---

## Action Items:

### 1. Update Sidebar Navigation
- File: `resources/views/components/partials/sidebar.blade.php`
- Pisahkan section: AR, AP, Cash & Bank

### 2. Rename Menu Labels
- "Payment Ledger" → tetap di section "KAS & BANK"
- "Payment Proofs" → pindah ke section "PIUTANG (AR)"
- "/payments/incoming" → rename label jadi "Manual Payment Entry"

### 3. Add Warning/Info
- ✅ Sudah ditambahkan warning di halaman manual entry
- Tambahkan tooltip/info di menu sidebar

### 4. Update Documentation
- Update user manual
- Training untuk Finance team
- Flow chart untuk kapan pakai payment proof vs manual entry

---

## Prioritas Implementasi:

### Phase 1: Quick Win (1-2 jam)
- ✅ Update sidebar navigation (reorganisasi menu)
- ✅ Rename menu labels
- ✅ Add warning di halaman manual entry (sudah done)

### Phase 2: Enhancement (3-5 jam)
- Create dedicated "Manual Payment Entry" page dengan UI lebih jelas
- Add tooltip/help text di sidebar
- Update breadcrumbs

### Phase 3: Documentation (2-3 jam)
- User manual
- Flow chart
- Training materials

---

## Apakah Anda Setuju?

Saya recommend **OPSI 3** karena paling jelas dan sesuai standar akuntansi. Tapi saya bisa adjust sesuai preferensi Anda.

**Pertanyaan untuk Anda:**
1. Apakah Anda setuju dengan struktur menu OPSI 3?
2. Apakah ada menu lain yang perlu dipindah/diganti?
3. Apakah nama section "PIUTANG (AR)" dan "HUTANG (AP)" sudah jelas? Atau prefer bahasa Inggris?
4. Apakah perlu saya implement sekarang atau review dulu?
