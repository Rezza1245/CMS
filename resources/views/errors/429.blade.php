<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 — Terlalu Banyak Permintaan | CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        navy: { 900: '#0f172a' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center">
        {{-- Icon Warning / Clock --}}
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/80 mx-auto flex items-center justify-center mb-5 shadow-xs">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>

        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 uppercase tracking-wider mb-2">
            Error 429 · Rate Limit Exceeded
        </span>

        <h1 class="text-xl font-bold text-slate-900 mb-2">
            Terlalu Banyak Permintaan
        </h1>

        <p class="text-xs text-slate-500 leading-relaxed mb-6">
            {{ $message ?? 'Sistem mendeteksi lonjakan aktivitas atau percobaan berulang dalam waktu singkat. Demi menjaga stabilitas dan keamanan data, akses dibatasi sementara.' }}
        </p>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-xs text-left mb-6 space-y-1">
            <div class="flex items-center gap-2 font-semibold text-slate-700">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Petunjuk Keamanan:</span>
            </div>
            <p class="text-[11px] text-slate-500 pl-6">
                Silakan tunggu <strong>1 menit</strong> sebelum memuat ulang halaman atau mengulang percobaan.
            </p>
        </div>

        <div class="flex items-center justify-center gap-3">
            <button type="button" onclick="window.location.reload()"
                class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                Muat Ulang Halaman
            </button>
            <a href="{{ url('/') }}"
                class="px-5 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-semibold transition">
                Ke Beranda
            </a>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-100 text-[11px] text-slate-400">
             CMS — Diskominfo Kota Batu
        </div>
    </div>

</body>
</html>
