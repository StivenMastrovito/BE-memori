<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureAddon extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'category', 'price', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function pageSections()
    {
        return $this->hasMany(PageSection::class);
    }

    public function pageAddons()
    {
        return $this->hasMany(PageAddon::class);
    }
}
