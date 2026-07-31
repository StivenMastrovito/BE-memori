<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PublicPageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'theme',
                'sections' => fn($q) => $q->where('is_visible', true)->orderBy('order'),
                'addons.featureAddon',
            ])
            ->firstOrFail();

        $hasPasswordProtection = $page->addons
            ->contains(fn($addon) => $addon->featureAddon->key === 'password_protection');

        if ($hasPasswordProtection) {
            $providedPassword = $request->header('X-Page-Password') ?? $request->query('password');

            if (! $providedPassword || ! Hash::check($providedPassword, $page->password)) {
                return response()->json([
                    'requires_password' => true,
                    'password_question' => $page->password_question,
                    'message' => 'Questa pagina è protetta da password.',
                ], 401);
            }
        }

        // Il conteggio avviene sempre, indipendentemente dal pagamento
        $page->increment('views_count');

        $hasAnalytics = $page->addons
            ->contains(fn($addon) => $addon->featureAddon->key === 'analytics');

        $payload = [
            'title' => $page->title,
            'occasion' => $page->occasion,
            'theme' => $page->theme,
            'background_pattern' => $page->background_pattern,
            'custom_colors' => $page->custom_colors,
            'custom_fonts' => $page->custom_fonts,
            'cover_image_url' => $page->cover_image_url,
            'sections' => $page->sections,
            'has_qr_code' => $page->addons->contains(fn($a) => $a->featureAddon->key === 'qr_code'),
            'removes_watermark' => $page->addons->contains(fn($a) => $a->featureAddon->key === 'remove_watermark'),
        ];

        // views_count visibile solo se l'utente ha pagato per le statistiche
        if ($hasAnalytics) {
            $payload['views_count'] = $page->views_count;
        }

        return response()->json($payload);
    }
}
