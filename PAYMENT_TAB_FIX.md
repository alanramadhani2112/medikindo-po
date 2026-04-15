# Perbaikan Tab Pending & Confirmed di Payment

## Status: ✅ SELESAI

## Masalah
Tab **Pending** dan **Confirmed** di halaman `/payments` tidak berfungsi karena controller tidak memiliki logic untuk filter berdasarkan status.

---

## Perbaikan yang Dilakukan

### 1. **Controller: `PaymentWebController.php`**

#### Menambahkan Tab Filtering
```php
// Tab Filtering
if ($tab === 'incoming') {
    $query->where('type', 'incoming');
} elseif ($tab === 'outgoing') {
    $query->where('type', 'outgoing');
} elseif ($tab === 'pending') {
    $query->where('status', 'pending');
} elseif ($tab === 'confirmed') {
    $query->whereIn('status', ['confirmed', 'completed']);
}
```

#### Menambahkan Counts untuk Badge
```php
$counts = [
    'all'       => (clone $scopedBase)->count(),
    'incoming'  => (clone $scopedBase)->where('type', 'incoming')->count(),
    'outgoing'  => (clone $scopedBase)->where('type', 'outgoing')->count(),
    'pending'   => (clone $scopedBase)->where('status', 'pending')->count(),
    'confirmed' => (clone $scopedBase)->whereIn('status', ['confirmed', 'completed'])->count(),
];
```

#### Memperbaiki Total Kas (Hanya Confirmed)
```php
// Hanya hitung payment yang sudah confirmed untuk total kas
$totalIn  = (clone $scopedBase)->where('type', 'incoming')->whereIn('status', ['confirmed', 'completed'])->sum('amount');
$totalOut = (clone $scopedBase)->where('type', 'outgoing')->whereIn('status', ['confirmed', 'completed'])->sum('amount');
```

**Alasan**: Payment yang masih `pending` belum benar-benar terjadi, jadi tidak boleh dihitung dalam total kas.

#### Menambahkan Variable `$tab` ke View
```php
return view('payments.index', compact('payments', 'totalIn', 'totalOut', 'balance', 'counts', 'breadcrumbs', 'tab'));
```

---

### 2. **View: `payments/index.blade.php`**

#### Menghapus Logic Counts dari View
**Sebelum**:
```php
$counts = [
    'all' => $payments->total(),
    'incoming' => $payments->where('type', 'incoming')->count(),
    // ... (salah karena hanya count dari current page)
];
```

**Sesudah**:
```php
// Counts sudah dikirim dari controller
$count = $counts[$val] ?? 0;
```

**Alasan**: Counts harus dihitung dari database (semua data), bukan dari collection paginated (hanya 15 data per halaman).

---

## Cara Kerja Setelah Perbaikan

### **Tab: Semua Transaksi**
- Menampilkan semua payment (incoming + outgoing, pending + confirmed)
- Badge: Total semua transaksi

### **Tab: Kas Masuk**
- Filter: `type = 'incoming'`
- Menampilkan semua payment masuk (pending + confirmed)
- Badge: Total kas masuk

### **Tab: Kas Keluar**
- Filter: `type = 'outgoing'`
- Menampilkan semua payment keluar (pending + confirmed)
- Badge: Total kas keluar

### **Tab: Pending** ⚠️
- Filter: `status = 'pending'`
- Menampilkan payment yang **belum dikonfirmasi**
- Badge: Total pending (warning color)
- Icon: `ki-time` (jam)

### **Tab: Confirmed** ✅
- Filter: `status IN ('confirmed', 'completed')`
- Menampilkan payment yang **sudah dikonfirmasi**
- Badge: Total confirmed (success color)
- Icon: `ki-check-circle` (centang)

---

## Status Payment

| Status | Deskripsi | Badge Color |
|--------|-----------|-------------|
| **pending** | Menunggu verifikasi/konfirmasi | Warning (Kuning) |
| **confirmed** | Sudah dikonfirmasi | Success (Hijau) |
| **completed** | Selesai (sama dengan confirmed) | Success (Hijau) |
| **cancelled** | Dibatalkan | Danger (Merah) |

---

## Perbedaan Penting

### **Total Kas (KPI Cards)**
- **Hanya menghitung payment yang `confirmed/completed`**
- Payment `pending` **TIDAK** dihitung
- Alasan: Uang belum benar-benar masuk/keluar

### **Badge Counts (Tab)**
- Menghitung **semua payment** sesuai filter
- Tab Pending: hitung yang status pending
- Tab Confirmed: hitung yang status confirmed/completed

---

## Testing

### Test Case 1: Tab Pending
1. Buka `/payments?tab=pending`
2. Harus menampilkan hanya payment dengan status `pending`
3. Badge menunjukkan jumlah yang benar

### Test Case 2: Tab Confirmed
1. Buka `/payments?tab=confirmed`
2. Harus menampilkan hanya payment dengan status `confirmed` atau `completed`
3. Badge menunjukkan jumlah yang benar

### Test Case 3: Total Kas
1. Buat payment baru dengan status `pending`
2. Total Kas **TIDAK** berubah
3. Update status ke `confirmed`
4. Total Kas **BERUBAH** (bertambah/berkurang)

---

## File yang Dimodifikasi

1. ✅ `app/Http/Controllers/Web/PaymentWebController.php`
   - Menambahkan tab filtering logic
   - Menambahkan counts calculation
   - Memperbaiki total kas (hanya confirmed)
   - Menambahkan variable `$tab` ke view

2. ✅ `resources/views/payments/index.blade.php`
   - Menghapus counts calculation dari view
   - Menggunakan counts dari controller

---

## Kesimpulan

Tab Pending dan Confirmed sekarang **berfungsi dengan benar**:
- ✅ Filter berdasarkan status
- ✅ Badge counts akurat
- ✅ Total kas hanya hitung confirmed
- ✅ UI/UX konsisten

**Status: PRODUCTION READY** ✅
