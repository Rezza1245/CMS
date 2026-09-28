<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    /**
     * Tampilkan formulir pengaturan platform global (Super Admin only).
     */
    public function edit()
    {
        $settings = [
            'max_upload_size_mb' => SystemSetting::get('max_upload_size_mb', '5'),
            'allowed_media_types' => SystemSetting::get('allowed_media_types', 'jpg,jpeg,png,webp,pdf'),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Simpan pembaruan pengaturan platform global.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'max_upload_size_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'allowed_media_types' => ['required', 'string', 'max:255'],
        ]);

        SystemSetting::set('max_upload_size_mb', (string) $validated['max_upload_size_mb']);
        SystemSetting::set('allowed_media_types', $validated['allowed_media_types']);

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Konfigurasi platform global berhasil disimpan.');
    }
}
