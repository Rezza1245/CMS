<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Media &amp; Dokumen Publik — {{ $dinas->name }}</title>
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

    <!-- Sidebar Navigasi (Mutlak 6 Menu) -->
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
            <!-- 1. Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
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

            <!-- 3. Media (Active) -->
            <a href="{{ route('dinas.media.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg bg-white text-[#2563EB] font-semibold shadow-sm transition-all">
                <svg class="w-5 h-5 text-[#2563EB] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <span class="text-sm">Media</span>
            </a>

            <!-- 4. Pages -->
            <a href="{{ route('dinas.pages.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span class="text-sm">Pages</span>
            </a>

            <!-- 5. Appearance -->
            <a href="{{ route('dinas.appearance.edit') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-[#94A3B8] hover:text-white hover:bg-slate-800/60 font-medium transition-colors">
                <svg class="w-5 h-5 text-[#94A3B8] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
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

    <!-- Main Content -->
    <div class="ml-64 flex-1 flex flex-col min-h-screen bg-[#F8FAFC]">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4.5 flex items-center justify-between sticky top-0 z-20 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <div>
                <h2 class="text-2xl font-bold text-[#0F172A] tracking-tight">Manajemen Media &amp; Dokumen Publik</h2>
                <p class="text-xs text-slate-500 mt-0.5">Unggah dokumen resmi PDF dan aset gambar untuk website publik dinas Anda</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('site.show', $website->domain) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">
                    <span>Lihat Website</span>
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </header>

        <main class="p-8 flex-1 space-y-6 max-w-6xl">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center gap-4 shadow-xs">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 font-medium">Total Berkas Dinas</span>
                        <div class="text-2xl font-bold text-slate-900 leading-tight">{{ $totalCount }} <span class="text-xs font-normal text-slate-400">aset</span></div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center gap-4 shadow-xs">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/><path d="M9 12h6"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 font-medium">Kapasitas Disk Terpakai</span>
                        <div class="text-2xl font-bold text-slate-900 leading-tight">{{ $totalSizeMb }} <span class="text-xs font-normal text-slate-400">MB</span></div>
                    </div>
                </div>
            </div>

            {{-- Form Upload Berkas Media --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <div class="border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Unggah Berkas Baru</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Sistem otomatis mengonversi gambar (PNG/JPG/JPEG) menjadi <strong>WebP</strong> berkualitas tinggi dan mengompresi dokumen (PDF/DOCX/XLSX) agar hemat kapasitas storage.</p>
                </div>

                <form method="POST" action="{{ route('dinas.media.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-xl p-6 text-center bg-slate-50/50 hover:bg-blue-50/20 transition cursor-pointer"
                         onclick="document.getElementById('media_file').click()">
                        <input type="file" name="file" id="media_file"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,image/png,image/jpeg,image/jpg,image/webp"
                               class="hidden" onchange="handleFileSelected(this)">

                        <div id="dropzone-prompt" class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <p class="text-xs font-semibold text-slate-700">
                                <span class="text-blue-600 hover:underline">Klik untuk memilih berkas</span> dari komputer Anda
                            </p>
                            <p class="text-[11px] text-slate-400 mt-1">Format didukung: <strong>PDF, Word (DOC/DOCX), Excel (XLS/XLSX), PPT/PPTX, Gambar (PNG/JPG/WEBP)</strong> (Maks 10 MB)</p>
                        </div>

                        <div id="file-selected-indicator" class="hidden mt-2 p-3 bg-white border border-blue-200 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 truncate">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span id="selected-file-name" class="truncate font-mono"></span>
                            </div>
                            <span id="selected-file-size" class="text-[11px] text-slate-400 shrink-0 font-mono"></span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Unggah Dokumen / Media</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Filter & Search Toolbar --}}
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-3 shadow-xs">
                <form method="GET" action="{{ route('dinas.media.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
                    <div class="relative flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama berkas..."
                            class="w-full text-xs pl-8 pr-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </div>

                    <select name="filter" class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Semua Tipe Berkas</option>
                        <option value="document" {{ in_array(request('filter'), ['document', 'pdf']) ? 'selected' : '' }}>Dokumen (PDF, Word, Excel)</option>
                        <option value="image" {{ request('filter') === 'image' ? 'selected' : '' }}>Gambar (WebP / Foto)</option>
                    </select>

                    <button type="submit" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                        Filter
                    </button>
                </form>

                <span class="text-xs text-slate-400">
                    Menampilkan <strong class="text-slate-700">{{ $mediaList->total() }}</strong> berkas
                </span>
            </div>

            {{-- Media Grid List --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">Nama Berkas</th>
                                <th class="px-4 py-3.5">Tipe &amp; Format</th>
                                <th class="px-4 py-3.5">Ukuran</th>
                                <th class="px-4 py-3.5">Waktu Unggah</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($mediaList as $item)
                                @php
                                    $ext = strtolower(pathinfo($item->file_name, PATHINFO_EXTENSION));
                                    $isPdf = str_contains($item->file_type, 'pdf') || $ext === 'pdf';
                                    $isDoc = in_array($ext, ['doc', 'docx']) || str_contains($item->file_type, 'word') || str_contains($item->file_type, 'officedocument.wordprocessingml');
                                    $isSheet = in_array($ext, ['xls', 'xlsx', 'csv']) || str_contains($item->file_type, 'sheet') || str_contains($item->file_type, 'excel') || str_contains($item->file_type, 'officedocument.spreadsheetml');
                                    $isPpt = in_array($ext, ['ppt', 'pptx']) || str_contains($item->file_type, 'presentation');
                                    $isImage = str_starts_with($item->file_type, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg']);

                                    $sizeFmt = $item->file_size < 1024 * 1024
                                        ? round($item->file_size / 1024, 1) . ' KB'
                                        : round($item->file_size / (1024 * 1024), 2) . ' MB';

                                    $badgeConfig = match(true) {
                                        $isPdf => ['label' => 'PDF', 'class' => 'bg-red-50 text-red-700 border-red-200', 'icon_bg' => 'bg-red-50 text-red-600'],
                                        $isDoc => ['label' => 'WORD', 'class' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon_bg' => 'bg-blue-50 text-blue-600'],
                                        $isSheet => ['label' => 'EXCEL', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon_bg' => 'bg-emerald-50 text-emerald-600'],
                                        $isPpt => ['label' => 'PPT', 'class' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon_bg' => 'bg-amber-50 text-amber-600'],
                                        $ext === 'webp' => ['label' => 'WEBP', 'class' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon_bg' => 'bg-purple-50 text-purple-600'],
                                        default => ['label' => 'GAMBAR', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'icon_bg' => 'bg-indigo-50 text-indigo-600'],
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $badgeConfig['icon_bg'] }}">
                                                @if($isPdf)
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2h10l4 4v14a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V7h3.5L15 3.5zM9 13v4h1.5v-1.5H12a1.5 1.5 0 001.5-1.5v-1a1.5 1.5 0 00-1.5-1.5H9zm1.5 1.5h1v1h-1v-1z"/></svg>
                                                @elseif($isDoc)
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 7V3.5L18.5 7H15zM9 13h6v2H9v-2zm0 4h6v2H9v-2zm0-8h2v2H9V9z"/></svg>
                                                @elseif($isSheet)
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm1 7V3.5L18.5 7H15zM8 17l2.5-4L8 9h2l1.5 2.5L13 9h2l-2.5 4 2.5 4h-2L11.5 14.5 10 17H8z"/></svg>
                                                @elseif($isImage)
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-800 text-xs truncate max-w-sm" title="{{ $item->file_name }}">
                                                    {{ $item->file_name }}
                                                </div>
                                                <div class="text-[10px] text-slate-400 font-mono truncate">
                                                    storage/{{ $item->file_path }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider border {{ $badgeConfig['class'] }}">
                                            {{ $badgeConfig['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-600 font-mono">
                                        {{ $sizeFmt }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-500 font-medium">
                                        {{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                                        <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                                            <span>Unduh</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('dinas.media.destroy', $item) }}" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas \'{{ $item->file_name }}\'? Berkas fisik di server juga akan dihapus.')">
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
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada berkas media atau dokumen</p>
                                        <p class="text-[11px] mt-0.5">Gunakan formulir di atas untuk mengunggah dokumen PDF atau foto resmi dinas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($mediaList->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $mediaList->links() }}
                    </div>
                @endif
            </div>

            <!-- Footer Platform -->
            <footer class="pt-6 pb-2 text-xs text-slate-400 flex items-center justify-between border-t border-slate-200">
                <p>CMS Diskominfo Kota Batu &bull; Developed by Azzaryansyaa</p>
                <p>&copy; {{ date('Y') }} Diskominfo Kota Batu</p>
            </footer>
        </main>
    </div>

    <script>
    function handleFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const indicator = document.getElementById('file-selected-indicator');
            const nameEl = document.getElementById('selected-file-name');
            const sizeEl = document.getElementById('selected-file-size');
            if (indicator && nameEl && sizeEl) {
                nameEl.textContent = file.name;
                const sizeFmt = file.size < 1024 * 1024
                    ? (file.size / 1024).toFixed(1) + ' KB'
                    : (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                sizeEl.textContent = sizeFmt;
                indicator.classList.remove('hidden');
            }
        }
    }
    </script>
</body>
</html>
