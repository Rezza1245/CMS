<?php

namespace App\Rules;

use App\Models\Template;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validasi struktur JSON canvas_data sesuai TEMPLATE.md (v2.2) Bab 2 & 7.
 *
 * Mendukung hierarki bersarang Section -> Container -> Component:
 *   - Root: array of sections atau object { template_version, canvas: [...] }
 *   - Section: { id, type: 'section', children: [ Container... ] }
 *   - Container: { id, type: 'container', children: [ Component... ] }
 *   - Component: { id, component: ALLOWED_COMPONENTS, layout_settings, slots }
 * Serta mendukung backward compatibility untuk node komponen flat.
 */
class ValidCanvasData implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($data)) {
            $fail('canvas_data harus berupa array atau JSON valid.');

            return;
        }

        $nodes = isset($data['canvas']) && is_array($data['canvas']) ? $data['canvas'] : $data;
        $seenIds = [];

        foreach ($nodes as $index => $node) {
            $prefix = "canvas[{$index}]";

            if (! is_array($node)) {
                $fail("{$prefix}: node harus berupa object.");

                return;
            }

            // 1. Wajib: id
            if (! isset($node['id']) || ! is_string($node['id']) || $node['id'] === '') {
                $fail("{$prefix}: field 'id' wajib berupa string non-kosong.");

                return;
            }

            if (in_array($node['id'], $seenIds, true)) {
                $fail("{$prefix}: id '{$node['id']}' duplikat dalam canvas.");

                return;
            }
            $seenIds[] = $node['id'];

            // 2. Cek apakah node bertipe 'section' (Hierarki bersarang v2.2)
            if (isset($node['type']) && $node['type'] === 'section') {
                if (! isset($node['children']) || ! is_array($node['children'])) {
                    $fail("{$prefix}: section harus memiliki array 'children'.");

                    return;
                }

                foreach ($node['children'] as $cIndex => $container) {
                    $cPrefix = "{$prefix}.children[{$cIndex}]";

                    if (! is_array($container) || ! isset($container['id']) || ! is_string($container['id']) || $container['id'] === '') {
                        $fail("{$cPrefix}: container harus memiliki 'id' non-kosong.");

                        return;
                    }

                    if (in_array($container['id'], $seenIds, true)) {
                        $fail("{$cPrefix}: id '{$container['id']}' duplikat.");

                        return;
                    }
                    $seenIds[] = $container['id'];

                    if (($container['type'] ?? '') !== 'container') {
                        $fail("{$cPrefix}: type harus 'container'.");

                        return;
                    }

                    if (! isset($container['children']) || ! is_array($container['children'])) {
                        $fail("{$cPrefix}: container harus memiliki array 'children'.");

                        return;
                    }

                    foreach ($container['children'] as $compIndex => $comp) {
                        $compPrefix = "{$cPrefix}.children[{$compIndex}]";
                        if (! $this->validateComponentNode($comp, $compPrefix, $seenIds, $fail)) {
                            return;
                        }
                    }
                }

                continue;
            }

            // 3. Fallback: Flat component node (Backward compatibility)
            if (! $this->validateComponentNode($node, $prefix, $seenIds, $fail)) {
                return;
            }
        }
    }

    /**
     * Validasi individual component node.
     */
    protected function validateComponentNode(mixed $node, string $prefix, array &$seenIds, Closure $fail): bool
    {
        if (! is_array($node)) {
            $fail("{$prefix}: node komponen harus berupa object.");

            return false;
        }

        if (! isset($node['id']) || ! is_string($node['id']) || $node['id'] === '') {
            $fail("{$prefix}: field 'id' komponen wajib berupa string non-kosong.");

            return false;
        }

        if (in_array($node['id'], $seenIds, true)) {
            $fail("{$prefix}: id komponen '{$node['id']}' duplikat dalam canvas.");

            return false;
        }
        $seenIds[] = $node['id'];

        if (! isset($node['component']) || ! is_string($node['component'])) {
            $fail("{$prefix}: field 'component' wajib berupa string.");

            return false;
        }

        if (! in_array($node['component'], Template::ALLOWED_COMPONENTS, true)) {
            $fail("{$prefix}: komponen '{$node['component']}' tidak diizinkan. Komponen yang tersedia: "
                .implode(', ', Template::ALLOWED_COMPONENTS).'.');

            return false;
        }

        if (! isset($node['layout_settings']) || ! is_array($node['layout_settings'])) {
            $fail("{$prefix}: field 'layout_settings' wajib berupa object.");

            return false;
        }

        if (isset($node['layout_settings']['columns'])) {
            $cols = $node['layout_settings']['columns'];
            if (! is_numeric($cols) || (int) $cols < 1 || (int) $cols > 4) {
                $fail("{$prefix}.layout_settings.columns harus berupa angka antara 1 dan 4.");

                return false;
            }
        }

        if (isset($node['layout_settings']['limit'])) {
            $limit = $node['layout_settings']['limit'];
            if (! is_numeric($limit) || (int) $limit < 1 || (int) $limit > 50) {
                $fail("{$prefix}.layout_settings.limit harus berupa angka positif antara 1 dan 50.");

                return false;
            }
        }

        if (! isset($node['slots']) || ! is_array($node['slots'])) {
            $fail("{$prefix}: field 'slots' wajib berupa object.");

            return false;
        }

        foreach ($node['slots'] as $slotKey => $slot) {
            $slotPrefix = "{$prefix}.slots.{$slotKey}";

            if (! is_array($slot)) {
                $fail("{$slotPrefix}: slot harus berupa object.");

                return false;
            }

            if (! isset($slot['slot_id']) || ! is_string($slot['slot_id']) || $slot['slot_id'] === '') {
                $fail("{$slotPrefix}: field 'slot_id' wajib berupa string non-kosong.");

                return false;
            }

            if (! isset($slot['binding']) || ! is_string($slot['binding']) || $slot['binding'] === '') {
                $fail("{$slotPrefix}: field 'binding' wajib berupa string non-kosong.");

                return false;
            }

            if (isset($slot['type']) && ! in_array($slot['type'], Template::ALLOWED_SLOT_TYPES, true)) {
                $fail("{$slotPrefix}: type '{$slot['type']}' tidak diizinkan.");

                return false;
            }

            if (isset($slot['editable_by']) && ! in_array($slot['editable_by'], Template::ALLOWED_EDITORS, true)) {
                $fail("{$slotPrefix}: editable_by '{$slot['editable_by']}' tidak diizinkan.");

                return false;
            }
        }

        if ($node['component'] === 'Container' && isset($node['children'])) {
            if (! is_array($node['children'])) {
                $fail("{$prefix}: field 'children' container harus berupa array.");

                return false;
            }

            foreach ($node['children'] as $childIdx => $childNode) {
                if (! $this->validateComponentNode($childNode, "{$prefix}.children[{$childIdx}]", $seenIds, $fail)) {
                    return false;
                }
            }
        }

        return true;
    }
}
