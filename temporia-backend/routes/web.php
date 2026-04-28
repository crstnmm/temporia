<?php

use Illuminate\Support\Facades\Route;

/**
 * Web Routes — Temporia
 *
 * CSRF protection is active on all web routes via the global
 * VerifyCsrfToken middleware registered in bootstrap/app.php.
 *
 * The root route returns a minimal health-check response.
 * It does not expose version numbers or internal details.
 */
Route::get('/', fn () => response()->json(['status' => 'ok']));
