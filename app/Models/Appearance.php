<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appearance extends Model
{
    protected $fillable = [
        'website_id',
        'logo',
        'favicon',
        'hero_banner',
        'primary_color',
        'secondary_color',
        'header_slogan',
        'hero_description',
        'footer_slogan',
        'footer_title',
        'footer_about_title',
        'footer_about_text',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
