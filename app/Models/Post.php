<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    public const PLACEMENTS = [
        'beranda', 'header', 'header_all', 'header_berita', 'header_pengumuman', 'header_kegiatan', 'sub_page',
        'profil', 'profil_tentang', 'profil_sejarah', 'profil_visi_misi', 'profil_tupoksi', 'profil_struktur', 'profil_pejabat',
        'layanan', 'layanan_daftar', 'layanan_persyaratan', 'layanan_prosedur', 'layanan_formulir', 'layanan_status',
        'informasi', 'informasi_pengumuman', 'informasi_agenda', 'informasi_publik', 'informasi_faq',
        'publikasi', 'publikasi_dokumen', 'publikasi_peraturan', 'publikasi_laporan', 'publikasi_statistik', 'publikasi_galeri',
        'kontak', 'kontak_alamat', 'kontak_email', 'kontak_telepon', 'kontak_medsos', 'kontak_peta',
    ];

    protected $fillable = [
        'website_id',
        'page_id',
        'user_id',
        'title',
        'slug',
        'content',
        'type',
        'image',
        'direct_link',
        'status',
        'placement',
        'published_at',
    ];

    public function isHeader(): bool
    {
        return $this->placement !== 'beranda';
    }

    public function appearsInHeaderCategory(string $category): bool
    {
        if (in_array($this->placement, ['header_all', 'header'])) {
            return true;
        }

        return match ($category) {
            'Berita' => $this->placement === 'header_berita',
            'Pengumuman' => $this->placement === 'header_pengumuman',
            'Kegiatan' => $this->placement === 'header_kegiatan',
            default => false,
        };
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
