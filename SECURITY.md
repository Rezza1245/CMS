# SECURITY.md — Kebijakan & Standar Keamanan Sistem Pyojek CMS

Dokumen ini mendefinisikan standar keamanan, mekanisme pertahanan, dan arsitektur isolasi multi-tenant yang diterapkan pada platform Pyojek CMS.

---

## 1. Prinsip Utama Keamanan

1. **Frontend ≠ Security Boundary**:  
   Menyembunyikan tombol, menonaktifkan elemen input, atau memvalidasi form pada antarmuka frontend hanyalah peningkatan pengalaman pengguna (UX). Seluruh otorisasi, hak akses, validasi kepemilikan, dan validasi tipe berkas **wajib dieksekusi secara mutlak di lapisan backend**.
2. **Zero Trust pada Identifier Klien**:  
   Sistem tidak pernah mempercayai parameter `website_id`, `dinas_id`, atau identifier tersembunyi (*hidden inputs*) yang dikirimkan oleh browser klien tanpa verifikasi silang terhadap sesi login pengguna.
3. **Penyimpanan Kredensial Aman**:  
   Seluruh kata sandi pengguna dienkripsi menggunakan algoritma hashing standar industri (`bcrypt` / `Argon2id`). Kata sandi plaintext dilarang keras disimpan di log maupun basis data.

---

## 2. Isolasi Multi-Tenant (Tenant Boundary Protection)

Pyojek CMS mengimplementasikan rantai kepemilikan data yang ketat:

```text
User (Pengguna Login)
  ↓
dinas_id (Kunci Afiliasi Dinas)
  ↓
Dinas
  ↓
Website (1 Dinas = Tepat 1 Website)
  ↓
[ Posts / Pages / Media / Appearance / Settings ]
```

### 2.1 Verifikasi Akses pada Controller
Setiap operasi baca (*read*) maupun mutasi (*create, update, delete*) oleh Admin Kedinasan wajib memvalidasi:
```php
$user = auth()->user();
if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
    abort(403, 'Akses ditolak.');
}

$website = $user->dinas?->website;
if (! $website || $resource->website_id !== $website->id) {
    abort(403, 'Anda tidak memiliki hak akses terhadap data kedinasan ini.');
}
```

### 2.2 Pencegahan Serangan IDOR (Insecure Direct Object Reference)
Jika Admin Dinas A mencoba memanipulasi URL untuk mengakses atau menghapus resource milik Dinas B (misal: `/dinas/posts/99/edit`), sistem langsung memutus request dengan status **HTTP 403 Forbidden** atau **HTTP 404 Not Found**.

---

## 3. Rate Limiting & Proteksi Serangan Brute-Force

Untuk mencegah serangan *credential stuffing*, *brute-force login*, *DDoS*, serta *scraping flooding*, sistem menerapkan 4 lapisan pembatas laju permintaan (*Rate Limiter*) terdaftar pada `AppServiceProvider`:

| Nama Limiter | Batas Maksimum | Target Scope | Respons Bila Melebihi Batas | Tujuan Keamanan |
|---|---|---|---|---|
| `login` | **5 permintaan / menit** | Kombinasi Hash IP + Email | `429 Too Many Requests` (Tampilan Khusus) | Mencegah tebakan kata sandi otomatis & pembajakan akun |
| `public-site` | **120 permintaan / menit** | Alamat IP Pengunjung | `429 Too Many Requests` | Mencegah bot scraper & flooding traffic pada situs publik |
| `cms-read` | **120 permintaan / menit** | ID Pengguna / IP | `429 Too Many Requests` | Melindungi beban server dari navigasi spam dashboard |
| `cms-write` | **40 permintaan / menit** | ID Pengguna / IP | `429 Too Many Requests` | Mencegah bot spamming submit form & flooding unggahan berkas |

---

## 4. Sanitasi Data & Pencegahan Injeksi (XSS & Script Injection)

Pyojek CMS melarang keras injeksi kode bebas (*Strict Out-of-Scope: No Custom CSS / Arbitrary Scripts*).

Pada mesin perender kanvas (`App\Services\CanvasRenderer`), seluruh nilai slot teks dan variabel dinas dibersihkan melalui metode sanitasi berlapis:
```php
public function sanitizeString(?string $value): ?string
{
    if ($value === null) return null;

    // 1. Hapus tag script beserta isinya secara menyeluruh
    $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);

    // 2. Hapus tag style dan CSS mentah
    $clean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $clean);

    // 3. Strip sisa tag HTML berbahaya
    $clean = strip_tags($clean);

    // 4. Cegah eksekusi pseudoprotocol javascript: atau data:
    if (preg_match('/^\s*(javascript|vbscript|data):/i', $clean)) {
        return '';
    }

    return trim($clean);
}
```

---

## 5. Keamanan Unggahan Berkas & Pustaka Media

Sistem menerapkan proteksi ketat melalui `App\Services\MediaOptimizerService`:

1. **Restriksi Ekstensi & MIME Type**:  
   Hanya berkas gambar (`jpg`, `jpeg`, `png`, `webp`, `svg`) dan berkas dokumen resmi (`pdf`, `docx`, `xlsx`, `pptx`) yang diizinkan. Ekstensi berkas yang dapat dieksekusi (`.php`, `.exe`, `.sh`, `.js`, dll.) diblokir mutlak oleh server.
2. **Pembersihan Metadata (EXIF Stripping)**:  
   Seluruh gambar raster yang diunggah otomatis diproses dan dikonversi menjadi format **WebP**. Proses pemrosesan ulang ini secara otomatis menghapus metadata EXIF tersembunyi (seperti koordinat GPS lokasi pemotretan dan identitas perangkat) demi privasi pengguna.
3. **Isolasi Direktori Unggahan**:  
   Aset disimpan dalam sub-direktori disk yang terkelola (`public/storage/media/`, `public/storage/logos/`, `public/storage/hero/`).

---

## 6. Proteksi CSRF & Manajemen Sesi

- **CSRF Token**: Seluruh mutasi HTTP berstatus non-GET (`POST`, `PUT`, `DELETE`) wajib menyertakan token CSRF valid (`@csrf` atau header `X-CSRF-TOKEN`).
- **Regenerasi Token Sesi**: Sistem secara otomatis meregenerasi session ID saat pengguna berhasil login dan membatalkan seluruh sesi saat pengguna logout (`session()->invalidate()`, `session()->regenerateToken()`) untuk mencegah serangan *Session Fixation*.
- **Session Hijacking Mitigation**: Cookie sesi dikonfigurasi dengan flag `HttpOnly`, `SameSite=Lax`, dan `Secure` (saat berjalan di protokol HTTPS).

---

## 7. Mode Pemeliharaan Tenant (Maintenance Mode)

Admin Kedinasan dapat mengaktifkan status situs `pemeliharaan`. Ketika aktif:
- Pengunjung publik dan tamu (guest) menerima halaman respons **HTTP 503 Service Unavailable**.
- Pengelola akun dinas terkait dan Super Admin tetap dapat melihat pratinjau internal secara aman untuk keperluan audit konten sebelum rilis publik.
