<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DinasMediaController extends Controller
{
    /**
     * Tampilkan daftar berkas media & dokumen publik milik website dinas aktif (Admin Kedinasan only).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola media & dokumen dinas.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $baseQuery = $website->media();
        $totalCount = (clone $baseQuery)->count();
        $totalBytes = (clone $baseQuery)->sum('file_size') ?: 0;
        $totalSizeMb = round($totalBytes / (1024 * 1024), 2);

        $query = (clone $baseQuery)->latest();

        if ($request->filled('filter')) {
            $filter = $request->query('filter');
            if ($filter === 'pdf') {
                $query->where('file_type', 'like', '%pdf%');
            } elseif ($filter === 'document') {
                $query->where(function ($q) {
                    $q->where('file_type', 'like', '%pdf%')
                        ->orWhere('file_type', 'like', '%word%')
                        ->orWhere('file_type', 'like', '%officedocument%')
                        ->orWhere('file_type', 'like', '%sheet%')
                        ->orWhere('file_type', 'like', '%presentation%')
                        ->orWhere('file_name', 'like', '%.pdf')
                        ->orWhere('file_name', 'like', '%.doc')
                        ->orWhere('file_name', 'like', '%.docx')
                        ->orWhere('file_name', 'like', '%.xls')
                        ->orWhere('file_name', 'like', '%.xlsx')
                        ->orWhere('file_name', 'like', '%.ppt')
                        ->orWhere('file_name', 'like', '%.pptx');
                });
            } elseif ($filter === 'image') {
                $query->where(function ($q) {
                    $q->where('file_type', 'like', '%image%')
                        ->orWhere('file_name', 'like', '%.jpg')
                        ->orWhere('file_name', 'like', '%.jpeg')
                        ->orWhere('file_name', 'like', '%.png')
                        ->orWhere('file_name', 'like', '%.webp');
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('file_name', 'like', "%{$search}%");
        }

        $mediaList = $query->paginate(12)->withQueryString();

        return view('dinas.media.index', [
            'user' => $user,
            'dinas' => $dinas,
            'website' => $website,
            'mediaList' => $mediaList,
            'totalCount' => $totalCount,
            'totalSizeMb' => $totalSizeMb,
        ]);
    }

    /**
     * Unggah berkas media atau dokumen resmi baru dari perangkat komputer.
     */
    public function store(Request $request, MediaOptimizerService $optimizer)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengunggah media & dokumen dinas.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        if (! $website) {
            abort(404, 'Entitas website dinas belum terdaftar.');
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp',
                'max:10240', // Maksimal 10MB
            ],
        ], [
            'file.required' => 'Silakan pilih berkas dokumen atau gambar yang ingin diunggah.',
            'file.mimes' => 'Format berkas tidak diizinkan. Gunakan format PDF, Word (DOC/DOCX), Excel (XLS/XLSX), PPT, atau Gambar (JPG, PNG, WEBP).',
            'file.max' => 'Ukuran berkas melebihi batas maksimal 10 MB.',
        ]);

        $file = $request->file('file');
        $optimized = $optimizer->optimizeAndStore($file, 'media', 'public');

        $website->media()->create([
            'user_id' => $user->id,
            'file_name' => $optimized['file_name'],
            'file_path' => $optimized['path'],
            'file_type' => $optimized['file_type'],
            'file_size' => $optimized['file_size'],
        ]);

        $savingsMsg = '';
        if ($optimized['original_size'] > $optimized['file_size']) {
            $savedPercent = round((($optimized['original_size'] - $optimized['file_size']) / $optimized['original_size']) * 100);
            if ($savedPercent > 0) {
                $savingsMsg = " (berhasil dikompresi hemat {$savedPercent}%)";
            }
        }

        return redirect()->route('dinas.media.index')
            ->with('success', "Berkas '{$optimized['file_name']}' berhasil diunggah{$savingsMsg} dan otomatis terintegrasi pada komponen Dokumen & Media Publik.");
    }

    /**
     * Hapus berkas media atau dokumen.
     */
    public function destroy(Media $media)
    {
        $user = auth()->user();

        if ($user?->role !== 'admin_dinas' || ! $user->dinas_id) {
            abort(403, 'Akses ditolak. Hanya Admin Kedinasan yang berhak mengelola media & dokumen dinas.');
        }

        $dinas = $user->dinas;
        $website = $dinas?->website;

        // Validasi kepemilikan tenant (AGENTS.md Bab 6)
        if (! $website || $media->website_id !== $website->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk menghapus berkas milik dinas lain.');
        }

        // Hapus berkas fisik dari storage jika ada
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $fileName = $media->file_name;
        $media->delete();

        return redirect()->route('dinas.media.index')
            ->with('success', "Berkas '{$fileName}' berhasil dihapus.");
    }
}
