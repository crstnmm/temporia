<?php

/**
 * API Routes — Temporia
 *
 * CSRF strategy:
 * All routes here are protected via Laravel Sanctum Bearer token authentication
 * (stateless, token-based). CSRF tokens are not applicable to Bearer-token APIs
 * because the browser cannot attach Authorization headers cross-origin without
 * an explicit CORS preflight — which is enforced in config/cors.php.
 *
 * The VerifyCsrfToken middleware is scoped to web routes only (bootstrap/app.php).
 * API routes use auth:sanctum which validates the Bearer token on every request.
 *
 * Reference: https://laravel.com/docs/sanctum#api-token-authentication
 */

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiaryEntryController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

// ── Rate limiter definitions ───────────────────────────────────────────────────

/**
 * 'auth' limiter — 5 attempts per minute per IP address.
 * Applied to login and register to prevent brute-force and credential stuffing.
 */
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)
        ->by($request->ip())
        ->response(function () {
            return response()->json([
                'message' => 'Too many attempts. Please wait before trying again.',
            ], 429);
        });
});

/**
 * 'api' limiter — 60 requests per minute per authenticated user (fallback: IP).
 * Prevents API abuse from authenticated sessions.
 */
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)
        ->by($request->user()?->id ?: $request->ip())
        ->response(function () {
            return response()->json([
                'message' => 'Request limit exceeded. Please slow down.',
            ], 429);
        });
});

// ── Public routes — throttled by IP ───────────────────────────────────────────
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ── Protected routes — Sanctum Bearer token + API rate limit ──────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::apiResource('diary-entries', DiaryEntryController::class);
    Route::apiResource('notes',         NoteController::class);
    Route::apiResource('alerts',        AlertController::class)->except(['show']);
});
