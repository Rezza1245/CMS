<?php

namespace App\Http\Controllers;

use App\Models\Appearance;
use App\Models\Media;
use App\Models\Website;
use App\Services\MediaOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DinasAppearanceController extends Controller
{
    /**
     * Tampilkan formulir pengaturan Appearance Slots milik dinas (Admin Kedinasan only).
     */
    public function edit()
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola tampilan dinas.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $appearance = $website->appearance ?? Appearance::firstOrCreate(
            ['website_id' => $website->id],
            [
                'header_slogan' => 'Portal Resmi Informasi dan Pelayanan Kedinasan Kota Batu',
                'hero_description' => 'Menyajikan informasi keterbukaan publik, agenda kegiatan, dan layanan terpadu kedinasan Pemerintah Kota Batu.',
                'footer_slogan' => 'Portal informasi dan layanan kedinasan resmi Pemerintah Kota Batu.',
                'footer_title' => $dinas->name,
                'footer_about_title' => 'Pemerintah Kota Batu',
                'footer_about_text' => 'Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu.',
            ]
        );

        return view('dinas.appearance', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'appearance' => $appearance,
            'heroBannerEditable' => $this->isHeroBannerEditable($website),
            'heroBannerDefault' => $this->getHeroBannerTemplateDefault($website),
            'logoEditable' => $this->isLogoEditable($website),
            'logoDefault' => $this->getLogoTemplateDefault($website),
            'footerTitleEditable' => $this->isFooterSlotEditable($website, 'title'),
            'footerSloganEditable' => $this->isFooterSlotEditable($website, 'slogan'),
            'footerSloganDefault' => $this->getFooterSlotDefault($website, 'slogan'),
            'footerAboutTitleEditable' => $this->isFooterSlotEditable($website, 'about_title'),
            'footerAboutTextEditable' => $this->isFooterSlotEditable($website, 'about_text'),
            'footerAboutTitleDefault' => $this->getFooterSlotDefault($website, 'about_title'),
            'footerAboutTextDefault' => $this->getFooterSlotDefault($website, 'about_text'),
        ]);
    }

    /**
     * Simpan pembaruan nilai Appearance Slots kedinasan.
     */
    public function update(Request $request, MediaOptimizerService $optimizer)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola tampilan dinas.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $validated = $request->validate([
            'header_slogan' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:2000'],
            'hero_banner' => ['nullable', 'string', 'max:1000'],
            'hero_banner_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_hero_banner' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'string', 'max:1000'],
            'logo_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'footer_slogan' => ['nullable', 'string', 'max:255'],
            'footer_title' => ['nullable', 'string', 'max:255'],
            'footer_about_title' => ['nullable', 'string', 'max:255'],
            'footer_about_text' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $appearance = $website->appearance ?? new Appearance(['website_id' => $website->id]);
        $isHeroEditable = $this->isHeroBannerEditable($website);
        $heroBanner = $appearance->hero_banner;

        if ($isHeroEditable) {
            if ($request->boolean('remove_hero_banner')) {
                if ($heroBanner && Storage::disk('public')->exists($heroBanner)) {
                    Storage::disk('public')->delete($heroBanner);
                }
                $heroBanner = null;
            } elseif ($request->hasFile('hero_banner_file')) {
                if ($heroBanner && Storage::disk('public')->exists($heroBanner)) {
                    Storage::disk('public')->delete($heroBanner);
                }

                $file = $request->file('hero_banner_file');
                $optimized = $optimizer->optimizeAndStore($file, 'hero', 'public');
                $heroBanner = $optimized['path'];

                // Catat ke tabel media sesuai SCHEMA 4.9 (format otomatis terkonversi WebP)
                Media::create([
                    'website_id' => $website->id,
                    'user_id' => $user->id,
                    'file_name' => $optimized['file_name'],
                    'file_path' => $optimized['path'],
                    'file_type' => $optimized['file_type'],
                    'file_size' => $optimized['file_size'],
                ]);
            } elseif ($request->filled('hero_banner')) {
                $heroBanner = $validated['hero_banner'];
            }
        }

        // Penanganan Logo Instansi (Upload / Remove / Fallback URL)
        $isLogoEditable = $this->isLogoEditable($website);
        $logo = $appearance->logo;

        if ($isLogoEditable) {
            if ($request->boolean('remove_logo')) {
                if ($logo && Storage::disk('public')->exists($logo)) {
                    Storage::disk('public')->delete($logo);
                }
                $logo = null;
            } elseif ($request->hasFile('logo_file')) {
                if ($logo && Storage::disk('public')->exists($logo)) {
                    Storage::disk('public')->delete($logo);
                }

                $file = $request->file('logo_file');
                $optimized = $optimizer->optimizeAndStore($file, 'logos', 'public');
                $logo = $optimized['path'];

                // Catat ke tabel media sesuai SCHEMA 4.9 (format otomatis terkonversi WebP)
                Media::create([
                    'website_id' => $website->id,
                    'user_id' => $user->id,
                    'file_name' => $optimized['file_name'],
                    'file_path' => $optimized['path'],
                    'file_type' => $optimized['file_type'],
                    'file_size' => $optimized['file_size'],
                ]);
            } elseif ($request->filled('logo')) {
                $logo = $validated['logo'];
            }
        }

        // 1. Update Appearance Slots (SCHEMA.md 2.6 & TEMPLATE.md Bab 3)
        $footerTitle = $this->isFooterSlotEditable($website, 'title')
            ? ($validated['footer_title'] ?? null)
            : $appearance->footer_title;

        $footerSlogan = $this->isFooterSlotEditable($website, 'slogan')
            ? ($validated['footer_slogan'] ?? null)
            : $appearance->footer_slogan;

        $footerAboutTitle = $this->isFooterSlotEditable($website, 'about_title')
            ? ($validated['footer_about_title'] ?? null)
            : $appearance->footer_about_title;

        $footerAboutText = $this->isFooterSlotEditable($website, 'about_text')
            ? ($validated['footer_about_text'] ?? null)
            : $appearance->footer_about_text;

        $appearance->fill([
            'header_slogan' => $validated['header_slogan'] ?? null,
            'hero_description' => $validated['hero_description'] ?? null,
            'hero_banner' => $heroBanner,
            'logo' => $logo,
            'footer_slogan' => $footerSlogan,
            'footer_title' => $footerTitle,
            'footer_about_title' => $footerAboutTitle,
            'footer_about_text' => $footerAboutText,
        ]);
        $appearance->website_id = $website->id;
        $appearance->save();

        // 2. Update Informasi Kontak Resmi Dinas (SCHEMA.md 2.1 & TEMPLATE.md Bab 3)
        $dinas->update([
            'address' => $validated['address'] ?? $dinas->address,
            'contact_email' => $validated['contact_email'] ?? $dinas->contact_email,
            'phone' => $validated['phone'] ?? $dinas->phone,
        ]);

        return redirect()->route('dinas.appearance.edit')->with('success', 'Pengaturan Appearance Slots berhasil disimpan dan otomatis diperbarui di website publik.');
    }

    /**
     * Cek apakah slot background_image Hero pada blueprint template aktif berstatus editable oleh Admin Kedinasan.
     */
    protected function isHeroBannerEditable(Website $website): bool
    {
        $canvasData = $website->template?->canvas_data;
        if (! $canvasData || ! is_array($canvasData)) {
            return true;
        }

        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas'])
            ? $canvasData['canvas']
            : $canvasData;

        $checkSlot = function ($items, $checkSlot) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (($item['component'] ?? '') === 'Hero') {
                    $slots = $item['slots'] ?? [];
                    if (isset($slots['background_image'])) {
                        return ($slots['background_image']['editable_by'] ?? 'admin_dinas') !== 'super_admin';
                    }
                    return true;
                }
                if (isset($item['children']) && is_array($item['children'])) {
                    $res = $checkSlot($item['children'], $checkSlot);
                    if ($res !== null) {
                        return $res;
                    }
                }
            }
            return null;
        };

        return $checkSlot($nodes, $checkSlot) ?? true;
    }

    /**
     * Ambil default_value fallback background_image dari template jika ada.
     */
    protected function getHeroBannerTemplateDefault(Website $website): ?string
    {
        $canvasData = $website->template?->canvas_data;
        if (! $canvasData || ! is_array($canvasData)) {
            return null;
        }

        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas'])
            ? $canvasData['canvas']
            : $canvasData;

        $checkDefault = function ($items, $checkDefault) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (($item['component'] ?? '') === 'Hero') {
                    return $item['slots']['background_image']['default_value'] ?? null;
                }
                if (isset($item['children']) && is_array($item['children'])) {
                    $res = $checkDefault($item['children'], $checkDefault);
                    if ($res !== null) {
                        return $res;
                    }
                }
            }
            return null;
        };

        return $checkDefault($nodes, $checkDefault);
    }

    /**
     * Cek apakah slot logo pada konfigurasi header/template berstatus editable oleh Admin Kedinasan.
     */
    protected function isLogoEditable(Website $website): bool
    {
        $canvasData = $website->template?->canvas_data;
        if (! $canvasData || ! is_array($canvasData)) {
            return true;
        }

        // 1. Cek langsung pada properti header global
        if (isset($canvasData['header']['slots']['logo']['editable_by'])) {
            return $canvasData['header']['slots']['logo']['editable_by'] !== 'super_admin';
        }

        // 2. Cek pada simpul kanvas (jika ada komponen Header/Navbar atau slot bertarget appearance.logo)
        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas'])
            ? $canvasData['canvas']
            : $canvasData;

        $checkSlot = function ($items, $checkSlot) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $slots = $item['slots'] ?? [];
                foreach ($slots as $slot) {
                    if (is_array($slot) && ($slot['binding'] ?? '') === 'appearance.logo') {
                        return ($slot['editable_by'] ?? 'admin_dinas') !== 'super_admin';
                    }
                }
                if (isset($item['children']) && is_array($item['children'])) {
                    $res = $checkSlot($item['children'], $checkSlot);
                    if ($res !== null) {
                        return $res;
                    }
                }
            }
            return null;
        };

        return $checkSlot($nodes, $checkSlot) ?? true;
    }

    /**
     * Ambil default_value fallback logo dari template jika ada.
     */
    protected function getLogoTemplateDefault(Website $website): ?string
    {
        $canvasData = $website->template?->canvas_data;
        if (! $canvasData || ! is_array($canvasData)) {
            return null;
        }

        if (isset($canvasData['header']['slots']['logo']['default_value'])) {
            return $canvasData['header']['slots']['logo']['default_value'];
        }

        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas'])
            ? $canvasData['canvas']
            : $canvasData;

        $checkDefault = function ($items, $checkDefault) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $slots = $item['slots'] ?? [];
                foreach ($slots as $slot) {
                    if (is_array($slot) && ($slot['binding'] ?? '') === 'appearance.logo') {
                        return $slot['default_value'] ?? null;
                    }
                }
                if (isset($item['children']) && is_array($item['children'])) {
                    $res = $checkDefault($item['children'], $checkDefault);
                    if ($res !== null) {
                        return $res;
                    }
                }
            }
            return null;
        };

        return $checkDefault($nodes, $checkDefault);
    }

    /**
     * Cek apakah slot pada Footer berstatus editable oleh Admin Kedinasan.
     */
    protected function isFooterSlotEditable(Website $website, string $slotKey): bool
    {
        $canvasData = $website->template?->canvas_data;
        if (! $canvasData || ! is_array($canvasData)) {
            return true;
        }

        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas'])
            ? $canvasData['canvas']
            : $canvasData;

        $checkSlot = function ($items, $checkSlot) use ($slotKey) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (($item['component'] ?? '') === 'Footer') {
                    $slots = $item['slots'] ?? [];
                    if (isset($slots[$slotKey])) {
                        return ($slots[$slotKey]['editable_by'] ?? 'admin_dinas') !== 'super_admin';
                    }
                    return true;
                }
                if (isset($item['children']) && is_array($item['children'])) {
                    $res = $checkSlot($item['children'], $checkSlot);
                    if ($res !== null) {
                        return $res;
                    }
                }
            }
            return null;
        };

        return $checkSlot($nodes, $checkSlot) ?? true;
    }

    /**
     * Ambil default_value fallback slot Footer dari template jika ada.
     */
    protected function getFooterSlotDefault(Website $website, string $slotKey): ?string
    {
        $canvasData = $website->template?->canvas_data;
        if (! $canvasData || ! is_array($canvasData)) {
            return null;
        }

        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas'])
            ? $canvasData['canvas']
            : $canvasData;

        $checkDefault = function ($items, $checkDefault) use ($slotKey) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (($item['component'] ?? '') === 'Footer') {
                    return $item['slots'][$slotKey]['default_value'] ?? null;
                }
                if (isset($item['children']) && is_array($item['children'])) {
                    $res = $checkDefault($item['children'], $checkDefault);
                    if ($res !== null) {
                        return $res;
                    }
                }
            }
            return null;
        };

        return $checkDefault($nodes, $checkDefault);
    }
}
