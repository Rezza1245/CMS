<?php

namespace Tests\Feature;

use App\Models\Dinas;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_renders_with_exact_components_and_queries(): void
    {
        // Seed active template
        $template = Template::create([
            'name' => 'Template Khusus Diskominfo',
            'status' => 'aktif',
        ]);

        $dinas = Dinas::create([
            'name' => 'Dinas Kesehatan',
            'code' => 'DINKES',
        ]);

        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $template->id,
            'domain' => 'dinkes-batu',
            'name' => 'Website Dinkes Batu',
            'status' => 'aktif',
        ]);

        // Seed admin_dinas users
        User::factory()->count(4)->create([
            'role' => 'admin_dinas',
        ]);

        // Seed other role user
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');

        $response->assertStatus(200);

        // 1. Sidebar background and active nav
        $response->assertSee('#0f172a', false);
        $response->assertSee('bg-white text-blue-600', false);
        $response->assertSee(route('admin.templates.builder', $template), false);

        // 2. Query active template & quick shortcuts
        $response->assertSee('Template Aktif: Template Khusus Diskominfo');
        $response->assertSee('Template "Template Khusus Diskominfo" diaktifkan', false);
        $response->assertSee('Edit di Builder');
        $response->assertSee(route('admin.templates.preview', $template));
        $response->assertSee('Preview Template');

        // 3. User::where('role', 'admin_dinas')->count()
        $response->assertSee('Jumlah Admin Kedinasan: 4');

        // 4. Monitoring Website Dinas card (menggantikan status sistem)
        $response->assertSee('Monitoring Website Dinas');
        $response->assertSee('1 Terdaftar');
        $response->assertSee('Live / Aktif');
        $response->assertSee('Website Dinkes Batu');
        $response->assertSee('Dinas Kesehatan');
        $response->assertSee(route('admin.websites.index'));
        $response->assertSee(route('admin.websites.create'));
        $response->assertSee(route('site.show', 'dinkes-batu'));

        // 5. Activity card items
        $response->assertSee('Aktivitas Terbaru');
        $response->assertSee('Admin kedinasan baru ditambahkan');
        $response->assertSee('Template "Layanan Publik" diperbarui', false);
        $response->assertSee('Admin kedinasan diperbarui');
        $response->assertSee('Pengaturan sistem diperbarui');

        // 6. Footer
        $response->assertSee('CMS Diskominfo Kota Batu');
        $response->assertSee('© 2024 Diskominfo Kota Batu');
    }
}
