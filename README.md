# Pyojek CMS — Platform Website Terpusat Kedinasan Kota Batu

Pyojek CMS adalah platform Content Management System (CMS) terpusat berbasis web untuk pembuatan, penyeragaman, dan pengelolaan website kedinasan di lingkungan Pemerintah Kota Batu yang diinisiasi oleh Diskominfo Kota Batu.

Konsep utama arsitektur sistem:

> **Single Global Template, Multi-Tenant Data**
> - **Super Admin** mengontrol struktur *global blueprint* template secara terpusat melalui *Visual Template Builder*.
> - **Admin Kedinasan** mengelola data konten operasional (Posts, Pages, Media, Appearance) khusus untuk dinas miliknya via formulir dashboard standar.
> - **Masyarakat** mengakses informasi publik secara read-only tanpa hak akses dashboard.
> - Struktur tata letak (layout) bersifat *fixed blueprint* yang seragam antar-dinas.

---

## 1. Persyaratan Sistem

- **PHP**: `>= 8.2` (disarankan PHP 8.3+)
- **Ekstensi PHP Wajib**: `pdo_sqlite` / `pdo_mysql`, `mbstring`, `fileinfo`, `gd` atau `imagick` (untuk optimasi gambar WebP), `zip` (untuk re-pack Office XML).
- **Composer**: `>= 2.2`
- **Node.js & npm**: Node.js `>= 18.x`, npm `>= 9.x`
- **Database**: SQLite (default lokal) atau MySQL / MariaDB

---

## 2. Panduan Instalasi & Menjalankan Proyek

### 2.1 Clone & Persiapan Lingkungan
```bash
# Masuk ke direktori proyek
cd cms_project

# Pasang dependensi PHP (Composer)
composer install

# Pasang dependensi JavaScript (npm)
npm install

# Buat berkas konfigurasi environment lokal
cp .env.example .env

# Generate Application Key
php artisan key:generate
```

### 2.2 Migrasi Database & Inisialisasi Akun Super Admin
```bash
# Jalankan seluruh migrasi skema tabel
php artisan migrate

# Buat akun Super Admin pertama secara aman & interaktif
php artisan make:super-admin
```

### 2.3 Menjalankan Server Pengembangan
Jalankan server Laravel dan build asset Vite secara simultan:

```bash
# Terminal 1: Laravel Web Server
php artisan serve

# Terminal 2: Vite Development Asset Watcher
npm run dev
```

Aplikasi dapat diakses melalui browser pada:  
- **Portal CMS**: `http://127.0.0.1:8000/login`

---

## 3. Manajemen Akun Pengguna

1. **Super Admin**: Dibuat melalui CLI perintah aman `php artisan make:super-admin` (kata sandi diinput interaktif tanpa tersimpan di repositori Git).
2. **Admin Kedinasan**: Dibuat dan dikelola secara terpusat oleh Super Admin melalui panel manajemen pengguna di dalam dashboard CMS (`/admin/users`).

---

## 4. Pengujian Otomatis (Automated Testing)

Proyek ini dilengkapi dengan cakupan unit dan feature test otomatis komprehensif:

```bash
# Menjalankan seluruh test suite otomatis
php artisan test

# Menjalankan test modul tertentu saja
php artisan test --filter=TemplateBuilderTest
php artisan test --filter=AdminDinasDashboardTest
php artisan test --filter=SuperAdminWebsiteTest
```

**Status Pengujian Saat Ini**: `138 Test / 769 Assertions (100% Passed)`.

---

## 5. Peta Indeks Dokumentasi Proyek

Untuk memahami seluruh spesifikasi dan arsitektur teknis secara mendalam, silakan merujuk pada berkas dokumentasi spesifik berikut:

| Berkas Dokumen | Deskripsi & Ruang Lingkup |
|---|---|
| [`PRD.md`](./PRD.md) | **Product Requirements Document**: Sasaran pengguna, batasan hak akses, modul fungsional, dan strict out-of-scope. |
| [`ROLES_RBAC.md`](./ROLES_RBAC.md) | **Role-Based Access Control**: Aturan otorisasi ketat, matriks permission, dan batasan tenant boundaries. |
| [`SCHEMA.md`](./SCHEMA.md) | **Database Schema Specification**: Struktur tabel relasional, foreign key, integritas data, dan LRS Canonical. |
| [`TECH_STACK.md`](./TECH_STACK.md) | **Spesifikasi Teknologi**: Detail framework Laravel, Blade, Tailwind CSS v4, SQLite/MySQL, dan pustaka terpasang. |
| [`TEMPLATE.md`](./TEMPLATE.md) | **Template Blueprint & Builder**: Aturan kanvas builder Super Admin, komponen baku, layout grid, dan spesifikasi data binding slot. |
| [`CONTENT_SPEC.md`](./CONTENT_SPEC.md) | **Spesifikasi Konten**: Pemodelan post, page hierarkis, pustaka media, dan appearance slot. |
| [`ARCHITECTURE.md`](./ARCHITECTURE.md) | **Arsitektur Sistem**: Desain Component Tree, pipeline optimasi berkas WebP, rate limiting, dan lifecycle request. |
| [`ROUTING.md`](./ROUTING.md) | **Katalog Rute & Hak Akses**: Pemetaan seluruh endpoint web, middleware rate limit, dan penanggung jawab controller. |
| [`SECURITY.md`](./SECURITY.md) | **Standar Keamanan Sistem**: Proteksi brute-force, isolasi tenant data, sanitasi XSS, dan validasi berkas unggahan. |
| [`PROGRESS.md`](./PROGRESS.md) | **Catatan Progres & Status Implementasi**: Log pembaruan fitur, status modul, dan verifikasi test suite. |

---

## 6. Hak Cipta & Lisensi

Platform ini dikembangkan untuk lingkungan **Pemerintah Kota Batu — Dinas Komunikasi dan Informatika**. Seluruh hak cipta dilindungi undang-undang.
