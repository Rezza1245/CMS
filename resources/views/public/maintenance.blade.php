<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeliharaan Sistem - {{ $website->name ?? 'Portal Kedinasan' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between font-sans antialiased selection:bg-amber-100 selection:text-amber-900">

    {{-- Header Minimalis --}}
    <header class="bg-white border-b border-slate-200/80 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-base shadow-xs">
                    {{ strtoupper(substr($website->dinas?->code ?? 'KB', 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 leading-tight">
                        {{ $website->name }}
                    </h1>
                    <p class="text-[11px] text-slate-500">
                        {{ $website->dinas?->name ?? 'Pemerintah Kota Batu' }}
                    </p>
                </div>
            </div>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Pemeliharaan Internal
            </span>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="max-w-xl w-full text-center space-y-6">
            {{-- Icon Banner --}}
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-amber-50 border border-amber-200/70 text-amber-600 shadow-sm mx-auto">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.32l-3.27 3.27-2.12-2.12 3.27-3.27a4.5 4.5 0 00-6.32 4.486c.118.58.094 1.193-.07 1.743m-1.442 2.502l-4.24-4.24a2.25 2.25 0 00-3.182 0l-.884.884a2.25 2.25 0 000 3.182l4.24 4.24m0 0l-1.06 1.06"></path>
                </svg>
            </div>

            {{-- Text Heading --}}
            <div class="space-y-2">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Website Sedang Dalam Pemeliharaan
                </h2>
                <p class="text-sm text-slate-600 leading-relaxed max-w-md mx-auto">
                    Website resmi <span class="font-semibold text-slate-800">{{ $website->dinas?->name ?? $website->name }}</span> sedang dalam proses pemeliharaan internal untuk peningkatan kualitas sistem dan layanan publik.
                </p>
            </div>

            {{-- Callout Box --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 text-left shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Layanan &amp; Kontak Resmi
                </h3>
                <p class="text-xs text-slate-500">
                    Masyarakat yang membutuhkan pelayanan kedinasan darurat dapat menghubungi kontak resmi kami di bawah ini:
                </p>
                <div class="pt-2 border-t border-slate-100 space-y-2 text-xs">
                    @if($website->dinas?->address)
                        <div class="flex items-start gap-2.5 text-slate-600">
                            <span class="font-semibold text-slate-700 shrink-0">Alamat:</span>
                            <span>{{ $website->dinas->address }}</span>
                        </div>
                    @endif
                    @if($website->dinas?->contact_email)
                        <div class="flex items-center gap-2.5 text-slate-600">
                            <span class="font-semibold text-slate-700 shrink-0">Email:</span>
                            <a href="mailto:{{ e($website->dinas->contact_email) }}" class="text-blue-600 hover:underline">{{ $website->dinas->contact_email }}</a>
                        </div>
                    @endif
                    @if($website->dinas?->phone)
                        <div class="flex items-center gap-2.5 text-slate-600">
                            <span class="font-semibold text-slate-700 shrink-0">Telepon:</span>
                            <span>{{ $website->dinas->phone }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <p class="text-[11px] text-slate-400">
                Sistem akan segera kembali aktif setelah pemeliharaan selesai. Terima kasih atas pengertian Anda.
            </p>
        </div>
    </main>

    {{-- Footer Minimalis --}}
    <footer class="bg-white border-t border-slate-200/80 py-4 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} {{ $website->dinas?->name ?? 'Pemerintah Kota Batu' }}. Pemerintah Kota Batu.</p>
    </footer>

</body>
</html>
