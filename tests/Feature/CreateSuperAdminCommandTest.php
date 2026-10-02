<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateSuperAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_super_admin_via_command_options(): void
    {
        $this->artisan('make:super-admin', [
            '--name' => 'Super Admin Diskominfo',
            '--email' => 'superadmin@batukota.go.id',
            '--password' => 'SecurePass123!',
        ])
            ->expectsOutputToContain('Super Admin [superadmin@batukota.go.id] berhasil dibuat.')
            ->assertSuccessful();

        $user = User::where('email', 'superadmin@batukota.go.id')->first();
        $this->assertNotNull($user);
        $this->assertSame('Super Admin Diskominfo', $user->name);
        $this->assertSame('super_admin', $user->role);
        $this->assertSame('aktif', $user->status);
        $this->assertNull($user->dinas_id);
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));

        // Baseline template otomatis dibuat
        $this->assertDatabaseHas('templates', [
            'name' => 'Portal Resmi Kedinasan',
            'status' => 'aktif',
        ]);
    }

    public function test_can_create_super_admin_interactively(): void
    {
        $this->artisan('make:super-admin')
            ->expectsQuestion('Nama Lengkap Super Admin', 'Kepala Diskominfo')
            ->expectsQuestion('Alamat Email', 'kepala@batukota.go.id')
            ->expectsQuestion('Kata Sandi (minimal 8 karakter)', 'SecretKey888')
            ->expectsQuestion('Konfirmasi Kata Sandi', 'SecretKey888')
            ->expectsOutputToContain('Super Admin [kepala@batukota.go.id] berhasil dibuat.')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'kepala@batukota.go.id',
            'role' => 'super_admin',
        ]);
    }

    public function test_fails_when_email_already_exists(): void
    {
        User::factory()->create([
            'email' => 'existing@batukota.go.id',
        ]);

        $this->artisan('make:super-admin', [
            '--name' => 'Another Admin',
            '--email' => 'existing@batukota.go.id',
            '--password' => 'SecurePass123!',
        ])
            ->assertFailed();
    }

    public function test_fails_when_password_mismatches_interactively(): void
    {
        $this->artisan('make:super-admin')
            ->expectsQuestion('Nama Lengkap Super Admin', 'Admin Test')
            ->expectsQuestion('Alamat Email', 'admintest@batukota.go.id')
            ->expectsQuestion('Kata Sandi (minimal 8 karakter)', 'PasswordA123')
            ->expectsQuestion('Konfirmasi Kata Sandi', 'PasswordB999')
            ->expectsOutput('Konfirmasi kata sandi tidak cocok.')
            ->assertFailed();

        $this->assertDatabaseMissing('users', [
            'email' => 'admintest@batukota.go.id',
        ]);
    }

    public function test_fails_when_password_too_short(): void
    {
        $this->artisan('make:super-admin', [
            '--name' => 'Short Pass',
            '--email' => 'short@batukota.go.id',
            '--password' => 'short',
        ])
            ->expectsOutput('Kata sandi minimal harus 8 karakter.')
            ->assertFailed();
    }
}
