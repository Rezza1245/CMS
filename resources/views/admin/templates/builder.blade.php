<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Template Builder — {{ $template->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com?v=4"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .sortable-ghost { opacity: 0.35; background-color: #e0e7ff; border-style: dashed; }
        .sortable-drag { box-shadow: 0 14px 28px rgba(0,0,0,0.18); transform: rotate(1deg); }
        .component-chip { cursor: grab; }
        .component-chip:active { cursor: grabbing; }
        .canvas-comp-node.selected { ring: 2px; }
    </style>
</head>
<body class="h-full bg-slate-100 overflow-hidden flex flex-col">

{{-- ===== TOP BAR (Toolbar, Undo/Redo, Responsive Preview, Save Status) ===== --}}
<header class="h-14 bg-slate-900 flex items-center justify-between px-4 shrink-0 z-50 border-b border-slate-800">
    {{-- Kiri: Brand & Nama Template --}}
    <div class="flex items-center gap-3 min-w-0">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-white transition p-1.5 rounded-lg hover:bg-slate-800" title="Kembali ke Dashboard">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="truncate">
            <span class="text-xs text-slate-400 font-medium">Template Builder</span>
            <h1 class="text-white font-semibold text-sm truncate leading-tight">
                {{ $template->name }}
            </h1>
        </div>
    </div>

    {{-- Tengah: Undo/Redo & Responsive Preview Switcher --}}
    <div class="flex items-center gap-2">
        {{-- Undo / Redo --}}
        <div class="flex items-center bg-slate-800 rounded-lg p-0.5 border border-slate-700">
            <button id="btn-undo" type="button" class="p-1.5 text-slate-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed rounded hover:bg-slate-700 transition" title="Undo (Ctrl+Z)" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4M3 10l4-4m-4 4l4 4"/>
                </svg>
            </button>
            <button id="btn-redo" type="button" class="p-1.5 text-slate-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed rounded hover:bg-slate-700 transition" title="Redo (Ctrl+Y)" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v2m0 0l4-4m-4 4l-4-4m15-3l-4-4m4 4l-4 4"/>
                </svg>
            </button>
        </div>

        <div class="h-5 w-px bg-slate-800 mx-1"></div>

        {{-- Responsive Switcher: Desktop, Tablet, Mobile (Bab 10) --}}
        <div class="flex items-center bg-slate-800 rounded-lg p-0.5 border border-slate-700">
            <button id="btn-preview-desktop" type="button" data-view="desktop"
                class="preview-btn active flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-medium bg-blue-600 text-white transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8m-4-4v4"/></svg>
                <span class="hidden sm:inline">Desktop</span>
            </button>
            <button id="btn-preview-tablet" type="button" data-view="tablet"
                class="preview-btn flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-medium text-slate-400 hover:text-white transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
                <span class="hidden sm:inline">Tablet</span>
            </button>
            <button id="btn-preview-mobile" type="button" data-view="mobile"
                class="preview-btn flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-medium text-slate-400 hover:text-white transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="2" width="12" height="20" rx="2"/><line x1="10" y1="18" x2="14" y2="18"/></svg>
                <span class="hidden sm:inline">Mobile</span>
            </button>
        </div>
    </div>

    {{-- Kanan: Indikator Status Penyimpanan, Shortcut Live Preview, & Tombol Simpan --}}
    <div class="flex items-center gap-3">
        <div id="save-indicator" class="flex items-center gap-1.5 text-xs font-medium">
            <span id="save-dot" class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span id="save-status-text" class="text-slate-300">✓ Tersimpan</span>
        </div>

        <a href="{{ route('admin.templates.preview', $template) }}" target="_blank"
            class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 hover:border-slate-600 text-xs font-medium px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 shadow-xs"
            title="Pratinjau Hasil Template di Tab Baru">
            <span>Preview Template</span>
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>

        <button id="btn-save"
            class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>Simpan Template</span>
        </button>
    </div>
</header>

{{-- ===== MAIN 3-PANEL LAYOUT ===== --}}
<div class="flex flex-1 overflow-hidden">

    {{-- === PANEL KIRI: Daftar Komponen & Tambah Seksi === --}}
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col shrink-0">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Komponen</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Drag ke Container di kanvas</p>
            </div>
            <button id="btn-add-section-sidebar" type="button" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline flex items-center gap-1" title="Tambah Seksi Baru">
                + Seksi
            </button>
        </div>

        <div id="component-palette" class="flex-1 overflow-y-auto p-3 space-y-2">
            @foreach($allowedComponents as $comp)
            <div class="component-chip bg-slate-50 hover:bg-blue-50/80 border border-slate-200 hover:border-blue-300 rounded-lg px-3 py-2.5 transition group select-none"
                 data-component="{{ $comp }}">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-md flex items-center justify-center text-sm shrink-0 shadow-xs
                        @if($comp === 'Hero') bg-purple-100 text-purple-600
                        @elseif($comp === 'Posts Grid') bg-green-100 text-green-600
                        @elseif($comp === 'Container') bg-slate-200 text-slate-600
                        @elseif($comp === 'Static Content') bg-yellow-100 text-yellow-600
                        @elseif(str_contains($comp, 'Media')) bg-pink-100 text-pink-600
                        @elseif($comp === 'Footer') bg-gray-200 text-gray-600
                        @else bg-blue-100 text-blue-600
                        @endif">
                        @if($comp === 'Hero')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg>
                        @elseif(str_contains($comp, 'Posts'))
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        @elseif($comp === 'Container')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                        @elseif($comp === 'Static Content')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h10"/></svg>
                        @elseif(str_contains($comp, 'Media'))
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        @elseif($comp === 'Footer')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 15h18"/></svg>
                        @endif
                    </span>
                    <div>
                        <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-600 transition block">{{ $comp }}</span>
                        <span class="text-[10px] text-slate-400">Pre-defined widget</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Developer Credit --}}
        <div class="px-4 py-2.5 border-t border-slate-200 bg-slate-50 text-[10px] text-slate-400 text-center font-medium select-none">
            Developed by Azzaryansyaa
        </div>
    </aside>

    {{-- === PANEL TENGAH: Kanvas Hierarki Bersarang (Section -> Container -> Component) === --}}
    <main class="flex-1 flex flex-col min-w-0 bg-slate-100 overflow-hidden">
        {{-- Toolbar Sub-header --}}
        <div class="px-6 py-2.5 bg-white border-b border-slate-200 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Pohon Kanvas Visual</span>
                <span class="text-[11px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded font-mono">Section → Container → Component</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500" id="canvas-count">0 seksi</span>
                <button id="btn-add-section-canvas" type="button" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Seksi
                </button>
            </div>
        </div>

        {{-- Canvas Workspace Area (dengan container responsive preview) --}}
        <div class="flex-1 overflow-y-auto p-6 flex justify-center">
            <div id="canvas-viewport" class="w-full max-w-5xl transition-all duration-300">
                {{-- Blueprint Fixed Structure: Header / Navbar (AGENTS.md Bab 9 & PRD.md 3.1) --}}
                <div id="blueprint-header-node" class="mb-4 bg-white border border-slate-200 hover:border-blue-400 rounded-xl shadow-xs overflow-hidden cursor-pointer transition select-none">
                    <div class="px-4 py-2 bg-slate-900 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                            <span class="text-xs font-bold uppercase tracking-wider">Header / Navbar</span>
                            <span class="text-[10px] font-mono text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded">Fixed System Blueprint</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="header-logo-badge" class="text-[10px] font-semibold px-2 py-0.5 rounded border bg-emerald-950/60 text-emerald-300 border-emerald-500/40">
                                Logo: Admin Editable
                            </span>
                            <span class="text-[10px] text-slate-400 hidden sm:inline">Klik untuk kelola slot Logo</span>
                        </div>
                    </div>
                    <div class="px-4 py-2.5 bg-slate-50 flex items-center justify-between text-xs text-slate-600">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded bg-white border border-slate-200 flex items-center justify-center text-slate-500 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 text-xs">Slot Logo Instansi</span>
                                <span id="header-logo-subtext" class="text-[11px] text-slate-400">Admin Kedinasan diizinkan mengunggah file logo dari komputer</span>
                            </div>
                        </div>
                        <span class="text-blue-600 font-semibold text-[11px] flex items-center gap-1">Kelola Slot &rarr;</span>
                    </div>
                </div>

                <div id="section-tree" class="space-y-4 min-h-[450px]">
                    {{-- Diisi secara dinamis via JavaScript --}}
                </div>

                {{-- Empty state bila belum ada seksi --}}
                <div id="canvas-empty" class="hidden flex flex-col items-center justify-center p-12 bg-white border-2 border-dashed border-slate-300 rounded-2xl text-slate-400 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Kanvas template masih kosong</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm">Klik tombol "Tambah Seksi" untuk memulai menyusun hierarki tampilan blueprint website dinas.</p>
                </div>
            </div>
        </div>
    </main>

    {{-- === PANEL KANAN: Inspector Konfigurasi (Admin Editable vs Template Controlled) === --}}
    <aside class="w-80 bg-white border-l border-slate-200 flex flex-col shrink-0">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Inspector</h2>
            <span class="text-[10px] font-mono text-slate-400" id="inspector-badge">Pilih elemen</span>
        </div>

        <div id="config-panel" class="flex-1 overflow-y-auto p-4">
            {{-- Empty state --}}
            <div id="config-empty" class="flex flex-col items-center justify-center h-72 text-slate-400 text-center px-4">
                <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93s.844.172 1.2-.073l.744-.557c.438-.328 1.04-.29 1.432.098l.773.773c.389.389.426.994.098 1.432l-.557.744c-.245.356-.279.804-.073 1.2s.506.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.93.78s-.172.844.073 1.2l.557.744c.328.438.29 1.04-.098 1.432l-.773.773c-.389.389-.994.426-1.432.098l-.744-.557c-.356-.245-.804-.279-1.2-.073s-.71.506-.78.93l-.15.894c-.09.542-.56.94-1.109.94h-1.094c-.55 0-1.02-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93s-.844-.172-1.2.073l-.744.557c-.438.328-1.04.29-1.432-.098l-.773-.773c-.389-.389-.426-.994-.098-1.432l.557-.744c.245-.356.279-.804.073-1.2s-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.764-.383.93-.78s.172-.844-.073-1.2l-.557-.744c-.328-.438-.29-1.04.098-1.432l.773-.773c.389-.389.994-.426 1.432-.098l.744.557c.356.245.804.279 1.2.073s.71-.506.78-.93l.15-.894zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="text-xs font-semibold text-slate-700">Pilih komponen di kanvas</p>
                <p class="text-[11px] text-slate-400 mt-0.5">untuk mengelola Layout Settings &amp; Editable Slots</p>
            </div>
        </div>
    </aside>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ====== CONSTANTS & CONFIG ======
    const ALLOWED_SLOT_TYPES = @json($allowedSlotTypes);
    const ALLOWED_EDITORS = @json($allowedEditors);
    const SAVE_URL = @json(route('admin.templates.builder.update', $template));
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // Default slots per component (TEMPLATE.md Bab 5 & 6)
    const COMPONENT_DEFAULTS = {
        'Hero': {
            layout_settings: { height: '480px', alignment: 'center' },
            slots: {
                title:            { slot_id: 'hero_title',       binding: 'appearance.header_slogan',   type: 'text',     data_source: 'appearance', editable_by: 'admin_dinas', required: false },
                description:      { slot_id: 'hero_description', binding: 'appearance.hero_description', type: 'textarea', data_source: 'appearance', editable_by: 'admin_dinas', required: false },
                background_image: { slot_id: 'hero_bg',          binding: 'appearance.hero_banner',      type: 'image',    data_source: 'appearance', editable_by: 'admin_dinas', required: false },
            }
        },
        'Posts Grid': {
            layout_settings: { columns: 3, limit: 6 },
            slots: {
                items: { slot_id: 'posts_source', binding: 'posts.published' }
            }
        },
        'Container': {
            layout_settings: { columns: 2 },
            slots: {}
        },
        'Static Content': {
            layout_settings: {},
            slots: {
                title:   { slot_id: 'static_title',   binding: 'pages.title',   type: 'text',     data_source: 'pages', editable_by: 'admin_dinas', default_value: '', required: false },
                content: { slot_id: 'static_content', binding: 'pages.content', type: 'textarea', data_source: 'pages', editable_by: 'admin_dinas', default_value: '', required: false }
            }
        },
        'Media / Document List': {
            layout_settings: { limit: 10 },
            slots: {
                items: { slot_id: 'media_source', binding: 'media.published', editable_by: 'admin_dinas' }
            }
        },
        'Footer': {
            layout_settings: { columns: 3 },
            slots: {
                title: { slot_id: 'footer_title', binding: 'appearance.footer_title', type: 'text', data_source: 'appearance', editable_by: 'admin_dinas', default_value: 'Pemerintah Kota Batu', required: false },
                slogan: { slot_id: 'footer_slogan', binding: 'appearance.footer_slogan', type: 'text', data_source: 'appearance', editable_by: 'admin_dinas', default_value: 'Portal informasi dan layanan kedinasan resmi Pemerintah Kota Batu.', required: false },
                about_title: { slot_id: 'footer_about_title', binding: 'appearance.footer_about_title', type: 'text', data_source: 'appearance', editable_by: 'admin_dinas', default_value: 'Pemerintah Kota Batu', required: false },
                about_text: { slot_id: 'footer_about_text', binding: 'appearance.footer_about_text', type: 'textarea', data_source: 'appearance', editable_by: 'admin_dinas', default_value: 'Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu.', required: false },
                copyright: { slot_id: 'footer_copyright', binding: 'template_config.copyright', type: 'text', data_source: 'template_config', editable_by: 'super_admin', default_value: 'Pemerintah Kota Batu', required: false }
            }
        },
    };

    const COMP_COLORS = {
        'Hero': { bg: 'bg-purple-50', border: 'border-purple-200', accent: 'text-purple-600', badge: 'bg-purple-100' },
        'Posts Grid': { bg: 'bg-green-50', border: 'border-green-200', accent: 'text-green-600', badge: 'bg-green-100' },
        'Container': { bg: 'bg-slate-50', border: 'border-slate-200', accent: 'text-slate-600', badge: 'bg-slate-100' },
        'Static Content': { bg: 'bg-yellow-50', border: 'border-yellow-200', accent: 'text-yellow-600', badge: 'bg-yellow-100' },
        'Media / Document List': { bg: 'bg-pink-50', border: 'border-pink-200', accent: 'text-pink-600', badge: 'bg-pink-100' },
        'Footer': { bg: 'bg-gray-50', border: 'border-gray-200', accent: 'text-gray-600', badge: 'bg-gray-100' },
    };

    const BINDING_OPTIONS = {
        'Hero': {
            'title': [
                { value: 'appearance.header_slogan', label: 'appearance.header_slogan (Slogan / Judul Utama Hero Dinas)' },
                { value: 'dinas.name', label: 'dinas.name (Nama Resmi Dinas)' },
            ],
            'description': [
                { value: 'appearance.hero_description', label: 'appearance.hero_description (Deskripsi / Sambutan Hero Dinas)' },
            ],
            'background_image': [
                { value: 'appearance.hero_banner', label: 'appearance.hero_banner (Gambar Latar Banner Hero Dinas)' },
            ],
        },
        'Posts Grid': {
            'items': [
                { value: 'posts.published', label: 'posts.published (Koleksi Artikel Berita & Pengumuman Published)' },
            ],
        },
        'Media / Document List': {
            'items': [
                { value: 'media.published', label: 'media.published (Koleksi Dokumen PDF & Berkas Media Published)' },
            ],
        },
        'Static Content': {
            'title': [
                { value: 'pages.title', label: 'pages.title (Judul Halaman Beranda / Statis)' },
            ],
            'content': [
                { value: 'pages.content', label: 'pages.content (Isi Teks Halaman Beranda / Statis)' },
            ],
        },
        'Footer': {
            'title': [
                { value: 'appearance.footer_title', label: 'appearance.footer_title (Nama / Judul Dinas di Footer)' },
                { value: 'dinas.name', label: 'dinas.name (Nama Resmi Dinas)' },
            ],
            'slogan': [
                { value: 'appearance.footer_slogan', label: 'appearance.footer_slogan (Slogan Penutup Footer Dinas)' },
            ],
            'about_title': [
                { value: 'appearance.footer_about_title', label: 'appearance.footer_about_title (Judul Informasi Tambahan Kolom 3)' },
                { value: 'template_config.about_title', label: 'template_config.about_title (Fallback Template)' },
            ],
            'about_text': [
                { value: 'appearance.footer_about_text', label: 'appearance.footer_about_text (Teks Informasi Tambahan Kolom 3)' },
                { value: 'template_config.about_text', label: 'template_config.about_text (Fallback Template)' },
            ],
            'copyright': [
                { value: 'template_config.copyright', label: 'template_config.copyright (Teks Hak Cipta Template)' },
                { value: 'dinas.name', label: 'dinas.name (Nama Resmi Dinas)' },
            ],
        },
    };

    let idCounter = Date.now();
    function uid(prefix) {
        return `${prefix}_${(++idCounter).toString(36)}`;
    }

    // ====== STATE NORMALIZATION (Section -> Container -> Component) ======
    let rawCanvas = @json($canvasData);

    function normalizeSections(input) {
        let nodes = [];
        if (input && typeof input === 'object') {
            if (Array.isArray(input.canvas)) {
                nodes = input.canvas;
            } else if (Array.isArray(input)) {
                nodes = input;
            }
        }

        // Check if input is already hierarchical (sections with type === 'section')
        const isNested = nodes.every(n => n && n.type === 'section' && Array.isArray(n.children));
        if (isNested && nodes.length > 0) {
            return JSON.parse(JSON.stringify(nodes));
        }

        // If flat list of components (legacy), migrate to Section -> Container -> Component
        if (nodes.length > 0) {
            const defaultSection = {
                id: uid('section'),
                type: 'section',
                children: [
                    {
                        id: uid('container'),
                        type: 'container',
                        children: nodes.map(c => JSON.parse(JSON.stringify(c)))
                    }
                ]
            };
            return [defaultSection];
        }

        // Default initial structure
        return [
            {
                id: uid('section'),
                type: 'section',
                children: [
                    {
                        id: uid('container'),
                        type: 'container',
                        children: []
                    }
                ]
            }
        ];
    }

    let sections = normalizeSections(rawCanvas);
    let headerConfig = (rawCanvas && rawCanvas.header && typeof rawCanvas.header === 'object')
        ? JSON.parse(JSON.stringify(rawCanvas.header))
        : {
            id: 'header_navbar',
            component: 'Header / Navbar',
            layout_settings: { position: 'sticky-top', structure: 'default' },
            slots: {
                logo: {
                    slot_id: 'header_logo',
                    binding: 'appearance.logo',
                    type: 'image',
                    data_source: 'appearance',
                    editable_by: 'admin_dinas',
                    default_value: '',
                    required: false
                }
            }
        };
    if (!headerConfig.slots) headerConfig.slots = {};
    if (!headerConfig.slots.logo) {
        headerConfig.slots.logo = {
            slot_id: 'header_logo',
            binding: 'appearance.logo',
            type: 'image',
            data_source: 'appearance',
            editable_by: 'admin_dinas',
            default_value: '',
            required: false
        };
    }

    let selectedComponentId = null;

    // ====== UNDO / REDO & SAVE TRACKING (Bab 8 & 9) ======
    function getSnapshot() {
        return JSON.stringify({ sections, headerConfig });
    }

    let history = [];
    let historyIndex = -1;
    let lastSavedSnapshot = getSnapshot();

    const btnUndo = document.getElementById('btn-undo');
    const btnRedo = document.getElementById('btn-redo');
    const saveDot = document.getElementById('save-dot');
    const saveStatusText = document.getElementById('save-status-text');
    const btnSave = document.getElementById('btn-save');

    function updateSaveStatus() {
        const currentSnapshot = getSnapshot();
        const isClean = (currentSnapshot === lastSavedSnapshot);

        if (isClean) {
            saveDot.className = 'w-2 h-2 rounded-full bg-emerald-400';
            saveStatusText.className = 'text-slate-300';
            saveStatusText.textContent = '✓ Tersimpan';
        } else {
            saveDot.className = 'w-2 h-2 rounded-full bg-amber-400 animate-pulse';
            saveStatusText.className = 'text-amber-400 font-semibold';
            saveStatusText.textContent = 'Perubahan belum disimpan';
        }

        btnUndo.disabled = (historyIndex <= 0);
        btnRedo.disabled = (historyIndex >= history.length - 1);
    }

    function pushHistory(record = true) {
        if (!record) return;
        const snapshot = getSnapshot();
        if (historyIndex >= 0 && history[historyIndex] === snapshot) {
            updateSaveStatus();
            return;
        }

        history = history.slice(0, historyIndex + 1);
        history.push(snapshot);
        if (history.length > 50) history.shift();
        historyIndex = history.length - 1;

        updateSaveStatus();
    }

    function undo() {
        if (historyIndex > 0) {
            historyIndex--;
            const st = JSON.parse(history[historyIndex]);
            sections = st.sections;
            headerConfig = st.headerConfig;
            renderCanvas();
            renderInspector();
            updateSaveStatus();
        }
    }

    function redo() {
        if (historyIndex < history.length - 1) {
            historyIndex++;
            const st = JSON.parse(history[historyIndex]);
            sections = st.sections;
            headerConfig = st.headerConfig;
            renderCanvas();
            renderInspector();
            updateSaveStatus();
        }
    }

    btnUndo.addEventListener('click', undo);
    btnRedo.addEventListener('click', redo);

    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z' && !e.shiftKey) {
            e.preventDefault();
            undo();
        } else if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'y' || (e.shiftKey && e.key.toLowerCase() === 'z'))) {
            e.preventDefault();
            redo();
        }
    });

    // ====== RESPONSIVE PREVIEW SWITCHER (Bab 10) ======
    const viewport = document.getElementById('canvas-viewport');
    const previewBtns = document.querySelectorAll('.preview-btn');

    previewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            previewBtns.forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white');
                b.classList.add('text-slate-400');
            });
            this.classList.remove('text-slate-400');
            this.classList.add('bg-blue-600', 'text-white');

            const view = this.dataset.view;
            if (view === 'mobile') {
                viewport.className = 'w-full max-w-[375px] transition-all duration-300';
            } else if (view === 'tablet') {
                viewport.className = 'w-full max-w-[768px] transition-all duration-300';
            } else {
                viewport.className = 'w-full max-w-5xl transition-all duration-300';
            }
        });
    });

    // ====== COMPONENT FACTORY ======
    function createComponentNode(componentName) {
        const defaults = COMPONENT_DEFAULTS[componentName] || { layout_settings: {}, slots: {} };
        const node = {
            id: uid(componentName.toLowerCase().replace(/[\s\/]/g, '_')),
            component: componentName,
            layout_settings: JSON.parse(JSON.stringify(defaults.layout_settings)),
            slots: JSON.parse(JSON.stringify(defaults.slots)),
        };
        if (componentName === 'Container') {
            node.children = [];
        }
        return node;
    }

    function removeComponentFromAll(compId) {
        for (const s of sections) {
            for (const c of s.children) {
                const idx = c.children.findIndex(x => x.id === compId);
                if (idx !== -1) {
                    return c.children.splice(idx, 1)[0];
                }
                for (const comp of c.children) {
                    if (comp.children && Array.isArray(comp.children)) {
                        const nestedIdx = comp.children.findIndex(x => x.id === compId);
                        if (nestedIdx !== -1) {
                            return comp.children.splice(nestedIdx, 1)[0];
                        }
                    }
                }
            }
        }
        return null;
    }

    function findComponentById(compId) {
        for (const s of sections) {
            for (const c of s.children) {
                for (const comp of c.children) {
                    if (comp.id === compId) return { component: comp, container: c, section: s };
                    if (comp.children && Array.isArray(comp.children)) {
                        const nested = comp.children.find(child => child.id === compId);
                        if (nested) return { component: nested, container: comp, section: s, parentContainerComp: comp };
                    }
                }
            }
        }
        return null;
    }

    // ====== CANVAS RENDERING ======
    const sectionTreeEl = document.getElementById('section-tree');
    const canvasEmptyEl = document.getElementById('canvas-empty');
    const canvasCountEl = document.getElementById('canvas-count');

    function renderCanvas() {
        // Update Fixed Blueprint Header / Navbar Node
        const headerNodeEl = document.getElementById('blueprint-header-node');
        const headerLogoBadge = document.getElementById('header-logo-badge');
        const headerLogoSubtext = document.getElementById('header-logo-subtext');

        if (headerNodeEl) {
            const isHeaderSelected = (selectedComponentId === 'header_navbar');
            headerNodeEl.className = `mb-4 bg-white border ${isHeaderSelected ? 'border-blue-500 ring-2 ring-blue-500 shadow-sm' : 'border-slate-200 hover:border-blue-300 shadow-xs'} rounded-xl overflow-hidden cursor-pointer transition select-none`;

            const isLogoEditable = (headerConfig.slots?.logo?.editable_by !== 'super_admin');
            if (isLogoEditable) {
                headerLogoBadge.className = 'text-[10px] font-semibold px-2 py-0.5 rounded border bg-emerald-950/60 text-emerald-300 border-emerald-500/40';
                headerLogoBadge.textContent = 'Logo: Admin Editable';
                headerLogoSubtext.textContent = 'Admin Kedinasan diizinkan mengunggah file logo dari komputer';
            } else {
                headerLogoBadge.className = 'text-[10px] font-semibold px-2 py-0.5 rounded border bg-slate-800 text-slate-300 border-slate-700';
                headerLogoBadge.textContent = 'Logo: Template Controlled';
                headerLogoSubtext.textContent = 'Logo instansi dikunci oleh template Super Admin';
            }
        }

        sectionTreeEl.innerHTML = '';
        canvasEmptyEl.classList.toggle('hidden', sections.length > 0);
        canvasCountEl.textContent = `${sections.length} seksi`;

        sections.forEach((section, sIdx) => {
            const secCard = document.createElement('div');
            secCard.className = 'section-node bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden transition';
            secCard.dataset.sectionId = section.id;

            // Section Header
            secCard.innerHTML = `
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="cursor-grab text-slate-400 hover:text-slate-600 section-drag-handle p-1 rounded hover:bg-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></svg>
                        </span>
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Seksi</span>
                        <span class="text-[11px] font-mono text-slate-400 bg-white px-2 py-0.5 rounded border border-slate-200">${section.id}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-add-container text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-2 py-1 rounded transition" data-section-id="${section.id}">
                            + Container
                        </button>
                        <button type="button" class="btn-remove-section text-slate-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition" data-section-id="${section.id}" title="Hapus Seksi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="section-containers-list p-3 space-y-3"></div>
            `;

            const containersListEl = secCard.querySelector('.section-containers-list');

            // Render Containers within Section
            section.children.forEach((container, cIdx) => {
                const conBox = document.createElement('div');
                conBox.className = 'container-node bg-slate-50/60 border-2 border-dashed border-slate-200 rounded-lg p-3 transition';
                conBox.dataset.containerId = container.id;

                conBox.innerHTML = `
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200/60">
                        <div class="flex items-center gap-2">
                            <span class="cursor-grab text-slate-400 hover:text-slate-600 container-drag-handle p-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></svg>
                            </span>
                            <span class="text-[11px] font-bold text-slate-600 uppercase">Container</span>
                            <span class="text-[10px] font-mono text-slate-400">${container.id}</span>
                        </div>
                        <button type="button" class="btn-remove-container text-slate-400 hover:text-red-500 p-0.5 rounded transition" data-container-id="${container.id}" title="Hapus Container">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="comp-dropzone min-h-[56px] space-y-2 rounded-md p-1"></div>
                `;

                const dropzoneEl = conBox.querySelector('.comp-dropzone');

                // Render Components inside Container
                if (container.children.length === 0) {
                    dropzoneEl.innerHTML = `
                        <div class="comp-empty-hint border border-dashed border-slate-200 rounded-md py-4 text-center text-slate-400 text-xs">
                            Drag komponen ke container ini
                        </div>
                    `;
                } else {
                    container.children.forEach(comp => {
                        const colors = COMP_COLORS[comp.component] || COMP_COLORS['Container'];
                        const compEl = document.createElement('div');
                        const isSelected = (selectedComponentId === comp.id);

                        if (comp.component === 'Container') {
                            if (!Array.isArray(comp.children)) {
                                comp.children = [];
                            }
                            const cols = comp.layout_settings?.columns || 2;
                            compEl.className = `canvas-comp-node bg-slate-100/90 border-2 ${isSelected ? 'border-blue-500 ring-2 ring-blue-500' : 'border-slate-300'} rounded-xl p-3 transition-all`;
                            compEl.dataset.compId = comp.id;

                            compEl.innerHTML = `
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200">
                                    <div class="flex items-center gap-2">
                                        <span class="cursor-grab text-slate-400 hover:text-slate-600 comp-drag-handle p-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></svg>
                                        </span>
                                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded">Container (${cols} Kolom)</span>
                                        <span class="text-[11px] text-slate-400 font-mono">${comp.id}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-slate-500">${comp.children.length} elemen</span>
                                        <button type="button" class="btn-remove-comp text-slate-400 hover:text-red-500 p-1 transition" data-comp-id="${comp.id}" title="Hapus Container">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="nested-comp-dropzone min-h-[50px] p-2 rounded-lg bg-white/90 border border-dashed border-slate-300 space-y-2"></div>
                            `;

                            // Select Container itself
                            compEl.addEventListener('click', function(e) {
                                if (e.target.closest('.btn-remove-comp') || e.target.closest('.nested-comp-node') || e.target.closest('.btn-remove-nested-comp')) return;
                                selectedComponentId = comp.id;
                                renderCanvas();
                                renderInspector();
                            });

                            // Remove Container
                            compEl.querySelector('.btn-remove-comp').addEventListener('click', function(e) {
                                e.stopPropagation();
                                container.children = container.children.filter(item => item.id !== comp.id);
                                if (selectedComponentId === comp.id) selectedComponentId = null;
                                pushHistory();
                                renderCanvas();
                                renderInspector();
                            });

                            const nestedDropzoneEl = compEl.querySelector('.nested-comp-dropzone');
                            if (comp.children.length === 0) {
                                nestedDropzoneEl.innerHTML = `
                                    <div class="comp-empty-hint border border-dashed border-slate-200 rounded-md py-3 text-center text-slate-400 text-xs">
                                        Drag komponen ke dalam grid Container ini (${cols} Kolom)
                                    </div>
                                `;
                            } else {
                                comp.children.forEach(childComp => {
                                    const childColors = COMP_COLORS[childComp.component] || COMP_COLORS['Container'];
                                    const childEl = document.createElement('div');
                                    const isChildSelected = (selectedComponentId === childComp.id);
                                    childEl.className = `nested-comp-node canvas-comp-node ${childColors.bg} border ${childColors.border} rounded-lg p-2.5 cursor-pointer transition-all hover:shadow-xs ${isChildSelected ? 'ring-2 ring-blue-500 shadow-sm' : ''}`;
                                    childEl.dataset.compId = childComp.id;
                                    childEl.innerHTML = `
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="cursor-grab text-slate-400 hover:text-slate-600 comp-drag-handle">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></svg>
                                                </span>
                                                <span class="${childColors.badge} ${childColors.accent} text-[11px] font-bold px-1.5 py-0.5 rounded">${childComp.component}</span>
                                                <span class="text-[10px] text-slate-400 font-mono">${childComp.id}</span>
                                            </div>
                                            <button type="button" class="btn-remove-nested-comp text-slate-400 hover:text-red-500 p-0.5 transition" data-child-id="${childComp.id}" title="Hapus komponen">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    `;

                                    childEl.addEventListener('click', function(e) {
                                        if (e.target.closest('.btn-remove-nested-comp')) return;
                                        e.stopPropagation();
                                        selectedComponentId = childComp.id;
                                        renderCanvas();
                                        renderInspector();
                                    });

                                    childEl.querySelector('.btn-remove-nested-comp').addEventListener('click', function(e) {
                                        e.stopPropagation();
                                        comp.children = comp.children.filter(item => item.id !== childComp.id);
                                        if (selectedComponentId === childComp.id) selectedComponentId = null;
                                        pushHistory();
                                        renderCanvas();
                                        renderInspector();
                                    });

                                    nestedDropzoneEl.appendChild(childEl);
                                });
                            }

                            // Sortable for nested dropzone
                            new Sortable(nestedDropzoneEl, {
                                group: { name: 'components-group', put: true },
                                animation: 180,
                                handle: '.comp-drag-handle',
                                draggable: '.canvas-comp-node',
                                ghostClass: 'sortable-ghost',
                                dragClass: 'sortable-drag',
                                onAdd: function(evt) {
                                    const chipEl = evt.item;
                                    const compName = chipEl.dataset.component;

                                    if (compName) {
                                        if (compName === 'Container') {
                                            chipEl.remove();
                                            renderCanvas();
                                            return;
                                        }
                                        const newCompNode = createComponentNode(compName);
                                        comp.children.splice(evt.newIndex, 0, newCompNode);
                                        chipEl.remove();
                                        selectedComponentId = newCompNode.id;
                                    } else {
                                        const compId = chipEl.dataset.compId;
                                        const moved = removeComponentFromAll(compId);
                                        if (moved) {
                                            comp.children.splice(evt.newIndex, 0, moved);
                                        }
                                    }
                                    pushHistory();
                                    renderCanvas();
                                    renderInspector();
                                },
                                onEnd: function(evt) {
                                    if (evt.from === evt.to && evt.oldIndex !== evt.newIndex) {
                                        const [moved] = comp.children.splice(evt.oldIndex, 1);
                                        comp.children.splice(evt.newIndex, 0, moved);
                                        pushHistory();
                                        renderCanvas();
                                    }
                                }
                            });

                            dropzoneEl.appendChild(compEl);
                            return;
                        }

                        compEl.className = `canvas-comp-node ${colors.bg} border ${colors.border} rounded-lg p-3 cursor-pointer transition-all hover:shadow-xs ${isSelected ? 'ring-2 ring-blue-500 shadow-sm' : ''}`;
                        compEl.dataset.compId = comp.id;

                        const slotCount = Object.keys(comp.slots || {}).length;
                        let heroBadge = '';
                        if (comp.component === 'Hero') {
                            const isBgEditable = comp.slots?.background_image?.editable_by !== 'super_admin';
                            heroBadge = isBgEditable
                                ? `<span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Upload Aktif</span>`
                                : `<span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">Hero Terkunci</span>`;
                        } else if (comp.component === 'Footer') {
                            const isSloganEditable = comp.slots?.slogan?.editable_by !== 'super_admin';
                            heroBadge = isSloganEditable
                                ? `<span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Slogan Editable</span>`
                                : `<span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">Footer Terkunci</span>`;
                        }

                        compEl.innerHTML = `
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="cursor-grab text-slate-400 hover:text-slate-600 comp-drag-handle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></svg>
                                    </span>
                                    <span class="${colors.badge} ${colors.accent} text-xs font-bold px-2 py-0.5 rounded">${comp.component}</span>
                                    <span class="text-[11px] text-slate-400 font-mono">${comp.id}</span>
                                    ${heroBadge}
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] text-slate-400">${slotCount} slot</span>
                                    <button type="button" class="btn-remove-comp text-slate-400 hover:text-red-500 p-1 transition" data-comp-id="${comp.id}" title="Hapus komponen">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        `;

                        // Select Component
                        compEl.addEventListener('click', function(e) {
                            if (e.target.closest('.btn-remove-comp')) return;
                            selectedComponentId = comp.id;
                            renderCanvas();
                            renderInspector();
                        });

                        // Remove Component
                        compEl.querySelector('.btn-remove-comp').addEventListener('click', function(e) {
                            e.stopPropagation();
                            container.children = container.children.filter(item => item.id !== comp.id);
                            if (selectedComponentId === comp.id) selectedComponentId = null;
                            pushHistory();
                            renderCanvas();
                            renderInspector();
                        });

                        dropzoneEl.appendChild(compEl);
                    });
                }

                // Remove Container
                conBox.querySelector('.btn-remove-container').addEventListener('click', function(e) {
                    e.stopPropagation();
                    section.children = section.children.filter(item => item.id !== container.id);
                    if (section.children.length === 0) {
                        // Keep at least one empty container in section
                        section.children.push({ id: uid('container'), type: 'container', children: [] });
                    }
                    pushHistory();
                    renderCanvas();
                    renderInspector();
                });

                containersListEl.appendChild(conBox);

                // Sortable for component dropzone
                new Sortable(dropzoneEl, {
                    group: { name: 'components-group', put: true },
                    animation: 180,
                    handle: '.comp-drag-handle',
                    draggable: '.canvas-comp-node',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onAdd: function(evt) {
                        const chipEl = evt.item;
                        const compName = chipEl.dataset.component;

                        if (compName) {
                            // Ditarik dari palet komponen kiri
                            const newCompNode = createComponentNode(compName);
                            container.children.splice(evt.newIndex, 0, newCompNode);
                            chipEl.remove();
                            selectedComponentId = newCompNode.id;
                        } else {
                            // Dipindahkan dari container atau nested container lain
                            const compId = chipEl.dataset.compId;
                            const moved = removeComponentFromAll(compId);
                            if (moved) {
                                container.children.splice(evt.newIndex, 0, moved);
                            }
                        }

                        pushHistory();
                        renderCanvas();
                        renderInspector();
                    },
                    onEnd: function(evt) {
                        if (evt.from === evt.to && evt.oldIndex !== evt.newIndex) {
                            const [moved] = container.children.splice(evt.oldIndex, 1);
                            container.children.splice(evt.newIndex, 0, moved);
                            pushHistory();
                            renderCanvas();
                        }
                    }
                });
            });

            // Add Container Button
            secCard.querySelector('.btn-add-container').addEventListener('click', function(e) {
                e.stopPropagation();
                section.children.push({ id: uid('container'), type: 'container', children: [] });
                pushHistory();
                renderCanvas();
            });

            // Remove Section Button
            secCard.querySelector('.btn-remove-section').addEventListener('click', function(e) {
                e.stopPropagation();
                sections = sections.filter(item => item.id !== section.id);
                pushHistory();
                renderCanvas();
                renderInspector();
            });

            sectionTreeEl.appendChild(secCard);
        });

        // Sortable for Sections
        new Sortable(sectionTreeEl, {
            animation: 180,
            handle: '.section-drag-handle',
            draggable: '.section-node',
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                if (evt.oldIndex !== evt.newIndex) {
                    const [moved] = sections.splice(evt.oldIndex, 1);
                    sections.splice(evt.newIndex, 0, moved);
                    pushHistory();
                }
            }
        });
    }

    // ====== ADD SECTION ACTIONS ======
    function addNewSection() {
        const newSec = {
            id: uid('section'),
            type: 'section',
            children: [
                {
                    id: uid('container'),
                    type: 'container',
                    children: []
                }
            ]
        };
        sections.push(newSec);
        pushHistory();
        renderCanvas();
    }

    document.getElementById('btn-add-section-sidebar').addEventListener('click', addNewSection);
    document.getElementById('btn-add-section-canvas').addEventListener('click', addNewSection);

    // ====== SORTABLE: PALETTE (Clone to container) ======
    new Sortable(document.getElementById('component-palette'), {
        group: { name: 'components-group', pull: 'clone', put: false },
        sort: false,
        draggable: '.component-chip',
    });

    const headerNodeBtn = document.getElementById('blueprint-header-node');
    if (headerNodeBtn) {
        headerNodeBtn.addEventListener('click', function() {
            selectedComponentId = 'header_navbar';
            renderCanvas();
            renderInspector();
        });
    }

    // ====== INSPECTOR PANEL (Bab 4: Admin Editable vs Template Controlled) ======
    const configPanel = document.getElementById('config-panel');
    const configEmpty = document.getElementById('config-empty');
    const inspectorBadge = document.getElementById('inspector-badge');

    function renderHeaderInspector() {
        const wrapper = document.createElement('div');
        wrapper.className = 'inspector-content space-y-5';

        const logoSlot = headerConfig.slots?.logo || {
            binding: 'appearance.logo',
            type: 'image',
            editable_by: 'admin_dinas',
            default_value: ''
        };
        const isAdminEditable = (logoSlot.editable_by !== 'super_admin');

        wrapper.innerHTML = `
            <div class="pb-3 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800">Header / Navbar</span>
                    <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">fixed_header</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Struktur navigasi atas dan pengaturan slot logo dinas</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Layout Settings</h4>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        Template Controlled
                    </span>
                </div>
                <div class="space-y-2 text-xs bg-slate-50 p-2.5 rounded border border-slate-200">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Posisi Navigasi:</span>
                        <span class="font-semibold text-slate-800">Sticky Top</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Struktur Komponen:</span>
                        <span class="font-semibold text-slate-800">Logo + Nama Dinas + Navigasi Menu Bertingkat</span>
                    </div>
                    <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-200">
                        Navbar publik secara otomatis menyertakan menu navigasi institusi yang mendukung hierarki menu, sub-menu, hingga sub-sub-menu untuk seluruh menu di header (Pages berstatus <em>published</em>).
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2 mt-4">
                    <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Slots &amp; Binding</h4>
                    <span class="text-[10px] text-slate-400">1 slot terdaftar</span>
                </div>

                <div class="bg-slate-50 rounded-lg p-3 mb-2.5 border border-slate-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700">Logo Resmi Instansi (Header Logo)</span>
                        ${isAdminEditable 
                            ? `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Admin Editable</span>`
                            : `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">Template Controlled</span>`
                        }
                    </div>
                    <div class="space-y-2 text-xs">
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Binding DataSource</label>
                            <select id="header-slot-binding"
                                class="w-full text-xs border border-slate-200 rounded px-2.5 py-1.5 bg-white focus:ring-1 focus:ring-blue-500 outline-none font-mono">
                                <option value="appearance.logo" selected>appearance.logo (Logo Resmi Instansi Dinas)</option>
                                ${logoSlot.binding && logoSlot.binding !== 'appearance.logo' ? `<option value="${logoSlot.binding}" selected>${logoSlot.binding} (Kustom)</option>` : ''}
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">URL / Fallback Logo Bawaan Template</label>
                            <input type="text" value="${logoSlot.default_value || ''}" id="header-slot-default"
                                placeholder="https://example.com/logo-batu-default.png"
                                class="w-full text-xs border border-slate-200 rounded px-2 py-1 bg-white focus:ring-1 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-0.5">Tipe Data</label>
                                <select id="header-slot-type"
                                    class="w-full text-xs border border-slate-200 rounded px-2 py-1 bg-white focus:ring-1 focus:ring-blue-500 outline-none">
                                    <option value="image" selected>image</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-0.5">Editable By</label>
                                <select id="header-slot-editable"
                                    class="w-full text-xs border border-slate-200 rounded px-2 py-1 bg-white focus:ring-1 focus:ring-blue-500 outline-none">
                                    <option value="admin_dinas" ${logoSlot.editable_by !== 'super_admin' ? 'selected' : ''}>admin_dinas (Admin Editable)</option>
                                    <option value="super_admin" ${logoSlot.editable_by === 'super_admin' ? 'selected' : ''}>super_admin (Template Controlled)</option>
                                </select>
                            </div>
                        </div>
                        ${isAdminEditable
                            ? `<p class="text-[10px] text-emerald-700 bg-emerald-50/80 p-2 rounded border border-emerald-200 mt-2">✓ <strong>Admin Editable</strong>: Admin Kedinasan dapat mengunggah file logo resmi dari komputernya di menu Appearance.</p>`
                            : `<p class="text-[10px] text-slate-600 bg-slate-100 p-2 rounded border border-slate-200 mt-2">🔒 <strong>Template Controlled</strong>: Logo dikunci seragam oleh Super Admin. Admin Kedinasan tidak diizinkan mengubah logo.</p>`
                        }
                    </div>
                </div>
            </div>
        `;

        configPanel.appendChild(wrapper);

        // Bind events
        const updateHeaderBinding = function() {
            headerConfig.slots.logo.binding = this.value;
            pushHistory();
        };
        wrapper.querySelector('#header-slot-binding').addEventListener('input', updateHeaderBinding);
        wrapper.querySelector('#header-slot-binding').addEventListener('change', updateHeaderBinding);

        wrapper.querySelector('#header-slot-default').addEventListener('input', function() {
            headerConfig.slots.logo.default_value = this.value;
            pushHistory();
        });

        wrapper.querySelector('#header-slot-editable').addEventListener('change', function() {
            headerConfig.slots.logo.editable_by = this.value;
            pushHistory();
            renderCanvas();
            renderInspector();
        });
    }

    function renderInspector() {
        configPanel.querySelectorAll('.inspector-content').forEach(el => el.remove());

        if (selectedComponentId === 'header_navbar') {
            configEmpty.classList.add('hidden');
            inspectorBadge.textContent = 'Header / Navbar';
            renderHeaderInspector();
            return;
        }

        const target = selectedComponentId ? findComponentById(selectedComponentId) : null;
        configEmpty.classList.toggle('hidden', !!target);

        if (!target) {
            inspectorBadge.textContent = 'Pilih elemen';
            return;
        }

        const comp = target.component;
        inspectorBadge.textContent = comp.component;

        const wrapper = document.createElement('div');
        wrapper.className = 'inspector-content space-y-5';

        // Header info
        wrapper.innerHTML = `
            <div class="pb-3 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800">${comp.component}</span>
                    <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">${comp.id}</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Konfigurasi tata letak &amp; Editable Slots</p>
            </div>
        `;

        // Section 1: Layout Settings (Template Controlled)
        const layoutBox = document.createElement('div');
        layoutBox.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Layout Settings</h4>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200" title="Dikunci oleh template Super Admin">
                    Template Controlled
                </span>
            </div>
        `;

        const layoutEntries = Object.entries(comp.layout_settings || {});
        if (layoutEntries.length === 0) {
            layoutBox.innerHTML += `<p class="text-xs text-slate-400 italic">Tidak ada pengaturan tata letak khusus</p>`;
        } else {
            layoutEntries.forEach(([key, val]) => {
                const isHeroAlignment = (comp.component === 'Hero' && key === 'alignment');
                const isHeroHeight = (comp.component === 'Hero' && key === 'height');
                const isPostsGridColumns = (comp.component === 'Posts Grid' && key === 'columns');
                const isPostsGridLimit = (comp.component === 'Posts Grid' && key === 'limit');
                const isFooterColumns = (comp.component === 'Footer' && key === 'columns');
                const isMediaLimit = (comp.component === 'Media / Document List' && key === 'limit');
                const isContainerColumns = (comp.component === 'Container' && key === 'columns');
                const field = document.createElement('div');
                field.className = 'mb-2.5';

                if (isHeroAlignment) {
                    comp.layout_settings['alignment'] = 'center';
                    field.innerHTML = `
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-medium text-slate-600 capitalize">Alignment</label>
                            <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">🔒 Paten</span>
                        </div>
                        <input type="text" value="center" readonly disabled
                            class="w-full text-xs border border-slate-200 bg-slate-100 text-slate-500 rounded-md px-2.5 py-1.5 cursor-not-allowed select-none font-medium">
                        <p class="text-[10px] text-slate-400 mt-1">Perataan posisi hero dipatenkan ke <strong>center</strong> (Fixed Blueprint).</p>
                    `;
                } else if (isHeroHeight) {
                    const heights = ['400px', '480px', '520px', '560px', '640px'];
                    const currentVal = String(val || '480px');
                    const hasCurrent = heights.includes(currentVal);
                    field.innerHTML = `
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tinggi Banner Hero (Height)</label>
                        <select data-layout-key="height" class="layout-input w-full text-xs border border-slate-200 rounded-md px-2.5 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none transition font-medium">
                            ${!hasCurrent ? `<option value="${currentVal}" selected>${currentVal} (Kustom)</option>` : ''}
                            <option value="400px" ${currentVal === '400px' ? 'selected' : ''}>400px (Kompak)</option>
                            <option value="480px" ${currentVal === '480px' ? 'selected' : ''}>480px (Standar Rekomendasi)</option>
                            <option value="520px" ${currentVal === '520px' ? 'selected' : ''}>520px (Sedang)</option>
                            <option value="560px" ${currentVal === '560px' ? 'selected' : ''}>560px (Tinggi Luas)</option>
                            <option value="640px" ${currentVal === '640px' ? 'selected' : ''}>640px (Hero Layar Penuh)</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Ukuran tinggi vertikal minimum banner hero di website publik.</p>
                    `;
                } else if (isPostsGridColumns) {
                    field.innerHTML = `
                        <label class="block text-xs font-medium text-slate-600 mb-1">Jumlah Kolom Kartu Berita</label>
                        <select data-layout-key="columns" class="layout-input w-full text-xs border border-slate-200 rounded-md px-2.5 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none transition font-medium">
                            <option value="1" ${val == 1 ? 'selected' : ''}>1 Kolom (Vertikal / Full Width)</option>
                            <option value="2" ${val == 2 ? 'selected' : ''}>2 Kolom</option>
                            <option value="3" ${val == 3 ? 'selected' : ''}>3 Kolom (Standar Rekomendasi)</option>
                            <option value="4" ${val == 4 ? 'selected' : ''}>4 Kolom</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Tata letak kolom kartu publikasi berita/artikel.</p>
                    `;
                } else if (isPostsGridLimit) {
                    field.innerHTML = `
                        <label class="block text-xs font-medium text-slate-600 mb-1">Batas Jumlah Artikel (Limit)</label>
                        <input type="number" min="1" max="24" step="1" value="${val || 6}" data-layout-key="limit"
                            class="layout-input w-full text-xs border border-slate-200 rounded-md px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none transition font-medium">
                        <p class="text-[10px] text-slate-400 mt-1">Jumlah artikel publikasi yang ditampilkan pada grid (1 – 24 artikel).</p>
                    `;
                } else if (isFooterColumns) {
                    field.innerHTML = `
                        <label class="block text-xs font-medium text-slate-600 mb-1">Jumlah Kolom Footer</label>
                        <select data-layout-key="columns" class="layout-input w-full text-xs border border-slate-200 rounded-md px-2.5 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none transition font-medium">
                            <option value="2" ${val == 2 ? 'selected' : ''}>2 Kolom (Identitas & Kontak Kedinasan)</option>
                            <option value="3" ${val == 3 ? 'selected' : ''}>3 Kolom (Identitas, Kontak, & Info Tambahan)</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Tata letak pembagian kolom informasi pada bagian footer.</p>
                    `;
                } else if (isMediaLimit) {
                    field.innerHTML = `
                        <label class="block text-xs font-medium text-slate-600 mb-1">Batas Jumlah Dokumen (Limit)</label>
                        <input type="number" min="1" max="30" step="1" value="${val || 10}" data-layout-key="limit"
                            class="layout-input w-full text-xs border border-slate-200 rounded-md px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none transition font-medium">
                        <p class="text-[10px] text-slate-400 mt-1">Batas jumlah berkas publik yang ditampilkan pada daftar (1 – 30 berkas).</p>
                    `;
                } else if (isContainerColumns) {
                    field.innerHTML = `
                        <label class="block text-xs font-medium text-slate-600 mb-1">Jumlah Kolom Grid</label>
                        <select data-layout-key="columns" class="layout-input w-full text-xs border border-slate-200 rounded-md px-2.5 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 outline-none transition font-medium">
                            <option value="1" ${val == 1 ? 'selected' : ''}>1 Kolom (Penuh / Stack)</option>
                            <option value="2" ${val == 2 ? 'selected' : ''}>2 Kolom (50 : 50)</option>
                            <option value="3" ${val == 3 ? 'selected' : ''}>3 Kolom (33 : 33 : 33)</option>
                            <option value="4" ${val == 4 ? 'selected' : ''}>4 Kolom (25 : 25 : 25 : 25)</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Mengatur pembagian kolom responsif untuk komponen di dalam container ini.</p>
                    `;
                } else {
                    field.innerHTML = `
                        <label class="block text-xs font-medium text-slate-600 mb-1 capitalize">${key.replace(/_/g, ' ')}</label>
                        <input type="text" value="${val}" data-layout-key="${key}"
                            class="layout-input w-full text-xs border border-slate-200 rounded-md px-2.5 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    `;
                }
                layoutBox.appendChild(field);
            });
        }
        wrapper.appendChild(layoutBox);

        // Section 2: Slots & Data Binding (Admin Editable vs Template Controlled)
        const slotsBox = document.createElement('div');
        slotsBox.innerHTML = `
            <div class="flex items-center justify-between mb-2 mt-4">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Slots &amp; Binding</h4>
                <span class="text-[10px] text-slate-400">${Object.keys(comp.slots || {}).length} slot terdaftar</span>
            </div>
        `;

        const slotEntries = Object.entries(comp.slots || {});
        if (comp.component === 'Container') {
            slotsBox.innerHTML += `
                <div class="p-3.5 rounded-lg bg-blue-50/70 border border-blue-200 text-blue-800 text-xs">
                    <p class="font-semibold mb-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Layout Wrapper Component</span>
                    </p>
                    <p class="text-[11px] text-blue-700 leading-relaxed">Komponen Container bertindak sebagai pembungkus grid multi-kolom bagi komponen di dalamnya. Tidak memerlukan konfigurasi data binding dari dinas.</p>
                </div>
            `;
        } else if (slotEntries.length === 0) {
            slotsBox.innerHTML += `<p class="text-xs text-slate-400 italic">Komponen ini tidak memiliki slot data variabel</p>`;
        } else {
            slotEntries.forEach(([slotKey, slot]) => {
                const card = document.createElement('div');
                card.className = 'bg-slate-50 rounded-lg p-3 mb-2.5 border border-slate-200';

                const isAdminEditable = (slot.editable_by !== 'super_admin');
                const isImageSlot = (slotKey === 'background_image' || slot.type === 'image');
                const isTextareaSlot = (slotKey === 'content' || slot.type === 'textarea');
                let slotLabel = slotKey.replace(/_/g, ' ');
                if (comp.component === 'Hero' && slotKey === 'background_image') {
                    slotLabel = 'Hero Background Image (Gambar Latar)';
                } else if (comp.component === 'Static Content') {
                    if (slotKey === 'title') slotLabel = 'Judul Seksi Profil';
                    else if (slotKey === 'content') slotLabel = 'Teks Isi Profil Ringkas';
                } else if (comp.component === 'Footer') {
                    if (slotKey === 'title') slotLabel = 'Judul / Nama Instansi (Kolom 1)';
                    else if (slotKey === 'slogan') slotLabel = 'Footer Slogan (Slogan Penutup)';
                    else if (slotKey === 'about_title') slotLabel = 'Judul Info Tambahan (Kolom 3)';
                    else if (slotKey === 'about_text') slotLabel = 'Teks Info Tambahan (Kolom 3)';
                    else if (slotKey === 'copyright') slotLabel = 'Pemilik Hak Cipta (Copyright)';
                }

                let slotNote = '';
                if (comp.component === 'Hero' && slotKey === 'background_image') {
                    slotNote = isAdminEditable
                        ? `<p class="text-[10px] text-emerald-700 bg-emerald-50/80 p-2 rounded border border-emerald-200 mt-2">✓ <strong>Admin Editable</strong>: Admin Kedinasan dapat mengunggah berkas gambar dari perangkat di menu Appearance.</p>`
                        : `<p class="text-[10px] text-slate-600 bg-slate-100 p-2 rounded border border-slate-200 mt-2">🔒 <strong>Template Controlled</strong>: Gambar latar dikunci oleh Super Admin. Admin Kedinasan tidak diizinkan mengunggah gambar.</p>`;
                } else if (comp.component === 'Static Content') {
                    slotNote = isAdminEditable
                        ? `<p class="text-[10px] text-emerald-700 bg-emerald-50/80 p-2 rounded border border-emerald-200 mt-2">✓ <strong>Admin Editable</strong>: Dikelola oleh Admin Kedinasan melalui modul Pages (penempatan Beranda). Jika belum diisi, website merender ruang placeholder tanpa teks dummy.</p>`
                        : `<p class="text-[10px] text-slate-600 bg-slate-100 p-2 rounded border border-slate-200 mt-2">🔒 <strong>Template Controlled</strong>: Teks dikunci oleh Super Admin menggunakan nilai default template.</p>`;
                } else if (comp.component === 'Media / Document List') {
                    slotNote = isAdminEditable
                        ? `<p class="text-[10px] text-emerald-700 bg-emerald-50/80 p-2 rounded border border-emerald-200 mt-2">✓ <strong>Admin Managed</strong>: Berkas dokumen PDF dan gambar diunggah oleh Admin Kedinasan melalui menu Media.</p>`
                        : `<p class="text-[10px] text-slate-600 bg-slate-100 p-2 rounded border border-slate-200 mt-2">🔒 <strong>Template Controlled</strong>: Sumber media dikunci oleh Super Admin.</p>`;
                } else if (comp.component === 'Footer') {
                    if (slotKey === 'title') {
                        slotNote = isAdminEditable
                            ? `<p class="text-[10px] text-emerald-700 bg-emerald-50/80 p-2 rounded border border-emerald-200 mt-2">✓ <strong>Admin Editable</strong>: Nama/judul instansi footer dapat disesuaikan Admin Kedinasan di menu Appearance.</p>`
                            : `<p class="text-[10px] text-slate-600 bg-slate-100 p-2 rounded border border-slate-200 mt-2">🔒 <strong>Template Controlled</strong>: Judul instansi footer dikunci oleh Super Admin.</p>`;
                    } else if (slotKey === 'slogan') {
                        slotNote = isAdminEditable
                            ? `<p class="text-[10px] text-emerald-700 bg-emerald-50/80 p-2 rounded border border-emerald-200 mt-2">✓ <strong>Admin Editable</strong>: Slogan/teks penutup dikelola oleh Admin Kedinasan di menu Appearance.</p>`
                            : `<p class="text-[10px] text-slate-600 bg-slate-100 p-2 rounded border border-slate-200 mt-2">🔒 <strong>Template Controlled</strong>: Teks slogan footer dikunci oleh Super Admin.</p>`;
                    } else if (slotKey === 'about_title' || slotKey === 'about_text') {
                        slotNote = isAdminEditable
                            ? `<p class="text-[10px] text-emerald-700 bg-emerald-50/80 p-2 rounded border border-emerald-200 mt-2">✓ <strong>Admin Editable</strong>: Kolom 3 info dapat dikelola oleh Admin Kedinasan di menu Appearance.</p>`
                            : `<p class="text-[10px] text-slate-600 bg-slate-100 p-2 rounded border border-slate-200 mt-2">🔒 <strong>Template Controlled</strong>: Kolom 3 info dikunci oleh Super Admin.</p>`;
                    } else if (slotKey === 'copyright') {
                        slotNote = `<p class="text-[10px] text-slate-600 bg-slate-100 p-2 rounded border border-slate-200 mt-2">ℹ️ <strong>Copyright</strong>: Entitas pemegang hak cipta pada baris penutup footer.</p>`;
                    }
                }

                const isCollectionSlot = (comp.component === 'Posts Grid' && slotKey === 'items') ||
                                         (comp.component === 'Media / Document List' && slotKey === 'items');

                const bindingOptions = BINDING_OPTIONS[comp.component]?.[slotKey] || [];
                const currentBinding = slot.binding || '';
                const hasCurrentBinding = bindingOptions.some(b => b.value === currentBinding);

                const bindingControlHtml = bindingOptions.length > 0 ? `
                    <select data-slot-key="${slotKey}" data-field="binding"
                        class="slot-select w-full text-xs border border-slate-200 rounded px-2.5 py-1.5 bg-white focus:ring-1 focus:ring-blue-500 outline-none font-mono">
                        ${!hasCurrentBinding && currentBinding ? `<option value="${currentBinding}" selected>${currentBinding} (Kustom)</option>` : ''}
                        ${bindingOptions.map(b => `<option value="${b.value}" ${currentBinding === b.value ? 'selected' : ''}>${b.label}</option>`).join('')}
                    </select>
                ` : `
                    <input type="text" value="${currentBinding}" data-slot-key="${slotKey}" data-field="binding"
                        class="slot-input w-full font-mono text-xs border border-slate-200 rounded px-2.5 py-1.5 bg-white focus:ring-1 focus:ring-blue-500 outline-none">
                `;

                const fallbackControlHtml = isCollectionSlot ? `
                    <div class="p-2.5 rounded bg-slate-100/90 text-slate-600 text-[11px] leading-relaxed border border-slate-200/80">
                        📊 <strong>Data Koleksi Otomatis:</strong> Slot ini menampung daftar record data dinamis dari database tenant. Jika dinas belum memiliki konten yang dipublikasikan, sistem secara otomatis menampilkan state kosong (empty state) yang rapi tanpa teks dummy.
                    </div>
                ` : `
                    <div>
                        <label class="block text-[11px] text-slate-500 mb-0.5">${isImageSlot ? 'URL / Fallback Gambar Bawaan Template' : 'Teks / Nilai Default (Fallback)'}</label>
                        ${isTextareaSlot 
                            ? `<textarea data-slot-key="${slotKey}" data-field="default_value" rows="3"
                                  placeholder="Teks fallback jika data dinas kosong..."
                                  class="slot-input w-full text-xs border border-slate-200 rounded px-2 py-1.5 bg-white focus:ring-1 focus:ring-blue-500 outline-none leading-relaxed">${slot.default_value || ''}</textarea>`
                            : `<input type="text" value="${slot.default_value || ''}" data-slot-key="${slotKey}" data-field="default_value"
                                  placeholder="${isImageSlot ? 'https://example.com/banner-default.jpg' : 'Teks fallback jika data dinas kosong'}"
                                  class="slot-input w-full text-xs border border-slate-200 rounded px-2 py-1 bg-white focus:ring-1 focus:ring-blue-500 outline-none">`
                        }
                    </div>
                `;

                const typeAndEditorHtml = isCollectionSlot ? `
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Tipe Data</label>
                            <span class="inline-flex items-center px-2 py-1 text-xs font-mono bg-slate-100 border border-slate-200 rounded text-slate-600 w-full">collection</span>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Editable By</label>
                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-emerald-50 border border-emerald-200 rounded text-emerald-700 w-full">admin_dinas</span>
                        </div>
                    </div>
                ` : `
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Tipe Data</label>
                            <select data-slot-key="${slotKey}" data-field="type"
                                class="slot-select w-full text-xs border border-slate-200 rounded px-2 py-1 bg-white focus:ring-1 focus:ring-blue-500 outline-none">
                                <option value="">— standar —</option>
                                ${ALLOWED_SLOT_TYPES.map(t => `<option value="${t}" ${slot.type === t ? 'selected' : ''}>${t}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Editable By</label>
                            <select data-slot-key="${slotKey}" data-field="editable_by"
                                class="slot-select w-full text-xs border border-slate-200 rounded px-2 py-1 bg-white focus:ring-1 focus:ring-blue-500 outline-none">
                                ${ALLOWED_EDITORS.map(e => `<option value="${e}" ${slot.editable_by === e ? 'selected' : ''}>${e}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                `;

                card.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700 capitalize">${slotLabel}</span>
                        ${isAdminEditable 
                            ? `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Admin Editable</span>`
                            : `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">Template Controlled</span>`
                        }
                    </div>
                    <div class="space-y-2 text-xs">
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">Binding DataSource</label>
                            ${bindingControlHtml}
                        </div>
                        ${fallbackControlHtml}
                        ${typeAndEditorHtml}
                        ${slotNote}
                    </div>
                `;
                slotsBox.appendChild(card);
            });
        }
        wrapper.appendChild(slotsBox);

        configPanel.appendChild(wrapper);

        // Bind Inspector Inputs
        wrapper.querySelectorAll('.layout-input').forEach(input => {
            const handleUpdate = function() {
                const key = this.dataset.layoutKey;
                let val = this.value;
                if (!isNaN(val) && val.trim() !== '') val = Number(val);
                if (val === 'true') val = true;
                if (val === 'false') val = false;
                comp.layout_settings[key] = val;
                pushHistory();
                renderCanvas();
            };
            input.addEventListener('input', handleUpdate);
            input.addEventListener('change', handleUpdate);
        });

        wrapper.querySelectorAll('.slot-input').forEach(input => {
            input.addEventListener('input', function() {
                const sk = this.dataset.slotKey;
                const field = this.dataset.field;
                comp.slots[sk][field] = this.value;
                pushHistory();
            });
        });

        wrapper.querySelectorAll('.slot-select').forEach(select => {
            select.addEventListener('change', function() {
                const sk = this.dataset.slotKey;
                const field = this.dataset.field;
                const val = this.value;
                if (val) {
                    comp.slots[sk][field] = val;
                } else {
                    delete comp.slots[sk][field];
                }
                pushHistory();
                renderInspector(); // Re-render to update badges
            });
        });
    }

    // ====== SAVE ACTION (Persist to Backend) ======
    btnSave.addEventListener('click', async function() {
        btnSave.disabled = true;
        btnSave.classList.add('opacity-50');
        saveDot.className = 'w-2 h-2 rounded-full bg-slate-400 animate-pulse';
        saveStatusText.textContent = 'Menyimpan...';

        // Pastikan alignment Hero selalu dipatenkan ke 'center'
        sections.forEach(s => {
            (s.children || []).forEach(c => {
                (c.children || []).forEach(comp => {
                    if (comp.component === 'Hero' && comp.layout_settings) {
                        comp.layout_settings.alignment = 'center';
                    }
                });
            });
        });

        const payload = {
            canvas_data: {
                template_version: '1.0',
                canvas: sections,
                header: headerConfig
            }
        };

        try {
            const resp = await fetch(SAVE_URL, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await resp.json();

            if (resp.ok) {
                lastSavedSnapshot = getSnapshot();
                updateSaveStatus();
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal menyimpan');
                saveDot.className = 'w-2 h-2 rounded-full bg-red-500';
                saveStatusText.className = 'text-red-500 font-semibold';
                saveStatusText.textContent = 'Error: ' + msg;
            }
        } catch (err) {
            saveDot.className = 'w-2 h-2 rounded-full bg-red-500';
            saveStatusText.className = 'text-red-500 font-semibold';
            saveStatusText.textContent = 'Error: ' + err.message;
        }

        btnSave.disabled = false;
        btnSave.classList.remove('opacity-50');
    });

    // ====== INITIALIZATION ======
    pushHistory(true);
    renderCanvas();
    renderInspector();
    updateSaveStatus();
});
</script>

</body>
</html>
