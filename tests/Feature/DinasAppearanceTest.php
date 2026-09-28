<?php

namespace Tests\Feature;

use App\Models\Appearance;
use App\Models\Dinas;
use App\Models\Media;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DinasAppearanceTest extends TestCase
{
    use RefreshDatabase;

    protected Template $template;

    protected Dinas $dinasA;

    protected Website $siteA;

    protected Appearance $appearanceA;

    protected User $adminDinasA;

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
                                        'layout_settings' => ['height' => '480px', 'alignment' => 'center'],
                                        'slots' => [
                                            'title' => ['slot_id' => 'title', 'binding' => 'appearance.header_slogan'],
                                            'description' => ['slot_id' => 'description', 'binding' => 'appearance.hero_description'],
                                            'background_image' => ['slot_id' => 'bg', 'binding' => 'appearance.hero_banner'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->dinasA = Dinas::create([
            'name' => 'Dinas Kesehatan Kota Batu',
            'code' => 'DINKES',
            'address' => 'Jl. Panglima Sudirman No. 507',
            'contact_email' => 'dinkes@batukota.go.id',
            'phone' => '(0341) 591035',
        ]);

        $this->siteA = Website::create([
            'dinas_id' => $this->dinasA->id,
            'template_id' => $this->template->id,
            'domain' => 'dinkes',
            'name' => 'Website Resmi Dinkes Batu',
            'status' => 'aktif',
        ]);

        $this->appearanceA = Appearance::create([
            'website_id' => $this->siteA->id,
            'header_slogan' => 'Slogan Awal Dinkes',
            'hero_description' => 'Deskripsi Awal Dinkes',
            'footer_slogan' => 'Melayani dengan Tulus.',
        ]);

        $this->adminDinasA = User::factory()->create([
            'name' => 'Admin Dinkes',
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasA->id,
        ]);
    }

    public function test_admin_dinas_can_access_appearance_form(): void
    {
        $response = $this->actingAs($this->adminDinasA)->get(route('dinas.appearance.edit'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Appearance Slots');
        $response->assertSee('Admin Dinas Kesehatan Kota Batu');
        $response->assertSee('Slogan Awal Dinkes');
        $response->assertSee('Deskripsi Awal Dinkes');
        $response->assertSee(route('site.show', 'dinkes'));
        $response->assertDontSee('URL Favicon');
        $response->assertDontSee('name="favicon"', false);
    }

    public function test_admin_dinas_can_update_appearance_slots_and_contact(): void
    {
        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'header_slogan' => 'Kesehatan Unggul, Warga Sejahtera',
            'hero_description' => 'Layanan kesehatan ramah dan prima untuk seluruh masyarakat Kota Batu.',
            'hero_banner' => 'https://example.com/banner-dinkes-baru.jpg',
            'footer_slogan' => 'Dinkes Batu Berintegritas.',
            'address' => 'Jl. Kartini No. 10 Kota Batu',
            'contact_email' => 'kontak@dinkes.batukota.go.id',
            'phone' => '(0341) 555999',
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));
        $response->assertSessionHas('success');

        $this->appearanceA->refresh();
        $this->dinasA->refresh();

        $this->assertEquals('Kesehatan Unggul, Warga Sejahtera', $this->appearanceA->header_slogan);
        $this->assertEquals('Layanan kesehatan ramah dan prima untuk seluruh masyarakat Kota Batu.', $this->appearanceA->hero_description);
        $this->assertEquals('https://example.com/banner-dinkes-baru.jpg', $this->appearanceA->hero_banner);
        $this->assertEquals('Dinkes Batu Berintegritas.', $this->appearanceA->footer_slogan);
        $this->assertEquals('Jl. Kartini No. 10 Kota Batu', $this->dinasA->address);
        $this->assertEquals('kontak@dinkes.batukota.go.id', $this->dinasA->contact_email);
        $this->assertEquals('(0341) 555999', $this->dinasA->phone);
    }

    public function test_updated_appearance_immediately_reflects_on_live_website(): void
    {
        // 1. Tambah footer component ke template canvas agar kontak dinas ter-render di website publik
        $canvasData = $this->template->canvas_data;
        $canvasData['canvas'][] = [
            'id' => 'sec_footer',
            'type' => 'section',
            'children' => [
                [
                    'id' => 'con_footer',
                    'type' => 'container',
                    'children' => [
                        [
                            'id' => 'footer_comp',
                            'component' => 'Footer',
                            'layout_settings' => ['columns' => 3],
                            'slots' => [
                                'title' => ['slot_id' => 'title', 'binding' => 'appearance.footer_title'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->template->update(['canvas_data' => $canvasData]);

        // 2. Update appearance
        $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'header_slogan' => 'Kota Batu Bebas Stunting 2026',
            'hero_description' => 'Komitmen terpadu Diskominfo dan Dinkes mewujudkan generasi emas.',
            'hero_banner' => 'https://example.com/hero-stunting.jpg',
            'address' => 'Gedung Terpadu Dinkes Batu',
            'contact_email' => 'layanan@dinkes.batukota.go.id',
            'phone' => '(0341) 123456',
        ]);

        // 3. Visit live public website for this tenant
        $response = $this->get(route('site.show', 'dinkes'));

        $response->assertStatus(200);
        $response->assertSee('Kota Batu Bebas Stunting 2026');
        $response->assertSee('Komitmen terpadu Diskominfo dan Dinkes mewujudkan generasi emas.');
        $response->assertSee('layanan@dinkes.batukota.go.id');
        $response->assertSee('Gedung Terpadu Dinkes Batu');
        $response->assertDontSee('Lihat Berita Terkini');
    }

    public function test_super_admin_cannot_access_dinas_appearance_edit(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('dinas.appearance.edit'));

        $response->assertStatus(403);
    }

    public function test_admin_dinas_can_upload_hero_banner_image_from_device(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('custom-hero.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'header_slogan' => 'Portal Dinkes Baru',
            'hero_banner_file' => $file,
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));
        $response->assertSessionHas('success');

        $this->appearanceA->refresh();

        $this->assertNotNull($this->appearanceA->hero_banner);
        $this->assertStringStartsWith('hero/', $this->appearanceA->hero_banner);
        Storage::disk('public')->assertExists($this->appearanceA->hero_banner);

        // Pastikan tercatat di tabel media (SCHEMA 4.9)
        $this->assertDatabaseHas('media', [
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'custom-hero.jpg',
            'file_path' => $this->appearanceA->hero_banner,
        ]);

        // Pastikan dirender dengan URL asset storage publik pada live website
        $siteResponse = $this->get(route('site.show', 'dinkes'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('storage/' . $this->appearanceA->hero_banner);
    }

    public function test_admin_dinas_cannot_upload_hero_banner_when_locked_by_template(): void
    {
        Storage::fake('public');

        // Update template to lock hero background to super_admin
        $lockedCanvas = $this->template->canvas_data;
        $lockedCanvas['canvas'][0]['children'][0]['children'][0]['slots']['background_image']['editable_by'] = 'super_admin';
        $this->template->update(['canvas_data' => $lockedCanvas]);

        $initialBanner = $this->appearanceA->hero_banner;
        $file = UploadedFile::fake()->create('hacker-banner.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'hero_banner_file' => $file,
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));

        $this->appearanceA->refresh();
        $this->assertEquals($initialBanner, $this->appearanceA->hero_banner);
        $this->assertDatabaseMissing('media', [
            'file_name' => 'hacker-banner.jpg',
        ]);
    }

    public function test_admin_dinas_can_remove_uploaded_hero_banner(): void
    {
        Storage::fake('public');

        $storedPath = 'hero/test-banner-to-delete.jpg';
        Storage::disk('public')->put($storedPath, 'fake-image-content');

        $this->appearanceA->update(['hero_banner' => $storedPath]);

        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'remove_hero_banner' => 1,
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));
        $this->appearanceA->refresh();

        $this->assertNull($this->appearanceA->hero_banner);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_admin_dinas_can_upload_logo_image_from_device(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('custom-logo.png', 120, 'image/png');

        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'header_slogan' => 'Portal Dinkes Baru',
            'logo_file' => $file,
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));
        $response->assertSessionHas('success');

        $this->appearanceA->refresh();

        $this->assertNotNull($this->appearanceA->logo);
        $this->assertStringStartsWith('logos/', $this->appearanceA->logo);
        Storage::disk('public')->assertExists($this->appearanceA->logo);

        // Pastikan tercatat di tabel media (SCHEMA 4.9)
        $this->assertDatabaseHas('media', [
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'custom-logo.png',
            'file_path' => $this->appearanceA->logo,
        ]);

        // Pastikan dirender dengan URL asset storage publik pada navbar website publik
        $siteResponse = $this->get(route('site.show', 'dinkes'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('storage/' . $this->appearanceA->logo);
    }

    public function test_admin_dinas_cannot_upload_logo_when_locked_by_template(): void
    {
        Storage::fake('public');

        // Update template to lock logo to super_admin
        $lockedCanvas = $this->template->canvas_data;
        $lockedCanvas['header'] = [
            'slots' => [
                'logo' => [
                    'slot_id' => 'header_logo',
                    'binding' => 'appearance.logo',
                    'editable_by' => 'super_admin',
                ],
            ],
        ];
        $this->template->update(['canvas_data' => $lockedCanvas]);

        $initialLogo = $this->appearanceA->logo;
        $file = UploadedFile::fake()->create('unauthorized-logo.png', 100, 'image/png');

        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'logo_file' => $file,
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));

        $this->appearanceA->refresh();
        $this->assertEquals($initialLogo, $this->appearanceA->logo);
        $this->assertDatabaseMissing('media', [
            'file_name' => 'unauthorized-logo.png',
        ]);
    }

    public function test_admin_dinas_can_remove_uploaded_logo(): void
    {
        Storage::fake('public');

        $storedPath = 'logos/test-logo-to-delete.png';
        Storage::disk('public')->put($storedPath, 'fake-logo-content');

        $this->appearanceA->update(['logo' => $storedPath]);

        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'remove_logo' => 1,
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));
        $this->appearanceA->refresh();

        $this->assertNull($this->appearanceA->logo);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_admin_dinas_can_update_footer_columns_1_and_3_slots(): void
    {
        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'footer_title' => 'Dinas Kesehatan Kota Batu Terpadu',
            'footer_slogan' => 'Mewujudkan Layanan Kesehatan Terbaik untuk Warga Batu.',
            'footer_about_title' => 'Visi Kesehatan Kota Batu',
            'footer_about_text' => 'Komitmen terintegrasi menjaga kesehatan dan kesejahteraan seluruh masyarakat Batu.',
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));
        $response->assertSessionHas('success');

        $this->appearanceA->refresh();

        $this->assertEquals('Dinas Kesehatan Kota Batu Terpadu', $this->appearanceA->footer_title);
        $this->assertEquals('Mewujudkan Layanan Kesehatan Terbaik untuk Warga Batu.', $this->appearanceA->footer_slogan);
        $this->assertEquals('Visi Kesehatan Kota Batu', $this->appearanceA->footer_about_title);
        $this->assertEquals('Komitmen terintegrasi menjaga kesehatan dan kesejahteraan seluruh masyarakat Batu.', $this->appearanceA->footer_about_text);
    }

    public function test_footer_customized_slots_reflect_on_live_website(): void
    {
        // 1. Tambah footer component ke template canvas
        $canvasData = $this->template->canvas_data;
        $canvasData['canvas'][] = [
            'id' => 'sec_footer_test',
            'type' => 'section',
            'children' => [
                [
                    'id' => 'con_footer_test',
                    'type' => 'container',
                    'children' => [
                        [
                            'id' => 'footer_test_comp',
                            'component' => 'Footer',
                            'layout_settings' => ['columns' => 3],
                            'slots' => [
                                'title' => ['slot_id' => 'footer_title', 'binding' => 'appearance.footer_title'],
                                'slogan' => ['slot_id' => 'footer_slogan', 'binding' => 'appearance.footer_slogan'],
                                'about_title' => ['slot_id' => 'footer_about_title', 'binding' => 'appearance.footer_about_title'],
                                'about_text' => ['slot_id' => 'footer_about_text', 'binding' => 'appearance.footer_about_text'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->template->update(['canvas_data' => $canvasData]);

        // 2. Simpan pengaturan footer oleh admin dinas
        $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'footer_title' => 'Dinkes Kota Batu Maju',
            'footer_slogan' => 'Sehat Bersama, Bahagia Selamanya.',
            'footer_about_title' => 'Pilar Keterbukaan Dinkes',
            'footer_about_text' => 'Pelayanan informasi kesehatan cepat, akurat, dan dapat diakses publik kapan saja.',
        ]);

        // 3. Akses live website tenant
        $response = $this->get(route('site.show', 'dinkes'));

        $response->assertStatus(200);
        $response->assertSee('Dinkes Kota Batu Maju');
        $response->assertSee('Sehat Bersama, Bahagia Selamanya.');
        $response->assertSee('Pilar Keterbukaan Dinkes');
        $response->assertSee('Pelayanan informasi kesehatan cepat, akurat');
    }

    public function test_admin_dinas_cannot_update_footer_slots_when_locked_by_template(): void
    {
        // Kunci slot about_title ke super_admin
        $canvasData = $this->template->canvas_data;
        $canvasData['canvas'][] = [
            'id' => 'sec_footer_locked',
            'type' => 'section',
            'children' => [
                [
                    'id' => 'con_footer_locked',
                    'type' => 'container',
                    'children' => [
                        [
                            'id' => 'footer_locked_comp',
                            'component' => 'Footer',
                            'layout_settings' => ['columns' => 3],
                            'slots' => [
                                'about_title' => [
                                    'slot_id' => 'footer_about_title',
                                    'binding' => 'appearance.footer_about_title',
                                    'editable_by' => 'super_admin',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->template->update(['canvas_data' => $canvasData]);

        $this->appearanceA->update(['footer_about_title' => 'Judul Asli Template']);

        // Admin dinas mencoba mengubah slot yang terkunci
        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.appearance.update'), [
            'footer_about_title' => 'Judul Hasil Hack',
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));
        $this->appearanceA->refresh();

        // Nilai tetap tidak berubah
        $this->assertEquals('Judul Asli Template', $this->appearanceA->footer_about_title);
    }
}
