@php
    $user = auth()->user();
    $dinas = $user?->dinas_id ? \App\Models\Dinas::find($user->dinas_id) : null;
    $website = $dinas?->website;
    $websiteId = $website?->id;

    // Tenant-scoped Post metrics (SCHEMA.md 2.8, ROLES_RBAC Rule 03 & 10)
    $totalPosts = 0;
    $publishedPosts = 0;
    $draftPosts = 0;
    $newsCount = 0;
    $announcementCount = 0;
    $activityCount = 0;
    $topCategory = 'Berita';
    $topCategoryCount = 0;
    $recentPosts = collect();

    if ($websiteId && \Illuminate\Support\Facades\Schema::hasTable('posts')) {
        $postBase = \App\Models\Post::where('website_id', $websiteId);
        $totalPosts = (clone $postBase)->count();
        $publishedPosts = (clone $postBase)->where('status', 'published')->count();
        $draftPosts = (clone $postBase)->where('status', 'draft')->count();

        $newsCount = (clone $postBase)->where('type', 'Berita')->count();
        $announcementCount = (clone $postBase)->where('type', 'Pengumuman')->count();
        $activityCount = (clone $postBase)->where('type', 'Kegiatan')->count();

        $counts = [
            'Berita' => $newsCount,
            'Pengumuman' => $announcementCount,
            'Kegiatan' => $activityCount,
        ];
        arsort($counts);
        $topCategory = array_key_first($counts) ?: 'Berita';
        $topCategoryCount = $counts[$topCategory] ?? 0;

        $recentPosts = (clone $postBase)->latest()->take(5)->get();
    }

    // Tenant-scoped Media metrics (SCHEMA.md 2.9)
    $mediaCount = 0;
    $mediaSizeMb = 0.0;
    if ($websiteId && \Illuminate\Support\Facades\Schema::hasTable('media')) {
        $mediaBase = \Illuminate\Support\Facades\DB::table('media')->where('website_id', $websiteId);
        $mediaCount = (clone $mediaBase)->count();
        $bytes = (clone $mediaBase)->sum('file_size') ?: 0;
        $mediaSizeMb = round($bytes / (1024 * 1024), 1);
    } else {
        // Mock fallback when migration table is not yet created
        $mediaCount = 18;
        $mediaSizeMb = 42.5;
    }

    // Static Pages metrics (SCHEMA.md 2.7)
    $pageCount = 4;
    if ($websiteId && \Illuminate\Support\Facades\Schema::hasTable('pages')) {
        $pageCount = \Illuminate\Support\Facades\DB::table('pages')
            ->where('website_id', $websiteId)
            ->where('status', 'published')
            ->count();
    }

    // Quick Access Drafts (Posts & Pages)
    $draftPostsList = collect();
    if ($websiteId && \Illuminate\Support\Facades\Schema::hasTable('posts')) {
        $draftPostsList = \App\Models\Post::where('website_id', $websiteId)
            ->where('status', 'draft')
            ->latest('updated_at')
            ->take(5)
            ->get();
    }

    $draftPagesList = collect();
    if ($websiteId && \Illuminate\Support\Facades\Schema::hasTable('pages')) {
        $draftPagesList = \App\Models\Page::where('website_id', $websiteId)
            ->where('status', 'draft')
            ->latest('updated_at')
            ->take(5)
            ->get();
    }

    // Appearance Slots check (SCHEMA.md 2.6)
    $appearance = $website?->appearance;
    $appearanceFilled = $appearance && ($appearance->hero_description || $appearance->header_slogan || $appearance->hero_banner);

    $isWebsiteLive = $website && $website->status === 'aktif';
    $isWebsiteMaintenance = $website && $website->status === 'pemeliharaan';
    $dinasDisplayName = $dinas?->name ?? 'Kedinasan';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Kedinasan - CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-[#F8FAFC] text-slate-800 flex min-h-screen">

    <!-- 2. Sidebar Navigasi (Mutlak 6 Menu: Admin Kedinasan) -->
    <aside class="w-64 fixed top-0 left-0 bottom-0 bg-[#0f172a] text-slate-300 flex flex-col z-30 shadow-lg">
        <!-- Brand Header: Logo segienam CMS di kiri atas -->
        <div class="px-6 py-5 flex items-center gap-3 border-b border-slate-800/80">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-md shadow-cyan-500/20 shrink-0">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div>
                <h1 class="text-sm font-bold text-white leading-tight">CMS</h1>
                <p class="text-[11px] text-slate-400 font-medium">Diskominfo Kota Batu</p>
            </div>
        </div>

        <!-- Menu Navigasi: Tepat 6 Menu -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <!-- 1. Dashboard (Active) -->
            <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-lg bg-white text-[#2563EB] font-semibold shadow-sm transition-all">
                <svg class="w-5 h-5 text-[#2563EB] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span class="text-sm">Dashboard</span>
            </a>

            <!-- 2. Posts -->
            <a href="{{ route('dinas.posts.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span class="text-sm">Posts</span>
            </a>

            <!-- 3. Media -->
            <a href="{{ route('dinas.media.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <span class="text-sm">Media</span>
            </a>

            <!-- 4. Pages -->
            <a href="{{ route('dinas.pages.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span class="text-sm">Pages</span>
            </a>

            <!-- 5. Appearance -->
            <a href="{{ route('dinas.appearance.edit') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                    <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                    <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                    <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2z"></path>
                </svg>
                <span class="text-sm">Appearance</span>
            </a>

            <!-- 6. Settings -->
            <a href="{{ route('dinas.settings.edit') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span class="text-sm">Settings</span>
            </a>
        </nav>

        <!-- Developer Credit -->
        <div class="px-6 py-4 border-t border-slate-800/80 text-[11px] text-slate-400">
            <span>Developed by Azzaryansyaa</span>
        </div>
    </aside>

    <!-- Main Content Area kanan flex-1 -->
    <div class="ml-64 flex-1 flex flex-col min-h-screen bg-[#F8FAFC]">

        <!-- 3. Header Top Bar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4.5 flex items-center justify-between sticky top-0 z-20 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <!-- Kiri: Judul Halaman -->
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Dashboard</h2>
            </div>

            <!-- Kanan: Profil Pengguna (Admin [Nama Dinas]) -->
            <div class="flex items-center gap-3">
                <div class="relative group">
                    <button type="button" class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-full hover:bg-slate-100/80 transition-colors cursor-pointer border border-slate-200 focus:outline-none">
                        <div class="w-8 h-8 rounded-full border border-slate-300 text-slate-400 flex items-center justify-center bg-slate-50 shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-800">Admin {{ $dinasDisplayName }}</span>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <!-- Dropdown Logout -->
                    <div class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-100 py-1 hidden group-hover:block z-30">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs font-semibold text-slate-800 truncate">{{ $user?->name ?? 'Admin Kedinasan' }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ $user?->email ?? '' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2 font-medium transition-colors">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span>Keluar (Logout)</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- 4. Main Canvas (Grid 2 Kolom) -->
        <main class="p-8 flex-1">

            <!-- Baris 1: Kartu Metrik Utama (2 Kolom Sejajar) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                <!-- 1. Card Kiri — Statistik Posts Dinas -->
                <div class="relative overflow-hidden bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6 flex items-start justify-between">
                    <div class="flex items-start gap-4 z-10">
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 leading-snug">Total Posts: {{ $totalPosts }} Konten</h3>
                            <p class="text-sm text-slate-500 mt-1">{{ $publishedPosts }} Berita Terpublikasi · {{ $draftPosts }} Draft tersimpan</p>
                        </div>
                    </div>
                    <!-- Watermark Dokumen Transparan -->
                    <div class="absolute right-4 -bottom-2 text-slate-900 opacity-10 pointer-events-none">
                        <svg class="w-24 h-24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                </div>

                <!-- 2. Card Kanan — Penggunaan Media Tenant -->
                <div class="relative overflow-hidden bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6 flex items-start justify-between">
                    <div class="flex items-start gap-4 z-10">
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 leading-snug">Media Dinas: {{ $mediaCount }} Aset</h3>
                            <p class="text-sm text-slate-500 mt-1">Format JPG, PNG, WEBP, PDF · {{ $mediaSizeMb }} MB terpakai</p>
                        </div>
                    </div>
                    <!-- Watermark Galeri Media Transparan -->
                    <div class="absolute right-4 -bottom-2 text-slate-900 opacity-10 pointer-events-none">
                        <svg class="w-24 h-24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                    </div>
                </div>

            </div>

            <!-- Baris 2: Konten Operasional (2 Kolom Sejajar) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                <!-- 1. Card Bawah Kiri — Aktivitas Konten Terbaru -->
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 mb-4">Aktivitas Konten Terbaru</h3>

                        <!-- 5 Item Aktivitas Vertikal -->
                        <div class="space-y-4">
                            <!-- Item 1 (Biru): Berita dipublikasikan -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            Berita "{{ $recentPosts->where('status', 'published')->first()?->title ?? 'Penyuluhan Posyandu Balita' }}" dipublikasikan
                                        </p>
                                        <p class="text-xs text-slate-500 mt-0.5">Status berubah menjadi published oleh Admin</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">Hari ini, 10:15</span>
                            </div>

                            <!-- Item 2 (Hijau): Banner Hero diperbarui -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Banner Hero diperbarui</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Aset gambar baru diunggah pada slot Hero Banner</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">Kemarin, 14:20</span>
                            </div>

                            <!-- Item 3 (Kuning): Pengumuman disimpan -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            Pengumuman "{{ $recentPosts->where('status', 'draft')->first()?->title ?? 'Jadwal Pelayanan' }}" disimpan
                                        </p>
                                        <p class="text-xs text-slate-500 mt-0.5">Tersimpan sebagai draft</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">Kemarin, 09:30</span>
                            </div>

                            <!-- Item 4 (Ungu): Halaman "Visi & Misi" diperbarui -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Halaman "Visi & Misi" diperbarui</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Konten teks profil institusi disunting</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">2 hari yang lalu, 11:00</span>
                            </div>

                            <!-- Item 5 (Abu-abu): Dokumen PDF SOP Pelayanan ditambahkan -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Dokumen PDF SOP Pelayanan ditambahkan</p>
                                        <p class="text-xs text-slate-500 mt-0.5">File media siap disematkan ke artikel</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">3 hari yang lalu, 15:45</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Card Kiri: Link teks biru -->
                    <div class="pt-5 border-t border-slate-100 mt-6">
                        <a href="{{ route('dinas.posts.index') }}" class="inline-flex items-center gap-1 text-[#2563EB] text-sm font-medium hover:underline">
                            Lihat semua postingan &gt;
                        </a>
                    </div>
                </div>

                <!-- 2. Card Bawah Kanan — Quick Access Draft -->
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <h3 class="text-base font-bold text-slate-900">Quick Access Draft</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ ($draftPostsList->count() + $draftPagesList->count()) > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $draftPostsList->count() + $draftPagesList->count() }} Draft
                                </span>
                            </div>
                            <!-- Filter Tabs -->
                            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg text-xs font-medium">
                                <button type="button" onclick="filterDrafts('all', this)" class="draft-tab px-2.5 py-1 rounded-md transition font-semibold bg-white text-blue-600 shadow-2xs">Semua</button>
                                <button type="button" onclick="filterDrafts('posts', this)" class="draft-tab px-2.5 py-1 rounded-md transition text-slate-600 hover:text-slate-900">Posts ({{ $draftPostsList->count() }})</button>
                                <button type="button" onclick="filterDrafts('pages', this)" class="draft-tab px-2.5 py-1 rounded-md transition text-slate-600 hover:text-slate-900">Pages ({{ $draftPagesList->count() }})</button>
                            </div>
                        </div>

                        <!-- Draft Items List -->
                        <div class="space-y-3" id="drafts-list-container">
                            @php
                                $hasAnyDraft = $draftPostsList->isNotEmpty() || $draftPagesList->isNotEmpty();
                            @endphp

                            @if(!$hasAnyDraft)
                                <div class="py-12 text-center flex flex-col items-center justify-center">
                                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Tidak ada draft tertunda</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Semua artikel berita dan halaman statis Anda telah terpublikasi.</p>
                                </div>
                            @else
                                {{-- Draft Posts Items --}}
                                @foreach($draftPostsList as $post)
                                    <div class="draft-item draft-item-posts flex items-center justify-between p-3 rounded-lg border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition">
                                        <div class="flex items-center gap-3 min-w-0 pr-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700 shrink-0">
                                                Post: {{ $post->type ?? 'Berita' }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-800 truncate" title="{{ $post->title }}">{{ $post->title }}</p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">Disunting {{ $post->updated_at ? $post->updated_at->diffForHumans() : 'baru saja' }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('dinas.posts.edit', $post) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md bg-white border border-slate-200 text-xs font-semibold text-blue-600 hover:bg-blue-50 transition shrink-0 shadow-2xs">
                                            <span>Edit Draft</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                @endforeach

                                {{-- Draft Pages Items --}}
                                @foreach($draftPagesList as $page)
                                    <div class="draft-item draft-item-pages flex items-center justify-between p-3 rounded-lg border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition">
                                        <div class="flex items-center gap-3 min-w-0 pr-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-700 shrink-0">
                                                Page
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-800 truncate" title="{{ $page->title }}">{{ $page->title }}</p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">Disunting {{ $page->updated_at ? $page->updated_at->diffForHumans() : 'baru saja' }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('dinas.pages.edit', $page) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md bg-white border border-slate-200 text-xs font-semibold text-blue-600 hover:bg-blue-50 transition shrink-0 shadow-2xs">
                                            <span>Edit Draft</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Footer Card Kanan: Quick Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 mt-6 text-xs">
                        <a href="{{ route('dinas.posts.create') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-blue-600 font-medium transition">
                            <span class="w-4 h-4 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-[10px] font-bold">+</span>
                            <span>Tulis Post Baru</span>
                        </a>
                        <a href="{{ route('dinas.pages.create') }}" class="inline-flex items-center gap-1 text-slate-600 hover:text-blue-600 font-medium transition">
                            <span class="w-4 h-4 rounded-full bg-purple-100 text-purple-600 inline-flex items-center justify-center text-[10px] font-bold">+</span>
                            <span>Buat Halaman Baru</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- 5. Footer Platform -->
            <footer class="pt-6 pb-2 border-t border-slate-200/70 flex items-center justify-between text-xs text-[#94A3B8]">
                <p>CMS Diskominfo Kota Batu &bull; Developed by Azzaryansyaa</p>
                <p>© 2026 Diskominfo Kota Batu</p>
            </footer>

        </main>
    </div>

    <script>
    function filterDrafts(type, btn) {
        document.querySelectorAll('.draft-tab').forEach(el => {
            el.className = 'draft-tab px-2.5 py-1 rounded-md transition text-slate-600 hover:text-slate-900';
        });
        btn.className = 'draft-tab px-2.5 py-1 rounded-md transition font-semibold bg-white text-blue-600 shadow-2xs';

        document.querySelectorAll('.draft-item').forEach(el => {
            if (type === 'all') {
                el.style.display = 'flex';
            } else if (type === 'posts') {
                el.style.display = el.classList.contains('draft-item-posts') ? 'flex' : 'none';
            } else if (type === 'pages') {
                el.style.display = el.classList.contains('draft-item-pages') ? 'flex' : 'none';
            }
        });
    }
    </script>
</body>
</html>
