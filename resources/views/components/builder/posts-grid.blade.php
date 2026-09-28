@props([
    'id' => 'posts-grid',
    'layout' => [],
    'slots' => [],
    'website' => null,
])

@php
    $columns = (int) ($layout['columns'] ?? 3);
    $colClass = match($columns) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    };

    /** @var \Illuminate\Support\Collection $posts */
    $posts = $slots['items'] ?? collect();
@endphp

<section id="{{ e($id) }}" class="py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 pb-4 border-b border-slate-200 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Publikasi Informasi</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Berita & Informasi Terkini</h2>
                <p class="text-sm text-slate-500 mt-1">Informasi resmi dari {{ $website?->dinas?->name ?? $website?->name }}</p>
            </div>
            <div class="text-xs text-slate-500">
                Menampilkan {{ $posts->count() }} publikasi
            </div>
        </div>

        @if($posts->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-600">Belum ada publikasi yang diterbitkan</p>
                <p class="text-xs text-slate-400 mt-1">Konten berstatus draft tidak ditampilkan ke publik</p>
            </div>
        @else
            <div class="grid {{ $colClass }} gap-6">
                @foreach($posts as $post)
                    <article class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-lg transition-shadow duration-200 flex flex-col">
                        @if($post->image)
                            <div class="w-full h-48 bg-slate-100 overflow-hidden shrink-0">
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                        @endif
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($post->type === 'Berita') bg-blue-50 text-blue-700 border border-blue-200
                                    @elseif($post->type === 'Pengumuman') bg-amber-50 text-amber-700 border border-amber-200
                                    @else bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @endif">
                                    {{ $post->type }}
                                </span>
                                <time class="text-xs text-slate-400" datetime="{{ $post->published_at?->toIso8601String() }}">
                                    {{ $post->published_at ? $post->published_at->format('d M Y') : '—' }}
                                </time>
                            </div>

                            @php
                                $postUrl = $post->direct_link ?: route('site.post.show', ['identifier' => $website?->domain ?? $post->website_id, 'slug' => $post->slug]);
                            @endphp

                            <h3 class="text-lg font-bold text-slate-900 hover:text-blue-600 transition mb-2 line-clamp-2 leading-snug">
                                <a href="{{ $postUrl }}" @if($post->direct_link) target="_blank" rel="noopener noreferrer" @endif>
                                    {{ $post->title }}
                                </a>
                            </h3>

                            <p class="text-sm text-slate-600 line-clamp-3 mb-4 leading-relaxed flex-1">
                                {{ Str::limit(strip_tags($post->content), 120) }}
                            </p>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 mt-auto">
                                @if($post->direct_link)
                                    <span class="font-medium text-slate-700">Dokumen Terlampir</span>
                                    <a href="{{ e($post->direct_link) }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition text-xs shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Unduh / Buka Dokumen</span>
                                    </a>
                                @else
                                    <span class="font-medium text-slate-700">Diterbitkan oleh Admin</span>
                                    <a href="{{ $postUrl }}" class="text-blue-600 hover:text-blue-700 font-semibold inline-flex items-center gap-1 transition">
                                        <span>Baca selengkapnya</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
