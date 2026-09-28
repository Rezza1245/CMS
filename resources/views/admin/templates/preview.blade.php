<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Template: {{ $template->name }} — CMS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com?v=4"></script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-white text-slate-800 antialiased">

    {{-- TOP BANNER: MODE PREVIEW TEMPLATE BUILDER --}}
    <div class="bg-indigo-600 text-white text-xs font-semibold px-4 py-2.5 text-center shadow-xs flex items-center justify-between z-50 sticky top-0">
        <div class="flex items-center gap-2 mx-auto">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>Mode Pratinjau Template — <strong>{{ $template->name }}</strong> (Hanya Pratinjau Struktur Template Builder, Tidak Ada Konten Terpost)</span>
        </div>
        <a href="{{ route('admin.templates.builder', $template) }}" class="text-[11px] bg-white/20 hover:bg-white/30 text-white font-medium px-3 py-1 rounded-md transition shrink-0">
            Kembali ke Builder
        </a>
    </div>

    {{-- BLUEPRINT HEADER / NAVBAR --}}
    <x-public.navbar :isPreview="true" :template="$template" />

    {{-- DYNAMIC TEMPLATE SECTIONS (FROM CANVAS RENDERER) --}}
    <main class="flex-1">
        {!! $renderedContent !!}
    </main>

</body>
</html>
