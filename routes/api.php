<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\PageAddonController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PageSectionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Api\PublicPageController;
use App\Http\Controllers\Api\FeatureAddonController;
use App\Http\Controllers\Api\ThemeController;


Route::get('/themes', [ThemeController::class, 'index']);
Route::get('/themes/{theme}', [ThemeController::class, 'show']);
Route::get('/feature-addons', [FeatureAddonController::class, 'index']);

Route::get('/p/{slug}', [PublicPageController::class, 'show']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('pages', PageController::class);
    Route::patch('/pages/{page}/password', [PageController::class, 'updatePassword']);
    Route::post('/pages/{page}/checkout', [CheckoutController::class, 'store']);

    Route::get('/pages/{page}/sections', [PageSectionController::class, 'index']);
    Route::post('/pages/{page}/sections', [PageSectionController::class, 'store']);
    Route::patch('/pages/{page}/sections/{section}', [PageSectionController::class, 'update']);
    Route::delete('/pages/{page}/sections/{section}', [PageSectionController::class, 'destroy']);
    Route::get('/pages/{page}/qr-code', [PageController::class, 'qrCode']);
    
    Route::get('/pages/{page}/addons', [PageAddonController::class, 'index']);
    Route::post('/pages/{page}/addons', [PageAddonController::class, 'store']);
    Route::delete('/pages/{page}/addons/{pageAddon}', [PageAddonController::class, 'destroy']);

    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
});
