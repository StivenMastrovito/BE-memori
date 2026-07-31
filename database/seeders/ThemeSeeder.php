<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Theme;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Classico Elegante',
                'slug' => 'classico-elegante',
                'preview_image_url' => null,
                'default_colors' => [
                    'primary' => '#2C2C2C',
                    'secondary' => '#B08D57',
                    'background' => '#FAF9F6',
                    'text' => '#1A1A1A',
                ],
                'default_fonts' => [
                    'heading' => 'Playfair Display',
                    'body' => 'Lato',
                ],
                'is_premium' => false,
            ],
            [
                'name' => 'Romantico',
                'slug' => 'romantico',
                'preview_image_url' => null,
                'default_colors' => [
                    'primary' => '#B76E79',
                    'secondary' => '#F7CAC9',
                    'background' => '#FFF5F5',
                    'text' => '#3D2C2E',
                ],
                'default_fonts' => [
                    'heading' => 'Cormorant Garamond',
                    'body' => 'Nunito',
                ],
                'is_premium' => false,
            ],
            [
                'name' => 'Moderno Minimal',
                'slug' => 'moderno-minimal',
                'preview_image_url' => null,
                'default_colors' => [
                    'primary' => '#111827',
                    'secondary' => '#6366F1',
                    'background' => '#FFFFFF',
                    'text' => '#1F2937',
                ],
                'default_fonts' => [
                    'heading' => 'Inter',
                    'body' => 'Inter',
                ],
                'is_premium' => false,
            ],
            [
                'name' => 'Celebrativo Dorato',
                'slug' => 'celebrativo-dorato',
                'preview_image_url' => null,
                'default_colors' => [
                    'primary' => '#8B6914',
                    'secondary' => '#D4AF37',
                    'background' => '#FFFDF7',
                    'text' => '#2B2109',
                ],
                'default_fonts' => [
                    'heading' => 'Cinzel',
                    'body' => 'Raleway',
                ],
                'is_premium' => true,
            ],
            [
                'name' => 'Malinconico',
                'slug' => 'malinconico',
                'preview_image_url' => null,
                'default_colors' => [
                    'primary' => '#3B4252',
                    'secondary' => '#81A1C1',
                    'background' => '#ECEFF4',
                    'text' => '#2E3440',
                ],
                'default_fonts' => [
                    'heading' => 'Merriweather',
                    'body' => 'Source Sans Pro',
                ],
                'is_premium' => true,
            ],
        ];

        foreach ($themes as $theme) {
            Theme::updateOrCreate(['slug' => $theme['slug']], $theme);
        }
    }
}