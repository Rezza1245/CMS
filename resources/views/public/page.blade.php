<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} — {{ $website->name }}</title>
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
    <x-public.navbar :website="$website" :current-page="$page" />

    {{-- BREADCRUMB & HERO HEADER --}}
    <div class="bg-slate-900 text-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-4 flex-wrap">
                <a href="{{ route('site.show', $website->domain) }}" class="hover:text-white transition">Beranda</a>
                @php
                    $crumbs = [];
                    $curr = $page->parent;
                    while ($curr) {
                        $crumbs[] = $curr;
                        $curr = $curr->parent;
                    }
                    $crumbs = array_reverse($crumbs);
                @endphp
                @if(empty($crumbs))
                    <span>/</span>
                    <span>Profil Institusi</span>
                @else
                    @foreach($crumbs as $crumb)
                        <span>/</span>
                        <a href="{{ route('site.page.show', ['identifier' => $website->domain, 'slug' => $crumb->slug]) }}" class="hover:text-white transition">{{ $crumb->title }}</a>
                    @endforeach
                @endif
                <span>/</span>
                <span class="text-white font-semibold truncate">{{ $page->title }}</span>
            </nav>

            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight text-white mb-3">
                {{ $page->title }}
            </h1>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span>{{ $website->dinas?->name ?? $website->name }}</span>
                <span>•</span>
                <span>Terakhir diperbarui: {{ $page->updated_at->format('d M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- MAIN PAGE CONTENT --}}
    <main class="flex-1 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-8">
            <article class="bg-white border border-slate-200 rounded-2xl p-8 sm:p-12 shadow-sm">
                @if($page->image)
                    <div class="mb-8 rounded-xl overflow-hidden border border-slate-200 max-h-96 bg-slate-100">
                        <img src="{{ asset('storage/' . $page->image) }}" alt="{{ $page->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                @if($page->direct_link)
                    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-emerald-900">Dokumen Lampiran / Tautan Terkait</h3>
                                <p class="text-xs text-emerald-700 mt-0.5">Halaman ini memiliki berkas lampiran resmi (Google Drive / Cloud Storage).</p>
                            </div>
                        </div>
                        <a href="{{ e($page->direct_link) }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition shadow-xs whitespace-nowrap">
                            <span>Unduh / Buka Dokumen</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                @endif

                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base space-y-4">
                    {!! nl2br(e($page->content)) !!}
                </div>

                @if($website->dinas?->address)
                    <div class="mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                        <span>Kantor Resmi: {{ $website->dinas->address }}</span>
                        <span class="font-semibold text-blue-600">Pemerintah Kota Batu</span>
                    </div>
                @endif
            </article>

            {{-- WADAH BAGIAN / DAFTAR ISI SUB-SUB BAB --}}
            @if($page->publishedChildren->isNotEmpty())
                <section class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <h2 class="text-xl font-bold text-slate-900">Daftar {{ $page->title }}</h2>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $page->publishedChildren->count() }} Bagian Tersedia
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($page->publishedChildren as $item)
                            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs hover:shadow-md hover:border-blue-300 transition flex flex-col justify-between">
                                <div>
                                    @if($item->image)
                                        <div class="h-44 w-full overflow-hidden bg-slate-100 border-b border-slate-100">
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif
                                    <div class="p-6">
                                        <h3 class="text-base font-bold text-slate-900 mb-2 leading-snug">
                                            {{ $item->title }}
                                        </h3>
                                        @if($item->content)
                                            <div class="prose prose-slate max-w-none text-slate-600 text-xs sm:text-sm leading-relaxed">
                                                {!! nl2br(e($item->content)) !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($item->direct_link)
                                    <div class="p-6 pt-0 mt-4">
                                        <a href="{{ e($item->direct_link) }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition shadow-xs">
                                            <span>Buka / Akses Tautan</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- POSTINGAN / ARTIKEL TERKAIT DI HALAMAN INI --}}
            @if($page->publishedPosts->isNotEmpty())
                <section class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <h2 class="text-xl font-bold text-slate-900">Publikasi &amp; Dokumen Terkait {{ $page->title }}</h2>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $page->publishedPosts->count() }} Publikasi
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($page->publishedPosts as $item)
                            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs hover:shadow-md hover:border-blue-300 transition flex flex-col justify-between">
                                <div>
                                    @if($item->image)
                                        <div class="h-44 w-full overflow-hidden bg-slate-100 border-b border-slate-100">
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif
                                    <div class="p-6">
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold
                                                @if($item->type === 'Berita') bg-blue-50 text-blue-700 border border-blue-200
                                                @elseif($item->type === 'Pengumuman') bg-amber-50 text-amber-700 border border-amber-200
                                                @else bg-emerald-50 text-emerald-700 border border-emerald-200
                                                @endif">
                                                {{ $item->type }}
                                            </span>
                                            <span class="text-xs text-slate-400">
                                                {{ $item->published_at ? $item->published_at->format('d M Y') : '—' }}
                                            </span>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-900 mb-2 leading-snug">
                                            <a href="{{ $item->direct_link ?: route('site.post.show', ['identifier' => $website->domain, 'slug' => $item->slug]) }}"
                                               @if($item->direct_link) target="_blank" rel="noopener noreferrer" @endif class="hover:text-blue-600 transition">
                                                {{ $item->title }}
                                            </a>
                                        </h3>
                                        @if($item->content)
                                            <div class="prose prose-slate max-w-none text-slate-600 text-xs sm:text-sm leading-relaxed line-clamp-3">
                                                {{ Str::limit(strip_tags($item->content), 120) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-6 pt-0 mt-2">
                                    <a href="{{ $item->direct_link ?: route('site.post.show', ['identifier' => $website->domain, 'slug' => $item->slug]) }}"
                                       @if($item->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                       class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition shadow-xs">
                                        <span>{{ $item->direct_link ? 'Buka Dokumen / Link' : 'Baca Artikel Lengkap' }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Rekomendasi Halaman Profil Lainnya --}}
            @if($otherPages->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Halaman Profil Lainnya</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($otherPages as $other)
                            <a href="{{ route('site.page.show', ['identifier' => $website->domain, 'slug' => $other->slug]) }}"
                                class="p-3 rounded-lg border border-slate-100 hover:border-blue-300 hover:bg-blue-50/40 transition group flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-600">{{ $other->title }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
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
