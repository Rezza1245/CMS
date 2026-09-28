<?php

namespace Tests\Feature;

use App\Models\Dinas;
use App\Models\Post;
use App\Models\SystemSetting;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminActivityTest extends TestCase
{
    use RefreshDatabase;

    protected Template $template;

    protected User $superAdmin;

    protected User $adminDinas;

    protected Dinas $dinas;

    protected Website $website;

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
            'canvas_data' => ['template_version' => '1.0', 'canvas' => []],
        ]);

        $this->dinas = Dinas::create([
            'name' => 'Dinas Kesehatan',
            'code' => 'DINKES',
        ]);

        $this->website = Website::create([
            'dinas_id' => $this->dinas->id,
            'template_id' => $this->template->id,
            'domain' => 'dinkes',
            'name' => 'Portal Dinas Kesehatan',
            'status' => 'aktif',
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        $this->adminDinas = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinas->id,
        ]);
    }

    public function test_guest_and_admin_dinas_cannot_access_activities(): void
    {
        $guestResp = $this->get(route('admin.activities.index'));
        $guestResp->assertRedirect(route('login'));

        $dinasResp = $this->actingAs($this->adminDinas)->get(route('admin.activities.index'));
        $dinasResp->assertStatus(403);
    }

    public function test_super_admin_can_view_aggregated_activity_log(): void
    {
        Post::create([
            'website_id' => $this->website->id,
            'user_id' => $this->adminDinas->id,
            'title' => 'Pekan Imunisasi Balita',
            'slug' => 'pekan-imunisasi-balita',
            'type' => 'Berita',
            'content' => 'Layanan imunisasi balita gratis.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        SystemSetting::set('platform_name', 'CMS Kota Batu');

        $response = $this->actingAs($this->superAdmin)->get(route('admin.activities.index'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Aktivitas Platform');
        $response->assertSee('Portal Dinas Kesehatan');
        $response->assertSee('Template Standar');
        $response->assertSee('Pekan Imunisasi Balita');
        $response->assertSee('CMS Kota Batu');
    }

    public function test_activity_log_filters_by_category_and_search(): void
    {
        $responseCategory = $this->actingAs($this->superAdmin)->get(route('admin.activities.index', ['category' => 'website']));
        $responseCategory->assertStatus(200);
        $responseCategory->assertSee('Portal Dinas Kesehatan');

        $responseSearch = $this->actingAs($this->superAdmin)->get(route('admin.activities.index', ['search' => 'Kesehatan']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Portal Dinas Kesehatan');

        $responseNotFound = $this->actingAs($this->superAdmin)->get(route('admin.activities.index', ['search' => 'NonExistentActivityQuery123']));
        $responseNotFound->assertStatus(200);
        $responseNotFound->assertSee('Tidak ada data aktivitas yang sesuai');
    }

    public function test_dashboard_links_directly_to_activities_and_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.activities.index'));
        $response->assertSee(route('admin.settings.edit'));
    }
}
