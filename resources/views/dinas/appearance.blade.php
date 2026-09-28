<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Appearance Slots — {{ $dinas->name }}</title>
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

    <!-- Sidebar Navigasi (Mutlak 6 Menu: Admin Kedinasan) -->
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
            <!-- 1. Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

            <!-- 5. Appearance (Active) -->
            <a href="{{ route('dinas.appearance.edit') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg bg-white text-[#2563EB] font-semibold shadow-sm transition-all">
                <svg class="w-5 h-5 text-[#2563EB] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

        <!-- Top Bar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4.5 flex items-center justify-between sticky top-0 z-20 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Pengaturan Appearance Slots</h2>
                <p class="text-xs text-slate-500 mt-0.5">Isi data variabel tampilan dinas Anda sesuai slot yang disediakan Global Template</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('site.show', $website->domain) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition"
                   title="Buka Website Publik Dinas Anda">
                    <span>Lihat Website Anda</span>
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>

                <div class="relative group">
                    <button type="button" class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-full hover:bg-slate-100/80 transition-colors cursor-pointer border border-slate-200 focus:outline-none">
                        <div class="w-8 h-8 rounded-full border border-slate-300 text-slate-400 flex items-center justify-center bg-slate-50 shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-800">Admin {{ $dinas->name }}</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="p-8 flex-1">

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('dinas.appearance.update') }}" enctype="multipart/form-data" class="space-y-6 max-w-5xl">
                @csrf
                @method('PUT')

                {{-- Card 1: Slot Banner Hero (TEMPLATE.md Bab 3 & Bab 4.1) --}}
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Slot Hero Banner</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Nilai teks dan gambar latar yang tampil pada banner paling atas website dinas Anda</p>
                        </div>
                        @if($heroBannerEditable)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Admin Editable
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                Template Controlled
                            </span>
                        @endif
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Judul / Slogan Utama Hero
                            </label>
                            <input type="text" name="header_slogan" value="{{ old('header_slogan', $appearance->header_slogan) }}"
                                placeholder="Contoh: Mewujudkan Kota Batu Cerdas dan Terintegrasi"
                                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <p class="text-[11px] text-slate-400 mt-1">Teks judul besar yang langsung terlihat masyarakat saat pertama kali mengunjungi website.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Deskripsi Hero / Subjudul
                            </label>
                            <textarea name="hero_description" rows="3"
                                placeholder="Jelaskan secara singkat visi, tugas, atau sambutan pengantar kedinasan Anda..."
                                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">{{ old('hero_description', $appearance->hero_description) }}</textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Penjelasan ringkas di bawah slogan utama hero.</p>
                        </div>

                        {{-- Unggah Gambar Latar Banner Hero Dari Perangkat --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Gambar Latar Banner Hero
                            </label>

                            @if($heroBannerEditable)
                                <div class="space-y-3">
                                    {{-- Pratinjau Banner Aktif --}}
                                    @php
                                        $currentBanner = $appearance->hero_banner;
                                        $bannerSrc = null;
                                        if ($currentBanner) {
                                            $bannerSrc = (str_starts_with($currentBanner, 'http://') || str_starts_with($currentBanner, 'https://') || str_starts_with($currentBanner, '//'))
                                                ? $currentBanner
                                                : asset(str_starts_with($currentBanner, 'storage/') ? $currentBanner : 'storage/' . ltrim($currentBanner, '/'));
                                        }
                                    @endphp

                                    @if($bannerSrc)
                                        <div id="current-banner-wrapper" class="relative rounded-lg overflow-hidden border border-slate-200 bg-slate-900 group">
                                            <div class="h-44 w-full bg-cover bg-center" style="background-image: url('{{ $bannerSrc }}');"></div>
                                            <div class="absolute inset-0 bg-slate-950/60 flex items-center justify-between p-4 text-white opacity-90">
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-600 text-white mb-1">Gambar Banner Aktif</span>
                                                    <p class="text-xs text-slate-200 truncate max-w-md font-mono">{{ basename($currentBanner) }}</p>
                                                </div>
                                                <label class="inline-flex items-center gap-1.5 text-xs bg-red-600/90 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg cursor-pointer transition">
                                                    <input type="checkbox" name="remove_hero_banner" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                                    <span>Hapus Banner</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Area Unggah Berkas Dari Perangkat --}}
                                    <div class="border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-xl p-5 text-center bg-slate-50/50 hover:bg-blue-50/20 transition cursor-pointer"
                                         onclick="document.getElementById('hero_banner_file').click()">
                                        <input type="file" name="hero_banner_file" id="hero_banner_file"
                                               accept="image/png,image/jpeg,image/jpg,image/webp"
                                               class="hidden" onchange="previewLocalHeroImage(this)">
                                        
                                        <div id="upload-prompt" class="flex flex-col items-center">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-700">
                                                <span class="text-blue-600 hover:underline">Klik untuk mengunggah</span> berkas gambar dari perangkat Anda
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">PNG, JPG, JPEG, atau WEBP (Maksimal 5MB)</p>
                                        </div>

                                        {{-- Live Preview Elemen Baru --}}
                                        <div id="local-preview-container" class="hidden mt-3 text-left">
                                            <div class="relative rounded-lg overflow-hidden border border-blue-200 bg-slate-100">
                                                <img id="local-preview-img" src="" alt="Pratinjau Banner Baru" class="w-full h-40 object-cover">
                                                <div class="absolute bottom-0 inset-x-0 bg-slate-900/80 text-white px-3 py-1.5 text-xs flex items-center justify-between">
                                                    <span id="local-file-name" class="truncate font-mono text-[11px]"></span>
                                                    <span class="text-emerald-400 text-[10px] font-semibold uppercase tracking-wider">Siap Disimpan</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-slate-400">Gambar yang diunggah akan disimpan di server CMS dan otomatis ditautkan ke banner hero website publik.</p>
                                </div>
                            @else
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-center gap-2 text-slate-600 mb-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span class="text-xs font-semibold">Slot Dikunci Oleh Template Global</span>
                                    </div>
                                    <p class="text-xs text-slate-500">
                                        Super Admin mengunci gambar latar Hero pada status <strong class="text-slate-700">Template Controlled</strong>. Admin Kedinasan tidak diberikan izin untuk mengubah atau mengunggah gambar latar untuk elemen ini.
                                    </p>
                                    @if($heroBannerDefault)
                                        <div class="mt-3 pt-3 border-t border-slate-200">
                                            <span class="text-[11px] font-medium text-slate-400 block mb-1">Gambar Bawaan Blueprint Template:</span>
                                            <div class="h-28 rounded-md bg-cover bg-center border border-slate-200" style="background-image: url('{{ e($heroBannerDefault) }}');"></div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card 2: Identitas Visual & Logo --}}
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Identitas Visual: Logo Resmi</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Logo instansi resmi yang ditampilkan pada header / navbar website</p>
                        </div>
                        @if($logoEditable)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Admin Editable
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                Template Controlled
                            </span>
                        @endif
                    </div>

                    {{-- Bagian Logo Resmi Dinas --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Logo Resmi Dinas (Ditampilkan pada Header / Navbar)
                        </label>

                        @if($logoEditable)
                            <div class="space-y-3">
                                @php
                                    $currentLogo = $appearance->logo;
                                    $logoSrc = null;
                                    if ($currentLogo) {
                                        $logoSrc = (str_starts_with($currentLogo, 'http://') || str_starts_with($currentLogo, 'https://') || str_starts_with($currentLogo, '//'))
                                            ? $currentLogo
                                            : asset(str_starts_with($currentLogo, 'storage/') ? $currentLogo : 'storage/' . ltrim($currentLogo, '/'));
                                    }
                                @endphp

                                @if($logoSrc)
                                    <div class="flex items-center justify-between p-3.5 rounded-lg border border-slate-200 bg-slate-50/80">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center p-1 shadow-2xs">
                                                <img src="{{ $logoSrc }}" alt="Logo Saat Ini" class="max-h-full max-w-full object-contain">
                                            </div>
                                            <div>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-600 text-white mb-0.5">Logo Aktif</span>
                                                <p class="text-xs text-slate-600 font-mono truncate max-w-xs">{{ basename($currentLogo) }}</p>
                                            </div>
                                        </div>
                                        <label class="inline-flex items-center gap-1.5 text-xs bg-red-600/90 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg cursor-pointer transition">
                                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                            <span>Hapus Logo</span>
                                        </label>
                                    </div>
                                @endif

                                {{-- Area Unggah Logo Dari Komputer --}}
                                <div class="border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-xl p-4 text-center bg-slate-50/50 hover:bg-blue-50/20 transition cursor-pointer"
                                     onclick="document.getElementById('logo_file').click()">
                                    <input type="file" name="logo_file" id="logo_file"
                                           accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                           class="hidden" onchange="previewLocalLogoImage(this)">

                                    <div id="upload-logo-prompt" class="flex flex-col items-center">
                                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-700">
                                            <span class="text-blue-600 hover:underline">Klik untuk mengunggah logo</span> dari komputer Anda
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">PNG transparan, JPG, WEBP, atau SVG (Maksimal 2MB)</p>
                                    </div>

                                    {{-- Live Preview Logo Baru --}}
                                    <div id="local-logo-preview-container" class="hidden mt-2 flex items-center justify-between p-2.5 rounded-lg border border-blue-200 bg-white">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded border border-slate-200 flex items-center justify-center p-1 bg-slate-50">
                                                <img id="local-logo-preview-img" src="" alt="Pratinjau Logo Baru" class="max-h-full max-w-full object-contain">
                                            </div>
                                            <span id="local-logo-file-name" class="font-mono text-xs text-slate-700 truncate"></span>
                                        </div>
                                        <span class="text-emerald-500 text-[10px] font-semibold uppercase tracking-wider">Siap Disimpan</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center gap-2 text-slate-600 mb-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="text-xs font-semibold">Slot Logo Dikunci Oleh Template Global</span>
                                </div>
                                <p class="text-xs text-slate-500">
                                    Super Admin menetapkan logo institusi seragam pada status <strong class="text-slate-700">Template Controlled</strong>. Admin Kedinasan tidak diizinkan mengubah atau mengunggah logo kustom.
                                </p>
                                @if($logoDefault)
                                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center gap-3">
                                        <span class="text-[11px] font-medium text-slate-400">Logo Bawaan Template:</span>
                                        <img src="{{ e($logoDefault) }}" alt="Logo Default" class="h-8 w-auto object-contain">
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card 3: Pengaturan Footer Website (Kolom 1 & Kolom 3) --}}
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Pengaturan Footer Website (Kolom 1 &amp; Kolom 3)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Kelola identitas, slogan instansi, serta informasi tambahan pada footer website publik</p>
                        </div>
                        @if($footerSloganEditable || $footerAboutTitleEditable || $footerAboutTextEditable || $footerTitleEditable)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Admin Editable
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                Template Controlled
                            </span>
                        @endif
                    </div>

                    {{-- Kolom 1 Footer --}}
                    <div class="mb-6 pb-6 border-b border-slate-100">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 inline-flex items-center justify-center text-[10px] font-bold">1</span>
                            Kolom 1: Identitas &amp; Slogan Instansi
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Nama / Judul Instansi Footer
                                </label>
                                @if($footerTitleEditable)
                                    <input type="text" name="footer_title" value="{{ old('footer_title', $appearance->footer_title) }}"
                                        placeholder="Contoh: {{ $dinas->name }}"
                                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                                    <p class="text-[11px] text-slate-400 mt-1">Default: nama dinas (<strong>{{ $dinas->name }}</strong>).</p>
                                @else
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-500">
                                        🔒 Slot Judul Footer dikunci oleh Template Global. Menampilkan: <strong class="text-slate-700">{{ $appearance->footer_title ?: $dinas->name }}</strong>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Slogan Penutup Footer (Kolom 1)
                                </label>
                                @if($footerSloganEditable)
                                    <input type="text" name="footer_slogan" value="{{ old('footer_slogan', $appearance->footer_slogan) }}"
                                        placeholder="Contoh: Portal informasi dan layanan kedinasan resmi Pemerintah Kota Batu."
                                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                                    <p class="text-[11px] text-slate-400 mt-1">Slogan atau keterangan instansi di bawah nama dinas.</p>
                                @else
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-500">
                                        🔒 Slot Slogan Footer dikunci oleh Template Global (Template Controlled).
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Kolom 3 Footer --}}
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 inline-flex items-center justify-center text-[10px] font-bold">3</span>
                            Kolom 3: Informasi Tambahan / Visi &amp; Komitmen
                        </h4>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Judul Informasi Tambahan (Kolom 3)
                                </label>
                                @if($footerAboutTitleEditable)
                                    <input type="text" name="footer_about_title" value="{{ old('footer_about_title', $appearance->footer_about_title) }}"
                                        placeholder="Contoh: Pemerintah Kota Batu"
                                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                                    <p class="text-[11px] text-slate-400 mt-1">Default general: <em>Pemerintah Kota Batu</em> (bisa disesuaikan per dinas).</p>
                                @else
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-500">
                                        🔒 Slot Judul Informasi Tambahan dikunci oleh Template Global.
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Teks Informasi Tambahan (Kolom 3)
                                </label>
                                @if($footerAboutTextEditable)
                                    <textarea name="footer_about_text" rows="3"
                                        placeholder="Contoh: Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu."
                                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">{{ old('footer_about_text', $appearance->footer_about_text) }}</textarea>
                                    <p class="text-[11px] text-slate-400 mt-1">Keterangan visi pelayanan atau komitmen keterbukaan informasi dinas.</p>
                                @else
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-500">
                                        🔒 Slot Deskripsi Kolom 3 dikunci oleh Template Global.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Informasi Kontak Resmi Kedinasan (Footer & Narahubung Utama) --}}
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Informasi Kontak Resmi Dinas (Footer &amp; Narahubung Utama)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Data alamat kantor, email resmi, dan nomor telepon yang ditampilkan pada Footer website publik. Untuk menambah cabang kontak di Header (Instagram, Maps, dll.), kelola melalui menu Pages (Target: Sub-menu Kontak).</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Admin Editable
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alamat Fisik Kantor Dinas
                            </label>
                            <textarea name="address" rows="2"
                                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">{{ old('address', $dinas->address) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Email Resmi Kedinasan
                                </label>
                                <input type="email" name="contact_email" value="{{ old('contact_email', $dinas->contact_email) }}"
                                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Nomor Telepon / Helpdesk
                                </label>
                                <input type="text" name="phone" value="{{ old('phone', $dinas->phone) }}"
                                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-5 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold text-sm transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Perubahan Slot</span>
                    </button>
                </div>
            </form>

            <!-- Footer Platform -->
            <footer class="pt-8 pb-4 mt-8 border-t border-slate-200/70 flex items-center justify-between text-xs text-[#94A3B8]">
                <p>CMS Diskominfo Kota Batu &bull; Developed by Azzaryansyaa</p>
                <p>&copy; {{ date('Y') }} Diskominfo Kota Batu</p>
            </footer>

        </main>
    </div>

    <script>
    function previewLocalHeroImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('local-preview-container');
                const previewImg = document.getElementById('local-preview-img');
                const fileName = document.getElementById('local-file-name');
                if (previewContainer && previewImg && fileName) {
                    previewImg.src = e.target.result;
                    fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    previewContainer.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function previewLocalLogoImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('local-logo-preview-container');
                const previewImg = document.getElementById('local-logo-preview-img');
                const fileName = document.getElementById('local-logo-file-name');
                if (previewContainer && previewImg && fileName) {
                    previewImg.src = e.target.result;
                    fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    previewContainer.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    }
    </script>
</body>
</html>
