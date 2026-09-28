@props([
    'website' => null,
    'currentPage' => null,
    'currentPost' => null,
    'isPreview' => false,
    'template' => null,
])

@php
    $domain = $website?->domain;
    $logo = $website?->appearance?->logo;
    if ($logo && !str_starts_with($logo, 'http://') && !str_starts_with($logo, 'https://') && !str_starts_with($logo, '//')) {
        $logo = asset(str_starts_with($logo, 'storage/') ? $logo : 'storage/' . ltrim($logo, '/'));
    }

    $brandName = $website?->name ?? $template?->name ?? 'Portal Resmi Kedinasan';
    $homeUrl = $website ? route('site.show', $domain) : '#';

    $resolvePageUrl = fn($p) => $p->direct_link ?: ($website ? route('site.page.show', ['identifier' => $domain, 'slug' => $p->slug]) : '#');
    $resolvePostUrl = fn($post) => $post->direct_link ?: ($website ? route('site.post.show', ['identifier' => $domain, 'slug' => $post->slug]) : '#');

    // Menu dinamis header: root pages yang dipublish (selain penempatan khusus beranda saja)
    $headerMenus = $website?->publishedHeaderMenus ?? collect();

    // Helper untuk mengecek apakah halaman yang sedang dibuka merupakan turunan dari menu ini
    $isMenuActive = function($menu) use ($currentPage) {
        if (!$currentPage) return false;
        if ($currentPage->id === $menu->id) return true;

        $checkDescendants = function($item) use (&$checkDescendants, $currentPage) {
            foreach ($item->publishedChildren as $child) {
                if ($child->id === $currentPage->id) return true;
                if ($checkDescendants($child)) return true;
            }
            return false;
        };

        return $checkDescendants($menu);
    };
@endphp

<header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200 shadow-2xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-18 py-2">
            {{-- BRAND LOGO & TITLE --}}
            <a href="{{ $homeUrl }}" class="flex items-center gap-3 shrink-0">
                @if($logo)
                    <img src="{{ e($logo) }}" alt="Logo {{ e($brandName) }}" class="h-10 w-auto object-contain">
                @else
                    <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        {{ Str::upper(Str::substr($website?->dinas?->code ?? $brandName, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <div class="font-extrabold text-slate-900 text-base leading-tight">
                        {{ $brandName }}
                    </div>
                    <div class="text-xs text-slate-500 font-medium">
                        Pemerintah Kota Batu
                    </div>
                </div>
            </a>

            {{-- DESKTOP NAVIGATION --}}
            <nav class="hidden lg:flex items-center gap-5 text-sm font-medium text-slate-600">
                {{-- 1. BERANDA (STANDAR / FIXED DEFAULT) --}}
                <a href="{{ $homeUrl }}"
                   class="hover:text-blue-600 transition {{ ((request()->routeIs('site.show') || request()->routeIs('admin.templates.preview')) && !request()->has('slug')) ? 'text-blue-600 font-semibold' : '' }}">
                    Beranda
                </a>

                {{-- 2. MENU DINAMIS HASIL INPUT ADMIN DINAS (DENGAN SUB-BAB & SUB-SUB-BAB) --}}
                @forelse($headerMenus as $menu)
                    @php
                        $subBabList = $menu->publishedChildren;
                        $hasChildren = $subBabList->isNotEmpty();
                        $active = $isMenuActive($menu);
                    @endphp

                    @if(!$hasChildren)
                        {{-- Menu Utama Tunggal (Tanpa Sub-bab) --}}
                        <a href="{{ $resolvePageUrl($menu) }}"
                           @if($menu->direct_link) target="_blank" rel="noopener noreferrer" @endif
                           class="{{ $active ? 'text-blue-600 font-semibold' : 'hover:text-blue-600' }} transition">
                            {{ $menu->title }}
                            @if($menu->direct_link) <span class="text-[10px] text-slate-400">↗</span> @endif
                        </a>
                    @else
                        {{-- Menu Utama dengan Sub-bab (Level 2) & Sub-sub-bab (Level 3/4) --}}
                        <div class="relative group py-2">
                            <a href="{{ $resolvePageUrl($menu) }}"
                               @if($menu->direct_link) target="_blank" rel="noopener noreferrer" @endif
                               class="inline-flex items-center gap-1 {{ $active ? 'text-blue-600 font-semibold' : 'hover:text-blue-600' }} transition font-medium focus:outline-none">
                                <span>{{ $menu->title }}</span>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </a>

                            <div class="absolute left-0 top-full pt-1 w-64 hidden group-hover:block z-50">
                                <div class="bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 overflow-visible">
                                    <a href="{{ $resolvePageUrl($menu) }}"
                                       @if($menu->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                       class="block px-4 py-2 text-xs text-blue-600 font-semibold hover:bg-blue-50 transition border-b border-slate-100">
                                        Ringkasan {{ $menu->title }}
                                    </a>

                                    @foreach($subBabList as $child)
                                        @php
                                            $subSubBabList = $child->publishedChildren;
                                            $hasSubSub = $subSubBabList->isNotEmpty();
                                            $isCurChild = $currentPage && ($child->id === $currentPage->id);
                                        @endphp

                                        @if(!$hasSubSub)
                                            {{-- Sub-bab biasa --}}
                                            <a href="{{ $resolvePageUrl($child) }}"
                                               @if($child->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                               class="block px-4 py-2 text-xs {{ $isCurChild ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-600' }} transition">
                                                {{ $child->title }}
                                                @if($child->direct_link) <span class="text-[10px] text-slate-400">↗</span> @endif
                                            </a>
                                        @else
                                            {{-- Sub-bab dengan Sub-sub-bab (Level 3 / Flyout) --}}
                                            <div class="relative group/sub">
                                                <a href="{{ $resolvePageUrl($child) }}"
                                                   @if($child->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                                   class="flex items-center justify-between px-4 py-2 text-xs {{ $isCurChild ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-600' }} transition font-medium">
                                                    <span>{{ $child->title }}</span>
                                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover/sub:text-blue-600 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                </a>

                                                <div class="absolute left-full top-0 pl-1 w-60 hidden group-hover/sub:block z-50">
                                                    <div class="bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 overflow-hidden">
                                                        <a href="{{ $resolvePageUrl($child) }}"
                                                           @if($child->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                                           class="block px-4 py-2 text-xs text-blue-600 font-semibold hover:bg-blue-50 transition border-b border-slate-100">
                                                            Ringkasan {{ $child->title }}
                                                        </a>

                                                        @foreach($subSubBabList as $subChild)
                                                            @php
                                                                $containerItems = $subChild->publishedChildren;
                                                                $hasContainerItems = $containerItems->isNotEmpty();
                                                                $isCurSubChild = $currentPage && ($subChild->id === $currentPage->id);
                                                            @endphp

                                                            @if(!$hasContainerItems)
                                                                <a href="{{ $resolvePageUrl($subChild) }}"
                                                                   @if($subChild->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                                                   class="block px-4 py-2 text-xs {{ $isCurSubChild ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-600' }} transition">
                                                                    {{ $subChild->title }}
                                                                    @if($subChild->direct_link) <span class="text-[10px] text-slate-400">↗</span> @endif
                                                                </a>
                                                            @else
                                                                {{-- Sub-sub-bab dengan Isi Wadah (Level 4) --}}
                                                                <div class="relative group/subsub">
                                                                    <a href="{{ $resolvePageUrl($subChild) }}"
                                                                       @if($subChild->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                                                       class="flex items-center justify-between px-4 py-2 text-xs {{ $isCurSubChild ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-600' }} transition font-medium">
                                                                        <span>{{ $subChild->title }}</span>
                                                                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover/subsub:text-blue-600 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                                    </a>

                                                                    <div class="absolute left-full top-0 pl-1 w-56 hidden group-hover/subsub:block z-50">
                                                                        <div class="bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 overflow-hidden">
                                                                            <a href="{{ $resolvePageUrl($subChild) }}"
                                                                               @if($subChild->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                                                               class="block px-4 py-2 text-xs text-blue-600 font-semibold hover:bg-blue-50 transition border-b border-slate-100">
                                                                                Ringkasan {{ $subChild->title }}
                                                                            </a>

                                                                            @foreach($containerItems as $item)
                                                                                <a href="{{ $resolvePageUrl($item) }}"
                                                                                   @if($item->direct_link) target="_blank" rel="noopener noreferrer" @endif
                                                                                   class="block px-4 py-2 text-xs {{ ($currentPage && $currentPage->id === $item->id) ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-600' }} transition">
                                                                                    {{ $item->title }}
                                                                                    @if($item->direct_link) <span class="text-[10px] text-slate-400">↗</span> @endif
                                                                                </a>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    {{-- Default Blueprint Navigation (Mode Pratinjau Template) --}}
                    <span class="hover:text-blue-600 transition cursor-default">Profil</span>
                    <span class="hover:text-blue-600 transition cursor-default">Layanan</span>
                    <span class="hover:text-blue-600 transition cursor-default">Berita</span>
                    <span class="hover:text-blue-600 transition cursor-default">Dokumen</span>
                    <span class="hover:text-blue-600 transition cursor-default">Kontak</span>
                @endforelse
            </nav>

            {{-- MOBILE TOGGLE --}}
            <div class="flex items-center">
                {{-- Hamburger menu button for mobile screens --}}
                <button type="button"
                        onclick="document.getElementById('mobile-navbar-drawer').classList.toggle('hidden')"
                        aria-label="Toggle Menu"
                        class="lg:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        {{-- MOBILE NAVIGATION DRAWER --}}
        <div id="mobile-navbar-drawer" class="hidden lg:hidden border-t border-slate-200 py-3 space-y-2 bg-white">
            <a href="{{ $homeUrl }}"
               class="block px-3 py-2 rounded-lg text-sm font-semibold {{ ((request()->routeIs('site.show') || request()->routeIs('admin.templates.preview')) && !request()->has('slug')) ? 'bg-blue-50 text-blue-600' : 'text-slate-700 hover:bg-slate-50' }}">
                🏠 Beranda
            </a>

            @forelse($headerMenus as $menu)
                @php $mActive = $isMenuActive($menu); @endphp
                <div class="border-t border-slate-100 pt-1">
                    <a href="{{ $resolvePageUrl($menu) }}"
                       @if($menu->direct_link) target="_blank" rel="noopener noreferrer" @endif
                       class="block px-3 py-1.5 rounded-lg text-sm font-semibold {{ $mActive ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800 hover:bg-slate-50' }}">
                        {{ $menu->title }}
                    </a>

                    @foreach($menu->publishedChildren as $child)
                        <a href="{{ $resolvePageUrl($child) }}"
                           @if($child->direct_link) target="_blank" rel="noopener noreferrer" @endif
                           class="block pl-7 pr-3 py-1 text-xs text-slate-600 hover:text-blue-600">
                            ↳ {{ $child->title }}
                        </a>

                        @foreach($child->publishedChildren as $subChild)
                            <a href="{{ $resolvePageUrl($subChild) }}"
                               @if($subChild->direct_link) target="_blank" rel="noopener noreferrer" @endif
                               class="block pl-11 pr-3 py-0.5 text-xs text-slate-500 hover:text-blue-600">
                                ↳↳ {{ $subChild->title }}
                            </a>
                        @endforeach
                    @endforeach
                </div>
            @empty
                <div class="border-t border-slate-100 pt-1 px-3 py-1.5 text-xs text-slate-500 space-y-1">
                    <div class="py-1">Profil</div>
                    <div class="py-1">Layanan</div>
                    <div class="py-1">Berita</div>
                    <div class="py-1">Dokumen</div>
                    <div class="py-1">Kontak</div>
                </div>
            @endforelse
        </div>
    </div>
</header>
