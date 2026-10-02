<?php

namespace App\Console\Commands;

use App\Models\Template;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'make:super-admin
                            {--name= : Nama lengkap Super Admin}
                            {--email= : Alamat email Super Admin}
                            {--password= : Kata sandi Super Admin}';

    protected $description = 'Buat akun Super Admin baru secara aman untuk produksi';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nama Lengkap Super Admin');
        $email = $this->option('email') ?: $this->ask('Alamat Email');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Kata Sandi (minimal 8 karakter)');
            $passwordConfirmation = $this->secret('Konfirmasi Kata Sandi');

            if ($password !== $passwordConfirmation) {
                $this->error('Konfirmasi kata sandi tidak cocok.');

                return self::FAILURE;
            }
        }

        if (strlen((string) $password) < 8) {
            $this->error('Kata sandi minimal harus 8 karakter.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'status' => 'aktif',
            'dinas_id' => null,
        ]);

        $this->ensureDefaultTemplateExists();

        $this->info("Super Admin [{$user->email}] berhasil dibuat.");

        return self::SUCCESS;
    }

    protected function ensureDefaultTemplateExists(): void
    {
        if (Template::count() === 0) {
            Template::create([
                'name' => 'Portal Resmi Kedinasan',
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
            ]);
        }
    }
}
