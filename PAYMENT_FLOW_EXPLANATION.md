# Penjelasan Flow Pembayaran (Payment Flow)

## Pertanyaan Anda:
> "Setelah pembayaran disetujui, maka uang otomatis masuk ke dalam buku kas dengan status incoming. Tapi saya bingung pada bagian ini http://medikindo-po.test/payments/incoming?invoice_id=4. Itu maksudnya apa, kenapa harus rekam penerimaan kembali. Padahalkan sudah otomatis tercatat. Apakah seperti itu?"

## Jawaban:

**TIDAK PERLU rekam ulang!** Anda benar bahwa sistem sudah otomatis mencatat pembayaran ketika Finance approve bukti bayar. Halaman `/payments/incoming` adalah untuk **kasus khusus** saja.

---

## 2 Jalur Pembayaran yang Berbeda:

### Jalur 1: **NORMAL FLOW** (Otomatis via Payment Proof) ✅

**Digunakan untuk:** 99% kasus pembayaran normal

**Flow:**
```
1. RS/Klinik submit bukti bayar di menu "Payment Proofs"
   ↓
2. Finance review & approve bukti bayar
   ↓
3. ✅ SISTEM OTOMATIS create Payment IN record
   ↓
4. Invoice status update (partial/paid)
   ↓
5. Buku kas tercatat otomatis
```

**Kapan digunakan:**
- Pembayaran via transfer bank
- Pembayaran via virtual account
- Pembayaran via giro/cek (dengan bukti)
- Semua pembayaran yang ada bukti transfernya

**File terkait:**
- Submit: `resources/views/payment-proofs/create.blade.php`
- Approve: `resources/views/payment-proofs/approve.blade.php`
- Service: `app/Services/PaymentProofService.php` (method `approvePaymentProof`)

---

### Jalur 2: **MANUAL ENTRY** (Khusus untuk Kasus Tertentu) ⚠️

**Digunakan untuk:** Kasus khusus/jarang (< 1%)

**Flow:**
```
1. Finance langsung input di menu "Payments → Incoming"
   ↓
2. Input manual: invoice, jumlah, tanggal, metode
   ↓
3. ✅ Create Payment IN record
   ↓
4. Invoice status update
```

**Kapan digunakan:**
- ✅ Pembayaran **cash langsung** (tanpa bukti transfer)
- ✅ Pembayaran **cek/giro yang sudah cair** (tanpa submit bukti)
- ✅ **Koreksi pembayaran** (jika ada kesalahan)
- ✅ Pembayaran dari **RS yang tidak punya akses sistem**
- ✅ Pembayaran **offline/manual** lainnya

**File terkait:**
- Form: `resources/views/payments/create_incoming.blade.php`
- Controller: `app/Http/Controllers/Web/PaymentWebController.php`

---

## Perbedaan Utama:

| Aspek | Payment Proof (Normal) | Manual Entry (Khusus) |
|-------|------------------------|----------------------|
| **Siapa yang input** | RS/Klinik submit → Finance approve | Finance langsung input |
| **Ada bukti transfer?** | ✅ Ya, wajib upload | ❌ Tidak ada/tidak perlu |
| **Proses** | 2 langkah (submit + approve) | 1 langkah (langsung input) |
| **Audit trail** | Lengkap (siapa submit, siapa approve) | Hanya Finance yang input |
| **Use case** | 99% pembayaran normal | 1% kasus khusus |

---

## Rekomendasi:

### ✅ Yang Sudah Benar:
1. Sistem otomatis create Payment IN ketika approve payment proof
2. Tidak perlu double entry untuk pembayaran normal

### ⚠️ Yang Perlu Diperbaiki:
1. **Tambahkan warning** di halaman manual entry agar Finance tidak input ulang
2. **Dokumentasi** kapan harus pakai manual entry vs approve payment proof
3. **Training** untuk Finance team tentang 2 jalur ini

### 📝 Update yang Sudah Dilakukan:
- ✅ Menambahkan **alert warning** di halaman `/payments/incoming` yang menjelaskan:
  - Halaman ini untuk manual entry khusus
  - Jika RS sudah submit bukti, JANGAN input ulang
  - Link ke menu Payment Proofs untuk approve

---

## Contoh Kasus Penggunaan:

### Kasus 1: Pembayaran Transfer Normal ✅
**Scenario:** RS Harapan Bunda transfer Rp 50.000.000 via BCA

**Flow yang BENAR:**
1. RS submit bukti bayar di Payment Proofs
2. Finance approve di Payment Proofs
3. ✅ Selesai (otomatis tercatat)

**JANGAN:** Input ulang di `/payments/incoming`

---

### Kasus 2: Pembayaran Cash Langsung 💵
**Scenario:** RS Sehat Sentosa bayar cash Rp 10.000.000 langsung ke kantor

**Flow yang BENAR:**
1. Finance langsung input di `/payments/incoming`
2. Pilih invoice, input jumlah, metode: Cash
3. ✅ Selesai

**Kenapa manual?** Karena tidak ada bukti transfer yang bisa di-submit RS

---

### Kasus 3: Cek yang Sudah Cair 📝
**Scenario:** RS Medika Utama bayar pakai cek, sudah cair di bank

**Flow yang BENAR:**
1. Finance langsung input di `/payments/incoming`
2. Pilih invoice, input jumlah, metode: Cek
3. ✅ Selesai

**Kenapa manual?** Cek sudah cair, tidak perlu proses approve lagi

---

## Kesimpulan:

**Anda BENAR!** Tidak perlu rekam ulang pembayaran yang sudah di-approve via Payment Proof. Halaman `/payments/incoming` hanya untuk kasus khusus seperti cash, cek cair, atau koreksi.

**Solusi:**
- Sudah ditambahkan warning di halaman manual entry
- Finance harus paham kapan pakai jalur mana
- 99% pembayaran pakai Payment Proof (otomatis)
- 1% pembayaran pakai Manual Entry (kasus khusus)
