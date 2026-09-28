<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class DinasSettingController extends Controller
{
    /**
     * Tampilkan formulir pengaturan website dinas (Admin Kedinasan only).
     */
    public function edit()
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola pengaturan website.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $setting = $website->settings ?? Setting::firstOrCreate(
            ['website_id' => $website->id],
            [
                'general_config' => [
                    'site_title' => $website->name,
                    'site_status' => $website->status ?? 'aktif',
                ],
                'privacy_config' => [
                    'public_visibility' => 'publik',
                    'privacy_contact_email' => $dinas->contact_email,
                ],
            ]
        );

        $general = $setting->general_config ?? [];
        $privacy = $setting->privacy_config ?? [];

        return view('dinas.settings', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'setting' => $setting,
            'general' => $general,
            'privacy' => $privacy,
        ]);
    }

    /**
     * Simpan pembaruan pengaturan website dinas.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola pengaturan website.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $validated = $request->validate([
            'site_title' => ['required', 'string', 'max:255'],
            'site_status' => ['required', 'in:aktif,pemeliharaan'],
            'public_visibility' => ['required', 'in:publik,terbatas'],
            'privacy_contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $setting = Setting::firstOrNew(['website_id' => $website->id]);

        $setting->general_config = [
            'site_title' => $validated['site_title'],
            'site_status' => $validated['site_status'],
        ];

        $setting->privacy_config = [
            'public_visibility' => $validated['public_visibility'],
            'privacy_contact_email' => $validated['privacy_contact_email'] ?? null,
        ];

        $setting->save();

        // Update juga nama website dan status jika sinkron
        $website->update([
            'name' => $validated['site_title'],
            'status' => $validated['site_status'],
        ]);

        return redirect()->route('dinas.settings.edit')
            ->with('success', 'Pengaturan website dinas berhasil disimpan.');
    }
}
