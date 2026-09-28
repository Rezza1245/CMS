<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Website - CMS Diskominfo Kota Batu</title>
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

    <!-- Sidebar Navigasi Kiri -->
    <aside class="w-64 fixed top-0 left-0 bottom-0 bg-[#0f172a] text-slate-300 flex flex-col z-30 shadow-lg">
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

        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span class="text-sm">Dashboard</span>
            </a>

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

            <a href="{{ route('dinas.media.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <span class="text-sm">Media</span>
            </a>

            <a href="{{ route('dinas.pages.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span class="text-sm">Pages</span>
            </a>

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

            <!-- 6. Settings (Active) -->
            <a href="{{ route('dinas.settings.edit') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg bg-white text-[#2563EB] font-semibold shadow-sm transition-all">
                <svg class="w-5 h-5 text-[#2563EB] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

    <!-- Main Content Area -->
    <div class="ml-64 flex-1 flex flex-col min-h-screen bg-[#F8FAFC]">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4.5 flex items-center justify-between sticky top-0 z-20 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Pengaturan Website Kedinasan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola identitas situs, kebijakan privasi, dan preferensi publikasi website {{ $dinas->name }}</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200/70 shadow-2xs">
                    <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                        {{ strtoupper(substr($dinas->code, 0, 2)) }}
                    </div>
                    <span class="text-xs font-semibold text-slate-700">Admin {{ $dinas->name }}</span>
                </div>
            </div>
        </header>

        <main class="flex-1 px-8 py-6 max-w-4xl w-full">
            @if (session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium shadow-xs">
                    <ul class="list-disc list-inside space-y-1 text-[11px] text-red-700">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('dinas.settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Card 1: Konfigurasi Umum Situs Dinas -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
                    <div class="pb-4 mb-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                                1. Konfigurasi Umum Situs Dinas
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Identitas utama situs website dan status operasional kedinasan</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Admin Managed
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Situs Website Dinas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="site_title" value="{{ old('site_title', $general['site_title'] ?? $website->name) }}" required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <p class="text-[11px] text-slate-400 mt-1">Judul yang muncul di header peramban dan tab situs publik.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status Operasional Website <span class="text-red-500">*</span>
                            </label>
                            <select name="site_status" required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-medium">
                                <option value="aktif" {{ old('site_status', $general['site_status'] ?? $website->status) === 'aktif' ? 'selected' : '' }}>Aktif (Dapat Diakses Publik)</option>
                                <option value="pemeliharaan" {{ old('site_status', $general['site_status'] ?? $website->status) === 'pemeliharaan' ? 'selected' : '' }}>Pemeliharaan Internal</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Kebijakan Privasi & Publikasi -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
                    <div class="pb-4 mb-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                                2. Kebijakan Privasi &amp; Publikasi
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pengaturan visibilitas data publik dan kontak privasi masyarakat</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Admin Managed
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tingkat Visibilitas Publik <span class="text-red-500">*</span>
                            </label>
                            <select name="public_visibility" required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-medium">
                                <option value="publik" {{ old('public_visibility', $privacy['public_visibility'] ?? 'publik') === 'publik' ? 'selected' : '' }}>Publik (Semua artikel published terlihat terbuka)</option>
                                <option value="terbatas" {{ old('public_visibility', $privacy['public_visibility'] ?? 'publik') === 'terbatas' ? 'selected' : '' }}>Terbatas (Hanya melayani halaman statis)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Email Layanan Informasi &amp; Pengaduan Privasi
                            </label>
                            <input type="email" name="privacy_contact_email" value="{{ old('privacy_contact_email', $privacy['privacy_contact_email'] ?? $dinas->contact_email) }}"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-mono">
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Pengaturan Website</span>
                    </button>
                </div>
            </form>
        </main>

        <footer class="pt-8 pb-4 mt-8 border-t border-slate-200/70 flex items-center justify-between text-xs text-[#94A3B8] px-8">
            <p>CMS Diskominfo Kota Batu &bull; Developed by Azzaryansyaa</p>
            <p>© 2026 Diskominfo Kota Batu</p>
        </footer>
    </div>
</body>
</html>
