<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SystemSetting;
use App\Models\Template;
use App\Models\User;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminActivityController extends Controller
{
    /**
     * Tampilkan riwayat seluruh aktivitas platform (Super Admin only).
     */
    public function index(Request $request)
    {
        $activities = $this->collectActivities();

        // 1. Filter Kategori
        $category = $request->query('category');
        if (! empty($category)) {
            $activities = $activities->where('category_key', $category);
        }

        // 2. Filter Pencarian
        $search = $request->query('search');
        if (! empty($search)) {
            $searchLower = strtolower($search);
            $activities = $activities->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['title']), $searchLower)
                    || str_contains(strtolower($item['description']), $searchLower)
                    || str_contains(strtolower($item['category']), $searchLower);
            });
        }

        // 3. Urutkan berdasarkan waktu terbaru
        $sorted = $activities->sortByDesc(fn ($item) => $item['timestamp']->timestamp)->values();

        // 4. Pagination
        $perPage = 12;
        $currentPage = (int) $request->input('page', 1);
        $total = $sorted->count();
        $pagedItems = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pagedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => route('admin.activities.index'), 'query' => $request->query()]
        );

        return view('admin.activities.index', [
            'activities' => $paginator,
            'selectedCategory' => $category,
            'search' => $search,
            'totalCount' => $total,
        ]);
    }

    /**
     * Himpun seluruh data aktivitas riil dari model-model platform.
     */
    protected function collectActivities(): Collection
    {
        $list = collect();

        // 1. Aktivitas Pengguna (User)
        $users = User::with('dinas')->latest('updated_at')->take(30)->get();
        foreach ($users as $user) {
            $roleLabel = $user->role === 'super_admin' ? 'Super Admin' : 'Admin Kedinasan';
            $dinasSuffix = $user->dinas ? " ({$user->dinas->name})" : '';

            $isCreatedOnly = $user->created_at->equalTo($user->updated_at);

            $list->push([
                'id' => 'user_' . $user->id,
                'category' => 'Pengguna',
                'category_key' => 'user',
                'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'icon_type' => 'user',
                'title' => $isCreatedOnly
                    ? "Akun {$roleLabel} baru ditambahkan"
                    : "Akun {$roleLabel} diperbarui",
                'description' => "Pengguna: {$user->name} ({$user->email}){$dinasSuffix}. Status: {$user->status}.",
                'timestamp' => $user->updated_at,
                'url' => route('admin.users.edit', $user),
                'action_label' => 'Kelola Pengguna',
            ]);
        }

        // 2. Aktivitas Website Dinas
        $websites = Website::with(['dinas', 'template'])->latest('updated_at')->take(30)->get();
        foreach ($websites as $web) {
            $dinasName = $web->dinas?->name ?? 'Instansi Kedinasan';
            $isCreatedOnly = $web->created_at->equalTo($web->updated_at);

            $list->push([
                'id' => 'website_' . $web->id,
                'category' => 'Website',
                'category_key' => 'website',
                'badge_color' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                'icon_bg' => 'bg-cyan-50 text-cyan-600',
                'icon_type' => 'globe',
                'title' => $isCreatedOnly
                    ? "Portal website {$web->name} didaftarkan"
                    : "Konfigurasi website {$web->name} diperbarui",
                'description' => "Instansi: {$dinasName} · Domain: /site/{$web->domain} · Status: {$web->status} · Template: " . ($web->template?->name ?? 'Standar'),
                'timestamp' => $web->updated_at,
                'url' => route('admin.websites.edit', $web),
                'action_label' => 'Kelola Website',
            ]);
        }

        // 3. Aktivitas Template Builder
        $templates = Template::latest('updated_at')->take(10)->get();
        foreach ($templates as $tpl) {
            $list->push([
                'id' => 'template_' . $tpl->id,
                'category' => 'Template',
                'category_key' => 'template',
                'badge_color' => 'bg-blue-50 text-blue-700 border-blue-200',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'icon_type' => 'template',
                'title' => "Template \"{$tpl->name}\" diperbarui",
                'description' => "Blueprint versi {$tpl->template_version}. Status: {$tpl->status}.",
                'timestamp' => $tpl->updated_at,
                'url' => route('admin.templates.builder', $tpl),
                'action_label' => 'Buka Builder',
            ]);
        }

        // 4. Aktivitas Pengaturan Sistem (SystemSetting)
        $systemSettings = SystemSetting::latest('updated_at')->take(5)->get();
        foreach ($systemSettings as $ss) {
            $valPreview = Str::limit($ss->value ?? '', 50);
            $list->push([
                'id' => 'setting_' . $ss->id,
                'category' => 'Sistem',
                'category_key' => 'system',
                'badge_color' => 'bg-slate-100 text-slate-700 border-slate-200',
                'icon_bg' => 'bg-slate-100 text-slate-600',
                'icon_type' => 'gear',
                'title' => "Pengaturan platform \"{$ss->key}\" diperbarui",
                'description' => "Parameter \"{$ss->key}\" diatur menjadi \"{$valPreview}\" oleh Super Admin.",
                'timestamp' => $ss->updated_at,
                'url' => route('admin.settings.edit'),
                'action_label' => 'Buka Pengaturan',
            ]);
        }

        // 5. Aktivitas Publikasi Konten (Post)
        $posts = Post::with(['website.dinas', 'user'])->latest('updated_at')->take(30)->get();
        foreach ($posts as $post) {
            $dinasName = $post->website?->dinas?->name ?? 'Kedinasan';
            $statusText = $post->status === 'published' ? 'dipublikasikan' : 'disimpan sebagai draft';

            $list->push([
                'id' => 'post_' . $post->id,
                'category' => 'Konten',
                'category_key' => 'content',
                'badge_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'icon_type' => 'document',
                'title' => "Artikel \"{$post->title}\" {$statusText}",
                'description' => "Kategori: {$post->type} · Instansi: {$dinasName} · Penulis: " . ($post->user?->name ?? 'Admin'),
                'timestamp' => $post->updated_at,
                'url' => $post->website ? route('site.post.show', ['identifier' => $post->website->domain, 'slug' => $post->slug]) : '#',
                'action_label' => 'Lihat Artikel',
            ]);
        }

        return $list;
    }
}
