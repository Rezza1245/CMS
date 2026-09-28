# AGENTS.md — Pyojek CMS

## 1. Tujuan

Dokumen ini berisi aturan kerja AI Agent dalam mengembangkan Pyojek CMS. Agent wajib mengikuti requirement project, menjaga batas akses dan tenant, meminimalkan asumsi, serta melakukan verifikasi sebelum menyatakan pekerjaan selesai.

## 2. Gambaran Project

Pyojek CMS adalah CMS terpusat berbasis web untuk pembuatan dan pengelolaan website Dinas di lingkungan Diskominfo Kota Batu.

Konsep utama:

**Single Global Template, Multi-Tenant Data**

- Super Admin mengendalikan template/blueprint global.
- Admin Kedinasan mengelola konten website Dinas miliknya.
- Masyarakat hanya membaca konten publik yang dipublikasikan.
- Satu Dinas memiliki satu Website.
- Struktur template global bersifat fixed.

## 3. Dokumen Acuan

Dokumen project:

```text
/
├── README.md
├── PRD.md
├── ROLES_RBAC.md
├── SCHEMA.md
├── TECH_STACK.md
├── TEMPLATE.md
├── CONTENT_SPEC.md
├── ARCHITECTURE.md
├── ROUTING.md
├── SECURITY.md
└── PROGRESS.md
```

Empat dokumen (`PRD`, `ROLES_RBAC`, `SCHEMA`, `TECH_STACK`) sudah cukup untuk memulai development. Dokumen lain digunakan ketika detail implementasinya diperlukan.

### Prioritas Source of Truth

Jika terjadi konflik:

1. Requirement/revisi terbaru dari user atau supervisor.
2. `PRD.md`
3. `ROLES_RBAC.md`
4. `SCHEMA.md`
5. `TEMPLATE.md`
6. `CONTENT_SPEC.md`
7. `TECH_STACK.md`
8. `ARCHITECTURE.md`
9. `PROGRESS.md`

Jangan menyelesaikan konflik dengan asumsi diam-diam.

## 4. Aturan Utama

### 4.1 Jangan Berasumsi

Jangan membuat keputusan produk yang belum ditentukan.

Contoh:

- Subdomain vs slug path.
- Struktur database final yang belum disepakati.
- Detail System Configuration.
- Mekanisme RBAC database.
- Format penyimpanan struktur Template.
- Library/framework tambahan.
- Fitur baru yang tidak ada dalam requirement.

Jika keputusan benar-benar diperlukan:

1. Periksa dokumen.
2. Identifikasi bagian yang belum ditentukan.
3. Berikan rekomendasi bila diperlukan.
4. Jangan mengubah requirement tanpa persetujuan.

### 4.2 Jangan Menambah Scope

Jangan menambahkan fitur hanya karena umum pada CMS.

**Alur Utama Template:**
Visual Template Builder berbasis drag-and-drop / section-block canvas adalah alur kerja utama eksklusif untuk **Super Admin** dalam menyusun Global Template (menggunakan daftar pre-defined system components).

Strict out-of-scope:

- Visual Page Builder / Drag-and-Drop Builder untuk Admin Kedinasan (Admin Kedinasan tetap mengelola konten via formulir dashboard standar).
- Ekosistem Plugin / Custom Widget bebas di luar komponen bawaan sistem.
- Custom CSS.
- Custom Script.
- Manipulasi DOM bebas.
- Multi-template selection oleh Admin.
- Comment/Discussion Engine.
- Public Admin Registration.
- Integrasi sistem eksternal.

Jika library/template membawa fitur tambahan, jangan otomatis mengimplementasikannya.

## 5. Role dan Permission

### Super Admin

Mengelola:

- User Management.
- Create/Manage Admin Account.
- Global Template.
- Modify Template Structure.
- System Configuration.
- Monitoring platform.

**Super Admin tidak mengelola Post.**

### Admin Kedinasan

Mengelola resource milik Dinas sendiri:

- Posts.
- Media.
- Pages.
- Appearance Slot.

Admin tidak boleh:

- Mengelola user global.
- Mengubah struktur Template.
- Mengakses data Dinas lain.
- Menambahkan CSS/script bebas.

### Masyarakat

- Tidak memiliki akses dashboard CMS.
- Hanya membaca data yang dipublikasikan.

Gunakan `ROLES_RBAC.md` sebagai acuan permission yang lebih detail.

## 6. Tenant Isolation

Tenant isolation adalah requirement keamanan utama.

Alur kepemilikan:

```text
User
 ↓
dinas_id
 ↓
Dinas
 ↓
Website
 ↓
Page / Post / Media / Appearance
```

Admin Kedinasan hanya boleh mengakses resource milik Dinas yang terhubung dengan akunnya.

Jangan hanya menyembunyikan data melalui frontend.

Backend wajib memverifikasi authorization dan ownership.

Contoh:

```text
Admin Dinas A → Resource Dinas A = ALLOW
Admin Dinas A → Resource Dinas B = DENY
```

Jangan mempercayai `website_id`, `dinas_id`, atau identifier dari client tanpa validasi.

## 7. Authentication dan Authorization

Authentication:

- Session-based.
- Login.
- Logout.

Authorization:

- Berdasarkan role.
- Membatasi akses dashboard dan resource.
- Harus diterapkan pada backend.

Authentication dan authorization bukan hal yang sama:

```text
Authentication = siapa pengguna?
Authorization  = apa yang boleh dilakukan?
```

Login berhasil tidak berarti user boleh mengakses seluruh CMS.

## 8. Keamanan

Prioritaskan keamanan backend.

Minimal:

- Jangan menyimpan password plaintext.
- Gunakan mekanisme hashing password yang aman.
- Validasi input di server.
- Validasi authorization sebelum operasi resource.
- Validasi ownership tenant.
- Validasi upload file.
- Jangan mengandalkan validasi frontend sebagai security.
- Jangan mengekspos secret/API key.
- Jangan menonaktifkan proteksi framework hanya untuk mempermudah development.
- Jangan memberikan permission melebihi requirement.

Untuk perubahan keamanan berisiko, jelaskan dampaknya sebelum menerapkannya.

## 9. Template Rule

Template adalah **Global Blueprint**.

Struktur fixed:

- Header / Navbar.
- Hero.
- Layanan / Informasi Singkat.
- Posts Terbaru.
- Profil Ringkas.
- Media / Dokumen Publik.
- Footer.
- Struktur section.
- Struktur card.
- Posisi layout.

Admin hanya mengisi slot data.

Contoh:

```text
Hero
├── title       → editable
├── description → editable
└── background  → editable

Posisi Hero     → NOT editable
Struktur Hero   → NOT editable
```

Jangan membuat Admin dapat mengubah layout.

## 10. Aturan Sebelum Coding

Sebelum mengubah kode:

1. Baca requirement yang relevan.
2. Periksa struktur project.
3. Cari implementasi yang sudah ada.
4. Identifikasi file yang terdampak.
5. Periksa role dan tenant boundary.
6. Implementasikan perubahan sekecil mungkin.
7. Siapkan verification yang relevan.

Jangan membuat file baru jika file/komponen yang sesuai sudah tersedia.

## 11. Aturan Implementasi

Gunakan pendekatan bertahap:

```text
Requirement
 ↓
Implementation
 ↓
Test
 ↓
Verification
 ↓
Next Task
```

### Perubahan Minimal

Jika diminta memperbaiki satu fitur:

- Ubah bagian yang diperlukan.
- Jangan melakukan refactor besar tanpa alasan.
- Jangan mengganti teknologi tanpa kebutuhan.
- Jangan menghapus kode yang tidak terkait.
- Pertahankan behavior yang sudah benar.

### Hindari Duplikasi

Sebelum membuat controller, component, service, helper, route, migration, atau utility:

1. Cari implementasi serupa.
2. Reuse jika sesuai.
3. Buat baru hanya jika memang diperlukan.

## 12. Database dan Migration

`SCHEMA.md` adalah acuan konseptual database, bukan migration final.

Sebelum membuat migration:

- Periksa schema.
- Periksa migration yang sudah ada.
- Hindari duplicate table/column.
- Perhatikan foreign key.
- Perhatikan tenant ownership.
- Jangan melakukan perubahan database destruktif tanpa alasan yang jelas.

Jika migration aktual bertentangan dengan schema, laporkan konflik dan jangan memilih secara diam-diam.

## 13. Frontend ≠ Security Boundary

Frontend hanya membantu pengalaman pengguna.

Backend bertanggung jawab atas:

- Authentication.
- Authorization.
- Validasi.
- Tenant isolation.
- Pemrosesan data.
- Perlindungan resource.

Contoh:

```text
Button Delete disembunyikan
        ≠
User tidak memiliki permission Delete
```

Backend tetap harus menolak request yang tidak sah.

## 14. Testing Minimum

Untuk fitur authorization, verifikasi:

### Positive

```text
User memiliki permission
→ request diterima
```

### Negative

```text
User tidak memiliki permission
→ request ditolak
```

### Tenant

```text
Admin Dinas A → resource A = boleh
Admin Dinas A → resource B = ditolak
```

### Public

```text
Published → tampil publik
Draft     → tidak tampil publik
```

## 15. Aturan Penggunaan AI

Agent bekerja berdasarkan repository dan dokumentasi aktual.

Jangan:

- Mengarang file yang belum ada.
- Mengarang endpoint.
- Mengarang tabel.
- Mengarang requirement.
- Menganggap library sudah terinstall.
- Mengubah arsitektur tanpa kebutuhan.

Jika informasi tidak tersedia:

```text
UNKNOWN
```

Jika keputusan belum ditetapkan:

```text
TBD
```

Lebih baik menyatakan `UNKNOWN`/`TBD` daripada membuat asumsi yang salah.

## 16. Efisiensi Kerja Agent

Untuk menghemat waktu dan token:

- Baca hanya dokumen yang relevan dengan task.
- Jangan membaca seluruh repository jika tidak diperlukan.
- Cari file/komponen terkait terlebih dahulu.
- Jangan mengulang requirement yang sudah jelas.
- Kerjakan satu task utama per iterasi.
- Hindari refactor yang tidak berhubungan.
- Jangan membuat dokumentasi baru tanpa kebutuhan.
- Gunakan kembali komponen dan logic yang sudah benar.
- Verifikasi perubahan sebelum melanjutkan ke task berikutnya.

## 17. Setelah Coding

Setelah perubahan:

1. Periksa syntax/error.
2. Jalankan test yang relevan jika tersedia.
3. Periksa authorization jika menyentuh akses.
4. Periksa migration/database jika menyentuh schema.
5. Periksa UI jika menyentuh frontend.
6. Pastikan bagian terkait yang sudah benar tidak rusak.
7. Update `PROGRESS.md` jika status implementasi berubah.

Jangan menyatakan fitur selesai hanya karena kode berhasil dibuat.

## 18. Format Laporan

Gunakan laporan ringkas:

```text
Task:
<task>

Changed:
- <file/perubahan>

Verification:
- <test/check>

Status:
DONE / BLOCKED

Notes:
<catatan jika ada>
```

## 19. Definition of Done

Task hanya boleh disebut `DONE` jika:

- Requirement telah diimplementasikan.
- Tidak melanggar permission.
- Tidak melanggar tenant isolation.
- Tidak menambahkan scope yang tidak diminta.
- Tidak ada error yang diketahui pada bagian terkait.
- Verification/test relevan telah dilakukan.
- Dokumentasi/status diperbarui jika diperlukan.

Jika belum memenuhi, gunakan `BLOCKED` atau jelaskan kondisi sebenarnya.

## 20. Prinsip Akhir

> **Follow the requirement, minimize assumptions, protect tenant boundaries, verify before declaring done.**

AI Agent bertugas mengimplementasikan requirement yang telah disepakati secara benar, aman, sederhana, dan dapat diverifikasi — bukan menambah kompleksitas project.
