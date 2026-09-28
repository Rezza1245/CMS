<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} — {{ $website->name }}</title>
    @if($website->appearance?->favicon)
        <link rel="icon" href="{{ e($website->appearance->favicon) }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com?v=4"></script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 antialiased">

    @if($website->status === 'pemeliharaan')
        <div class="bg-amber-600 text-white text-xs font-semibold px-4 py-2 text-center shadow-xs flex items-center justify-center gap-2">
            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
            Mode Pemeliharaan Aktif — Halaman ini hanya dapat diakses melalui pratinjau internal pengelola kedinasan.
        </div>
    @endif

    {{-- TOP NAVBAR (7 MAIN MENUS) --}}
    <x-public.navbar :website="$website" :current-post="$post" />

    {{-- BREADCRUMB & HERO HEADER --}}
    <div class="bg-slate-900 text-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-4 flex-wrap">
                <a href="{{ route('site.show', $website->domain) }}" class="hover:text-white transition">Beranda</a>
                <span>/</span>
                <a href="{{ route('site.show', $website->domain) }}#posts-grid" class="hover:text-white transition">Publikasi</a>
                <span>/</span>
                <span class="text-slate-300">{{ $post->type }}</span>
                <span>/</span>
                <span class="text-white font-semibold truncate">{{ $post->title }}</span>
            </nav>

            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-3
                @if($post->type === 'Berita') bg-blue-500/20 text-blue-300 border border-blue-400/30
                @elseif($post->type === 'Pengumuman') bg-amber-500/20 text-amber-300 border border-amber-400/30
                @else bg-emerald-500/20 text-emerald-300 border border-emerald-400/30
                @endif">
                {{ $post->type }}
            </div>

            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight text-white mb-3">
                {{ $post->title }}
            </h1>

            <div class="flex items-center gap-4 text-xs text-slate-400 flex-wrap">
                <span>{{ $website->dinas?->name ?? $website->name }}</span>
                <span>•</span>
                <span>Diterbitkan: {{ $post->published_at ? $post->published_at->format('d M Y, H:i') : $post->created_at->format('d M Y') }}</span>
                @if($post->placement !== 'beranda')
                    <span>•</span>
                    <span class="inline-flex items-center gap-1 text-blue-400 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                        Tampil di Menu Header
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-8">
            <article class="bg-white border border-slate-200 rounded-2xl p-8 sm:p-12 shadow-sm">
                @if($post->image)
                    <div class="mb-8 rounded-xl overflow-hidden border border-slate-200 max-h-96 bg-slate-100">
                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                @if($post->direct_link)
                    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-emerald-900">Dokumen Lampiran / Tautan Terkait</h3>
                                <p class="text-xs text-emerald-700 mt-0.5">Artikel ini memiliki berkas lampiran resmi (Google Drive / Cloud Storage).</p>
                            </div>
                        </div>
                        <a href="{{ e($post->direct_link) }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition shadow-xs whitespace-nowrap">
                            <span>Unduh / Buka Dokumen</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                @endif

                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base space-y-4">
                    {!! nl2br(e($post->content)) !!}
                </div>

                @if($website->dinas?->address)
                    <div class="mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                        <span>Kantor Resmi: {{ $website->dinas->address }}</span>
                        <span class="font-semibold text-blue-600">Pemerintah Kota Batu</span>
                    </div>
                @endif
            </article>

            {{-- Rekomendasi Publikasi Lainnya --}}
            @if($otherPosts->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Publikasi Terkait Lainnya</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($otherPosts as $other)
                            <a href="{{ $other->direct_link ?: route('site.post.show', ['identifier' => $website->domain, 'slug' => $other->slug]) }}"
                               @if($other->direct_link) target="_blank" rel="noopener noreferrer" @endif
                               class="p-3 rounded-lg border border-slate-100 hover:border-blue-300 hover:bg-blue-50/40 transition group flex items-center justify-between">
                                <div class="truncate mr-2">
                                    <span class="text-[10px] font-semibold uppercase text-blue-600 block">{{ $other->type }}</span>
                                    <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-600 truncate block">{{ $other->title }}</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 transition shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="text-center pt-4">
                <a href="{{ route('site.show', $website->domain) }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-blue-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </main>

    {{-- TEMPLATE FOOTER --}}
    {!! $renderedFooter ?? '' !!}

</body>
</html>
