<?php

namespace Tests\Feature;

use App\Models\Appearance;
use App\Models\Dinas;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DinasPostTest extends TestCase
{
    use RefreshDatabase;

    protected Template $template;

    protected Dinas $dinasA;

    protected Website $siteA;

    protected User $adminDinasA;

    protected Dinas $dinasB;

    protected Website $siteB;

    protected User $adminDinasB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = Template::create([
            'name' => 'Template Standar',
            'status' => 'aktif',
            'template_version' => '1.0',
            'header_structure' => 'default',
            'post_layout' => 'grid',
            'page_layout' => 'standard',
            'navigation_structure' => 'top',
            'canvas_data' => [
                'template_version' => '1.0',
                'canvas' => [
                    [
                        'id' => 'sec_posts',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_posts',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'posts_comp',
                                        'component' => 'Posts Grid',
                                        'layout_settings' => ['columns' => 3, 'limit' => 6],
                                        'slots' => [
                                            'items' => ['slot_id' => 'posts_source', 'binding' => 'posts.published'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // Dinas A
        $this->dinasA = Dinas::create([
            'name' => 'Dinas Kesehatan Kota Batu',
            'code' => 'DINKES',
        ]);
        $this->siteA = Website::create([
            'dinas_id' => $this->dinasA->id,
            'template_id' => $this->template->id,
            'domain' => 'dinkes',
            'name' => 'Portal Dinkes Batu',
            'status' => 'aktif',
        ]);
        $this->adminDinasA = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasA->id,
        ]);

        // Dinas B
        $this->dinasB = Dinas::create([
            'name' => 'Dinas Pariwisata Kota Batu',
            'code' => 'DISPARTA',
        ]);
        $this->siteB = Website::create([
            'dinas_id' => $this->dinasB->id,
            'template_id' => $this->template->id,
            'domain' => 'pariwisata',
            'name' => 'Portal Wisata Batu',
            'status' => 'aktif',
        ]);
        $this->adminDinasB = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasB->id,
        ]);
    }

    public function test_admin_dinas_can_view_post_index_with_tenant_isolation(): void
    {
        $postA = Post::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'title' => 'Vaksinasi Terpadu Balita',
            'slug' => 'vaksinasi-terpadu-balita',
            'type' => 'Berita',
            'content' => 'Layanan imunisasi lengkap.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $postB = Post::create([
            'website_id' => $this->siteB->id,
            'user_id' => $this->adminDinasB->id,
            'title' => 'Festival Bunga Selecta',
            'slug' => 'festival-bunga-selecta',
            'type' => 'Berita',
            'content' => 'Pameran keindahan bunga Kota Batu.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->adminDinasA)->get(route('dinas.posts.index'));

        $response->assertStatus(200);
        $response->assertSee('Vaksinasi Terpadu Balita');
        $response->assertDontSee('Festival Bunga Selecta');
    }

    public function test_admin_dinas_can_create_published_post_and_it_appears_on_posts_grid(): void
    {
        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Puskesmas 24 Jam Buka di Kota Batu',
            'type' => 'Berita',
            'content' => 'Pelayanan rawat inap dan IGD puskesmas siaga 24 jam.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('dinas.posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'title' => 'Puskesmas 24 Jam Buka di Kota Batu',
            'slug' => 'puskesmas-24-jam-buka-di-kota-batu',
            'type' => 'Berita',
            'status' => 'published',
        ]);

        // Pastikan langsung muncul pada komponen Posts Grid di website publik
        $siteResponse = $this->get(route('site.show', 'dinkes'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('Puskesmas 24 Jam Buka di Kota Batu');
    }

    public function test_draft_post_does_not_appear_on_public_website_posts_grid(): void
    {
        $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Draf Rahasia Internal Dinkes',
            'type' => 'Pengumuman',
            'content' => 'Rancangan internal belum untuk konsumsi publik.',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'title' => 'Draf Rahasia Internal Dinkes',
            'status' => 'draft',
        ]);

        // Web publik tidak boleh menampilkan draft
        $siteResponse = $this->get(route('site.show', 'dinkes'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertDontSee('Draf Rahasia Internal Dinkes');
    }

    public function test_admin_dinas_can_update_post(): void
    {
        $post = Post::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'title' => 'Judul Lama',
            'slug' => 'judul-lama',
            'type' => 'Berita',
            'content' => 'Isi berita lama.',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.posts.update', $post), [
            'title' => 'Judul Baru yang Direvisi',
            'type' => 'Kegiatan',
            'content' => 'Isi konten yang diperbarui.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('dinas.posts.index'));
        $post->refresh();

        $this->assertEquals('Judul Baru yang Direvisi', $post->title);
        $this->assertEquals('judul-baru-yang-direvisi', $post->slug);
        $this->assertEquals('Kegiatan', $post->type);
        $this->assertEquals('published', $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_admin_dinas_cannot_edit_or_delete_other_dinas_post(): void
    {
        $postB = Post::create([
            'website_id' => $this->siteB->id,
            'user_id' => $this->adminDinasB->id,
            'title' => 'Paket Wisata Paralayang',
            'slug' => 'paket-wisata-paralayang',
            'type' => 'Berita',
            'content' => 'Nikmati pemandangan Kota Batu dari udara.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Admin Dinas A mencoba membuka edit post milik Dinas B
        $editResponse = $this->actingAs($this->adminDinasA)->get(route('dinas.posts.edit', $postB));
        $editResponse->assertStatus(403);

        // Admin Dinas A mencoba mengupdate post milik Dinas B
        $updateResponse = $this->actingAs($this->adminDinasA)->put(route('dinas.posts.update', $postB), [
            'title' => 'Hacked by Dinas A',
            'type' => 'Berita',
            'content' => 'Injeksi artikel.',
            'status' => 'published',
        ]);
        $updateResponse->assertStatus(403);

        // Admin Dinas A mencoba menghapus post milik Dinas B
        $deleteResponse = $this->actingAs($this->adminDinasA)->delete(route('dinas.posts.destroy', $postB));
        $deleteResponse->assertStatus(403);

        // Pastikan post B tetap utuh
        $this->assertDatabaseHas('posts', [
            'id' => $postB->id,
            'title' => 'Paket Wisata Paralayang',
        ]);
    }

    public function test_admin_dinas_can_delete_own_post(): void
    {
        $post = Post::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'title' => 'Berita Dihapus',
            'slug' => 'berita-dihapus',
            'type' => 'Berita',
            'content' => 'Isi.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->adminDinasA)->delete(route('dinas.posts.destroy', $post));

        $response->assertRedirect(route('dinas.posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_super_admin_cannot_access_dinas_posts(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('dinas.posts.index'));
        $response->assertStatus(403);
    }

    public function test_admin_dinas_can_upload_post_image_and_it_records_to_media_table(): void
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->create('thumbnail-berita.jpg', 800, 'image/jpeg');

        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Peringatan Hari Pendidikan Kota Batu 2026',
            'type' => 'Kegiatan',
            'content' => 'Rangkaian kegiatan peringatan Hardiknas Kota Batu.',
            'status' => 'published',
            'image_file' => $imageFile,
        ]);

        $response->assertRedirect(route('dinas.posts.index'));

        $post = Post::where('slug', 'peringatan-hari-pendidikan-kota-batu-2026')->firstOrFail();
        $this->assertNotNull($post->image);
        Storage::disk('public')->assertExists($post->image);

        // Pastikan otomatis tercatat di tabel media (SCHEMA 4.9 & PRD 4.4)
        $this->assertDatabaseHas('media', [
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'thumbnail-berita.jpg',
            'file_path' => $post->image,
        ]);
    }

    public function test_admin_dinas_can_create_post_with_direct_link(): void
    {
        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Pengumuman Seleksi Beasiswa Mahasiswa',
            'type' => 'Pengumuman',
            'content' => 'Unduh berkas pendaftaran melalui Google Drive berikut.',
            'status' => 'published',
            'direct_link' => 'https://drive.google.com/file/d/sample-gdrive-doc/view',
        ]);

        $response->assertRedirect(route('dinas.posts.index'));

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'title' => 'Pengumuman Seleksi Beasiswa Mahasiswa',
            'direct_link' => 'https://drive.google.com/file/d/sample-gdrive-doc/view',
        ]);

        $siteResponse = $this->get(route('site.show', $this->siteA->domain));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('https://drive.google.com/file/d/sample-gdrive-doc/view');
        $siteResponse->assertSee('Unduh / Buka Dokumen');
    }

    public function test_admin_dinas_can_create_post_with_header_placement(): void
    {
        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Pengumuman Penting Vaksinasi Booster',
            'type' => 'Pengumuman',
            'placement' => 'header',
            'content' => 'Vaksinasi booster tersedia di seluruh puskesmas se-Kota Batu.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('dinas.posts.index'));

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'title' => 'Pengumuman Penting Vaksinasi Booster',
            'placement' => 'header',
            'status' => 'published',
        ]);

        // Post tampil di halaman website publik
        $siteResponse = $this->get(route('site.show', $this->siteA->domain));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('Pengumuman Penting Vaksinasi Booster');
        $siteResponse->assertSee(route('site.post.show', ['identifier' => $this->siteA->domain, 'slug' => 'pengumuman-penting-vaksinasi-booster']));
    }

    public function test_public_user_can_view_post_detail_and_tenant_isolation_is_enforced(): void
    {
        $postA = Post::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'title' => 'Pelayanan IGD Siaga Bencana',
            'slug' => 'pelayanan-igd-siaga-bencana',
            'type' => 'Berita',
            'placement' => 'header',
            'content' => 'Kesiapsiagaan penanganan gawat darurat bencana.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 1. Publik bisa melihat halaman artikel post
        $response = $this->get(route('site.post.show', ['identifier' => $this->siteA->domain, 'slug' => 'pelayanan-igd-siaga-bencana']));
        $response->assertStatus(200);
        $response->assertSee('Pelayanan IGD Siaga Bencana');
        $response->assertSee('Kesiapsiagaan penanganan gawat darurat bencana.');
        $response->assertSee('Tampil di Menu Header');

        // 2. Isolasi tenant: tidak bisa membuka slug post dinas A lewat domain dinas B
        $crossTenantResponse = $this->get(route('site.post.show', ['identifier' => $this->siteB->domain, 'slug' => 'pelayanan-igd-siaga-bencana']));
        $crossTenantResponse->assertStatus(404);

        // 3. Draft post tidak bisa dibuka
        $draftPost = Post::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'title' => 'Draft Rahasia Internal',
            'slug' => 'draft-rahasia-internal',
            'type' => 'Berita',
            'placement' => 'header',
            'content' => 'Draft.',
            'status' => 'draft',
        ]);

        $draftResponse = $this->get(route('site.post.show', ['identifier' => $this->siteA->domain, 'slug' => 'draft-rahasia-internal']));
        $draftResponse->assertStatus(404);
    }

    public function test_admin_dinas_can_place_post_in_all_sub_menus_under_berita(): void
    {
        // 1. Post khusus sub-menu Berita Kedinasan
        $resp1 = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Berita Khusus Kedinasan',
            'type' => 'Berita',
            'placement_target' => 'header_berita',
            'content' => 'Isi berita kedinasan.',
            'status' => 'published',
        ]);
        $resp1->assertRedirect(route('dinas.posts.index'));

        // 2. Post di seluruh sub-menu Berita
        $resp2 = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Pengumuman Seluruh Kategori',
            'type' => 'Pengumuman',
            'placement_target' => 'header_all',
            'content' => 'Pengumuman yang muncul di semua sub-menu berita.',
            'status' => 'published',
        ]);
        $resp2->assertRedirect(route('dinas.posts.index'));

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'title' => 'Berita Khusus Kedinasan',
            'placement' => 'header_berita',
        ]);

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'title' => 'Pengumuman Seluruh Kategori',
            'placement' => 'header_all',
        ]);

        $siteResponse = $this->get(route('site.show', $this->siteA->domain));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('Berita Khusus Kedinasan');
        $siteResponse->assertSee('Pengumuman Seluruh Kategori');
    }

    public function test_admin_dinas_can_place_post_in_profil_and_kontak_header_menus(): void
    {
        // Post di menu Profil
        $respProfil = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Laporan Akuntabilitas Kinerja 2026',
            'type' => 'Berita',
            'placement_target' => 'profil',
            'content' => 'Laporan kinerja resmi.',
            'status' => 'published',
        ]);
        $respProfil->assertRedirect(route('dinas.posts.index'));

        // Post di menu Kontak
        $respKontak = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Nomor Pengaduan Darurat Call Center',
            'type' => 'Pengumuman',
            'placement_target' => 'kontak',
            'content' => 'Hotline 112 bebas pulsa.',
            'status' => 'published',
        ]);
        $respKontak->assertRedirect(route('dinas.posts.index'));

        $siteResponse = $this->get(route('site.show', $this->siteA->domain));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('Laporan Akuntabilitas Kinerja 2026');
        $siteResponse->assertSee('Nomor Pengaduan Darurat Call Center');
    }

    public function test_admin_dinas_can_attach_post_to_specific_page_with_tenant_isolation(): void
    {
        $pageA = \App\Models\Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan Perizinan',
            'slug' => 'layanan-perizinan',
            'content' => 'Konten halaman perizinan.',
            'status' => 'published',
        ]);

        $pageB = \App\Models\Page::create([
            'website_id' => $this->siteB->id,
            'title' => 'Layanan Wisata',
            'slug' => 'layanan-wisata',
            'content' => 'Konten wisata dinas lain.',
            'status' => 'published',
        ]);

        // 1. Sukses menautkan ke halaman milik dinas sendiri
        $respOk = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Buku Panduan SOP Perizinan 2026',
            'type' => 'Pengumuman',
            'placement_target' => 'page_' . $pageA->id,
            'content' => 'Panduan lengkap tata cara perizinan.',
            'status' => 'published',
        ]);
        $respOk->assertRedirect(route('dinas.posts.index'));

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'page_id' => $pageA->id,
            'placement' => 'sub_page',
            'title' => 'Buku Panduan SOP Perizinan 2026',
        ]);

        // Verifikasi tampil di halaman publik terkait
        $pageResponse = $this->get(route('site.page.show', ['identifier' => $this->siteA->domain, 'slug' => $pageA->slug]));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Publikasi & Dokumen Terkait Layanan Perizinan');
        $pageResponse->assertSee('Buku Panduan SOP Perizinan 2026');

        // 2. Isolasi tenant: ditolak jika mencoba menautkan ke halaman milik dinas lain
        $respDeny = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Penyusup Halaman',
            'type' => 'Berita',
            'placement_target' => 'page_' . $pageB->id,
            'content' => 'Tidak boleh lolos.',
            'status' => 'published',
        ]);
        $respDeny->assertSessionHasErrors(['page_id']);
        $this->assertDatabaseMissing('posts', ['title' => 'Penyusup Halaman']);
    }

    public function test_admin_dinas_can_create_post_with_specific_header_sub_menu_placements(): void
    {
        // 1. Post pada sub-menu Informasi Pengumuman
        $respPeng = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Pengumuman Pelayanan Libur Nasional',
            'type' => 'Pengumuman',
            'placement_target' => 'informasi_pengumuman',
            'content' => 'Pelayanan tatap muka diliburkan.',
            'status' => 'published',
        ]);
        $respPeng->assertRedirect(route('dinas.posts.index'));

        // 2. Post pada sub-menu Kontak Telepon
        $respTel = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Hotline Darurat Ambulans Dinkes 24 Jam',
            'type' => 'Pengumuman',
            'placement_target' => 'kontak_telepon',
            'content' => 'Hubungi 0341-591119 bebas pulsa.',
            'status' => 'published',
        ]);
        $respTel->assertRedirect(route('dinas.posts.index'));

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'title' => 'Pengumuman Pelayanan Libur Nasional',
            'placement' => 'informasi_pengumuman',
        ]);

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'title' => 'Hotline Darurat Ambulans Dinkes 24 Jam',
            'placement' => 'kontak_telepon',
        ]);

        // Verifikasi tautan muncul di navbar publik
        $siteResponse = $this->get(route('site.show', $this->siteA->domain));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee(route('site.post.show', ['identifier' => $this->siteA->domain, 'slug' => 'pengumuman-pelayanan-libur-nasional']));
        $siteResponse->assertSee(route('site.post.show', ['identifier' => $this->siteA->domain, 'slug' => 'hotline-darurat-ambulans-dinkes-24-jam']));
    }

    public function test_post_create_form_displays_only_beranda_and_helper_alert_when_no_pages_exist(): void
    {
        $response = $this->actingAs($this->adminDinasA)->get(route('dinas.posts.create'));

        $response->assertStatus(200);
        $response->assertSee('🏠 Tampilkan di Beranda (Posts Grid Utama — Default)');
        $response->assertSee('Menu header / halaman kedinasan saat ini masih kosong. Jika ingin menempatkan artikel di bawah menu header atau sub-bab tertentu, buat menunya terlebih dahulu di modul Pages.');
        $response->assertDontSee('Tautkan ke Menu Header / Halaman Kedinasan:');
        $response->assertDontSee('Sub-menu Berita:');
    }

    public function test_post_create_and_edit_form_displays_structured_menu_pages_when_pages_exist(): void
    {
        $rootPage = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Profil Kedinasan',
            'slug' => 'profil-kedinasan',
            'content' => 'Konten profil.',
            'status' => 'published',
            'placement' => 'header_menu',
        ]);

        $childPage = Page::create([
            'website_id' => $this->siteA->id,
            'parent_id' => $rootPage->id,
            'title' => 'Visi & Misi',
            'slug' => 'visi-misi',
            'content' => 'Konten visi misi.',
            'status' => 'published',
            'placement' => 'sub_menu',
        ]);

        $subChildPage = Page::create([
            'website_id' => $this->siteA->id,
            'parent_id' => $childPage->id,
            'title' => 'Indikator Kinerja',
            'slug' => 'indikator-kinerja',
            'content' => 'Konten indikator.',
            'status' => 'published',
            'placement' => 'sub_menu',
        ]);

        // Verifikasi pada form Create
        $responseCreate = $this->actingAs($this->adminDinasA)->get(route('dinas.posts.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Tautkan ke Menu Header / Halaman Kedinasan:');
        $responseCreate->assertSee('[Menu Header] Profil Kedinasan');
        $responseCreate->assertSee('↳ [Sub-menu] Profil Kedinasan > Visi &amp; Misi', false);
        $responseCreate->assertSee('↳↳ [Wadah] Profil Kedinasan > Visi &amp; Misi > Indikator Kinerja', false);
        $responseCreate->assertDontSee('Menu header / halaman kedinasan saat ini masih kosong.');

        // Verifikasi backward compatibility pada form Edit (post legacy)
        $legacyPost = Post::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'title' => 'Post Warisan Lama',
            'slug' => 'post-warisan-lama',
            'type' => 'Berita',
            'placement' => 'header_berita',
            'content' => 'Konten dengan penempatan lama.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $responseEdit = $this->actingAs($this->adminDinasA)->get(route('dinas.posts.edit', $legacyPost));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Penempatan Sebelumnya (Legacy):');
        $responseEdit->assertSee('[Legacy] Berita Kedinasan');
    }

    public function test_post_attached_to_page_shows_in_table_badge_filter_and_frontend(): void
    {
        $page = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan Kesehatan Terpadu',
            'slug' => 'layanan-kesehatan-terpadu',
            'content' => 'Informasi layanan kesehatan.',
            'status' => 'published',
            'placement' => 'header_menu',
        ]);

        // 1. Simpan post tertaut page_id
        $storeResponse = $this->actingAs($this->adminDinasA)->post(route('dinas.posts.store'), [
            'title' => 'Standar Pelayanan Medis 2026',
            'type' => 'Pengumuman',
            'placement_target' => 'page_' . $page->id,
            'content' => 'Standar operasional prosedur pelayanan.',
            'status' => 'published',
        ]);
        $storeResponse->assertRedirect(route('dinas.posts.index'));

        $this->assertDatabaseHas('posts', [
            'website_id' => $this->siteA->id,
            'page_id' => $page->id,
            'placement' => 'sub_page',
            'title' => 'Standar Pelayanan Medis 2026',
        ]);

        // 2. Verifikasi badge dan filter di halaman index
        $indexResponse = $this->actingAs($this->adminDinasA)->get(route('dinas.posts.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('↳ Halaman: Layanan Kesehatan Terpadu');

        // Filter sub_page
        $filterSubResponse = $this->actingAs($this->adminDinasA)->get(route('dinas.posts.index', ['placement' => 'sub_page']));
        $filterSubResponse->assertStatus(200);
        $filterSubResponse->assertSee('Standar Pelayanan Medis 2026');

        // Filter beranda (tidak boleh melihat post sub_page)
        $filterBerandaResponse = $this->actingAs($this->adminDinasA)->get(route('dinas.posts.index', ['placement' => 'beranda']));
        $filterBerandaResponse->assertStatus(200);
        $filterBerandaResponse->assertDontSee('Standar Pelayanan Medis 2026');

        // 3. Verifikasi frontend
        $frontendResponse = $this->get(route('site.page.show', ['identifier' => $this->siteA->domain, 'slug' => $page->slug]));
        $frontendResponse->assertStatus(200);
        $frontendResponse->assertSee('Standar Pelayanan Medis 2026');
    }
}
