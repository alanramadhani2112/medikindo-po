# Struktur Menu Final - Mengikuti Flow Bisnis

## Urutan Menu Berdasarkan Flow Bisnis (UI/UX):

```
📊 DASHBOARD
   └── Overview sistem

📦 PROCUREMENT (Langkah 1: Beli dari Supplier)
   ├── Purchase Orders (buat PO)
   ├── Approvals (approve PO)
   └── Goods Receipt (terima barang)

💸 HUTANG (AP) (Langkah 2: Bayar ke Supplier)
   ├── Supplier Invoices (tagihan dari supplier)
   └── Payment Out (bayar ke supplier) - coming soon

💰 PIUTANG (AR) (Langkah 3: Tagih ke RS/Klinik)
   ├── Customer Invoices (tagihan ke RS)
   ├── Payment Proofs (bukti bayar dari RS)
   ├── Manual Payment Entry (penerimaan khusus)
   ├── AR Aging (umur piutang)
   ├── Credit Notes (retur/koreksi)
   └── Credit Control (limit kredit)

📒 KAS & BANK (Langkah 4: Hasil Akhir)
   ├── Payment Ledger (buku kas - semua transaksi)
   ├── Bank Accounts (rekening bank)
   └── Cash Flow (coming soon)

📦 INVENTORY
   └── Inventory (coming soon)

⚙️ MASTER DATA (tidak berubah)
   ├── Organizations
   ├── Suppliers
   ├── Products
   ├── Price Lists
   └── Users
```

---

## Penjelasan Flow Bisnis:

### 1. PROCUREMENT (Beli dari Supplier)
**Flow:**
```
Create PO → Approve PO → Receive Goods → Create Supplier Invoice
```
**User:** Medikindo internal (Purchasing, Approver, Warehouse)

---

### 2. HUTANG/AP (Bayar ke Supplier)
**Flow:**
```
Supplier Invoice (tagihan dari supplier) → Payment Out (bayar ke supplier)
```
**User:** Medikindo Finance
**Output:** Uang keluar dari Medikindo ke Supplier

---

### 3. PIUTANG/AR (Tagih ke RS/Klinik)
**Flow:**
```
Customer Invoice (tagih ke RS) 
   ↓
RS submit Payment Proof (bukti bayar)
   ↓
Finance approve Payment Proof
   ↓
Otomatis tercatat di Payment Ledger
```

**Alternative Flow (Manual Entry):**
```
Customer Invoice (tagih ke RS)
   ↓
Finance input Manual Payment Entry (untuk cash/cek/koreksi)
   ↓
Otomatis tercatat di Payment Ledger
```

**User:** 
- RS/Klinik: submit payment proof
- Medikindo Finance: approve payment proof atau manual entry

**Output:** Uang masuk dari RS ke Medikindo

---

### 4. KAS & BANK (Hasil Akhir)
**Flow:**
```
Payment Ledger = Hasil dari:
   - Payment Proof yang di-approve (incoming)
   - Manual Payment Entry (incoming)
   - Payment Out (outgoing) - coming soon
```

**User:** Medikindo Finance (read-only, untuk monitoring)
**Output:** Laporan buku kas (incoming + outgoing)

---

## Kenapa Urutan Ini Lebih Baik (UI/UX):

### ✅ Mengikuti Flow Bisnis Kronologis:
1. **PROCUREMENT** → Beli barang dulu
2. **HUTANG (AP)** → Bayar supplier
3. **PIUTANG (AR)** → Tagih ke RS
4. **KAS & BANK** → Lihat hasil akhir

### ✅ Logical Grouping:
- **HUTANG (AP)** = Semua yang berhubungan dengan supplier (uang keluar)
- **PIUTANG (AR)** = Semua yang berhubungan dengan RS (uang masuk)
- **KAS & BANK** = Hasil akhir (buku kas)

### ✅ User Journey:
- User mulai dari **PROCUREMENT** (beli barang)
- Lanjut ke **HUTANG** (bayar supplier)
- Lanjut ke **PIUTANG** (tagih ke RS)
- Akhir di **KAS & BANK** (lihat hasil)

### ✅ Tidak Membingungkan:
- **Payment Proofs** ada di section PIUTANG (karena memang untuk tagihan RS)
- **Manual Payment Entry** jelas untuk kasus khusus (ada badge warning)
- **Payment Ledger** ada di section KAS & BANK (karena ini hasil akhir)

---

## Perubahan dari Menu Lama:

### Before (Membingungkan):
```
INVOICING
├── Supplier Invoice (AP)
├── Customer Invoice (AR)
├── AR Aging
└── Credit Notes

PAYMENT
├── Payment Ledger
├── Payment Proofs
├── Credit Control
└── Payment Out (soon)

AKUN BANK
└── Bank Accounts
```

### After (Jelas):
```
PROCUREMENT
├── Purchase Orders
├── Approvals
└── Goods Receipt

HUTANG (AP)
├── Supplier Invoices
└── Payment Out (soon)

PIUTANG (AR)
├── Customer Invoices
├── Payment Proofs
├── Manual Payment Entry
├── AR Aging
├── Credit Notes
└── Credit Control

KAS & BANK
├── Payment Ledger
├── Bank Accounts
└── Cash Flow (soon)
```

---

## Benefit Reorganisasi:

1. ✅ **Flow bisnis jelas**: Procurement → AP → AR → Kas & Bank
2. ✅ **Tidak ada duplikasi**: Manual entry jelas untuk kasus khusus
3. ✅ **Mudah dipahami**: User tahu harus ke menu mana
4. ✅ **Sesuai standar akuntansi**: AP, AR, Cash & Bank terpisah
5. ✅ **Scalable**: Mudah tambah fitur baru

---

## Files Changed:

1. ✅ `resources/views/components/partials/sidebar.blade.php` - Menu navigation
2. ✅ `resources/views/payments/create_incoming.blade.php` - Warning message
3. ✅ `resources/views/payment-proofs/approve.blade.php` - Modal lightbox
4. ✅ `routes/web.php` - View document route

---

## Next Steps:

1. ✅ Test menu navigation
2. ✅ Verify all links working
3. ✅ Update user documentation
4. ✅ Training untuk Finance team
