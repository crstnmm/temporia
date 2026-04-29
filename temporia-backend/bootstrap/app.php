<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
        \Illuminate\Http\Middleware\HandleCors::class,
        ]);    
    /**
         * Security headers on every response (web + API).
         * Includes CSP, X-Frame-Options, X-XSS-Protection, X-Content-Type-Options,
         * Referrer-Policy, Permissions-Policy, and server header removal.
         */
        $middleware->append(SecurityHeaders::class);

        /**
         * Force JSON Accept header on all requests so Laravel always
         * returns JSON error responses — never HTML stack traces.
         */
        $middleware->append(ForceJsonResponse::class);

        /**
         * Input sanitization on API routes only.
         * Strips HTML tags, null bytes, and control characters from string inputs
         * before they reach any controller.
         */
        $middleware->api(append: [
            SanitizeInput::class,
        ]);

        /**
         * CSRF protection:
         * - Web routes: VerifyCsrfToken is active via Laravel's default web middleware group.
         * - API routes: CSRF is not applicable — all API endpoints are protected by
         *   Sanctum Bearer token authentication (auth:sanctum middleware).
         *   Bearer tokens cannot be attached by cross-origin requests without an
         *   explicit Authorization header, which is blocked by CORS preflight.
         *
         * statefulApi() configures Sanctum's cookie-based SPA auth for the stateful
         * domain list defined in config/sanctum.php.
         */
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * Global exception handler for API routes.
         * Never exposes stack traces, file paths, or internal error details
         * in production — satisfies OWASP A05 (Security Misconfiguration).
         */
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null; // Let Laravel handle non-API exceptions normally
            }

            // Structured validation error response
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // Known HTTP exceptions (403, 404, 405, 429, etc.)
            if ($e instanceof HttpException) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'An error occurred.',
                ], $e->getStatusCode());
            }

            // Unknown exceptions — never leak internals in production
            $status  = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = app()->isProduction()
                ? 'An unexpected error occurred. Please try again later.'
                : $e->getMessage();

            return response()->json(['message' => $message], $status);
        });
    })->create();
