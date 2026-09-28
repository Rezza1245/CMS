<?php

namespace Tests\Feature;

use App\Models\Dinas;
use App\Models\Media;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DinasMediaTest extends TestCase
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
            'name' => 'Template Lengkap Media',
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
                        'id' => 'sec_media',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'con_media',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'media_comp',
                                        'component' => 'Media / Document List',
                                        'layout_settings' => ['limit' => 10],
                                        'slots' => [
                                            'items' => ['slot_id' => 'media_source', 'binding' => 'media.published'],
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
            'name' => 'Dinas Lingkungan Hidup Kota Batu',
            'code' => 'DLH',
        ]);
        $this->siteA = Website::create([
            'dinas_id' => $this->dinasA->id,
            'template_id' => $this->template->id,
            'domain' => 'dlh',
            'name' => 'Portal Resmi DLH Batu',
            'status' => 'aktif',
        ]);
        $this->adminDinasA = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasA->id,
        ]);

        // Dinas B
        $this->dinasB = Dinas::create([
            'name' => 'Dinas Perhubungan Kota Batu',
            'code' => 'DISHUB',
        ]);
        $this->siteB = Website::create([
            'dinas_id' => $this->dinasB->id,
            'template_id' => $this->template->id,
            'domain' => 'dishub',
            'name' => 'Portal Resmi Dishub Batu',
            'status' => 'aktif',
        ]);
        $this->adminDinasB = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasB->id,
        ]);
    }

    public function test_admin_dinas_can_view_media_index_with_tenant_isolation(): void
    {
        Media::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'Laporan-AMDAL-DLH-2026.pdf',
            'file_path' => 'media/amdal-dlh.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 2048000,
        ]);

        Media::create([
            'website_id' => $this->siteB->id,
            'user_id' => $this->adminDinasB->id,
            'file_name' => 'Trayek-Angkutan-Dishub.pdf',
            'file_path' => 'media/trayek-dishub.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1024000,
        ]);

        $response = $this->actingAs($this->adminDinasA)->get(route('dinas.media.index'));

        $response->assertStatus(200);
        $response->assertSee('Laporan-AMDAL-DLH-2026.pdf');
        $response->assertDontSee('Trayek-Angkutan-Dishub.pdf');
        $response->assertSee('Total Berkas Dinas');
    }

    public function test_admin_dinas_can_upload_pdf_document_and_it_reflects_on_public_media_list(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('Rencana-Strategis-DLH-2026-2030.pdf', 1500, 'application/pdf');

        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.media.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('dinas.media.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('media', [
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'Rencana-Strategis-DLH-2026-2030.pdf',
            'file_type' => 'application/pdf',
        ]);

        $media = Media::where('website_id', $this->siteA->id)->first();
        $this->assertNotNull($media);
        Storage::disk('public')->assertExists($media->file_path);

        // Pastikan ter-render di website publik pada komponen Media / Document List
        $siteResponse = $this->get(route('site.show', 'dlh'));
        $siteResponse->assertStatus(200);
        $siteResponse->assertSee('Dokumen &amp; Media Publik', false);
        $siteResponse->assertSee('Rencana-Strategis-DLH-2026-2030.pdf');
        $siteResponse->assertSee('Unduh Berkas');
    }

    public function test_admin_dinas_can_upload_image_media(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('infografis-daur-ulang.png', 800, 'image/png');

        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.media.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('dinas.media.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('media', [
            'website_id' => $this->siteA->id,
            'file_name' => 'infografis-daur-ulang.png',
        ]);
    }

    public function test_admin_dinas_cannot_upload_disallowed_file_extension(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('virus-script.exe', 500, 'application/x-msdownload');

        $response = $this->actingAs($this->adminDinasA)->post(route('dinas.media.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseMissing('media', [
            'file_name' => 'virus-script.exe',
        ]);
    }

    public function test_admin_dinas_cannot_delete_other_dinas_media(): void
    {
        Storage::fake('public');

        $mediaB = Media::create([
            'website_id' => $this->siteB->id,
            'user_id' => $this->adminDinasB->id,
            'file_name' => 'Dokumen-Sensitif-Dishub.pdf',
            'file_path' => 'media/dokumen-dishub.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1024000,
        ]);

        // Admin Dinas A mencoba menghapus media milik Dinas B
        $response = $this->actingAs($this->adminDinasA)->delete(route('dinas.media.destroy', $mediaB));

        $response->assertStatus(403);
        $this->assertDatabaseHas('media', [
            'id' => $mediaB->id,
            'file_name' => 'Dokumen-Sensitif-Dishub.pdf',
        ]);
    }

    public function test_admin_dinas_can_delete_own_media_and_file_is_removed_from_storage(): void
    {
        Storage::fake('public');

        $storedPath = 'media/dokumen-dlh-dihapus.pdf';
        Storage::disk('public')->put($storedPath, 'isi-dokumen-pdf');

        $media = Media::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'dokumen-dlh-dihapus.pdf',
            'file_path' => $storedPath,
            'file_type' => 'application/pdf',
            'file_size' => 2048,
        ]);

        $response = $this->actingAs($this->adminDinasA)->delete(route('dinas.media.destroy', $media));

        $response->assertRedirect(route('dinas.media.index'));
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_super_admin_cannot_access_dinas_media(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('dinas.media.index'));
        $response->assertStatus(403);
    }

    public function test_public_website_renders_media_grid_with_image_and_pdf_previews(): void
    {
        Storage::fake('public');

        // Buat media gambar
        $imgPath = 'media/galeri-kegiatan.png';
        Storage::disk('public')->put($imgPath, 'fake-image-content');
        Media::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'galeri-kegiatan.png',
            'file_path' => $imgPath,
            'file_type' => 'image/png',
            'file_size' => 1024000,
        ]);

        // Buat media PDF
        $pdfPath = 'media/laporan-tahunan.pdf';
        Storage::disk('public')->put($pdfPath, 'fake-pdf-content');
        Media::create([
            'website_id' => $this->siteA->id,
            'user_id' => $this->adminDinasA->id,
            'file_name' => 'laporan-tahunan.pdf',
            'file_path' => $pdfPath,
            'file_type' => 'application/pdf',
            'file_size' => 2048000,
        ]);

        $response = $this->get(route('site.show', 'dlh'));
        $response->assertStatus(200);

        // Preview gambar asli ter-render dengan tag img
        $response->assertSee(asset('storage/' . $imgPath));
        $response->assertSee('galeri-kegiatan.png');

        // Preview dokumen PDF ter-render dengan iframe terisolasi
        $response->assertSee(asset('storage/' . $pdfPath));
        $response->assertSee('laporan-tahunan.pdf');

        // Grid 3 kolom ter-render
        $response->assertSee('grid-cols-1 md:grid-cols-2 lg:grid-cols-3', false);
    }
}
