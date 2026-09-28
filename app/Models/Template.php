<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'canvas_data',
        'template_version',
        'header_structure',
        'post_layout',
        'page_layout',
        'navigation_structure',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'canvas_data' => 'array',
        ];
    }

    /**
     * Daftar komponen bawaan sistem yang diizinkan (TEMPLATE.md Bab 3).
     * Komponen di luar daftar ini dilarang.
     */
    public const ALLOWED_COMPONENTS = [
        'Container',
        'Hero',
        'Posts Grid',
        'Static Content',
        'Media / Document List',
        'Footer',
    ];

    /**
     * Tipe slot yang diizinkan (TEMPLATE.md Bab 4.1).
     */
    public const ALLOWED_SLOT_TYPES = [
        'text',
        'textarea',
        'image',
        'document',
        'dimension',
        'color',
    ];

    /**
     * Pihak yang boleh mengedit slot (TEMPLATE.md Bab 4.1).
     */
    public const ALLOWED_EDITORS = [
        'super_admin',
        'admin_dinas',
    ];

    public function websites()
    {
        return $this->hasMany(Website::class);
    }
}
