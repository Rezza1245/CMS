<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory;

    public const PLACEMENTS = [
        'beranda', 'header_menu', 'sub_menu',
        'profil', 'profil_tentang', 'profil_sejarah', 'profil_visi_misi', 'profil_tupoksi', 'profil_struktur', 'profil_pejabat',
        'layanan', 'layanan_daftar', 'layanan_persyaratan', 'layanan_prosedur', 'layanan_formulir', 'layanan_status',
        'informasi', 'informasi_pengumuman', 'informasi_agenda', 'informasi_publik', 'informasi_faq',
        'publikasi', 'publikasi_dokumen', 'publikasi_peraturan', 'publikasi_laporan', 'publikasi_statistik', 'publikasi_galeri',
        'kontak', 'kontak_alamat', 'kontak_email', 'kontak_telepon', 'kontak_medsos', 'kontak_peta',
    ];

    protected $fillable = [
        'website_id',
        'parent_id',
        'title',
        'slug',
        'content',
        'status',
        'placement',
        'image',
        'direct_link',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function publishedChildren(): HasMany
    {
        return $this->children()
            ->where('status', 'published')
            ->with('publishedChildren');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function getDepth(): int
    {
        $depth = 1;
        $current = $this;

        while ($current->parent_id !== null && $current->parent) {
            $depth++;
            $current = $current->parent;
        }

        return $depth;
    }

    public function isContainerItem(): bool
    {
        return $this->getDepth() >= 4;
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()
            ->where('status', 'published')
            ->orderByDesc('published_at');
    }
}
