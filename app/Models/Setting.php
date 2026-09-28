<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'website_id',
        'general_config',
        'media_config',
        'privacy_config',
        'system_config',
        'backup_config',
        'storage_config',
    ];

    protected function casts(): array
    {
        return [
            'general_config' => 'array',
            'media_config' => 'array',
            'privacy_config' => 'array',
            'system_config' => 'array',
            'backup_config' => 'array',
            'storage_config' => 'array',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
