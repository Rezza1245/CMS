<?php

namespace Tests\Feature;

use App\Models\Dinas;
use App\Models\Page;
use App\Models\Post;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsitePageTest extends TestCase
{
    use RefreshDatabase;

    protected Template $template;

    protected Dinas $dinasA;

    protected Website $siteA;

    protected Page $pageProfilA;

    protected Page $pageVisiMisiA;

    protected Page $pageDraftA;

    protected Dinas $dinasB;

    protected Website $siteB;

    protected Page $pageProfilB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = Template::create([
            'name' => 'Template Portal Resmi',
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
            'name' => 'Dinas Komunikasi dan Informatika',
            'code' => 'DISKOMINFO',
            'address' => 'Balai Kota Among Tani Gedung B',
        ]);
        $this->siteA = Website::create([
            'dinas_id' => $this->dinasA->id,
            'template_id' => $this->template->id,
            'domain' => 'diskominfo',
            'name' => 'Portal Resmi Diskominfo',
            'status' => 'aktif',
        ]);

        $this->pageProfilA = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Profil Lengkap Diskominfo Kota Batu',
            'slug' => 'profil-diskominfo',
            'content' => 'Diskominfo bertugas mengelola infrastruktur TI, persandian, dan keterbukaan informasi.',
            'status' => 'published',
            'placement' => 'profil',
        ]);

        $this->pageVisiMisiA = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Visi & Misi Diskominfo',
            'slug' => 'visi-misi',
            'content' => 'Visi: Terwujudnya tata kelola pemerintahan digital yang terpercaya.',
            'status' => 'published',
            'placement' => 'profil',
        ]);

        $this->pageDraftA = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Rencana Rahasia 2030',
            'slug' => 'rencana-rahasia-2030',
            'content' => 'Dokumen draf internal.',
            'status' => 'draft',
        ]);

        // Dinas B
        $this->dinasB = Dinas::create([
            'name' => 'Dinas Kesehatan Kota Batu',
            'code' => 'DINKES',
        ]);
        $this->siteB = Website::create([
            'dinas_id' => $this->dinasB->id,
            'template_id' => $this->template->id,
            'domain' => 'dinkes',
            'name' => 'Portal Resmi Dinkes',
            'status' => 'aktif',
        ]);

        $this->pageProfilB = Page::create([
            'website_id' => $this->siteB->id,
            'title' => 'Profil Kesehatan Masyarakat',
            'slug' => 'profil-kesehatan',
            'content' => 'Layanan kesehatan paripurna.',
            'status' => 'published',
        ]);
    }

    public function test_public_user_can_access_published_static_page(): void
    {
        $response = $this->get(route('site.page.show', [
            'identifier' => 'diskominfo',
            'slug' => 'profil-diskominfo',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Profil Lengkap Diskominfo Kota Batu');
        $response->assertSee('Diskominfo bertugas mengelola infrastruktur TI');
        $response->assertSee('Balai Kota Among Tani Gedung B');
        // Terdapat tautan ke halaman profil lainnya
        $response->assertSee('Visi &amp; Misi Diskominfo', false);
    }

    public function test_public_user_cannot_access_draft_static_page(): void
    {
        $response = $this->get(route('site.page.show', [
            'identifier' => 'diskominfo',
            'slug' => 'rencana-rahasia-2030',
        ]));

        $response->assertStatus(404);
    }

    public function test_tenant_isolation_on_public_page_route(): void
    {
        // Mencoba membuka slug milik Dinkes (Dinas B) menggunakan domain Diskominfo (Dinas A)
        $response = $this->get(route('site.page.show', [
            'identifier' => 'diskominfo',
            'slug' => 'profil-kesehatan',
        ]));

        $response->assertStatus(404);
    }

    public function test_navbar_contains_profil_dropdown_with_all_published_pages(): void
    {
        // Akses homepage Diskominfo
        $response = $this->get(route('site.show', 'diskominfo'));

        $response->assertStatus(200);
        // Menu Profil ada di navbar
        $response->assertSee('Profil');
        // Menampilkan link kedua halaman yang dipublikasikan
        $response->assertSee(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'profil-diskominfo']));
        $response->assertSee(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'visi-misi']));
        // Draft tidak boleh muncul di dropdown navbar
        $response->assertDontSee('Rencana Rahasia 2030');
    }

    public function test_homepage_renders_profil_ringkas_with_cta_and_related_page_pills(): void
    {
        $response = $this->get(route('site.show', 'diskominfo'));

        $response->assertStatus(200);
        // Header seksi Profil Ringkas
        $response->assertSee('Profil Ringkas');
        // Tombol CTA menuju halaman lengkap
        $response->assertSee('Baca Halaman Selengkapnya');
        $response->assertSee(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'profil-diskominfo']));
        // Terdapat pill tautan ke halaman statis lainnya
        $response->assertSee('Halaman Lainnya:');
        $response->assertSee('Visi &amp; Misi Diskominfo', false);
    }

    public function test_navbar_renders_nested_sub_menus_and_sub_sub_menus_for_all_headers(): void
    {
        // Buat menu bertingkat di bawah menu baru "Layanan Publik"
        $layananRoot = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan Publik',
            'slug' => 'layanan-publik',
            'content' => 'Pusat layanan informasi publik.',
            'status' => 'published',
            'placement' => 'header_menu',
            'parent_id' => null,
        ]);

        $pengaduanSub = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan Pengaduan',
            'slug' => 'layanan-pengaduan',
            'content' => 'Kanal aspirasi warga.',
            'status' => 'published',
            'parent_id' => $layananRoot->id,
        ]);

        $sp4nSubSub = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Integrasi SP4N LAPOR',
            'slug' => 'integrasi-sp4n-lapor',
            'content' => 'Layanan nasional pengaduan terpadu.',
            'status' => 'published',
            'parent_id' => $pengaduanSub->id,
        ]);

        $response = $this->get(route('site.show', 'diskominfo'));

        $response->assertStatus(200);

        // Menu Utama Layanan Publik muncul di navbar
        $response->assertSee('Layanan Publik');

        // Sub-menu level 2 muncul
        $response->assertSee('Layanan Pengaduan');
        $response->assertSee(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'layanan-pengaduan']));

        // Sub-sub-menu level 3 muncul
        $response->assertSee('Integrasi SP4N LAPOR');
        $response->assertSee(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'integrasi-sp4n-lapor']));
    }

    public function test_beranda_placement_renders_tabs_on_home_and_not_in_navbar_header(): void
    {
        // 1. Buat 2 halaman khusus beranda
        $tab1 = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Visi Misi 2026',
            'slug' => 'visi-misi-2026',
            'content' => 'Teks visi misi tahun 2026.',
            'status' => 'published',
            'placement' => 'beranda',
        ]);

        $tab2 = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Tugas & Fungsi Kedinasan',
            'slug' => 'tupoksi-kedinasan',
            'content' => 'Teks tugas dan fungsi terpadu.',
            'status' => 'published',
            'placement' => 'beranda',
        ]);

        $response = $this->get(route('site.show', 'diskominfo'));

        $response->assertStatus(200);

        // Kedua tab muncul di dalam card seksi Static Content di beranda
        $response->assertSee('Visi Misi 2026');
        $response->assertSee('Tugas &amp; Fungsi Kedinasan', false);
        $response->assertSee('static-tab-btn', false);

        // Halaman penempatan 'beranda' TIDAK menambah menu baru di bar navigasi atas
        $response->assertDontSee('<span class="font-bold">Visi Misi 2026</span>', false);
    }

    public function test_page_with_direct_link_renders_external_url_in_navbar_and_cta(): void
    {
        $pageWithLink = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Dokumen SOP Google Drive',
            'slug' => 'dokumen-sop-google-drive',
            'content' => 'Silakan unduh dokumen SOP resmi melalui Google Drive.',
            'status' => 'published',
            'placement' => 'profil',
            'direct_link' => 'https://drive.google.com/drive/folders/sample-sop-folder',
        ]);

        $homeResponse = $this->get(route('site.show', 'diskominfo'));
        $homeResponse->assertStatus(200);

        // Navbar dropdown Profil memiliki tautan ke Google Drive
        $homeResponse->assertSee('https://drive.google.com/drive/folders/sample-sop-folder');

        // Pada detail halaman statis, muncul banner CTA unduhan dokumen
        $pageResponse = $this->get(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'dokumen-sop-google-drive']));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Dokumen Lampiran / Tautan Terkait');
        $pageResponse->assertSee('https://drive.google.com/drive/folders/sample-sop-folder');
        $pageResponse->assertSee('Unduh / Buka Dokumen');
    }

    public function test_sub_sub_bab_serves_as_wadah_and_renders_all_child_items(): void
    {
        $root = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Profil Kedinasan',
            'slug' => 'profil-kedinasan',
            'content' => 'Profil institusi.',
            'status' => 'published',
            'placement' => 'profil',
            'parent_id' => null,
        ]);

        $layanan = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan Publik',
            'slug' => 'layanan-publik',
            'content' => 'Sub-menu layanan.',
            'status' => 'published',
            'parent_id' => $root->id,
        ]);

        $aplikasi = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Aplikasi Terpadu',
            'slug' => 'aplikasi-terpadu',
            'content' => 'Wadah direktori aplikasi resmi pemerintah.',
            'status' => 'published',
            'parent_id' => $layanan->id,
        ]);

        // 2 items inside Aplikasi (wadah)
        $item1 = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Si Cantik Cloud Kota Batu',
            'slug' => 'si-cantik-cloud',
            'content' => 'Sistem perizinan berusaha dan non-berusaha.',
            'status' => 'published',
            'parent_id' => $aplikasi->id,
            'direct_link' => 'https://sicantik.batukota.go.id',
        ]);

        $item2 = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'E-Office Persuratan',
            'slug' => 'e-office-persuratan',
            'content' => 'Tata naskah dinas elektronik terintegrasi.',
            'status' => 'published',
            'parent_id' => $aplikasi->id,
            'direct_link' => 'https://eoffice.batukota.go.id',
        ]);

        // 1. Kunjungi halaman wadah: Aplikasi Terpadu
        $response = $this->get(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'aplikasi-terpadu']));

        $response->assertStatus(200);
        $response->assertSee('Aplikasi Terpadu');
        $response->assertSee('Daftar Aplikasi Terpadu');
        $response->assertSee('2 Bagian Tersedia');
        $response->assertSee('Si Cantik Cloud Kota Batu');
        $response->assertSee('Sistem perizinan berusaha dan non-berusaha.');
        $response->assertSee('https://sicantik.batukota.go.id');
        $response->assertSee('E-Office Persuratan');
        $response->assertSee('Tata naskah dinas elektronik terintegrasi.');
        $response->assertSee('https://eoffice.batukota.go.id');

        // 2. Kunjungi item wadah secara langsung -> redirect ke wadah Aplikasi Terpadu
        $itemResponse = $this->get(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'si-cantik-cloud']));
        $itemResponse->assertRedirect(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'aplikasi-terpadu']));
    }

    public function test_navbar_profil_renders_down_to_sub_sub_bab_level_3(): void
    {
        $root = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Profil Daerah',
            'slug' => 'profil-daerah',
            'content' => 'Profil daerah.',
            'status' => 'published',
            'placement' => 'profil',
            'parent_id' => null,
        ]);

        $sub = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan TI',
            'slug' => 'layanan-ti',
            'content' => 'Layanan TI.',
            'status' => 'published',
            'parent_id' => $root->id,
        ]);

        $subSub = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Aplikasi Layanan',
            'slug' => 'aplikasi-layanan',
            'content' => 'Wadah aplikasi.',
            'status' => 'published',
            'parent_id' => $sub->id,
        ]);

        $response = $this->get(route('site.show', 'diskominfo'));

        $response->assertStatus(200);
        $response->assertSee('Profil Daerah');
        $response->assertSee('Layanan TI');
        $response->assertSee('Aplikasi Layanan');
        $response->assertSee(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'aplikasi-layanan']));
    }

    public function test_navbar_renders_beranda_and_dynamic_pages_with_sub_menus_correctly(): void
    {
        // 1. Buat menu utama dan sub-menu kustom
        $layananMenu = Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'Layanan Perizinan',
            'slug' => 'layanan-perizinan',
            'content' => 'Layanan perizinan terpadu dinas.',
            'status' => 'published',
            'placement' => 'header_menu',
        ]);

        Page::create([
            'website_id' => $this->siteA->id,
            'title' => 'SOP Pengajuan Rekomendasi Jaringan',
            'slug' => 'sop-pengajuan-rekomendasi-jaringan',
            'content' => 'Alur dan persyaratan pengajuan.',
            'status' => 'published',
            'placement' => 'sub_menu',
            'parent_id' => $layananMenu->id,
        ]);

        $response = $this->get(route('site.show', 'diskominfo'));

        $response->assertStatus(200);

        // Memastikan Beranda tampil di Navbar
        $response->assertSee('Beranda');

        // Memastikan Menu Utama dan Sub-menu tampil
        $response->assertSee('Layanan Perizinan');
        $response->assertSee('SOP Pengajuan Rekomendasi Jaringan');
    }

    public function test_new_website_without_pages_renders_clean_placeholder_space_without_dummy_diskominfo_text(): void
    {
        $newDinas = Dinas::create([
            'name' => 'Dinas Koperasi dan UMKM',
            'code' => 'DISKOP',
        ]);

        $newSite = Website::create([
            'dinas_id' => $newDinas->id,
            'template_id' => $this->template->id,
            'domain' => 'diskop',
            'name' => 'Website Resmi Dinas Koperasi',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('site.show', 'diskop'));
        $response->assertStatus(200);

        // Tidak boleh menampilkan teks hardcode Diskominfo
        $response->assertDontSee('Dinas Komunikasi dan Informatika Kota Batu berkomitmen');

        // Harus menampilkan ruang placeholder untuk static content
        $response->assertSee('Ruang Konten Profil &amp; Informasi Kedinasan', false);
        $response->assertSee('Dinas Koperasi dan UMKM');
    }

    public function test_static_content_with_template_controlled_custom_default_renders_on_website(): void
    {
        $dinasPora = Dinas::create([
            'name' => 'Dinas Pemuda dan Olahraga',
            'code' => 'DISPORA',
        ]);

        $customTpl = Template::create([
            'name' => 'Template Khusus Dispora',
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
                        'id' => 'sec_sc',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_sc',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'sc_01',
                                        'component' => 'Static Content',
                                        'layout_settings' => [],
                                        'slots' => [
                                            'title' => [
                                                'slot_id' => 'sc_title',
                                                'binding' => 'pages.title',
                                                'default_value' => 'Visi Prestasi Olahraga Kota Batu',
                                            ],
                                            'content' => [
                                                'slot_id' => 'sc_content',
                                                'binding' => 'pages.content',
                                                'default_value' => 'Mewujudkan atlet berprestasi dan generasi muda berdaya saing tinggi.',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $siteDispora = Website::create([
            'dinas_id' => $dinasPora->id,
            'template_id' => $customTpl->id,
            'domain' => 'dispora',
            'name' => 'Portal Dispora Batu',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('site.show', 'dispora'));
        $response->assertStatus(200);

        // Menampilkan nilai default template terkustomisasi
        $response->assertSee('Visi Prestasi Olahraga Kota Batu');
        $response->assertSee('Mewujudkan atlet berprestasi dan generasi muda berdaya saing tinggi.');
    }

    public function test_hero_hubungi_kami_button_is_templated_and_links_to_dinas_whatsapp(): void
    {
        $heroTemplate = Template::create([
            'name' => 'Template Hero WhatsApp',
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
                        'id' => 'sec_hero',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_hero',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'hero_comp',
                                        'component' => 'Hero',
                                        'slots' => [
                                            'title' => ['slot_id' => 'hero_title', 'binding' => 'appearance.header_slogan'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // 1. Setup dinas dengan nomor telepon format lokal (0812...)
        $dinasWa = Dinas::create([
            'name' => 'Dinas Lingkungan Hidup',
            'code' => 'DLH',
            'phone' => '0812-3456-7890',
            'contact_email' => 'dlh@batukota.go.id',
        ]);

        $siteWa = Website::create([
            'dinas_id' => $dinasWa->id,
            'template_id' => $heroTemplate->id,
            'domain' => 'dlh',
            'name' => 'Portal Resmi DLH Kota Batu',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('site.show', 'dlh'));
        $response->assertStatus(200);

        // Verifikasi tombol Hubungi Kami selalu ada di template
        $response->assertSee('Hubungi Kami');

        // Verifikasi tautan WhatsApp berawalan 62 dan dinormalisasi
        $response->assertSee('https://wa.me/6281234567890', false);

        // 2. Setup dinas dengan nomor telepon format landline / area (0341)
        $dinasLandline = Dinas::create([
            'name' => 'Dinas Perhubungan',
            'code' => 'DISHUB',
            'phone' => '(0341) 591036',
        ]);

        $siteLandline = Website::create([
            'dinas_id' => $dinasLandline->id,
            'template_id' => $heroTemplate->id,
            'domain' => 'dishub',
            'name' => 'Portal Dishub Batu',
            'status' => 'aktif',
        ]);

        $respLandline = $this->get(route('site.show', 'dishub'));
        $respLandline->assertStatus(200);
        $respLandline->assertSee('Hubungi Kami');
        $respLandline->assertSee('https://wa.me/62341591036', false);
    }

    public function test_public_cannot_access_website_in_maintenance_mode(): void
    {
        $this->siteA->update(['status' => 'pemeliharaan']);

        $author = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasA->id,
        ]);

        $post = Post::create([
            'website_id' => $this->siteA->id,
            'user_id' => $author->id,
            'title' => 'Berita Diskominfo 1',
            'slug' => 'berita-diskominfo-1',
            'content' => 'Konten berita.',
            'status' => 'published',
            'type' => 'berita',
        ]);

        // 1. Akses halaman index publik dinas diblokir (503)
        $respIndex = $this->get(route('site.show', 'diskominfo'));
        $respIndex->assertStatus(503);
        $respIndex->assertSee('Website Sedang Dalam Pemeliharaan');
        $respIndex->assertSee('Dinas Komunikasi dan Informatika');
        $respIndex->assertDontSee('CMS Login');

        // 2. Akses halaman statis diblokir (503)
        $respPage = $this->get(route('site.page.show', ['identifier' => 'diskominfo', 'slug' => 'profil-diskominfo']));
        $respPage->assertStatus(503);
        $respPage->assertSee('Website Sedang Dalam Pemeliharaan');

        // 3. Akses detail post berita diblokir (503)
        $respPost = $this->get(route('site.post.show', ['identifier' => 'diskominfo', 'slug' => 'berita-diskominfo-1']));
        $respPost->assertStatus(503);
        $respPost->assertSee('Website Sedang Dalam Pemeliharaan');
    }

    public function test_internal_admin_can_preview_website_in_maintenance_mode(): void
    {
        $this->siteA->update(['status' => 'pemeliharaan']);

        $adminA = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasA->id,
        ]);

        $adminB = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasB->id,
        ]);

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        // 1. Admin dinas pemilik dapat melakukan preview (200) dengan banner pemeliharaan
        $respAdminA = $this->actingAs($adminA)->get(route('site.show', 'diskominfo'));
        $respAdminA->assertStatus(200);
        $respAdminA->assertSee('Mode Pemeliharaan Aktif');

        // 2. Admin dinas lain tetap diblokir (503) — tenant isolation
        $respAdminB = $this->actingAs($adminB)->get(route('site.show', 'diskominfo'));
        $respAdminB->assertStatus(503);
        $respAdminB->assertSee('Website Sedang Dalam Pemeliharaan');

        // 3. Super admin dapat melakukan preview (200)
        $respSuperAdmin = $this->actingAs($superAdmin)->get(route('site.show', 'diskominfo'));
        $respSuperAdmin->assertStatus(200);
        $respSuperAdmin->assertSee('Mode Pemeliharaan Aktif');
    }
}
