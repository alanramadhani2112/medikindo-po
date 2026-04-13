# 📦 GITHUB PUSH GUIDE

**Date**: April 14, 2026  
**Project**: Medikindo PO System v2.0  
**Status**: Ready to push

---

## ✅ LANGKAH 1: BUAT REPOSITORY DI GITHUB

### Option A: Via GitHub Website
1. Buka https://github.com
2. Login ke akun Anda
3. Klik tombol **"+"** di kanan atas → **"New repository"**
4. Isi form:
   - **Repository name**: `medikindo-po` (atau nama lain)
   - **Description**: "Medikindo Procurement & Financial System v2.0"
   - **Visibility**: Private (recommended) atau Public
   - **JANGAN** centang "Initialize this repository with a README"
5. Klik **"Create repository"**

### Option B: Via GitHub CLI (jika sudah install)
```bash
gh repo create medikindo-po --private --source=. --remote=origin
```

---

## ✅ LANGKAH 2: HUBUNGKAN LOCAL KE GITHUB

Setelah repository dibuat, GitHub akan memberikan URL. Ada 2 pilihan:

### Option A: HTTPS (Recommended untuk Windows)
```bash
git remote add origin https://github.com/USERNAME/medikindo-po.git
git branch -M main
git push -u origin main
```

**Ganti `USERNAME` dengan username GitHub Anda!**

### Option B: SSH (jika sudah setup SSH key)
```bash
git remote add origin git@github.com:USERNAME/medikindo-po.git
git branch -M main
git push -u origin main
```

---

## ✅ LANGKAH 3: PUSH KE GITHUB

```bash
# Push ke GitHub
git push -u origin main
```

**Jika diminta username & password**:
- Username: username GitHub Anda
- Password: **Personal Access Token** (bukan password biasa!)

---

## 🔑 CARA BUAT PERSONAL ACCESS TOKEN

Jika belum punya token:

1. Buka https://github.com/settings/tokens
2. Klik **"Generate new token"** → **"Generate new token (classic)"**
3. Isi form:
   - **Note**: "Medikindo PO System"
   - **Expiration**: 90 days (atau sesuai kebutuhan)
   - **Scopes**: Centang **"repo"** (full control)
4. Klik **"Generate token"**
5. **COPY TOKEN** (hanya muncul sekali!)
6. Gunakan token ini sebagai password saat push

---

## 📊 VERIFIKASI

Setelah push berhasil, cek:

1. Buka repository di GitHub
2. Pastikan semua file sudah ada
3. Cek commit history
4. Cek branch (should be `main`)

---

## 🔄 PUSH PERUBAHAN SELANJUTNYA

Setelah setup awal, untuk push perubahan berikutnya:

```bash
# 1. Cek status
git status

# 2. Add files yang berubah
git add .

# 3. Commit dengan message
git commit -m "Your commit message here"

# 4. Push ke GitHub
git push
```

---

## 🚨 TROUBLESHOOTING

### Error: "remote origin already exists"
```bash
# Hapus remote lama
git remote remove origin

# Tambah remote baru
git remote add origin https://github.com/USERNAME/medikindo-po.git
```

### Error: "failed to push some refs"
```bash
# Pull dulu (jika ada perubahan di GitHub)
git pull origin main --allow-unrelated-histories

# Lalu push lagi
git push -u origin main
```

### Error: "Authentication failed"
- Pastikan menggunakan Personal Access Token, bukan password
- Generate token baru jika sudah expired

---

## 📝 CURRENT STATUS

✅ Git initialized  
✅ Initial commit created  
⏳ Remote origin (pending - need GitHub repo URL)  
⏳ Push to GitHub (pending)

---

## 🎯 NEXT STEPS

1. **Buat repository di GitHub** (via website atau CLI)
2. **Copy repository URL** dari GitHub
3. **Jalankan command**:
   ```bash
   git remote add origin https://github.com/USERNAME/medikindo-po.git
   git branch -M main
   git push -u origin main
   ```
4. **Verify** di GitHub website

---

**Prepared By**: System Engineer  
**Date**: April 14, 2026

---

**END OF GUIDE**
