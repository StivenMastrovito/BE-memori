<?php

namespace Database\Seeders;

use App\Models\FeatureAddon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureAddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            ['key' => 'base_page', 'name' => 'Pubblicazione pagina base', 'category' => 'base', 'price' => 9.99],

            ['key' => 'timeline', 'name' => 'Timeline dei ricordi', 'category' => 'section', 'price' => 4.99],
            ['key' => 'playlist', 'name' => 'Playlist musicale', 'category' => 'section', 'price' => 3.99],
            ['key' => 'countdown', 'name' => 'Countdown', 'category' => 'section', 'price' => 2.99],
            ['key' => 'video', 'name' => 'Sezione video', 'category' => 'section', 'price' => 5.99],
            ['key' => 'letter', 'name' => 'Lettera personalizzata', 'category' => 'section', 'price' => 2.99],

            ['key' => 'password_protection', 'name' => 'Protezione con password', 'category' => 'feature', 'price' => 2.99],
            ['key' => 'qr_code', 'name' => 'QR Code dedicato', 'category' => 'feature', 'price' => 4.99],
            ['key' => 'remove_watermark', 'name' => 'Rimozione watermark', 'category' => 'feature', 'price' => 3.99],
        ];

        foreach ($addons as $addon) {
            FeatureAddon::updateOrCreate(['key' => $addon['key']], $addon);
        }
    }
}
