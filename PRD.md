# PRD — Pyojek CMS
**Product Requirements Document**  
**Produk:** Pyojek CMS — Content Management System Website Dinas  
**Instansi:** Diskominfo Kota Batu  
**Status:** Implemented Baseline  
**Versi:** 1.1  
**Tanggal:** 28 September 2026  

---

## 1. Ringkasan Produk
Pyojek CMS adalah platform Content Management System (CMS) terpusat berbasis web untuk pembuatan dan pengelolaan website dinas di lingkungan Pemerintah Kota Batu.  
Konsep intinya adalah **Single Global Template, Multi-Tenant Data**. Super Admin Diskominfo mengendalikan struktur blueprint tampilan secara terpusat, sedangkan Admin Kedinasan hanya mengelola data konten pada website dinas yang menjadi kewenangannya.

```text
Super Admin (Diskominfo)
    ↓ Mengontrol struktur & blueprint
Global Blueprint / Template (Builder & Preview)
    ↓ Dirender menjadi
Website Dinas (Multi-Tenant, Slug Path: /site/{identifier})
    ↑ Mengisi data pada slot
Admin Kedinasan (Content Manager)
    ↓ Publikasi
Masyarakat / Pengunjung (Read-Only)
```

---

## 2. Sasaran Pengguna dan Hak Akses

| Entitas / Role | Tanggung Jawab Utama | Batasan Ketat (Constraints) |
| :--- | :--- | :--- |
| **Super Admin** | Mengelola pengguna (Admin Dinas & Super Admin), mendaftarkan Website Dinas baru, mengelola Global Template (Canvas Builder & Preview), konfigurasi sistem terpusat, serta memantau log aktivitas platform. | Tidak bertugas menginput konten operasional rutin harian milik dinas (Posts, Media dinas, Pages dinas). |
| **Admin Kedinasan** | Mengelola Post, Media, Page, Website Settings, dan data Appearance spesifik dinas miliknya. | Dilarang memodifikasi tata letak (layout), menambah script/CSS bebas, mengubah struktur template global, atau mengakses data dinas lain. |
| **Masyarakat** | Mengakses informasi, berita, dokumen publik, dan layanan publik yang dipublikasikan. | Akses murni *read-only*, tidak memiliki akun maupun akses ke antarmuka dashboard CMS. |

---

## 3. Konsep Template (Fixed Blueprint)
Template website bersifat fixed dan seragam untuk seluruh dinas. Admin Kedinasan tidak diberikan akses modifikasi layout.

Struktur halaman publik yang dikunci oleh sistem:
1. **Header / Navbar:** Navigasi dinamis bertingkat hingga 4 level (Menu Utama Header → Sub-menu → Wadah Sub-sub-bab → Isi Wadah).
2. **Hero Section:** Slot judul/slogan, deskripsi, dan gambar latar (banner).
3. **Section Layanan / Informasi Singkat:** Terintegrasi dengan tautan kontak dinas.
4. **Section Posts Terbaru:** Publikasi terkini berkategori Berita, Pengumuman, dan Kegiatan.
5. **Section Profil Ringkas (Static Content):** Terhubung ke halaman statis beranda dinas.
6. **Section Media / Dokumen Publik:** Galeri berkas resmi (PDF & Gambar) dengan pratinjau asli.
7. **Footer:** Identitas dinas, kontak resmi, dan informasi tambahan.

Admin Kedinasan hanya mengisi nilai data variabel yang ditampung pada masing-masing slot section yang diizinkan oleh template.

---

## 4. Modul dan Fitur Fungsional

### 4.1 Autentikasi, Otorisasi, & Keamanan
- Autentikasi berbasis sesi bawaan Laravel (`/login`, `/logout`).
- Penegakan Role-Based Access Control (RBAC) pada level middleware backend (`EnsureSuperAdmin` untuk rute `/admin/*`).
- Pembatasan login hanya untuk akun dengan status `aktif`.
- Rate Limiting 4 tingkat: proteksi brute-force login (5 req/menit), website publik (120 req/menit), pembacaan CMS (120 req/menit), dan mutasi penulisan CMS (40 req/menit).

### 4.2 Manajemen Pengguna (Super Admin)
- Super Admin membuat, melihat, mengubah, dan menghapus akun pengguna (Super Admin maupun Admin Kedinasan).
- Menautkan akun Admin Kedinasan ke entitas Dinas terkait.
- Mengaktifkan atau menonaktifkan status akun (`aktif` / `nonaktif`).
- Proteksi akun mandiri: Super Admin dicegah menonaktifkan, menghapus, atau menurunkan role akun dirinya sendiri.

### 4.3 Manajemen Website Dinas (Super Admin)
- Super Admin mendaftarkan website dinas baru dengan relasi 1:1 terhadap entitas dinas.
- Pendaftaran website secara otomatis menginisialisasi slot Appearance default dan Setting operasional dinas.
- Mengatur nama situs, kode dinas, domain unik (slug path `/site/{identifier}`), dan status operasional (`aktif` / `nonaktif`).

### 4.4 Template Builder & Preview Mandiri (Super Admin)
- **Visual Template Builder:** Antarmuka penyusunan kanvas berbasis drag-and-drop (`SortableJS`) dengan struktur pohon bersarang: `Section → Container → Component`.
- Pengaturan layout (tinggi hero, jumlah kolom, limit artikel/dokumen) dan binding slot data.
- Kemampuan mengunci slot (`editable_by: 'super_admin'`) agar tidak dapat diubah oleh dinas.
- **Template Preview Mode (`/admin/templates/{template}/preview`):** Pratinjau visual template bersih tanpa memuat data dinas riil atau postingan dinas tertentu.

### 4.5 Konfigurasi Sistem & Log Aktivitas (Super Admin)
- **Konfigurasi Platform Global:** Mengatur batas maksimal ukuran unggah (`max_upload_size_mb`) dan format ekstensi yang diizinkan (`allowed_media_types`).
- **Platform Activity Logs:** Memantau linimasa aktivitas teragregasi lintas modul (Pengguna, Website, Template, Pengaturan, Konten) dengan filter kategori dan pencarian.

### 4.6 Manajemen Posts (Admin Kedinasan)
- Operasi CRUD artikel berkala: Berita, Pengumuman, dan Kegiatan.
- Status publikasi: `draft` (hanya internal CMS) dan `published` (tampil di publik).
- Atribut: Judul, slug URL unik per tenant, isi tulisan, thumbnail gambar (otomatis tersimpan ke pustaka Media dan dikompresi WebP), direct link unduhan berkas eksternal, penempatan target menu, dan waktu publikasi.

### 4.7 Manajemen Media & Dokumen Publik (Admin Kedinasan)
- Pengunggahan dan pengelolaan berkas terpusat yang terisolasi per dinas.
- Format yang didukung: Gambar (`.jpg`, `.jpeg`, `.png`, `.webp`) dan Dokumen (`.pdf`, `.doc`, `.docx`, `.xls`, `.xlsx`, `.ppt`, `.pptx`).
- Otomatisasi kompresi via `MediaOptimizerService`: konversi gambar ke WebP (85%), kompresi dokumen Office dengan ZIP deflate level 9, dan optimasi stream PDF.

### 4.8 Manajemen Pages (Admin Kedinasan)
- Halaman konten statis institusi (Profil, Visi & Misi, Struktur Organisasi, Layanan, Kontak).
- Mendukung hierarki menu hingga **4 level kedalaman**:
  - Level 1: Menu Utama Header (`header_menu`)
  - Level 2: Sub-menu
  - Level 3: Wadah Sub-sub-bab
  - Level 4: Isi Wadah
- Penempatan khusus: Tab di Beranda (`beranda`) yang otomatis terhubung ke komponen *Static Content*.
- Pengurutan tabel Pages: Halaman Tab Beranda selalu di urutan teratas, diikuti pengelompokan per Menu Utama Header beserta turunannya. Sub-menu berstatus draft diposisikan tetap sejajar dengan sub-menu publish di atas sub-sub menu.
- Validasi pencegahan *circular reference* dan pembatasan kedalaman maksimal 4 level.

### 4.9 Appearance Slot (Admin Kedinasan)
- Pengisian elemen visual yang slotnya disediakan oleh template: Logo dinas, teks slogan header, teks deskripsi hero, gambar latar (banner) hero, serta identitas dan kontak pada Footer.
- Pengunggahan logo dan banner langsung dari komputer dengan konversi WebP otomatis.
- Slot yang dikunci Super Admin pada builder diproteksi dari penimpaan Admin Dinas.
- Tidak menyediakan editor visual tata letak atau injeksi CSS/JS bebas.

### 4.10 Pengaturan Website Kedinasan (Admin Kedinasan)
- Mengatur judul situs (`site_title`), status operasional website dinas (`aktif` atau `pemeliharaan` / 503 internal preview), visibilitas (`publik` atau `terbatas`), dan email kontak privasi.

### 4.11 Website Publik (Read-Only)
- Merender konten dinas secara dinamis berdasarkan slug path `/site/{identifier}`.
- Menyaring dan menampilkan hanya data yang berstatus `published`.
- Jika website berstatus `pemeliharaan`, pengunjung publik menerima tampilan 503 sementara Admin Dinas pemilik dan Super Admin tetap dapat melakukan pratinjau internal.

### 4.12 Developer Credit Internal
- Teks `"Developed by Azzaryansyaa"` ditampilkan secara terstandarisasi pada bagian bawah sidebar navigasi dan footer di seluruh area internal CMS (Super Admin dan Admin Kedinasan).
- Credit tidak muncul pada website publik yang dilihat oleh masyarakat umum.

---

## 5. Aturan Bisnis (Business Rules)
- **BR-01 (Isolasi Tenant):** Admin Kedinasan hanya dapat membaca, mengedit, dan menghapus data yang dimiliki oleh dinasnya sendiri (`dinas_id` / `website_id`).
- **BR-02 (Relasi Dinas-Website):** Tepat 1 Dinas memiliki 1 entitas profil Website.
- **BR-03 (Immutability Template):** Struktur tampilan dan komponen template ditentukan global dan tidak dapat diubah oleh Admin Kedinasan.
- **BR-04 (Isolasi Draft):** Post atau data berstatus draft dilarang ditampilkan pada website publik.
- **BR-05 (Backend Authorization):** Validasi hak akses data dinas wajib dilakukan di lapisan controller/middleware backend, bukan sekadar manipulasi tampilan antarmuka.
- **BR-06 (Proteksi Diri Super Admin):** Super Admin tidak dapat menghapus akun sendiri, menonaktifkan akun sendiri, atau mengubah rolenya sendiri.
- **BR-07 (Navigasi Wadah):** Halaman yang berada pada Level 4 (isi wadah) otomatis dialihkan ke halaman wadah induknya (Level 3) saat diakses via URL langsung.

---

## 6. Ruang Lingkup Ketat (Strict Out-of-Scope)
Untuk menjaga fokus kesederhanaan dan keamanan sistem:
1. **Tidak Ada Page/Visual Builder untuk Admin Kedinasan:** Builder visual kanvas drag-and-drop eksklusif hanya untuk Super Admin.
2. **Tidak Ada Ekosistem Plugin/Widget Bebas:** Komponen terbatas hanya pada daftar pre-defined bawaan sistem (`Container`, `Hero`, `Posts Grid`, `Static Content`, `Media / Document List`, `Footer`).
3. **Tidak Ada Custom Code Injection:** Tidak menyediakan fitur Custom CSS, manipulasi DOM bebas, maupun injeksi script JavaScript/PHP mentah.
4. **Tidak Ada Multi-Template Chooser oleh Admin Kedinasan:** Seluruh website dinas mengacu pada blueprint template global aktif yang ditentukan Super Admin Diskominfo.
5. **Tidak Ada Sistem Komentar:** Tidak menyediakan comment/discussion engine publik.
6. **Tidak Ada Registrasi Publik:** Akun tidak dapat dibuat sendiri oleh publik; seluruh akun dibuat terpusat oleh Super Admin.
7. **Tidak Ada Integrasi Sistem Eksternal:** Tidak menyertakan koneksi API atau integrasi ke layanan pihak ketiga di luar ekosistem Pyojek CMS.
8. **Tidak Ada Subdomain Wildcard Hosting:** Seluruh resolusi website publik menggunakan rute slug path `/site/{identifier}`.
9. **Tidak Ada Automated Backup Scheduler:** Pengelolaan backup database/file dilakukan di tingkat infrastruktur server, bukan melalui dashboard aplikasi.
