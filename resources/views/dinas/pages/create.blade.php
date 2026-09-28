<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Halaman Baru — {{ $dinas->name }}</title>
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

    <div class="ml-64 flex-1 flex flex-col min-h-screen bg-[#F8FAFC]">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4.5 flex items-center justify-between sticky top-0 z-20 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <div class="flex items-center gap-3">
                <a href="{{ route('dinas.pages.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Tambah Halaman Baru</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tulis profil dinas, visi misi, atau informasi statis kelembagaan</p>
                </div>
            </div>
        </header>

        <main class="p-8 flex-1">
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('dinas.pages.store') }}" enctype="multipart/form-data" class="space-y-6 max-w-4xl">
                @csrf

                {{-- PRESET REKOMENDASI MENU STANDAR (OPSI B) --}}
                <div class="p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2 text-blue-900 font-bold text-xs uppercase tracking-wide">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Preset Rekomendasi Menu Standar (Diskominfo)</span>
                        </div>
                        <span class="text-[11px] text-blue-600 font-medium">Opsional</span>
                    </div>
                    <p class="text-xs text-blue-800/80 mb-3">
                        Pilih template menu standar pemerintahan di bawah ini. Sistem otomatis mengisi judul, mencocokkan menu induk, dan menyiapkan draf konten awal.
                    </p>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <select id="standardPresetSelect" class="flex-1 text-xs border border-blue-200 rounded-lg px-3 py-2.5 bg-white text-slate-700 font-medium focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">-- Pilih Template Menu Standar --</option>
                            <optgroup label="📌 Menu Utama Header (Level 1):">
                                <option value="menu_profil" data-title="Profil Kedinasan" data-type="root" data-content="Profil resmi dinas, kedudukan, sejarah singkat, dan ikhtisar kelembagaan dinas.">Profil Kedinasan (Menu Utama)</option>
                                <option value="menu_layanan" data-title="Layanan Publik" data-type="root" data-content="Katalog dan standar pelayanan publik yang diselenggarakan oleh dinas bagi masyarakat.">Layanan Publik (Menu Utama)</option>
                                <option value="menu_informasi" data-title="Informasi Publik" data-type="root" data-content="Pusat informasi resmi, keterbukaan informasi publik, dan transparansi dinas.">Informasi Publik (Menu Utama)</option>
                                <option value="menu_publikasi" data-title="Publikasi & Dokumen" data-type="root" data-content="Laporan akuntabilitas, dokumen perencanaan, produk hukum, dan data statistik dinas.">Publikasi & Dokumen (Menu Utama)</option>
                                <option value="menu_kontak" data-title="Kontak & Pengaduan" data-type="root" data-content="Saluran komunikasi resmi, alamat kantor, kontak pengaduan, dan media sosial dinas.">Kontak & Pengaduan (Menu Utama)</option>
                            </optgroup>
                            <optgroup label="👤 Sub-bab Profil:">
                                <option value="sub_visimisi" data-title="Visi & Misi" data-parent-keyword="profil" data-content="Visi dan Misi Pemerintah Kota Batu serta arah kebijakan kedinasan.">Visi & Misi</option>
                                <option value="sub_tupoksi" data-title="Tugas Pokok & Fungsi" data-parent-keyword="profil" data-content="Tugas pokok, fungsi, dan wewenang dinas berdasarkan peraturan daerah yang berlaku.">Tugas Pokok & Fungsi</option>
                                <option value="sub_struktur" data-title="Struktur Organisasi" data-parent-keyword="profil" data-content="Bagan struktur organisasi dan deskripsi tata kerja pejabat di lingkungan dinas.">Struktur Organisasi</option>
                                <option value="sub_pejabat" data-title="Profil Pejabat & Pimpinan" data-parent-keyword="profil" data-content="Biografi dan profil pejabat pimpinan tinggi, administrator, dan pengawas kedinasan.">Profil Pejabat & Pimpinan</option>
                                <option value="sub_sejarah" data-title="Sejarah Kedinasan" data-parent-keyword="profil" data-content="Sejarah berdirinya dan perkembangan instansi dinas dari masa ke masa.">Sejarah Kedinasan</option>
                            </optgroup>
                            <optgroup label="🛠️ Sub-bab Layanan:">
                                <option value="sub_daftarlayanan" data-title="Daftar Layanan Publik" data-parent-keyword="layanan" data-content="Daftar rincian layanan perizinan/non-perizinan atau jasa publik yang disediakan.">Daftar Layanan Publik</option>
                                <option value="sub_prosedur" data-title="Standar & Prosedur Pelayanan" data-parent-keyword="layanan" data-content="Standar Operasional Prosedur (SOP) dan alur permohonan layanan publik.">Standar & Prosedur Pelayanan</option>
                                <option value="sub_persyaratan" data-title="Persyaratan Pelayanan" data-parent-keyword="layanan" data-content="Daftar berkas kelengkapan administrasi dan syarat pengajuan permohonan layanan.">Persyaratan Pelayanan</option>
                                <option value="sub_formulir" data-title="Formulir Permohonan Layanan" data-parent-keyword="layanan" data-content="Tautan unduh dan petunjuk pengisian formulir permohonan layanan publik.">Formulir Permohonan Layanan</option>
                                <option value="sub_cekstatus" data-title="Cek Status Layanan" data-parent-keyword="layanan" data-content="Panduan dan tautan mengecek progres berkas permohonan layanan secara mandiri.">Cek Status Layanan</option>
                            </optgroup>
                            <optgroup label="ℹ️ Sub-bab Informasi:">
                                <option value="sub_pengumuman" data-title="Pengumuman Resmi" data-parent-keyword="informasi" data-content="Pengumuman penting, surat edaran, dan siaran pers resmi kedinasan.">Pengumuman Resmi</option>
                                <option value="sub_agenda" data-title="Agenda Kegiatan" data-parent-keyword="informasi" data-content="Jadwal agenda acara, sosialisasi, dan kegiatan penting kedinasan.">Agenda Kegiatan</option>
                                <option value="sub_faq" data-title="FAQ / Pertanyaan Umum" data-parent-keyword="informasi" data-content="Pertanyaan yang sering diajukan masyarakat beserta penjelasan jawabannya.">FAQ / Pertanyaan Umum</option>
                            </optgroup>
                            <optgroup label="📚 Sub-bab Publikasi:">
                                <option value="sub_laporan" data-title="Laporan Kinerja & Akuntabilitas (LAKIP)" data-parent-keyword="publikasi" data-content="Laporan Kinerja Instansi Pemerintah (LKjIP/LAKIP) dan dokumen akuntabilitas kinerja.">Laporan Kinerja & Akuntabilitas</option>
                                <option value="sub_renja" data-title="Rencana Kerja (Renja / Renstra)" data-parent-keyword="publikasi" data-content="Dokumen Rencana Strategis (Renstra) dan Rencana Kerja Tahunan (Renja).">Rencana Kerja (Renja / Renstra)</option>
                                <option value="sub_peraturan" data-title="Produk Hukum & Peraturan" data-parent-keyword="publikasi" data-content="Kumpulan Peraturan Walikota, Keputusan Kepala Dinas, dan regulasi sektoral.">Produk Hukum & Peraturan</option>
                                <option value="sub_data" data-title="Data & Statistik Sektoral" data-parent-keyword="publikasi" data-content="Kompilasi data statistik sektoral dan indikator kinerja dinas.">Data & Statistik Sektoral</option>
                            </optgroup>
                            <optgroup label="📞 Sub-bab Kontak:">
                                <option value="sub_alamat" data-title="Alamat & Jam Operasional" data-parent-keyword="kontak" data-content="Lokasi kantor, denah/peta, dan jam operasional pelayanan dinas.">Alamat & Jam Operasional</option>
                                <option value="sub_callcenter" data-title="Telepon & Layanan Pengaduan" data-parent-keyword="kontak" data-content="Nomor call center, kontak WhatsApp pengaduan, dan SP4N LAPOR.">Telepon & Layanan Pengaduan</option>
                                <option value="sub_emailmedsos" data-title="Email Resmi & Media Sosial" data-parent-keyword="kontak" data-content="Alamat email korespondensi resmi dan tautan media sosial dinas.">Email Resmi & Media Sosial</option>
                            </optgroup>
                        </select>
                        <button type="button" id="applyPresetBtn" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center justify-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Terapkan Preset</span>
                        </button>
                    </div>
                    <div id="presetFeedback" class="hidden mt-2 text-[11px] font-semibold"></div>
                </div>

                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Judul Halaman <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            placeholder="Contoh: Profil Kedinasan / Layanan Publik / Visi Misi"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Posisi Menu / Target Penempatan <span class="text-red-500">*</span>
                        </label>
                        <select name="placement_target" id="placementTargetSelect" required class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="header_menu" {{ old('placement_target', 'header_menu') === 'header_menu' ? 'selected' : '' }}>
                                📌 Menu Utama di Header (Level 1 — Tampil Sejajar dengan Beranda)
                            </option>
                            <option value="beranda" {{ old('placement_target') === 'beranda' ? 'selected' : '' }}>
                                🏠 Tab Khusus Beranda Saja (Hanya Tampil di Bagian Beranda Website)
                            </option>

                            @if(isset($eligibleParents) && $eligibleParents->isNotEmpty())
                                <optgroup label="Tautkan sebagai Sub-menu / Sub-sub-bab (Hierarki Dropdown):">
                                    @foreach($eligibleParents as $parentOption)
                                        @php $depth = $parentOption->getDepth(); @endphp
                                        <option value="parent_{{ $parentOption->id }}" {{ old('placement_target') === 'parent_' . $parentOption->id ? 'selected' : '' }}>
                                            @if($depth === 1)
                                                ↳ [Sub-menu Level 2] Di bawah: {{ $parentOption->title }}
                                            @elseif($depth === 2)
                                                ↳↳ [Sub-sub-bab Level 3 / Wadah] Di bawah: {{ $parentOption->parent?->title }} > {{ $parentOption->title }}
                                            @else
                                                ↳↳↳ [Isi Wadah Level 4] Masukkan ke: {{ $parentOption->parent?->parent?->title }} > {{ $parentOption->parent?->title }} > {{ $parentOption->title }}
                                            @endif
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Pilih apakah halaman ini menjadi Menu Utama baru di header, atau menjadi sub-menu / sub-sub-bab di bawah menu yang sudah ada.</p>
                    </div>

                    <div id="headerMenuNotice" class="hidden p-3.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs">
                        <span class="font-semibold">ℹ Informasi Menu Utama Header:</span> Menu navigasi utama di header berfungsi sebagai label navigasi / kategori menu (tanpa unggahan gambar dan tanpa tautan langsung URL). Opsi banner gambar dan tautan langsung (direct link) hanya tersedia untuk <strong>Sub-menu</strong>.
                    </div>

                    <div id="subMenuFieldsWrapper" class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Gambar Ilustrasi / Banner Halaman (Opsional)
                            </label>
                            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/jpg"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-lg cursor-pointer bg-white">
                            <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maks 2MB. Gambar otomatis tersimpan ke perpustakaan Media dinas.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Direct Link / Tautan Langsung (Google Drive, Portal Eksternal, Dokumen Cloud, Opsional)
                            </label>
                            <input type="url" name="direct_link" value="{{ old('direct_link') }}"
                                placeholder="Contoh: https://drive.google.com/... atau https://layanan.batukota.go.id"
                                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <p class="text-[11px] text-slate-400 mt-1">Jika diisi, klik pada menu navigasi / tombol aksi halaman ini akan langsung mengarahkan pengunjung ke tautan tersebut (tab baru).</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Status Publikasi <span class="text-red-500">*</span>
                        </label>
                        <select name="status" required class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published (Langsung Aktif di Menu Header &amp; Website Publik)</option>
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Simpan Sebagai Konsep)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Isi Konten Halaman <span class="text-red-500">*</span>
                        </label>
                        <textarea name="content" rows="12" required
                            placeholder="Jelaskan profil lengkap kedinasan, tugas pokok, fungsi, atau visi-misi Anda..."
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">{{ old('content') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('dinas.pages.index') }}"
                       class="px-5 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold text-sm transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Halaman</span>
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
        function updateSubMenuFieldsVisibility() {
            const placementSelect = document.getElementById('placementTargetSelect');
            const wrapper = document.getElementById('subMenuFieldsWrapper');
            const notice = document.getElementById('headerMenuNotice');
            const isHeader = placementSelect && placementSelect.value === 'header_menu';

            if (wrapper) {
                if (isHeader) {
                    wrapper.classList.add('hidden');
                } else {
                    wrapper.classList.remove('hidden');
                }
            }
            if (notice) {
                if (isHeader) {
                    notice.classList.remove('hidden');
                } else {
                    notice.classList.add('hidden');
                }
            }
        }

        const placementSelectEl = document.getElementById('placementTargetSelect');
        if (placementSelectEl) {
            placementSelectEl.addEventListener('change', updateSubMenuFieldsVisibility);
        }
        updateSubMenuFieldsVisibility();

        document.getElementById('applyPresetBtn')?.addEventListener('click', function() {
            const select = document.getElementById('standardPresetSelect');
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) {
                alert('Silakan pilih salah satu template menu standar terlebih dahulu.');
                return;
            }

            const title = opt.getAttribute('data-title');
            const type = opt.getAttribute('data-type');
            const parentKeyword = opt.getAttribute('data-parent-keyword');
            const defaultContent = opt.getAttribute('data-content');

            const titleInput = document.querySelector('input[name="title"]');
            const placementSelect = document.getElementById('placementTargetSelect');
            const contentTextarea = document.querySelector('textarea[name="content"]');
            const feedback = document.getElementById('presetFeedback');

            if (titleInput && title) {
                titleInput.value = title;
            }

            let placementFound = false;
            let matchedParentText = '';

            if (type === 'root') {
                placementSelect.value = 'header_menu';
                placementFound = true;
            } else if (parentKeyword) {
                const options = placementSelect.options;
                for (let i = 0; i < options.length; i++) {
                    const optVal = options[i].value;
                    const optText = options[i].text.toLowerCase();
                    if (optVal.startsWith('parent_') && optText.includes(parentKeyword.toLowerCase())) {
                        placementSelect.selectedIndex = i;
                        placementFound = true;
                        matchedParentText = options[i].text.replace(/^[↳\s\[\]\w\d\-]+:\s*/, '').trim();
                        break;
                    }
                }
            }

            updateSubMenuFieldsVisibility();

            if (contentTextarea && (!contentTextarea.value.trim() || contentTextarea.value.trim().length < 50)) {
                contentTextarea.value = defaultContent;
            }

            if (feedback) {
                feedback.classList.remove('hidden');
                if (type === 'root') {
                    feedback.className = 'mt-2 text-[11px] font-semibold text-emerald-700 bg-emerald-50 p-2.5 rounded-lg border border-emerald-200';
                    feedback.textContent = `✓ Template diterapkan! Halaman diatur sebagai Menu Utama Header "${title}".`;
                } else if (placementFound) {
                    feedback.className = 'mt-2 text-[11px] font-semibold text-emerald-700 bg-emerald-50 p-2.5 rounded-lg border border-emerald-200';
                    feedback.textContent = `✓ Template diterapkan! Posisi otomatis dihubungkan ke induk yang cocok: ${matchedParentText || 'Menu Induk'}.`;
                } else {
                    feedback.className = 'mt-2 text-[11px] font-semibold text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200';
                    feedback.textContent = `ℹ Menu induk untuk kategori ini belum dibuat. Disarankan buat Menu Utama "${parentKeyword ? parentKeyword.toUpperCase() : ''}" terlebih dahulu, atau pilih menu induk yang tersedia di bawah.`;
                }
            }
        });
    </script>
</body>
</html>
