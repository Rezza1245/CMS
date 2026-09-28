@props([
    'id' => 'hero',
    'layout' => [],
    'slots' => [],
    'website' => null,
])

@php
    $height = e($layout['height'] ?? '480px');
    // Alignment dipatenkan ke center sesuai fixed blueprint
    $alignment = 'center';
    $alignClass = 'text-center items-center';

    $title = $slots['title'] ?? ($website?->name ?? 'Portal Resmi Kedinasan');
    $description = $slots['description'] ?? ($website?->dinas?->address ?? '');
    $bgImage = $slots['background_image'] ?? null;
    if ($bgImage && !str_starts_with($bgImage, 'http://') && !str_starts_with($bgImage, 'https://') && !str_starts_with($bgImage, '//')) {
        $bgImage = asset(str_starts_with($bgImage, 'storage/') ? $bgImage : 'storage/' . ltrim($bgImage, '/'));
    }

    // Format WhatsApp URL untuk tombol baku template "Hubungi Kami"
    $rawPhone = $website?->dinas?->phone ?? '';
    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawPhone);

    if (str_starts_with($cleanPhone, '0')) {
        $waNumber = '62' . substr($cleanPhone, 1);
    } elseif (str_starts_with($cleanPhone, '62')) {
        $waNumber = $cleanPhone;
    } elseif (!empty($cleanPhone)) {
        $waNumber = '62' . $cleanPhone;
    } else {
        $waNumber = null;
    }

    $dinasName = $website?->dinas?->name ?? 'Instansi Kedinasan';
    $waText = rawurlencode("Halo {$dinasName} Kota Batu, saya ingin berkonsultasi / mendapatkan informasi lebih lanjut.");
    $waUrl = $waNumber ? "https://wa.me/{$waNumber}?text={$waText}" : ($website?->dinas?->contact_email ? 'mailto:' . $website->dinas->contact_email : '#footer');
@endphp

<section id="{{ e($id) }}" class="relative overflow-hidden bg-slate-900 text-white flex {{ $alignClass }} justify-center px-4 sm:px-6 lg:px-8"
    style="min-height: {{ $height }}; @if($bgImage) background-image: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), url('{{ e($bgImage) }}'); background-size: cover; background-position: center; @endif">
    
    {{-- Decorative subtle gradient overlay --}}
    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent opacity-80 pointer-events-none"></div>

    <div class="relative z-10 max-w-4xl py-16 flex flex-col {{ $alignClass }}">
        @if($website?->dinas)
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-semibold uppercase tracking-wider mb-6">
                <span>{{ $website->dinas->name }}</span>
            </div>
        @endif

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white mb-4 leading-tight">
            {{ $title }}
        </h1>

        @if($description)
            <p class="text-base sm:text-lg text-slate-300 max-w-2xl leading-relaxed mb-8">
                {{ $description }}
            </p>
        @endif

        <div class="flex flex-wrap gap-4">
            <a href="{{ $waUrl }}"
               @if($waNumber) target="_blank" rel="noopener noreferrer" @endif
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition shadow-lg shadow-emerald-600/20">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.53 7.34C9.36 7.34 9.09 7.4 8.87 7.65C8.64 7.9 8 8.5 8 9.71C8 10.93 8.89 12.1 9.01 12.27C9.14 12.44 10.74 14.91 13.2 15.97C13.78 16.22 14.24 16.37 14.6 16.49C15.18 16.67 15.71 16.65 16.13 16.59C16.6 16.51 17.56 16 17.76 15.43C17.96 14.87 17.96 14.38 17.9 14.28C17.84 14.19 17.68 14.13 17.44 14.01C17.2 13.89 16.01 13.31 15.79 13.23C15.57 13.15 15.41 13.11 15.25 13.35C15.08 13.6 14.62 14.13 14.47 14.29C14.33 14.46 14.19 14.48 13.95 14.36C13.71 14.24 12.94 13.99 12.03 13.17C11.32 12.54 10.84 11.76 10.7 11.52C10.56 11.28 10.69 11.14 10.81 11.02C10.92 10.91 11.06 10.73 11.18 10.59C11.3 10.45 11.34 10.35 11.42 10.19C11.5 10.03 11.46 9.88 11.4 9.76C11.34 9.64 10.84 8.42 10.64 7.92C10.44 7.44 10.23 7.51 10.08 7.5C9.93 7.5 9.75 7.49 9.57 7.49L9.53 7.34Z"/>
                </svg>
                <span>Hubungi Kami</span>
            </a>
        </div>
    </div>
</section>
