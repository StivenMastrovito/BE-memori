<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageSectionRequest;
use App\Http\Requests\UpdatePageSectionRequest;
use App\Models\Page;
use App\Models\PageSection;

class PageSectionController extends Controller
{
    public function index(Page $page)
    {
        $this->authorize('view', $page);

        return response()->json($page->sections()->orderBy('order')->get());
    }

    public function store(StorePageSectionRequest $request, Page $page)
    {
        $this->authorize('update', $page);
        $this->abortIfLocked($page);

        $order = $request->order ?? ($page->sections()->max('order') + 1);

        $section = $page->sections()->create([
            'feature_addon_id' => null,
            'type' => $request->type,
            'order' => $order,
            'content' => $request->content,
            'price' => 0, // tutte le sezioni sono ora gratuite
            'is_visible' => true,
        ]);

        return response()->json($section->fresh(), 201);
    }

    public function update(UpdatePageSectionRequest $request, Page $page, PageSection $section)
    {
        $this->authorize('update', $page);
        $this->abortIfLocked($page);
        abort_if($section->page_id !== $page->id, 404);

        $section->update($request->validated());

        return response()->json($section);
    }

    public function destroy(Page $page, PageSection $section)
    {
        $this->authorize('update', $page);
        $this->abortIfLocked($page);
        abort_if($section->page_id !== $page->id, 404);

        $section->delete();
        $page->recalculateTotal();

        return response()->json(null, 204);
    }

    private function abortIfLocked(Page $page): void
    {
        abort_if($page->is_published, 422, 'Questa pagina è stata pubblicata e non può più essere modificata.');
    }
}
