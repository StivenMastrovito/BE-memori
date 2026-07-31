<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = $request->user()->media()->latest()->get();

        return response()->json($media);
    }

    public function store(StoreMediaRequest $request)
    {
        $user = $request->user();
        $file = $request->file('file');

        if ($request->page_id) {
            $page = Page::findOrFail($request->page_id);
            $this->authorize('update', $page);
            abort_if($page->isLocked(), 422, 'Questa pagina è stata pubblicata e non può più essere modificata.');
        }


        $type = $this->resolveType($file->getMimeType());

        $path = $file->store('media/' . $user->id, 'r2');

        $media = Media::create([
            'user_id' => $user->id,
            'page_id' => $request->page_id,
            'type' => $type,
            'url' => Storage::disk('r2')->url($path),
            'size_bytes' => $file->getSize(),
        ]);

        return response()->json($media, 201);
    }

    public function destroy(Request $request, Media $media)
    {
        abort_if($media->user_id !== $request->user()->id, 403, 'Non autorizzato.');

        // Ricava il path relativo dall'URL salvato per poterlo eliminare da R2
        $path = $this->extractPathFromUrl($media->url);

        if ($path && Storage::disk('r2')->exists($path)) {
            Storage::disk('r2')->delete($path);
        }

        $media->delete();

        return response()->json(null, 204);
    }

    private function resolveType(string $mimeType): string
    {
        return match (true) {
            Str::startsWith($mimeType, 'image/') => 'image',
            Str::startsWith($mimeType, 'video/') => 'video',
            Str::startsWith($mimeType, 'audio/') => 'audio',
            default => 'unknown',
        };
    }

    private function extractPathFromUrl(string $url): ?string
    {
        $bucket = config('filesystems.disks.r2.bucket');
        $marker = '/' . $bucket . '/';

        if (! str_contains($url, $marker)) {
            return null;
        }

        return Str::after($url, $marker);
    }
}
