<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('CMS');
        $response->assertSee('Diskominfo Kota Batu');
        $response->assertSee('Masuk ke Akun');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@batukota.go.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@batukota.go.id',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'inactive@batukota.go.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'status' => 'nonaktif',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@batukota.go.id',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
