<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminDinas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $this->adminDinas = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
        ]);
    }

    public function test_super_admin_can_view_settings_form(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.settings.edit'));

        $response->assertStatus(200);
        $response->assertSee('Konfigurasi Sistem Global');
        $response->assertDontSee('Identitas Platform Global');
        $response->assertDontSee('Mode Pemeliharaan Platform');
        $response->assertSee('Batasan Media &amp; Penyimpanan Disk', false);
    }

    public function test_super_admin_can_update_system_settings(): void
    {
        // ponytail: implementasi validasi dasar pengaturan global, upgrade ke queue/event cache invalidation jika traffic tinggi
        $response = $this->actingAs($this->superAdmin)->put(route('admin.settings.update'), [
            'max_upload_size_mb' => 15,
            'allowed_media_types' => 'jpg,png,pdf,webp',
        ]);

        $response->assertRedirect(route('admin.settings.edit'));
        $response->assertSessionHas('success');

        $this->assertEquals('15', SystemSetting::get('max_upload_size_mb'));
        $this->assertEquals('jpg,png,pdf,webp', SystemSetting::get('allowed_media_types'));
    }

    public function test_admin_dinas_is_forbidden_from_admin_settings(): void
    {
        // ROLES_RBAC Rule 05 & Matrix 7: System Configuration DENY for Admin Kedinasan
        $responseGet = $this->actingAs($this->adminDinas)->get(route('admin.settings.edit'));
        $responseGet->assertStatus(403);

        $responsePut = $this->actingAs($this->adminDinas)->put(route('admin.settings.update'), [
            'max_upload_size_mb' => 50,
            'allowed_media_types' => 'exe,sh',
        ]);
        $responsePut->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.settings.edit'));
        $response->assertRedirect(route('login'));
    }
}
