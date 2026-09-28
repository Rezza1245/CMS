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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class MediaOptimizerTest extends TestCase
{
    use RefreshDatabase;

    protected Template $template;

    protected Dinas $dinas;

    protected Website $website;

    protected User $adminDinas;

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
                                            'background_image' => ['slot_id' => 'bg', 'binding' => 'appearance.hero_banner', 'editable_by' => 'admin_dinas'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->dinas = Dinas::create([
            'name' => 'Dinas Komunikasi dan Informatika',
            'code' => 'DISKOMINFO',
            'phone' => '081234567890',
        ]);

        $this->website = Website::create([
            'dinas_id' => $this->dinas->id,
            'template_id' => $this->template->id,
            'domain' => 'diskominfo',
            'name' => 'Portal Diskominfo',
            'status' => 'aktif',
        ]);

        Appearance::create([
            'website_id' => $this->website->id,
            'header_slogan' => 'Mewujudkan Kota Batu Cerdas',
        ]);

        $this->adminDinas = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinas->id,
        ]);
    }

    public function test_uploaded_png_and_jpg_are_converted_to_webp_in_media_library(): void
    {
        Storage::fake('public');

        // Gunakan real image generation dari fake()->image() agar GD dapat membaca binary gambar
        $pngFile = UploadedFile::fake()->image('laporan-grafik.png', 400, 300);

        $response = $this->actingAs($this->adminDinas)->post(route('dinas.media.store'), [
            'file' => $pngFile,
        ]);

        $response->assertRedirect(route('dinas.media.index'));
        $response->assertSessionHas('success');

        $media = Media::where('website_id', $this->website->id)->first();
        $this->assertNotNull($media);

        // Verifikasi ekstensi diubah ke webp dan mime-type adalah image/webp
        $this->assertEquals('laporan-grafik.webp', $media->file_name);
        $this->assertEquals('image/webp', $media->file_type);
        $this->assertStringEndsWith('.webp', $media->file_path);

        Storage::disk('public')->assertExists($media->file_path);
    }

    public function test_uploaded_docx_and_xlsx_documents_are_supported_and_stored(): void
    {
        Storage::fake('public');

        // Buat file docx valid (zip archive dengan XML di dalamnya)
        $tempDocx = tempnam(sys_get_temp_dir(), 'test_docx_') . '.docx';
        $zip = new ZipArchive();
        $zip->open($tempDocx, ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types></Types>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document><w:body><w:p><w:r><w:t>Surat Keputusan Resmi Kedinasan</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        $uploadedDocx = new UploadedFile($tempDocx, 'Surat-Keputusan.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $response = $this->actingAs($this->adminDinas)->post(route('dinas.media.store'), [
            'file' => $uploadedDocx,
        ]);

        $response->assertRedirect(route('dinas.media.index'));
        $response->assertSessionHas('success');

        $media = Media::where('file_name', 'Surat-Keputusan.docx')->first();
        $this->assertNotNull($media);
        $this->assertStringEndsWith('.docx', $media->file_path);

        Storage::disk('public')->assertExists($media->file_path);

        @unlink($tempDocx);
    }

    public function test_post_image_upload_is_converted_to_webp(): void
    {
        Storage::fake('public');

        $jpgImage = UploadedFile::fake()->image('kegiatan-bimtek.jpg', 600, 400);

        $response = $this->actingAs($this->adminDinas)->post(route('dinas.posts.store'), [
            'title' => 'Bimtek Transformasi Digital 2026',
            'type' => 'Kegiatan',
            'content' => 'Pelatihan kompetensi digital aparatur.',
            'status' => 'published',
            'image_file' => $jpgImage,
        ]);

        $response->assertRedirect(route('dinas.posts.index'));

        $post = Post::where('title', 'Bimtek Transformasi Digital 2026')->first();
        $this->assertNotNull($post);
        $this->assertStringEndsWith('.webp', $post->image);
        Storage::disk('public')->assertExists($post->image);

        // Verifikasi media record yang tercatat juga berformat webp
        $media = Media::where('file_path', $post->image)->first();
        $this->assertNotNull($media);
        $this->assertEquals('image/webp', $media->file_type);
        $this->assertEquals('kegiatan-bimtek.webp', $media->file_name);
    }

    public function test_appearance_hero_banner_and_logo_are_converted_to_webp(): void
    {
        Storage::fake('public');

        $bannerJpg = UploadedFile::fake()->image('hero-batu.jpg', 1200, 600);
        $logoPng = UploadedFile::fake()->image('logo-resmi.png', 200, 200);

        $response = $this->actingAs($this->adminDinas)->put(route('dinas.appearance.update'), [
            'hero_banner_file' => $bannerJpg,
            'logo_file' => $logoPng,
            'header_slogan' => 'Kota Batu Cerdas',
        ]);

        $response->assertRedirect(route('dinas.appearance.edit'));

        $appearance = $this->website->fresh()->appearance;
        $this->assertNotNull($appearance->hero_banner);
        $this->assertNotNull($appearance->logo);

        $this->assertStringEndsWith('.webp', $appearance->hero_banner);
        $this->assertStringEndsWith('.webp', $appearance->logo);

        Storage::disk('public')->assertExists($appearance->hero_banner);
        Storage::disk('public')->assertExists($appearance->logo);
    }
}
