# Pyojek CMS — TEMPLATE.md

**Produk:** Pyojek CMS — Content Management System Website Dinas  
**Dokumen:** `TEMPLATE.md`  
**Versi:** 2.2 (Refined Visual Builder Baseline)  
**Status:** Baseline Specification  
**Target Pengguna Builder:** Super Admin (Diskominfo)

---

# 1. Konsep Utama

Pyojek CMS memisahkan peran penyusunan arsitektur tampilan dan pengelolaan isi.

## 1.1 Super Admin sebagai Template Architect

Super Admin:
- Menggunakan antarmuka visual builder berbasis drag-and-drop / section-block canvas.
- Menyusun tata letak website dinas.
- Menentukan struktur **Section → Container → Component**.
- Menentukan jenis komponen, struktur grid, hierarki vertikal, properti layout, dan batas Editable Slots.
- Tidak mengelola postingan berita harian dinas; builder mengatur bagaimana wadah data ditampilkan pada website publik.

## 1.2 Admin Kedinasan sebagai Content Filler

Admin Kedinasan:
- Dilarang masuk dan tidak memiliki akses ke kanvas Builder.
- Mengisi dan mengelola data melalui formulir/dashboard CMS standar.
- Tidak dapat mengubah struktur template global.
- Data yang diisikan akan dirender ke dalam Editable Slots yang disediakan blueprint template aktif.

## 1.3 Single Global Blueprint

Template global menjadi blueprint yang digunakan oleh website Dinas yang terhubung dengannya.

Mekanisme propagasi perubahan template ke website dinas berstatus **TBD** (lihat Bab 11).

---

# 2. Struktur Visual Builder

Canvas Builder menggunakan hierarki visual yang jelas dan bersarang. Canvas **bukan daftar komponen datar**.

Struktur baseline:

```text
SECTION
└── CONTAINER
    ├── COMPONENT
    ├── COMPONENT
    └── COMPONENT
```

Contoh tampilan:

```text
┌─────────────────────────────────────────────┐
│ SECTION                                     │
│                                             │
│  ┌───────────────────────────────────────┐  │
│  │ CONTAINER                             │  │
│  │                                       │  │
│  │  ┌─────────────────────────────────┐  │  │
│  │  │ HERO                            │  │  │
│  │  └─────────────────────────────────┘  │  │
│  │                                       │  │
│  │  ┌─────────────────────────────────┐  │  │
│  │  │ POSTS GRID                      │  │  │
│  │  └─────────────────────────────────┘  │  │
│  │                                       │  │
│  └───────────────────────────────────────┘  │
│                                             │
└─────────────────────────────────────────────┘
```

Representasi visual harus mencerminkan hierarki Component Tree. Detail schema JSON final tetap mengikuti `SCHEMA.md`/dokumen arsitektur terkait; ilustrasi ini tidak boleh dianggap sebagai schema database final.

---

# 3. Batasan Hak Editing Admin Kedinasan

| Komponen / Properti | Hak Akses Admin Kedinasan | Keterangan |
|---|---|---|
| Header Logo / Logo Dinas | **Boleh Upload / Edit** | Mengunggah berkas logo instansi dari komputer (kecuali dikunci Template Controlled) |
| Header Navbar Navigation | **Template Blueprint** | Struktur navigasi tetap; mendukung hierarki menu dinamis (Menu Utama -> Sub-menu -> Sub-sub-menu) untuk seluruh menu di header yang otomatis memuat halaman institusi milik dinas |
| Hero Title / Slogan | **Boleh Edit** | Mengubah teks judul melalui modul tampilan/appearance |
| Hero Description | **Boleh Edit** | Mengubah teks deskripsi/subjudul |
| Hero Image / Banner | **Boleh Upload / Edit** | Mengunggah gambar aset banner milik dinasnya |
| Static Content / Profil Ringkas | **Boleh Edit** | Mengisi teks profil ringkas beranda & artikel halaman lengkap via modul Pages |
| Media & Dokumen Publik | **Boleh Upload / Edit** | Mengunggah dokumen resmi PDF dan berkas gambar via modul Media |
| Hero Height / Ukuran | **Dilarang** | Dikunci mutlak oleh template |
| Hero Position / Alignment | **Dilarang** | Dikunci mutlak oleh template |
| Section Order (Urutan Seksi) | **Dilarang** | Ditentukan sepenuhnya pada kanvas Builder |
| Card Structure / Grid Count | **Dilarang** | Ditentukan sepenuhnya pada kanvas Builder |
| Navbar Structure & Position | **Dilarang** | Struktur navigasi dikunci oleh template |
| Footer Content (Kontak, Alamat) | **Boleh Edit** | Mengisi nilai kontak, email, dan alamat dinas |
| Footer Structure / Grid | **Dilarang** | Layout baris dan kolom footer dikunci oleh template |
| Custom CSS / JavaScript | **Dilarang** | Tidak disediakan editor kode/skrip bebas |

---

# 4. Penanda Hak Edit pada Builder

Inspector Builder harus membedakan secara eksplisit dua kategori konfigurasi.

## 4.1 Admin Editable

Slot/properti yang nantinya dapat diisi atau diubah Admin Kedinasan diberi penanda:

```text
Admin Editable
```

Contoh:

```text
HERO
────────────────────────
Content

Title
[ Selamat Datang        ]
Admin Editable

Description
[ Portal resmi ...     ]
Admin Editable

Background Image
[ Upload Image          ]
Admin Editable
```

Penanda ini menunjukkan bahwa Super Admin sedang menentukan **slot konten**, bukan memberikan Admin akses ke Builder.

## 4.2 Template Controlled

Property yang tetap dikendalikan Super Admin melalui template diberi penanda:

```text
Template Controlled
```

Contoh:

```text
LAYOUT
────────────────────────
Height
[ 480px ]
Template Controlled

Alignment
[ Center ]
Template Controlled

Grid Count
[ 3 ]
Template Controlled
```

Admin Kedinasan tidak boleh mengubah property yang berstatus `Template Controlled`.

---

# 5. Komponen Bawaan Sistem (Available Components)

Super Admin menyusun template menggunakan daftar komponen bawaan yang telah disediakan sistem (**pre-defined system components**). Sistem tidak mendukung pembuatan komponen/plugin kustom baru secara bebas.

Daftar komponen baseline:

- **Container** — Wadah pembungkus layout grid 1 kolom, 2 kolom, 3 kolom, atau 4 kolom (mendukung *nested dropzone* untuk menampung komponen anak secara hierarkis).
- **Hero** — Wadah banner utama, judul, deskripsi, dan tombol interaksi kontak resmi instansi (posisi paten: center).
- **Posts Grid** — Wadah daftar berita/pengumuman dalam bentuk kartu kisi responsif.
- **Static Content** — Wadah teks profil ringkas, visi-misi, dan tab navigasi interaktif beranda.
- **Media / Document List** — Wadah daftar unduhan dokumen publik resmi (PDF) dan galeri berkas.
- **Footer** — Wadah navigasi bawah, kontak resmi kedinasan, dan identitas penutup.

*Catatan Penyederhanaan:* Komponen `Posts Carousel` dan `Contact / Dinas Info` telah dihapus dari sistem. Berita ditampilkan terpusat melalui `Posts Grid`, sedangkan informasi kontak instansi dikelola mandiri via modul **Pages** dan ditampilkan baku pada **Footer**.

---

# 6. Spesifikasi & Metadata Editable Slot

Slot pada komponen memiliki metadata logis yang membedakan properti tata letak milik Super Admin dari slot konten milik Admin Kedinasan.

## 6.1 Struktur Metadata Slot

Setiap slot konseptual memiliki atribut:

```text
id
type
data_source
editable_by
required
validation
```

- `id` — Identifier unik slot dalam komponen.
- `type` — Jenis tipe data slot (`text`, `textarea`, `image`, `document`, `dimension`, `color`).
- `data_source` — Referensi sumber data logis (misal: `appearance`, `posts`, `pages`, `dinas`).
- `editable_by` — Penanggung jawab pengisian (`super_admin` atau `admin_dinas`).
- `required` — Boolean status keharusan pengisian (`true` / `false`).
- `validation` — Aturan validasi, misalnya format file gambar atau batas karakter teks.

## 6.2 Perbedaan Slot Konten vs Properti Layout

### Header Logo — Editable Slot

```yaml
id: "header_logo_01"
type: "image"
data_source: "appearance"
editable_by: "admin_dinas"
required: false
validation: "mimes:jpg,jpeg,png,webp,svg|max:2048"
```

Status: **Admin Editable**.
Ketika diset `admin_dinas` oleh Super Admin pada simpul Header / Navbar, Admin Kedinasan dapat mengunggah berkas logo dari komputer mereka melalui modul Appearance (`/dinas/appearance`), yang otomatis tersimpan di storage server, dicatat ke tabel `media`, dan ditautkan ke navbar website publik.

Jika slot diset `super_admin` (**Template Controlled**), slot logo dikunci dan Admin Kedinasan tidak diizinkan mengubah/mengunggah logo. Super Admin dapat menentukan logo bawaan blueprint melalui field `default_value`.

### Hero Background — Editable Slot

```yaml
id: "hero_bg_01"
type: "image"
data_source: "appearance"
editable_by: "admin_dinas"
required: false
validation: "mimes:jpg,jpeg,png,webp|max:5120"
```

Status: **Admin Editable**.
Ketika diset `admin_dinas` oleh Super Admin, Admin Kedinasan dapat mengunggah berkas gambar langsung dari perangkat lokal melalui modul Appearance Slots (`/dinas/appearance`), yang otomatis disimpan ke penyimpanan server, dicatat ke tabel `media`, dan ditautkan ke hero website publik.

Jika slot diset `super_admin` (**Template Controlled**), formulir unggah pada Admin Kedinasan dikunci dan sistem backend menolak modifikasi gambar hero oleh Admin Kedinasan. Super Admin dapat menentukan URL/gambar bawaan blueprint melalui field `default_value`.

### Hero Height — Layout Property

```yaml
id: "hero_height_01"
type: "dimension"
data_source: "template_config"
editable_by: "super_admin"
default_value: "480px"
```

Status: **Template Controlled**.

### Posts Source — Dynamic Content Binding

```yaml
id: "posts_source_01"
type: "collection"
data_source: "posts"
binding: "posts.published"
editable_by: "admin_dinas"
```

Status: **Admin Managed Content (CRUD Form)**.
Komponen `Posts Grid` tidak memiliki slot teks statis tunggal, melainkan terikat secara dinamis ke koleksi `posts.published` milik website dinas terkait. Admin Kedinasan mengelola konten artikel melalui modul manajemen Posts (`/dinas/posts`) menggunakan formulir standar (tambah, edit, hapus, dan pengalihan status draft/published). Konten berstatus `published` otomatis dirender ke kisi `Posts Grid`, sedangkan konten `draft` hanya tersimpan di dashboard admin dan diisolasi dari tampilan publik. Super Admin mengontrol tata letak (jumlah kolom 1–4 dan limit tampil 1–24) pada kanvas builder menggunakan kontrol form baku (bukan teks bebas), namun tidak mengelola konten artikel dinas. Slot `items` pada komponen koleksi ini tidak memerlukan teks fallback karena sistem otomatis merender *empty state* yang rapi jika belum ada postingan.

### Static Content / Profil Ringkas — Institutional Content Binding

```yaml
id: "static_content_01"
type: "textarea"
data_source: "pages"
binding: "pages.content"
editable_by: "admin_dinas"
```

Status: **Admin Editable**.
Komponen `Static Content` pada beranda website publik berfungsi sebagai wadah **Profil Ringkas & Informasi Beranda**. Komponen ini mendukung **Tab Navigasi Interaktif di Beranda** untuk seluruh halaman dengan target penempatan Beranda (`placement: beranda`), dilengkapi tombol Call-to-Action (*"Baca Halaman Selengkapnya"*) menuju rute artikel lengkap `/site/{identifier}/page/{slug}`.

Pada level navigasi global (**Header / Navbar**), sistem menyediakan navigasi dinamis secara otomatis: halaman dengan target `profil` langsung masuk ke dropdown Profil, halaman dengan target `kontak` masuk ke dropdown Kontak (mendukung tautan eksternal media sosial Instagram, Google Maps lokasi kantor, direct link Google Drive, maupun tautan email), halaman dengan target `header_menu` membuat menu utama baru di header, dan sub-menu bersarang (hingga 3 level) didukung penuh untuk seluruh menu di header. Jika slot diset `super_admin` (**Template Controlled**), teks profil ringkas dikunci dengan nilai default template.

Informasi kontak fisik resmi (alamat, email resmi, telepon) dikelola oleh Admin Kedinasan melalui modul **Pages** (preset target penempatan Kontak) dan dirender terpusat pada Footer website publik (Kolom 2) untuk menghindari duplikasi komponen pada beranda. Komponen lepas `Contact / Dinas Info` telah dihapus dari sistem demi efisiensi dan kerapian tata letak.

### Media / Document List — Asset Binding

```yaml
id: "media_source_01"
type: "collection"
data_source: "media"
binding: "media.published"
editable_by: "admin_dinas"
```

Status: **Admin Managed Content (Upload / CRUD Form)**.
Komponen `Media / Document List` terhubung ke koleksi berkas media dinas (`media.published`). Admin Kedinasan mengelola dokumen publik (format PDF) dan aset gambar resmi melalui modul Media (`/dinas/media`). Blueprint template merender komponen ini dalam format tata letak **2 baris × 3 kolom (total 6 berkas terkini)** dengan pratinjau asli (jendela gambar asli untuk berkas gambar dan embedded preview interaktif untuk dokumen PDF), bukan sekadar ikon atau teks ekstensi. Berkas yang diunggah langsung terintegrasi dan ditampilkan pada komponen ini dengan tombol unduh publik, sementara kapasitas penyimpanan dan daftar berkas diisolasi ketat per dinas. Super Admin mengendalikan batas jumlah berkas yang ditampilkan (`limit`, default: 6) pada kanvas builder.

### Footer — Identity & Slogan Binding

```yaml
id: "footer_01"
component: "Footer"
layout_settings:
  columns: 3
slots:
  slogan:
    slot_id: "footer_slogan"
    binding: "appearance.footer_slogan"
    type: "text"
    data_source: "appearance"
    editable_by: "admin_dinas"
    default_value: "Portal informasi dan layanan kedinasan resmi Pemerintah Kota Batu."
    required: false
  about_title:
    slot_id: "footer_about_title"
    binding: "template_config.about_title"
    type: "text"
    data_source: "template_config"
    editable_by: "super_admin"
    default_value: "Pemerintah Kota Batu"
    required: false
  about_text:
    slot_id: "footer_about_text"
    binding: "template_config.about_text"
    type: "textarea"
    data_source: "template_config"
    editable_by: "super_admin"
    default_value: "Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu."
    required: false
  copyright:
    slot_id: "footer_copyright"
    binding: "template_config.copyright"
    type: "text"
    data_source: "template_config"
    editable_by: "super_admin"
    default_value: "Pemerintah Kota Batu"
    required: false
```

Status: **Multi-Tenant Dynamic Footer with Configurable Slots**.
Komponen `Footer` dikendalikan secara visual oleh Super Admin di kanvas builder (dapat ditambahkan, dipindahkan urutannya, diatur jumlah kolomnya, atau dihapus). Pada website publik, footer dirender secara dinamis oleh renderer kanvas dan tidak di-hardcode di luar kanvas:
- **Kolom 1 (Identitas & Slogan):** Otomatis menampilkan nama website dinas aktif (`website->name`), kode instansi dinas (`dinas->code`), dan teks slogan penutup (`slogan`) yang dapat diedit oleh Admin Kedinasan melalui menu Appearance (`/dinas/appearance`).
- **Kolom 2 (Kontak Resmi):** Memetakan alamat kantor, email resmi, dan nomor telepon milik dinas tenant terkait tanpa resiko data instansi lain tertukar.
- **Kolom 3 (Informasi Tambahan):** Memuat judul (`about_title`) dan deskripsi (`about_text`) yang dapat dikustomisasi Super Admin di builder agar netral untuk semua dinas (bukan Diskominfo-spesifik).
- **Baris Bawah (Copyright):** Menggunakan slot `copyright` dengan fallback otomatis ke nama dinas terkait (`dinas->name`), mencegah ketidaksesuaian nama instansi penanggung jawab hak cipta.
Jika komponen Footer dihapus dari kanvas builder, website publik tidak menampilkan footer.

---

# 7. Penyimpanan Kanvas & Aturan Binding Logis

Pohon komponen (**Component Tree**) yang disusun Super Admin disimpan dalam struktur JSON.

## 7.1 Hierarki Component Tree

Baseline hierarchy:

```text
Section
└── Container
    ├── Component
    └── Component
```

Contoh konseptual:

```json
{
  "template_version": "1.0",
  "canvas": [
    {
      "id": "section_hero_01",
      "type": "section",
      "children": [
        {
          "id": "container_hero_01",
          "type": "container",
          "children": [
            {
              "id": "hero_01",
              "component": "Hero",
              "layout_settings": {
                "height": "480px",
                "alignment": "center"
              },
              "slots": {
                "title": {
                  "slot_id": "hero_title",
                  "binding": "appearance.header_slogan"
                },
                "background_image": {
                  "slot_id": "hero_bg",
                  "binding": "appearance.hero_banner"
                }
              }
            }
          ]
        }
      ]
    },
    {
      "id": "section_posts_02",
      "type": "section",
      "children": [
        {
          "id": "container_posts_02",
          "type": "container",
          "children": [
            {
              "id": "posts_grid_01",
              "component": "Posts Grid",
              "layout_settings": {
                "columns": 3,
                "limit": 6
              },
              "slots": {
                "items": {
                  "slot_id": "posts_source",
                  "binding": "posts.published"
                }
              }
            }
          ]
        }
      ]
    }
  ]
}
```

Contoh JSON tersebut adalah **representasi konseptual baseline**, bukan pengganti schema database final.

## 7.2 Peringatan Binding untuk AI Agent

Nilai binding seperti:

```text
appearance.header_slogan
appearance.hero_banner
posts.published
```

merupakan **referensi logis** terhadap data/slot dan **tidak secara otomatis menentukan nama tabel atau kolom fisik database**.

Penamaan tabel, kolom, foreign key, dan relasi riil wajib mengikuti `SCHEMA.md`.

**AI Coding Agent dilarang membuat migrasi database hanya berdasarkan string JSON binding.**

---

# 8. Status Penyimpanan Builder

Builder harus memberikan status penyimpanan yang jelas kepada Super Admin.

Minimal terdapat kondisi:

```text
Perubahan belum disimpan
```

dan:

```text
Tersimpan
```

Contoh ketika terdapat perubahan:

```text
Template Builder — Portal Resmi Kedinasan

Perubahan belum disimpan                 [ Simpan Template ]
```

Setelah berhasil disimpan:

```text
Template Builder — Portal Resmi Kedinasan

✓ Tersimpan                              [ Simpan Template ]
```

Status harus merepresentasikan kondisi penyimpanan aktual. Jangan menampilkan status `Tersimpan` apabila perubahan Builder belum berhasil dipersist ke backend.

---

# 9. Undo / Redo

Undo / Redo disepakati sebagai fitur Builder yang diperlukan untuk membantu Super Admin membatalkan atau mengulangi perubahan pada kanvas.

Secara konseptual, histori dapat mencakup:

- Menambah component.
- Menghapus component.
- Memindahkan/reorder component.
- Mengubah property component.
- Mengubah konfigurasi layout.
- Mengubah konfigurasi slot.

**Mekanisme teknis penyimpanan histori state masih dapat ditentukan pada tahap implementasi.**

AI Coding Agent tidak boleh membuat versioning database atau histori permanen hanya berdasarkan kebutuhan Undo/Redo tanpa acuan arsitektur/schema yang sesuai.

---

# 10. Aturan Responsive Preview & Live Preview

Antarmuka Builder menyediakan pratinjau tampilan responsif untuk Super Admin:

- Desktop view.
- Tablet view.
- Mobile view.

Super Admin dapat beralih tampilan pratinjau untuk melihat kerapian komponen.

Selain simulasi ukuran layar di dalam kanvas builder, Super Admin juga disediakan tombol pintasan **'Lihat Live Website ↗'** pada topbar builder dan kartu Template Aktif di dashboard utama untuk melihat render hasil template secara langsung pada website publik yang terhubung.

Nilai numerik breakpoint dan perilaku wrapping kolom otomatis mengikuti standar utilitas CSS (seperti Tailwind breakpoint: `sm`, `md`, `lg`) yang didefinisikan pada dokumen teknis lanjutan (**TBD**).

Responsive Preview tidak otomatis memberikan Admin Kedinasan hak untuk mengubah konfigurasi responsive template.

---

# 11. Lifecycle & Propagasi Perubahan Template

## 11.1 Status Siklus Template

Secara konseptual:

```text
Draft (Tersimpan di Builder)
        ↓
Preview (Uji Visual Super Admin)
        ↓
Publish (Tersedia untuk Digunakan)
        ↓
Active Template (Digunakan Website Dinas)
```

### Draft
Konfigurasi kanvas yang sedang disusun dan belum memengaruhi website publik.

### Preview
Tampilan untuk menguji hasil visual template sebelum digunakan.

### Publish
Template telah dinyatakan tersedia untuk digunakan.

### Active Template
Blueprint resmi yang dirujuk oleh website dinas.

## 11.2 Hal yang Berstatus TBD

AI Coding Agent dilarang membuat asumsi kode mandiri pada poin-poin berikut:

### Mekanisme Propagasi Template

Belum diputuskan apakah perubahan template aktif otomatis langsung ter-update ke seluruh website dinas atau setiap website dinas memiliki siklus rilis pembaruan tersendiri.

**Status: TBD.**

### Versioning Template

Belum diputuskan apakah sistem menyimpan riwayat template seperti `template_v1`, `template_v2`, dan seterusnya di database.

**Status: TBD.**

### Fallback Slot Kosong

Belum diputuskan perilaku render jika Admin Kedinasan belum mengisi salah satu slot yang diperlukan.

**Status: TBD.**

### Responsive Breakpoint Detail

Breakpoints dan perilaku wrapping final mengikuti dokumen teknis lanjutan.

**Status: TBD.**

> Undo/Redo bukan lagi fitur yang belum diputuskan. Fitur tersebut telah disepakati sebagai kebutuhan Builder; yang masih dapat ditentukan adalah mekanisme teknis implementasinya.

---

# 12. Aturan Ketat untuk AI Coding Agent

## 12.1 Jangan Membuka Akses Builder

Pastikan rute, controller, dan middleware Builder hanya dapat dieksekusi pengguna dengan peran:

```text
super_admin
```

Admin Kedinasan yang mencoba mengakses URL Builder wajib mendapatkan:

```text
403 Forbidden
```

Jangan hanya menyembunyikan tombol Builder dari UI. Authorization wajib ditegakkan pada backend.

## 12.2 Jangan Membuat Plugin Architecture Bebas

Batasi komponen Builder hanya pada daftar **Available Components** di Bab 5.

Jangan membuat sistem yang memungkinkan arbitrary PHP, JavaScript, HTML execution, atau custom code injector.

## 12.3 Pisahkan Template dan Data Tenant

Saat memprogram engine render publik:

```text
Template
→ menentukan struktur/layout/component
```

sedangkan:

```text
Tenant/Dinas
→ menyediakan data untuk Editable Slots
```

Data slot wajib ditarik berdasarkan relasi database Dinas/tenant yang bersangkutan.

Jangan menyimpan data tenant secara sembarangan di konfigurasi template global.

## 12.4 Hormati Label Admin Editable dan Template Controlled

AI Agent wajib mempertahankan pemisahan:

```text
Admin Editable
→ data yang boleh diisi/diubah Admin Kedinasan

Template Controlled
→ struktur/property yang dikendalikan Super Admin
```

AI Agent tidak boleh memberikan Admin Kedinasan akses untuk mengubah Section Order, Grid Count, Component Structure, Hero Height, Hero Alignment, Navbar Structure, Footer Structure, atau Custom CSS/JavaScript, kecuali aturan tersebut secara eksplisit diubah melalui spesifikasi proyek.

## 12.5 Jangan Menganggap Binding sebagai Schema Database

Contoh:

```text
appearance.header_slogan
```

tidak berarti:

```text
table = appearance
column = header_slogan
```

AI Agent wajib membaca `SCHEMA.md` sebelum membuat atau mengubah migration, model, relasi, repository, query, atau struktur database yang berkaitan dengan binding.

## 12.6 Jangan Mengubah Informasi TBD Secara Diam-Diam

Jika implementasi membutuhkan keputusan terkait propagasi template, versioning, fallback slot, responsive breakpoint detail, atau keputusan arsitektural lain yang belum ditetapkan, AI Agent wajib **berhenti dan meminta konfirmasi**.

Jangan memilih implementasi berdasarkan asumsi pribadi hanya agar kode dapat selesai.

## 12.7 Jangan Menganggap Contoh JSON sebagai Schema Final

Contoh JSON Component Tree pada dokumen ini adalah baseline konseptual.

AI Agent wajib mengikuti schema final yang ditetapkan pada `SCHEMA.md` dan dokumen arsitektur terkait.

AI Agent dilarang membuat struktur database baru hanya karena melihat field pada contoh JSON.

---

# 13. Prinsip Utama Builder

Template Builder Pyojek bukan arbitrary website/code builder.

Builder adalah alat untuk menyusun **blueprint tampilan global** menggunakan komponen sistem yang telah disediakan.

Model konseptual:

```text
Super Admin
    ↓
Template Builder
    ↓
Section
    ↓
Container
    ↓
Approved Components
    ↓
Editable Slots
    ↓
Template JSON
    ↓
Template Engine
    ↓
Website Dinas
    ↑
Tenant Data
    ↑
Admin Kedinasan
```

Boundary utama:

```text
Super Admin
= menentukan HOW website ditampilkan

Admin Kedinasan
= mengisi WHAT yang ditampilkan
```

Tidak boleh terjadi pertukaran tanggung jawab tersebut tanpa perubahan spesifikasi resmi.
