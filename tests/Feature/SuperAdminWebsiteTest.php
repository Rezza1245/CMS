<?php

namespace Tests\Feature;

use App\Models\Appearance;
use App\Models\Dinas;
use App\Models\Setting;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminWebsiteTest extends TestCase
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
            'name' => 'Template Standar Kedinasan',
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

        $this->dinas = Dinas::create([
            'name' => 'Dinas Komunikasi dan Informatika',
            'code' => 'DISKOMINFO',
            'address' => 'Balaikota Among Tani, Kota Batu',
            'contact_email' => 'diskominfo@batukota.go.id',
            'phone' => '0341-591032',
        ]);

        $this->website = Website::create([
            'dinas_id' => $this->dinas->id,
            'template_id' => $this->template->id,
            'domain' => 'diskominfo',
            'name' => 'Website Resmi Diskominfo Kota Batu',
            'status' => 'aktif',
        ]);

        Appearance::create([
            'website_id' => $this->website->id,
            'header_slogan' => 'Mewujudkan Kota Batu Cerdas',
        ]);

        Setting::create([
            'website_id' => $this->website->id,
            'general_config' => ['site_name' => $this->website->name],
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $this->adminDinas = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinas->id,
        ]);
    }

    public function test_guest_and_admin_dinas_cannot_access_website_management(): void
    {
        // 1. Guest diarahkan ke login
        $guestResp = $this->get(route('admin.websites.index'));
        $guestResp->assertRedirect(route('login'));

        // 2. Admin Kedinasan ditolak dengan 403 Forbidden
        $dinasResp = $this->actingAs($this->adminDinas)->get(route('admin.websites.index'));
        $dinasResp->assertStatus(403);

        $dinasCreateResp = $this->actingAs($this->adminDinas)->get(route('admin.websites.create'));
        $dinasCreateResp->assertStatus(403);
    }

    public function test_super_admin_can_view_website_index_and_create_form(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.websites.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Website Dinas');
        $response->assertSee('Website Resmi Diskominfo Kota Batu');
        $response->assertSee('/site/diskominfo');
        $response->assertSee('DISKOMINFO');

        $createResponse = $this->actingAs($this->superAdmin)->get(route('admin.websites.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Tambah Website Dinas Baru');
        $createResponse->assertSee('Template Standar Kedinasan');
    }

    public function test_super_admin_can_create_new_dinas_and_website_with_automatic_appearance_and_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.websites.store'), [
            'dinas_name' => 'Dinas Pendidikan Kota Batu',
            'dinas_code' => 'disdik', // sistem akan uppercase menjadi DISDIK
            'dinas_email' => 'disdik@batukota.go.id',
            'dinas_phone' => '0341-591035',
            'dinas_address' => 'Jl. Panglima Sudirman No. 507',
            'website_name' => 'Website Resmi Dinas Pendidikan Kota Batu',
            'domain' => 'disdik-batu',
            'template_id' => $this->template->id,
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.websites.index'));
        $response->assertSessionHas('success');

        // Verifikasi entitas dinas dibuat
        $this->assertDatabaseHas('dinas', [
            'name' => 'Dinas Pendidikan Kota Batu',
            'code' => 'DISDIK',
            'contact_email' => 'disdik@batukota.go.id',
        ]);

        $createdDinas = Dinas::where('code', 'DISDIK')->first();
        $this->assertNotNull($createdDinas);

        // Verifikasi entitas website dibuat dengan relasi 1:1 dinas
        $this->assertDatabaseHas('websites', [
            'dinas_id' => $createdDinas->id,
            'template_id' => $this->template->id,
            'domain' => 'disdik-batu',
            'name' => 'Website Resmi Dinas Pendidikan Kota Batu',
            'status' => 'aktif',
        ]);

        $createdWebsite = Website::where('domain', 'disdik-batu')->first();
        $this->assertNotNull($createdWebsite);

        // Verifikasi slot appearance terinisialisasi otomatis
        $this->assertDatabaseHas('appearances', [
            'website_id' => $createdWebsite->id,
            'footer_title' => 'Dinas Pendidikan Kota Batu',
        ]);

        // Verifikasi setting terinisialisasi otomatis
        $this->assertDatabaseHas('settings', [
            'website_id' => $createdWebsite->id,
        ]);

        // Verifikasi dinas baru otomatis muncul di formulir pembuatan user Super Admin
        $userCreateResp = $this->actingAs($this->superAdmin)->get(route('admin.users.create'));
        $userCreateResp->assertStatus(200);
        $userCreateResp->assertSee('Dinas Pendidikan Kota Batu (DISDIK)');

        // Verifikasi portal website publik baru dapat diakses langsung oleh masyarakat
        $publicResp = $this->get(route('site.show', 'disdik-batu'));
        $publicResp->assertStatus(200);
        $publicResp->assertSee('Dinas Pendidikan Kota Batu');
    }

    public function test_super_admin_cannot_create_duplicate_dinas_code_or_website_domain(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.websites.store'), [
            'dinas_name' => 'Dinas Duplikat',
            'dinas_code' => 'DISKOMINFO', // duplikat
            'website_name' => 'Website Duplikat',
            'domain' => 'diskominfo', // duplikat
            'template_id' => $this->template->id,
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors(['dinas_code', 'domain']);
    }

    public function test_super_admin_can_edit_and_update_website_and_dinas(): void
    {
        $editResp = $this->actingAs($this->superAdmin)->get(route('admin.websites.edit', $this->website));
        $editResp->assertStatus(200);
        $editResp->assertSee('Edit Website Dinas');
        $editResp->assertSee($this->website->name);

        $updateResp = $this->actingAs($this->superAdmin)->put(route('admin.websites.update', $this->website), [
            'dinas_name' => 'Dinas Komunikasi, Informatika dan Statistik',
            'dinas_code' => 'DISKOMINFOSTAT',
            'dinas_email' => 'diskominfostat@batukota.go.id',
            'website_name' => 'Portal Resmi Kominfo dan Statistik Kota Batu',
            'domain' => 'diskominfostat',
            'template_id' => $this->template->id,
            'status' => 'aktif',
        ]);

        $updateResp->assertRedirect(route('admin.websites.index'));

        $this->assertDatabaseHas('dinas', [
            'id' => $this->dinas->id,
            'name' => 'Dinas Komunikasi, Informatika dan Statistik',
            'code' => 'DISKOMINFOSTAT',
        ]);

        $this->assertDatabaseHas('websites', [
            'id' => $this->website->id,
            'domain' => 'diskominfostat',
            'name' => 'Portal Resmi Kominfo dan Statistik Kota Batu',
        ]);
    }

    public function test_super_admin_can_delete_website_and_dinas(): void
    {
        $dinasToDelete = Dinas::create([
            'name' => 'Dinas Sementara',
            'code' => 'DISSEMENTARA',
        ]);

        $websiteToDelete = Website::create([
            'dinas_id' => $dinasToDelete->id,
            'template_id' => $this->template->id,
            'domain' => 'sementara',
            'name' => 'Website Sementara',
            'status' => 'aktif',
        ]);

        Appearance::create([
            'website_id' => $websiteToDelete->id,
        ]);

        $userAdmin = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $dinasToDelete->id,
        ]);

        $deleteResp = $this->actingAs($this->superAdmin)->delete(route('admin.websites.destroy', $websiteToDelete));
        $deleteResp->assertRedirect(route('admin.websites.index'));

        $this->assertDatabaseMissing('websites', ['id' => $websiteToDelete->id]);
        $this->assertDatabaseMissing('dinas', ['id' => $dinasToDelete->id]);
        $this->assertDatabaseMissing('appearances', ['website_id' => $websiteToDelete->id]);

        // User terlepas dari dinas dan statusnya nonaktif
        $userAdmin->refresh();
        $this->assertNull($userAdmin->dinas_id);
        $this->assertEquals('nonaktif', $userAdmin->status);
    }
}
