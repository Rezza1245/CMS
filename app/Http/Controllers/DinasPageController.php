<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Page;
use App\Services\MediaOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DinasPageController extends Controller
{
    /**
     * Tampilkan daftar halaman institusional milik website dinas aktif (Admin Kedinasan only).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola halaman statis.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $query = $website->pages()->with(['parent.parent']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $rawPages = $query->get();
        $sortedPages = $this->sortPagesHierarchically($rawPages);

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $sortedPages->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $pages = new LengthAwarePaginator(
            $currentItems,
            $sortedPages->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('dinas.pages.index', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'pages' => $pages,
        ]);
    }

    /**
     * Formulir pembuatan halaman baru.
     */
    public function create()
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola halaman statis.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $eligibleParents = $this->getEligibleParents($website);

        return view('dinas.pages.create', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'eligibleParents' => $eligibleParents,
        ]);
    }

    /**
     * Simpan halaman baru.
     */
    public function store(Request $request, MediaOptimizerService $optimizer)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola halaman statis.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $placement = $request->input('placement', 'header_menu');
        $parentId = $request->input('parent_id');

        if ($request->filled('placement_target')) {
            $target = $request->input('placement_target');
            if (str_starts_with($target, 'parent_')) {
                $parentId = (int) Str::after($target, 'parent_');
                $placement = 'sub_menu';
            } else {
                $placement = $target;
                $parentId = null;
            }
        } elseif (! empty($parentId)) {
            $placement = 'sub_menu';
        }

        $request->merge([
            'placement' => $placement,
            'parent_id' => $parentId,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'placement' => ['required', 'string', Rule::in(Page::PLACEMENTS)],
            'direct_link' => [
                'nullable',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $isUrl = filter_var($value, FILTER_VALIDATE_URL);
                        $isMailto = str_starts_with($value, 'mailto:');
                        $isTel = str_starts_with($value, 'tel:');
                        if (! $isUrl && ! $isMailto && ! $isTel) {
                            $fail('Direct link harus berupa URL web valid (https://...), tautan mailto:, atau nomor tel:.');
                        }
                    }
                },
            ],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('pages', 'id')->where('website_id', $website->id),
            ],
        ]);

        // Khusus menu utama header (Level 1), gambar dan direct link tidak berlaku (hanya untuk sub-menu)
        $isHeaderMenu = ($validated['placement'] === 'header_menu' && empty($validated['parent_id']));
        $directLink = $isHeaderMenu ? null : ($validated['direct_link'] ?? null);

        // Upload Gambar Halaman & catat ke tabel media (otomatis konversi WebP) — hanya jika bukan header_menu
        $image = null;
        if (! $isHeaderMenu && $request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $optimized = $optimizer->optimizeAndStore($file, 'pages', 'public');
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

        // Validasi kedalaman menu: Maksimal parent level 3 (anak menjadi level 4 / item wadah)
        if (! empty($validated['parent_id'])) {
            $parent = $website->pages()->with('parent.parent')->findOrFail($validated['parent_id']);
            if ($parent->getDepth() >= 4) {
                return back()->withErrors([
                    'parent_id' => 'Halaman di dalam wadah tidak dapat memiliki sub-halaman lagi.',
                ])->withInput();
            }
        }

        // Buat slug unik dalam scope website tenant ini (SCHEMA 4.7)
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug ?: Str::random(8);
        $counter = 1;

        while ($website->pages()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $website->pages()->create([
            'parent_id' => $validated['parent_id'] ?? null,
            'placement' => $validated['placement'],
            'image' => $image,
            'direct_link' => $directLink,
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('dinas.pages.index')
            ->with('success', 'Halaman berhasil ditambahkan' . ($validated['status'] === 'published' ? ' dan langsung aktif sesuai target penempatan.' : ' sebagai draft.'));
    }

    /**
     * Formulir edit halaman.
     */
    public function edit(Page $page)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola halaman statis.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        // Validasi kepemilikan tenant (AGENTS.md Bab 6)
        if (! $website || $page->website_id !== $website->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengedit halaman milik dinas lain.');
        }

        $eligibleParents = $this->getEligibleParents($website, $page);

        return view('dinas.pages.edit', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'page' => $page,
            'eligibleParents' => $eligibleParents,
        ]);
    }

    /**
     * Perbarui data halaman.
     */
    public function update(Request $request, Page $page, MediaOptimizerService $optimizer)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola halaman statis.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        // Validasi kepemilikan tenant (AGENTS.md Bab 6)
        if (! $website || $page->website_id !== $website->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengedit halaman milik dinas lain.');
        }

        $placement = $request->input('placement', $page->placement ?? 'header_menu');
        $parentId = $request->input('parent_id');

        if ($request->filled('placement_target')) {
            $target = $request->input('placement_target');
            if (str_starts_with($target, 'parent_')) {
                $parentId = (int) Str::after($target, 'parent_');
                $placement = 'sub_menu';
            } else {
                $placement = $target;
                $parentId = null;
            }
        } elseif (! empty($parentId)) {
            $placement = 'sub_menu';
        }

        $request->merge([
            'placement' => $placement,
            'parent_id' => $parentId,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'placement' => ['required', 'string', Rule::in(Page::PLACEMENTS)],
            'direct_link' => [
                'nullable',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $isUrl = filter_var($value, FILTER_VALIDATE_URL);
                        $isMailto = str_starts_with($value, 'mailto:');
                        $isTel = str_starts_with($value, 'tel:');
                        if (! $isUrl && ! $isMailto && ! $isTel) {
                            $fail('Direct link harus berupa URL web valid (https://...), tautan mailto:, atau nomor tel:.');
                        }
                    }
                },
            ],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('pages', 'id')->where('website_id', $website->id),
            ],
        ]);

        $parentId = $validated['parent_id'] ?? null;
        $isHeaderMenu = ($validated['placement'] === 'header_menu' && empty($parentId));
        $directLink = $isHeaderMenu ? null : ($validated['direct_link'] ?? null);

        // Penanganan Gambar Halaman
        $image = $page->image;
        if ($isHeaderMenu) {
            // Hapus gambar jika beralih ke header_menu
            if ($image && Storage::disk('public')->exists($image)) {
                Storage::disk('public')->delete($image);
            }
            $image = null;
        } elseif ($request->boolean('remove_image')) {
            if ($image && Storage::disk('public')->exists($image)) {
                Storage::disk('public')->delete($image);
            }
            $image = null;
        } elseif ($request->hasFile('image_file')) {
            if ($image && Storage::disk('public')->exists($image)) {
                Storage::disk('public')->delete($image);
            }

            $file = $request->file('image_file');
            $optimized = $optimizer->optimizeAndStore($file, 'pages', 'public');
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

        // Cegah circular reference (halaman tidak boleh menjadi anak dari dirinya sendiri atau keturunannya)
        if ($parentId) {
            if ($parentId === $page->id) {
                return back()->withErrors([
                    'parent_id' => 'Halaman tidak boleh menjadi induk dari dirinya sendiri.',
                ])->withInput();
            }

            $descendantIds = $this->getDescendantIds($page);
            if (in_array($parentId, $descendantIds, true)) {
                return back()->withErrors([
                    'parent_id' => 'Halaman tidak boleh menjadi sub-menu di bawah anak halamannya sendiri.',
                ])->withInput();
            }

            // Validasi kedalaman menu: Maksimal parent level 3
            $parent = $website->pages()->with('parent.parent')->findOrFail($parentId);
            if ($parent->getDepth() >= 4) {
                return back()->withErrors([
                    'parent_id' => 'Halaman di dalam wadah tidak dapat memiliki sub-halaman lagi.',
                ])->withInput();
            }

            // Jika halaman ini memiliki anak, cek apakah kedalaman total tidak melebihi 4 level
            $maxSubtreeDepth = $this->getMaxSubtreeDepth($page);
            if ($parent->getDepth() + $maxSubtreeDepth > 4) {
                return back()->withErrors([
                    'parent_id' => 'Halaman ini telah memiliki sub-halaman. Jika dipindahkan ke level ini, hierarki anak akan melebihi batas wadah.',
                ])->withInput();
            }
        }

        $slug = $page->slug;
        if ($page->title !== $validated['title']) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug ?: Str::random(8);
            $counter = 1;

            while ($website->pages()->where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
        }

        $page->update([
            'parent_id' => $parentId,
            'placement' => $validated['placement'],
            'image' => $image,
            'direct_link' => $directLink,
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('dinas.pages.index')
            ->with('success', 'Halaman berhasil diperbarui.');
    }

    /**
     * Dapatkan daftar calon induk halaman yang memenuhi syarat (maks level 3 sebagai wadah).
     */
    protected function getEligibleParents($website, ?Page $excludePage = null)
    {
        $excludeIds = [];
        if ($excludePage) {
            $excludeIds = array_merge([$excludePage->id], $this->getDescendantIds($excludePage));
        }

        return $website->pages()
            ->whereNotIn('id', $excludeIds)
            ->where(function ($query) {
                // Level 1, Level 2, dan Level 3 (wadah) boleh menjadi parent
                $query->whereNull('parent_id')
                    ->orWhereHas('parent', function ($q) {
                        $q->whereNull('parent_id')
                            ->orWhereHas('parent', fn ($q2) => $q2->whereNull('parent_id'));
                    });
            })
            ->with(['parent.parent'])
            ->orderBy('title')
            ->get();
    }

    /**
     * Hitung tinggi maksimum pohon turunan dari halaman (inklusif dirinya sendiri).
     */
    protected function getMaxSubtreeDepth(Page $page): int
    {
        $children = $page->children;
        if ($children->isEmpty()) {
            return 1;
        }

        $maxChild = 0;
        foreach ($children as $child) {
            $maxChild = max($maxChild, $this->getMaxSubtreeDepth($child));
        }

        return 1 + $maxChild;
    }

    /**
     * Dapatkan seluruh ID turunan (anak dan cucu) dari suatu halaman secara rekursif.
     */
    protected function getDescendantIds(Page $page): array
    {
        $ids = [];
        $children = $page->children;

        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }

        return $ids;
    }

    /**
     * Hapus halaman.
     */
    public function destroy(Page $page)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola halaman statis.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        // Validasi kepemilikan tenant (AGENTS.md Bab 6)
        if (! $website || $page->website_id !== $website->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk menghapus halaman milik dinas lain.');
        }

        if ($page->image && Storage::disk('public')->exists($page->image)) {
            Storage::disk('public')->delete($page->image);
        }

        $page->delete();

        return redirect()->route('dinas.pages.index')
            ->with('success', 'Halaman berhasil dihapus.');
    }

    /**
     * Urutkan halaman statis secara hierarkis:
     * 1. Tab Beranda selalu di paling awal.
     * 2. Per Menu Utama (Header Root):
     *    - Menu Utama itu sendiri (Level 1)
     *    - Seluruh Sub Menu miliknya (Level 2: published & draft tetap sejajar di level ini)
     *    - Seluruh Sub Sub Menu miliknya (Level 3: published & draft)
     *    - Seluruh Isi Wadah (Level 4, jika ada)
     */
    protected function sortPagesHierarchically(\Illuminate\Support\Collection $pages): \Illuminate\Support\Collection
    {
        if ($pages->isEmpty()) {
            return collect();
        }

        // 1. Tab Beranda selalu di awal
        $berandaPages = $pages->filter(fn ($p) => $p->placement === 'beranda')->sortBy('id')->values();

        // 2. Halaman non-beranda
        $nonBeranda = $pages->filter(fn ($p) => $p->placement !== 'beranda');

        // Helper untuk mencari root ancestor ID
        $findRootId = function (Page $page) {
            $current = $page;
            $guard = 0;
            while ($current->parent_id !== null && $current->parent && $guard < 10) {
                $current = $current->parent;
                $guard++;
            }
            return $current->id;
        };

        // Kelompokkan per root_id
        $groups = $nonBeranda->groupBy($findRootId)->sortBy(fn ($group, $rootId) => $rootId);

        $result = collect($berandaPages);

        foreach ($groups as $rootId => $groupPages) {
            // Level 1: Menu Utama (Root itu sendiri jika ada di group)
            $level1 = $groupPages->filter(fn ($p) => $p->getDepth() === 1)->sortBy('id')->values();
            foreach ($level1 as $p) {
                $result->push($p);
            }

            // Level 2: Sub Menu (publish & draft tetap sejajar di level ini, di atas sub-sub menu)
            $level2 = $groupPages->filter(fn ($p) => $p->getDepth() === 2)->sortBy('id')->values();
            foreach ($level2 as $p) {
                $result->push($p);
            }

            // Level 3: Sub Sub Menu (dikelompokkan per parent_id sub-menunya, lalu id asc)
            $level3 = $groupPages->filter(fn ($p) => $p->getDepth() === 3)->sortBy(fn ($p) => $p->parent_id . '_' . $p->id)->values();
            foreach ($level3 as $p) {
                $result->push($p);
            }

            // Level 4: Isi Wadah (dikelompokkan per parent_id sub-sub-menunya, lalu id asc)
            $level4 = $groupPages->filter(fn ($p) => $p->getDepth() === 4)->sortBy(fn ($p) => $p->parent_id . '_' . $p->id)->values();
            foreach ($level4 as $p) {
                $result->push($p);
            }

            // Level 5+ jika ada
            $deeper = $groupPages->filter(fn ($p) => $p->getDepth() > 4)->sortBy('id')->values();
            foreach ($deeper as $p) {
                $result->push($p);
            }
        }

        return $result;
    }
}
