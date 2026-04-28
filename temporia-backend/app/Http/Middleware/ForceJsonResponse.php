<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip OPTIONS preflight requests — the browser sends these before POST/PUT
        // to check CORS headers. Overwriting Accept on OPTIONS breaks the preflight
        // response and causes the browser to block the actual request with a network error.
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        // Force JSON Accept header so Laravel always returns JSON error responses,
        // never HTML stack traces or redirect pages.
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
