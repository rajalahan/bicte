<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\OfficialController;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\AssistantController;

/*
|--------------------------------------------------------------------------
| Public API routes consumed by the kiosk frontend
|--------------------------------------------------------------------------
| These are unauthenticated read endpoints for kiosk display, plus a
| handful of write endpoints (feedback, tokens, assistant.ask) protected
| only by rate limiting.  All admin write operations live under
| /api/admin/* and require Sanctum auth + the "admin" role (Spatie).
*/

Route::middleware('throttle:60,1')->group(function () {

    Route::get('/health', fn () => ['status' => 'ok', 'time' => now()->toIso8601String()]);

    // Read-only catalogue
    Route::get('/officials',                   [OfficialController::class,    'index']);
    Route::get('/departments',                 [DepartmentController::class,  'index']);
    Route::get('/departments/{slug}',          [DepartmentController::class,  'show']);
    Route::get('/notices',                     [NoticeController::class,      'index']);
    Route::get('/notices/{notice}',            [NoticeController::class,      'show']);
    Route::get('/faq',                         [FaqController::class,         'index']);
    Route::get('/achievements',                [AchievementController::class, 'index']);

    // Citizen submissions (rate-limited)
    Route::post('/feedback',                   [FeedbackController::class,    'store']);
    Route::post('/tokens',                     [TokenController::class,       'store']);
    Route::get('/tokens/{token}',              [TokenController::class,       'show']);

    // AI assistant
    Route::post('/assistant/ask',              [AssistantController::class,   'ask']);
});

/*
|--------------------------------------------------------------------------
| Admin (CMS) API routes – Sanctum-protected, role:admin or role:editor
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin|editor'])
    ->group(function () {
        Route::apiResource('officials',    OfficialController::class)->except(['index']);
        Route::apiResource('departments',  DepartmentController::class)->except(['index', 'show']);
        Route::apiResource('notices',      NoticeController::class)->except(['index', 'show']);
        Route::apiResource('faq',          FaqController::class)->except(['index']);
        Route::apiResource('achievements', AchievementController::class)->except(['index']);

        Route::get('/feedback',            [FeedbackController::class, 'adminIndex']);
        Route::patch('/feedback/{id}',     [FeedbackController::class, 'updateStatus']);

        Route::get('/tokens',              [TokenController::class, 'adminIndex']);
        Route::patch('/tokens/{token}',    [TokenController::class, 'updateStatus']);
    });
