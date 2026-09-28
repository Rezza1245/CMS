<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dinas extends Model
{
    protected $table = 'dinas';

    protected $fillable = [
        'name',
        'code',
        'address',
        'contact_email',
        'phone',
    ];

    public function website(): HasOne
    {
        return $this->hasOne(Website::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
