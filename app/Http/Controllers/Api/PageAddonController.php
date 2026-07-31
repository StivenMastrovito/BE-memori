<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageAddonRequest;
use App\Models\FeatureAddon;
use App\Models\Page;
use App\Models\PageAddon;

class PageAddonController extends Controller
{
    public function index(Page $page)
    {
        $this->authorize('view', $page);

        return response()->json($page->addons()->with('featureAddon')->get());
    }

    public function store(StorePageAddonRequest $request, Page $page)
    {
        $this->authorize('update', $page);

        abort_if($page->isLocked(), 422, 'Questa pagina è stata pubblicata e non può più essere modificata.');

        $featureAddon = FeatureAddon::where('id', $request->feature_addon_id)
            ->where('category', 'feature')
            ->where('is_active', true)
            ->firstOrFail();

        $alreadyActive = $page->addons()
            ->where('feature_addon_id', $featureAddon->id)
            ->exists();

        if ($alreadyActive) {
            return response()->json(['message' => 'Questa feature è già attiva.'], 409);
        }

        $addon = $page->addons()->create([
            'feature_addon_id' => $featureAddon->id,
            'price' => $featureAddon->price,
        ]);

        $page->recalculateTotal();

        return response()->json($addon->load('featureAddon'), 201);
    }

    public function destroy(Page $page, PageAddon $pageAddon)
    {
        $this->authorize('update', $page);
        abort_if($page->isLocked(), 422, 'Questa pagina è stata pubblicata e non può più essere modificata.');
        abort_if($pageAddon->page_id !== $page->id, 404);

        $pageAddon->delete();
        $page->recalculateTotal();

        return response()->json(null, 204);
    }
}
