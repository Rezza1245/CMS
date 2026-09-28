<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $website->name }} — Pemerintah Kota Batu</title>
    @if($website->appearance?->favicon)
        <link rel="icon" href="{{ e($website->appearance->favicon) }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com?v=4"></script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-white text-slate-800 antialiased">

    @if($website->status === 'pemeliharaan')
        <div class="bg-amber-600 text-white text-xs font-semibold px-4 py-2 text-center shadow-xs flex items-center justify-center gap-2">
            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
            Mode Pemeliharaan Aktif — Halaman ini hanya dapat diakses melalui pratinjau internal pengelola kedinasan.
        </div>
    @endif

    {{-- TOP NAVBAR (7 MAIN MENUS) --}}
    <x-public.navbar :website="$website" />

    {{-- DYNAMIC TEMPLATE SECTIONS (FROM CANVAS RENDERER) --}}
    <main class="flex-1">
        {!! $renderedContent !!}
    </main>

</body>
</html>
