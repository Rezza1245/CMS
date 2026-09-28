<?php

namespace App\Services;

use App\Models\Template;
use App\Models\Website;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class CanvasRenderer
{
    /**
     * Peta komponen ke Blade component view.
     */
    protected array $componentViewMap = [
        'Hero' => 'components.builder.hero',
        'Posts Grid' => 'components.builder.posts-grid',
        'Static Content' => 'components.builder.static-content',
        'Media / Document List' => 'components.builder.media-list',
        'Footer' => 'components.builder.footer',
        'Container' => 'components.builder.container',
    ];

    /**
     * Render seluruh canvas template aktif milik website dinas.
     * Mendukung struktur pohon Section -> Container -> Component (TEMPLATE.md v2.2)
     * serta struktur flat list komponen legacy.
     */
    public function render(Website $website): string
    {
        $template = $website->template;
        if (! $template || empty($template->canvas_data) || ! is_array($template->canvas_data)) {
            return '';
        }

        $canvasData = $template->canvas_data;
        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas']) ? $canvasData['canvas'] : $canvasData;

        $html = '';
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (($node['type'] ?? '') === 'section') {
                $sectionId = e($this->sanitizeString($node['id'] ?? 'section'));
                $sectionContent = '';

                foreach ($node['children'] ?? [] as $container) {
                    if (! is_array($container)) {
                        continue;
                    }
                    $containerId = e($this->sanitizeString($container['id'] ?? 'container'));
                    $containerContent = '';

                    foreach ($container['children'] ?? [] as $comp) {
                        if (! is_array($comp)) {
                            continue;
                        }
                        $containerContent .= $this->renderNode($comp, $website);
                    }

                    $sectionContent .= "<div id=\"{$containerId}\" class=\"builder-container w-full\">{$containerContent}</div>";
                }

                $html .= "<div id=\"{$sectionId}\" class=\"builder-section w-full\">{$sectionContent}</div>";
            } else {
                $html .= $this->renderNode($node, $website);
            }
        }

        return $html;
    }

    /**
     * Render kanvas template murni tanpa data dinas (untuk preview Super Admin).
     */
    public function renderTemplate(Template $template): string
    {
        if (empty($template->canvas_data) || ! is_array($template->canvas_data)) {
            return '';
        }

        $canvasData = $template->canvas_data;
        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas']) ? $canvasData['canvas'] : $canvasData;

        $html = '';
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (($node['type'] ?? '') === 'section') {
                $sectionId = e($this->sanitizeString($node['id'] ?? 'section'));
                $sectionContent = '';

                foreach ($node['children'] ?? [] as $container) {
                    if (! is_array($container)) {
                        continue;
                    }
                    $containerId = e($this->sanitizeString($container['id'] ?? 'container'));
                    $containerContent = '';

                    foreach ($container['children'] ?? [] as $comp) {
                        if (! is_array($comp)) {
                            continue;
                        }
                        $containerContent .= $this->renderNode($comp, null);
                    }

                    $sectionContent .= "<div id=\"{$containerId}\" class=\"builder-container w-full\">{$containerContent}</div>";
                }

                $html .= "<div id=\"{$sectionId}\" class=\"builder-section w-full\">{$sectionContent}</div>";
            } else {
                $html .= $this->renderNode($node, null);
            }
        }

        return $html;
    }

    /**
     * Render komponen Footer secara independen (misal untuk layout halaman statis).
     */
    public function renderFooter(Website $website): string
    {
        $template = $website->template;
        if (! $template || empty($template->canvas_data) || ! is_array($template->canvas_data)) {
            return '';
        }

        $canvasData = $template->canvas_data;
        $nodes = isset($canvasData['canvas']) && is_array($canvasData['canvas']) ? $canvasData['canvas'] : $canvasData;

        $footerNode = $this->findComponentInNodes($nodes, 'Footer');
        if (! $footerNode) {
            return '';
        }

        return $this->renderNode($footerNode, $website);
    }

    /**
     * Cari node komponen tertentu dalam struktur kanvas bersarang.
     */
    protected function findComponentInNodes(array $nodes, string $componentName): ?array
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (($node['component'] ?? '') === $componentName) {
                return $node;
            }

            if (! empty($node['children']) && is_array($node['children'])) {
                $found = $this->findComponentInNodes($node['children'], $componentName);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Render satu node komponen menjadi HTML.
     */
    public function renderNode(array $node, ?Website $website = null): string
    {
        $componentName = $node['component'] ?? '';
        $view = $this->componentViewMap[$componentName] ?? null;

        if (! $view || ! View::exists($view)) {
            return '';
        }

        $layoutSettings = $node['layout_settings'] ?? [];
        $slots = $this->resolveSlots($node['slots'] ?? [], $layoutSettings, $website);

        // Jika komponen adalah Container, render seluruh anak di dalamnya
        $childrenHtml = '';
        if ($componentName === 'Container' && ! empty($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $childNode) {
                if (is_array($childNode)) {
                    $childrenHtml .= $this->renderNode($childNode, $website);
                }
            }
        }

        return View::make($view, [
            'id' => $this->sanitizeString($node['id'] ?? Str::random(8)),
            'layout' => $layoutSettings,
            'slots' => $slots,
            'slot' => $childrenHtml,
            'website' => $website,
        ])->render();
    }

    /**
     * Selesaikan resolusi semua slot untuk komponen tertentu.
     */
    protected function resolveSlots(array $slotsDef, array $layoutSettings, ?Website $website = null): array
    {
        $resolved = [];
        foreach ($slotsDef as $slotKey => $slot) {
            $binding = $slot['binding'] ?? '';
            $resolved[$slotKey] = $this->resolveBinding($binding, $slot, $layoutSettings, $website);
        }

        return $resolved;
    }

    /**
     * Petakan binding logis ke model Eloquent aktual tenant secara aman.
     */
    protected function resolveBinding(string $binding, array $slot, array $layoutSettings, ?Website $website = null): mixed
    {
        // 1. Post Binding: selalu filter status 'published' (BR-04)
        if (Str::startsWith($binding, 'posts.')) {
            if (! $website) {
                return collect();
            }

            $limit = (int) ($layoutSettings['limit'] ?? 6);
            if ($limit <= 0) {
                $limit = 6;
            }

            return $website->publishedPosts()
                ->take($limit)
                ->get();
        }

        // 2. Media Binding: berkas media milik website dinas
        if (Str::startsWith($binding, 'media.')) {
            if (! $website) {
                return collect();
            }

            $limit = (int) ($layoutSettings['limit'] ?? 6);
            if ($limit <= 0) {
                $limit = 6;
            }

            return $website->media()
                ->latest()
                ->take($limit)
                ->get();
        }

        $val = null;

        // 3. Pages Binding: konten halaman statis institusi
        if (Str::startsWith($binding, 'pages.')) {
            if ($website) {
                $rest = Str::after($binding, 'pages.');
                if (str_contains($rest, '.')) {
                    [$slug, $field] = explode('.', $rest, 2);
                    $page = $website->publishedPages()->where('slug', $slug)->first();
                } else {
                    $field = $rest;
                    $page = $website->publishedPages()->first();
                }
                $val = $page ? ($page->{$field} ?? $page->content) : null;
            }
        }

        // 4. Appearance Binding: dipetakan ke record appearance milik website
        elseif (Str::startsWith($binding, 'appearance.')) {
            if ($website) {
                $field = Str::after($binding, 'appearance.');
                $appearance = $website->appearance;
                $val = $appearance ? $appearance->{$field} : null;
            }
        }

        // 5. Dinas Binding: dipetakan ke profil dinas milik website
        elseif (Str::startsWith($binding, 'dinas.')) {
            if ($website) {
                $field = Str::after($binding, 'dinas.');
                $dinas = $website->dinas;
                $val = $dinas ? $dinas->{$field} : null;
            }
        }

        // 6. Template Config Binding: dukung override appearance tenant jika diedit dinas
        // ponytail: fallback template_config ke appearance dinas jika admin_dinas mengisi slot
        elseif (Str::startsWith($binding, 'template_config.')) {
            if ($website && $website->appearance) {
                $field = Str::after($binding, 'template_config.');
                $appearanceField = 'footer_' . $field;
                if (! empty($website->appearance->{$appearanceField})) {
                    $val = $website->appearance->{$appearanceField};
                }
            }
        }

        if ($val !== null && trim((string) $val) !== '') {
            return $this->sanitizeString($val);
        }

        return isset($slot['default_value']) ? $this->sanitizeString($slot['default_value']) : null;
    }

    /**
     * Sanitasi data teks untuk mencegah injeksi script / HTML mentah berbahaya.
     */
    public function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Hapus script dan style beserta seluruh isinya
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
        $clean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $clean);

        // Hapus sisa tag HTML mentah
        $clean = strip_tags($clean);

        // Cegah protocol javascript: atau data:
        if (preg_match('/^\s*(javascript|vbscript|data):/i', $clean)) {
            return '';
        }

        return trim($clean);
    }
}
