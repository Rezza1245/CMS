<?php

namespace Tests\Feature;

use App\Models\Dinas;
use App\Models\Media;
use App\Models\Page;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DinasPageTest extends TestCase
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
            'name' => 'Template Profil Kedinasan',
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
                        'id' => 'sec_static',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_static',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'static_comp',
                                        'component' => 'Static Content',
                                        'layout_settings' => [],
                                        'slots' => [
                                            'title' => ['slot_id' => 'static_title', 'binding' => 'pages.title'],
                                            'content' => ['slot_id' => 'static_content', 'binding' => 'pages.content'],
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
            'name' => 'Dinas Pendidikan Kota Batu',
            'code' => 'DISDIK',
        ]);
        $this->siteA = Website::create([
            'dinas_id' => $this->dinasA->id,
            'template_id' => $this->template->id,
            'domain' => 'disdik',
            'name' => 'Portal Disdik Batu',
            'status' => 'aktif',
        ]);
        $this->adminDinasA = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasA->id,
        ]);

        // Dinas B
        $this->dinasB = Dinas::create([
            'name' => 'Dinas Sosial Kota Batu',
            'code' => 'DINSOS',
        ]);
        $this->siteB = Website::create([
            'dinas_id' => $this->dinasB->id,
            'template_id' => $this->template->id,
            'domain' => 'dinsos',
            'name' => 'Portal Dinsos Batu',
            'status' => 'aktif',
        ]);
        $this->adminDinasB = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasB->id,
        ]);
    }

    public function test_admin_dinas_can_view_pages_index_with_tenant_isolation(): void
    {
        Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Profil Resmi Disdik',
            'slug' => 'profil-resmi-disdik',
            'content' => 'Visi misi pendidikan bermutu.',
            'status' => 'published',
        ]);

        Page::create([
            'website_id' => $this->siteB->id,
            'title' => 'Profil Rahasia Dinsos',
            'slug' => 'profil-rahasia-dinsos',
            'content' => 'Bantuan sosial terpadu.',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->adminDinasA)->get(route('dinas.pages.index'));

        $response->assertStatus(200);
        $response->assertSee('Profil Resmi Disdik');
        $response->assertDontSee('Profil Rahasia Dinsos');
    }

    public function test_admin_dinas_can_create_published_page_and_it_reflects_on_static_content(): void
    {
        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Visi & Misi Pendidikan Unggul Kota Batu',
            'content' => 'Mewujudkan generasi cerdas, berkarakter, dan berdaya saing global.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('dinas.pages.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'website_id' => $this->siteA->id,
            'title' => 'Visi & Misi Pendidikan Unggul Kota Batu',
            'slug' => 'visi-misi-pendidikan-unggul-kota-batu',
            'status' => 'published',
        ]);

        // Pastikan langsung muncul pada komponen Static Content di website publik
        $siteResponse = $this->get(route('site.show', 'disdik'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('Visi &amp; Misi Pendidikan Unggul Kota Batu', false);
        $siteResponse->assertSee('Mewujudkan generasi cerdas, berkarakter, dan berdaya saing global.');
    }

    public function test_draft_page_does_not_appear_on_public_website_static_content(): void
    {
        $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Draf Rencana Renstra Internal',
            'content' => 'Rencana internal belum dipublikasikan.',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('pages', [
            'website_id' => $this->siteA->id,
            'title' => 'Draf Rencana Renstra Internal',
            'status' => 'draft',
        ]);

        // Website publik tidak boleh merender konten draft
        $siteResponse = $this->get(route('site.show', 'disdik'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertDontSee('Draf Rencana Renstra Internal');
    }

    public function test_admin_dinas_can_update_page(): void
    {
        $page = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Profil Awal',
            'slug' => 'profil-awal',
            'content' => 'Isi profil awal.',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.pages.update', $page), [
            'title' => 'Profil Disdik Terkini 2026',
            'content' => 'Konten profil yang disempurnakan untuk publik.',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('dinas.pages.index'));
        $page->refresh();

        $this->assertEquals('Profil Disdik Terkini 2026', $page->title);
        $this->assertEquals('profil-disdik-terkini-2026', $page->slug);
        $this->assertEquals('Konten profil yang disempurnakan untuk publik.', $page->content);
        $this->assertEquals('published', $page->status);

        // Langsung tampil di live website
        $siteResponse = $this->get(route('site.show', 'disdik'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('Profil Disdik Terkini 2026');
        $siteResponse->assertSee('Konten profil yang disempurnakan untuk publik.');
    }

    public function test_admin_dinas_cannot_access_or_modify_other_dinas_page(): void
    {
        $pageB = Page::create([
            'website_id' => $this->siteB->id,
            'title' => 'Profil Rahasia Dinsos',
            'slug' => 'profil-rahasia-dinsos',
            'content' => 'Data sensitif.',
            'status' => 'published',
        ]);

        // Admin Dinas A mencoba edit page milik Dinas B
        $editResponse = $this->actingAs($this->adminDinasA)->get(route('dinas.pages.edit', $pageB));
        $editResponse->assertStatus(403);

        // Admin Dinas A mencoba update page milik Dinas B
        $updateResponse = $this->actingAs($this->adminDinasA)->put(route('dinas.pages.update', $pageB), [
            'title' => 'Injeksi Hacked',
            'content' => 'Hacked content.',
            'status' => 'published',
        ]);
        $updateResponse->assertStatus(403);

        // Admin Dinas A mencoba delete page milik Dinas B
        $deleteResponse = $this->actingAs($this->adminDinasA)->delete(route('dinas.pages.destroy', $pageB));
        $deleteResponse->assertStatus(403);

        // Pastikan record halaman B tetap utuh
        $this->assertDatabaseHas('pages', [
            'id' => $pageB->id,
            'title' => 'Profil Rahasia Dinsos',
        ]);
    }

    public function test_admin_dinas_can_delete_own_page(): void
    {
        $page = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Halaman Dihapus',
            'slug' => 'halaman-dihapus',
            'content' => 'Konten.',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->adminDinasA)->delete(route('dinas.pages.destroy', $page));

        $response->assertRedirect(route('dinas.pages.index'));
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_super_admin_cannot_access_dinas_pages(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('dinas.pages.index'));
        $response->assertStatus(403);
    }

    public function test_admin_dinas_can_create_sub_menu_and_sub_sub_menu_hierarchy(): void
    {
        // 1. Buat Menu Header (Level 1 Root)
        $rootResponse = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Profil Kedinasan',
            'content' => 'Profil umum dinas.',
            'status' => 'published',
            'parent_id' => null,
        ]);
        $rootResponse->assertRedirect(route('dinas.pages.index'));
        $root = Page::where('slug', 'profil-kedinasan')->firstOrFail();
        $this->assertNull($root->parent_id);

        // 2. Buat Sub-menu (Level 2)
        $subResponse = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Struktur Organisasi',
            'content' => 'Bagan organisasi kedinasan.',
            'status' => 'published',
            'parent_id' => $root->id,
        ]);
        $subResponse->assertRedirect(route('dinas.pages.index'));
        $sub = Page::where('slug', 'struktur-organisasi')->firstOrFail();
        $this->assertEquals($root->id, $sub->parent_id);

        // 3. Buat Sub-sub-menu (Level 3)
        $subSubResponse = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Pejabat Struktural',
            'content' => 'Daftar pejabat struktural dinas.',
            'status' => 'published',
            'parent_id' => $sub->id,
        ]);
        $subSubResponse->assertRedirect(route('dinas.pages.index'));
        $subSub = Page::where('slug', 'pejabat-struktural')->firstOrFail();
        $this->assertEquals($sub->id, $subSub->parent_id);
        $this->assertEquals(3, $subSub->getDepth());
    }

    public function test_admin_dinas_can_create_items_inside_sub_sub_bab_wadah(): void
    {
        $root = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Profil',
            'slug' => 'profil',
            'content' => 'Profil root',
            'status' => 'published',
            'parent_id' => null,
        ]);

        $sub = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan',
            'slug' => 'layanan',
            'content' => 'Sub menu layanan',
            'status' => 'published',
            'parent_id' => $root->id,
        ]);

        $wadah = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Aplikasi',
            'slug' => 'aplikasi',
            'content' => 'Daftar portal dan sistem aplikasi',
            'status' => 'published',
            'parent_id' => $sub->id,
        ]);

        // Upload item ke dalam wadah Aplikasi (Level 4)
        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Si Cantik Cloud',
            'content' => 'Aplikasi perizinan terpadu Kota Batu.',
            'status' => 'published',
            'placement_target' => 'parent_' . $wadah->id,
            'direct_link' => 'https://sicantik.layanan.go.id',
        ]);

        $response->assertRedirect(route('dinas.pages.index'));
        $item = Page::where('slug', 'si-cantik-cloud')->firstOrFail();
        $this->assertEquals($wadah->id, $item->parent_id);
        $this->assertEquals(4, $item->getDepth());
        $this->assertTrue($item->isContainerItem());
    }

    public function test_page_hierarchy_enforces_container_item_cannot_have_children(): void
    {
        $root = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Root Menu',
            'slug' => 'root-menu',
            'content' => 'Root',
            'status' => 'published',
            'parent_id' => null,
        ]);

        $level2 = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Level 2 Sub',
            'slug' => 'level-2-sub',
            'content' => 'Level 2',
            'status' => 'published',
            'parent_id' => $root->id,
        ]);

        $level3 = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Level 3 Wadah',
            'slug' => 'level-3-wadah',
            'content' => 'Level 3',
            'status' => 'published',
            'parent_id' => $level2->id,
        ]);

        $level4 = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Level 4 Item Wadah',
            'slug' => 'level-4-item-wadah',
            'content' => 'Level 4',
            'status' => 'published',
            'parent_id' => $level3->id,
        ]);

        // Coba membuat anak dari level 4 -> harus ditolak
        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Level 5 Ilegal',
            'content' => 'Konten level 5.',
            'status' => 'published',
            'parent_id' => $level4->id,
        ]);

        $response->assertSessionHasErrors(['parent_id']);
        $this->assertDatabaseMissing('pages', ['title' => 'Level 5 Ilegal']);
    }

    public function test_page_hierarchy_enforces_tenant_isolation_on_parent_id(): void
    {
        $pageB = Page::create([
            'website_id' => $this->siteB->id,
            'title' => 'Menu Dinsos',
            'slug' => 'menu-dinsos',
            'content' => 'Dinsos',
            'status' => 'published',
        ]);

        // Admin Dinas A mencoba menautkan halamannya ke induk milik Dinas B
        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Menu Disdik Pembajak',
            'content' => 'Coba bajak parent.',
            'status' => 'published',
            'parent_id' => $pageB->id,
        ]);

        $response->assertSessionHasErrors(['parent_id']);
        $this->assertDatabaseMissing('pages', ['title' => 'Menu Disdik Pembajak']);
    }

    public function test_page_update_prevents_circular_reference(): void
    {
        $pageA = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Halaman Utama',
            'slug' => 'halaman-utama',
            'content' => 'Utama',
            'status' => 'published',
        ]);

        $pageB = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Anak Halaman',
            'slug' => 'anak-halaman',
            'content' => 'Anak',
            'status' => 'published',
            'parent_id' => $pageA->id,
        ]);

        // 1. Update diri sendiri sebagai parent -> tolak
        $selfResponse = $this->actingAs($this->adminDinasA)->put(route('dinas.pages.update', $pageA), [
            'title' => 'Halaman Utama',
            'content' => 'Utama',
            'status' => 'published',
            'parent_id' => $pageA->id,
        ]);
        $selfResponse->assertSessionHasErrors(['parent_id']);

        // 2. Update parent menjadi anak dari halamannya sendiri -> tolak
        $circularResponse = $this->actingAs($this->adminDinasA)->put(route('dinas.pages.update', $pageA), [
            'title' => 'Halaman Utama',
            'content' => 'Utama',
            'status' => 'published',
            'parent_id' => $pageB->id,
        ]);
        $circularResponse->assertSessionHasErrors(['parent_id']);
    }

    public function test_admin_dinas_can_create_pages_with_different_placements(): void
    {
        // 1. Tampil di beranda (default)
        $resp1 = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Tugas Pokok dan Fungsi',
            'content' => 'Tupoksi dinas pendidikan.',
            'status' => 'published',
            'placement_target' => 'beranda',
        ]);
        $resp1->assertRedirect(route('dinas.pages.index'));
        $page1 = Page::where('slug', 'tugas-pokok-dan-fungsi')->firstOrFail();
        $this->assertEquals('beranda', $page1->placement);
        $this->assertNull($page1->parent_id);

        // 2. Sub-menu di bawah Profil
        $resp2 = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Visi Misi Profil',
            'content' => 'Visi misi khusus menu profil.',
            'status' => 'published',
            'placement_target' => 'profil',
        ]);
        $resp2->assertRedirect(route('dinas.pages.index'));
        $page2 = Page::where('slug', 'visi-misi-profil')->firstOrFail();
        $this->assertEquals('profil', $page2->placement);
        $this->assertNull($page2->parent_id);

        // 3. Menu utama baru di header
        $resp3 = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Layanan Publik Mandiri',
            'content' => 'Layanan publik baru.',
            'status' => 'published',
            'placement_target' => 'header_menu',
        ]);
        $resp3->assertRedirect(route('dinas.pages.index'));
        $page3 = Page::where('slug', 'layanan-publik-mandiri')->firstOrFail();
        $this->assertEquals('header_menu', $page3->placement);
        $this->assertNull($page3->parent_id);

        // 4. Sub-menu dari halaman lain
        $resp4 = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'SOP Layanan',
            'content' => 'SOP.',
            'status' => 'published',
            'placement_target' => 'parent_' . $page3->id,
        ]);
        $resp4->assertRedirect(route('dinas.pages.index'));
        $page4 = Page::where('slug', 'sop-layanan')->firstOrFail();
        $this->assertEquals('sub_menu', $page4->placement);
        $this->assertEquals($page3->id, $page4->parent_id);
    }

    public function test_admin_dinas_can_upload_page_image_and_it_records_to_media_table(): void
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->create('banner-profil.jpg', 1200, 'image/jpeg');

        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Struktur Lengkap Dinas',
            'content' => 'Bagan susunan pejabat.',
            'status' => 'published',
            'placement_target' => 'profil',
            'image_file' => $imageFile,
        ]);

        $response->assertRedirect(route('dinas.pages.index'));

        $page = Page::where('slug', 'struktur-lengkap-dinas')->firstOrFail();
        $this->assertNotNull($page->image);
        Storage::disk('public')->assertExists($page->image);

        // Pastikan otomatis tercatat di tabel media (SCHEMA 4.9 & PRD 4.4)
        $this->assertDatabaseHas('media', [
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'banner-profil.jpg',
            'file_path' => $page->image,
        ]);
    }

    public function test_admin_dinas_can_create_page_with_direct_link(): void
    {
        $parent = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan Publik',
            'slug' => 'layanan-publik',
            'content' => 'Induk menu.',
            'status' => 'published',
            'placement' => 'header_menu',
        ]);

        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Portal Layanan Terpadu Batu',
            'content' => 'Tautan langsung ke sistem layanan terpadu.',
            'status' => 'published',
            'placement_target' => 'parent_' . $parent->id,
            'direct_link' => 'https://layanan.batukota.go.id',
        ]);

        $response->assertRedirect(route('dinas.pages.index'));

        $this->assertDatabaseHas('pages', [
            'website_id' => $this->siteA->id,
            'title' => 'Portal Layanan Terpadu Batu',
            'direct_link' => 'https://layanan.batukota.go.id',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_header_menu_page_ignores_image_and_direct_link(): void
    {
        Storage::fake('public');
        $imageFile = UploadedFile::fake()->create('banner.jpg', 500, 'image/jpeg');

        // Saat membuat halaman Menu Utama Header (header_menu), direct_link dan gambar diabaikan/null
        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Menu Utama Tanpa Gambar',
            'content' => 'Konten menu utama.',
            'status' => 'published',
            'placement_target' => 'header_menu',
            'direct_link' => 'https://example.com/ignored',
            'image_file' => $imageFile,
        ]);

        $response->assertRedirect(route('dinas.pages.index'));

        $page = Page::where('slug', 'menu-utama-tanpa-gambar')->firstOrFail();
        $this->assertEquals('header_menu', $page->placement);
        $this->assertNull($page->direct_link);
        $this->assertNull($page->image);
    }

    public function test_updating_page_to_header_menu_clears_image_and_direct_link(): void
    {
        Storage::fake('public');
        $imagePath = 'pages/existing-banner.webp';
        Storage::disk('public')->put($imagePath, 'dummy image content');

        $parent = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Induk Menu',
            'slug' => 'induk-menu',
            'content' => 'Konten induk.',
            'status' => 'published',
            'placement' => 'header_menu',
        ]);

        $subPage = Page::create([
            'website_id' => $this->siteA->id,
            'parent_id' => $parent->id,
            'title' => 'Sub Menu Awal',
            'slug' => 'sub-menu-awal',
            'content' => 'Konten sub menu.',
            'status' => 'published',
            'placement' => 'sub_menu',
            'direct_link' => 'https://example.com/sub',
            'image' => $imagePath,
        ]);

        // Update sub page menjadi Menu Utama Header (header_menu)
        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.pages.update', $subPage), [
            'title' => 'Menu Utama Promosi',
            'content' => 'Konten menu utama baru.',
            'status' => 'published',
            'placement_target' => 'header_menu',
            'direct_link' => 'https://example.com/sub',
        ]);

        $response->assertRedirect(route('dinas.pages.index'));

        $subPage->refresh();
        $this->assertEquals('header_menu', $subPage->placement);
        $this->assertNull($subPage->parent_id);
        $this->assertNull($subPage->direct_link);
        $this->assertNull($subPage->image);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_admin_dinas_can_create_page_with_kontak_placement_and_various_direct_links(): void
    {
        // 1. Buat kontak Instagram (direct link URL)
        $respIg = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Instagram Resmi',
            'content' => 'Akun sosial media resmi dinas.',
            'status' => 'published',
            'placement_target' => 'kontak',
            'direct_link' => 'https://instagram.com/diskominfo_batu',
        ]);
        $respIg->assertRedirect(route('dinas.pages.index'));

        // 2. Buat kontak email pengaduan (direct link mailto)
        $respMail = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Email Pengaduan',
            'content' => 'Layanan aduan masyarakat melalui email.',
            'status' => 'published',
            'placement_target' => 'kontak',
            'direct_link' => 'mailto:pengaduan@batukota.go.id',
        ]);
        $respMail->assertRedirect(route('dinas.pages.index'));

        // 3. Buat kontak halaman statis lokasi kantor
        $respLoc = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Lokasi Kantor',
            'content' => 'Balaikota Among Tani Gedung B Lantai 2 Kota Batu.',
            'status' => 'published',
            'placement_target' => 'kontak',
        ]);
        $respLoc->assertRedirect(route('dinas.pages.index'));

        $this->assertDatabaseHas('pages', [
            'website_id' => $this->siteA->id,
            'title' => 'Instagram Resmi',
            'placement' => 'kontak',
        ]);

        // Verifikasi render pada website publik: muncul dropdown Kontak di navbar atas
        $siteResponse = $this->get(route('site.show', $this->siteA->domain));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('Instagram Resmi');
        $siteResponse->assertSee('https://instagram.com/diskominfo_batu');
        $siteResponse->assertSee('Email Pengaduan');
        $siteResponse->assertSee('mailto:pengaduan@batukota.go.id');
        $siteResponse->assertSee('Lokasi Kantor');
    }

    public function test_admin_dinas_can_create_page_with_specific_header_sub_menu_placements(): void
    {
        // 1. Target spesifik Visi & Misi di Profil
        $respVisi = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Visi & Misi Disdik',
            'content' => 'Visi misi resmi dinas pendidikan.',
            'status' => 'published',
            'placement_target' => 'profil_visi_misi',
        ]);
        $respVisi->assertRedirect(route('dinas.pages.index'));

        // 2. Target spesifik Persyaratan di Layanan
        $respSyarat = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Persyaratan Beasiswa Berprestasi',
            'content' => 'Syarat pendaftaran beasiswa.',
            'status' => 'published',
            'placement_target' => 'layanan_persyaratan',
        ]);
        $respSyarat->assertRedirect(route('dinas.pages.index'));

        // 3. Target spesifik Peraturan di Publikasi
        $respPeraturan = $this->actingAs($this->adminDinasA)->post(route('dinas.pages.store'), [
            'title' => 'Peraturan Walikota Tentang Kurikulum Muatan Lokal',
            'content' => 'Perwali no 12 tahun 2026.',
            'status' => 'published',
            'placement_target' => 'publikasi_peraturan',
        ]);
        $respPeraturan->assertRedirect(route('dinas.pages.index'));

        $this->assertDatabaseHas('pages', [
            'website_id' => $this->siteA->id,
            'title' => 'Visi & Misi Disdik',
            'placement' => 'profil_visi_misi',
        ]);

        $this->assertDatabaseHas('pages', [
            'website_id' => $this->siteA->id,
            'title' => 'Persyaratan Beasiswa Berprestasi',
            'placement' => 'layanan_persyaratan',
        ]);

        $this->assertDatabaseHas('pages', [
            'website_id' => $this->siteA->id,
            'title' => 'Peraturan Walikota Tentang Kurikulum Muatan Lokal',
            'placement' => 'publikasi_peraturan',
        ]);

        // Verifikasi smart mapping di navbar publik
        $siteResponse = $this->get(route('site.show', $this->siteA->domain));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee(route('site.page.show', ['identifier' => $this->siteA->domain, 'slug' => 'visi-misi-disdik']));
        $siteResponse->assertSee(route('site.page.show', ['identifier' => $this->siteA->domain, 'slug' => 'persyaratan-beasiswa-berprestasi']));
        $siteResponse->assertSee(route('site.page.show', ['identifier' => $this->siteA->domain, 'slug' => 'peraturan-walikota-tentang-kurikulum-muatan-lokal']));
    }

    public function test_create_page_view_renders_standard_menu_presets_and_navbar_renders_beranda_with_dynamic_hierarchy(): void
    {
        // 1. Verifikasi form create menampilkan opsi preset standar (Opsi B)
        $formResponse = $this->actingAs($this->adminDinasA)->get(route('dinas.pages.create'));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Preset Rekomendasi Menu Standar (Diskominfo)');
        $formResponse->assertSee('Profil Kedinasan (Menu Utama)');
        $formResponse->assertSee('Visi & Misi', false);
        $formResponse->assertSee('Daftar Layanan Publik');

        // 2. Buat Menu Utama (Level 1)
        $rootMenu = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Profil Kedinasan',
            'slug' => 'profil-kedinasan',
            'content' => 'Profil resmi dinas.',
            'status' => 'published',
            'placement' => 'header_menu',
        ]);

        // 3. Buat Sub-bab (Level 2)
        $subBab = Page::create([
            'website_id' => $this->siteA->id,
            'parent_id' => $rootMenu->id,
            'title' => 'Struktur Organisasi',
            'slug' => 'struktur-organisasi',
            'content' => 'Struktur pejabat dinas.',
            'status' => 'published',
            'placement' => 'sub_menu',
        ]);

        // 4. Buat Sub-sub-bab (Level 3)
        $subSubBab = Page::create([
            'website_id' => $this->siteA->id,
            'parent_id' => $subBab->id,
            'title' => 'Bidang Pembinaan SMP',
            'slug' => 'bidang-pembinaan-smp',
            'content' => 'Informasi bidang pembinaan SMP.',
            'status' => 'published',
            'placement' => 'sub_menu',
        ]);

        // 5. Cek tampilan navbar publik
        $siteResponse = $this->get(route('site.show', $this->siteA->domain));
        $siteResponse->assertStatus(200);

        // Hanya Beranda dan menu dinamis yang ada di navbar
        $siteResponse->assertSee('Beranda');
        $siteResponse->assertSee('Profil Kedinasan');
        $siteResponse->assertSee('Struktur Organisasi');
        $siteResponse->assertSee('Bidang Pembinaan SMP');
    }

    public function test_pages_index_orders_beranda_first_followed_by_menu_utama_and_keeps_sub_menu_draft_above_sub_sub_menu(): void
    {
        // 1. Menu Utama Profil
        $rootProfil = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Menu Profil Kedinasan',
            'slug' => 'menu-profil-kedinasan',
            'content' => 'Profil instansi.',
            'status' => 'published',
            'placement' => 'header_menu',
            'parent_id' => null,
        ]);

        // 2. Sub Menu 1 (Publish) di bawah Profil
        $subSejarah = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Sub Menu Sejarah Lembaga',
            'slug' => 'sub-menu-sejarah-lembaga',
            'content' => 'Sejarah dinas.',
            'status' => 'published',
            'placement' => 'sub_menu',
            'parent_id' => $rootProfil->id,
        ]);

        // 3. Sub-sub Menu 1.1 (Publish) di bawah Sejarah
        $subSubSejarah = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Sub Sub Menu Periode 2020',
            'slug' => 'sub-sub-menu-periode-2020',
            'content' => 'Periode 2020.',
            'status' => 'published',
            'placement' => 'sub_menu',
            'parent_id' => $subSejarah->id,
        ]);

        // 4. Sub Menu 2 (Draft) di bawah Profil
        $subVisi = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Sub Menu Visi Misi Strategis',
            'slug' => 'sub-menu-visi-misi-strategis',
            'content' => 'Visi misi dinas.',
            'status' => 'draft',
            'placement' => 'sub_menu',
            'parent_id' => $rootProfil->id,
        ]);

        // 5. Sub-sub Menu 2.1 (Draft) di bawah Visi
        $subSubVisi = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Sub Sub Menu Rencana Aksi 2025',
            'slug' => 'sub-sub-menu-rencana-aksi-2025',
            'content' => 'Rencana aksi.',
            'status' => 'draft',
            'placement' => 'sub_menu',
            'parent_id' => $subVisi->id,
        ]);

        // 6. Tab Beranda (dibuat belakangan, tapi harus selalu di awal halaman)
        $tabBeranda = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Halaman Tab Beranda Utama',
            'slug' => 'halaman-tab-beranda-utama',
            'content' => 'Konten profil beranda.',
            'status' => 'published',
            'placement' => 'beranda',
            'parent_id' => null,
        ]);

        // 7. Menu Utama Layanan
        $rootLayanan = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Menu Layanan Publik Terpadu',
            'slug' => 'menu-layanan-publik-terpadu',
            'content' => 'Layanan dinas.',
            'status' => 'published',
            'placement' => 'header_menu',
            'parent_id' => null,
        ]);

        // 8. Sub Menu Layanan (Publish)
        $subIzin = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Sub Menu Pengajuan Izin Sekolah',
            'slug' => 'sub-menu-pengajuan-izin-sekolah',
            'content' => 'Izin sekolah.',
            'status' => 'published',
            'placement' => 'sub_menu',
            'parent_id' => $rootLayanan->id,
        ]);

        $response = $this->actingAs($this->adminDinasA)->get(route('dinas.pages.index'));
        $response->assertStatus(200);

        // Pastikan urutan:
        // 1. Tab Beranda selalu di paling awal
        // 2. Menu Utama Profil
        // 3. Sub Menu Sejarah (Publish)
        // 4. Sub Menu Visi Misi (Draft) — TETAP DI ATAS DARI SUB-SUB MENU
        // 5. Sub Sub Menu Periode 2020 (Publish)
        // 6. Sub Sub Menu Rencana Aksi 2025 (Draft)
        // 7. Menu Utama Layanan
        // 8. Sub Menu Pengajuan Izin Sekolah (Publish)
        $response->assertSeeInOrder([
            'Halaman Tab Beranda Utama',
            'Menu Profil Kedinasan',
            'Sub Menu Sejarah Lembaga',
            'Sub Menu Visi Misi Strategis',
            'Sub Sub Menu Periode 2020',
            'Sub Sub Menu Rencana Aksi 2025',
            'Menu Layanan Publik Terpadu',
            'Sub Menu Pengajuan Izin Sekolah',
        ]);
    }
}
