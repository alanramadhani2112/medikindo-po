# Manual Payment Entry Form - Perbaikan

## Masalah yang Ditemukan:

1. ❌ Menggunakan `x-show` dengan `x-transition` - field tidak di-submit browser
2. ❌ Menggunakan `sender_bank_code` padahal seharusnya `sender_bank_name` (seperti payment proof)
3. ❌ Layout masih menumpuk dan membingungkan
4. ❌ Tidak mengikuti standar form Payment Proof yang sudah ada

## Solusi yang Benar (Belajar dari Payment Proof):

### 1. Gunakan `:style` bukan `x-show`
```blade
{{-- SALAH --}}
<div x-show="showBankFields" x-transition>

{{-- BENAR --}}
<div :style="!showBankFields ? 'display: none;' : ''">
```

### 2. Gunakan `sender_bank_name` bukan `sender_bank_code`
```blade
{{-- SALAH --}}
<select name="sender_bank_code">

{{-- BENAR --}}
<select name="sender_bank_name">
```

### 3. Gunakan dropdown bank dari IndonesianBankSeeder
```blade
@foreach(\Database\Seeders\IndonesianBankSeeder::$BANKS as $bank)
    <option value="{{ $bank['name'] }}">
        {{ $bank['name'] }} ({{ $bank['code'] }})
    </option>
@endforeach
```

### 4. Label yang berubah sesuai metode pembayaran
```blade
<label class="form-label fw-bold">
    <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">No. Referensi Transfer</span>
    <span x-show="paymentMethod === 'Cash'">No. Kwitansi</span>
    <span x-show="paymentMethod === 'Giro/Cek'">No. Referensi</span>
    <span x-show="paymentMethod === ''">No. Referensi</span>
</label>
```

### 5. Upload file dengan label dinamis
```blade
<label class="form-label fw-bold required">
    <span x-show="paymentMethod === 'Bank Transfer' || paymentMethod === 'Virtual Account'">Upload Bukti Transfer</span>
    <span x-show="paymentMethod === 'Cash'">Upload Kwitansi</span>
    <span x-show="paymentMethod === 'Giro/Cek'">Upload Foto Giro/Cek</span>
    <span x-show="paymentMethod === ''">Upload Bukti Pembayaran</span>
    <span class="badge badge-light-danger ms-2">Wajib</span>
</label>
```

## Action Items:

1. Ganti semua `x-show` dengan `:style`
2. Ganti `sender_bank_code` jadi `sender_bank_name`
3. Gunakan `IndonesianBankSeeder::$BANKS` untuk dropdown
4. Tambahkan label dinamis untuk reference dan upload
5. Hapus alert info/warning/success yang menumpuk
6. Simplify layout agar tidak membingungkan

## File yang Perlu Diupdate:

- `resources/views/payments/create_incoming.blade.php` - Form view
- `app/Http/Controllers/Web/PaymentWebController.php` - Controller (jika perlu)
- `app/Http/Requests/StoreIncomingPaymentRequest.php` - Validation (jika perlu)
