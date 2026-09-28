@props([
    'id' => 'template-footer',
    'layout' => [],
    'slots' => [],
    'website' => null,
])

@php
    $columns = (int) ($layout['columns'] ?? 3);
    $footerTitle = $slots['title'] ?? ($website?->appearance?->footer_title ?: ($website?->dinas?->name ?? $website?->name ?? 'Portal Resmi Pemerintah Kota Batu'));
    $slogan = $slots['slogan'] ?? ($website?->appearance?->footer_slogan ?: 'Portal informasi dan layanan kedinasan resmi Pemerintah Kota Batu.');
    $aboutTitle = $slots['about_title'] ?? ($website?->appearance?->footer_about_title ?: 'Pemerintah Kota Batu');
    $aboutText = $slots['about_text'] ?? ($website?->appearance?->footer_about_text ?: 'Portal informasi dan layanan keterbukaan publik terintegrasi di lingkungan Pemerintah Kota Batu.');
    $copyrightOwner = $slots['copyright'] ?? ($website?->dinas?->name ?? 'Pemerintah Kota Batu');
@endphp

<footer id="footer" data-builder-id="{{ e($id) }}" class="builder-footer bg-slate-900 text-slate-300 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 {{ $columns === 2 ? 'md:grid-cols-2' : 'md:grid-cols-3' }} gap-8">
            {{-- Kolom 1: Identitas & Slogan Dinas --}}
            <div>
                <h3 class="text-white font-bold text-base mb-3">{{ $footerTitle }}</h3>
                <p class="text-sm text-slate-400 leading-relaxed mb-4">
                    {{ $slogan }}
                </p>
                <div class="text-xs text-slate-400">
                    Kode Instansi: <span class="font-mono text-slate-300">{{ $website?->dinas?->code ?? '—' }}</span>
                </div>
            </div>

            {{-- Kolom 2: Kontak Resmi Tenant --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">Kontak Resmi</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    @if($website?->dinas?->address)
                        <li class="flex items-start gap-2">
                            <span class="text-slate-400 shrink-0">Alamat:</span>
                            <span>{{ $website->dinas->address }}</span>
                        </li>
                    @endif
                    @if($website?->dinas?->contact_email)
                        <li class="flex items-center gap-2">
                            <span class="text-slate-400 shrink-0">Email:</span>
                            <a href="mailto:{{ e($website->dinas->contact_email) }}" class="text-blue-400 hover:underline">{{ $website->dinas->contact_email }}</a>
                        </li>
                    @endif
                    @if($website?->dinas?->phone)
                        <li class="flex items-center gap-2">
                            <span class="text-slate-400 shrink-0">Telepon:</span>
                            <span>{{ $website->dinas->phone }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Kolom 3: Informasi Tambahan / Visi Kota (Dinamis Slot) --}}
            @if($columns >= 3)
            <div>
                <h4 class="text-white font-semibold text-sm mb-3">{{ $aboutTitle }}</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    {{ $aboutText }}
                </p>
            </div>
            @endif
        </div>

        <div class="mt-12 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
            <p>&copy; {{ date('Y') }} {{ $copyrightOwner }}. Hak Cipta Dilindungi.</p>
            <p>{{ $website?->name ?? 'Pemerintah Kota Batu' }}</p>
        </div>
    </div>
</footer>
