<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Halaman Statis (Pages) — {{ $dinas->name }}</title>
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
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { navy: { 900: '#0f172a' } }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-[#F8FAFC] text-slate-800 flex min-h-screen">

    <!-- Sidebar Navigasi -->
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
                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
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
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <span class="text-sm">Media</span>
            </a>

            <a href="{{ route('dinas.pages.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg bg-white text-[#2563EB] font-semibold shadow-sm transition-all">
                <svg class="w-5 h-5 text-[#2563EB] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span class="text-sm">Pages</span>
            </a>

            <a href="{{ route('dinas.appearance.edit') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2z"></path>
                </svg>
                <span class="text-sm">Appearance</span>
            </a>

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

    <!-- Main Content -->
    <div class="ml-64 flex-1 flex flex-col min-h-screen bg-[#F8FAFC]">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4.5 flex items-center justify-between sticky top-0 z-20 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Manajemen Halaman Institusi (Pages)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola konten statis seperti Profil Dinas, Visi &amp; Misi yang otomatis terhubung ke elemen Static Content website</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('site.show', $website->domain) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">
                    <span>Lihat Website</span>
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>

                <a href="{{ route('dinas.pages.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Halaman Baru</span>
                </a>
            </div>
        </header>

        <main class="p-8 flex-1">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Info Banner Integrasi Static Content --}}
            <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <span class="font-bold block text-sm mb-0.5">Integrasi Elemen Static Content</span>
                    <p class="text-blue-800/90 leading-relaxed">
                        Halaman pertama yang berstatus <strong>Published</strong> akan otomatis ditarik dan dirender ke dalam elemen <strong>Static Content</strong> pada website publik dinas Anda. Anda dapat mengubah isi teks profil, visi-misi, atau informasi kelembagaan kapan saja melalui tombol <strong>Edit</strong>.
                    </p>
                </div>
            </div>

            {{-- Table Pages --}}
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">Judul Halaman</th>
                                <th class="px-4 py-3.5">Slug URL</th>
                                <th class="px-4 py-3.5">Status</th>
                                <th class="px-4 py-3.5">Terakhir Diperbarui</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pages as $index => $page)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($page->parent_id !== null)
                                                @php $depth = $page->getDepth(); @endphp
                                                @if($depth === 2)
                                                    <span class="text-slate-400 font-mono text-sm">↳</span>
                                                    <span class="font-semibold text-slate-800 text-sm hover:text-blue-600 transition">
                                                        {{ $page->title }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                        Sub-menu ({{ $page->parent->title }})
                                                    </span>
                                                @elseif($depth === 3)
                                                    <span class="text-slate-400 font-mono text-sm pl-2">↳↳</span>
                                                    <span class="font-semibold text-slate-800 text-sm hover:text-blue-600 transition">
                                                        {{ $page->title }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        📦 Wadah Sub-sub-bab ({{ $page->parent?->title }})
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 font-mono text-sm pl-4">↳↳↳</span>
                                                    <span class="font-medium text-slate-700 text-sm hover:text-blue-600 transition">
                                                        {{ $page->title }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                        📄 Isi Wadah ({{ $page->parent?->title }})
                                                    </span>
                                                @endif
                                            @else
                                                <span class="font-bold text-slate-800 text-sm hover:text-blue-600 transition">
                                                    {{ $page->title }}
                                                </span>
                                                @php
                                                    $placementLabel = match($page->placement) {
                                                        'profil' => '👤 Profil (Umum)',
                                                        'profil_tentang' => '👤 Profil: Tentang Instansi',
                                                        'profil_sejarah' => '👤 Profil: Sejarah',
                                                        'profil_visi_misi' => '👤 Profil: Visi & Misi',
                                                        'profil_tupoksi' => '👤 Profil: Tugas & Fungsi',
                                                        'profil_struktur' => '👤 Profil: Struktur Organisasi',
                                                        'profil_pejabat' => '👤 Profil: Pejabat / Pimpinan',

                                                        'layanan' => '🛠️ Layanan (Umum)',
                                                        'layanan_daftar' => '🛠️ Layanan: Daftar Layanan',
                                                        'layanan_persyaratan' => '🛠️ Layanan: Persyaratan',
                                                        'layanan_prosedur' => '🛠️ Layanan: Prosedur / Alur',
                                                        'layanan_formulir' => '🛠️ Layanan: Formulir',
                                                        'layanan_status' => '🛠️ Layanan: Cek Status',

                                                        'informasi' => 'ℹ️ Informasi (Umum)',
                                                        'informasi_pengumuman' => 'ℹ️ Informasi: Pengumuman',
                                                        'informasi_agenda' => 'ℹ️ Informasi: Agenda',
                                                        'informasi_publik' => 'ℹ️ Informasi: Info Publik',
                                                        'informasi_faq' => 'ℹ️ Informasi: FAQ',

                                                        'publikasi' => '📚 Publikasi (Umum)',
                                                        'publikasi_dokumen' => '📚 Publikasi: Dokumen',
                                                        'publikasi_peraturan' => '📚 Publikasi: Peraturan',
                                                        'publikasi_laporan' => '📚 Publikasi: Laporan',
                                                        'publikasi_statistik' => '📚 Publikasi: Data / Statistik',
                                                        'publikasi_galeri' => '📚 Publikasi: Galeri',

                                                        'kontak' => '📞 Kontak (Umum)',
                                                        'kontak_alamat' => '📞 Kontak: Alamat',
                                                        'kontak_email' => '📞 Kontak: Email',
                                                        'kontak_telepon' => '📞 Kontak: Telepon',
                                                        'kontak_medsos' => '📞 Kontak: Medsos',
                                                        'kontak_peta' => '📞 Kontak: Peta / Lokasi',

                                                        'header_menu' => '📌 Menu Utama Header',
                                                        'beranda' => '🏠 Tab Beranda (Static Content)',
                                                        default => '📄 ' . ucfirst($page->placement),
                                                    };

                                                    $badgeColor = match(true) {
                                                        $page->placement === 'beranda' => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
                                                        str_starts_with($page->placement, 'profil') => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                                        str_starts_with($page->placement, 'layanan') => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        str_starts_with($page->placement, 'informasi') => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        str_starts_with($page->placement, 'publikasi') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        str_starts_with($page->placement, 'kontak') => 'bg-sky-50 text-sky-700 border-sky-200',
                                                        $page->placement === 'header_menu' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                        default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $badgeColor }} border">
                                                    {{ $placementLabel }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 line-clamp-1 mt-1 max-w-lg">
                                            {{ Str::limit(strip_tags($page->content), 90) }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-mono text-slate-500">
                                        /{{ $page->slug }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($page->status === 'published')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Published
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Draft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-500 font-medium">
                                        {{ $page->updated_at ? $page->updated_at->format('d M Y, H:i') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('dinas.pages.edit', $page) }}"
                                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                                            Edit Konten
                                        </a>
                                        <form method="POST" action="{{ route('dinas.pages.destroy', $page) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus halaman ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-semibold hover:underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada halaman institusi</p>
                                        <p class="text-[11px] mt-0.5">Klik tombol "Tambah Halaman Baru" untuk menambahkan profil atau visi-misi dinas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pages->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $pages->links() }}
                    </div>
                @endif
            </div>

            <!-- Footer Platform -->
            <footer class="pt-8 pb-4 mt-8 border-t border-slate-200/70 flex items-center justify-between text-xs text-[#94A3B8]">
                <p>CMS Diskominfo Kota Batu &bull; Developed by Azzaryansyaa</p>
                <p>&copy; {{ date('Y') }} Diskominfo Kota Batu</p>
            </footer>
        </main>
    </div>

</body>
</html>
