<?php

namespace Tests\Feature;

use App\Models\Dinas;
use App\Models\Setting;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DinasSettingTest extends TestCase
{
    use RefreshDatabase;

    protected Template $template;
    protected Dinas $dinasA;
    protected Dinas $dinasB;
    protected Website $siteA;
    protected Website $siteB;
    protected User $adminDinasA;
    protected User $adminDinasB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = Template::create([
            'name' => 'Portal Template',
            'status' => 'aktif',
            'template_version' => '1.0',
            'header_structure' => 'default',
            'post_layout' => 'grid',
            'page_layout' => 'standard',
            'navigation_structure' => 'top',
            'canvas_data' => ['template_version' => '1.0', 'canvas' => []],
        ]);

        $this->dinasA = Dinas::create([
            'name' => 'Dinas Kesehatan Kota Batu',
            'code' => 'DINKES',
            'address' => 'Jl. Panglima Sudirman No. 507',
            'contact_email' => 'dinkes@batukota.go.id',
            'phone' => '(0341) 591035',
        ]);

        $this->dinasB = Dinas::create([
            'name' => 'Dinas Pariwisata Kota Batu',
            'code' => 'DISPAR',
            'address' => 'Jl. Kartini No. 1',
            'contact_email' => 'dispar@batukota.go.id',
            'phone' => '(0341) 591036',
        ]);

        $this->siteA = Website::create([
            'dinas_id' => $this->dinasA->id,
            'template_id' => $this->template->id,
            'domain' => 'dinkes',
            'name' => 'Website Resmi Dinkes Batu',
            'status' => 'aktif',
        ]);

        $this->siteB = Website::create([
            'dinas_id' => $this->dinasB->id,
            'template_id' => $this->template->id,
            'domain' => 'dispar',
            'name' => 'Website Resmi Dispar Batu',
            'status' => 'aktif',
        ]);

        $this->adminDinasA = User::factory()->create([
            'name' => 'Admin Dinkes',
            'email' => 'admin.dinkes@batukota.go.id',
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasA->id,
        ]);

        $this->adminDinasB = User::factory()->create([
            'name' => 'Admin Dispar',
            'email' => 'admin.dispar@batukota.go.id',
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasB->id,
        ]);
    }

    public function test_admin_dinas_can_view_settings_form(): void
    {
        $response = $this->actingAs($this->adminDinasA)->get(route('dinas.settings.edit'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Website Kedinasan');
        $response->assertSee('Dinas Kesehatan Kota Batu');
        $response->assertSee('Website Resmi Dinkes Batu');
        $response->assertDontSee('Deskripsi Meta Publik / SEO');
        $response->assertDontSee('Search Engine Indexing');
    }

    public function test_admin_dinas_can_update_dinas_settings(): void
    {
        // ponytail: validasi konfigurasi tenant standar, upgrade ke skema custom metadata jika ada kebutuhan dinas khusus
        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.settings.update'), [
            'site_title' => 'Portal Layanan Dinkes Terpadu',
            'site_status' => 'aktif',
            'public_visibility' => 'publik',
            'privacy_contact_email' => 'privasi@dinkes.batukota.go.id',
        ]);

        $response->assertRedirect(route('dinas.settings.edit'));
        $response->assertSessionHas('success');

        $this->siteA->refresh();
        $this->assertEquals('Portal Layanan Dinkes Terpadu', $this->siteA->name);

        $setting = Setting::where('website_id', $this->siteA->id)->first();
        $this->assertNotNull($setting);
        $this->assertEquals('Portal Layanan Dinkes Terpadu', $setting->general_config['site_title']);
        $this->assertEquals('privasi@dinkes.batukota.go.id', $setting->privacy_config['privacy_contact_email']);
    }

    public function test_admin_dinas_can_toggle_maintenance_mode_and_blocks_public(): void
    {
        // 1. Ubah status ke pemeliharaan
        $response = $this->actingAs($this->adminDinasA)->put(route('dinas.settings.update'), [
            'site_title' => 'Portal Layanan Dinkes Terpadu',
            'site_status' => 'pemeliharaan',
            'public_visibility' => 'publik',
        ]);

        $response->assertRedirect(route('dinas.settings.edit'));
        $this->siteA->refresh();
        $this->assertEquals('pemeliharaan', $this->siteA->status);

        // 2. Admin dinas yang sedang login tetap dapat preview (200)
        $previewResp = $this->get(route('site.show', 'dinkes'));
        $previewResp->assertStatus(200);
        $previewResp->assertSee('Mode Pemeliharaan Aktif');

        // 3. Pengunjung publik / guest diblokir (503)
        auth()->logout();
        $publicResp = $this->get(route('site.show', 'dinkes'));
        $publicResp->assertStatus(503);
        $publicResp->assertSee('Website Sedang Dalam Pemeliharaan');

        // 4. Kembalikan ke aktif
        $put2 = $this->actingAs($this->adminDinasA)->put(route('dinas.settings.update'), [
            'site_title' => 'Portal Layanan Dinkes Terpadu',
            'site_status' => 'aktif',
            'public_visibility' => 'publik',
        ]);
        $put2->assertRedirect(route('dinas.settings.edit'));

        $this->siteA->refresh();
        $this->assertEquals('aktif', $this->siteA->status);

        // 4. Publik kembali dapat mengakses (200)
        $publicResp2 = $this->get(route('site.show', 'dinkes'));
        $publicResp2->assertStatus(200);
    }

    public function test_tenant_isolation_on_settings(): void
    {
        // Set initial setting on Website B
        Setting::create([
            'website_id' => $this->siteB->id,
            'general_config' => ['site_title' => 'Situs Asli Dispar', 'site_status' => 'aktif'],
            'privacy_config' => ['public_visibility' => 'publik'],
        ]);

        // Admin Dinas A updates settings
        $this->actingAs($this->adminDinasA)->put(route('dinas.settings.update'), [
            'site_title' => 'Update Oleh Admin A',
            'site_status' => 'aktif',
            'public_visibility' => 'publik',
        ]);

        // Pastikan setting Dinas B tidak tersentuh (BR-01)
        $settingB = Setting::where('website_id', $this->siteB->id)->first();
        $this->assertEquals('Situs Asli Dispar', $settingB->general_config['site_title']);
    }

    public function test_super_admin_cannot_access_dinas_settings(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('dinas.settings.edit'));
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dinas.settings.edit'));
        $response->assertRedirect(route('login'));
    }
}
