<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    // Sezioni sempre incluse gratis nella pagina base
    public const FREE_TYPES = ['text', 'photo', 'song'];

    protected $fillable = [
        'page_id', 'feature_addon_id', 'type', 'order', 'content', 'price', 'is_visible',
    ];

    protected $casts = [
        'content' => 'array',
        'price' => 'decimal:2',
        'is_visible' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function featureAddon()
    {
        return $this->belongsTo(FeatureAddon::class);
    }

    public function isFree(): bool
    {
        return in_array($this->type, self::FREE_TYPES, true);
    }
}
