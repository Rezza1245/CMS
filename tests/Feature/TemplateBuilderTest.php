<?php

namespace Tests\Feature;

use App\Models\Appearance;
use App\Models\Dinas;
use App\Models\Media;
use App\Models\Post;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected Template $template;

    protected User $superAdmin;

    protected User $adminDinas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = Template::create([
            'name' => 'Portal Resmi Kedinasan',
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
                        'id' => 'section_hero_01',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'container_hero_01',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'hero_01',
                                        'component' => 'Hero',
                                        'layout_settings' => ['height' => '480px', 'alignment' => 'center'],
                                        'slots' => [
                                            'title' => [
                                                'slot_id' => 'hero_title',
                                                'binding' => 'appearance.header_slogan',
                                                'type' => 'text',
                                                'editable_by' => 'admin_dinas',
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

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        $dinas = Dinas::create([
            'name' => 'Dinas Komunikasi dan Informatika',
            'code' => 'DISKOMINFO',
        ]);

        $this->adminDinas = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $dinas->id,
        ]);
    }

    public function test_super_admin_can_access_builder_with_required_ui_elements(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.templates.builder', $this->template));

        $response->assertStatus(200);

        // Responsive switcher
        $response->assertSee('data-view="desktop"', false);
        $response->assertSee('data-view="tablet"', false);
        $response->assertSee('data-view="mobile"', false);

        // Undo / Redo buttons
        $response->assertSee('id="btn-undo"', false);
        $response->assertSee('id="btn-redo"', false);

        // Save Status Indicator
        $response->assertSee('id="save-indicator"', false);
        $response->assertSee('id="save-status-text"', false);

        // Live preview shortcut
        $response->assertSee(route('admin.templates.preview', $this->template));
        $response->assertSee('Preview Template');

        // Component Palette
        $response->assertSee('Hero');
        $response->assertSee('Posts Grid');
        $response->assertSee('Container');
        $response->assertSee('Static Content');
        $response->assertSee('Footer');
        $response->assertDontSee('Posts Carousel');
        $response->assertDontSee('Contact / Dinas Info');
    }

    public function test_admin_dinas_is_forbidden_from_accessing_builder(): void
    {
        $response = $this->actingAs($this->adminDinas)->get(route('admin.templates.builder', $this->template));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.templates.builder', $this->template));

        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_can_update_nested_canvas_data(): void
    {
        $newNestedCanvas = [
            'template_version' => '1.0',
            'canvas' => [
                [
                    'id' => 'section_main_01',
                    'type' => 'section',
                    'children' => [
                        [
                            'id' => 'container_main_01',
                            'type' => 'container',
                            'children' => [
                                [
                                    'id' => 'comp_hero_01',
                                    'component' => 'Hero',
                                    'layout_settings' => ['height' => '520px', 'alignment' => 'left'],
                                    'slots' => [
                                        'title' => [
                                            'slot_id' => 'hero_title',
                                            'binding' => 'appearance.header_slogan',
                                            'type' => 'text',
                                            'editable_by' => 'admin_dinas',
                                        ],
                                    ],
                                ],
                                [
                                    'id' => 'comp_posts_01',
                                    'component' => 'Posts Grid',
                                    'layout_settings' => ['columns' => 3, 'limit' => 6],
                                    'slots' => [
                                        'items' => [
                                            'slot_id' => 'posts_source',
                                            'binding' => 'posts.published',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->superAdmin)->putJson(
            route('admin.templates.builder.update', $this->template),
            ['canvas_data' => $newNestedCanvas]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Template berhasil disimpan.');

        $this->template->refresh();
        $this->assertEquals($newNestedCanvas, $this->template->canvas_data);
    }

    public function test_invalid_canvas_component_name_fails_validation(): void
    {
        $invalidCanvas = [
            'template_version' => '1.0',
            'canvas' => [
                [
                    'id' => 'section_invalid_01',
                    'type' => 'section',
                    'children' => [
                        [
                            'id' => 'container_invalid_01',
                            'type' => 'container',
                            'children' => [
                                [
                                    'id' => 'comp_illegal_01',
                                    'component' => 'ArbitraryCodeInjectorWidget', // Forbidden
                                    'layout_settings' => [],
                                    'slots' => [
                                        'hack' => ['slot_id' => 'hack', 'binding' => 'hack'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->superAdmin)->putJson(
            route('admin.templates.builder.update', $this->template),
            ['canvas_data' => $invalidCanvas]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['canvas_data']);
    }

    public function test_public_website_renders_nested_canvas_hierarchy(): void
    {
        $dinas = Dinas::first();
        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $this->template->id,
            'domain' => 'diskominfo',
            'name' => 'Portal Diskominfo',
            'status' => 'aktif',
        ]);

        Appearance::create([
            'website_id' => $website->id,
            'header_slogan' => 'Transformasi Digital Kota Batu',
            'hero_description' => 'Website Resmi Diskominfo Batu',
        ]);

        Post::create([
            'website_id' => $website->id,
            'user_id' => $this->adminDinas->id,
            'title' => 'Berita Teknologi Informasi Terbaru',
            'slug' => 'berita-ti-terbaru',
            'content' => 'Konten berita teknologi informasi.',
            'type' => 'Berita',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('site.show', 'diskominfo'));

        $response->assertStatus(200);
        $response->assertSee('Transformasi Digital Kota Batu');
        $response->assertSee('builder-section', false);
        $response->assertSee('builder-container', false);
    }

    public function test_public_website_renders_media_static_content_and_contact_components(): void
    {
        $dinas = Dinas::create([
            'name' => 'Dinas Pariwisata',
            'code' => 'DISPARTA',
            'contact_email' => 'pariwisata@batukota.go.id',
            'phone' => '(0341) 591033',
            'address' => 'Gedung C Balaikota Among Tani, Kota Batu',
        ]);

        $customTemplate = Template::create([
            'name' => 'Template Lengkap',
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
                        'id' => 'sec_all',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_all',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'static_01',
                                        'component' => 'Static Content',
                                        'layout_settings' => [],
                                        'slots' => [
                                            'content' => [
                                                'slot_id' => 'content',
                                                'binding' => 'pages.content',
                                                'default_value' => 'Profil Wisata Kota Batu.',
                                            ],
                                        ],
                                    ],
                                    [
                                        'id' => 'media_01',
                                        'component' => 'Media / Document List',
                                        'layout_settings' => ['limit' => 5],
                                        'slots' => [
                                            'items' => [
                                                'slot_id' => 'items',
                                                'binding' => 'media.published',
                                            ],
                                        ],
                                    ],
                                    [
                                        'id' => 'footer_01',
                                        'component' => 'Footer',
                                        'layout_settings' => [],
                                        'slots' => [
                                            'slogan' => ['slot_id' => 'slogan', 'binding' => 'appearance.footer_slogan', 'default_value' => 'Kota Batu Shining.'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $customTemplate->id,
            'domain' => 'wisata-batu',
            'name' => 'Website Wisata Batu',
            'status' => 'aktif',
        ]);

        Media::create([
            'website_id' => $website->id,
            'user_id' => $this->adminDinas->id,
            'file_name' => 'Peta-Wisata-Batu-2026.pdf',
            'file_path' => 'documents/peta-wisata.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 2048000,
        ]);

        $response = $this->get(route('site.show', 'wisata-batu'));

        $response->assertStatus(200);
        $response->assertSee('Dokumen &amp; Media Publik', false);
        $response->assertSee('Peta-Wisata-Batu-2026.pdf');
        $response->assertSee('Profil Wisata Kota Batu.');
        $response->assertSee('pariwisata@batukota.go.id');
        $response->assertSee('Gedung C Balaikota Among Tani, Kota Batu');
        $response->assertSee('Kota Batu Shining.');
    }

    public function test_public_website_without_footer_in_canvas_does_not_render_footer(): void
    {
        $dinas = Dinas::create([
            'name' => 'Dinas Pendidikan',
            'code' => 'DISDIK',
            'contact_email' => 'disdik@batukota.go.id',
            'phone' => '(0341) 591040',
            'address' => 'Gedung A Balaikota Among Tani, Kota Batu',
        ]);

        $templateNoFooter = Template::create([
            'name' => 'Template Tanpa Footer',
            'status' => 'aktif',
            'template_version' => '1.0',
            'canvas_data' => [
                'template_version' => '1.0',
                'canvas' => [
                    [
                        'id' => 'sec_main',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_main',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'hero_nodik',
                                        'component' => 'Hero',
                                        'layout_settings' => ['height' => '400px'],
                                        'slots' => [
                                            'title' => [
                                                'slot_id' => 'hero_title',
                                                'binding' => 'appearance.header_slogan',
                                                'default_value' => 'Pendidikan Bermutu Batu',
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

        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $templateNoFooter->id,
            'domain' => 'pendidikan-batu',
            'name' => 'Website Dinas Pendidikan',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('site.show', 'pendidikan-batu'));

        $response->assertStatus(200);
        $response->assertSee('Pendidikan Bermutu Batu');
        // Tidak boleh ada elemen footer yang dirender karena template tidak memiliki komponen Footer
        $response->assertDontSee('builder-footer', false);
        $response->assertDontSee('<footer id="footer"', false);
    }

    public function test_public_website_with_footer_in_canvas_renders_dynamic_footer(): void
    {
        $dinas = Dinas::create([
            'name' => 'Dinas Sosial Kota Batu',
            'code' => 'DINSOS',
            'contact_email' => 'dinsos@batukota.go.id',
            'phone' => '(0341) 591055',
            'address' => 'Jl. Panglima Sudirman No. 507 Gedung B Lantai 1, Kota Batu',
        ]);

        $templateWithFooter = Template::create([
            'name' => 'Template Dengan Footer',
            'status' => 'aktif',
            'template_version' => '1.0',
            'canvas_data' => [
                'template_version' => '1.0',
                'canvas' => [
                    [
                        'id' => 'sec_footer_only',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_footer_only',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'footer_dinsos',
                                        'component' => 'Footer',
                                        'layout_settings' => ['columns' => 3],
                                        'slots' => [
                                            'slogan' => [
                                                'slot_id' => 'slogan',
                                                'binding' => 'appearance.footer_slogan',
                                                'default_value' => 'Peduli, Tanggap, dan Melayani Sepenuh Hati.',
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

        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $templateWithFooter->id,
            'domain' => 'dinsos-batu',
            'name' => 'Website Dinsos Batu',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('site.show', 'dinsos-batu'));

        $response->assertStatus(200);
        $response->assertSee('builder-footer', false);
        $response->assertSee('Website Dinsos Batu');
        $response->assertSee('Peduli, Tanggap, dan Melayani Sepenuh Hati.');
        $response->assertSee('dinsos@batukota.go.id');
        $response->assertSee('(0341) 591055');
        $response->assertSee('Jl. Panglima Sudirman No. 507 Gedung B Lantai 1, Kota Batu');
        $response->assertSee('DINSOS');
        $response->assertSee('Dinas Sosial Kota Batu. Hak Cipta Dilindungi.');
        $response->assertSee('Pemerintah Kota Batu');
        $response->assertDontSee('Diskominfo Kota Batu. Hak Cipta Dilindungi.');
    }

    public function test_footer_renders_customized_slots_for_different_dinas(): void
    {
        $dinas = Dinas::create([
            'name' => 'Dinas Lingkungan Hidup',
            'code' => 'DLH',
            'contact_email' => 'dlh@batukota.go.id',
            'phone' => '(0341) 591060',
            'address' => 'Balaikota Among Tani Gedung A, Kota Batu',
        ]);

        $customFooterTemplate = Template::create([
            'name' => 'Template DLH',
            'status' => 'aktif',
            'template_version' => '1.0',
            'canvas_data' => [
                'template_version' => '1.0',
                'canvas' => [
                    [
                        'id' => 'sec_footer_dlh',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_footer_dlh',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'footer_dlh',
                                        'component' => 'Footer',
                                        'layout_settings' => ['columns' => 3],
                                        'slots' => [
                                            'slogan' => ['slot_id' => 'slogan', 'binding' => 'appearance.footer_slogan', 'default_value' => 'Kota Batu Bersih dan Asri.'],
                                            'about_title' => ['slot_id' => 'about_title', 'binding' => 'template_config.about_title', 'default_value' => 'Batu Go Green'],
                                            'about_text' => ['slot_id' => 'about_text', 'binding' => 'template_config.about_text', 'default_value' => 'Komitmen kelestarian lingkungan hidup dan tata kelola ruang terbuka hijau.'],
                                            'copyright' => ['slot_id' => 'copyright', 'binding' => 'template_config.copyright', 'default_value' => 'Dinas Lingkungan Hidup Kota Batu'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $customFooterTemplate->id,
            'domain' => 'dlh-batu',
            'name' => 'Website DLH Batu',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('site.show', 'dlh-batu'));

        $response->assertStatus(200);
        $response->assertSee('Kota Batu Bersih dan Asri.');
        $response->assertSee('Batu Go Green');
        $response->assertSee('Komitmen kelestarian lingkungan hidup');
        $response->assertSee('Dinas Lingkungan Hidup Kota Batu. Hak Cipta Dilindungi.');
        $response->assertDontSee('Diskominfo');
    }

    public function test_builder_view_locks_hero_alignment_to_center_and_hero_renders_centered(): void
    {
        $dinas = Dinas::first();
        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $this->template->id,
            'domain' => 'diskominfo',
            'name' => 'Website Diskominfo',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.templates.builder', $this->template));

        $response->assertStatus(200);
        $response->assertSee("Perataan posisi hero dipatenkan ke <strong>center</strong>", false);

        // Komponen Hero publik selalu me-render text-center items-center
        $siteResponse = $this->get(route('site.show', 'diskominfo'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('text-center items-center', false);
    }

    public function test_container_component_with_nested_children_persists_and_renders_on_public_website(): void
    {
        $dinas = Dinas::first();
        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $this->template->id,
            'domain' => 'batu-grid',
            'name' => 'Website Grid Batu',
            'status' => 'aktif',
        ]);

        $nestedCanvas = [
            'template_version' => '1.0',
            'canvas' => [
                [
                    'id' => 'section_grid_01',
                    'type' => 'section',
                    'children' => [
                        [
                            'id' => 'container_struct_01',
                            'type' => 'container',
                            'children' => [
                                [
                                    'id' => 'grid_wrapper_01',
                                    'component' => 'Container',
                                    'layout_settings' => ['columns' => 2],
                                    'slots' => [],
                                    'children' => [
                                        [
                                            'id' => 'static_col_01',
                                            'component' => 'Static Content',
                                            'layout_settings' => [],
                                            'slots' => [
                                                'title' => ['slot_id' => 'title', 'binding' => 'pages.title'],
                                            ],
                                        ],
                                        [
                                            'id' => 'posts_col_02',
                                            'component' => 'Posts Grid',
                                            'layout_settings' => ['columns' => 1, 'limit' => 3],
                                            'slots' => [
                                                'items' => ['slot_id' => 'items', 'binding' => 'posts.published'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // 1. Simpan ke database via Builder Update API
        $response = $this->actingAs($this->superAdmin)->putJson(
            route('admin.templates.builder.update', $this->template),
            ['canvas_data' => $nestedCanvas]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Template berhasil disimpan.');

        // 2. Akses halaman publik, pastikan anak-anak di dalam Container ter-render dengan kelas grid md:grid-cols-2
        $siteResponse = $this->get(route('site.show', 'batu-grid'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('builder-container-component grid grid-cols-1 md:grid-cols-2 gap-6', false);
        $siteResponse->assertSee('Profil Ringkas', false);
        $siteResponse->assertSee('Berita & Informasi Terkini', false);
    }

    public function test_invalid_columns_layout_setting_fails_validation(): void
    {
        $invalidCanvas = [
            'template_version' => '1.0',
            'canvas' => [
                [
                    'id' => 'section_invalid_col',
                    'type' => 'section',
                    'children' => [
                        [
                            'id' => 'container_invalid_col',
                            'type' => 'container',
                            'children' => [
                                [
                                    'id' => 'comp_posts_invalid',
                                    'component' => 'Posts Grid',
                                    'layout_settings' => ['columns' => 8, 'limit' => 6],
                                    'slots' => [
                                        'items' => ['slot_id' => 'posts_source', 'binding' => 'posts.published'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->superAdmin)->putJson(
            route('admin.templates.builder.update', $this->template),
            ['canvas_data' => $invalidCanvas]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('canvas_data');
    }

    public function test_invalid_limit_layout_setting_fails_validation(): void
    {
        $invalidCanvas = [
            'template_version' => '1.0',
            'canvas' => [
                [
                    'id' => 'section_invalid_lim',
                    'type' => 'section',
                    'children' => [
                        [
                            'id' => 'container_invalid_lim',
                            'type' => 'container',
                            'children' => [
                                [
                                    'id' => 'comp_media_invalid',
                                    'component' => 'Media / Document List',
                                    'layout_settings' => ['limit' => 0],
                                    'slots' => [
                                        'items' => ['slot_id' => 'media_source', 'binding' => 'media.published'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->superAdmin)->putJson(
            route('admin.templates.builder.update', $this->template),
            ['canvas_data' => $invalidCanvas]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('canvas_data');
    }

    public function test_builder_view_contains_structured_layout_setting_form_controls(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.templates.builder', $this->template));

        $response->assertStatus(200);
        $response->assertSee('Tinggi Banner Hero (Height)');
        $response->assertSee('Jumlah Kolom Kartu Berita');
        $response->assertSee('Batas Jumlah Artikel (Limit)');
        $response->assertSee('Jumlah Kolom Footer');
        $response->assertSee('Batas Jumlah Dokumen (Limit)');
    }

    public function test_builder_view_contains_structured_slot_binding_options_and_collection_notice(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.templates.builder', $this->template));

        $response->assertStatus(200);
        // Header logo binding option
        $response->assertSee('appearance.logo (Logo Resmi Instansi Dinas)');
        // BINDING_OPTIONS in script
        $response->assertSee('appearance.header_slogan (Slogan / Judul Utama Hero Dinas)');
        $response->assertSee('posts.published (Koleksi Artikel Berita & Pengumuman Published)', false);
        // Collection slot helper notice
        $response->assertSee('Data Koleksi Otomatis:');
    }

    public function test_super_admin_can_preview_template_without_dinas_posts_or_data(): void
    {
        // Berikan canvas_data lengkap dengan Posts Grid, Static Content, dan Media List
        $this->template->update([
            'canvas_data' => [
                'template_version' => '1.0',
                'canvas' => [
                    [
                        'id' => 'section_hero_01',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'container_hero_01',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'hero_01',
                                        'component' => 'Hero',
                                        'layout_settings' => ['height' => '480px', 'alignment' => 'center'],
                                        'slots' => [
                                            'title' => [
                                                'slot_id' => 'hero_title',
                                                'binding' => 'appearance.header_slogan',
                                                'type' => 'text',
                                                'editable_by' => 'admin_dinas',
                                            ],
                                        ],
                                    ],
                                    [
                                        'id' => 'posts_01',
                                        'component' => 'Posts Grid',
                                        'layout_settings' => ['columns' => 3, 'limit' => 6],
                                        'slots' => [
                                            'items' => [
                                                'slot_id' => 'posts_source',
                                                'binding' => 'posts.published',
                                            ],
                                        ],
                                    ],
                                    [
                                        'id' => 'static_01',
                                        'component' => 'Static Content',
                                        'layout_settings' => [],
                                        'slots' => [
                                            'title' => [
                                                'slot_id' => 'static_title',
                                                'binding' => 'pages.title',
                                            ],
                                        ],
                                    ],
                                    [
                                        'id' => 'media_01',
                                        'component' => 'Media / Document List',
                                        'layout_settings' => ['limit' => 6],
                                        'slots' => [
                                            'items' => [
                                                'slot_id' => 'media_source',
                                                'binding' => 'media.published',
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

        // Buat dinas dan website dengan postingan nyata
        $dinas = \App\Models\Dinas::create([
            'name' => 'Dinas Komunikasi dan Informatika',
            'code' => 'DISKOMINFO',
        ]);

        $website = \App\Models\Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $this->template->id,
            'domain' => 'diskominfo',
            'name' => 'Website Resmi Diskominfo',
            'status' => 'aktif',
        ]);

        \App\Models\Post::create([
            'website_id' => $website->id,
            'user_id' => $this->adminDinas->id,
            'title' => 'Berita Khusus Milik Diskominfo Yang Terpost',
            'slug' => 'berita-diskominfo-khusus',
            'content' => 'Konten rahasia internal dinas kominfo',
            'status' => 'published',
            'type' => 'Berita',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.templates.preview', $this->template));

        $response->assertStatus(200);

        // Memastikan banner mode preview template tampil
        $response->assertSee('Mode Pratinjau Template');
        $response->assertSee($this->template->name);
        $response->assertSee('Kembali ke Builder');

        // Memastikan TIDAK menampilkan konten/postingan spesifik milik dinas
        $response->assertDontSee('Berita Khusus Milik Diskominfo Yang Terpost');
        $response->assertDontSee('Konten rahasia internal dinas kominfo');

        // Memastikan template dirender murni dengan empty state yang bersih tanpa post
        $response->assertSee('Belum ada publikasi yang diterbitkan');
        $response->assertSee('Ruang Konten Profil &amp; Informasi Kedinasan', false);
        $response->assertSee('Belum ada dokumen publik yang diunggah');
    }

    public function test_admin_dinas_is_forbidden_from_template_preview(): void
    {
        $response = $this->actingAs($this->adminDinas)->get(route('admin.templates.preview', $this->template));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_template_preview(): void
    {
        $response = $this->get(route('admin.templates.preview', $this->template));

        $response->assertRedirect(route('login'));
    }
}
