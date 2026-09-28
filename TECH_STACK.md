# TECH_STACK — Pyojek CMS

**Dokumen Spesifikasi Teknologi**  
**Produk:** Pyojek CMS — Content Management System Website Dinas  
**Status:** CONFIRMED Baseline Teknis  
**Versi:** 1.1  
**Tanggal:** 28 September 2026  

---

## 1. Tujuan Dokumen
Dokumen ini mendokumentasikan keputusan teknologi Pyojek CMS yang diterapkan secara aktual pada source code proyek, meliputi bahasa pemrograman, framework, database, frontend, sistem autentikasi, otorisasi, media optimization, dan keamanan.

---

## 2. Ringkasan Tech Stack

| Layer | Teknologi | Status | Catatan Implementasi Aktual |
| :--- | :--- | :--- | :--- |
| **Backend Language** | PHP (^8.3) | CONFIRMED | Bahasa pemrograman utama backend. |
| **Backend Framework**| Laravel (v13.x) | CONFIRMED | Framework utama (`laravel/framework: ^13.17`). |
| **Architecture** | MVC + Dedicated Service Layer | CONFIRMED | Model, Controller, Service Layer (`CanvasRenderer`, `MediaOptimizerService`). |
| **Authentication** | Laravel Session Auth | CONFIRMED | Session-based (`/login`, `/logout`), password hashing Bcrypt. |
| **Authorization** | Middleware + Tenant Ownership | CONFIRMED | Middleware `EnsureSuperAdmin` (`super_admin`) & validasi `dinas_id`/`website_id` di controller. |
| **Database** | SQLite (Dev) / Relational SQL | CONFIRMED | SQLite baseline lokal; didukung 21 file migrasi relasional standar. |
| **Frontend UI** | Laravel Blade Components | CONFIRMED | Antarmuka dashboard internal dan website publik berbasis Blade native. |
| **CSS Framework** | Tailwind CSS v4 | CONFIRMED | `@tailwindcss/vite` (^4.0.0) + Inter Font typography. |
| **Drag-and-Drop** | SortableJS (^1.15.7) | CONFIRMED | Interaksi kanvas visual template builder Super Admin. |
| **File Storage** | Laravel Storage (Local/Public) | CONFIRMED | Penyimpanan aset gambar dan dokumen publik pada direktori `storage/app/public`. |
| **Media Optimizer** | GD Extension & ZipArchive | CONFIRMED | Konversi otomatis gambar ke WebP (85%), kompresi deflate Office, dan optimasi stream PDF. |
| **Security & Throttle**| Laravel RateLimiter | CONFIRMED | 4 limit: `login` (5/m), `public-site` (120/m), `cms-read` (120/m), `cms-write` (40/m). |
| **Automated Testing** | PHPUnit (^12.5) | CONFIRMED | 145 feature tests & unit tests dengan 835 assertions lulus 100%. |

---

## 3. Backend & Arsitektur

### 3.1 PHP & Laravel
- **PHP ^8.3:** Menangani seluruh alur *request lifecycle*, *business rules*, otorisasi tenant, dan pengolahan berkas.
- **Laravel Framework ^13.17:**
  - Routing terpusat di `routes/web.php` dan `routes/console.php`.
  - Middleware alias `super_admin` (`EnsureSuperAdmin`) dan throttling terdaftar di `bootstrap/app.php` & `AppServiceProvider`.
  - Eloquent ORM dengan relasi 1:1, 1:N, dan relasi berjenjang (nested parent-child).

### 3.2 Pola Arsitektur
Pola dasar menggunakan MVC Laravel dengan penambahan Service Layer untuk domain logic yang kompleks:
```text
Browser / Client
      ↓
    Route (web.php)
      ↓
  Middleware (auth, super_admin, throttle)
      ↓
  Controller (Admin / Dinas / Public)
      ↓
  Service Layer:
    ├── CanvasRenderer        → Resolusi slot & perakitan pohon HTML template
    └── MediaOptimizerService → Optimasi kompresi WebP & deflate dokumen
      ↓
    Model (Eloquent ORM)
      ↓
  Database (SQLite)
```

---

## 4. Frontend & Rendering

### 4.1 Antarmuka Berbasis Blade Components
Aplikasi memiliki pemisahan tegas antara area internal CMS dan website publik:
1. **CMS Internal (Super Admin & Admin Kedinasan):**
   - Menggunakan layout sidebar tetap berlatar gelap (`#0f172a`) dan topbar putih.
   - Dilengkapi developer credit terstandarisasi: `"Developed by Azzaryansyaa"` pada sidebar bawah dan footer.
2. **Website Publik:**
   - Dirender secara dinamis oleh `CanvasRenderer` berdasarkan payload kanvas template aktif dan data tenant dinas terkait.
   - Menggunakan rute slug path `/site/{identifier}`.
   - Tidak memuat elemen dashboard internal maupun developer credit.

### 4.2 Styling & Interaktivitas
- **Tailwind CSS v4:** Styling modern dengan palet warna enterprise (Navy `#0f172a`, Slate `#f8fafc`, Blue `#2563eb`).
- **SortableJS:** Menggerakkan elemen visual drag-and-drop pada Template Builder Super Admin.
- **AJAX Endpoint:** `PUT /admin/templates/{template}/builder` merespons payload JSON untuk penyimpanan kanvas builder secara real-time.

---

## 5. Media & File Optimization

Aplikasi menyertakan `MediaOptimizerService` yang aktif otomatis saat berkas diunggah:
1. **Gambar Raster (`.jpg`, `.jpeg`, `.png`, `.webp`):**
   - Otomatis dikonversi ke format `.webp` dengan kualitas 85% menggunakan pustaka PHP GD (`imagewebp`).
   - Saluran transparansi Alpha dipertahankan.
   - Menghemat ruang penyimpanan server rata-rata 40% – 80%.
2. **Dokumen Perkantoran (`.docx`, `.xlsx`, `.pptx`):**
   - Dideteksi sebagai arsip Open XML dan dikompresi ulang menggunakan `ZipArchive` dengan tingkat kompresi `CM_DEFLATE` Level 9.
3. **Dokumen PDF (`.pdf`):**
   - Dilakukan optimasi stream PDF uncompressed untuk meminimalkan ukuran berkas tanpa merusak integritas dokumen.

---

## 6. Keamanan & Rate Limiting

### 6.1 Autentikasi & RBAC
- Proteksi route internal menggunakan middleware `auth` dan status check `status === 'aktif'`.
- Middleware `super_admin` (`EnsureSuperAdmin`) membatasi modul Super Admin (Users, Websites, Templates, Settings, Activities).
- Tenant ownership check diterapkan pada level query controller (`$website->id === $resource->website_id`), bukan mengandalkan frontend.

### 6.2 Rate Limiting (Anti Brute-Force & Abuse)
Dikonfigurasi di `AppServiceProvider`:
1. `login`: Maksimal 5 percobaan per menit per kombinasi `IP + Email`. Jika terlampaui, mengembalikan response HTTP 429 kustom (`views/errors/429.blade.php`).
2. `public-site`: Maksimal 120 request per menit per IP untuk melindungi website dinas dari scraping agresif.
3. `cms-read`: Maksimal 120 request per menit per user/IP pada dashboard internal.
4. `cms-write`: Maksimal 40 request per menit per user/IP untuk mencegah spam operasi form/upload.
