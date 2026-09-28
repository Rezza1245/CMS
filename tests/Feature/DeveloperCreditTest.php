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
use Tests\TestCase;

class DeveloperCreditTest extends TestCase
{
    use RefreshDatabase;

    protected Template $template;
    protected Dinas $dinas;
    protected Website $website;
    protected User $superAdmin;
    protected User $adminDinas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = Template::create([
            'name' => 'Portal Resmi Kedinasan',
            'status' => 'aktif',
            'canvas_data' => [
                'template_version' => '1.0',
                'canvas' => [
                    [
                        'id' => 'section_hero',
                        'type' => 'section',
                        'children' => [
                            [
                                'id' => 'container_hero',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'hero_01',
                                        'component' => 'Hero',
                                        'layout_settings' => ['height' => '480px'],
                                        'slots' => [
                                            'title' => [
                                                'slot_id' => 'hero_title',
                                                'binding' => 'appearance.header_slogan',
                                                'type' => 'text',
                                                'editable_by' => 'admin_dinas',
                                            ],
                                        ],
                                    ],
                                    [
                                        'id' => 'posts_01',
                                        'component' => 'Posts Grid',
                                        'layout_settings' => ['columns' => 3, 'limit' => 6],
                                        'slots' => [
                                            'items' => [
                                                'slot_id' => 'posts_source',
                                                'binding' => 'posts.published',
                                            ],
                                        ],
                                    ],
                                    [
                                        'id' => 'footer_01',
                                        'component' => 'Footer',
                                        'layout_settings' => ['columns' => 3],
                                        'slots' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->dinas = Dinas::create([
            'name' => 'Dinas Pendidikan',
            'code' => 'DISDIK',
        ]);

        $this->website = Website::create([
            'dinas_id' => $this->dinas->id,
            'template_id' => $this->template->id,
            'domain' => 'disdik',
            'name' => 'Website Resmi Disdik',
            'status' => 'aktif',
        ]);

        Appearance::create([
            'website_id' => $this->website->id,
            'header_slogan' => 'Pendidikan Cerdas',
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

    public function test_super_admin_views_display_developer_credit(): void
    {
        $credit = 'Developed by Azzaryansyaa';

        // 1. Dashboard Super Admin
        $this->actingAs($this->superAdmin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 2. Websites Index
        $this->actingAs($this->superAdmin)
            ->get(route('admin.websites.index'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 3. Websites Create
        $this->actingAs($this->superAdmin)
            ->get(route('admin.websites.create'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 4. Websites Edit
        $this->actingAs($this->superAdmin)
            ->get(route('admin.websites.edit', $this->website))
            ->assertStatus(200)
            ->assertSee($credit);

        // 5. Users Index
        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 6. Users Create
        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.create'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 7. Users Edit
        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.edit', $this->adminDinas))
            ->assertStatus(200)
            ->assertSee($credit);

        // 8. Settings Edit
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.edit'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 9. Activities Index
        $this->actingAs($this->superAdmin)
            ->get(route('admin.activities.index'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 10. Template Builder
        $this->actingAs($this->superAdmin)
            ->get(route('admin.templates.builder', $this->template))
            ->assertStatus(200)
            ->assertSee($credit);
    }

    public function test_admin_dinas_views_display_developer_credit(): void
    {
        $credit = 'Developed by Azzaryansyaa';

        $post = Post::create([
            'website_id' => $this->website->id,
            'user_id' => $this->adminDinas->id,
            'title' => 'Berita Pertama Disdik',
            'slug' => 'berita-pertama-disdik',
            'content' => 'Konten berita disdik.',
            'status' => 'published',
            'type' => 'Berita',
        ]);

        $page = Page::create([
            'website_id' => $this->website->id,
            'title' => 'Profil Disdik',
            'slug' => 'profil-disdik',
            'content' => 'Konten profil disdik.',
            'status' => 'published',
            'placement' => 'header_menu',
        ]);

        // 1. Dashboard Admin Dinas
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.dashboard'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 2. Posts Index
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.posts.index'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 3. Posts Create
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.posts.create'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 4. Posts Edit
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.posts.edit', $post))
            ->assertStatus(200)
            ->assertSee($credit);

        // 5. Pages Index
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.pages.index'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 6. Pages Create
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.pages.create'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 7. Pages Edit
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.pages.edit', $page))
            ->assertStatus(200)
            ->assertSee($credit);

        // 8. Media Index
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.media.index'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 9. Appearance Edit
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.appearance.edit'))
            ->assertStatus(200)
            ->assertSee($credit);

        // 10. Settings Edit
        $this->actingAs($this->adminDinas)
            ->get(route('dinas.settings.edit'))
            ->assertStatus(200)
            ->assertSee($credit);
    }

    public function test_public_website_views_do_not_contain_developer_credit(): void
    {
        $credit = 'Developed by Azzaryansyaa';

        $post = Post::create([
            'website_id' => $this->website->id,
            'user_id' => $this->adminDinas->id,
            'title' => 'Pengumuman Beasiswa Kota Batu',
            'slug' => 'pengumuman-beasiswa-kota-batu',
            'content' => 'Detail beasiswa terbuka untuk siswa.',
            'status' => 'published',
            'type' => 'Pengumuman',
        ]);

        $page = Page::create([
            'website_id' => $this->website->id,
            'title' => 'Visi Misi Pendidikan',
            'slug' => 'visi-misi-pendidikan',
            'content' => 'Mewujudkan pendidikan bermutu.',
            'status' => 'published',
            'placement' => 'header_menu',
        ]);

        // 1. Beranda Publik
        $this->get(route('site.show', $this->website->domain))
            ->assertStatus(200)
            ->assertDontSee($credit);

        // 2. Halaman Statis Publik
        $this->get(route('site.page.show', ['identifier' => $this->website->domain, 'slug' => $page->slug]))
            ->assertStatus(200)
            ->assertDontSee($credit);

        // 3. Post / Berita Publik
        $this->get(route('site.post.show', ['identifier' => $this->website->domain, 'slug' => $post->slug]))
            ->assertStatus(200)
            ->assertDontSee($credit);

        // 4. Halaman Pemeliharaan Publik
        $this->website->update(['status' => 'pemeliharaan']);
        $this->get(route('site.show', $this->website->domain))
            ->assertStatus(503)
            ->assertDontSee($credit);

        // 5. Pratinjau Template Builder
        $this->actingAs($this->superAdmin)
            ->get(route('admin.templates.preview', $this->template))
            ->assertStatus(200)
            ->assertDontSee($credit);
    }
}
