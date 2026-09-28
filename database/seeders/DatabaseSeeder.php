<?php

namespace Database\Seeders;

use App\Models\Appearance;
use App\Models\Dinas;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Template Aktif dengan canvas_data baseline (TEMPLATE.md Bab 5 & 7)
        $template = Template::updateOrCreate(
            ['name' => 'Portal Resmi Kedinasan'],
            [
                'status' => 'aktif',
                'template_version' => '1.0',
                'canvas_data' => [
                    'template_version' => '1.0',
                    'canvas' => [
                        [
                            'id' => 'section_hero_01',
                            'type' => 'section',
                            'children' => [
                                [
                                    'id' => 'container_hero_01',
                                    'type' => 'container',
                                    'children' => [
                                        [
                                            'id' => 'hero_01',
                                            'component' => 'Hero',
                                            'layout_settings' => [
                                                'height' => '480px',
                                                'alignment' => 'center',
                                            ],
                                            'slots' => [
                                                'title' => [
                                                    'slot_id' => 'hero_title',
                                                    'binding' => 'appearance.header_slogan',
                                                    'type' => 'text',
                                                    'data_source' => 'appearance',
                                                    'editable_by' => 'admin_dinas',
                                                    'required' => false,
                                                ],
                                                'description' => [
                                                    'slot_id' => 'hero_description',
                                                    'binding' => 'appearance.hero_description',
                                                    'type' => 'textarea',
                                                    'data_source' => 'appearance',
                                                    'editable_by' => 'admin_dinas',
                                                    'required' => false,
                                                ],
                                                'background_image' => [
                                                    'slot_id' => 'hero_bg',
                                                    'binding' => 'appearance.hero_banner',
                                                    'type' => 'image',
                                                    'data_source' => 'appearance',
                                                    'editable_by' => 'admin_dinas',
                                                    'required' => false,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'section_posts_02',
                            'type' => 'section',
                            'children' => [
                                [
                                    'id' => 'container_posts_02',
                                    'type' => 'container',
                                    'children' => [
                                        [
                                            'id' => 'posts_grid_01',
                                            'component' => 'Posts Grid',
                                            'layout_settings' => [
                                                'columns' => 3,
                                                'limit' => 6,
                                            ],
                                            'slots' => [
                                                'items' => [
                                                    'slot_id' => 'posts_source',
                                                    'binding' => 'posts.published',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'section_static_03',
                            'type' => 'section',
                            'children' => [
                                [
                                    'id' => 'container_static_03',
                                    'type' => 'container',
                                    'children' => [
                                        [
                                            'id' => 'static_content_01',
                                            'component' => 'Static Content',
                                            'layout_settings' => [],
                                            'slots' => [
                                                'title' => [
                                                    'slot_id' => 'static_title',
                                                    'binding' => 'pages.title',
                                                    'type' => 'text',
                                                    'data_source' => 'pages',
                                                    'editable_by' => 'admin_dinas',
                                                    'required' => false,
                                                ],
                                                'content' => [
                                                    'slot_id' => 'static_content',
                                                    'binding' => 'pages.content',
                                                    'type' => 'textarea',
                                                    'data_source' => 'pages',
                                                    'editable_by' => 'admin_dinas',
                                                    'required' => false,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'section_media_04',
                            'type' => 'section',
                            'children' => [
                                [
                                    'id' => 'container_media_04',
                                    'type' => 'container',
                                    'children' => [
                                        [
                                            'id' => 'media_list_01',
                                            'component' => 'Media / Document List',
                                            'layout_settings' => [
                                                'limit' => 6,
                                            ],
                                            'slots' => [
                                                'items' => [
                                                    'slot_id' => 'media_source',
                                                    'binding' => 'media.published',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'section_footer_05',
                            'type' => 'section',
                            'children' => [
                                [
                                    'id' => 'container_footer_06',
                                    'type' => 'container',
                                    'children' => [
                                        [
                                            'id' => 'footer_01',
                                            'component' => 'Footer',
                                            'layout_settings' => [
                                                'columns' => 3,
                                            ],
                                             'slots' => [
                                                 'title' => [
                                                     'slot_id' => 'footer_title',
                                                     'binding' => 'appearance.footer_title',
                                                     'type' => 'text',
                                                     'data_source' => 'appearance',
                                                     'editable_by' => 'admin_dinas',
                                                     'default_value' => 'Pemerintah Kota Batu',
                                                     'required' => false,
                                                 ],
                                                 'slogan' => [
                                                     'slot_id' => 'footer_slogan',
                                                     'binding' => 'appearance.footer_slogan',
                                                     'type' => 'text',
                                                     'data_source' => 'appearance',
                                                     'editable_by' => 'admin_dinas',
                                                     'default_value' => 'Portal informasi dan layanan kedinasan resmi Pemerintah Kota Batu.',
                                                     'required' => false,
                                                 ],
                                                 'about_title' => [
                                                     'slot_id' => 'footer_about_title',
                                                     'binding' => 'appearance.footer_about_title',
                                                     'type' => 'text',
                                                     'data_source' => 'appearance',
                                                     'editable_by' => 'admin_dinas',
                                                     'default_value' => 'Pemerintah Kota Batu',
                                                     'required' => false,
                                                 ],
                                                 'about_text' => [
                                                     'slot_id' => 'footer_about_text',
                                                     'binding' => 'appearance.footer_about_text',
                                                     'type' => 'textarea',
                                                     'data_source' => 'appearance',
                                                     'editable_by' => 'admin_dinas',
                                                     'default_value' => 'Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu.',
                                                     'required' => false,
                                                 ],
                                                 'copyright' => [
                                                    'slot_id' => 'footer_copyright',
                                                    'binding' => 'template_config.copyright',
                                                    'type' => 'text',
                                                    'data_source' => 'template_config',
                                                    'editable_by' => 'super_admin',
                                                    'default_value' => 'Pemerintah Kota Batu',
                                                    'required' => false,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'header_structure' => 'default',
                'post_layout' => 'grid',
                'page_layout' => 'standard',
                'navigation_structure' => 'top',
            ]
        );

        // 2. Entitas Dinas (SCHEMA.md 2.1)
        $dinasKominfo = Dinas::updateOrCreate(
            ['code' => 'DISKOMINFO'],
            [
                'name' => 'Dinas Komunikasi dan Informatika',
                'address' => 'Balaikota Among Tani, Gedung B Lantai 2, Jl. Panglima Sudirman No. 507, Kota Batu',
                'contact_email' => 'diskominfo@batukota.go.id',
                'phone' => '(0341) 591032',
            ]
        );

        $dinasPariwisata = Dinas::updateOrCreate(
            ['code' => 'DISPARTA'],
            [
                'name' => 'Dinas Pariwisata',
                'address' => 'Balaikota Among Tani, Gedung C Lantai 2, Jl. Panglima Sudirman No. 507, Kota Batu',
                'contact_email' => 'disparta@batukota.go.id',
                'phone' => '(0341) 591033',
            ]
        );

        // 3. Akun Pengguna (SCHEMA.md 2.2)
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@batukota.go.id'],
            [
                'name' => 'Super Admin Diskominfo',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'status' => 'aktif',
                'dinas_id' => null,
            ]
        );

        $adminKominfo = User::updateOrCreate(
            ['email' => 'admin.ikp@batukota.go.id'],
            [
                'name' => 'Admin Diskominfo Bidang IKP',
                'password' => Hash::make('password123'),
                'role' => 'admin_dinas',
                'status' => 'aktif',
                'dinas_id' => $dinasKominfo->id,
            ]
        );

        $adminPariwisata = User::updateOrCreate(
            ['email' => 'admin.pariwisata@batukota.go.id'],
            [
                'name' => 'Admin Dinas Pariwisata',
                'password' => Hash::make('password123'),
                'role' => 'admin_dinas',
                'status' => 'aktif',
                'dinas_id' => $dinasPariwisata->id,
            ]
        );

        // 4. Website Publik per Dinas (SCHEMA.md 2.4 - Relasi 1:1 Dinas-Website)
        $siteKominfo = Website::updateOrCreate(
            ['dinas_id' => $dinasKominfo->id],
            [
                'template_id' => $template->id,
                'domain' => 'diskominfo',
                'name' => 'Website Resmi Diskominfo Kota Batu',
                'status' => 'aktif',
            ]
        );

        $sitePariwisata = Website::updateOrCreate(
            ['dinas_id' => $dinasPariwisata->id],
            [
                'template_id' => $template->id,
                'domain' => 'pariwisata',
                'name' => 'Website Resmi Dinas Pariwisata Kota Batu',
                'status' => 'aktif',
            ]
        );

        // 5. Appearance Data (SCHEMA.md 2.6)
        Appearance::updateOrCreate(
            ['website_id' => $siteKominfo->id],
            [
                'logo' => null,
                'favicon' => null,
                'primary_color' => '#1e40af',
                'secondary_color' => '#3b82f6',
                'header_slogan' => 'Mewujudkan Kota Batu Cerdas dan Terintegrasi Melalui Transformasi Digital',
                'hero_description' => 'Penyelenggaraan tata kelola teknologi informasi, keterbukaan informasi publik, dan integrasi data kedinasan Pemerintah Kota Batu.',
                'footer_slogan' => 'Portal informasi dan layanan kedinasan resmi Pemerintah Kota Batu.',
                'footer_title' => 'Diskominfo Kota Batu',
                'footer_about_title' => 'Pemerintah Kota Batu',
                'footer_about_text' => 'Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu.',
            ]
        );

        Appearance::updateOrCreate(
            ['website_id' => $sitePariwisata->id],
            [
                'logo' => null,
                'favicon' => null,
                'primary_color' => '#047857',
                'secondary_color' => '#10b981',
                'header_slogan' => 'Kota Wisata Batu: Pesona Alam dan Wisata Ramah Keluarga',
                'hero_description' => 'Jelajahi keindahan destinasi wisata, ragam agrowisata, dan kekayaan seni budaya lokal Kota Batu.',
                'footer_slogan' => 'Portal informasi dan layanan kedinasan resmi Pemerintah Kota Batu.',
                'footer_title' => 'Dinas Pariwisata Kota Batu',
                'footer_about_title' => 'Pemerintah Kota Batu',
                'footer_about_text' => 'Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu.',
            ]
        );

        // 6. Posts Diskominfo (SCHEMA.md 2.8) — Termasuk draft untuk menguji BR-04
        Post::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'slug' => 'peluncuran-portal-layanan-digital-kota-batu'],
            [
                'user_id' => $adminKominfo->id,
                'title' => 'Peluncuran Portal Terpadu Satu Data Kota Batu',
                'content' => 'Pemerintah Kota Batu melalui Diskominfo resmi meluncurkan portal integrasi data sektoral untuk mempercepat pengambilan kebijakan berbasis data akurat dan terbuka bagi masyarakat.',
                'type' => 'Berita',
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ]
        );

        Post::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'slug' => 'jadwal-pemeliharaan-jaringan-fiber-optik'],
            [
                'user_id' => $adminKominfo->id,
                'title' => 'Pengumuman Pemeliharaan Jaringan Internet Kedinasan',
                'content' => 'Diberitahukan kepada seluruh perangkat daerah bahwa akan dilakukan pemeliharaan infrastruktur jaringan intranet kota pada Sabtu akhir pekan.',
                'type' => 'Pengumuman',
                'status' => 'published',
                'published_at' => now()->subDay(),
            ]
        );

        Post::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'slug' => 'workshop-literasi-digital-pelajar'],
            [
                'user_id' => $adminKominfo->id,
                'title' => 'Workshop Literasi Digital bagi Generasi Muda Kota Batu',
                'content' => 'Diskominfo mengadakan bimbingan teknis literasi digital dan keamanan internet sehat yang diikuti ratusan pelajar tingkat menengah atas se-Kota Batu.',
                'type' => 'Kegiatan',
                'status' => 'published',
                'published_at' => now()->subHours(5),
            ]
        );

        // Draft Post (Wajib TIDAK muncul di web publik - BR-04)
        Post::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'slug' => 'draf-rencana-strategis-diskominfo-2027'],
            [
                'user_id' => $adminKominfo->id,
                'title' => 'Draf Rahasia: Rencana Kerja Jangka Panjang Diskominfo 2027',
                'content' => 'Dokumen internal ini masih dalam tahap pembahasan dan belum siap dirilis ke masyarakat umum.',
                'type' => 'Berita',
                'status' => 'draft',
                'published_at' => null,
            ]
        );

        // 7. Posts Dinas Pariwisata (Untuk menguji isolasi tenant)
        Post::updateOrCreate(
            ['website_id' => $sitePariwisata->id, 'slug' => 'festival-batu-shining-orchid-2026'],
            [
                'user_id' => $adminPariwisata->id,
                'title' => 'Festival Anggrek Internasional Batu Shining Orchid 2026',
                'content' => 'Dinas Pariwisata mengundang wisatawan domestik maupun mancanegara untuk menghadiri pameran anggrek berskala internasional di Balai Kota Among Tani.',
                'type' => 'Berita',
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ]
        );

        // 8. Pages Statis (SCHEMA.md 2.7 & CONTENT_SPEC.md 6)
        Page::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'slug' => 'profil-diskominfo'],
            [
                'title' => 'Profil Dinas Komunikasi dan Informatika',
                'content' => 'Dinas Komunikasi dan Informatika Kota Batu mempunyai tugas membantu Wali Kota melaksanakan urusan pemerintahan yang menjadi kewenangan Daerah di bidang komunikasi, informatika, statistik dan persandian. Melalui komitmen transparansi dan inovasi, Diskominfo terus memperkuat integrasi Satu Data Kota Batu.',
                'status' => 'published',
            ]
        );

        Page::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'slug' => 'visi-misi'],
            [
                'title' => 'Visi & Misi Diskominfo Kota Batu',
                'content' => 'Visi: Mewujudkan Kota Wisata Batu yang Maju, Sejahtera, dan Berkelanjutan Berbasis Teknologi Digital Terintegrasi. Misi: Meningkatkan konektivitas infrastruktur digital antar OPD, mewujudkan keterbukaan informasi publik yang berkualitas, serta menjamin keamanan siber kedinasan.',
                'status' => 'published',
            ]
        );

        // 9. Media & Dokumen Publik (SCHEMA.md 2.9 & CONTENT_SPEC.md 5)
        Media::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'file_name' => 'SOP-Layanan-Informasi-Publik-2026.pdf'],
            [
                'user_id' => $adminKominfo->id,
                'file_path' => 'documents/sop-layanan-ikp-2026.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 1542000,
            ]
        );

        Media::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'file_name' => 'Laporan-Kinerja-LKjIP-Diskominfo-2025.pdf'],
            [
                'user_id' => $adminKominfo->id,
                'file_path' => 'documents/lkjip-diskominfo-2025.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 2890000,
            ]
        );

        Media::updateOrCreate(
            ['website_id' => $siteKominfo->id, 'file_name' => 'Peraturan-Walikota-Satu-Data-Batu.pdf'],
            [
                'user_id' => $adminKominfo->id,
                'file_path' => 'documents/perwali-satu-data-batu.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 1048576,
            ]
        );

        Media::updateOrCreate(
            ['website_id' => $sitePariwisata->id, 'file_name' => 'Brosur-Panduan-Wisata-Batu-2026.pdf'],
            [
                'user_id' => $adminPariwisata->id,
                'file_path' => 'documents/brosur-wisata-batu-2026.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 4520000,
            ]
        );
    }
}
