<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Fields that intentionally allow longer text — still sanitized, not truncated here.
     * Actual length limits are enforced in controller validation rules.
     */
    private const SKIP_KEYS = ['password', 'password_confirmation'];

    public function handle(Request $request, Closure $next): Response
    {
        $cleaned = $this->sanitize($request->all());
        $request->merge($cleaned);

        return $next($request);
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, self::SKIP_KEYS, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                // Remove null bytes, strip HTML tags, normalize whitespace
                $value      = str_replace("\0", '', $value);
                $value      = strip_tags($value);
                $data[$key] = trim($value);
            }
        }

        return $data;
    }
}
