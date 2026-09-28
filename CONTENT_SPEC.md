**# CONTENT_SPEC.md — Spesifikasi Konten Pyojek CMS**

**\*\*Produk:\*\*** Pyojek CMS — Content Management System Website Dinas  

**\*\*Instansi:\*\*** Diskominfo Kota Batu  

**\*\*Dokumen:\*\*** CONTENT_SPEC.md  

**\*\*Versi:\*\*** 1.0  

**\*\*Status:\*\*** Baseline Specification  

**\*\*Sumber:\*\*** Hasil diskusi dan keputusan requirement Pyojek CMS sampai September 2026

**## 1. Tujuan Dokumen**

Dokumen ini mendefinisikan jenis konten, fungsi pengelolaan konten, batas pengelolaan, serta aturan publikasi pada Pyojek CMS.

Fokus dokumen:

\- Posts

\- Media

\- Pages

\- Appearance Slot

\- hubungan content dengan template

\- ownership/tenant isolation

\- aturan publikasi

Dokumen ini tidak mendefinisikan struktur database secara rinci, teknologi, RBAC secara lengkap, visual builder secara rinci, arsitektur sistem, atau status implementasi.

**## 2. Konsep Konten**

Pyojek CMS menggunakan konsep **\*\*Single Global Template, Multi-Tenant Data\*\***.

Struktur tampilan website dikendalikan oleh Super Admin, sedangkan data pada setiap website berasal dari Dinas yang bersangkutan.

Aturan utama:

\`\`\`text

1 Dinas

   └── 1 Website

        ├── Appearance

        ├── Posts

        ├── Pages

        └── Media

\`\`\`

Data satu Dinas tidak boleh dapat diakses atau dimodifikasi oleh Admin Dinas lain.

**## 3. Aktor Pengelola Konten**

**### 3.1 Super Admin**

Super Admin Diskominfo bertanggung jawab terhadap:

\- pengelolaan Admin Kedinasan;

\- template global;

\- struktur template;

\- konfigurasi sistem.

**\*\*Super Admin tidak mengelola Posts, Media, Pages, atau Appearance milik Dinas sebagai aktivitas operasional konten.\*\***

**### 3.2 Admin Kedinasan**

Admin Kedinasan merupakan pengelola konten website Dinasnya.

Admin dapat mengelola:

\- Posts;

\- Media;

\- Pages;

\- Appearance Slot.

Seluruh pengelolaan dibatasi pada Dinas/website yang menjadi kewenangannya.

**### 3.3 Masyarakat**

Masyarakat adalah pengunjung website publik.

Masyarakat hanya dapat membaca data yang telah dipublikasikan dan tidak memiliki akses ke CMS.

**## 4. Posts**

**### 4.1 Fungsi**

Posts digunakan untuk konten informasi yang bersifat publik dan memiliki waktu publikasi.

Jenis Posts yang telah ditetapkan:

1\. Berita

2\. Pengumuman

3\. Kegiatan

**### 4.2 Operasi**

Admin Kedinasan dapat:

\- membuat;

\- membaca;

\- mengubah;

\- menghapus;

\- mempublikasikan Post milik Dinasnya.

Super Admin tidak mengelola Posts.

Masyarakat hanya dapat membaca Posts yang telah dipublikasikan.

**### 4.3 Data Utama**

Data yang telah disebutkan dalam requirement:

\- judul;

\- slug;

\- body/isi;

\- thumbnail;

\- direct_link (tautan dokumen eksternal / Google Drive);

\- jenis/kategori Post;

\- status;

\- waktu publikasi;

\- website/Dinas pemilik;

\- pengguna pembuat.

Apakah tiga jenis Post diimplementasikan sebagai \`type\`, \`category\`, atau mekanisme taxonomy lain belum ditetapkan dan menjadi detail \`SCHEMA.md\`.

**### 4.4 Status**

Status yang telah ditetapkan:

\- \`Draft\`

\- \`Published\`

**\*\*Draft\*\***

\- masih dalam proses penyusunan;

\- dapat dikelola Admin;

\- tidak boleh tampil pada website publik.

**\*\*Published\*\***

\- telah dipublikasikan;

\- dapat ditampilkan pada website publik.

**## 5. Media**

**### 5.1 Fungsi**

Media digunakan untuk mengelola file yang digunakan oleh website Dinas, terutama:

\- gambar/foto;

\- dokumen PDF.

Seluruh pengunggahan berkas gambar melalui formulir Posts (thumbnail/gambar artikel) dan Pages (gambar ilustrasi) otomatis dicatat ke dalam modul Media milik dinas terkait.

**### 5.2 Format**

Format yang didukung sistem:

\`\`\`text
Gambar:
- .jpg / .jpeg
- .png
- .webp

Dokumen:
- .pdf
- .doc / .docx (Word)
- .xls / .xlsx (Excel)
- .ppt / .pptx (PowerPoint)
\`\`\`

Setiap berkas diproses secara otomatis oleh `MediaOptimizerService` (kompresi WebP untuk gambar, kompresi ZIP DEFLATE level 9 untuk berkas Office Open XML, dan optimasi stream PDF).

**### 5.3 Operasi**

Admin Kedinasan dapat:

\- upload Media;

\- melihat/mengelola Media milik Dinasnya.

Super Admin tidak mengelola Media operasional Dinas.

**### 5.4 Data Media**

Data yang dibutuhkan sekurang-kurangnya:

\- nama file;

\- path/lokasi penyimpanan;

\- tipe file;

\- ukuran file;

\- website/Dinas pemilik;

\- pengguna yang mengunggah.

Batas ukuran maksimum berkas:
- Berkas Media/Dokumen publik: Maksimal 10 MB (`10240 KB`).
- Berkas Gambar Postingan & Halaman: Maksimal 2 MB (`2048 KB`).
- Berkas Banner Hero Appearance: Maksimal 5 MB (`5120 KB`).
- Berkas Logo Dinas Appearance: Maksimal 2 MB (`2048 KB`).

**### 5.5 Isolasi Media**

Media harus terisolasi berdasarkan Dinas/website:

\`\`\`text

Dinas A

 └── Media A

Dinas B

 └── Media B

\`\`\`

Admin Dinas A tidak boleh mengelola Media Dinas B.

**## 6. Pages

### 6.1 Fungsi

Pages digunakan untuk konten institusional yang relatif statis.

Halaman default yang disediakan template:

- Profil;
- Visi & Misi;
- Struktur Organisasi;
- Kontak.

Daftar tersebut merupakan **default content structure**, bukan daftar halaman yang wajib dan permanen untuk setiap Dinas.

Admin Kedinasan dapat membuat Page tambahan sesuai kebutuhan instansinya.

### 6.2 Operasi

Admin Kedinasan dapat mengelola isi Pages milik Dinasnya, termasuk:

- membuat;
- membaca;
- mengubah;
- menghapus;
- mengatur status publikasi (`draft` / `published`);
- menentukan penempatan Page pada navigasi yang tersedia.

Pada website publik:

- Halaman dapat ditempatkan pada 7 Menu Utama beserta rincian sub-menu standarnya (Profil, Layanan, Informasi, Publikasi, Kontak, Tab Beranda, atau Menu Utama Kustom);
- Halaman dengan `direct_link` (misalnya Google Drive, Instagram, Google Maps, atau portal eksternal) akan langsung diarahkan ke tautan tujuan saat diklik;
- Setiap halaman statis dapat diakses secara utuh melalui rute publik `/site/{identifier}/page/{slug}`;
- Pada halaman beranda, elemen `Static Content` dapat menampilkan tab navigasi interaktif untuk halaman berpenempatan `beranda` dengan tombol aksi **"Baca Halaman Selengkapnya"**.

Admin tidak dapat mengubah layout visual halaman melalui Pages.

Super Admin tidak mengelola isi Pages milik Dinas.

### 6.3 Batasan

Pages bukan visual page builder. Admin tidak mendapatkan:

- drag-and-drop layout builder;
- pengubahan section;
- pengubahan struktur card;
- pengubahan desain navbar;
- pengubahan footer;
- arbitrary CSS;
- arbitrary JavaScript/script;
- eksekusi kode PHP.

**Catatan:** Admin dapat mengelola **struktur item navigasi/header menu** melalui modul Navigation, tetapi tidak dapat mengubah desain, layout, atau struktur visual navbar yang ditentukan oleh Template.

## 8. Navigation / Header Menu

### 7.1 Fungsi

Navigation digunakan untuk mengelola **struktur menu yang ditampilkan pada header/navbar website publik**.

Template menyediakan struktur navigasi default yang bersifat general untuk berbagai jenis instansi pemerintahan. Struktur default bukan struktur yang wajib dan permanen.

Default Navigation:

```text
Header
├── Beranda
├── Profil
│   ├── Tentang Instansi
│   ├── Sejarah
│   ├── Visi & Misi
│   ├── Tugas & Fungsi
│   ├── Struktur Organisasi
│   └── Pejabat / Pimpinan
├── Layanan
│   ├── Daftar Layanan
│   ├── Persyaratan
│   ├── Prosedur / Alur Layanan
│   ├── Formulir
│   └── Cek Status Layanan
├── Informasi
│   ├── Pengumuman
│   ├── Agenda
│   ├── Informasi Publik
│   └── FAQ
├── Berita
├── Publikasi
│   ├── Dokumen
│   ├── Peraturan
│   ├── Laporan
│   ├── Data / Statistik
│   └── Galeri
└── Kontak
    ├── Alamat
    ├── Email
    ├── Telepon
    ├── Media Sosial
    └── Lokasi / Peta
```

### 7.2 Prinsip General Template

Default Navigation harus cukup general untuk digunakan oleh berbagai jenis Dinas/instansi.

Menu level utama default:

- Beranda;
- Profil;
- Layanan;
- Informasi;
- Berita;
- Publikasi;
- Kontak.

Struktur submenu merupakan **default recommendation**, bukan struktur yang harus selalu digunakan.

Contoh:

```text
Profil
├── Tentang Instansi
├── Sejarah
├── Visi & Misi
├── Tugas & Fungsi
├── Struktur Organisasi
└── Pejabat / Pimpinan
```

Admin dapat menyesuaikan struktur tersebut dengan kebutuhan instansinya.

### 7.3 Operasi Navigation

Admin Kedinasan dapat:

- menambah menu;
- menghapus menu;
- mengubah nama menu;
- mengubah urutan menu;
- membuat submenu;
- membuat sub-submenu;
- mengubah target/URL menu;
- menghubungkan menu dengan Page;
- menghubungkan menu dengan Post listing/category yang tersedia;
- menghubungkan menu dengan fitur/modul yang tersedia;
- menggunakan direct link eksternal;
- mengaktifkan atau menonaktifkan item navigasi.

Hierarki Navigation mendukung hingga **4 level kedalaman**:

```text
Level 1: Menu Utama Header (Root)
└── Level 2: Sub-menu
    └── Level 3: Wadah Sub-sub-bab
        └── Level 4: Isi Wadah
```

- Jika halaman Level 4 (isi wadah) diakses melalui URL publik secara langsung, sistem secara otomatis mengarahkannya (*redirect*) ke halaman wadah induknya (Level 3).
- Pada tabel manajemen Pages Admin Kedinasan, halaman diurutkan secara hierarkis: Tab Beranda selalu di urutan teratas, diikuti pengelompokan per Menu Utama Header beserta anak-anaknya. Sub-menu berstatus draft diposisikan tetap sejajar dengan sub-menu publish di atas sub-sub menu.

### 7.4 Batasan Navigation

Admin Kedinasan **tidak dapat**:

- mengubah desain visual navbar;
- mengubah warna/style navbar;
- mengubah typography navbar;
- mengubah struktur HTML/layout navbar;
- membuat arbitrary CSS;
- membuat arbitrary JavaScript;
- mengubah komponen visual navbar yang ditentukan Template;
- masuk ke Template Builder.

Dengan demikian:

```text
Template
├── menentukan bagaimana navbar terlihat
├── menentukan komponen/navbar layout
└── menyediakan default navigation

Admin Kedinasan
├── menentukan menu apa yang ditampilkan
├── menentukan urutan menu
├── menentukan hierarchy menu
└── menentukan tujuan setiap menu
```

### 7.5 Hubungan Navigation dengan Pages

Page dan Navigation merupakan dua konsep yang berbeda.

```text
Page
└── menyediakan konten

Navigation
└── menyediakan cara pengunjung menuju konten
```

Satu Page dapat direferensikan oleh satu item Navigation.

Item Navigation juga dapat menunjuk ke:

- Page;
- Post listing;
- fitur/modul;
- direct link eksternal.

Tidak semua Page harus muncul di Navigation.

Tidak semua item Navigation harus berupa Page.

### 7.6 Default Navigation vs Tenant Navigation

Template global menyediakan default structure:

```text
GLOBAL TEMPLATE
└── Default Navigation
```

Saat website Dinas dibuat:

```text
Dinas A
└── Navigation A
    ├── menggunakan default structure
    └── dapat disesuaikan Admin A

Dinas B
└── Navigation B
    ├── menggunakan default structure
    └── dapat disesuaikan Admin B
```

Perubahan Navigation Dinas A tidak boleh mengubah Navigation Dinas B atau default Template secara global.

### 7.7 Ownership dan Isolation

Navigation merupakan data milik tenant/website setelah website Dinas dibuat dan dikonfigurasi.

Admin Dinas A tidak boleh:

- membaca Navigation Dinas B;
- mengubah Navigation Dinas B;
- menghapus Navigation Dinas B.

Perubahan Navigation harus diverifikasi pada backend berdasarkan ownership Dinas/Website.

## 8. Appearance Slot**

**### 7.1 Fungsi**

Appearance digunakan untuk mengisi data identitas/visual Dinas yang telah disediakan sebagai **\*\*Editable Slot\*\*** oleh template.

Appearance pada Admin Kedinasan bukan template builder.

**### 7.2 Slot yang Telah Ditentukan**

Admin dapat mengisi:

\- logo resmi Dinas (unggah berkas gambar langsung);

\- teks slogan & deskripsi Hero;

\- banner/background Hero;

\- informasi identitas dan kontak resmi pada Footer (judul, slogan, info tambahan, alamat, telepon, email).

Contoh informasi kontak:

\- alamat;

\- nomor telepon;

\- informasi kontak resmi lainnya sesuai slot.

**### 7.3 Batasan**

Admin:

\- dapat mengisi slot yang disediakan;

\- tidak dapat membuat slot baru;

\- tidak dapat mengubah posisi slot;

\- tidak dapat mengubah struktur section/card/navbar/footer;

\- tidak dapat memasukkan arbitrary CSS;

\- tidak dapat memasukkan arbitrary JavaScript/script;

\- tidak dapat menjalankan kode PHP.

**### 7.4 Hubungan dengan Template**

\`\`\`text

Super Admin

    │

    └── Menentukan struktur template

             │

             └── Menentukan Editable Slot

                        │

                        ▼

                Admin Kedinasan

                        │

                        └── Mengisi nilai slot

                                  │

                                  ▼

                           Website Dinas

\`\`\`

Template menentukan tempat dan jenis data; Admin Kedinasan menyediakan nilai data.

**## 9. Hubungan Content dengan Template**

Template global menentukan bagaimana content ditampilkan.

Data content tetap menjadi data tenant dan tidak menjadi bagian dari struktur layout.

\`\`\`text

GLOBAL TEMPLATE

     │

     ├── Struktur

     ├── Section

     ├── Widget/Component

     ├── Layout

     └── Editable Slot

              │

              ▼

       TENANT CONTENT

              │

              ├── Appearance

              ├── Posts

              ├── Pages

              └── Media

              │

              ▼

        PUBLIC WEBSITE

\`\`\`

Contoh:

\`\`\`text

Template:

Hero Widget

  ├── Slot: Hero Title

  └── Slot: Hero Background

Dinas:

  Hero Title = "Dinas Pertanian Kota Batu"

  Hero Background = "banner-pertanian.jpg"

\`\`\`

Struktur Hero berasal dari template; nilai aktual berasal dari Dinas.

**## 10. Dynamic Posts pada Website Publik**

Template dapat menyediakan komponen untuk menampilkan Posts.

Secara konseptual:

\`\`\`text

Posts Grid

 ├── Source: Posts

 ├── Filter: Published

 └── Limit: sesuai konfigurasi template

\`\`\`

Data yang ditampilkan harus:

\- berasal dari Dinas/website yang sedang diakses;

\- berstatus Published.

Draft tidak boleh muncul di website publik.

**## 11. Media/Dokumen Publik**

Template menyediakan komponen \`Media / Document List\` untuk menampilkan Media/Dokumen publik.

Format tampilan global template:

\- Tata letak grid: **\*\*2 baris × 3 kolom\*\*** (menampilkan 6 berkas terkini).

\- Pratinjau media nyata: Jendela gambar asli untuk berkas grafis (JPG, PNG, WEBP) dan embedded document preview terisolasi untuk dokumen PDF (bukan sekadar ikon atau teks ekstensi).

\- Aksi: Tombol buka/pratinjau layar penuh dan tombol unduh berkas resmi.

Data berasal dari Media Dinas terkait.

Contoh:

\- foto kegiatan;

\- gambar informasi;

\- dokumen PDF yang ditujukan untuk publik.

Belum ditentukan apakah seluruh Media otomatis publik setelah upload atau membutuhkan status/penandaan publik. **\*\*TBD.\*\***

**## 12. Content Ownership**

Setiap konten operasional harus memiliki hubungan dengan Dinas/Website pemilik.

\`\`\`text

Admin Dinas A

      │

      └── Website Dinas A

             ├── Posts A

             ├── Pages A

             ├── Media A

             └── Appearance A

\`\`\`

Ownership menjadi dasar tenant isolation.

**## 13. Aturan Publikasi**

\`\`\`text

Draft

  │

  └── Tidak tampil di publik

Published

  │

  └── Dapat tampil di website publik

\`\`\`

Website publik bersifat read-only terhadap data CMS.

Pengunjung tidak dapat membuat, mengubah, atau menghapus content CMS.

**## 14. Alur Content Admin Kedinasan**

\`\`\`text

Login

  │

  ▼

Dashboard CMS

  │

  ├── Posts

  │     ├── Create

  │     ├── Edit

  │     ├── Delete

  │     └── Publish

  │

  ├── Media

  │     ├── Upload

  │     └── Manage

  │

  ├── Pages

  │     └── Manage Content

  │

  └── Appearance

        └── Fill Editable Slots

\`\`\`

Admin Kedinasan tidak masuk ke Template Builder.

**## 15. Ringkasan Batas Pengelolaan**

\| Konten | Super Admin | Admin Kedinasan | Masyarakat |

\|---|---|---|---|

\| Posts | DENY | ALLOW / OWN | PUBLIC READ |

\| Media | DENY | ALLOW / OWN | Public sesuai aturan publikasi |

\| Pages | DENY | ALLOW / OWN | PUBLIC READ |

\| Appearance Slot | DENY / GLOBAL TEMPLATE OWNER | ALLOW / OWN | DENY |

\| Template Structure | ALLOW / GLOBAL | DENY | DENY |

Matriks hak akses lengkap dan normatif berada di \`ROLES_RBAC.md\`.

**## 16. Keamanan Content**

**### 16.1 Tenant Isolation**

Konsep hubungan:

\`\`\`text

Authenticated User

       │

       ▼

User.dinas_id

       │

       ▼

Dinas

       │

       ▼

Website

       │

       ▼

Content

\`\`\`

Sistem tidak boleh memperbolehkan Admin mengakses content tenant lain hanya dengan mengganti ID pada URL, request, atau parameter.

**### 16.2 Backend Authorization**

Ownership dan authorization harus diverifikasi pada backend. Frontend bukan security boundary.

**### 16.3 Arbitrary Code**

Content tidak boleh menjadi sarana untuk menjalankan:

\- PHP arbitrary code;

\- JavaScript mentah;

\- arbitrary script;

\- mekanisme eksekusi kode lain tanpa kontrol.

**## 17. Keputusan Final Spesifikasi Konten**

1. **Ukuran Maksimum Media:** Maksimal 10 MB untuk pustaka media, 5 MB untuk banner hero, dan 2 MB untuk thumbnail post dan gambar halaman.
2. **Status Publikasi Media:** Setiap berkas yang diunggah ke pustaka media langsung berstatus siap tayang dan dapat diintegrasikan pada komponen publikasi maupun unduhan dokumen publik.
3. **Attachment pada Pages:** Menggunakan atribut `image` (tercatat di tabel `media`) dan `direct_link` (tautan Google Drive, mailto:, tel:, atau URL eksternal).
4. **Editor Konten:** Menggunakan textarea formulir standar dengan sanitasi pembersihan tag skrip berbahaya pada layer `CanvasRenderer`.
5. **Kategori Post:** Menggunakan kolom `type` dengan nilai baku: `Berita`, `Pengumuman`, dan `Kegiatan`.
6. **Pagination dan Filtering:** Menggunakan `LengthAwarePaginator` bawaan Laravel dengan opsi query string filter `search`, `status`, `type`, dan `placement`.
7. **SEO dan Identitas:** Menggunakan kolom `slug` unik per website, `general_config.site_title`, dan meta pengaturan privasi (`privacy_config.allow_indexing`).
8. **Struktur Navigasi:** Menggunakan kolom `parent_id` pada tabel `pages` dengan dukungan hierarki hingga 4 level kedalaman.
9. **Pratinjau Draft:** Konten berstatus draft hanya dapat diakses melalui pratinjau internal CMS oleh Admin Dinas pemilik akun atau Super Admin. Konten draft dilarang keras tampil di publik.

**## 18. Prinsip Implementasi**

1\. **\*\*Content belongs to a tenant.\*\*** Setiap content operasional memiliki ownership Dinas/Website.

2\. **\*\*Admin only manages own content.\*\*** Admin hanya mengelola content Dinasnya.

3\. **\*\*Template and content are separate concerns.\*\*** Template menentukan struktur; content menyediakan data.

4\. **\*\*Admin is a content filler, not a template architect.\*\***

5\. **\*\*Published data only is public.\*\***

6\. **\*\*Backend authorization is mandatory.\*\***

7\. **\*\*Requirement yang belum diputuskan ditandai TBD dan tidak diasumsikan sebagai implementasi final.\*\***

**## 19. Definition of Done — Content Module**

\- [x] Admin dapat membuat Post.

\- [x] Admin dapat mengubah Post miliknya.

\- [x] Admin dapat menghapus Post miliknya.

\- [x] Admin dapat mempublikasikan Post miliknya.

\- [x] Post Draft tidak tampil di website publik.

\- [x] Admin tidak dapat mengakses Post Dinas lain.

\- [x] Admin dapat upload Media dengan format yang diizinkan.

\- [x] Media terisolasi berdasarkan Dinas/Website.

\- [x] Admin dapat mengelola isi Pages miliknya.

\- [x] Admin tidak dapat mengubah layout melalui Pages.

\- [x] Admin dapat mengisi Appearance Slot yang tersedia.

\- [x] Admin tidak dapat masuk ke Template Builder.

\- [x] Template dapat menggunakan content tenant melalui slot/component yang ditentukan.

\- [x] Website publik hanya membaca data tenant yang sedang dirender.

\- [x] Template publik menyajikan 7 Menu Utama standar (Beranda, Profil, Layanan, Informasi, Berita, Publikasi, Kontak).

\- [x] Admin dapat menempatkan Post dan Page ke seluruh menu dan sub-menu yang tersedia.

\- [x] Sub-sub bab dapat berfungsi sebagai wadah penampung isi bagian di dalamnya.

\- [x] Tidak ada arbitrary code execution melalui input content.

\- [x] Authorization tenant diverifikasi pada backend.

**## 20. Status Dokumen**

**\*\*Status:\*\*** Baseline Specification

Dokumen ini merupakan acuan awal untuk modul content management Pyojek CMS. Detail database Navigation, teknologi, arsitektur, dan mekanisme Template Builder mengikuti dokumen masing-masing serta requirement terbaru dari project owner.