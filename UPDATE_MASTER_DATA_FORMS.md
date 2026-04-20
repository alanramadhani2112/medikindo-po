# PANDUAN UPDATE MASTER DATA FORMS

## STATUS IMPLEMENTASI

### ✅ SELESAI:
1. **Migrations** - 4 file migration baru dibuat
2. **Models** - Semua model sudah diupdate (User, Product, Supplier, Organization)
3. **Controllers** - Semua controller sudah diupdate dengan validasi baru
4. **Forms** - Organizations create/edit sudah lengkap
5. **Forms** - Suppliers create sudah lengkap

### ⏳ PERLU DISELESAIKAN MANUAL:
1. **Suppliers edit form** - Tambahkan field license_expiry_date dan is_authorized_narcotic
2. **Products create/edit forms** - Tambahkan conditional logic untuk narcotic_group
3. **Users create/edit forms** - Tambahkan conditional logic untuk is_pharmacist

---

## LANGKAH EKSEKUSI

### 1. Jalankan Migrations
```bash
php artisan migrate
```

Ini akan menambahkan kolom baru:
- `users.is_pharmacist`
- `products.narcotic_group`, `requires_sp`, `requires_prescription`
- `organizations.city`, `province`, `is_authorized_narcotic`
- `suppliers.license_expiry_date`, `is_authorized_narcotic`

### 2. Update Suppliers Edit Form

File: `resources/views/suppliers/edit.blade.php`

Tambahkan setelah field `license_number`:

```html
{{-- Tanggal Kadaluarsa Izin --}}
<div class="mb-5">
    <label class="form-label fs-6 fw-semibold">Tanggal Kadaluarsa Izin</label>
    <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date', $supplier->license_expiry_date?->format('Y-m-d')) }}"
           class="form-control form-control-solid @error('license_expiry_date') is-invalid @enderror">
    <div class="form-text">Tanggal berakhirnya izin distribusi</div>
    @error('license_expiry_date')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

Tambahkan sebelum closing `</div>` dari col-12:

```html
{{-- Izin Narkotika --}}
<div class="mb-5">
    <div class="form-check form-switch form-check-custom form-check-solid">
        <input class="form-check-input" type="checkbox" name="is_authorized_narcotic" 
               id="is_authorized_narcotic" value="1" 
               {{ old('is_authorized_narcotic', $supplier->is_authorized_narcotic) ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold text-gray-700" for="is_authorized_narcotic">
            Memiliki Izin Distribusi Narkotika
        </label>
    </div>
    <div class="form-text text-warning mt-2">
        <i class="ki-outline ki-information-5 fs-5"></i>
        Centang jika supplier memiliki izin resmi untuk mendistribusikan obat narkotika/psikotropika
    </div>
</div>
```

### 3. Update Products Create Form

File: `resources/views/products/create.blade.php`

Tambahkan setelah checkbox `is_narcotic`:

```html
{{-- Conditional: Narcotic Group (shown only if is_narcotic = true) --}}
<div class="mb-5" id="narcotic_group_field" style="display: none;">
    <label class="form-label fs-6 fw-semibold required">Golongan Narkotika</label>
    <select name="narcotic_group" class="form-select form-select-solid" id="narcotic_group_select">
        <option value="">Pilih Golongan</option>
        <option value="I" {{ old('narcotic_group') === 'I' ? 'selected' : '' }}>Golongan I</option>
        <option value="II" {{ old('narcotic_group') === 'II' ? 'selected' : '' }}>Golongan II</option>
        <option value="III" {{ old('narcotic_group') === 'III' ? 'selected' : '' }}>Golongan III</option>
    </select>
    <div class="form-text">
        <strong>Golongan I:</strong> Paling berbahaya, hanya untuk penelitian<br>
        <strong>Golongan II:</strong> Dapat digunakan untuk terapi dengan pengawasan ketat<br>
        <strong>Golongan III:</strong> Dapat digunakan untuk terapi dengan pengawasan
    </div>
</div>
```

Tambahkan JavaScript sebelum closing `</form>`:

```html
<script>
function toggleNarcoticFields() {
    const isNarcotic = document.getElementById('is_narcotic')?.checked || false;
    const narcoticGroupField = document.getElementById('narcotic_group_field');
    const narcoticGroupSelect = document.getElementById('narcotic_group_select');
    
    if (isNarcotic) {
        narcoticGroupField.style.display = '';
        narcoticGroupSelect.required = true;
    } else {
        narcoticGroupField.style.display = 'none';
        narcoticGroupSelect.required = false;
        narcoticGroupSelect.value = '';
    }
}

// Attach event listener
document.addEventListener('DOMContentLoaded', function() {
    const narcoticCheckbox = document.getElementById('is_narcotic');
    if (narcoticCheckbox) {
        narcoticCheckbox.addEventListener('change', toggleNarcoticFields);
        toggleNarcoticFields(); // Initialize on page load
    }
});
</script>
```

### 4. Update Products Edit Form

File: `resources/views/products/edit.blade.php`

Sama seperti create form, tambahkan field dan JavaScript yang sama, tapi gunakan:

```html
<option value="I" {{ old('narcotic_group', $product->narcotic_group) === 'I' ? 'selected' : '' }}>Golongan I</option>
<option value="II" {{ old('narcotic_group', $product->narcotic_group) === 'II' ? 'selected' : '' }}>Golongan II</option>
<option value="III" {{ old('narcotic_group', $product->narcotic_group) === 'III' ? 'selected' : '' }}>Golongan III</option>
```

### 5. Update Users Create Form

File: `resources/views/users/create.blade.php`

Tambahkan setelah field `role`:

```html
{{-- Conditional: Pharmacist Checkbox (shown only if role = Healthcare) --}}
<div class="mb-5" id="pharmacist_field" style="display: none;">
    <div class="form-check form-switch form-check-custom form-check-solid">
        <input class="form-check-input" type="checkbox" name="is_pharmacist" 
               id="is_pharmacist" value="1" {{ old('is_pharmacist') ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold text-gray-700" for="is_pharmacist">
            User adalah Apoteker Berlisensi
        </label>
    </div>
    <div class="form-text text-info mt-2">
        <i class="ki-outline ki-information-5 fs-5"></i>
        Apoteker memiliki akses tambahan untuk verifikasi resep narkotika
    </div>
</div>
```

Tambahkan JavaScript:

```html
<script>
function togglePharmacistField() {
    const role = document.getElementById('role_select')?.value || '';
    const pharmacistField = document.getElementById('pharmacist_field');
    
    if (role === 'Healthcare') {
        pharmacistField.style.display = '';
    } else {
        pharmacistField.style.display = 'none';
        document.getElementById('is_pharmacist').checked = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_select');
    if (roleSelect) {
        roleSelect.addEventListener('change', togglePharmacistField);
        togglePharmacistField(); // Initialize on page load
    }
});
</script>
```

### 6. Update Users Edit Form

File: `resources/views/users/edit.blade.php`

Sama seperti create form, tapi gunakan:

```html
<input class="form-check-input" type="checkbox" name="is_pharmacist" 
       id="is_pharmacist" value="1" 
       {{ old('is_pharmacist', $user->is_pharmacist) ? 'checked' : '' }}>
```

---

## TESTING CHECKLIST

### Organizations Form
- [ ] Bisa input semua field baru (city, province, NPWP, NIK, customer_code, tax_rate, discount)
- [ ] Checkbox is_authorized_narcotic berfungsi
- [ ] Validasi unique untuk customer_code
- [ ] Data tersimpan dengan benar

### Suppliers Form
- [ ] Field license_number menjadi required
- [ ] Bisa input license_expiry_date
- [ ] Checkbox is_authorized_narcotic berfungsi
- [ ] Validasi unique untuk license_number
- [ ] Data tersimpan dengan benar

### Products Form
- [ ] Saat centang is_narcotic, field narcotic_group muncul
- [ ] Field narcotic_group menjadi required saat is_narcotic = true
- [ ] Saat uncheck is_narcotic, field narcotic_group hilang
- [ ] requires_sp dan requires_prescription auto-set di backend
- [ ] Data tersimpan dengan benar

### Users Form
- [ ] Saat pilih role "Healthcare", field is_pharmacist muncul
- [ ] Saat pilih role lain, field is_pharmacist hilang
- [ ] Data tersimpan dengan benar

---

## ROLLBACK (Jika Ada Masalah)

```bash
# Rollback 4 migrations terakhir
php artisan migrate:rollback --step=4
```

---

## CATATAN PENTING

1. **Puskesmas sudah dihapus** - Hanya ada "clinic" dan "hospital" di dropdown type
2. **Conditional logic** - Semua sudah diimplementasi di backend (controller)
3. **Frontend validation** - Menggunakan JavaScript untuk show/hide field
4. **Backend validation** - Menggunakan conditional rules di controller
5. **Auto-set fields** - requires_sp dan requires_prescription auto-set saat is_narcotic = true

---

**ESTIMASI WAKTU PENYELESAIAN MANUAL:** 30-45 menit untuk update 5 file form yang tersisa
