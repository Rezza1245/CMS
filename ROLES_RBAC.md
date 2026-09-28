# ROLES_RBAC.md — Roles and RBAC Specification

**Produk:** Pyojek CMS — Content Management System Website Dinas  
**Dokumen:** ROLES_RBAC.md  
**Versi:** 1.1  
**Tanggal:** 28 September 2026  
**Status:** CONFIRMED Specification  

---

## 1. Document Purpose
Dokumen ini mendefinisikan role, kewenangan, batasan akses, dan aturan authorization pada Pyojek CMS.

Dokumen ini menjadi acuan utama untuk implementasi:
- authentication dan authorization;
- middleware (`EnsureSuperAdmin`);
- route protection (`routes/web.php`);
- controller authorization dan tenant ownership check;
- pembatasan akses data antar dinas;
- pembatasan akses fitur pada dashboard.

Rujukan dokumen:
- `PRD.md` untuk requirement dan business rules produk.
- `TECH_STACK.md` untuk keputusan teknologi.
- `SCHEMA.md` untuk struktur database dan relasi.
- `TEMPLATE.md` untuk aturan struktur template.
- `CONTENT_SPEC.md` untuk detail konten.

---

## 2. Konsep Role
Pyojek CMS memiliki tiga kelompok pengguna utama:
1. **Super Admin:** Pengelola teknis platform terpusat di lingkungan Diskominfo Kota Batu.
2. **Admin Kedinasan:** Pengelola konten operasional website dinas miliknya sendiri.
3. **Masyarakat / Pengunjung:** Pengguna umum penikmat informasi publik (read-only).

Masyarakat tidak memiliki akun CMS dan tidak dapat mengakses antarmuka dashboard CMS.

---

## 3. Model Hubungan Pengguna dan Dinas
```text
    Super Admin (Diskominfo)
        |
        | mengelola platform, website, user, & template
        v
    Admin Kedinasan
        |
        | belongs to (users.dinas_id)
        v
      Dinas
        |
        | owns (1:1)
        v
     Website (Slug: /site/{identifier})
        |
        +-- Posts
        +-- Pages (Hierarki 4 Level)
        +-- Media
        +-- Appearance Slots
        +-- Settings
```

---

## 4. Super Admin

### 4.1 Tanggung Jawab
Super Admin bertanggung jawab atas stabilitas dan manajemen platform Pyojek CMS secara keseluruhan.

Tanggung jawab utama:
1. Mengelola pengguna (Admin Kedinasan dan sesama Super Admin).
2. Mendaftarkan dan mengelola portal Website Dinas.
3. Mengelola Template Global (Canvas Builder & Preview Mode).
4. Mengelola konfigurasi sistem terpusat.
5. Memantau linimasa aktivitas sistem (Activity Logs).

Super Admin **tidak bertugas menginput konten operasional rutin harian milik dinas** (Post berita, Page profil dinas, unggahan media dinas).

### 4.2 Akses Modul Super Admin
Super Admin memiliki hak akses penuh terhadap modul internal:
- **Dashboard (`/admin/dashboard`):** Ringkasan statistik template aktif, total admin dinas, monitoring website dinas, dan aktivitas terbaru.
- **Template Builder (`/admin/templates/{id}/builder`):** Pengaturan kanvas drag-and-drop komponen, layout settings, dan binding slot.
- **Template Preview (`/admin/templates/{id}/preview`):** Pratinjau visual template bersih tanpa memuat data dinas riil.
- **User Management (`/admin/users`):** CRUD akun Super Admin dan Admin Kedinasan.
- **Website Dinas Management (`/admin/websites`):** CRUD entitas website dinas dan instansi terkait.
- **System Settings (`/admin/settings`):** Konfigurasi batas upload (`max_upload_size_mb`) dan tipe media (`allowed_media_types`).
- **Platform Activity Logs (`/admin/activities`):** Log riwayat aktivitas teragregasi lintas modul.

### 4.3 Aturan Proteksi Diri Super Admin
Dalam `AdminUserController`, Super Admin yang sedang login dilarang:
- Menghapus akunnya sendiri (`abort/redirect error`).
- Menonaktifkan status akunnya sendiri.
- Mengubah role akunnya sendiri dari `super_admin` menjadi role lain.

---

## 5. Admin Kedinasan

### 5.1 Tanggung Jawab
Admin Kedinasan adalah *Content Manager* untuk portal website dinas yang terikat pada akunnya (`dinas_id`).

Tanggung jawab utama:
1. Mengelola publikasi artikel berkala (Posts).
2. Mengelola dokumen dan media publik (Media).
3. Mengelola halaman statis kelembagaan dan hierarki menu (Pages).
4. Mengisi data visual pada slot yang disediakan template (Appearance).
5. Mengelola pengaturan operasional situs dinas (Website Settings).

### 5.2 Scope & Isolasi Tenant
Admin Kedinasan dibatasi mutlak pada website dinasnya sendiri:
```text
Admin Dinas A → Resource Dinas A = ALLOW
Admin Dinas A → Resource Dinas B = DENY (403 Forbidden)
```
Pembatasan ini divalidasi pada lapisan controller/middleware backend (`$resource->website_id === $website->id`).

### 5.3 Modul Posts
- CRUD artikel berkategori: *Berita*, *Pengumuman*, dan *Kegiatan*.
- Pengelolaan status: `draft` (hanya internal) dan `published` (tayang publik).
- Penempatan: Beranda atau terhubung ke Halaman / Sub-menu tertentu.

### 5.4 Modul Media
- Mengunggah berkas gambar (`.jpg`, `.jpeg`, `.png`, `.webp`) dan dokumen (`.pdf`, `.doc`, `.docx`, `.xls`, `.xlsx`, `.ppt`, `.pptx`).
- Seluruh berkas otomatis dioptimasi oleh `MediaOptimizerService`.
- Terisolasi per `website_id`; admin tidak dapat melihat atau menghapus media milik dinas lain.

### 5.5 Modul Pages & Hierarki Menu
- CRUD halaman statis dengan dukungan hierarki menu hingga **4 level kedalaman**:
  - Level 1: Menu Utama Header
  - Level 2: Sub-menu
  - Level 3: Wadah Sub-sub-bab
  - Level 4: Isi Wadah
- Penempatan khusus: Tab di Beranda (`beranda`) yang terhubung langsung ke elemen *Static Content* template.
- Pengurutan tabel: Tab Beranda selalu di urutan teratas, diikuti hierarki per Menu Utama Header (sub-menu draft tetap sejajar sub-menu publish di atas sub-sub menu).

### 5.6 Modul Appearance
- Pengisian data variabel visual: Logo resmi, slogan header, deskripsi hero, gambar latar (banner) hero, dan kontak footer.
- **Aturan Immutability Slot:** Jika Super Admin mengunci slot pada builder (`editable_by: 'super_admin'`), Admin Kedinasan tidak dapat menimpa nilai default template.

### 5.7 Modul Website Settings
- Pengaturan pada `/dinas/settings`: Judul situs (`site_title`), status operasional (`aktif` / `pemeliharaan`), visibilitas publik, dan email privasi.
- Jika status diubah menjadi `pemeliharaan`, masyarakat umum menerima respons 503 sementara Admin Dinas pemilik dan Super Admin tetap dapat mengakses pratinjau.

---

## 6. Masyarakat / Pengunjung
- Bersifat **READ ONLY**.
- Hanya dapat mengakses rute publik (`/site/{identifier}`, `/site/{identifier}/page/{slug}`, `/site/{identifier}/post/{slug}`).
- Hanya dapat melihat konten yang berstatus `published`.
- Dilarang keras mengakses rute internal dashboard CMS.

---

## 7. Permission Matrix

| Resource / Action | Super Admin | Admin Kedinasan | Masyarakat |
| :--- | :--- | :--- | :--- |
| **Dashboard CMS** | ALLOW / GLOBAL | ALLOW / OWN | DENY |
| **User Management (CRUD)** | ALLOW / GLOBAL | DENY | DENY |
| **Website Dinas Management (CRUD)** | ALLOW / GLOBAL | DENY | DENY |
| **Template Builder (Manage Canvas)** | ALLOW / GLOBAL | DENY | DENY |
| **Template Preview Mode** | ALLOW / GLOBAL | DENY | DENY |
| **System Settings (Global Config)** | ALLOW / GLOBAL | DENY | DENY |
| **Platform Activity Logs (View)** | ALLOW / GLOBAL | DENY | DENY |
| **Posts (CRUD & Publish)** | DENY / NOT ROUTINE | ALLOW / OWN | PUBLIC READ (`published` only) |
| **Media (Upload & Manage)** | DENY / NOT ROUTINE | ALLOW / OWN | PUBLIC READ (Published assets) |
| **Pages (CRUD & Menu Hierarchy)** | DENY / NOT ROUTINE | ALLOW / OWN | PUBLIC READ (`published` only) |
| **Appearance Slots (Edit Slots)** | GLOBAL BLUEPRINT OWNER | ALLOW / OWN (Unlocked slots) | PUBLIC READ |
| **Website Settings (Edit Settings)**| DENY / NOT ROUTINE | ALLOW / OWN | DENY |
| **Public Website Content (Read)** | ALLOW | ALLOW / OWN | PUBLIC READ |

---

## 8. Backend Authorization Rules

- **RBAC Rule 01:** Setiap rute internal wajib melalui middleware `auth` dan throttle `cms-read` / `cms-write`.
- **RBAC Rule 02:** Seluruh rute `/admin/*` wajib melewati middleware `super_admin` (`EnsureSuperAdmin`).
- **RBAC Rule 03:** Admin Kedinasan hanya dapat mengakses resource yang terikat pada `dinas_id` / `website_id` miliknya.
- **RBAC Rule 04:** Otorisasi wajib ditegakkan di backend controller, bukan sekadar manipulasi tampilan frontend.
- **RBAC Rule 05:** Konten berstatus `draft` dilarang ditampilkan pada respons publik (BR-04).
- **RBAC Rule 06:** Super Admin dilindungi dari aksi destruktif mandiri (tidak dapat menghapus, menonaktifkan, atau mendegradasi akun sendiri).
- **RBAC Rule 07:** Seluruh panel internal CMS memuat identitas developer credit `"Developed by Azzaryansyaa"` yang diisolasi ketat dari tampilan website publik.
