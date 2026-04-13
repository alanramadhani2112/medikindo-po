# Quick Fix Guide - UI Layout Issues

## 🚀 Quick Testing (5 menit)

### 1. Test Asset Loading
```
URL: http://medikindo-po.test/diagnostic
```
Cek apakah semua status menunjukkan "Loaded" (hijau)

### 2. Test Layout
```
URL: http://medikindo-po.test/test-layout
```
Cek apakah header, sidebar, dan content tampil dengan benar

### 3. Test Approvals
```
URL: http://medikindo-po.test/approvals
```
Cek apakah halaman approvals tampil tanpa error

---

## 🔧 Quick Fixes

### UI Masih Rusak?

#### Fix 1: Clear Cache (30 detik)
```bash
php artisan view:clear
php artisan cache:clear
```
Kemudian refresh browser dengan Ctrl+F5

#### Fix 2: Rebuild Assets (1 menit)
```bash
npm run build
```
Kemudian refresh browser dengan Ctrl+F5

#### Fix 3: Clear Browser Cache
1. Tekan Ctrl+Shift+Delete
2. Pilih "Cached images and files"
3. Clear data
4. Refresh halaman dengan Ctrl+F5

---

## 🐛 Common Issues

### Issue: Halaman Blank/Putih
**Penyebab**: JavaScript error
**Solusi**: 
1. Buka browser console (F12)
2. Lihat error message
3. Screenshot dan laporkan

### Issue: Styling Berantakan
**Penyebab**: CSS tidak load
**Solusi**:
1. Buka Network tab (F12)
2. Cek apakah ada file CSS yang 404
3. Pastikan file ada di `public/assets/metronic8/css/`

### Issue: Sidebar Tidak Muncul
**Penyebab**: JavaScript tidak initialize
**Solusi**:
1. Cek browser console untuk error
2. Pastikan `scripts.bundle.js` loaded
3. Hard refresh (Ctrl+F5)

### Issue: Icons Tidak Muncul
**Penyebab**: Keenicons font tidak load
**Solusi**:
1. Cek Network tab untuk font files
2. Pastikan `plugins.bundle.css` loaded
3. Clear browser cache

---

## 📋 Checklist Debugging

Jika UI rusak, cek satu per satu:

- [ ] Browser console tidak ada error merah
- [ ] Network tab: semua file status 200 (bukan 404)
- [ ] File `style.bundle.css` loaded
- [ ] File `scripts.bundle.js` loaded
- [ ] File `app-*.css` (Vite) loaded
- [ ] File `app-*.js` (Vite) loaded
- [ ] Cache sudah di-clear
- [ ] Browser cache sudah di-clear

---

## 🎯 Expected Results

### Diagnostic Page (`/diagnostic`)
✅ CSS Bundle: **Loaded** (hijau)
✅ JS Bundle: **Loaded** (hijau)
✅ Metronic Components: **Available** (hijau)
✅ Alert box tampil dengan icon
✅ Buttons tampil dengan warna yang benar
✅ Icons (home, user, document, check) tampil

### Test Layout Page (`/test-layout`)
✅ Header tampil dengan logo "Medikindo"
✅ Sidebar tampil dengan menu Dashboard
✅ Toolbar tampil dengan title "Test Page"
✅ Content area tampil dengan 2 cards
✅ Buttons berwarna (Primary biru, Success hijau)

### Approvals Page (`/approvals`)
✅ Header "Manajemen Persetujuan" tampil
✅ Filter bar tampil
✅ Tabs "Antrian Persetujuan" dan "Riwayat Keputusan" tampil
✅ Table tampil dengan data
✅ Action buttons (Setujui/Tolak) tampil
✅ Tidak ada error di console

---

## 🆘 Emergency Rollback

Jika semua fix tidak berhasil, gunakan minimal layout:

### Step 1: Edit View File
Ganti di file yang bermasalah (contoh: `approvals/index.blade.php`):
```blade
@extends('layouts.minimal')
```

### Step 2: Test
Refresh halaman dan cek apakah tampil dengan benar

### Step 3: Report
Jika minimal layout bekerja, berarti ada masalah di `layouts/app.blade.php`

---

## 📞 Support Commands

### Check Laravel Version
```bash
php artisan --version
```

### Check Node Version
```bash
node --version
npm --version
```

### Check Vite Build
```bash
npm run build
```

### List Routes
```bash
php artisan route:list --name=web.approvals
```

### Check Permissions
```bash
php artisan permission:show
```

---

## 💡 Tips

1. **Selalu cek browser console** - 90% masalah UI terlihat di sini
2. **Gunakan hard refresh** - Ctrl+F5 untuk bypass cache
3. **Test di incognito mode** - Untuk memastikan bukan masalah cache
4. **Screenshot error** - Lebih mudah untuk debugging
5. **Test satu halaman dulu** - Jangan test semua halaman sekaligus

---

## ✅ Verification

Setelah fix, pastikan:
- [ ] `/diagnostic` - Semua hijau
- [ ] `/test-layout` - Layout tampil sempurna
- [ ] `/approvals` - Halaman bekerja normal
- [ ] Browser console - Tidak ada error
- [ ] Network tab - Semua file 200

Jika semua checklist ✅, maka UI sudah fixed! 🎉