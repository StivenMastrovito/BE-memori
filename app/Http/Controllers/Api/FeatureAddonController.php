<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureAddon;

class FeatureAddonController extends Controller
{
    public function index()
    {
        $addons = FeatureAddon::where('is_active', true)
            ->where('category', 'feature') // esclude "base" e le vecchie "section"
            ->orderBy('name')
            ->get();

        return response()->json($addons);
    }
}
