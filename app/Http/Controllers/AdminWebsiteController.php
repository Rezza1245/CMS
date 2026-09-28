<?php

namespace App\Http\Controllers;

use App\Models\Appearance;
use App\Models\Dinas;
use App\Models\Setting;
use App\Models\Template;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminWebsiteController extends Controller
{
    /**
     * Tampilkan daftar seluruh website dinas (Super Admin only).
     */
    public function index(Request $request)
    {
        $query = Website::with(['dinas', 'template'])->withCount(['posts', 'publishedPages as pages_count'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhereHas('dinas', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $websites = $query->paginate(10)->withQueryString();

        return view('admin.websites.index', [
            'websites' => $websites,
        ]);
    }

    /**
     * Tampilkan formulir pembuatan website dan dinas baru.
     */
    public function create()
    {
        $templates = Template::where('status', 'aktif')->orderBy('id')->get();
        if ($templates->isEmpty()) {
            $templates = Template::orderBy('id')->get();
        }

        return view('admin.websites.create', [
            'templates' => $templates,
        ]);
    }

    /**
     * Simpan website dinas baru beserta relasi dinas, appearance, dan settings.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dinas_name' => ['required', 'string', 'max:255'],
            'dinas_code' => ['required', 'string', 'max:50', 'unique:dinas,code'],
            'dinas_address' => ['nullable', 'string'],
            'dinas_email' => ['nullable', 'email', 'max:255'],
            'dinas_phone' => ['nullable', 'string', 'max:50'],
            'website_name' => ['required', 'string', 'max:255'],
            'domain' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:websites,domain',
            ],
            'template_id' => ['required', 'exists:templates,id'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'dinas_code.unique' => 'Kode instansi sudah terdaftar.',
            'domain.unique' => 'Domain/subdomain website sudah digunakan.',
            'domain.alpha_dash' => 'Domain hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
        ]);

        $domain = Str::lower(trim($validated['domain']));

        DB::transaction(function () use ($validated, $domain) {
            // 1. Buat Dinas
            $dinas = Dinas::create([
                'name' => $validated['dinas_name'],
                'code' => strtoupper(trim($validated['dinas_code'])),
                'address' => $validated['dinas_address'] ?? null,
                'contact_email' => $validated['dinas_email'] ?? null,
                'phone' => $validated['dinas_phone'] ?? null,
            ]);

            // 2. Buat Website (1:1 dengan dinas)
            $website = Website::create([
                'dinas_id' => $dinas->id,
                'template_id' => $validated['template_id'],
                'domain' => $domain,
                'name' => $validated['website_name'],
                'status' => $validated['status'],
            ]);

            // 3. Inisialisasi Slot Appearance
            Appearance::create([
                'website_id' => $website->id,
                'header_slogan' => "Website Resmi {$dinas->name} Kota Batu",
                'hero_description' => 'Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu.',
                'footer_title' => $dinas->name,
                'footer_about_title' => 'Tentang Kami',
                'footer_about_text' => $dinas->address ?? 'Pemerintah Kota Batu, Jawa Timur',
            ]);

            // 4. Inisialisasi Setting
            Setting::create([
                'website_id' => $website->id,
                'general_config' => [
                    'site_name' => $website->name,
                    'contact_email' => $dinas->contact_email,
                    'contact_phone' => $dinas->phone,
                    'address' => $dinas->address,
                ],
            ]);
        });

        return redirect()->route('admin.websites.index')
            ->with('success', "Website dan Dinas {$validated['dinas_name']} berhasil didaftarkan.");
    }

    /**
     * Tampilkan formulir ubah data website dan dinas.
     */
    public function edit(Website $website)
    {
        $website->load(['dinas', 'template']);
        $templates = Template::where('status', 'aktif')->orderBy('id')->get();
        if ($templates->isEmpty()) {
            $templates = Template::orderBy('id')->get();
        }

        return view('admin.websites.edit', [
            'website' => $website,
            'dinas' => $website->dinas,
            'templates' => $templates,
        ]);
    }

    /**
     * Simpan pembaruan data website dan dinas.
     */
    public function update(Request $request, Website $website)
    {
        $dinas = $website->dinas;

        $validated = $request->validate([
            'dinas_name' => ['required', 'string', 'max:255'],
            'dinas_code' => ['required', 'string', 'max:50', Rule::unique('dinas', 'code')->ignore($dinas?->id)],
            'dinas_address' => ['nullable', 'string'],
            'dinas_email' => ['nullable', 'email', 'max:255'],
            'dinas_phone' => ['nullable', 'string', 'max:50'],
            'website_name' => ['required', 'string', 'max:255'],
            'domain' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('websites', 'domain')->ignore($website->id),
            ],
            'template_id' => ['required', 'exists:templates,id'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'dinas_code.unique' => 'Kode instansi sudah terdaftar.',
            'domain.unique' => 'Domain/subdomain website sudah digunakan.',
            'domain.alpha_dash' => 'Domain hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
        ]);

        $domain = Str::lower(trim($validated['domain']));

        DB::transaction(function () use ($website, $dinas, $validated, $domain) {
            if ($dinas) {
                $dinas->update([
                    'name' => $validated['dinas_name'],
                    'code' => strtoupper(trim($validated['dinas_code'])),
                    'address' => $validated['dinas_address'] ?? null,
                    'contact_email' => $validated['dinas_email'] ?? null,
                    'phone' => $validated['dinas_phone'] ?? null,
                ]);
            }

            $website->update([
                'template_id' => $validated['template_id'],
                'domain' => $domain,
                'name' => $validated['website_name'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('admin.websites.index')
            ->with('success', "Data website {$website->name} berhasil diperbarui.");
    }

    /**
     * Hapus website dan dinas terkait secara aman.
     */
    public function destroy(Website $website)
    {
        $dinas = $website->dinas;
        $name = $website->name;

        DB::transaction(function () use ($website, $dinas) {
            // Hapus website terlebih dahulu (cascadeOnDelete akan bersihkan appearance, pages, posts, media, settings)
            $website->delete();

            // Hapus dinas jika tidak memiliki website lain (1:1)
            if ($dinas) {
                // Set null dinas_id pada user yang terikat ke dinas ini agar akun tidak rusak
                $dinas->users()->update(['dinas_id' => null, 'status' => 'nonaktif']);
                $dinas->delete();
            }
        });

        return redirect()->route('admin.websites.index')
            ->with('success', "Website {$name} beserta instansi kedinasannya berhasil dihapus.");
    }
}
