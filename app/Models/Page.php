<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'user_id',
        'theme_id',
        'slug',
        'title',
        'occasion',
        'custom_colors',
        'custom_fonts',
        'background_pattern',
        'base_price',
        'total_amount',
        'payment_status',
        'paid_at',
        'is_published',
        'published_at',
        'cover_image_url',
        'views_count',
        'password',
        'password_question',
    ];

    protected $casts = [
        'custom_colors' => 'array',
        'custom_fonts' => 'array',
        'base_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function sections()
    {
        return $this->hasMany(PageSection::class)->orderBy('order');
    }

    public function addons()
    {
        return $this->hasMany(PageAddon::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Ricalcola e salva il totale della pagina:
     * base_price + somma sezioni a pagamento + somma addon attivi
     */
    public function recalculateTotal(): float
    {
        $sectionsTotal = $this->sections()->sum('price');
        $addonsTotal = $this->addons()->sum('price');

        $total = (float) $this->base_price + (float) $sectionsTotal + (float) $addonsTotal;

        $this->update(['total_amount' => $total]);

        return $total;
    }

    public function isLocked(): bool
    {
        return (bool) $this->is_published;
    }
}
