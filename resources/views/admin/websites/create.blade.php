<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Website Dinas Baru — CMS</title>
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
                <h1 class="text-sm font-bold text-white leading-tight"> CMS</h1>
                <p class="text-[11px] text-slate-400 font-medium">Diskominfo Kota Batu</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span class="text-sm">Dashboard</span>
            </a>

            <a href="{{ route('admin.templates.builder', 1) }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span class="text-sm">Template</span>
            </a>

            <a href="{{ route('admin.websites.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl bg-white text-blue-600 font-semibold shadow-sm transition-all">
                <svg class="w-5 h-5 text-blue-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
                <span class="text-sm">Website Dinas</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span class="text-sm">User</span>
            </a>

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

    <!-- Main Content Area -->
    <div class="ml-64 flex-1 flex flex-col min-h-screen bg-slate-50">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4.5 flex items-center justify-between sticky top-0 z-20 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.websites.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Website Dinas Baru</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftarkan entitas instansi kedinasan beserta portal website resminya</p>
                </div>
            </div>
        </header>

        <!-- Main Content Form -->
        <main class="flex-1 px-8 py-6 max-w-4xl w-full">
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium shadow-xs">
                    <div class="flex items-center gap-2 mb-2 font-bold text-red-900">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span>Terdapat kesalahan pada formulir:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-[11px] text-red-700">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.websites.store') }}" class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-6">
                @csrf

                <!-- Bagian 1: Identitas Instansi Kedinasan -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-bold">1</span>
                        <span>Identitas Instansi Kedinasan (OPD)</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Lengkap Instansi / Dinas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="dinas_name" id="dinas_name" value="{{ old('dinas_name') }}" required
                                placeholder="Contoh: Dinas Pendidikan"
                                onkeyup="syncWebsiteDetails(this.value)"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kode Singkatan Instansi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="dinas_code" value="{{ old('dinas_code') }}" required
                                placeholder="Contoh: DISDIK"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition uppercase">
                            <p class="text-[11px] text-slate-400 mt-1">Digunakan untuk penanda internal dan kode instansi.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Email Resmi Kontak <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="email" name="dinas_email" value="{{ old('dinas_email') }}"
                                placeholder="disdik@batukota.go.id"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nomor Telepon Kantor <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="dinas_phone" value="{{ old('dinas_phone') }}"
                                placeholder="Contoh: (0341) 591034"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alamat Kantor Kedinasan <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="dinas_address" value="{{ old('dinas_address') }}"
                                placeholder="Contoh: Balaikota Among Tani, Gedung B Lantai 3, Kota Batu"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Konfigurasi Portal Website -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-bold">2</span>
                        <span>Konfigurasi Portal Website Dinas</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Portal Website <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="website_name" id="website_name" value="{{ old('website_name') }}" required
                                placeholder="Contoh: Website Resmi Dinas Pendidikan Kota Batu"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Domain / Subdomain Identifier <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center">
                                <span class="px-3 py-2.5 bg-slate-100 border border-r-0 border-slate-200 text-slate-500 text-xs rounded-l-lg font-mono">
                                    /site/
                                </span>
                                <input type="text" name="domain" id="domain" value="{{ old('domain') }}" required
                                    placeholder="pendidikan"
                                    class="w-full text-xs border border-slate-200 rounded-r-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-mono">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Hanya huruf kecil, angka, atau tanda hubung. Akses publik: <code>/site/[domain]</code></p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Template Global Blueprint <span class="text-red-500">*</span>
                            </label>
                            <select name="template_id" required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-medium">
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" {{ old('template_id') == $tmpl->id ? 'selected' : '' }}>
                                        {{ $tmpl->name }} (v{{ $tmpl->template_version }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Struktur tampilan mengacu pada visual template builder Super Admin.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status Operasional Website <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-medium">
                                <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif (Dapat Diakses Publik)</option>
                                <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif (Pemeliharaan)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200/80 text-blue-900 text-xs flex items-start gap-3">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="leading-relaxed">
                        <strong>Otomatisasi Sistem:</strong> Setelah website dibuat, sistem secara otomatis menginisialisasi slot Appearance default dan konfigurasi pengaturan. Anda dapat langsung membuat akun Admin Kedinasan untuk mengelola website ini melalui menu <strong>User</strong>.
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.websites.index') }}"
                        class="px-5 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold text-xs transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Simpan &amp; Terbitkan Website</span>
                    </button>
                </div>
            </form>

            <!-- Footer Halaman -->
            <footer class="pt-6 pb-2 border-t border-slate-200/70 flex items-center justify-between text-xs text-slate-500 mt-8">
                <p>CMS Diskominfo Kota Batu &bull; Developed by Azzaryansyaa</p>
                <p>© 2024 Diskominfo Kota Batu</p>
            </footer>
        </main>
    </div>

    <script>
        function syncWebsiteDetails(dinasName) {
            const webNameInput = document.getElementById('website_name');
            const domainInput = document.getElementById('domain');

            if (!webNameInput.value || webNameInput.value.startsWith('Website Resmi ')) {
                webNameInput.value = dinasName ? 'Website Resmi ' + dinasName + ' Kota Batu' : '';
            }

            if (!domainInput.value || domainInput.dataset.manual !== 'true') {
                const slug = dinasName.toLowerCase()
                    .replace(/dinas/g, '')
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                domainInput.value = slug;
            }
        }

        document.getElementById('domain').addEventListener('input', function() {
            this.dataset.manual = 'true';
        });
    </script>
</body>
</html>
