<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::orderBy('is_premium')->orderBy('name')->get();

        return response()->json($themes);
    }

    public function show(Theme $theme)
    {
        return response()->json($theme);
    }
}