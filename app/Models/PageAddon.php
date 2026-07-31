<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageAddon extends Model
{
    protected $fillable = [
        'page_id',
        'feature_addon_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function featureAddon()
    {
        return $this->belongsTo(FeatureAddon::class);
    }
}
