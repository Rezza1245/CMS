# ROUTING.md — Katalog Rute & Hak Akses Endpoint Pyojek CMS

Dokumen ini mendokumentasikan seluruh pemetaan rute, metode HTTP, controller, middleware keamanan, dan batasan hak akses yang dikonfigurasi pada `routes/web.php`.

---

## 1. Arsitektur & Tingkat Proteksi Rute

Sistem routing dibagi ke dalam 4 zona utama:
1. **Public Zone (Read-Only)**: Diakses oleh masyarakat umum tanpa login, dilindungi oleh rate limiter `throttle:public-site` (120 req/menit).
2. **Guest / Authentication Zone**: Khusus pengguna belum terotentikasi, dilindungi `throttle:login` (5 percobaan/menit per IP+Email).
3. **Admin Kedinasan Zone**: Terproteksi sesi `auth`, `throttle:cms-read` (120 req/menit), `throttle:cms-write` (40 req/menit), serta verifikasi kepemilikan tenant dinas pada level controller.
4. **Super Admin Zone**: Terproteksi sesi `auth` dan middleware `super_admin`, memiliki wewenang mengelola konfigurasi sistem global, blueprint template, akun admin dinas, dan pendaftaran website dinas.

---

## 2. Katalog Rute Lengkap

### 2.1 Zona Publik (Read-Only)

| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/` | — | Closure (`redirect -> login`) | `throttle:public-site` | Mengarahkan halaman awal ke portal login |
| `GET` | `/site/{identifier}` | `site.show` | `PublicWebsiteController@show` | `throttle:public-site` | Menampilkan beranda website dinas (berdasarkan domain atau ID tenant) |
| `GET` | `/site/{identifier}/page/{slug}` | `site.page.show` | `PublicWebsiteController@showPage` | `throttle:public-site` | Menampilkan halaman statis institusi dinas |
| `GET` | `/site/{identifier}/post/{slug}` | `site.post.show` | `PublicWebsiteController@showPost` | `throttle:public-site` | Menampilkan artikel berita / pengumuman publik |

---

### 2.2 Zona Autentikasi (Guest & Sesi)

| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/login` | `login` | `AuthController@showLoginForm` | `guest`, `throttle:public-site` | Menampilkan formulir login CMS |
| `POST` | `/login` | `login.post` | `AuthController@login` | `guest`, `throttle:login` | Memproses autentikasi kredensial pengguna |
| `POST` | `/logout` | `logout` | `AuthController@logout` | `auth`, `throttle:cms-read` | Mengakhiri sesi login & regenerasi token sesi |

---

### 2.3 Dashboard Umum

| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/admin/dashboard` | `admin.dashboard` | Closure | `auth`, `throttle:cms-read` | Rute dashboard pintar: merender view Super Admin atau Admin Dinas sesuai role |
| `GET` | `/dinas/dashboard` | `dinas.dashboard` | Closure | `auth`, `throttle:cms-read` | Dashboard operasional Admin Kedinasan (Quick Access Draft & Metrik Tenant) |

---

### 2.4 Zona Admin Kedinasan (`role: admin_dinas`)

Seluruh operasi pada zona ini memvalidasi keterikatan `dinas_id` akun pengguna terhadap `website_id` resource terkait (Tenant Isolation).

#### Posts Management (Berita / Pengumuman / Kegiatan)
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/dinas/posts` | `dinas.posts.index` | `DinasPostController@index` | `auth`, `throttle:cms-read` | Daftar postingan artikel milik dinas |
| `GET` | `/dinas/posts/create` | `dinas.posts.create` | `DinasPostController@create` | `auth`, `throttle:cms-read` | Formulir tambah postingan artikel baru |
| `POST` | `/dinas/posts` | `dinas.posts.store` | `DinasPostController@store` | `auth`, `throttle:cms-write` | Menyimpan postingan artikel baru |
| `GET` | `/dinas/posts/{post}/edit` | `dinas.posts.edit` | `DinasPostController@edit` | `auth`, `throttle:cms-read` | Formulir edit postingan artikel dinas |
| `PUT` | `/dinas/posts/{post}` | `dinas.posts.update` | `DinasPostController@update` | `auth`, `throttle:cms-write` | Memperbarui data postingan artikel dinas |
| `DELETE` | `/dinas/posts/{post}` | `dinas.posts.destroy` | `DinasPostController@destroy` | `auth`, `throttle:cms-write` | Menghapus postingan artikel dinas |

#### Media Management (Dokumen PDF & Berkas Gambar)
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/dinas/media` | `dinas.media.index` | `DinasMediaController@index` | `auth`, `throttle:cms-read` | Galeri pustaka media dinas & kuota |
| `POST` | `/dinas/media` | `dinas.media.store` | `DinasMediaController@store` | `auth`, `throttle:cms-write` | Mengunggah berkas baru (WebP auto-convert) |
| `DELETE` | `/dinas/media/{media}` | `dinas.media.destroy` | `DinasMediaController@destroy` | `auth`, `throttle:cms-write` | Menghapus berkas media fisik dari disk |

#### Pages Management (Halaman Statis Kelembagaan)
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/dinas/pages` | `dinas.pages.index` | `DinasPageController@index` | `auth`, `throttle:cms-read` | Struktur hierarki navigasi & daftar halaman |
| `GET` | `/dinas/pages/create` | `dinas.pages.create` | `DinasPageController@create` | `auth`, `throttle:cms-read` | Formulir tambah halaman / wadah / menu baru |
| `POST` | `/dinas/pages` | `dinas.pages.store` | `DinasPageController@store` | `auth`, `throttle:cms-write` | Menyimpan halaman baru ke pohon navigasi |
| `GET` | `/dinas/pages/{page}/edit` | `dinas.pages.edit` | `DinasPageController@edit` | `auth`, `throttle:cms-read` | Formulir edit halaman dinas |
| `PUT` | `/dinas/pages/{page}` | `dinas.pages.update` | `DinasPageController@update` | `auth`, `throttle:cms-write` | Memperbarui isi dan penempatan halaman |
| `DELETE` | `/dinas/pages/{page}` | `dinas.pages.destroy` | `DinasPageController@destroy` | `auth`, `throttle:cms-write` | Menghapus halaman (cascade anak-anaknya) |

#### Appearance Slot Management
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/dinas/appearance` | `dinas.appearance.edit` | `DinasAppearanceController@edit` | `auth`, `throttle:cms-read` | Formulir pengaturan Appearance Slots dinas |
| `PUT` | `/dinas/appearance` | `dinas.appearance.update` | `DinasAppearanceController@update` | `auth`, `throttle:cms-write` | Menyimpan perubahan nilai slot tampilan |

#### Settings Website Kedinasan
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/dinas/settings` | `dinas.settings.edit` | `DinasSettingController@edit` | `auth`, `throttle:cms-read` | Formulir identitas situs & pemeliharaan |
| `PUT` | `/dinas/settings` | `dinas.settings.update` | `DinasSettingController@update` | `auth`, `throttle:cms-write` | Memperbarui status situs & privasi |

---

### 2.5 Zona Super Admin (`role: super_admin`)

Seluruh rute pada grup ini wajib melewati middleware `EnsureSuperAdmin` (`super_admin`). Permintaan dari Admin Kedinasan akan langsung ditolak dengan status HTTP 403 Forbidden.

#### Visual Template Builder
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/admin/templates/{template}/builder` | `admin.templates.builder` | `TemplateBuilderController@show` | `auth`, `super_admin`, `throttle:cms-read` | Antarmuka kanvas builder drag-and-drop |
| `PUT` | `/admin/templates/{template}/builder` | `admin.templates.builder.update` | `TemplateBuilderController@update` | `auth`, `super_admin`, `throttle:cms-write` | Menyimpan struktur JSON canvas data template |

#### User Management (Akun Admin Kedinasan)
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/admin/users` | `admin.users.index` | `AdminUserController@index` | `auth`, `super_admin`, `throttle:cms-read` | Daftar akun pengelola sistem & filter |
| `GET` | `/admin/users/create` | `admin.users.create` | `AdminUserController@create` | `auth`, `super_admin`, `throttle:cms-read` | Formulir pendaftaran akun admin dinas baru |
| `POST` | `/admin/users` | `admin.users.store` | `AdminUserController@store` | `auth`, `super_admin`, `throttle:cms-write` | Menyimpan akun admin dinas baru |
| `GET` | `/admin/users/{user}/edit` | `admin.users.edit` | `AdminUserController@edit` | `auth`, `super_admin`, `throttle:cms-read` | Formulir ubah profil/status & reset password |
| `PUT` | `/admin/users/{user}` | `admin.users.update` | `AdminUserController@update` | `auth`, `super_admin`, `throttle:cms-write` | Memperbarui data pengguna |
| `DELETE` | `/admin/users/{user}` | `admin.users.destroy` | `AdminUserController@destroy` | `auth`, `super_admin`, `throttle:cms-write` | Menghapus akun pengguna dari sistem |

#### Website Dinas Management
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/admin/websites` | `admin.websites.index` | `AdminWebsiteController@index` | `auth`, `super_admin`, `throttle:cms-read` | Daftar seluruh portal website dinas terdaftar |
| `GET` | `/admin/websites/create` | `admin.websites.create` | `AdminWebsiteController@create` | `auth`, `super_admin`, `throttle:cms-read` | Formulir pendaftaran website dinas baru |
| `POST` | `/admin/websites` | `admin.websites.store` | `AdminWebsiteController@store` | `auth`, `super_admin`, `throttle:cms-write` | Menyimpan website baru + auto relasi |
| `GET` | `/admin/websites/{website}/edit` | `admin.websites.edit` | `AdminWebsiteController@edit` | `auth`, `super_admin`, `throttle:cms-read` | Formulir edit entitas website dinas |
| `PUT` | `/admin/websites/{website}` | `admin.websites.update` | `AdminWebsiteController@update` | `auth`, `super_admin`, `throttle:cms-write` | Memperbarui entitas website dinas |
| `DELETE` | `/admin/websites/{website}` | `admin.websites.destroy` | `AdminWebsiteController@destroy` | `auth`, `super_admin`, `throttle:cms-write` | Menghapus website dinas (cascade database) |

#### System Configuration & Monitoring
| Metode | URI | Nama Rute | Controller & Action | Middleware | Deskripsi |
|---|---|---|---|---|---|
| `GET` | `/admin/settings` | `admin.settings.edit` | `AdminSettingController@edit` | `auth`, `super_admin`, `throttle:cms-read` | Formulir batas kuota media & format berkas |
| `PUT` | `/admin/settings` | `admin.settings.update` | `AdminSettingController@update` | `auth`, `super_admin`, `throttle:cms-write` | Menyimpan konfigurasi sistem terpusat |
| `GET` | `/admin/activities` | `admin.activities.index` | `AdminActivityController@index` | `auth`, `super_admin`, `throttle:cms-read` | Log audit aktivitas platform seluruh tenant |

---

## 3. Penegakan Otorisasi & Validasi Tenant

1. **Route Model Binding Otomatis**:  
   Parameter rute seperti `{post}`, `{page}`, dan `{media}` diperiksa secara eksplisit kepemilikan `website_id`-nya terhadap akun admin yang sedang login pada lapisan Controller.
2. **Pencegahan Eksploitasi IDOR**:  
   Permintaan manipulasi data dinas lain (misal Admin Dinas A mencoba mengubah ID postingan Dinas B) selalu menghasilkan `403 Forbidden` atau `404 Not Found`.
