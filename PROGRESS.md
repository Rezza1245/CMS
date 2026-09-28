# PROGRESS.md — Status Implementasi Pyojek CMS

## 1. Ringkasan Status Proyek

Status Saat Ini: **STABLE, PRODUCTION-READY & TESTED**  
Pengujian Otomatis: **138 Test / 769 Assertions (100% Passed)**

---

## 2. Rincian Fitur & Modul

### 2.1 Modul Autentikasi, RBAC & Keamanan
- [x] Session-based login & logout (`/login`, `/logout`).
- [x] Pemisahan hak akses role `super_admin` vs `admin_dinas`.
- [x] Middleware `EnsureSuperAdmin` untuk modul platform global.
- [x] Tenant isolation ketat pada level controller untuk seluruh resource dinas.
- [x] **Rate Limiting & Throttling Terpadu (Anti-Spam & Anti-Brute-Force)**:
  - Proteksi login: batas maksimal 5 percobaan per menit per kombinasi IP + Email (`throttle:login`) dengan tampilan error 429 informatif.
  - Proteksi website publik: 120 permintaan per menit per IP (`throttle:public-site`) mencegah scraping dan traffic flooding.
  - Proteksi operasi mutasi CMS: 40 permintaan tulis per menit (`throttle:cms-write`) mencegah bot spamming data dan flooding upload.
  - Proteksi dashboard CMS: 120 permintaan per menit (`throttle:cms-read`).

### 2.2 Modul Super Admin
- [x] **Dashboard Super Admin**:
  - Metrik kartu statistik (Template Aktif & Jumlah Admin Kedinasan).
  - Aktivitas terbaru sistem lintas tenant.
  - **Monitoring Website Dinas**: Pemantauan status operasional multi-tenant (Aktif/Live, Pemeliharaan, Nonaktif), daftar website dinas terkini, tautan langsung ke website publik, dan tombol kelola website.
- [x] Riwayat Aktivitas Platform Terpadu (`/admin/activities`) dengan filter kategori & pencarian.
- [x] Manajemen Website Dinas (Pendaftaran dinas baru, portal website, tautan live preview, otomasi inisialisasi appearance & settings).
- [x] Manajemen Pengguna & Admin Kedinasan (CRUD + reset password).
- [x] **Visual Template Builder**:
  - Drag-and-drop kanvas berbasis komponen sistem terdaftar (`Container`, `Hero`, `Posts Grid`, `Static Content`, `Media / Document List`, `Footer`).
  - **Komponen Container Bersarang**: Mendukung grid multi-kolom (1–4 kolom) dengan dropzone SortableJS internal.
  - **Patenisasi Hero Alignment**: Alignment Hero dipatenkan ke `center` (Fixed Blueprint).
  - **Kontrol Layout Baku**: Pengaturan layout menggunakan dropdown terstruktur (kolom, limit, tinggi hero) menggantikan input teks bebas.
  - **Dropdown DataSource Binding**: Pemetaan baku `BINDING_OPTIONS` per komponen/slot dengan preservasi binding kustom.
  - Penanganan khusus slot koleksi otomatis (`Posts Grid` dan `Media List`) tanpa fallback teks tak relevan.
  - Penghapusan komponen redundan: `Posts Carousel` dan `Contact / Dinas Info` (kontak kini terintegrasi di modul Pages & Footer).
- [x] **Pengaturan Sistem Platform (`/admin/settings`)**:
  - Konfigurasi batasan media & penyimpanan server (`max_upload_size_mb` dan `allowed_media_types`).

### 2.3 Modul Admin Kedinasan
- [x] **Dashboard Admin Kedinasan**:
  - Metrik postingan dan penggunaan media tenant.
  - Aktivitas konten terbaru dinas.
  - **Quick Access Draft**: Tab filter draf (Semua / Posts / Pages), akses sunting langsung via tombol `Edit Draft`, empty state otomatis, dan tombol aksi pembuatan konten baru.
- [x] **Posts Management**:
  - CRUD Berita, Pengumuman, dan Kegiatan.
  - Upload thumbnail gambar (otomatis tercatat ke perpustakaan `media`).
  - Dukungan `direct_link` berkas unduhan eksternal (Google Drive / Cloud).
  - Filter penempatan dan badge tabel yang disederhanakan.
- [x] **Pages Management**:
  - CRUD Halaman Statis Kelembagaan.
  - Struktur navigasi hierarki bertingkat (Root > Sub-bab > Sub-sub-bab / Wadah > Isi Wadah).
  - Sub-sub bab sebagai **Wadah (Container)** penampung bagian-bagian/item di dalamnya.
  - Upload ilustrasi/banner halaman.
  - **Preset Menu Standar Diskominfo (Opsi B)**: Quick-fill judul, pencocokan parent otomatis, kerangka draf konten, dan preset informasi kontak terpadu.
  - Penempatan fleksibel: Menu Utama Header (`header_menu`), Sub-menu bertingkat (`sub_menu`), atau Tab Beranda (`beranda`).
- [x] **Media Management**:
  - Upload file multi-format: Dokumen (PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX) dan Gambar (JPG, PNG, WEBP).
  - **Sistem Konversi & Kompresi Otomatis (Storage Optimization)**:
    - Seluruh unggahan gambar raster (PNG, JPG, JPEG) otomatis dikonversi ke format **WebP** dengan preservasi transparansi alpha dan kompresi kualitas tinggi (hemat 40%–80% storage).
    - Terintegrasi di seluruh titik upload: Pustaka Media, Thumbnail Berita/Post, Ilustrasi Halaman/Page, Logo Dinas, dan Banner Hero.
    - Dokumen Office Open XML (DOCX, XLSX, PPTX) dikompresi ulang dengan *re-pack zip deflate level 9*.
  - Isolasi direktori dan kepemilikan tenant per dinas (`website_id`).
- [x] **Appearance Slot**:
  - Konfigurasi logo resmi dinas (unggah berkas gambar langsung), banner hero, slogan, deskripsi, informasi footer (judul instansi, slogan, info tambahan), dan kontak resmi kedinasan.
  - Peniadaan opsi URL Favicon eksternal untuk simplifikasi alur kerja.
- [x] **Settings Website Kedinasan**:
  - Pengaturan nama situs website dinas, status operasional (Aktif vs Pemeliharaan), visibilitas publik, dan email narahubung privasi.

### 2.4 Website Publik & Template
- [x] Render struktur kanvas dinamis via `CanvasRenderer`.
- [x] **Header Navigation Minimalis & Dinamis**:
  - Menu `Beranda` fixed sebagai titik awal.
  - Seluruh menu utama lainnya dirender dinamis dari `Page` milik dinas (`parent_id IS NULL`, non-beranda).
  - Multi-level dropdown bersarang: Sub-bab (Level 2) & Sub-sub-bab (Level 3/4 Wadah).
  - Mobile drawer navigation responsive.
- [x] Smart routing & rendering untuk sub-sub bab wadah (grid kartu judul, gambar, deskripsi, tautan).
- [x] Halaman baca penuh postingan berita/artikel publik (`/site/{identifier}/post/{slug}`).
- [x] Halaman baca penuh halaman statis publik (`/site/{identifier}/page/{slug}`).
- [x] **Hero Section Bersih**:
  - Judul, slogan, deskripsi, dan tombol interaksi langsung "Hubungi Kami" (WhatsApp/Email).
  - Posisi center tetap (fixed blueprint).
- [x] Tenant boundary verification: akses lintas domain dinas ditolak dengan HTTP 404/403.
