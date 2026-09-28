<?php

namespace Tests\Feature;

use App\Models\Dinas;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
        RateLimiter::clear('public-site');
        RateLimiter::clear('cms-write');
        RateLimiter::clear('cms-read');
    }

    public function test_login_endpoint_is_throttled_after_consecutive_failed_attempts(): void
    {
        $email = 'admin.target@batukota.go.id';

        // 5 percobaan login pertama harus diproses (status 302 redirect back with error)
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'wrongpassword',
            ]);

            $response->assertStatus(302);
            $response->assertSessionHasErrors('email');
        }

        // Percobaan ke-6 wajib ditolak oleh throttle dengan HTTP 429 (Too Many Requests)
        $throttledResponse = $this->post('/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $throttledResponse->assertStatus(429);
        $throttledResponse->assertSee('Terlalu Banyak Permintaan');
        $throttledResponse->assertSee('Demi keamanan sistem, silakan tunggu 1 menit');
    }

    public function test_public_website_endpoints_enforce_rate_limiting_headers(): void
    {
        $template = Template::create([
            'name' => 'Template Standar',
            'status' => 'aktif',
            'template_version' => '1.0',
            'header_structure' => 'default',
            'post_layout' => 'grid',
            'page_layout' => 'standard',
            'navigation_structure' => 'top',
            'canvas_data' => ['template_version' => '1.0', 'canvas' => []],
        ]);

        $dinas = Dinas::create([
            'name' => 'Dinas Perhubungan',
            'code' => 'DISHUB',
        ]);

        $website = Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $template->id,
            'domain' => 'dishub',
            'name' => 'Portal Dishub',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('site.show', 'dishub'));
        $response->assertStatus(200);

        // Header rate limit harus ada pada response publik
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertEquals(120, (int) $response->headers->get('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
    }

    public function test_cms_write_endpoint_enforces_rate_limiting_headers(): void
    {
        $dinas = Dinas::create([
            'name' => 'Dinas Kominfo',
            'code' => 'DISKOMINFO',
        ]);

        $user = User::factory()->create([
            'role' => 'admin_dinas',
            'status' => 'aktif',
            'dinas_id' => $dinas->id,
        ]);

        $template = Template::create([
            'name' => 'Template Kominfo',
            'status' => 'aktif',
            'template_version' => '1.0',
            'header_structure' => 'default',
            'post_layout' => 'grid',
            'page_layout' => 'standard',
            'navigation_structure' => 'top',
            'canvas_data' => ['template_version' => '1.0', 'canvas' => []],
        ]);

        Website::create([
            'dinas_id' => $dinas->id,
            'template_id' => $template->id,
            'domain' => 'diskominfo',
            'name' => 'Portal Kominfo',
            'status' => 'aktif',
        ]);

        // Melakukan request write (store post)
        $response = $this->actingAs($user)->post(route('dinas.posts.store'), [
            'title' => 'Judul Artikel Aman Anti Spam',
            'type' => 'Berita',
            'content' => 'Konten artikel.',
            'status' => 'published',
            'placement_target' => 'beranda',
        ]);

        $response->assertRedirect(route('dinas.posts.index'));

        // Cek header rate limit mutasi (cms-write = 40)
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertEquals(40, (int) $response->headers->get('X-RateLimit-Limit'));
    }
}
