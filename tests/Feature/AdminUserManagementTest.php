<?php

namespace Tests\Feature;

use App\Models\Dinas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminDinas;
    protected Dinas $dinasA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dinasA = Dinas::create([
            'name' => 'Dinas Pendidikan Kota Batu',
            'code' => 'DISDIK',
            'address' => 'Jl. Panglima Sudirman No. 507',
            'contact_email' => 'disdik@batukota.go.id',
            'phone' => '(0341) 591040',
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Utama',
            'email' => 'superadmin@batukota.go.id',
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $this->adminDinas = User::factory()->create([
            'name' => 'Admin Disdik',
            'email' => 'admin.disdik@batukota.go.id',
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $this->dinasA->id,
        ]);
    }

    public function test_super_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Super Admin Utama');
        $response->assertSee('Admin Disdik');
        $response->assertSee('Dinas Pendidikan Kota Batu');
    }

    public function test_super_admin_can_view_create_user_form(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Pengguna Baru');
        $response->assertSee('Dinas Pendidikan Kota Batu');
    }

    public function test_super_admin_can_create_admin_dinas_user(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'name' => 'Pegawai Baru Disdik',
            'code' => 'PEG-001',
            'email' => 'pegawai.baru@batukota.go.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin_dinas',
            'dinas_id' => $this->dinasA->id,
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Pegawai Baru Disdik',
            'email' => 'pegawai.baru@batukota.go.id',
            'role' => 'admin_dinas',
            'dinas_id' => $this->dinasA->id,
            'code' => 'PEG-001',
            'status' => 'aktif',
        ]);

        $newUser = User::where('email', 'pegawai.baru@batukota.go.id')->first();
        $this->assertTrue(Hash::check('password123', $newUser->password));
    }

    public function test_super_admin_can_create_super_admin_user(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'name' => 'Super Admin Tambahan',
            'email' => 'admin2@batukota.go.id',
            'password' => 'securepass123',
            'password_confirmation' => 'securepass123',
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Super Admin Tambahan',
            'email' => 'admin2@batukota.go.id',
            'role' => 'super_admin',
            'dinas_id' => null,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_dinas_creation_requires_dinas_id(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'name' => 'Admin Tanpa Dinas',
            'email' => 'tanpadinas@batukota.go.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin_dinas',
            'dinas_id' => null,
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors('dinas_id');
        $this->assertDatabaseMissing('users', [
            'email' => 'tanpadinas@batukota.go.id',
        ]);
    }

    public function test_user_creation_fails_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'name' => 'Akun Duplikat',
            'email' => 'admin.disdik@batukota.go.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin_dinas',
            'dinas_id' => $this->dinasA->id,
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_edit_and_update_user(): void
    {
        // ponytail: implementasi validasi dasar user management, upgrade ke permission granular jika ada requirement multi-level superadmin
        $response = $this->actingAs($this->superAdmin)->put(route('admin.users.update', $this->adminDinas), [
            'name' => 'Admin Disdik Terbarui',
            'email' => 'admin.disdik@batukota.go.id',
            'role' => 'admin_dinas',
            'dinas_id' => $this->dinasA->id,
            'status' => 'nonaktif',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->adminDinas->refresh();

        $this->assertEquals('Admin Disdik Terbarui', $this->adminDinas->name);
        $this->assertEquals('nonaktif', $this->adminDinas->status);
    }

    public function test_super_admin_cannot_deactivate_or_delete_self(): void
    {
        // 1. Coba nonaktifkan akun sendiri
        $responseUpdate = $this->actingAs($this->superAdmin)->put(route('admin.users.update', $this->superAdmin), [
            'name' => 'Super Admin',
            'email' => $this->superAdmin->email,
            'role' => 'super_admin',
            'status' => 'nonaktif',
        ]);

        $this->superAdmin->refresh();
        $this->assertEquals('aktif', $this->superAdmin->status);

        // 2. Coba hapus akun sendiri
        $responseDelete = $this->actingAs($this->superAdmin)->delete(route('admin.users.destroy', $this->superAdmin));
        $this->assertDatabaseHas('users', [
            'id' => $this->superAdmin->id,
        ]);
    }

    public function test_super_admin_can_delete_another_user(): void
    {
        $response = $this->actingAs($this->superAdmin)->delete(route('admin.users.destroy', $this->adminDinas));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $this->adminDinas->id,
        ]);
    }

    public function test_admin_dinas_is_forbidden_from_user_management(): void
    {
        // ROLES_RBAC Rule 05: Admin Kedinasan dilarang mengelola user
        $response = $this->actingAs($this->adminDinas)->get(route('admin.users.index'));
        $response->assertStatus(403);

        $responseCreate = $this->actingAs($this->adminDinas)->get(route('admin.users.create'));
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($this->adminDinas)->post(route('admin.users.store'), [
            'name' => 'Hacker User',
            'email' => 'hacker@batukota.go.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin_dinas',
            'dinas_id' => $this->dinasA->id,
            'status' => 'aktif',
        ]);
        $responseStore->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('login'));
    }
}
