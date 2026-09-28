@props([
    'id' => 'media-list',
    'layout' => [],
    'slots' => [],
    'website' => null,
])

@php
    $limit = (int) ($layout['limit'] ?? 6);
    if ($limit <= 0) {
        $limit = 6;
    }
    $mediaItems = $slots['items'] ?? collect();
    if ($mediaItems instanceof \Illuminate\Support\Collection && $limit > 0) {
        $mediaItems = $mediaItems->take($limit);
    }
@endphp

<section id="{{ e($id) }}" class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-4 border-b border-slate-200 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Transparansi Informasi</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Dokumen &amp; Media Publik</h2>
                <p class="text-sm text-slate-500 mt-1">Galeri berkas resmi, dokumen publik, dan infografis {{ $website?->dinas?->name ?? $website?->name }}</p>
            </div>
            <div class="text-xs text-slate-400 font-mono">
                Format: PDF &amp; Gambar Resmi (6 Berkas Terkini)
            </div>
        </div>

        @if($mediaItems->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-600">Belum ada dokumen publik yang diunggah</p>
                <p class="text-xs text-slate-400 mt-1">Dokumen PDF resmi dan galeri berkas akan ditampilkan di bagian ini</p>
            </div>
        @else
            {{-- Grid 2 Baris x 3 Kolom (Total 6 Berkas) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($mediaItems as $media)
                        @php
                            $sizeFormatted = $media->file_size ? round($media->file_size / (1024 * 1024), 2) . ' MB' : '1.2 MB';
                            $ext = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                            $isImage = str_starts_with($media->file_type ?? '', 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                            $isPdf = str_contains($media->file_type ?? '', 'pdf') || $ext === 'pdf';
                            $isDoc = in_array($ext, ['doc', 'docx']) || str_contains($media->file_type ?? '', 'word') || str_contains($media->file_type ?? '', 'officedocument.wordprocessingml');
                            $isSheet = in_array($ext, ['xls', 'xlsx', 'csv']) || str_contains($media->file_type ?? '', 'sheet') || str_contains($media->file_type ?? '', 'officedocument.spreadsheetml');
                            $mediaUrl = asset('storage/' . $media->file_path);
                        @endphp
                        <div class="group/card bg-white border border-slate-200 hover:border-blue-400 rounded-2xl overflow-hidden transition-all shadow-xs hover:shadow-lg flex flex-col">
                            {{-- Jendela Pratinjau Asli Gambar atau Dokumen --}}
                            <div class="w-full h-48 bg-slate-100 relative overflow-hidden flex items-center justify-center border-b border-slate-100">
                                @if($isImage)
                                    <img src="{{ $mediaUrl }}" alt="{{ $media->file_name }}" class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-300">
                                    <div class="absolute top-3 left-3 px-2 py-0.5 rounded-md bg-black/60 backdrop-blur text-white text-[11px] font-semibold flex items-center gap-1 shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Gambar (WebP)</span>
                                    </div>
                                @elseif($isPdf)
                                    <iframe src="{{ $mediaUrl }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH" class="w-full h-full pointer-events-none bg-white scale-100" loading="lazy"></iframe>
                                    <div class="absolute inset-0 bg-transparent"></div>
                                    <div class="absolute top-3 left-3 px-2 py-0.5 rounded-md bg-red-600/90 backdrop-blur text-white text-[11px] font-semibold flex items-center gap-1 shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2h10l4 4v14a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V7h3.5L15 3.5zM9 13v4h1.5v-1.5H12a1.5 1.5 0 001.5-1.5v-1a1.5 1.5 0 00-1.5-1.5H9zm1.5 1.5h1v1h-1v-1z"/></svg>
                                        <span>Dokumen PDF</span>
                                    </div>
                                @elseif($isDoc)
                                    <div class="flex flex-col items-center justify-center p-6 text-blue-600 bg-blue-50/40 w-full h-full">
                                        <svg class="w-12 h-12 mb-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 7V3.5L18.5 7H15zM9 13h6v2H9v-2zm0 4h6v2H9v-2zm0-8h2v2H9V9z"/></svg>
                                        <span class="text-xs font-bold text-blue-700 uppercase">Dokumen Word (.{{ $ext }})</span>
                                    </div>
                                    <div class="absolute top-3 left-3 px-2 py-0.5 rounded-md bg-blue-600/90 backdrop-blur text-white text-[11px] font-semibold flex items-center gap-1 shadow-xs">
                                        <span>Word DOCX</span>
                                    </div>
                                @elseif($isSheet)
                                    <div class="flex flex-col items-center justify-center p-6 text-emerald-600 bg-emerald-50/40 w-full h-full">
                                        <svg class="w-12 h-12 mb-2 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 7V3.5L18.5 7H15zM8 17l2.5-4L8 9h2l1.5 2.5L13 9h2l-2.5 4 2.5 4h-2L11.5 14.5 10 17H8z"/></svg>
                                        <span class="text-xs font-bold text-emerald-700 uppercase">Spreadsheet Excel (.{{ $ext }})</span>
                                    </div>
                                    <div class="absolute top-3 left-3 px-2 py-0.5 rounded-md bg-emerald-600/90 backdrop-blur text-white text-[11px] font-semibold flex items-center gap-1 shadow-xs">
                                        <span>Excel XLSX</span>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center p-6 text-slate-400">
                                        <svg class="w-12 h-12 mb-2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span class="text-xs font-semibold uppercase">{{ $ext ?: 'Berkas' }}</span>
                                    </div>
                                @endif

                            {{-- Overlay Aksi Cepat Saat Hover --}}
                            <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer"
                               class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover/card:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 text-white text-xs font-semibold">
                                <span class="px-3 py-1.5 rounded-lg bg-black/60 backdrop-blur border border-white/20 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Pratinjau Layar Penuh</span>
                                </span>
                            </a>
                        </div>

                        {{-- Metadata & Keterangan Berkas --}}
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 line-clamp-2 leading-snug group-hover/card:text-blue-600 transition" title="{{ $media->file_name }}">
                                    {{ $media->file_name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-2 text-xs text-slate-400">
                                    <span class="font-medium text-slate-600">{{ $sizeFormatted }}</span>
                                    <span>•</span>
                                    <span class="uppercase font-mono text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $ext ?: ($isPdf ? 'PDF' : 'DOKUMEN') }}</span>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>Buka</span>
                                </a>
                                <a href="{{ $mediaUrl }}" download="{{ $media->file_name }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh Berkas</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
