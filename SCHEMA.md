# SCHEMA.md — Database Schema Specification

**Produk:** Pyojek CMS — Content Management System Website Dinas  
**Dokumen:** SCHEMA.md  
**Versi:** 1.1 (Canonical LRS Specification)  
**Tanggal:** 28 September 2026  
**Status:** CONFIRMED Baseline Schema  

---

## 1. Tujuan Dokumen & Konvensi
Dokumen ini mendefinisikan skema data relasional, aturan kepemilikan tenant, dan batasan integritas untuk Pyojek CMS. Digunakan sebagai acuan migrasi database dan model Eloquent ORM.

### Konvensi Penamaan (Laravel Standard)
- **Tabel:** Plural snake_case (`users`, `dinas`, `websites`, `templates`, `pages`, `posts`, `media`, `appearances`, `settings`, `system_settings`).
- **Primary Key:** `id` (auto-incrementing bigint).
- **Foreign Key:** `{singular_table_name}_id` (contoh: `dinas_id`, `website_id`, `template_id`, `user_id`, `page_id`).
- **Timestamps:** `created_at` dan `updated_at`.

---

## 2. Relasi Entitas Logis (Canonical LRS)

Struktur relasi dan kardinalitas sistem:
- **DINAS — USER (1 : N):** Admin Kedinasan terikat pada satu Dinas (`users.dinas_id` → `dinas.id`). Nullable untuk Super Admin.
- **DINAS — WEBSITE (1 : 1):** Tepat satu Dinas memiliki satu Website (`websites.dinas_id` → `dinas.id`, UNIQUE).
- **TEMPLATE — WEBSITE (1 : N):** Satu Template global digunakan oleh banyak Website dinas (`websites.template_id` → `templates.id`).
- **WEBSITE — SETTINGS (1 : 1):** Konfigurasi operasional website dinas (`settings.website_id` → `websites.id`, UNIQUE).
- **WEBSITE — APPEARANCE (1 : 1):** Penataan slot visual website dinas (`appearances.website_id` → `websites.id`, UNIQUE).
- **WEBSITE — PAGE (1 : N):** Website memiliki banyak Page (`pages.website_id` → `websites.id`).
- **PAGE — PAGE (1 : N):** Hierarki menu bersarang hingga **4 level kedalaman**:
  - Level 1: Menu Utama Header (`parent_id = null`)
  - Level 2: Sub-menu
  - Level 3: Wadah Sub-sub-bab
  - Level 4: Isi Wadah (`pages.parent_id` → `pages.id`)
- **WEBSITE — POST (1 : N):** Website memiliki banyak Post (`posts.website_id` → `websites.id`).
- **PAGE — POST (1 : N):** Halaman/Sub-menu opsional menampung postingan terkait (`posts.page_id` → `pages.id`, NULLABLE).
- **USER — POST (1 : N):** Admin mencatat riwayat pembuat postingan (`posts.user_id` → `users.id`).
- **WEBSITE — MEDIA (1 : N):** Website memiliki pustaka Media terisolasi (`media.website_id` → `websites.id`).
- **USER — MEDIA (1 : N):** Admin mencatat riwayat pengunggah berkas (`media.user_id` → `users.id`).

---

## 3. Struktur Tabel & Kolom

### 3.1 Tabel: `dinas`
Menyimpan entitas organisasi perangkat daerah / instansi.

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `name` | String(255), NOT NULL | Nama dinas / instansi |
| `code` | String(50), NOT NULL, UNIQUE | Kode instansi (contoh: DISKOMINFO, DISDIK) |
| `address` | Text, NULLABLE | Alamat fisik kantor dinas |
| `contact_email` | String(255), NULLABLE | Email resmi dinas |
| `phone` | String(50), NULLABLE | Nomor telepon dinas |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.2 Tabel: `users`
Akun pengelola sistem untuk Super Admin dan Admin Kedinasan.

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `dinas_id` | FK (`dinas.id`), NULLABLE | Nullable untuk Super Admin; Wajib untuk Admin Dinas |
| `name` | String(255), NOT NULL | Nama lengkap pengguna |
| `code` | String(50), NULLABLE | NIP / Kode identitas pegawai |
| `email` | String(255), UNIQUE, NOT NULL | Email login pengguna |
| `password` | String(255), NOT NULL | Password hash (Bcrypt) |
| `role` | String(50), NOT NULL | Role identitas (`super_admin`, `admin_dinas`) |
| `status` | String(50), NOT NULL, Default: 'aktif' | Status akun pengguna (`aktif` / `nonaktif`) |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.3 Tabel: `templates`
Kerangka dasar blueprint tampilan global yang dikendalikan Super Admin.

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `name` | String(255), NOT NULL | Nama blueprint template |
| `canvas_data` | JSON, NOT NULL | Struktur pohon komponen kanvas visual builder (`section → container → component`) |
| `template_version` | String(50), NOT NULL | Versi blueprint template (default: '1.0') |
| `header_structure` | String(255), NOT NULL | Konfigurasi bawaan header/navbar |
| `post_layout` | String(255), NOT NULL | Tata letak kartu publikasi |
| `page_layout` | String(255), NOT NULL | Tata letak halaman statis |
| `navigation_structure`| String(255), NOT NULL | Kerangka struktur navigasi |
| `status` | String(50), NOT NULL, Default: 'aktif'| Status ketersediaan template |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.4 Tabel: `websites`
Representasi entitas website publik per dinas.

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `dinas_id` | FK (`dinas.id`), UNIQUE, CASCADE | Relasi 1:1 terhadap entitas dinas pemilik |
| `template_id` | FK (`templates.id`), RESTRICT | Template global yang digunakan website |
| `domain` | String(100), UNIQUE, NOT NULL | Identifier unik slug path website (`/site/{domain}`) |
| `name` | String(255), NOT NULL | Nama situs portal website dinas |
| `status` | String(50), NOT NULL, Default: 'aktif'| Status operasional (`aktif`, `pemeliharaan`, `nonaktif`) |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.5 Tabel: `settings`
Pengaturan website yang melekat pada entitas website dinas (multi-tenant).

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `website_id` | FK (`websites.id`), UNIQUE, CASCADE | Relasi 1:1 ke entitas website |
| `general_config` | JSON, NULLABLE | Konfigurasi aktif: judul situs (`site_title`) dan status website (`site_status`) |
| `privacy_config` | JSON, NULLABLE | Konfigurasi aktif: visibilitas (`public_visibility`) dan email privasi |
| `media_config` | JSON, NULLABLE | Kolom konfigurasi cadangan |
| `system_config` | JSON, NULLABLE | Kolom konfigurasi cadangan |
| `backup_config` | JSON, NULLABLE | Kolom konfigurasi cadangan |
| `storage_config` | JSON, NULLABLE | Kolom konfigurasi cadangan |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.6 Tabel: `system_settings`
Pengaturan platform CMS terpusat pada tingkat global (Super Admin only).

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `key` | String(255), UNIQUE, NOT NULL | Kunci konfigurasi (`max_upload_size_mb`, `allowed_media_types`) |
| `value` | Text, NULLABLE | Nilai parameter konfigurasi |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.7 Tabel: `appearances`
Elemen visual dan penataan identitas visual pada slot yang telah disediakan template.

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `website_id` | FK (`websites.id`), UNIQUE, CASCADE | Relasi 1:1 ke entitas website dinas |
| `logo` | String(1000), NULLABLE | Path berkas logo dinas |
| `favicon` | String(1000), NULLABLE | Path berkas favicon |
| `hero_banner` | String(1000), NULLABLE | Path berkas gambar latar hero section |
| `hero_description` | Text, NULLABLE | Teks deskripsi / sambutan pengantar hero |
| `header_slogan` | String(255), NULLABLE | Teks slogan / tagline area header |
| `footer_slogan` | String(255), NULLABLE | Teks slogan penutup area footer |
| `footer_title` | String(255), NULLABLE | Judul instansi pada footer kolom 1 |
| `footer_about_title`| String(255), NULLABLE | Judul tentang kami pada footer kolom 3 |
| `footer_about_text` | Text, NULLABLE | Teks informasi kota / dinas pada footer kolom 3 |
| `primary_color` | String(50), NULLABLE | Kode warna tema utama |
| `secondary_color` | String(50), NULLABLE | Kode warna tema sekunder |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.8 Tabel: `pages`
Halaman informasi statis institusi dinas dan struktur navigasi menu header bertingkat.

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `website_id` | FK (`websites.id`), CASCADE | Induk website pemilik halaman |
| `parent_id` | FK (`pages.id`), NULLABLE, CASCADE | Relasi hierarkis self-referencing (maksimal kedalaman 4 level) |
| `title` | String(255), NOT NULL | Judul halaman / label menu |
| `slug` | String(255), NOT NULL | Slug URL unik per website (`unique(website_id, slug)`) |
| `content` | LongText, NOT NULL | Isi teks konten halaman |
| `image` | String(1000), NULLABLE | Path berkas gambar banner/halaman (tercatat di tabel media) |
| `direct_link` | String(500), NULLABLE | Tautan eksternal langsung (Google Drive, URL, mailto:, tel:) |
| `status` | String(50), NOT NULL, Default: 'draft'| Status halaman (`draft`, `published`) |
| `placement` | String(100), NOT NULL, Default: 'beranda'| Target penempatan (`beranda`, `header_menu`, `sub_menu`, dll) |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.9 Tabel: `posts`
Konten artikel berkala dinas (Berita, Pengumuman, Kegiatan).

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `website_id` | FK (`websites.id`), CASCADE | Induk website pemilik post |
| `page_id` | FK (`pages.id`), NULLABLE, NULL ON DELETE | Tautan relasi opsional ke menu penampung |
| `user_id` | FK (`users.id`), RESTRICT | Admin pembuat artikel |
| `title` | String(255), NOT NULL | Judul artikel |
| `slug` | String(255), NOT NULL | Slug URL unik per website (`unique(website_id, slug)`) |
| `content` | Text, NOT NULL | Isi teks artikel |
| `type` | String(50), NOT NULL | Kategori (`Berita`, `Pengumuman`, `Kegiatan`) |
| `status` | String(50), NOT NULL, Default: 'draft'| Status publikasi (`draft`, `published`) |
| `placement` | String(100), NOT NULL, Default: 'beranda'| Target penempatan (`beranda`, `sub_page`, dll) |
| `image` | String(1000), NULLABLE | Path berkas gambar sampul (tercatat di tabel media) |
| `direct_link` | String(500), NULLABLE | Tautan unduhan berkas eksternal |
| `published_at` | Timestamp, NULLABLE | Waktu publikasi tayang |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |

---

### 3.10 Tabel: `media`
Metadata berkas gambar dan dokumen publik yang diunggah dinas.

| Kolom | Tipe / Constraint | Keterangan |
| :--- | :--- | :--- |
| `id` | PK, BigInt | Primary Key auto-increment |
| `website_id` | FK (`websites.id`), CASCADE | Website pemilik aset media |
| `user_id` | FK (`users.id`), RESTRICT | Admin pengunggah berkas |
| `file_name` | String(255), NOT NULL | Nama asli berkas |
| `file_path` | String(1000), NOT NULL | Path penyimpanan pada filesystem |
| `file_type` | String(100), NOT NULL | Format / MIME type (WebP, PNG, PDF, Word, Excel, PPT) |
| `file_size` | UnsignedBigInteger, NOT NULL | Ukuran berkas teroptimasi (dalam bytes) |
| `created_at` | Timestamp | Waktu record dibuat |
| `updated_at` | Timestamp | Waktu record diperbarui |
