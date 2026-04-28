<?php

return [
    /*
     * Stateful domains for cookie-based SPA auth.
     * Only the explicit frontend origin is trusted — no wildcards.
     */
    'stateful' => [],

    'guard' => ['web'],

    /*
     * Token expiration: 30 days (in minutes).
     * Tokens are also explicitly revoked on logout and on new login.
     */
    'expiration' => 60 * 24 * 30,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies'      => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token'  => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
