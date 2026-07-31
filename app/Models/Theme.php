<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name', 'slug', 'preview_image_url', 'default_colors', 'default_fonts', 'is_premium',
    ];

    protected $casts = [
        'default_colors' => 'array',
        'default_fonts' => 'array',
        'is_premium' => 'boolean',
    ];

    public function pages()
    {
        return $this->hasMany(Page::class);
    }
}
