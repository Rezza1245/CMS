Target View: resources/views/admin/dashboard.blade.php
Aktor / Role: Super Admin (role = 'super_admin')
Asset Path Gambar Mockup: docs/mockups/super-admin-dashboard.png (simpan screenshot di path ini)

1. Global Layout & Theme SystemLayout 
- Container: 2-kolom (Sidebar Kiri w-64 fixed, Main Content Area kanan flex-1 dengan background abu-abu terang #f8fafc / bg-slate-50).
- Sidebar Theme: Dark Navy #0f172a (bg-slate-900) dengan teks putih/abu-abu terang.
- Font Family: Inter / Sans-serif modern.Card Style: Background putih (#ffffff), rounded corners (rounded-xl / rounded-2xl), border tipis halus (border border-slate-100), shadow halus (shadow-sm).

2. Wireframe ASCII LayoutPlaintext
+---------------------+-------------------------------------------------------------------------------+
| [Logo] Pyojek CMS   | Dashboard                                                  (O) Super Admin [v]| -> Top Navbar
| Diskominfo Kota Batu|                                                                               |
+---------------------+-------------------------------------------------------------------------------+
|                     |                                                                               |
| [Icon] Dashboard *  | +------------------------------------+ +------------------------------------+ |
|                     | | [Icon] Template Aktif: [Nama]      | | [Icon] Jml Admin Kedinasan: [Jml]  | | -> Summary Cards
| [Icon] Template     | |        Template yg digunakan... (W)| |        Total admin terdaftar...  (W)| |    (Row 1: 2 Kolom)
|                     | +------------------------------------+ +------------------------------------+ |
| [Icon] User         |                                                                               |
|                     | +------------------------------------+ +------------------------------------+ |
| [Icon] Settings     | | Aktivitas Terbaru                  | | Kartu Status Sistem                | |
|                     | |                                    | |                                    | |
|                     | | (i) Template diaktifkan     09:45  | |   Server: Online     Database: Sehat| | -> Content Grid
|                     | | (i) Admin baru ditambah     14:30  | |   Keamanan: Aman     Backup: Terbaru| |    (Row 2: 2 Kolom)
|                     | | (i) Template diperbarui     10:12  | |                                    | |
|                     | | (i) Admin diperbarui        16:05  | |                                    | |
|                     | | (i) Pengaturan sistem       11:20  | |                                    | |
|                     | |                                    | |                                    | |
|                     | | Lihat semua aktivitas >            | | Terakhir: 10:00   Lihat detail >   | |
|                     | +------------------------------------+ +------------------------------------+ |
+---------------------+-------------------------------------------------------------------------------+
| (Fixed Sidebar)     | Pyojek CMS Diskominfo Kota Batu                                 © 2024 Diskominfo   | -> Footer
+---------------------+-------------------------------------------------------------------------------+
* = Active state
(W) = Watermark icon di pojok kanan kartu

3. Rincian Komponen Tampilan
A. Sidebar Navigasi Kiri (<aside>)
- Header Brand (Top):
    Logo: Kubus/geometris cyan-biru (w-8 h-8).
    Teks: Pyojek CMS (Bold, Putih), Subteks: Diskominfo Kota Batu (Slate-400, 
    Text-xs).
- Menu Items:
    Dashboard (Active): Background putih penuh (bg-white), teks biru terang (text-blue-600), icon kotak/home biru.
    Template: Icon dokumen/layout, teks slate-300, hover:text-white.
    User: Icon grup/user, teks slate-300, hover:text-white.
    Settings: Icon gear/roda gigi, teks slate-300, hover:text-white.
    
B. Header / Topbar Utama
- Kiri: Teks judul halaman "Dashboard" (text-2xl font-bold text-slate-900).
- Kanan: User Profile Badge:
    Avatar icon bulat outline (text-slate-400).
    Label: Super Admin (text-sm font-semibold text-slate-800).
    Chevron dropdown icon v kecil (text-slate-400).
    
C. Baris 1: Stat Summary Cards (Grid 2 Kolom)
 1. Card Template Aktif:
    Icon Kiri: Lingkaran background biru muda (bg-blue-50), icon layout/tabel biru (text-blue-600).
     Konten Teks:
        Judul: Template Aktif: [Nama Template] (text-lg font-bold text-slate-900).
        Subtitle: Template yang sedang digunakan saat ini (text-sm text-slate-400).
        Aksi Cepat: Link teks biru "Edit di Builder >" dan "Lihat Live Website >".
    Watermark Kanan: Icon siluet gedung/pilar transparan (opacity-10 text-blue-900 / text-slate-200 w-16 h-16).
 2. Card Jumlah Admin:
    Icon Kiri: Lingkaran background biru muda (bg-blue-50), icon dua user (text-blue-600).
    Konten Teks:
        Judul: Jumlah Admin Kedinasan: [Jumlah] (text-lg font-bold text-slate-900).
        Subtitle: Total admin kedinasan terdaftar (text-sm text-slate-400
    Watermark Kanan: Icon siluet grup user transparan (opacity-10 w-16 h-16).

D.Baris 2: Detail Section (Grid 2 Kolom)
 1. Card "Aktivitas Terbaru" (Kiri):
  - Title: Aktivitas Terbaru (text-base font-bold text-slate-900 mb-6).
  - List Item (5 Baris Aktivitas):
    Baris 1: Icon lingkaran biru (Dokumen) $\rightarrow$ Template "[Nama Template]" diaktifkan (Subteks: Template berhasil diatur sebagai template aktif) $\rightarrow$ Timestamp: Hari ini, 09:45.
    Baris 2: Icon lingkaran hijau (User) $\rightarrow$ Admin kedinasan baru ditambahkan (Subteks: Akun "Diskominfo Bidang IKP" berhasil ditambahkan) $\rightarrow$ Timestamp: Kemarin, 14:30.
    Baris 3: Icon lingkaran kuning/oranye (Pensil/Edit) $\rightarrow$ Template "Layanan Publik" diperbarui (Subteks: Perubahan pada konfigurasi template berhasil disimpan) $\rightarrow$ Timestamp: Kemarin, 10:12.
    Baris 4: Icon lingkaran ungu (User Edit) $\rightarrow$ Admin kedinasan diperbarui (Subteks: Informasi akun "Dinas Pariwisata" berhasil diperbarui) $\rightarrow$ Timestamp: 2 hari yang lalu, 16:05.
    Baris 5: Icon lingkaran abu-abu (Gear) $\rightarrow$ Pengaturan sistem diperbarui (Subteks: Konfigurasi pengaturan umum berhasil disimpan) $\rightarrow$ Timestamp: 3 hari yang lalu, 11:20.
 Card Footer: Link teks biru dengan panah Lihat semua aktivitas > (text-blue-600 text-sm font-semibold hover:underline).
 2. Card "Monitoring Website Dinas" (Kanan):
  - Title: Monitoring Website Dinas (text-base font-bold text-slate-900).
  - Badge Counter: [Jumlah] Terdaftar (text-blue-700 bg-blue-50).
  - Mini Status Bar (3 Kolom Status):
     Kolom 1: Live / Aktif (badge hijau, jumlah website aktif).
     Kolom 2: Pemeliharaan (badge kuning, jumlah website dalam maintenance).
     Kolom 3: Nonaktif (badge abu-abu, jumlah website nonaktif).
  - Daftar Website Dinas Terkini:
     Avatar inisial dinas, nama website, nama dinas & slug domain, badge status operasional, tombol aksi cepat Lihat Website (ikon eksternal) dan Edit Website (ikon pensil).
  - Card Footer:
     Kiri: Tautan cepat + Daftarkan Website Baru (text-blue-600 font-semibold).
     Kanan: Tautan Kelola Semua Website > (text-slate-500 font-medium).
E. Footer Halaman
 - Pembatas garis horizontal tipis atau margin luas ke bawah.
 - Kiri: Pyojek CMS Diskominfo Kota Batu (text-xs text-slate-500).
 - Kanan: © 2024 Diskominfo Kota Batu (text-xs text-slate-500).