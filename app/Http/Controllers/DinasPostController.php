<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Post;
use App\Services\MediaOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DinasPostController extends Controller
{
    /**
     * Tampilkan daftar publikasi artikel/berita milik website dinas aktif (Admin Kedinasan only).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola artikel publikasi.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $query = $website->posts()->with(['user', 'page'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('placement')) {
            $query->where('placement', $request->query('placement'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(10)->withQueryString();

        return view('dinas.posts.index', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'posts' => $posts,
        ]);
    }

    /**
     * Formulir pembuatan artikel baru.
     */
    public function create()
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola artikel publikasi.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        // Hanya published pages milik dinas ini, disusun hierarkis dengan relasi parent.parent
        $eligiblePages = $this->getStructuredEligiblePages($website);

        return view('dinas.posts.create', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'eligiblePages' => $eligiblePages,
        ]);
    }

    /**
     * Simpan publikasi artikel baru.
     */
    public function store(Request $request, MediaOptimizerService $optimizer)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola artikel publikasi.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $placement = $request->input('placement', 'beranda');
        $pageId = $request->input('page_id');

        if ($request->filled('placement_target')) {
            $target = $request->input('placement_target');
            if (str_starts_with($target, 'page_')) {
                $pageId = (int) Str::after($target, 'page_');
                $placement = 'sub_page';
            } elseif ($target === 'beranda') {
                $placement = 'beranda';
                $pageId = null;
            } else {
                $placement = $target;
                $pageId = null;
            }
        } elseif (! empty($pageId)) {
            $placement = 'sub_page';
        }

        $request->merge([
            'placement' => $placement,
            'page_id' => $pageId,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['Berita', 'Pengumuman', 'Kegiatan'])],
            'placement' => ['required', 'string', Rule::in(Post::PLACEMENTS)],
            'page_id' => [
                'nullable',
                'integer',
                Rule::exists('pages', 'id')->where('website_id', $website->id),
            ],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'direct_link' => ['nullable', 'url', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Upload Thumbnail Gambar & catat ke tabel media (otomatis konversi WebP)
        $image = null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $optimized = $optimizer->optimizeAndStore($file, 'posts', 'public');
            $image = $optimized['path'];

            Media::create([
                'website_id' => $website->id,
                'user_id' => $user->id,
                'file_name' => $optimized['file_name'],
                'file_path' => $optimized['path'],
                'file_type' => $optimized['file_type'],
                'file_size' => $optimized['file_size'],
            ]);
        }

        // Buat slug unik dalam scope website tenant ini (SCHEMA 4.8)
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug ?: Str::random(8);
        $counter = 1;

        while ($website->posts()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $publishedAt = ($validated['status'] === 'published') ? now() : null;

        $website->posts()->create([
            'user_id' => $user->id,
            'page_id' => $validated['page_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'type' => $validated['type'],
            'placement' => $validated['placement'],
            'image' => $image,
            'direct_link' => $validated['direct_link'] ?? null,
            'content' => $validated['content'],
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('dinas.posts.index')
            ->with('success', 'Artikel berhasil ditambahkan' . ($validated['status'] === 'published' ? ' dan langsung tampil di website publik.' : ' sebagai draft.'));
    }

    /**
     * Formulir edit artikel publikasi.
     */
    public function edit(Post $post)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola artikel publikasi.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        // Validasi kepemilikan tenant (AGENTS.md Bab 6)
        if (! $website || $post->website_id !== $website->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengedit artikel milik dinas lain.');
        }

        // Hanya published pages milik dinas ini, disusun hierarkis dengan relasi parent.parent
        $eligiblePages = $this->getStructuredEligiblePages($website);

        return view('dinas.posts.edit', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'post' => $post,
            'eligiblePages' => $eligiblePages,
        ]);
    }

    /**
     * Perbarui data artikel publikasi.
     */
    public function update(Request $request, Post $post, MediaOptimizerService $optimizer)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola artikel publikasi.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        // Validasi kepemilikan tenant (AGENTS.md Bab 6)
        if (! $website || $post->website_id !== $website->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengedit artikel milik dinas lain.');
        }

        $placement = $request->input('placement', $post->placement ?? 'beranda');
        $pageId = $request->input('page_id', $post->page_id);

        if ($request->filled('placement_target')) {
            $target = $request->input('placement_target');
            if (str_starts_with($target, 'page_')) {
                $pageId = (int) Str::after($target, 'page_');
                $placement = 'sub_page';
            } elseif ($target === 'beranda') {
                $placement = 'beranda';
                $pageId = null;
            } else {
                $placement = $target;
                $pageId = null;
            }
        } elseif (! empty($pageId)) {
            $placement = 'sub_page';
        }

        $request->merge([
            'placement' => $placement,
            'page_id' => $pageId,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['Berita', 'Pengumuman', 'Kegiatan'])],
            'placement' => ['required', 'string', Rule::in(Post::PLACEMENTS)],
            'page_id' => [
                'nullable',
                'integer',
                Rule::exists('pages', 'id')->where('website_id', $website->id),
            ],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'direct_link' => ['nullable', 'url', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        // Penanganan Gambar Thumbnail
        $image = $post->image;
        if ($request->boolean('remove_image')) {
            if ($image && Storage::disk('public')->exists($image)) {
                Storage::disk('public')->delete($image);
            }
            $image = null;
        } elseif ($request->hasFile('image_file')) {
            if ($image && Storage::disk('public')->exists($image)) {
                Storage::disk('public')->delete($image);
            }

            $file = $request->file('image_file');
            $optimized = $optimizer->optimizeAndStore($file, 'posts', 'public');
            $image = $optimized['path'];

            Media::create([
                'website_id' => $website->id,
                'user_id' => $user->id,
                'file_name' => $optimized['file_name'],
                'file_path' => $optimized['path'],
                'file_type' => $optimized['file_type'],
                'file_size' => $optimized['file_size'],
            ]);
        }

        // Perbarui slug jika judul berubah
        $slug = $post->slug;
        if ($post->title !== $validated['title']) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug ?: Str::random(8);
            $counter = 1;

            while ($website->posts()->where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
        }

        // Tentukan timestamp publikasi
        $publishedAt = $post->published_at;
        if ($validated['status'] === 'published' && ! $publishedAt) {
            $publishedAt = now();
        } elseif ($validated['status'] === 'draft') {
            $publishedAt = null;
        }

        $post->update([
            'page_id' => $validated['page_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'type' => $validated['type'],
            'placement' => $validated['placement'],
            'image' => $image,
            'direct_link' => $validated['direct_link'] ?? null,
            'content' => $validated['content'],
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('dinas.posts.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    /**
     * Hapus artikel publikasi.
     */
    public function destroy(Post $post)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola artikel publikasi.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        // Validasi kepemilikan tenant (AGENTS.md Bab 6)
        if (! $website || $post->website_id !== $website->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk menghapus artikel milik dinas lain.');
        }

        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()->route('dinas.posts.index')
            ->with('success', 'Artikel berhasil dihapus.');
    }

    /**
     * Dapatkan daftar published pages tersusun hierarkis (tree-flattened) dengan relasi parent.parent.
     */
    protected function getStructuredEligiblePages($website)
    {
        $allPages = $website->publishedPages()
            ->with(['parent.parent'])
            ->orderBy('id')
            ->get();

        $grouped = $allPages->groupBy('parent_id');
        $result = collect();

        $flattenTree = function ($parentId) use (&$flattenTree, $grouped, &$result) {
            foreach ($grouped->get($parentId, collect()) as $page) {
                $result->push($page);
                $flattenTree($page->id);
            }
        };

        // Mulai dari level 1 (parent_id null)
        $flattenTree(null);

        // Tambahkan halaman yatim jika ada
        $addedIds = $result->pluck('id')->all();
        foreach ($allPages as $page) {
            if (! in_array($page->id, $addedIds, true)) {
                $result->push($page);
            }
        }

        return $result;
    }
}
