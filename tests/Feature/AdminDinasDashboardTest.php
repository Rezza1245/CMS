<?php

namespace Tests\Feature;

use App\Models\Appearance;
use App\Models\Dinas;
use App\Models\Page;
use App\Models\Post;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDinasDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dinas_dashboard_renders_with_tenant_isolation_and_ui_specs(): void
    {
        $template = Template::create([
            'name' => 'Portal Resmi Kedinasan',
            'status' => 'aktif',
            'header_structure' => 'default',
            'post_layout' => 'grid',
            'page_layout' => 'standard',
            'navigation_structure' => 'top',
        ]);

        // Dinas A (User Dinas)
        $dinasA = Dinas::create([
            'name' => 'Dinas Kesehatan',
            'code' => 'DINKES',
        ]);

        $siteA = Website::create([
            'dinas_id' => $dinasA->id,
            'template_id' => $template->id,
            'domain' => 'dinkes',
            'name' => 'Website Resmi Dinkes',
            'status' => 'aktif',
        ]);

        Appearance::create([
            'website_id' => $siteA->id,
            'header_slogan' => 'Kesehatan Masyarakat Prioritas Utama',
            'hero_description' => 'Layanan informasi kesehatan terpadu Kota Batu.',
        ]);

        $adminDinasA = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $dinasA->id,
        ]);

        // Posts for Dinas A (2 Published, 1 Draft)
        Post::create([
            'website_id' => $siteA->id,
            'user_id' => $adminDinasA->id,
            'title' => 'Vaksinasi Massal Balita',
            'slug' => 'vaksinasi-massal-balita',
            'content' => 'Pelaksanaan vaksinasi di seluruh puskesmas.',
            'type' => 'Berita',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Post::create([
            'website_id' => $siteA->id,
            'user_id' => $adminDinasA->id,
            'title' => 'Penyuluhan Gizi Anak',
            'slug' => 'penyuluhan-gizi-anak',
            'content' => 'Penyuluhan tentang gizi seimbang.',
            'type' => 'Berita',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Post::create([
            'website_id' => $siteA->id,
            'user_id' => $adminDinasA->id,
            'title' => 'Draft SOP Pelayanan Medis',
            'slug' => 'draft-sop-pelayanan-medis',
            'content' => 'Dokumen draf internal pelayanan.',
            'type' => 'Pengumuman',
            'status' => 'draft',
        ]);

        foreach (['Profil', 'Visi Misi', 'Struktur Organisasi', 'Kontak'] as $pageTitle) {
            Page::create([
                'website_id' => $siteA->id,
                'title' => $pageTitle,
                'slug' => Str::slug($pageTitle),
                'content' => "Konten {$pageTitle}",
                'status' => 'published',
            ]);
        }

        $draftPageA = Page::create([
            'website_id' => $siteA->id,
            'title' => 'Draft Standar Pelayanan',
            'slug' => 'draft-standar-pelayanan',
            'content' => 'Konten draf pelayanan',
            'status' => 'draft',
        ]);

        // Dinas B (Other Dinas to verify tenant isolation)
        $dinasB = Dinas::create([
            'name' => 'Dinas Pendidikan',
            'code' => 'DISDIK',
        ]);

        $siteB = Website::create([
            'dinas_id' => $dinasB->id,
            'template_id' => $template->id,
            'domain' => 'disdik',
            'name' => 'Website Resmi Disdik',
            'status' => 'aktif',
        ]);

        Post::create([
            'website_id' => $siteB->id,
            'user_id' => $adminDinasA->id,
            'title' => 'Draft Rahasia Disdik',
            'slug' => 'draft-rahasia-disdik',
            'content' => 'Konten draf disdik',
            'type' => 'Pengumuman',
            'status' => 'draft',
        ]);

        // 5 posts for Dinas B
        for ($i = 1; $i <= 5; $i++) {
            Post::create([
                'website_id' => $siteB->id,
                'user_id' => $adminDinasA->id,
                'title' => "Post Disdik {$i}",
                'slug' => "post-disdik-{$i}",
                'content' => "Konten Disdik {$i}",
                'type' => 'Berita',
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        // Act: Login as Admin Dinas A and hit /admin/dashboard
        $response = $this->actingAs($adminDinasA)->get('/admin/dashboard');

        $response->assertStatus(200);

        // 1. Sidebar checks: background #0f172a, active Dashboard pill, 6 menus, NO Template / User menu
        $response->assertSee('#0f172a', false);
        $response->assertSee('Dashboard');
        $response->assertSee('Posts');
        $response->assertSee('Media');
        $response->assertSee('Pages');
        $response->assertSee('Appearance');
        $response->assertSee(route('dinas.appearance.edit'), false);
        $response->assertSee('Settings');
        $response->assertDontSee('>Template<', false);
        $response->assertDontSee('>User<', false);

        // 2. Header & User badge with Dinas name
        $response->assertSee('Admin Dinas Kesehatan');

        // 3. Post statistics scoped strictly to Dinas A (Total: 3, Published: 2, Draft: 1)
        // NOT 8 (which would include Dinas B)
        $response->assertSee('Total Posts: 3 Konten');
        $response->assertSee('2 Berita Terpublikasi · 1 Draft tersimpan');

        // 4. Quick Access Draft (menggantikan Status Website & Ringkasan Slot)
        $response->assertSee('Quick Access Draft');
        $response->assertSee('Draft SOP Pelayanan Medis');
        $response->assertSee(route('dinas.posts.edit', 3));
        $response->assertSee('Draft Standar Pelayanan');
        $response->assertSee(route('dinas.pages.edit', $draftPageA));
        $response->assertDontSee('Draft Rahasia Disdik'); // Tenant isolation

        // 5. Footer check
        $response->assertSee('CMS Diskominfo Kota Batu');
        $response->assertSee('© 2026 Diskominfo Kota Batu');
    }
}
