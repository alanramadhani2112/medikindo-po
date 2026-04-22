---
name: "medikindo-dev"
displayName: "Medikindo Dev Server"
description: "Panduan lengkap menjalankan dev server Medikindo PO System — Laravel 13 + Vite + MySQL. Mencakup setup awal, menjalankan semua service, troubleshooting, dan perintah-perintah harian."
keywords: ["medikindo", "laravel", "dev-server", "vite", "artisan"]
author: "Alan Ramadhani"
---

# Medikindo Dev Server

## Overview

Medikindo PO System adalah aplikasi procurement healthcare berbasis **Laravel 13** (PHP 8.3+) dengan frontend **Vite + Alpine.js**. Database menggunakan **MySQL**, queue dan session berbasis database.

Saat development, ada 4 service yang perlu berjalan bersamaan:
- **PHP dev server** — `php artisan serve` (port 8000)
- **Queue listener** — memproses background jobs
- **Pail** — log viewer real-time
- **Vite** — hot-reload frontend assets (port 5173)

---

## Onboarding

### Prerequisites

- PHP 8.3+
- MySQL 5.7+ (atau MariaDB)
- Node.js 16+
- Composer 2.0+
- Laragon / XAMPP / native MySQL

### Setup Awal (Fresh Install)

```bash
# 1. Install dependencies
composer install
npm install --ignore-scripts

# 2. Copy environment file
cp .env.example .env
php artisan key:generate

# 3. Buat database di MySQL, lalu set di .env:
#    DB_DATABASE=medikindo_po
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Jalankan migrasi + seeder
php artisan migrate:fresh --seed

# 5. Build frontend assets
npm run build
```

Atau pakai satu perintah (jika sudah ada .env):
```bash
composer run setup
```

### Konfigurasi .env Penting

```env
APP_NAME="Medikindo PO System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://medikindo-po.test   # atau http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medikindo_po
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

---

## Menjalankan Dev Server

### Cara Cepat — Semua Sekaligus

```bash
composer run dev
```

Perintah ini menjalankan 4 service secara paralel dengan warna berbeda di terminal:
- 🔵 `server` — `php artisan serve`
- 🟣 `queue` — `php artisan queue:listen --tries=1 --timeout=0`
- 🔴 `logs` — `php artisan pail --timeout=0`
- 🟠 `vite` — `npm run dev`

Akses aplikasi di: **http://localhost:8000**

### Cara Manual — Terminal Terpisah

Jika `composer run dev` bermasalah, jalankan masing-masing di terminal berbeda:

```bash
# Terminal 1 — PHP Server
php artisan serve

# Terminal 2 — Queue Worker
php artisan queue:listen --tries=1 --timeout=0

# Terminal 3 — Log Viewer
php artisan pail --timeout=0

# Terminal 4 — Vite (hot reload)
npm run dev
```

### URL Akses

| Service | URL |
|---------|-----|
| Web App | http://localhost:8000 |
| Vite HMR | http://localhost:5173 |
| API | http://localhost:8000/api |

---

## Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | alanramadhani21@gmail.com | Medikindo@2026! |
| Healthcare | budi.santoso@testhospital.com | Healthcare@2026! |
| Approver | siti.nurhaliza@medikindo.com | Approver@2026! |
| Finance | ahmad.hidayat@medikindo.com | Finance@2026! |

---

## Perintah Harian

### Database

```bash
# Reset total + seed ulang
php artisan migrate:fresh --seed

# Seed ulang tanpa reset
php artisan db:seed

# Seed spesifik
php artisan db:seed --class=MasterDataSeeder
php artisan db:seed --class=CleanUserSeeder
php artisan db:seed --class=RolePermissionSeeder

# Tinker (interactive shell)
php artisan tinker
```

### Cache

```bash
# Clear semua cache sekaligus
php artisan optimize:clear

# Clear spesifik
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Testing

```bash
# Jalankan semua test
php artisan test

# Test spesifik
php artisan test --filter=NamaTest

# Clear config sebelum test (recommended)
php artisan config:clear && php artisan test
```

### Frontend

```bash
# Build untuk production
npm run build

# Dev dengan hot reload
npm run dev
```

---

## Troubleshooting

### Error: "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Error: "SQLSTATE[HY000] [1049] Unknown database"
Pastikan database `medikindo_po` sudah dibuat di MySQL:
```sql
CREATE DATABASE medikindo_po CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Error: "Class not found" atau autoload issues
```bash
composer dump-autoload
```

### Error: "Vite manifest not found"
Frontend belum di-build. Jalankan:
```bash
npm run build
# atau untuk dev:
npm run dev
```

### Queue jobs tidak diproses
Pastikan queue listener berjalan:
```bash
php artisan queue:listen --tries=1 --timeout=0
```
Cek failed jobs:
```bash
php artisan queue:failed
php artisan queue:retry all
```

### Error 419 (CSRF Token Mismatch)
```bash
php artisan config:clear
php artisan cache:clear
```

### Permission denied (storage/bootstrap/cache)
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache

# Windows (Laragon) — biasanya tidak perlu, tapi jika perlu:
# Pastikan folder storage/ dan bootstrap/cache/ writable
```

### Port 8000 sudah dipakai
```bash
php artisan serve --port=8001
```

---

## Struktur Singkat

```
app/
├── Http/Controllers/
│   ├── Api/        # REST API controllers
│   └── Web/        # Web (Blade) controllers
├── Models/         # Eloquent models
├── Services/       # Business logic
├── States/         # State machines
└── Enums/          # Status enums

resources/views/    # Blade templates
routes/
├── web.php         # Web routes
└── api.php         # API routes
database/
├── migrations/
└── seeders/
```
