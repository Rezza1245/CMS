<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Website extends Model
{
    protected $fillable = [
        'dinas_id',
        'template_id',
        'domain',
        'name',
        'status',
    ];

    public function dinas(): BelongsTo
    {
        return $this->belongsTo(Dinas::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function appearance(): HasOne
    {
        return $this->hasOne(Appearance::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Posts yang berstatus published saja (BR-04).
     */
    public function publishedPosts(): HasMany
    {
        return $this->posts()
            ->where('status', 'published')
            ->orderByDesc('published_at');
    }

    /**
     * Posts berstatus published yang ditempatkan di Menu Header (Berita).
     */
    public function publishedHeaderPosts(): HasMany
    {
        return $this->publishedPosts()
            ->whereIn('placement', ['header', 'header_all', 'header_berita', 'header_pengumuman', 'header_kegiatan']);
    }

    /**
     * Posts berstatus published yang ditempatkan di Menu Profil.
     */
    public function publishedProfilPosts(): HasMany
    {
        return $this->publishedPosts()
            ->where(fn($q) => $q->where('placement', 'profil')->orWhere('placement', 'like', 'profil_%'));
    }

    /**
     * Posts berstatus published yang ditempatkan di Menu Layanan.
     */
    public function publishedLayananPosts(): HasMany
    {
        return $this->publishedPosts()
            ->where(fn($q) => $q->where('placement', 'layanan')->orWhere('placement', 'like', 'layanan_%'));
    }

    /**
     * Posts berstatus published yang ditempatkan di Menu Informasi.
     */
    public function publishedInformasiPosts(): HasMany
    {
        return $this->publishedPosts()
            ->where(fn($q) => $q->where('placement', 'informasi')->orWhere('placement', 'like', 'informasi_%'));
    }

    /**
     * Posts berstatus published yang ditempatkan di Menu Publikasi.
     */
    public function publishedPublikasiPosts(): HasMany
    {
        return $this->publishedPosts()
            ->where(fn($q) => $q->where('placement', 'publikasi')->orWhere('placement', 'like', 'publikasi_%'));
    }

    /**
     * Posts berstatus published yang ditempatkan di Menu Kontak.
     */
    public function publishedKontakPosts(): HasMany
    {
        return $this->publishedPosts()
            ->where(fn($q) => $q->where('placement', 'kontak')->orWhere('placement', 'like', 'kontak_%'));
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function publishedPages(): HasMany
    {
        return $this->pages()->where('status', 'published');
    }

    /**
     * Halaman berstatus published yang dikhususkan tampil sebagai Tab di Beranda.
     */
    public function publishedBerandaPages(): HasMany
    {
        return $this->publishedPages()
            ->where('placement', 'beranda');
    }

    /**
     * Halaman sub-menu di bawah dropdown profil pada header.
     */
    public function publishedProfilPages(): HasMany
    {
        return $this->publishedPages()
            ->where(fn($q) => $q->where('placement', 'profil')->orWhere('placement', 'like', 'profil_%'))
            ->whereNull('parent_id')
            ->with(['publishedChildren.publishedChildren']);
    }

    /**
     * Halaman sub-menu di bawah dropdown layanan pada header.
     */
    public function publishedLayananPages(): HasMany
    {
        return $this->publishedPages()
            ->where(fn($q) => $q->where('placement', 'layanan')->orWhere('placement', 'like', 'layanan_%'))
            ->whereNull('parent_id')
            ->with(['publishedChildren.publishedChildren']);
    }

    /**
     * Halaman sub-menu di bawah dropdown informasi pada header.
     */
    public function publishedInformasiPages(): HasMany
    {
        return $this->publishedPages()
            ->where(fn($q) => $q->where('placement', 'informasi')->orWhere('placement', 'like', 'informasi_%'))
            ->whereNull('parent_id')
            ->with(['publishedChildren.publishedChildren']);
    }

    /**
     * Halaman sub-menu di bawah dropdown publikasi pada header.
     */
    public function publishedPublikasiPages(): HasMany
    {
        return $this->publishedPages()
            ->where(fn($q) => $q->where('placement', 'publikasi')->orWhere('placement', 'like', 'publikasi_%'))
            ->whereNull('parent_id')
            ->with(['publishedChildren.publishedChildren']);
    }

    /**
     * Halaman sub-menu di bawah dropdown kontak pada header (Email, Medsos, Lokasi).
     */
    public function publishedKontakPages(): HasMany
    {
        return $this->publishedPages()
            ->where(fn($q) => $q->where('placement', 'kontak')->orWhere('placement', 'like', 'kontak_%'))
            ->whereNull('parent_id')
            ->with(['publishedChildren.publishedChildren']);
    }

    /**
     * Halaman menu kustom yang sengaja dijadikan Menu Utama Baru di header.
     */
    public function publishedCustomHeaderPages(): HasMany
    {
        return $this->publishedPages()
            ->where('placement', 'header_menu')
            ->whereNull('parent_id')
            ->with(['publishedChildren.publishedChildren']);
    }

    /**
     * Halaman menu utama di bar header (selain beranda) beserta seluruh sub-bab dan sub-sub-bab.
     */
    public function publishedHeaderMenus(): HasMany
    {
        return $this->publishedPages()
            ->whereNull('parent_id')
            ->where('placement', '!=', 'beranda')
            ->orderBy('id', 'asc')
            ->with(['publishedChildren.publishedChildren.publishedChildren']);
    }

    /**
     * Halaman root (Level 1 Menu Header) berstatus published dengan relasi anak bersarang.
     */
    public function publishedRootPages(): HasMany
    {
        return $this->publishedPages()
            ->whereNull('parent_id')
            ->with(['publishedChildren.publishedChildren.publishedChildren']);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
