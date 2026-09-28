@props([
    'id' => 'static-content',
    'layout' => [],
    'slots' => [],
    'website' => null,
])

@php
    $berandaPages = $website?->publishedBerandaPages ?? collect();
    if ($berandaPages->isEmpty()) {
        $berandaPages = $website?->publishedPages ?? collect();
    }
    $primaryPage = $berandaPages->first();
    $dinasName = $website?->dinas?->name ?? 'Pemerintah Kota Batu';
    $title = !empty($slots['title']) ? $slots['title'] : ($primaryPage?->title ?? 'Profil & Informasi ' . $dinasName);
    $customContent = !empty($slots['content']) ? $slots['content'] : null;
@endphp

<section id="{{ e($id) }}" class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Profil Ringkas</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $title }}</h2>
            <p class="text-xs text-slate-500 mt-1.5">{{ $website?->dinas?->name ?? 'Pemerintah Kota Batu' }}</p>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-8 sm:p-10 shadow-xs" id="{{ e($id) }}-card">
            @if($berandaPages->count() > 1)
                {{-- Tab Navigasi Beranda Interaktif --}}
                <div class="flex items-center gap-2 overflow-x-auto border-b border-slate-200 pb-3 mb-6 scrollbar-none">
                    @foreach($berandaPages as $idx => $p)
                        <button type="button"
                                onclick="switchStaticTab('{{ e($id) }}-card', {{ $idx }})"
                                class="static-tab-btn px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $idx === 0 ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                            {{ $p->title }}
                        </button>
                    @endforeach
                </div>

                @foreach($berandaPages as $idx => $p)
                    <div class="static-tab-pane {{ $idx === 0 ? '' : 'hidden' }}">
                        @if($p->image)
                            <div class="mb-5 rounded-xl overflow-hidden max-h-72 border border-slate-200 bg-slate-100">
                                <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <h3 class="text-lg font-bold text-slate-900 mb-3">{{ $p->title }}</h3>
                        <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base space-y-4">
                            {!! nl2br(e($p->content)) !!}
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('site.page.show', ['identifier' => $website->domain, 'slug' => $p->slug]) }}"
                                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                                    <span>Baca Halaman Selengkapnya</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                                @if($p->direct_link)
                                    <a href="{{ e($p->direct_link) }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Unduh / Buka Dokumen</span>
                                    </a>
                                @endif
                            </div>

                            @if($website?->dinas?->address)
                                <span class="text-xs text-slate-500">Kantor: {{ $website->dinas->address }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @elseif($primaryPage)
                @if($primaryPage->image)
                    <div class="mb-5 rounded-xl overflow-hidden max-h-72 border border-slate-200 bg-slate-100">
                        <img src="{{ asset('storage/' . $primaryPage->image) }}" alt="{{ $primaryPage->title }}" class="w-full h-full object-cover">
                    </div>
                @endif
                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base space-y-4">
                    {!! nl2br(e($primaryPage->content)) !!}
                </div>

                <div class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('site.page.show', ['identifier' => $website->domain, 'slug' => $primaryPage->slug]) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                            <span>Baca Halaman Selengkapnya</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        @if($primaryPage->direct_link)
                            <a href="{{ e($primaryPage->direct_link) }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Unduh / Buka Dokumen</span>
                            </a>
                        @endif
                    </div>

                    @if($website?->dinas?->address)
                        <span class="text-xs text-slate-500">Kantor: {{ $website->dinas->address }}</span>
                    @endif
                </div>
            @elseif($customContent)
                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base space-y-4">
                    {!! nl2br(e($customContent)) !!}
                </div>
            @else
                <div class="py-8 px-6 rounded-xl border border-dashed border-slate-300 bg-white/60 text-center flex flex-col items-center justify-center">
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 mb-1">Ruang Konten Profil &amp; Informasi Kedinasan</h4>
                    <p class="text-xs text-slate-500 max-w-md leading-relaxed">
                        Informasi profil resmi, visi-misi, dan tata kelola {{ $dinasName }} akan segera dipublikasikan di bagian ini.
                    </p>
                    @if(auth()->check() && auth()->user()->role === 'admin_dinas' && auth()->user()->dinas_id === $website?->dinas_id)
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                            <a href="{{ route('dinas.pages.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Kelola Konten di Modul Pages</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Pintasan ke Halaman Statis Lainnya (Visi Misi, Struktur, dsb) --}}
            @php
                $allPublished = $website?->publishedPages ?? collect();
            @endphp
            @if($allPublished->count() > 1)
                <div class="mt-6 pt-5 border-t border-slate-200/60 flex flex-wrap items-center gap-2">
                    <span class="text-xs text-slate-400 font-medium">Halaman Lainnya:</span>
                    @foreach($allPublished as $p)
                        <a href="{{ route('site.page.show', ['identifier' => $website->domain, 'slug' => $p->slug]) }}"
                           class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white border border-slate-200 hover:border-blue-300 text-slate-600 hover:text-blue-600 text-xs transition shadow-2xs font-medium">
                            <span>{{ $p->title }}</span>
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

<script>
    function switchStaticTab(cardId, activeIndex) {
        const card = document.getElementById(cardId);
        if (!card) return;
        const buttons = card.querySelectorAll('.static-tab-btn');
        const panes = card.querySelectorAll('.static-tab-pane');

        buttons.forEach((btn, idx) => {
            if (idx === activeIndex) {
                btn.className = 'static-tab-btn px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap bg-blue-600 text-white shadow-xs';
            } else {
                btn.className = 'static-tab-btn px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap bg-white text-slate-700 hover:bg-slate-100 border border-slate-200';
            }
        });

        panes.forEach((pane, idx) => {
            if (idx === activeIndex) {
                pane.classList.remove('hidden');
            } else {
                pane.classList.add('hidden');
            }
        });
    }
</script>
