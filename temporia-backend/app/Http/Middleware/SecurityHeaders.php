<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Headers applied to every response.
     * CSP is intentionally strict: only same-origin + trusted CDNs used by the app.
     */
    private $headers = [
        // Prevent clickjacking
        'X-Frame-Options'           => 'DENY',

        // Legacy XSS filter (IE/old Chrome) — modern browsers use CSP instead
        'X-XSS-Protection'          => '1; mode=block',

        // Prevent MIME-type sniffing
        'X-Content-Type-Options'    => 'nosniff',

        // Limit referrer information leakage
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',

        // Disable browser features not needed by the app
        'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=(), payment=()',

        // Force HTTPS for 1 year (enable only when TLS is confirmed)
        // 'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',

        // Content Security Policy — built dynamically so adding a new
        // frontend origin only requires an env variable change, not a code change.
       
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $csp = implode('; ', array_filter([
    "default-src 'self'",
    "script-src 'self'",
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
    "font-src 'self' https://fonts.gstatic.com",
    "img-src 'self' data:",
    'connect-src ' . implode(' ', array_unique(array_filter([
        "'self'",
        env('APP_URL', 'http://localhost:8000'),
        env('FRONTEND_URL', 'http://localhost:5173'),
        env('FRONTEND_PREVIEW_URL', 'http://localhost:4173'),
    ]))),
    "frame-ancestors 'none'",
    "base-uri 'self'",
    "form-action 'self'",
    "object-src 'none'",
]));
        foreach ($this->headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        $response->headers->set('Content-Security-Policy', $csp);
        // Remove headers that leak server information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
