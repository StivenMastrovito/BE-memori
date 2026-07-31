<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\FeatureAddon;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\UpdatePagePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;


class PageController extends Controller
{
    public function index(Request $request)
    {
        $pages = $request->user()
            ->pages()
            ->with('theme')
            ->latest()
            ->get();

        return response()->json($pages);
    }

    public function store(StorePageRequest $request)
    {
        $this->authorize('create', Page::class);

        $basePrice = FeatureAddon::where('key', 'base_page')
            ->where('is_active', true)
            ->value('price') ?? 0;

        $page = $request->user()->pages()->create([
            'theme_id' => $request->theme_id,
            'title' => $request->title,
            'occasion' => $request->occasion,
            'custom_colors' => $request->custom_colors,
            'custom_fonts' => $request->custom_fonts,
            'slug' => $this->generateUniqueSlug($request->title),
            'base_price' => $basePrice,
            'total_amount' => $basePrice,
        ]);

        return response()->json($page->load('theme'), 201);
    }

    public function show(Request $request, Page $page)
    {
        $this->authorize('view', $page);

        return response()->json(
            $page->load(['theme', 'sections' => fn($q) => $q->orderBy('order'), 'addons.featureAddon'])
        );
    }

    public function update(UpdatePageRequest $request, Page $page)
    {
        $this->authorize('update', $page);

        abort_if($page->isLocked(), 422, 'Questa pagina è stata pubblicata e non può più essere modificata.');

        $page->update($request->validated());

        return response()->json($page->fresh('theme'));
    }

    public function destroy(Request $request, Page $page)
    {
        $this->authorize('delete', $page);

        $page->delete();

        return response()->json(null, 204);
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;

        while (Page::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . Str::lower(Str::random(5));
        }

        return $slug;
    }

    public function updatePassword(UpdatePagePasswordRequest $request, Page $page)
    {
        $this->authorize('update', $page);

        abort_if($page->isLocked(), 422, 'Questa pagina è stata pubblicata e non può più essere modificata.');

        $hasPasswordProtection = $page->addons()
            ->whereHas('featureAddon', fn($q) => $q->where('key', 'password_protection'))
            ->exists();

        if (! $hasPasswordProtection) {
            return response()->json([
                'message' => 'Devi prima attivare la feature "Protezione con password" su questa pagina.',
            ], 422);
        }

        $page->update([
            'password' => Hash::make($request->password),
            'password_question' => $request->password_question,
        ]);

        return response()->json(['message' => 'Password impostata correttamente.']);
    }

    public function qrCode(Request $request, Page $page)
    {
        $this->authorize('view', $page);

        $hasQrCode = $page->addons()
            ->whereHas('featureAddon', fn($q) => $q->where('key', 'qr_code'))
            ->exists();

        if (! $hasQrCode) {
            return response()->json([
                'message' => 'Devi prima attivare la feature "QR Code dedicato" su questa pagina.',
            ], 422);
        }

        $pageUrl = rtrim(config('services.frontend_url'), '/') . '/p/' . $page->slug;

        $qrCode = new QrCode($pageUrl);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return response($result->getString(), 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qr-code-' . $page->slug . '.png"');
    }
}
