<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Rules\ValidCanvasData;
use App\Services\CanvasRenderer;
use Illuminate\Http\Request;

class TemplateBuilderController extends Controller
{
    /**
     * Tampilkan antarmuka visual builder (Super Admin only).
     */
    public function show(Template $template)
    {
        return view('admin.templates.builder', [
            'template' => $template,
            'canvasData' => $template->canvas_data ?? [],
            'allowedComponents' => Template::ALLOWED_COMPONENTS,
            'allowedSlotTypes' => Template::ALLOWED_SLOT_TYPES,
            'allowedEditors' => Template::ALLOWED_EDITORS,
        ]);
    }

    /**
     * Pratinjau visual template builder murni (Super Admin only).
     * Menampilkan struktur template global tanpa konten/postingan kedinasan.
     */
    public function preview(Template $template, CanvasRenderer $renderer)
    {
        $renderedContent = $renderer->renderTemplate($template);

        return view('admin.templates.preview', [
            'template' => $template,
            'renderedContent' => $renderedContent,
        ]);
    }

    /**
     * Simpan payload JSON canvas dari builder.
     */
    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'canvas_data' => ['required', 'array', new ValidCanvasData],
        ]);

        $template->update([
            'canvas_data' => $validated['canvas_data'],
        ]);

        return response()->json([
            'message' => 'Template berhasil disimpan.',
            'template' => $template->only(['id', 'name', 'canvas_data', 'updated_at']),
        ]);
    }
}
