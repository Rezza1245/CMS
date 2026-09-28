<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\CanvasRenderer;
use Illuminate\Http\Request;

class PublicWebsiteController extends Controller
{
    public function __construct(
        protected CanvasRenderer $renderer
    ) {}

    /**
     * Resolusi website dinas dan validasi status operasional.
     * Jika website berstatus pemeliharaan, masyarakat umum diblokir dan menerima layar 503.
     * Admin kedinasan pemilik website atau super admin diizinkan melakukan pratinjau internal.
     */
    protected function resolveWebsite(string $identifier): array
    {
        $website = Website::with(['dinas', 'template', 'appearance'])
            ->where(function ($query) use ($identifier) {
                $query->where('domain', $identifier)
                    ->orWhere('id', is_numeric($identifier) ? (int) $identifier : null);
            })
            ->firstOrFail();

        if ($website->status === 'nonaktif') {
            abort(404);
        }

        if ($website->status === 'pemeliharaan') {
            $user = auth()->user();
            $canPreview = $user && (
                $user->role === 'super_admin' ||
                ($user->role === 'admin_dinas' && (int) $user->dinas_id === (int) $website->dinas_id)
            );

            if (! $canPreview) {
                return [$website, response()->view('public.maintenance', ['website' => $website], 503)];
            }
        }

        return [$website, null];
    }

    /**
     * Tampilkan halaman publik website dinas berdasarkan identifier (domain/slug).
     */
    public function show(string $identifier)
    {
        [$website, $maintenanceResponse] = $this->resolveWebsite($identifier);
        if ($maintenanceResponse) {
            return $maintenanceResponse;
        }

        // Render struktur template dinamis via CanvasRenderer
        $renderedContent = $this->renderer->render($website);

        return view('public.index', [
            'website' => $website,
            'renderedContent' => $renderedContent,
        ]);
    }

    /**
     * Tampilkan halaman statis lengkap (Profil, Visi Misi, dsb) milik website dinas.
     */
    public function showPage(string $identifier, string $slug)
    {
        [$website, $maintenanceResponse] = $this->resolveWebsite($identifier);
        if ($maintenanceResponse) {
            return $maintenanceResponse;
        }

        // Pastikan hanya halaman berstatus published milik dinas ini yang dapat diakses (BR-01 & BR-04)
        $page = $website->publishedPages()
            ->where('slug', $slug)
            ->with(['publishedChildren', 'parent.parent.parent'])
            ->firstOrFail();

        // Jika halaman merupakan isi dari wadah sub-sub-bab, arahkan langsung ke halaman wadah induknya
        if ($page->isContainerItem() && $page->parent) {
            return redirect()->route('site.page.show', [
                'identifier' => $identifier,
                'slug' => $page->parent->slug,
            ]);
        }

        $otherPages = $website->publishedPages()
            ->where('id', '!=', $page->id)
            ->whereNull('parent_id')
            ->get();

        return view('public.page', [
            'website' => $website,
            'page' => $page,
            'otherPages' => $otherPages,
            'renderedFooter' => $this->renderer->renderFooter($website),
        ]);
    }

    /**
     * Tampilkan detail artikel/berita publik milik website dinas.
     */
    public function showPost(string $identifier, string $slug)
    {
        [$website, $maintenanceResponse] = $this->resolveWebsite($identifier);
        if ($maintenanceResponse) {
            return $maintenanceResponse;
        }

        // Pastikan hanya post berstatus published milik dinas ini yang dapat diakses (BR-01 & BR-04)
        $post = $website->publishedPosts()
            ->where('slug', $slug)
            ->firstOrFail();

        $otherPosts = $website->publishedPosts()
            ->where('id', '!=', $post->id)
            ->take(4)
            ->get();

        return view('public.post', [
            'website' => $website,
            'post' => $post,
            'otherPosts' => $otherPosts,
            'renderedFooter' => $this->renderer->renderFooter($website),
        ]);
    }

    /**
     * Halaman beranda publik default (mengambil website dinas aktif pertama).
     */
    public function home()
    {
        $website = Website::with(['dinas', 'template', 'appearance'])
            ->where('status', 'aktif')
            ->first();

        if (! $website) {
            return redirect()->route('login');
        }

        return redirect()->route('site.show', ['identifier' => $website->domain]);
    }
}
