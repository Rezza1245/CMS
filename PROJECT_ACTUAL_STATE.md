# PROJECT ACTUAL STATE — PYOJEK CMS

> **Dokumen Status Aktual Sistem**  
> **Tanggal Analisis:** 28 September 2026  
> **Metode:** Analisis Statis Source Code, Skema Database, Routing, Middleware, Controller, Service Layer, dan Pengujian Otomatis.  
> **Prinsip Utama:** Source code dan konfigurasi aktual adalah satu-satunya sumber kebenaran (*single source of truth*).

---

## 1. Project Overview

- **Nama Project:** Pyojek CMS (berdasarkan `composer.json`, `PRD.md`, dan teks antarmuka navigasi).
- **Tujuan Aplikasi:** Centralized Web-based Content Management System (CMS) untuk pembuatan dan pengelolaan website resmi dinas/instansi di lingkungan Pemerintah Kota Batu dengan konsep *Single Global Template, Multi-Tenant Data*.
- **Bahasa Pemrograman:** PHP `^8.3` (aktual runtime: PHP 8.3/8.4).
- **Backend Framework:** Laravel Framework `v13.17` (`laravel/framework: ^13.17`).
- **Frontend Framework / UI:** Native Laravel Blade Components + Tailwind CSS `v4.0.0` (via `@tailwindcss/vite`) + SortableJS `v1.15.7`.
- **Database Engine:** SQLite (pengembangan lokal, `database/database.sqlite`), dikelola melalui 21 berkas migrasi relasional standar Laravel Eloquent.
- **Library / Dependencies Penting:**
  - `sortablejs` (^1.15.7): Engine drag-and-drop kanvas visual builder.
  - `php-gd` (ekstensi GD): Pengolahan gambar dan kompresi WebP.
  - `zip` (ZipArchive): Kompresi deflate dokumen Office Open XML.
  - `filament/filament` (^5.7): Terpasang di `composer.json` dengan provider terdaftar, namun **tidak digunakan untuk antarmuka CMS** (seluruh UI menggunakan Blade kustom).
- **Struktur Aplikasi Secara Umum:**
  Monolithic MVC (Model-View-Controller) Laravel dengan pemisahan tegas antara panel administrasi internal (`/admin/*`, `/dinas/*`) dan website publik (`/site/*`). Dilengkapi Service Layer (`CanvasRenderer` & `MediaOptimizerService`).

---

## 2. Project Structure

```text
cms_project/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # 12 Controller backend (Auth, Super Admin, Admin Dinas, Public)
│   │   └── Middleware/           # EnsureSuperAdmin.php
│   ├── Models/                   # 10 Model Eloquent (User, Dinas, Website, Template, Post, Page, Media, Appearance, Setting, SystemSetting)
│   ├── Providers/                # AppServiceProvider (Rate Limiting) & Filament/AdminPanelProvider (idle)
│   ├── Rules/                    # ValidCanvasData.php (Validasi AST JSON Canvas Builder)
│   └── Services/                 # CanvasRenderer.php & MediaOptimizerService.php
├── bootstrap/                    # app.php (Inisialisasi aplikasi, alias middleware, exception handling)
├── config/                       # Konfigurasi Laravel (auth, database, filesystems, session, dll)
├── database/
│   ├── factories/                # UserFactory.php
│   ├── migrations/               # 21 Berkas migrasi database relasional
│   └── seeders/                  # DatabaseSeeder.php (Master data baseline)
├── public/                       # Entry point (index.php), aset build Vite, storage symlink
├── resources/
│   ├── css/                      # app.css (Tailwind CSS v4 entrypoint)
│   ├── js/                       # app.js
│   └── views/
│       ├── admin/                # Antarmuka Super Admin (Dashboard, Users, Websites, Settings, Activities, Templates)
│       ├── auth/                 # Halaman login (login.blade.php)
│       ├── components/           # Blade components: public/navbar & builder/* (hero, posts, static, media, footer, container)
│       ├── dinas/                # Antarmuka Admin Kedinasan (Dashboard, Posts, Pages, Media, Appearance, Settings)
│       ├── errors/               # Halaman error khusus (429.blade.php)
│       └── public/               # Layout dan view website publik (index, page, post, maintenance)
├── routes/
│   ├── console.php               # Artisan command route
│   └── web.php                   # Definisi seluruh rute aplikasi (Web, Auth, CMS, Public)
├── storage/                      # File storage (app/public untuk berkas media, logs, cache)
└── tests/
    └── Feature/                  # 18 Berkas pengujian otomatis (145 tests, 835 assertions)
```

---

## 3. User Roles

Berdasarkan enum/validasi di `app/Models/User.php`, `AdminUserController.php`, dan `database/migrations/2026_09_15_000002_add_role_and_fields_to_users_table.php`:

### 1. `super_admin`
- **Cara Ditentukan:** Nilai kolom `users.role = 'super_admin'`.
- **Akses:** Global platform level.
- **Halaman yang Dapat Diakses:**
  - Dashboard Super Admin (`/admin/dashboard`)
  - Template Canvas Builder (`/admin/templates/{id}/builder`)
  - Template Preview Mode (`/admin/templates/{id}/preview`)
  - Manajemen Pengguna (`/admin/users/*`)
  - Manajemen Website Dinas (`/admin/websites/*`)
  - Konfigurasi Sistem Global (`/admin/settings`)
  - Platform Activity Logs (`/admin/activities`)
- **Middleware:** `auth`, `super_admin` (`EnsureSuperAdmin`), `throttle:cms-read`, `throttle:cms-write`.
- **Proteksi Khusus:** Dilarang menghapus akun sendiri, menonaktifkan akun sendiri, atau mengubah rolenya sendiri.

### 2. `admin_dinas`
- **Cara Ditentukan:** Nilai kolom `users.role = 'admin_dinas'` dan wajib memiliki `users.dinas_id` yang valid.
- **Akses:** Terisolasi ketat pada dinas yang terikat (`dinas_id` → `website_id`).
- **Halaman yang Dapat Diakses:**
  - Dashboard Admin Kedinasan (`/dinas/dashboard` atau `/admin/dashboard` dialihkan otomatis)
  - Manajemen Posts / Berita (`/dinas/posts/*`)
  - Manajemen Media / Dokumen (`/dinas/media/*`)
  - Manajemen Halaman Statis / Pages (`/dinas/pages/*`)
  - Manajemen Appearance Slots (`/dinas/appearance`)
  - Pengaturan Website Dinas (`/dinas/settings`)
- **Middleware:** `auth`, `throttle:cms-read`, `throttle:cms-write`.
- **Proteksi Khusus:** Ditolak dengan kode HTTP 403 (*Forbidden*) jika mencoba mengakses rute `/admin/*` atau resource dinas lain.

### 3. Masyarakat / Pengunjung Publik
- **Cara Ditentukan:** Pengguna non-autentikasi (tamu / *guest*).
- **Akses:** Read-only pada rute publik website dinas:
  - Beranda Dinas (`/site/{identifier}`)
  - Halaman Statis (`/site/{identifier}/page/{slug}`)
  - Detail Artikel Post (`/site/{identifier}/post/{slug}`)
- **Middleware:** `throttle:public-site`.

---

## 4. Authentication & Authorization

### Implementasi Aktual:
- **Login:** `POST /login` (`AuthController@login`). Memverifikasi `email` dan `password` (Bcrypt hash), serta memastikan `status === 'aktif'`. Akun `nonaktif` ditolak masuk.
- **Logout:** `POST /logout` (`AuthController@logout`). Menghapus session, melakukan `invalidate()` dan `regenerateToken()`.
- **Register Publik:** **Tidak Ditemukan / Sengaja Ditiadakan** (*Strict Out-of-Scope*). Akun hanya dapat dibuat oleh Super Admin melalui panel internal.
- **Session Auth:** Driver session berbasis file/database bawaan Laravel (`config/auth.php`). Tidak menggunakan JWT.
- **Protected Routes:** Menggunakan middleware bawaan `auth` yang membungkus seluruh rute `/admin/*` dan `/dinas/*`.
- **Role Authorization:** Menggunakan middleware `App\Http\Middleware\EnsureSuperAdmin` (`alias: super_admin`) pada seluruh grup rute Super Admin.
- **Tenant Authorization:** Ditegakkan langsung pada level query di seluruh Controller Admin Dinas (`$resource->website_id === $website->id` atau membatasi query `$website->pages()`, `$website->posts()`, dll).

---

## 5. Frontend

### Tabel Halaman & Fitur Frontend Aktual

| Halaman / Fitur | Route | Role | Status | Bukti Implementasi |
| :--- | :--- | :--- | :--- | :--- |
| **Login Form** | `/login` | Guest | Implemented | `resources/views/auth/login.blade.php` |
| **Dashboard Super Admin** | `/admin/dashboard` | `super_admin` | Implemented | `resources/views/admin/dashboard.blade.php` |
| **Template Builder** | `/admin/templates/{id}/builder` | `super_admin` | Implemented | `resources/views/admin/templates/builder.blade.php` |
| **Template Preview Mode** | `/admin/templates/{id}/preview` | `super_admin` | Implemented | `resources/views/admin/templates/preview.blade.php` |
| **User Index** | `/admin/users` | `super_admin` | Implemented | `resources/views/admin/users/index.blade.php` |
| **User Create Form** | `/admin/users/create` | `super_admin` | Implemented | `resources/views/admin/users/create.blade.php` |
| **User Edit Form** | `/admin/users/{id}/edit` | `super_admin` | Implemented | `resources/views/admin/users/edit.blade.php` |
| **Website Dinas Index** | `/admin/websites` | `super_admin` | Implemented | `resources/views/admin/websites/index.blade.php` |
| **Website Dinas Create Form**| `/admin/websites/create` | `super_admin` | Implemented | `resources/views/admin/websites/create.blade.php` |
| **Website Dinas Edit Form** | `/admin/websites/{id}/edit` | `super_admin` | Implemented | `resources/views/admin/websites/edit.blade.php` |
| **Global Settings Form** | `/admin/settings` | `super_admin` | Implemented | `resources/views/admin/settings.blade.php` |
| **Platform Activity Logs** | `/admin/activities` | `super_admin` | Implemented | `resources/views/admin/activities/index.blade.php` |
| **Dashboard Admin Dinas** | `/dinas/dashboard` | `admin_dinas` | Implemented | `resources/views/dinas/dashboard.blade.php` |
| **Posts Index** | `/dinas/posts` | `admin_dinas` | Implemented | `resources/views/dinas/posts/index.blade.php` |
| **Post Create Form** | `/dinas/posts/create` | `admin_dinas` | Implemented | `resources/views/dinas/posts/create.blade.php` |
| **Post Edit Form** | `/dinas/posts/{id}/edit` | `admin_dinas` | Implemented | `resources/views/dinas/posts/edit.blade.php` |
| **Media Index & Upload** | `/dinas/media` | `admin_dinas` | Implemented | `resources/views/dinas/media/index.blade.php` |
| **Pages Index** | `/dinas/pages` | `admin_dinas` | Implemented | `resources/views/dinas/pages/index.blade.php` |
| **Page Create Form** | `/dinas/pages/create` | `admin_dinas` | Implemented | `resources/views/dinas/pages/create.blade.php` |
| **Page Edit Form** | `/dinas/pages/{id}/edit` | `admin_dinas` | Implemented | `resources/views/dinas/pages/edit.blade.php` |
| **Appearance Slots Form** | `/dinas/appearance` | `admin_dinas` | Implemented | `resources/views/dinas/appearance.blade.php` |
| **Website Settings Form** | `/dinas/settings` | `admin_dinas` | Implemented | `resources/views/dinas/settings.blade.php` |
| **Website Publik Beranda** | `/site/{identifier}` | Publik | Implemented | `resources/views/public/index.blade.php` |
| **Website Publik Statis** | `/site/{identifier}/page/{slug}` | Publik | Implemented | `resources/views/public/page.blade.php` |
| **Website Publik Post** | `/site/{identifier}/post/{slug}` | Publik | Implemented | `resources/views/public/post.blade.php` |
| **Layar Pemeliharaan (503)** | `/site/{identifier}` | Publik (Saat Maintenance) | Implemented | `resources/views/public/maintenance.blade.php` |

---

## 6. Backend

### Tabel Rute & Endpoint HTTP Aktual

| Method | Endpoint | Fungsi | Auth | Role | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | Redirect ke `/login` | No | Any | Implemented |
| `GET` | `/login` | Tampilkan form login | Guest | Guest | Implemented |
| `POST` | `/login` | Autentikasi sesi pengguna | Guest | Guest | Implemented |
| `POST` | `/logout` | Logout sesi pengguna | Yes | Authenticated | Implemented |
| `GET` | `/admin/dashboard` | Dashboard Super Admin | Yes | `super_admin` / redirect | Implemented |
| `GET` | `/admin/templates/{template}/builder` | Buka visual builder kanvas | Yes | `super_admin` | Implemented |
| `PUT` | `/admin/templates/{template}/builder` | Simpan payload JSON kanvas | Yes | `super_admin` | Implemented |
| `GET` | `/admin/templates/{template}/preview` | Pratinjau bersih template | Yes | `super_admin` | Implemented |
| `GET` | `/admin/users` | Daftar seluruh pengguna sistem | Yes | `super_admin` | Implemented |
| `GET` | `/admin/users/create` | Form tambah pengguna | Yes | `super_admin` | Implemented |
| `POST` | `/admin/users` | Simpan pengguna baru | Yes | `super_admin` | Implemented |
| `GET` | `/admin/users/{user}/edit` | Form ubah pengguna | Yes | `super_admin` | Implemented |
| `PUT` | `/admin/users/{user}` | Simpan pembaruan pengguna | Yes | `super_admin` | Implemented |
| `DELETE` | `/admin/users/{user}` | Hapus pengguna | Yes | `super_admin` | Implemented |
| `GET` | `/admin/websites` | Daftar website dinas | Yes | `super_admin` | Implemented |
| `GET` | `/admin/websites/create` | Form pendaftaran website dinas | Yes | `super_admin` | Implemented |
| `POST` | `/admin/websites` | Simpan dinas & website baru | Yes | `super_admin` | Implemented |
| `GET` | `/admin/websites/{website}/edit` | Form ubah website dinas | Yes | `super_admin` | Implemented |
| `PUT` | `/admin/websites/{website}` | Simpan pembaruan website dinas | Yes | `super_admin` | Implemented |
| `DELETE` | `/admin/websites/{website}` | Hapus website & dinas terkait | Yes | `super_admin` | Implemented |
| `GET` | `/admin/settings` | Form pengaturan sistem global | Yes | `super_admin` | Implemented |
| `PUT` | `/admin/settings` | Simpan pengaturan sistem global | Yes | `super_admin` | Implemented |
| `GET` | `/admin/activities` | Log aktivitas platform teragregasi | Yes | `super_admin` | Implemented |
| `GET` | `/dinas/dashboard` | Dashboard Admin Kedinasan | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/posts` | Daftar artikel postingan dinas | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/posts/create` | Form tambah postingan | Yes | `admin_dinas` | Implemented |
| `POST` | `/dinas/posts` | Simpan postingan baru | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/posts/{post}/edit` | Form ubah postingan | Yes | `admin_dinas` | Implemented |
| `PUT` | `/dinas/posts/{post}` | Simpan pembaruan postingan | Yes | `admin_dinas` | Implemented |
| `DELETE` | `/dinas/posts/{post}` | Hapus postingan | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/media` | Galeri media & dokumen dinas | Yes | `admin_dinas` | Implemented |
| `POST` | `/dinas/media` | Unggah media/dokumen baru | Yes | `admin_dinas` | Implemented |
| `DELETE` | `/dinas/media/{media}` | Hapus berkas media | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/pages` | Daftar halaman institusi dinas | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/pages/create` | Form tambah halaman baru | Yes | `admin_dinas` | Implemented |
| `POST` | `/dinas/pages` | Simpan halaman baru | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/pages/{page}/edit` | Form ubah halaman | Yes | `admin_dinas` | Implemented |
| `PUT` | `/dinas/pages/{page}` | Simpan pembaruan halaman | Yes | `admin_dinas` | Implemented |
| `DELETE` | `/dinas/pages/{page}` | Hapus halaman | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/appearance` | Form penataan slot appearance | Yes | `admin_dinas` | Implemented |
| `PUT` | `/dinas/appearance` | Simpan perubahan slot appearance | Yes | `admin_dinas` | Implemented |
| `GET` | `/dinas/settings` | Form pengaturan website dinas | Yes | `admin_dinas` | Implemented |
| `PUT` | `/dinas/settings` | Simpan pengaturan website dinas | Yes | `admin_dinas` | Implemented |
| `GET` | `/site/{identifier}` | Beranda website publik dinas | No | Public | Implemented |
| `GET` | `/site/{identifier}/page/{slug}`| Halaman statis publik | No | Public | Implemented |
| `GET` | `/site/{identifier}/post/{slug}`| Halaman artikel berita publik | No | Public | Implemented |

---

## 7. Database

### Diagram Relasi Aktual (ERD Ringkas)
```text
  dinas (1) ────────── (1) websites (1) ────────── (N) posts
    │                       │   │
    │ (1)                   │   ├─────────────── (N) pages ──┐ (1:N self-ref)
    │                       │   │                            └── pages.parent_id
    ▼ (N)                   │   ├─────────────── (N) media
  users                     │   │
    │                       │   ├─────────────── (1) appearances
    └─────────── (N) posts  │   │
    └─────────── (N) media  │   └─────────────── (1) settings
                            │
  templates (1) ────────────┘

  system_settings (Tabel konfigurasi key-value independen global)
```

### Rincian Tabel Aktual

1. **`dinas`**: `id` (PK), `name` (string), `code` (string unique), `address` (text null), `contact_email` (string null), `phone` (string null), `created_at`, `updated_at`.
2. **`users`**: `id` (PK), `dinas_id` (FK null), `name` (string), `code` (string null), `email` (string unique), `password` (string), `role` (string: super_admin, admin_dinas), `status` (string: aktif, nonaktif), `created_at`, `updated_at`.
3. **`templates`**: `id` (PK), `name` (string), `canvas_data` (json), `template_version` (string), `header_structure` (string), `post_layout` (string), `page_layout` (string), `navigation_structure` (string), `status` (string), `created_at`, `updated_at`.
4. **`websites`**: `id` (PK), `dinas_id` (FK unique cascade), `template_id` (FK restrict), `domain` (string unique), `name` (string), `status` (string: aktif, pemeliharaan, nonaktif), `created_at`, `updated_at`.
5. **`settings`**: `id` (PK), `website_id` (FK unique cascade), `general_config` (json null), `media_config` (json null), `privacy_config` (json null), `system_config` (json null), `backup_config` (json null), `storage_config` (json null), `created_at`, `updated_at`.
6. **`system_settings`**: `id` (PK), `key` (string unique), `value` (text null), `created_at`, `updated_at`.
7. **`appearances`**: `id` (PK), `website_id` (FK unique cascade), `logo` (string null), `favicon` (string null), `hero_banner` (string null), `hero_description` (text null), `header_slogan` (string null), `footer_slogan` (string null), `footer_title` (string null), `footer_about_title` (string null), `footer_about_text` (text null), `primary_color` (string null), `secondary_color` (string null), `created_at`, `updated_at`.
8. **`pages`**: `id` (PK), `website_id` (FK cascade), `parent_id` (FK cascade null), `title` (string), `slug` (string), `content` (longText), `image` (string null), `direct_link` (string null), `status` (string: draft, published), `placement` (string), `created_at`, `updated_at`. Unique index: `[website_id, slug]`.
9. **`posts`**: `id` (PK), `website_id` (FK cascade), `page_id` (FK nullOnDelete null), `user_id` (FK restrict), `title` (string), `slug` (string), `content` (text), `type` (string: Berita, Pengumuman, Kegiatan), `status` (string: draft, published), `placement` (string), `image` (string null), `direct_link` (string null), `published_at` (timestamp null), `created_at`, `updated_at`. Unique index: `[website_id, slug]`.
10. **`media`**: `id` (PK), `website_id` (FK cascade), `user_id` (FK restrict), `file_name` (string), `file_path` (string), `file_type` (string), `file_size` (unsignedBigInt), `created_at`, `updated_at`.

---

## 8. CRUD Features

| Entity | Create | Read | Update | Delete | Status | Keterangan / Sumber |
| :--- | :---: | :---: | :---: | :---: | :--- | :--- |
| **User (Super Admin)** | ✓ | ✓ | ✓ | ✓ | **Implemented** | `AdminUserController` |
| **Website Dinas** | ✓ | ✓ | ✓ | ✓ | **Implemented** | `AdminWebsiteController` (Transaksi DB bersama entitas Dinas) |
| **Global Template** | - | ✓ | ✓ | - | **Implemented** | `TemplateBuilderController` (Update struktur AST kanvas via JSON) |
| **System Settings** | - | ✓ | ✓ | - | **Implemented** | `AdminSettingController` (Key-value platform) |
| **Activity Logs** | - | ✓ | - | - | **Implemented** | `AdminActivityController` (Read-only agregator) |
| **Posts (Berita/Artikel)** | ✓ | ✓ | ✓ | ✓ | **Implemented** | `DinasPostController` (Otomasi WebP & Media) |
| **Media Publik** | ✓ | ✓ | - | ✓ | **Implemented** | `DinasMediaController` (Upload & Hapus fisik berkas) |
| **Pages (Halaman Statis)**| ✓ | ✓ | ✓ | ✓ | **Implemented** | `DinasPageController` (Hierarki 4 level & penataan Beranda) |
| **Appearance Slots** | - | ✓ | ✓ | - | **Implemented** | `DinasAppearanceController` (Pengisian slot template) |
| **Website Settings** | - | ✓ | ✓ | - | **Implemented** | `DinasSettingController` (Konfigurasi umum & privasi) |

---

## 9. File Upload

- **Mekanisme Upload:** Ditangani oleh `App\Services\MediaOptimizerService`.
- **Lokasi Penyimpanan:** Direktori `storage/app/public/` (`media/`, `posts/`, `pages/`, `hero/`, `logos/`) yang dipublikasikan via symlink `public/storage`.
- **Jenis Berkas:**
  - Gambar: `.jpg`, `.jpeg`, `.png`, `.webp`. Otomatis dikompresi menjadi `.webp` berkualitas 85% via GD.
  - Dokumen Office: `.docx`, `.xlsx`, `.pptx`. Dikompresi ulang dengan `ZipArchive` Deflate level 9.
  - Dokumen PDF: `.pdf`. Dioptimasi stream internalnya.
- **Pencatatan Database:** Setiap berkas yang diunggah tercatat pada tabel `media` (`website_id`, `user_id`, `file_name`, `file_path`, `file_type`, `file_size`).
- **Batasan Ukuran Berkas:**
  - Modul Media Publik: Maksimal 10 MB (`10240 KB`).
  - Thumbnail Post & Gambar Page: Maksimal 2 MB (`2048 KB`).
  - Hero Banner: Maksimal 5 MB (`5120 KB`).
  - Logo Dinas: Maksimal 2 MB (`2048 KB`).
- **Hak Akses:** Khusus Admin Kedinasan yang terautentikasi dan terikat dinas terkait.

---

## 10. CMS / Website Management

- **Manajemen Website Dinas:** Super Admin membuat dinas dan website secara transaksional (`DB::transaction`). Status website: `aktif`, `pemeliharaan`, `nonaktif`.
- **Mode Pemeliharaan (Maintenance Mode):** Ketika website disetel `pemeliharaan`, publik menerima respons HTTP 503 dengan halaman pemeliharaan kustom. Admin Dinas pemilik dan Super Admin tetap dapat mengakses pratinjau.
- **Template Management:** Super Admin menyusun kanvas kanvas visual berbasis `SortableJS` dengan 6 komponen bawaan: `Container`, `Hero`, `Posts Grid`, `Static Content`, `Media / Document List`, dan `Footer`.
- **Template Preview:** Tersedia endpoint mandiri `/admin/templates/{template}/preview` untuk melihat hasil builder tanpa data dinas.
- **Isolasi Slot (Slot Locking):** Super Admin dapat mengunci slot data pada builder (`editable_by: 'super_admin'`). Backend Appearance Admin Kedinasan mematuhi batasan ini.

---

## 11. UI / Navigation

- **Sidebar Internal CMS:**
  - Super Admin: Dashboard, Template, Website Dinas, User, Settings.
  - Admin Kedinasan: Dashboard, Posts, Media, Pages, Appearance, Settings.
  - Bagian bawah sidebar di seluruh panel internal memuat developer credit `"Developed by Azzaryansyaa"`.
- **Navbar Publik:**
  - Menampilkan brand logo dan nama dinas.
  - Navigasi dinamis bertingkat hingga **4 level kedalaman** (Menu Utama Header → Sub-menu → Wadah Sub-sub-bab → Isi Wadah).
  - Halaman Level 4 (isi wadah) dialihkan otomatis (*redirect*) ke halaman induk Level 3 saat diakses langsung.
- **Pengurutan Halaman:** Halaman berpenempatan `beranda` selalu muncul di baris teratas pada tabel manajemen Pages internal.

---

## 12. API ↔ Frontend Integration

- **Model Komunikasi:** Mayoritas form menggunakan HTTP POST/PUT standar dengan proteksi CSRF token (`@csrf`) dan pengalihan session dengan pesan kilat (*flash message*).
- **AJAX / Fetch API:** Digunakan secara khusus pada Template Builder Super Admin:
  - **Endpoint:** `PUT /admin/templates/{template}/builder`
  - **Client:** `fetch()` native JavaScript (`resources/views/admin/templates/builder.blade.php`).
  - **Header:** `X-CSRF-TOKEN: meta[csrf-token]`, `Content-Type: application/json`.
  - **Payload:** `{ canvas_data: { template_version: "1.0", canvas: [...] } }`.
  - **Respons:** JSON `{ message: "Template berhasil disimpan.", template: {...} }`.

---

## 13. Environment & Configuration

Berdasarkan `.env.example` dan berkas konfigurasi di `config/`:
- `APP_NAME`: `configured`
- `APP_ENV`: `local / production`
- `APP_KEY`: `configured`
- `APP_DEBUG`: `true / false`
- `APP_URL`: `configured` (contoh: `http://localhost:8000`)
- `DB_CONNECTION`: `sqlite` (baseline default)
- `DB_DATABASE`: `database/database.sqlite`
- `SESSION_DRIVER`: `database / file`
- `FILESYSTEM_DISK`: `local` (public symlink: `storage/app/public` → `public/storage`)
- `BCRYPT_ROUNDS`: `12`
- `RATE_LIMITING`: `configured` di `AppServiceProvider` (Login: 5/m, Public: 120/m, CMS Read: 120/m, CMS Write: 40/m).

---

## 14. Implemented Features

### Authentication & Authorization
- [x] Session login dengan validasi status aktif (`AuthController@login`).
- [x] Session logout dengan invalidasi token (`AuthController@logout`).
- [x] Proteksi rute via middleware `auth` dan `super_admin`.
- [x] Rate limiting anti brute-force login (5 req/menit dengan HTTP 429 kustom).
- [x] Tenant isolation query filtering pada seluruh operasi CRUD dinas.

### User Management (Super Admin)
- [x] CRUD akun pengguna (Super Admin & Admin Kedinasan).
- [x] Validasi email unik dan konfirmasi kata sandi.
- [x] Filter pencarian, role, dinas, dan status akun.
- [x] Proteksi diri Super Admin (cegah hapus/nonaktifkan/degradasi role akun sendiri).

### Website & Platform Management (Super Admin)
- [x] CRUD pendaftaran Website Dinas dengan transaksi DB relasi 1:1 ke Dinas.
- [x] Inisialisasi otomatis Appearance Slots default dan Website Settings saat website didaftarkan.
- [x] Konfigurasi sistem platform global (`max_upload_size_mb`, `allowed_media_types`).
- [x] Riwayat aktivitas platform teragregasi lintas entitas (`AdminActivityController`).

### Global Template & Builder (Super Admin)
- [x] Drag-and-drop kanvas visual builder berbasis SortableJS.
- [x] Struktur pohon bersarang: `Section → Container → Component`.
- [x] Dukungan 6 komponen sistem: Container, Hero, Posts Grid, Static Content, Media List, Footer.
- [x] Penyimpanan real-time via AJAX PUT JSON dengan aturan validasi `ValidCanvasData`.
- [x] Pratinjau responsif (Desktop, Tablet, Mobile) dan Undo/Redo history stack.
- [x] Rute khusus pratinjau template murni (`/admin/templates/{template}/preview`).

### Content Management (Admin Kedinasan)
- [x] CRUD Posts berkategori Berita, Pengumuman, dan Kegiatan.
- [x] CRUD Pages dengan hierarki bertingkat hingga 4 level dan penataan khusus Tab Beranda.
- [x] Pengurutan tabel Pages dengan Tab Beranda selalu di urutan teratas dan sub-menu draft sejajar sub-menu publish.
- [x] Pengalihan otomatis halaman Level 4 (isi wadah) ke wadah induknya.
- [x] Pustaka media dengan pengunggahan dokumen dan gambar teroptimasi.
- [x] Pengaturan Appearance Slots dengan penegakan hak kunci slot template.
- [x] Pengaturan operasional website dinas dan mode pemeliharaan (503).

### Optimasi & Keamanan Sistem
- [x] Layanan `MediaOptimizerService` (WebP kompresi 85%, ZIP deflate level 9, PDF stream deflation).
- [x] Layanan `CanvasRenderer` dengan sanitasi tag script/HTML berbahaya.
- [x] Rate Limiting tersegmentasi (Public Site, CMS Read, CMS Write).
- [x] Developer credit `"Developed by Azzaryansyaa"` di seluruh panel internal CMS.

---

## 15. Partially Implemented Features

1. **Kolom Konfigurasi Lanjutan Tabel `settings`:**
   - *Bagian yang Ada:* Tabel `settings` memiliki kolom JSON `media_config`, `system_config`, `backup_config`, `storage_config`.
   - *Bagian yang Belum Ada:* Form di `/dinas/settings` hanya mengelola `general_config` dan `privacy_config`. Kolom lainnya belum memiliki antarmuka pengisian.
   - *File Terkait:* `app/Models/Setting.php`, `DinasSettingController.php`.
2. **Package `filament/filament` di `composer.json`:**
   - *Bagian yang Ada:* Terdaftar di dependensi composer dan memiliki `AdminPanelProvider.php`.
   - *Bagian yang Belum Ada:* Tidak ada Filament Resource atau Page fungsional di `app/Filament/` karena CMS dibangun menggunakan Blade kustom.
   - *File Terkait:* `composer.json`, `app/Providers/Filament/AdminPanelProvider.php`.
3. **Resolusi Domain Website:**
   - *Bagian yang Ada:* Kolom `domain` tersimpan di database dan digunakan sebagai identifier unik.
   - *Bagian yang Belum Ada:* Akses hanya melalui slug path `/site/{identifier}`. Wildcard domain virtual host belum diimplementasikan.
   - *File Terkait:* `routes/web.php`, `PublicWebsiteController.php`.

---

## 16. Planned but Not Implemented (Berdasarkan Dokumen / Rencana Lama)

| Fitur | Dokumentasi Lama | Implementasi Aktual | Status |
| :--- | :--- | :--- | :--- |
| **Wildcard Subdomain Routing** | PRD & Routing konseptual (`dinas.batukota.go.id`) | Menggunakan rute slug path `/site/{identifier}` | **Planned/Documented Only** |
| **Automated Backup Engine** | SCHEMA `backup_config` konseptual | Belum ada scheduler/command pencadangan | **Planned/Documented Only** |
| **Eksternal REST API Publik** | TECH_STACK Section 11 konseptual | Tidak ada `routes/api.php` | **Planned/Documented Only** |

---

## 17. Inconsistencies (Ketidaksesuaian yang Ditemukan)

1. **Dependency Filament Tidak Digunakan:**
   `composer.json` menginstal `filament/filament: ^5.7`, namun tidak ada resource Filament aktif. Seluruh sistem berjalan pada custom Blade Controllers.
2. **Kolom Cadangan pada Tabel `settings`:**
   Skema tabel `settings` memuat kolom `media_config`, `system_config`, `backup_config`, dan `storage_config` yang saat ini tidak digunakan oleh controller mana pun.

---

## 18. Current System Flow

```text
Pengguna (Publik / Admin / Super Admin)
   │
   ├── Request Publik: GET /site/{identifier} (Throttle: 120 req/menit)
   │      ↓
   │   PublicWebsiteController
   │      ↓
   │   Cek Status Operasional (Jika Pemeliharaan → Tampilkan 503)
   │      ↓
   │   CanvasRenderer::render(Website)
   │      ↓
   │   Ambil AST Template JSON & Saring Data Tenant (Status = published)
   │      ↓
   │   Render Komponen Blade Publik
   │
   └── Request Internal CMS: /admin/* atau /dinas/* (Throttle: cms-read 120 / cms-write 40)
          ↓
       Middleware: auth & status === 'aktif'
          ↓
       Super Admin Route (/admin/*)?
          ├── YES → Middleware: EnsureSuperAdmin
          │            ↓
          │         Admin Controllers (Users, Websites, Templates, Settings, Activities)
          │
          └── NO (/dinas/*) → Dinas Controllers (Posts, Pages, Media, Appearance, Settings)
                               ↓
                            Tenant Isolation Check ($resource->website_id === $website->id)
                               ↓
                            Eksekusi Transaksi DB / MediaOptimizerService
                               ↓
                            Render View Internal CMS (dengan Developer Credit)
```

---

## 19. Current System Status

| Area | Status | Catatan |
| :--- | :--- | :--- |
| **Authentication** | **Implemented** | Session auth lengkap dengan status check dan rate limiting anti brute-force. |
| **Authorization & RBAC** | **Implemented** | Middleware `EnsureSuperAdmin` dan isolasi tenant per `website_id`/`dinas_id` di setiap controller. |
| **User Management** | **Implemented** | CRUD lengkap akun Super Admin dan Admin Dinas dengan proteksi diri. |
| **Website Management** | **Implemented** | CRUD pendaftaran dinas & website dengan inisialisasi relasi otomatis. |
| **Global Template Builder** | **Implemented** | Drag-and-drop SortableJS, pohon seksi-kontainer-komponen, dan pratinjau bersih. |
| **Content Management (Posts & Pages)**| **Implemented** | CRUD postingan dan hierarki halaman 4 level dengan pengurutan cerdas. |
| **Media Management & Optimizer** | **Implemented** | Upload multi-format dengan kompresi WebP & ZIP deflate otomatis. |
| **Public Website Layer** | **Implemented** | Rendering dinamis berbasis slug path `/site/{identifier}` dengan penanganan maintenance. |
| **Database & Migrations** | **Implemented** | 21 Berkas migrasi aktif, 10 model Eloquent, relasi teruji 100%. |

---

## 20. Evidence / Source Reference

- **Routing Terpusat:** `routes/web.php`
- **Rate Limiting:** `app/Providers/AppServiceProvider.php`
- **Middleware Role:** `app/Http/Middleware/EnsureSuperAdmin.php`
- **Autentikasi Controller:** `app/Http/Controllers/AuthController.php`
- **User Management Controller:** `app/Http/Controllers/AdminUserController.php`
- **Website Dinas Controller:** `app/Http/Controllers/AdminWebsiteController.php`
- **Template Builder Controller:** `app/Http/Controllers/TemplateBuilderController.php`
- **Activity Log Controller:** `app/Http/Controllers/AdminActivityController.php`
- **Posts Controller:** `app/Http/Controllers/DinasPostController.php`
- **Pages Controller:** `app/Http/Controllers/DinasPageController.php`
- **Media Controller:** `app/Http/Controllers/DinasMediaController.php`
- **Appearance Controller:** `app/Http/Controllers/DinasAppearanceController.php`
- **Settings Controller:** `app/Http/Controllers/DinasSettingController.php`
- **Public Website Controller:** `app/Http/Controllers/PublicWebsiteController.php`
- **Canvas Renderer Engine:** `app/Services/CanvasRenderer.php`
- **Media Optimizer Engine:** `app/Services/MediaOptimizerService.php`
- **Canvas Validation Rule:** `app/Rules/ValidCanvasData.php`
- **Database Migrations:** `database/migrations/`
- **Test Suite (Bukti Berfungsi):** `tests/Feature/` (145 Feature Tests, 835 Assertions lulus 100%)
