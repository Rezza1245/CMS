# Pyojek CMS --- Architecture

> Dokumen ini mendefinisikan arsitektur konseptual dan batasan
> arsitektur Pyojek CMS untuk membantu AI Agent/developer memahami
> bagaimana sistem harus dibangun tanpa mencampurkan requirement dengan
> asumsi implementasi.
>
> **Source of truth:** keputusan terbaru Project Owner/Supervisor
> memiliki prioritas tertinggi. Detail yang belum diputuskan harus
> diperlakukan sebagai `TBD` atau `UNKNOWN`, bukan diasumsikan.

------------------------------------------------------------------------

## 1. Tujuan Dokumen

`ARCHITECTURE.md` menjelaskan:

-   batas arsitektur Pyojek CMS;
-   hubungan antara public website, CMS dashboard, template engine,
    tenant data, dan database;
-   pembagian tanggung jawab antar komponen;
-   alur data utama;
-   batas keamanan;
-   hubungan arsitektur dengan RBAC, Global Template, Content, dan
    Tenant Isolation;
-   aturan yang harus dipatuhi AI Agent ketika melakukan implementasi.

Dokumen ini **bukan** sumber utama untuk:

-   permission detail → gunakan `ROLES_RBAC.md`;
-   perilaku Global Template/Visual Builder → gunakan
    `TEMPLATE.md`;
-   perilaku Posts, Media, Pages, Appearance → gunakan
    `CONTENT_SPEC.md`;
-   database/entity/relationship → gunakan `SCHEMA.md`;
-   teknologi final → gunakan `TECH_STACK.md`;
-   status implementasi → gunakan `PROGRESS.md`.

Jika dokumen lain yang lebih spesifik menetapkan detail berbeda, ikuti
dokumen tersebut sesuai hierarki source of truth proyek.

------------------------------------------------------------------------

# 2. Architecture Principles

Pyojek CMS dibangun berdasarkan tiga pemisahan utama:

``` text
WHO
│
└── Roles / RBAC

WHAT DATA
│
└── Content / Tenant Data

HOW WEBSITE LOOKS
│
└── Global Template
```

Implikasinya:

``` text
Super Admin
    ↓
mengatur HOW

Admin Kedinasan
    ↓
mengatur WHAT

RBAC
    ↓
mengatur WHO MAY DO WHAT
```

Prinsip ini merupakan boundary arsitektur utama dan tidak boleh
dilanggar demi kemudahan implementasi.

------------------------------------------------------------------------

# 3. Product Architecture Overview

Pyojek CMS adalah CMS terpusat untuk menyediakan website bagi banyak
Dinas/instansi.

Model utamanya:

``` text
Single Global Template
        +
Multi-Tenant Data
        ↓
Website Dinas
```

Secara konseptual:

``` text
                         DISKOMINFO
                             │
                             ▼
                         SUPER ADMIN
                             │
                             │ manages
                             ▼
                    GLOBAL TEMPLATE
                    / TEMPLATE BUILDER
                             │
              ┌──────────────┼──────────────┐
              │              │              │
              ▼              ▼              ▼
          WEBSITE A      WEBSITE B      WEBSITE C
              │              │              │
              ▼              ▼              ▼
           ADMIN A         ADMIN B         ADMIN C
              │              │              │
              ▼              ▼              ▼
         CONTENT A      CONTENT B      CONTENT C
```

Template global menentukan struktur dan tampilan.

Tenant data menentukan data yang ditampilkan.

------------------------------------------------------------------------

# 4. Multi-Tenant Architecture

## 4.1 Tenant Model

Aturan dasar:

``` text
1 Dinas = 1 Website
```

Setiap website memiliki data tenant sendiri:

``` text
Dinas A
└── Website A
    ├── Posts A
    ├── Pages A
    ├── Media A
    └── Appearance A

Dinas B
└── Website B
    ├── Posts B
    ├── Pages B
    ├── Media B
    └── Appearance B
```

Data tenant tidak boleh tercampur.

Admin Dinas A hanya boleh mengakses resource yang berada dalam
kewenangan Website/Dinas A.

------------------------------------------------------------------------

## 4.2 Tenant Boundary

Tenant boundary harus diterapkan pada backend.

Secara konseptual:

``` text
Authenticated Admin
        ↓
Identify User
        ↓
Identify Dinas / Website
        ↓
Resolve Ownership
        ↓
Query Tenant-Scoped Resource
```

Contoh:

``` text
Admin A
   ↓
website_id = A
   ↓
query Posts
   ↓
only Posts belonging to Website A
```

Backend tidak boleh menerima akses resource hanya berdasarkan identifier
tanpa memverifikasi ownership.

Contoh prinsip:

``` text
GET /posts/{id}

Backend:
    apakah user authenticated?
    apakah role mengizinkan operasi?
    apakah Post {id} milik Website user?
        YES → lanjut
        NO  → DENY
```

------------------------------------------------------------------------

# 5. High-Level System Architecture

Arsitektur konseptual:

``` text
                         PUBLIC USER
                              │
                              ▼
                    Public Website Layer
                              │
                              ▼
                     Laravel Application
                              │
              ┌───────────────┴────────────────┐
              │                                │
              ▼                                ▼
       Template Engine                    CMS Dashboard
              │                                │
              │                    ┌───────────┴───────────┐
              │                    │                       │
              ▼                    ▼                       ▼
       Global Template        Super Admin             Admin Dinas
              │                    │                       │
              │                    │                       ▼
              │                    │                Tenant Content
              │                    │                       │
              └────────────────────┴───────────────┬───────┘
                                                   ▼
                                               Database
```

Arsitektur di atas adalah konseptual. Detail deployment, API
architecture, frontend framework, database engine, web server, hosting,
dan infrastructure masih mengikuti `TECH_STACK.md` jika belum
ditetapkan.

------------------------------------------------------------------------

# 6. Application Boundaries

Pyojek CMS secara konseptual memiliki beberapa area utama.

## 6.1 Public Website Layer

Tanggung jawab:

-   menerima request public visitor;
-   menentukan website/Dinas yang diminta;
-   mengambil Global Template;
-   mengambil tenant data yang relevan;
-   meresolve Editable Slots;
-   meresolve dynamic data;
-   merender website publik;
-   hanya menampilkan content yang boleh dipublikasikan.

Public visitor bersifat:

``` text
READ ONLY
```

Public tidak boleh memperoleh akses CMS atau operasi administratif.

------------------------------------------------------------------------

## 6.2 CMS Dashboard

CMS Dashboard digunakan oleh pengguna administratif.

Terdapat dua konteks utama:

``` text
Super Admin Dashboard
Admin Kedinasan Dashboard
```

### Super Admin

Tanggung jawab arsitektural:

-   Global Template;
-   Visual Template Builder;
-   Template Structure;
-   User Management;
-   Admin Kedinasan;
-   System Configuration.

Super Admin **tidak** menjadi editor Posts Dinas.

### Admin Kedinasan

Tanggung jawab arsitektural:

-   Posts;
-   Media;
-   Pages;
-   Appearance / Editable Slots;
-   konten website Dinas sendiri.

Admin tidak memiliki Visual Template Builder.

------------------------------------------------------------------------

# 7. Super Admin Architecture

Super Admin berada pada level platform/global.

``` text
Super Admin
    │
    ├── User Management
    │
    ├── Website Dinas Management
    │
    ├── Global Template
    │       │
    │       ├── Visual Template Builder (Section → Container → Component)
    │       └── Template Preview Mode (Clean Slate)
    │
    ├── System Configuration
    │
    └── Platform Activity Logs
```

Super Admin dapat mengontrol bagaimana website dibangun:

-   section;
-   urutan section;
-   container;
-   kolom;
-   posisi;
-   ukuran;
-   tinggi;
-   lebar;
-   spacing;
-   padding;
-   margin;
-   struktur card;
-   jumlah kolom;
-   struktur navbar;
-   struktur hero;
-   struktur footer;
-   component/widget;
-   editable slots;
-   data binding.

Namun kontrol tersebut berada pada level Global Template, bukan pada
data tenant.

------------------------------------------------------------------------

# 8. Admin Kedinasan Architecture

Admin Kedinasan bekerja pada scope satu Dinas/Website.

``` text
Admin Dinas
    │
    └── Website miliknya
          │
          ├── Posts
          ├── Media (with MediaOptimizerService)
          ├── Pages (4-Level Hierarchy)
          ├── Appearance (Unlocked Slots)
          └── Website Settings
```

Admin mengelola data, bukan struktur global.

Admin dapat:

-   membuat dan mengelola Posts;
-   upload dan mengelola Media;
-   mengedit content Pages;
-   mengisi Editable Slots yang disediakan template.

Admin tidak dapat:

-   membuat Global Template;
-   mengubah struktur template;
-   membuka Template Builder;
-   mengubah global layout;
-   mengelola Admin lain;
-   mengakses data Dinas lain;
-   menyisipkan Custom CSS;
-   menyisipkan Custom JavaScript;
-   melakukan arbitrary code execution.

------------------------------------------------------------------------

# 9. Global Template Architecture

Global Template merupakan layer yang menentukan:

``` text
HOW THE WEBSITE LOOKS
```

Template dibuat dan dikontrol oleh Super Admin.

Template bukan data tenant.

Secara konseptual:

``` text
Global Template
      │
      ├── Structure
      ├── Layout
      ├── Components
      ├── Editable Slots
      └── Data Binding
```

Kemudian:

``` text
Global Template
       +
Tenant Data
       +
Tenant Assets
       ↓
Rendered Website
```

------------------------------------------------------------------------

# 10. Visual Template Builder Architecture

Visual Builder merupakan alat Super Admin untuk membangun Global
Template.

Struktur UI:

``` text
┌─────────────────┬─────────────────────────────┬─────────────────┐
│ Left Sidebar    │       Center Canvas         │ Inspector       │
│                 │                             │                 │
│ Widgets         │   Visual Website Preview   │ Settings        │
│ Layout          │                             │ Slot Binding    │
│ Components      │   Drag / Drop / Reorder     │ Style           │
│                 │                             │                 │
└─────────────────┴─────────────────────────────┴─────────────────┘
```

## 10.1 Left Sidebar

Berisi layout dan component/widget yang tersedia.

Contoh baseline:

``` text
Layout
├── Container 1 kolom
├── Container 2 kolom
├── 50:50
├── 70:30
├── 3 kolom
└── 4 kolom
```

Dynamic Components:

``` text
Hero Banner
Posts Grid
Static Content/Page
Media/Document List
Container (Grid Wrapper 1-4 Kolom)
Footer
```

Daftar tersebut merupakan komponen resmi sistem (pre-defined system components). Komponen redundan seperti carousel dan widget kontak terpisah telah diintegrasikan ke Posts Grid, Pages, dan Footer.

------------------------------------------------------------------------

## 10.2 Center Canvas

Canvas digunakan Super Admin untuk:

-   drag;
-   drop;
-   reorder;
-   menyusun section;
-   menyusun component;
-   melihat preview visual.

Preview baseline:

``` text
Desktop
Tablet
Mobile
```

Detail responsive breakpoint masih dapat berstatus `TBD`.

------------------------------------------------------------------------

## 10.3 Inspector

Inspector mengatur property element yang dipilih.

Contoh property:

``` text
Padding
Margin
Background
Overlay
Border Radius
Alignment
Height
Width
Position
```

Inspector juga menjadi tempat konsep:

``` text
Slot Binding
```

------------------------------------------------------------------------

# 11. Component Tree Architecture

Struktur template direncanakan direpresentasikan sebagai JSON Component
Tree.

Contoh konseptual:

``` json
{
  "template_version": "1.0",
  "canvas": [
    {
      "id": "sec_hero_01",
      "type": "hero_section",
      "settings": {
        "height": "480px",
        "align": "center"
      },
      "slots": {
        "title": "appearance.header_slogan",
        "background": "appearance.hero_banner"
      }
    },
    {
      "id": "sec_posts_02",
      "type": "posts_grid",
      "settings": {
        "columns": 3,
        "limit": 6,
        "show_date": true
      },
      "slots": {
        "source": "posts"
      }
    }
  ]
}
```

**Penting:**

JSON tersebut adalah representasi struktur builder secara konseptual.

JSON component tree **bukan** database schema.

Jangan membuat migration secara langsung berdasarkan contoh JSON.

Alur yang benar:

``` text
Requirement
    ↓
Template Specification
    ↓
Schema Design
    ↓
Migration
```

Format final/schema JSON masih dapat berkembang.

------------------------------------------------------------------------

# 12. Editable Slot Architecture

Editable Slot adalah boundary antara Global Template dan Tenant Data.

``` text
Template Structure
       +
Editable Slot
       +
Tenant Data
       ↓
Website Dinas
```

Contoh:

``` text
Hero
├── Title
├── Description
└── Background Image
```

Super Admin menentukan bahwa property tertentu adalah editable slot.

Admin kemudian mengisi nilai aktualnya.

Contoh:

``` text
Hero Title
type = text
editable_by = Admin

Hero Description
type = text
editable_by = Admin

Hero Background
type = image
editable_by = Admin

Hero Height
type = dimension
editable_by = Super Admin
```

------------------------------------------------------------------------

# 13. Content Slot vs Layout Property

Arsitektur harus membedakan:

``` text
Content Slot
```

dari:

``` text
Layout Property
```

Contoh:

``` text
Admin boleh:
    Hero Title
    Hero Description
    Hero Image

Admin tidak boleh:
    Hero Height
    Hero Position
    Hero Structure
```

Kecuali suatu property secara eksplisit ditentukan sebagai editable.

Prinsip:

> Admin dapat mengganti data yang disediakan template, tetapi tidak
> memperoleh kontrol bebas terhadap struktur template.

------------------------------------------------------------------------

# 14. Data Binding Architecture

Template dapat menentukan sumber data component.

Contoh:

``` text
Posts Grid
    ↓
Data Source
    ↓
Posts
    ↓
status = published
    ↓
limit = 6
```

Contoh lainnya:

``` text
Hero
├── Title → appearance hero title
└── Image → appearance hero banner
```

Binding adalah konsep logis antara component dan sumber data.

Nama binding seperti:

``` text
appearance.header_slogan
```

tidak secara otomatis berarti database harus memiliki kolom dengan nama
tersebut.

Database tetap harus mengikuti `SCHEMA.md`.

------------------------------------------------------------------------

# 15. Rendering Architecture

Rendering public website dan template builder dikelola oleh `App\Services\CanvasRenderer`:

``` text
Public Request (GET /site/{identifier})
   ↓
Identifikasi Website/Dinas & Validasi Status (Aktif/Pemeliharaan)
   ↓
Ambil Global Template & Canvas AST (Section → Container → Component)
   ↓
CanvasRenderer::render(Website $website)
   ↓
Resolve Tenant Data Binding (appearance, posts.published, pages, dinas)
   ↓
Render Pre-defined Blade Components (Hero, Posts Grid, Static Content, Media List, Footer, Container)
   ↓
Website Publik

---

Super Admin Preview (GET /admin/templates/{template}/preview)
   ↓
CanvasRenderer::renderTemplate(Template $template)
   ↓
Render Struktur Blueprint dengan Fallback Default (Empty State, Tanpa Data/Postingan Dinas)
   ↓
Halaman Pratinjau Template Murni
```

Secara keseluruhan:

``` text
Global Template (Pohon Kanvas: Section → Container → Component)
      +
Tenant Data (Disaring status = published)
      +
Tenant Assets (MediaOptimizerService)
      ↓
Rendered Website
```

Rendering harus menggunakan komponen yang telah didefinisikan sistem.

Rendering tidak boleh menjalankan arbitrary PHP atau JavaScript yang
berasal dari data template/content.

------------------------------------------------------------------------

# 16. Dynamic Content Rendering

Component yang mengambil data dinamis harus melakukan filtering
berdasarkan aturan sistem.

Contoh Posts Widget:

``` text
Posts
   ↓
Tenant Scope
   ↓
status = published
   ↓
limit = configured value
   ↓
Render Component
```

Public website tidak boleh menampilkan:

``` text
draft
```

Public hanya dapat melihat data yang sudah dipublikasikan.

------------------------------------------------------------------------

# 17. Posts Architecture

Posts merupakan tenant content.

Jenis Post baseline:

``` text
Berita
Pengumuman
Kegiatan
```

Operasi Admin:

``` text
Create
Read
Update
Delete
Publish
Draft
```

Field konseptual:

``` text
title
slug
body
thumbnail
direct_link
type
status
published_at
website_id
author
```

Status minimal:

``` text
draft
published
```

Posts tidak menjadi modul operasional Super Admin.

------------------------------------------------------------------------

# 18. Media Architecture

Media merupakan tenant asset yang dikelola dan diisolasi per website dinas.

Format yang didukung:

``` text
Gambar: JPG, JPEG, PNG, WEBP
Dokumen: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX
```

Setiap berkas unggahan diproses melalui `App\Services\MediaOptimizerService`:
1. Gambar otomatis dikonversi ke format `.webp` berkualitas 85% via ekstensi PHP GD.
2. Dokumen Office (DOCX, XLSX, PPTX) dikompresi ulang dengan tingkat kompresi ZipArchive Deflate level 9.
3. Dokumen PDF dioptimasi struktur stream internalnya.

Secara konseptual:

``` text
Website A
├── image A1
├── image A2
└── document A1

Website B
├── image B1
└── document B1
```

Media harus memiliki tenant ownership yang jelas.

Admin Dinas A tidak boleh menggunakan atau mengelola Media Dinas B
secara sembarangan.

Seluruh file yang diunggah melalui formulir Posts (thumbnail), Pages (gambar ilustrasi), dan Appearance (logo & banner) secara otomatis dicatat ke tabel `media` milik tenant bersangkutan.

Detail storage infrastructure masih mengikuti `TECH_STACK.md`.

------------------------------------------------------------------------

# 19. Pages Architecture

Pages digunakan untuk content statis/institusional serta struktur navigasi menu header bertingkat hingga **4 level kedalaman**:

Baseline:

``` text
Level 1: Menu Utama Header (Root)
├── Level 2: Sub-menu
│   └── Level 3: Wadah Sub-sub-bab
│       └── Level 4: Isi Wadah
└── Level 2: Sub-menu Tunggal
```

Pages mendukung opsi penempatan `placement`:
- `beranda`: Tampil sebagai Tab Interaktif di beranda (Static Content), tidak membuat menu baru di navbar header. Pada tabel Pages internal dinas, halaman beranda selalu diurutkan paling awal.
- `profil`: Menjadi sub-menu di bawah dropdown Profil pada header.
- `header_menu`: Menjadi menu utama baru di header navbar.
- `sub_menu`: Menjadi sub-menu / sub-sub-menu via `parent_id` (maksimal 4 level kedalaman).

Pages juga mendukung atribut `direct_link` untuk menautkan halaman/menu secara langsung ke link unduhan Google Drive, URL portal eksternal, `mailto:`, atau `tel:`.
Jika halaman Level 4 (isi wadah) diakses melalui URL publik secara langsung, sistem secara arsitektural mengarahkannya otomatis (*redirect*) ke halaman wadah induknya (Level 3).

Admin dapat mengelola isi content dan opsi penempatan menu.

Admin tidak memperoleh kebebasan untuk mengubah layout/template halaman.

------------------------------------------------------------------------

# 20. Appearance Architecture

Appearance Admin bukan Visual Page Builder.

Appearance merupakan layer untuk mengelola nilai Editable Slots yang
disediakan template.

Contoh:

``` text
Logo
Favicon
Hero Title
Hero Description
Hero Banner
Official Contact
Footer Content
```

Admin dapat mengganti data/asset yang memang disediakan sebagai slot.

Admin tidak dapat mengubah:

``` text
Template Structure
Section Position
Layout
Section Height
Card Structure
```

kecuali property tersebut secara eksplisit dirancang editable.

------------------------------------------------------------------------

# 21. Navbar Architecture

Struktur Navbar ditentukan oleh Super Admin melalui Global Template.

Navbar mendukung navigasi bertingkat: seluruh menu di header dapat memiliki sub-menu dan sub-sub-menu (maksimal 3 level kedalaman) yang dirender secara dinamis dari Pages dan kategori Posts milik dinas.

Admin tidak bebas mengubah:

``` text
Navbar Structure
Navbar Layout
Navbar Position
```

Jika template menyediakan bagian Navbar sebagai Editable Slot, Admin
hanya mengisi data slot tersebut. Konten menu dikontrol melalui hierarki Pages tenant.

------------------------------------------------------------------------

# 22. Hero Architecture

Super Admin mengontrol:

``` text
Height
Width
Position
Structure
Alignment
Layout
Overlay / Style
```

Admin dapat mengontrol nilai yang memang diberikan sebagai slot:

``` text
Title
Description
Background Image
```

Prinsip:

``` text
Admin → Hero Content
Super Admin → Hero Structure
```

------------------------------------------------------------------------

# 23. Footer Architecture

Footer structure dikontrol Global Template.

Contoh:

``` text
Footer Structure
├── Logo
├── Address
├── Phone
├── Contact
└── Copyright
```

Admin dapat mengisi nilai jika slot tersebut tersedia:

``` text
Address
Phone
Contact
Copyright
```

Admin tidak mengubah:

``` text
Footer Layout
Footer Structure
Footer Position
```

------------------------------------------------------------------------

# 24. Authentication Architecture

Sistem menggunakan:

``` text
Session-based authentication
```

Authentication dan authorization merupakan dua concern berbeda.

``` text
Authentication
= siapa pengguna?

Authorization
= apa yang boleh dilakukan pengguna?
```

Authentication digunakan untuk mengidentifikasi user.

Authorization digunakan untuk menentukan apakah user memiliki hak
terhadap operasi/resource tertentu.

Authorization harus dilakukan pada backend.

------------------------------------------------------------------------

# 25. Authorization Architecture

Frontend/UI bukan security boundary.

Hal berikut tidak cukup untuk mengamankan resource:

``` text
Hidden Button
Frontend Route Restriction
UI Restriction
```

Backend harus menjadi enforcement point.

Secara konseptual setiap request administratif harus memvalidasi:

``` text
Authentication
      ↓
Role
      ↓
Ownership
      ↓
Website / Tenant
      ↓
Permission
      ↓
Operation
```

Contoh:

``` text
Admin A
    ↓
GET /posts/10
    ↓
Backend
    ↓
Apakah Post 10 milik Website A?
    ├── YES → lanjut
    └── NO  → DENY
```

------------------------------------------------------------------------

# 26. Security Architecture

Security boundary utama:

``` text
Frontend
    ≠
Security Boundary

Backend
    =
Enforcement Point
```

Sistem tidak boleh menyediakan mekanisme untuk:

``` html
<script>
```

atau:

``` php
<?php ...
```

atau bentuk arbitrary executable code lainnya melalui
CMS/template/content.

Tidak boleh ada:

-   arbitrary PHP execution;
-   arbitrary JavaScript execution;
-   Custom CSS dari Admin;
-   Custom JavaScript dari Admin;
-   arbitrary DOM manipulation;
-   arbitrary code injection.

Template harus dirender melalui komponen yang sudah didefinisikan
sistem.

------------------------------------------------------------------------

# 27. Tenant Isolation Security

Tenant isolation adalah requirement keamanan utama.

Setiap resource tenant harus dapat ditelusuri ke Website/Dinas yang
berwenang.

Secara konseptual:

``` text
Request
  ↓
Authenticated User
  ↓
Associated Dinas
  ↓
Associated Website
  ↓
Resource Ownership Check
  ↓
Allow / Deny
```

Contoh serangan yang harus dicegah:

``` text
Admin A
    ↓
mengakses Post B
    ↓
DENY
```

dan:

``` text
Admin A
    ↓
menghapus Media B
    ↓
DENY
```

Isolation tidak boleh bergantung pada frontend.

------------------------------------------------------------------------

# 28. Public Visibility Boundary

Public visitor hanya boleh memperoleh data yang telah dipublikasikan.

``` text
Public Request
    ↓
Resolve Tenant
    ↓
Resolve Content
    ↓
Filter Published
    ↓
Render
```

Aturan:

``` text
published → PUBLIC READ
draft     → NOT PUBLIC
```

Draft tidak boleh muncul pada website publik.

------------------------------------------------------------------------

# 29. Request Processing Model

Model konseptual request untuk CMS:

``` text
HTTP Request
     ↓
Session Authentication
     ↓
Identify User
     ↓
Identify Role
     ↓
Identify Tenant Scope
     ↓
Authorization
     ↓
Validate Input
     ↓
Execute Domain Operation
     ↓
Persist / Retrieve Data
     ↓
Response
```

Untuk public website:

``` text
HTTP Request
     ↓
Identify Website / Dinas
     ↓
Load Active Global Template
     ↓
Load Tenant Data
     ↓
Resolve Slots / Dynamic Data
     ↓
Render Approved Components
     ↓
Public Response
```

Detail middleware, service, repository, controller, atau class structure
belum ditetapkan oleh master context dan harus mengikuti dokumen teknis
yang lebih spesifik jika tersedia.

------------------------------------------------------------------------

# 30. Data Ownership Model

Model konseptual entity:

``` text
User
Dinas
Website
Template
Appearance
Page
Post
Media
```

Relasi konseptual:

``` text
User
  N : 1
Dinas
  1 : 1
Website
  N : 1
Template
```

Kemudian:

``` text
Website
├── Appearance
├── Pages
├── Posts
└── Media
```

User Admin memiliki hubungan dengan Dinas.

Post memiliki author/user.

Media memiliki uploader/user.

**Catatan:** relasi dan field database final harus mengikuti
`SCHEMA.md`, bukan diturunkan dari dokumen arsitektur ini.

------------------------------------------------------------------------

# 31. Separation of Concerns

Arsitektur harus mempertahankan pemisahan berikut.

## 31.1 Template Layer

Mengatur:

``` text
Structure
Layout
Component
Style
Editable Slot Definition
Binding
```

## 31.2 Content Layer

Mengatur:

``` text
Posts
Media
Pages
Appearance Values
Tenant Content
```

## 31.3 Identity & Authorization Layer

Mengatur:

``` text
Authentication
Role
Permission
Ownership
Tenant Scope
```

## 31.4 Rendering Layer

Mengatur:

``` text
Template Resolution
Data Resolution
Slot Resolution
Component Rendering
Public Output
```

Tidak boleh mencampurkan tanggung jawab tersebut hanya untuk
menyederhanakan implementasi.

------------------------------------------------------------------------

# 32. What Super Admin Controls vs What Admin Controls

  Area                   Super Admin             Admin Kedinasan
  ---------------------- ----------------------- -----------------
  Global Template        ALLOW                   DENY
  Template Structure     ALLOW                   DENY
  Visual Builder         ALLOW                   DENY
  Layout Global          ALLOW                   DENY
  User Management        ALLOW                   DENY
  Admin Account          ALLOW                   DENY
  System Configuration   ALLOW                   DENY
  Posts                  DENY                    ALLOW / OWN
  Media                  DENY                    ALLOW / OWN
  Pages Content          DENY                    ALLOW / OWN
  Appearance Slots       Global Template Owner   ALLOW / OWN
  Public Website Read    ALLOW                   ALLOW / OWN

Permission detail harus selalu dikonfirmasi terhadap `ROLES_RBAC.md`.

------------------------------------------------------------------------

# 33. Explicitly Forbidden Architectural Shortcuts

AI Agent/developer **tidak boleh** mengambil pendekatan berikut:

### 33.1 Memberikan Builder kepada Admin

Salah:

``` text
Admin
 ↓
Visual Builder
```

Requirement:

``` text
Super Admin
 ↓
Visual Builder
```

Admin hanya mengisi Editable Slots.

------------------------------------------------------------------------

### 33.2 Menjadikan Frontend sebagai Authorization

Salah:

``` text
Hide Delete Button
    ↓
anggap aman
```

Benar:

``` text
Request
 ↓
Backend Authorization
 ↓
Ownership Check
 ↓
Allow / Deny
```

------------------------------------------------------------------------

### 33.3 Membuat Super Admin sebagai Post Editor

Salah:

``` text
Super Admin
 ↓
Posts Dinas
```

Posts adalah tanggung jawab Admin Kedinasan.

------------------------------------------------------------------------

### 33.4 Membuat Free-form Page Builder untuk Admin

Admin tidak mendapatkan:

``` text
Drag / Drop arbitrary page structure
Custom Layout
Custom CSS
Custom JS
```

Admin hanya mengelola content dan editable slots yang telah disediakan.

------------------------------------------------------------------------

### 33.5 Mengeksekusi Template sebagai Arbitrary Code

Template JSON harus diperlakukan sebagai data/struktur yang
diinterpretasikan oleh component system.

Jangan mengubah data template menjadi mekanisme arbitrary code
execution.

------------------------------------------------------------------------

### 33.6 Menurunkan Database dari JSON Builder

Salah:

``` text
JSON Component Example
        ↓
Migration
```

Benar:

``` text
Requirement
    ↓
Specification
    ↓
Schema
    ↓
Migration
```

------------------------------------------------------------------------

# 34. Architecture Boundaries

## Allowed

``` text
Global Template
        ↓
Component Definition
        ↓
Editable Slot
        ↓
Tenant Data
        ↓
Rendering
```

## Not Allowed

``` text
Tenant Admin
    ↓
Global Layout Modification
```

``` text
Tenant Admin
    ↓
Arbitrary Code
```

``` text
Frontend
    ↓
Security Enforcement
```

``` text
Super Admin
    ↓
Tenant Post Management
```

``` text
Admin A
    ↓
Tenant B Resource
```

------------------------------------------------------------------------

# 35. Template-to-Tenant Interaction

Hubungan antara template dan tenant:

``` text
                    GLOBAL
                      │
                      ▼
               Global Template
                      │
          ┌───────────┴───────────┐
          │                       │
          ▼                       ▼
   Structure/Layout         Editable Slots
                                  │
                                  ▼
                         Tenant-specific Values
                                  │
                                  ▼
                            Website Dinas
```

Template menentukan ruang yang tersedia.

Tenant menentukan nilai yang mengisi ruang tersebut.

------------------------------------------------------------------------

# 36. Public Website Rendering Example

Contoh konseptual:

``` text
Visitor requests Website A
          ↓
System identifies Website A
          ↓
Load Global Template
          ↓
Load Template Component Tree
          ↓
Resolve:
    Hero Title
    Hero Description
    Hero Banner
          ↓
Load Website A Appearance
          ↓
Resolve Posts Widget
          ↓
Filter:
    website_id = A
    status = published
          ↓
Render Blade Components
          ↓
Return Website A
```

Website B melalui proses yang sama tetapi menggunakan tenant data B.

``` text
Website A
→ Template Global
→ Data A
→ Output A

Website B
→ Template Global
→ Data B
→ Output B
```

Template dapat sama sementara data tenant berbeda.

------------------------------------------------------------------------

# 37. Error and Unknown Handling for AI Agent

Jika AI Agent menemukan informasi yang tidak ditentukan:

``` text
TBD
```

atau:

``` text
UNKNOWN
```

harus digunakan.

AI Agent tidak boleh mengarang:

-   database field;
-   migration;
-   service architecture;
-   API endpoint;
-   frontend framework;
-   infrastructure;
-   deployment architecture;
-   template lifecycle;
-   versioning strategy;
-   publish mechanism;
-   propagation mechanism;
-   responsive breakpoint;
-   component schema final.

Jika implementasi membutuhkan keputusan tersebut, AI Agent harus meminta
konfirmasi atau merujuk ke dokumen proyek yang relevan.

------------------------------------------------------------------------

# 38. Known TBD Architecture Areas

Area berikut masih belum final:

-   detail JSON schema;
-   component/widget final;
-   template versioning;
-   template lifecycle;
-   template publish mechanism;
-   propagasi perubahan Global Template;
-   responsive breakpoint;
-   detail database;
-   infrastructure;
-   deployment;
-   detail Settings lanjutan (Settings dasar platform dan tenant telah diimplementasikan).

Contoh lifecycle:

``` text
Draft
  ↓
Preview
  ↓
Publish
  ↓
Active
```

hanya merupakan kemungkinan konseptual dan **bukan implementasi final**
sampai diputuskan.

------------------------------------------------------------------------

# 39. Implementation Decision Rules

Sebelum membuat perubahan arsitektur atau implementasi yang mempengaruhi
data/permission/template:

``` text
1. Identify Requirement
2. Identify Responsible Role
3. Identify Tenant Boundary
4. Identify Affected Domain
5. Check Relevant Specification
6. Check Schema if data-related
7. Check Tech Stack if technology-related
8. Implement Only Confirmed Behavior
9. Add Negative/Isolation Tests
```

Jangan mengubah requirement agar implementasi menjadi lebih mudah.

------------------------------------------------------------------------

# 40. Testing Architecture

Setiap fitur yang berhubungan dengan permission harus memiliki minimal:

## Positive Case

User yang berhak dapat melakukan operasi.

``` text
Authorized User
    ↓
ALLOW
```

## Negative Case

User yang tidak berhak ditolak.

``` text
Unauthorized User
    ↓
DENY
```

## Tenant Isolation

``` text
Admin A
    ↓
Resource B
    ↓
DENY
```

## Public Visibility

``` text
Published
    ↓
PUBLIC READ
```

``` text
Draft
    ↓
NOT PUBLIC
```

Testing harus memvalidasi enforcement backend, bukan hanya kondisi UI.

------------------------------------------------------------------------

# 41. Architecture Checklist for AI Agent

Sebelum mengimplementasikan fitur, AI Agent harus dapat menjawab:

### Identity

-   Siapa user yang menjalankan operasi?
-   Role apa yang dimiliki user?

### Tenant

-   Dinas mana yang dimiliki user?
-   Website mana yang menjadi scope?
-   Resource mana yang sedang diakses?
-   Apakah resource tersebut benar-benar milik tenant?

### Permission

-   Apakah role tersebut memiliki permission?
-   Apakah operation tersebut diperbolehkan?

### Template

-   Apakah perubahan berkaitan dengan Global Template?
-   Apakah perubahan berkaitan dengan Editable Slot?
-   Apakah Admin sedang mencoba mengubah layout yang seharusnya
    immutable?

### Content

-   Apakah resource berupa Post, Media, Page, atau Appearance?
-   Apakah resource tersebut tenant-scoped?

### Rendering

-   Apakah data yang ditampilkan sudah melalui tenant scope?
-   Apakah public hanya menerima `published` content?

### Security

-   Apakah implementasi memungkinkan arbitrary code execution?
-   Apakah security hanya bergantung pada frontend?
-   Apakah tenant isolation ditegakkan backend?

### Specification

-   Apakah requirement sudah ditentukan?
-   Jika belum, apakah statusnya `TBD`/`UNKNOWN`?
-   Dokumen mana yang menjadi source of truth untuk perubahan tersebut?

------------------------------------------------------------------------

# 42. Architecture Anti-Patterns

Hindari pola berikut:

``` text
UI restriction = security
```

``` text
Admin = Super Admin + Content Editor
```

``` text
Template JSON = database schema
```

``` text
Editable Slot = arbitrary layout control
```

``` text
Tenant ID supplied by client = trusted tenant
```

``` text
Hidden route = authorization
```

``` text
Template data = executable code
```

``` text
New feature = automatically part of scope
```

------------------------------------------------------------------------

# 43. Final Architectural Model

Model akhir yang harus dipahami AI Agent:

``` text
                         PYOJEK CMS
                             │
            ┌────────────────┴────────────────┐
            │                                 │
            ▼                                 ▼
      GLOBAL PLATFORM                    TENANT WEBSITES
            │                                 │
            ▼                                 ▼
      SUPER ADMIN                       ADMIN KEDINASAN
            │                                 │
      ┌─────┼─────┐                    ┌─────┼─────┐
      │     │     │                    │     │     │
      ▼     ▼     ▼                    ▼     ▼     ▼
   User  Template Setting             Posts Media Pages
            │                           │
            ▼                           ▼
      Visual Builder                Appearance
            │
            ▼
    Component Tree / JSON
            │
            ▼
      Editable Slots
            │
            └──────────────┐
                           ▼
                     Tenant Data
                           │
                           ▼
                    Template Engine
                           │
                           ▼
                   Blade Components
                           │
                           ▼
                   Public Website
```

Dengan prinsip:

``` text
WHO
→ RBAC

WHAT
→ Tenant Content

HOW
→ Global Template

WHERE
→ Tenant / Website Scope

HOW TO RENDER
→ Template Engine + Approved Components

WHO MAY ACCESS WHAT
→ Backend Authorization
```

------------------------------------------------------------------------

# 44. Non-Negotiable Rules

AI Agent **WAJIB** mempertahankan aturan berikut:

1.  `1 Dinas = 1 Website`.
2.  Tenant data harus terisolasi.
3.  Authorization harus ditegakkan di backend.
4.  Frontend bukan security boundary.
5.  Super Admin mengelola Global Template, bukan Posts Dinas.
6.  Admin Kedinasan mengelola content Dinas sendiri.
7.  Admin tidak memiliki Visual Template Builder.
8.  Admin tidak dapat mengubah Global Template Structure.
9.  Editable Slot tidak sama dengan Layout Property.
10. Template JSON bukan database schema.
11. Public hanya dapat membaca content yang `published`.
12. Tidak boleh ada arbitrary PHP/JavaScript/code execution.
13. Jangan menambahkan entity atau fitur yang belum memiliki
    requirement.
14. Jangan mengubah schema berdasarkan asumsi.
15. `TBD` harus tetap diperlakukan sebagai TBD sampai ada keputusan.
16. Implementasi tidak boleh mengubah requirement hanya demi kemudahan
    coding.

------------------------------------------------------------------------

# 45. Relationship With Other Project Documents

Gunakan dokumen proyek berdasarkan concern:

``` text
PRD.md
  ↓
What the system must do

ROLES_RBAC.md
  ↓
Who may do what

TEMPLATE.md
  ↓
How Global Template / Builder behaves

CONTENT_SPEC.md
  ↓
How Posts / Media / Pages / Appearance behave

SCHEMA.md
  ↓
Database / Entity / Relationship

ARCHITECTURE.md
  ↓
How system components interact

TECH_STACK.md
  ↓
Technology decisions

PROGRESS.md
  ↓
Implementation status
```

Untuk keputusan lintas dokumen:

``` text
Latest Project Owner / Supervisor Decision
                    ↓
               PRD terbaru
                    ↓
              Specific Spec
                    ↓
             Architecture
                    ↓
              Implementation
```

------------------------------------------------------------------------

# 46. Closing Principle

Pyojek CMS harus selalu mempertahankan pemisahan:

``` text
WHO
    → RBAC

WHAT DATA
    → Tenant Content

HOW WEBSITE LOOKS
    → Global Template
```

Kemudian sistem menggabungkannya pada rendering:

``` text
Global Template
      +
Tenant Data
      +
Tenant Assets
      ↓
Template Engine
      ↓
Approved Components
      ↓
Rendered Website
```

Arsitektur tidak boleh berubah menjadi:

``` text
Tenant Data
    →
Arbitrary Layout
    →
Arbitrary Code
```

atau:

``` text
Frontend Restriction
    →
anggap sebagai Security
```

atau:

``` text
Super Admin
    →
semua content tenant
```

Pemisahan **RBAC + Tenant Data + Global Template** merupakan fondasi
utama Pyojek CMS dan harus dipertahankan dalam seluruh implementasi.
