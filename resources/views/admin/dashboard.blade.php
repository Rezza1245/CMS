@php
    $activeTemplateRecord = \Illuminate\Support\Facades\Schema::hasTable('templates')
        ? \App\Models\Template::where('status', 'aktif')->first()
        : null;
    $activeTemplateName = $activeTemplateRecord?->name ?? 'Portal Resmi Kedinasan';
    $activeTemplateId = $activeTemplateRecord?->id ?? (\Illuminate\Support\Facades\Schema::hasTable('templates') ? \App\Models\Template::first()?->id : null) ?? 1;

    $adminCount = \Illuminate\Support\Facades\Schema::hasColumn('users', 'role')
        ? \App\Models\User::where('role', 'admin_dinas')->count()
        : 0;

    $hasWebsitesTable = \Illuminate\Support\Facades\Schema::hasTable('websites');
    $totalWebsites = $hasWebsitesTable ? \App\Models\Website::count() : 0;
    $activeWebsitesCount = $hasWebsitesTable ? \App\Models\Website::where('status', 'aktif')->count() : 0;
    $maintenanceWebsitesCount = $hasWebsitesTable ? \App\Models\Website::where('status', 'pemeliharaan')->count() : 0;
    $inactiveWebsitesCount = $hasWebsitesTable ? \App\Models\Website::where('status', 'nonaktif')->count() : 0;
    $recentWebsites = $hasWebsitesTable
        ? \App\Models\Website::with('dinas')->latest('updated_at')->take(4)->get()
        : collect();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CMS Diskominfo Kota Batu</title>
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
<body class="font-sans antialiased bg-slate-50 text-slate-800 flex min-h-screen">

    <!-- A. Sidebar Navigasi Kiri -->
    <aside class="w-64 fixed top-0 left-0 bottom-0 bg-[#0f172a] text-slate-300 flex flex-col z-30 shadow-lg">
        <!-- Header Brand (Top) -->
        <div class="px-6 py-5 flex items-center gap-3 border-b border-slate-800/80">
            <!-- Logo: Kubus/geometris cyan-biru (w-8 h-8) -->
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

        <!-- Menu Items -->
        <nav class="flex-1 px-4 py-6 space-y-2">
            <!-- Dashboard (Active): Background putih penuh, teks biru terang, icon kotak/home biru, melengkung -->
            <a href="#" class="flex items-center gap-3.5 px-4 py-3 rounded-xl bg-white text-blue-600 font-semibold shadow-sm transition-all">
                <svg class="w-5 h-5 text-blue-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span class="text-sm">Dashboard</span>
            </a>

            <!-- Template -->
            <a href="{{ route('admin.templates.builder', $activeTemplateId) }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span class="text-sm">Template</span>
            </a>

            <!-- Website Dinas -->
            <a href="{{ route('admin.websites.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
                <span class="text-sm">Website Dinas</span>
            </a>

            <!-- User -->
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span class="text-sm">User</span>
            </a>

            <!-- Settings -->
            <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
    <div class="ml-64 flex-1 flex flex-col min-h-screen bg-slate-50">

        <!-- B. Header / Topbar Utama -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4.5 flex items-center justify-between sticky top-0 z-20 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <!-- Kiri: Teks judul halaman "Dashboard" -->
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard</h2>
            </div>

            <!-- Kanan: User Profile Badge -->
            <div class="flex items-center gap-3">
                <div class="relative group">
                    <button type="button" class="flex items-center gap-2.5 px-3 py-1.5 rounded-full hover:bg-slate-100/80 transition-colors cursor-pointer border border-slate-200/60 focus:outline-none">
                        <!-- Avatar icon bulat outline -->
                        <div class="w-8 h-8 rounded-full border border-slate-300 text-slate-400 flex items-center justify-center bg-slate-50 shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <!-- Label: Super Admin -->
                        <span class="text-sm font-semibold text-slate-800">{{ auth()->user()?->role === 'super_admin' ? 'Super Admin' : (auth()->user()?->name ?? 'Super Admin') }}</span>
                        <!-- Chevron dropdown icon v kecil -->
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 hidden group-hover:block z-30">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()?->name ?? 'Super Admin' }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()?->email ?? 'superadmin@batukota.go.id' }}</p>
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

        <!-- Main Workspace -->
        <main class="p-8 flex-1">

            <!-- C. Baris 1: Stat Summary Cards (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                <!-- 1. Card Template Aktif -->
                <div class="relative overflow-hidden bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex items-start justify-between">
                    <div class="flex items-start gap-4 z-10">
                        <!-- Icon Kiri: Lingkaran background biru muda (bg-blue-50), icon layout/tabel biru (text-blue-600) -->
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                            </svg>
                        </div>
                        <div>
                            <!-- Judul: Template Aktif: [Nama Template] -->
                            <h3 class="text-lg font-bold text-slate-900 leading-snug">Template Aktif: {{ $activeTemplateName }}</h3>
                            <!-- Subtitle: Template yang sedang digunakan saat ini -->
                            <p class="text-sm text-slate-400 mt-1">Template yang sedang digunakan saat ini</p>
                            <!-- Aksi Cepat Super Admin: Edit di Builder & Preview Template -->
                            <div class="flex items-center gap-3 mt-3">
                                <a href="{{ route('admin.templates.builder', $activeTemplateId) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                                    <span>Edit di Builder</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <span class="text-slate-300">•</span>
                                <a href="{{ route('admin.templates.preview', $activeTemplateId) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:underline">
                                    <span>Preview Template</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Watermark Kanan: Icon siluet gedung/pilar transparan -->
                    <div class="absolute right-4 -bottom-2 text-slate-900 opacity-[0.04] pointer-events-none">
                        <svg class="w-24 h-24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="2" y1="20" x2="22" y2="20"></line>
                            <line x1="6" y1="20" x2="6" y2="8"></line>
                            <line x1="10" y1="20" x2="10" y2="8"></line>
                            <line x1="14" y1="20" x2="14" y2="8"></line>
                            <line x1="18" y1="20" x2="18" y2="8"></line>
                            <polygon points="12 2 2 7 22 7 12 2"></polygon>
                        </svg>
                    </div>
                </div>

                <!-- 2. Card Jumlah Admin -->
                <div class="relative overflow-hidden bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex items-start justify-between">
                    <div class="flex items-start gap-4 z-10">
                        <!-- Icon Kiri: Lingkaran background biru muda (bg-blue-50), icon dua user (text-blue-600) -->
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div>
                            <!-- Judul: Jumlah Admin Kedinasan: [Jumlah] -->
                            <h3 class="text-lg font-bold text-slate-900 leading-snug">Jumlah Admin Kedinasan: {{ $adminCount }}</h3>
                            <!-- Subtitle: Total admin kedinasan terdaftar -->
                            <p class="text-sm text-slate-400 mt-1">Total admin kedinasan terdaftar</p>
                            <div class="mt-3">
                                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                                    <span>Kelola User</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Watermark Kanan: Icon siluet grup user transparan -->
                    <div class="absolute right-4 -bottom-2 text-slate-900 opacity-[0.04] pointer-events-none">
                        <svg class="w-24 h-24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                </div>

            </div>

            <!-- D. Baris 2: Detail Section (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                <!-- 1. Card "Aktivitas Terbaru" (Kiri) -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <!-- Title Card dengan Badge Status Emerald untuk metrik sehat/online -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-base font-bold text-slate-900">Aktivitas Terbaru</h3>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Online
                            </span>
                        </div>

                        <!-- List Item (5 Baris Aktivitas) -->
                        <div class="space-y-4">
                            <!-- Baris 1: Icon lingkaran biru (Dokumen) -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Template "{{ $activeTemplateName }}" diaktifkan</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Template berhasil diatur sebagai template aktif</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">Hari ini, 09:45</span>
                            </div>

                            <!-- Baris 2: Icon lingkaran hijau (User) -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="20" y1="8" x2="20" y2="14"></line>
                                            <line x1="23" y1="11" x2="17" y2="11"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Admin kedinasan baru ditambahkan</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Akun "Diskominfo Bidang IKP" berhasil ditambahkan</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">Kemarin, 14:30</span>
                            </div>

                            <!-- Baris 3: Icon lingkaran kuning/oranye (Pensil/Edit) -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Template "Layanan Publik" diperbarui</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Perubahan pada konfigurasi template berhasil disimpan</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">Kemarin, 10:12</span>
                            </div>

                            <!-- Baris 4: Icon lingkaran ungu (User Edit) -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Admin kedinasan diperbarui</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Informasi akun "Dinas Pariwisata" berhasil diperbarui</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">2 hari yang lalu, 16:05</span>
                            </div>

                            <!-- Baris 5: Icon lingkaran abu-abu (Gear) -->
                            <div class="flex items-start justify-between gap-4 py-1">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Pengaturan sistem diperbarui</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi pengaturan umum berhasil disimpan</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap mt-1">3 hari yang lalu, 11:20</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer: Link teks biru dengan panah Lihat semua aktivitas > -->
                    <div class="pt-5 border-t border-slate-100 mt-6">
                        <a href="{{ route('admin.activities.index') }}" class="inline-flex items-center gap-1 text-blue-600 text-sm font-semibold hover:underline">
                            Lihat semua aktivitas &gt;
                        </a>
                    </div>
                </div>

                <!-- 2. Card "Monitoring Website Dinas" (Kanan) -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <!-- Title & Metrik Badge -->
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <h3 class="text-base font-bold text-slate-900">Monitoring Website Dinas</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $totalWebsites }} Terdaftar
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium">Multi-Tenant Status</span>
                        </div>

                        <!-- Mini Status Bar: 3 Metrik Status -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="p-2.5 rounded-lg bg-emerald-50/70 border border-emerald-100 flex flex-col">
                                <span class="text-[10px] font-semibold text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Live / Aktif
                                </span>
                                <span class="text-lg font-bold text-emerald-700 mt-1">{{ $activeWebsitesCount }}</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-amber-50/70 border border-amber-100 flex flex-col">
                                <span class="text-[10px] font-semibold text-amber-800 uppercase tracking-wider flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Pemeliharaan
                                </span>
                                <span class="text-lg font-bold text-amber-700 mt-1">{{ $maintenanceWebsitesCount }}</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-slate-100/80 border border-slate-200 flex flex-col">
                                <span class="text-[10px] font-semibold text-slate-600 uppercase tracking-wider flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Nonaktif
                                </span>
                                <span class="text-lg font-bold text-slate-700 mt-1">{{ $inactiveWebsitesCount }}</span>
                            </div>
                        </div>

                        <!-- Daftar Ringkas Website Dinas Terkini -->
                        <div class="space-y-2.5">
                            @forelse($recentWebsites as $web)
                                <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition">
                                    <div class="flex items-center gap-3 min-w-0 pr-2">
                                        <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-blue-600 font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($web->dinas?->code ?? $web->domain, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate" title="{{ $web->name }}">{{ $web->name }}</p>
                                            <p class="text-[10px] text-slate-400 truncate">{{ $web->dinas?->name ?? 'Instansi Kedinasan' }} • <span class="font-mono text-slate-500">/site/{{ $web->domain }}</span></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($web->status === 'aktif')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Live
                                            </span>
                                        @elseif($web->status === 'pemeliharaan')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Maintenance
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                Nonaktif
                                            </span>
                                        @endif
                                        <a href="{{ route('site.show', $web->domain) }}" target="_blank" class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-white transition" title="Lihat Website">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                        <a href="{{ route('admin.websites.edit', $web) }}" class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-white transition" title="Edit Website">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-slate-400">
                                    <p class="text-xs font-medium">Belum ada website dinas yang didaftarkan.</p>
                                    <a href="{{ route('admin.websites.create') }}" class="text-xs font-semibold text-blue-600 hover:underline mt-1 inline-block">+ Daftarkan Website Baru</a>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Card Footer: Quick Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 mt-5 text-xs">
                        <a href="{{ route('admin.websites.create') }}" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 font-semibold transition">
                            <span class="w-4 h-4 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-[10px] font-bold">+</span>
                            <span>Daftarkan Website Baru</span>
                        </a>
                        <a href="{{ route('admin.websites.index') }}" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-800 font-medium transition">
                            <span>Kelola Semua Website</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- E. Footer Halaman -->
            <footer class="pt-6 pb-2 border-t border-slate-200/70 flex items-center justify-between text-xs text-slate-500">
                <p>CMS Diskominfo Kota Batu &bull; Developed by Azzaryansyaa</p>
                <p>© 2024 Diskominfo Kota Batu</p>
            </footer>

        </main>
    </div>

</body>
</html>
