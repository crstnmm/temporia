# Temporia Security Documentation

This document outlines all security measures implemented to pass OWASP ZAP scans with zero vulnerabilities.

---

## Backend Security (Laravel)

### 1. Input Validation & Sanitization

**Middleware:**
- `SanitizeInput` — strips HTML tags, null bytes, and normalizes whitespace from all string inputs before they reach controllers
- Applied globally to all API routes via `bootstrap/app.php`

**Controller Validation:**
- All controllers use Laravel's `validate()` with strict rules:
  - `max:` length limits on all string fields
  - `in:` whitelists for enums (mood, priority, color)
  - `date_format:` strict date/time validation
  - `email:rfc,dns` for email validation
  - `regex:` patterns for name fields (letters, spaces, hyphens only)
  - `Password::min(8)->mixedCase()->numbers()` for password strength

**Query Parameter Validation:**
- All `?month=`, `?year=`, `?date=` params are validated and cast to integers
- Prevents SQL injection via query string manipulation

### 2. SQL Injection Prevention

- **100% Eloquent ORM** — no raw SQL queries anywhere
- All database interactions use parameterized queries via Eloquent
- `whereDate()`, `whereMonth()`, `whereYear()` use bound parameters
- User input never concatenated into SQL strings

### 3. XSS Protection

**Backend:**
- `SanitizeInput` middleware strips all HTML tags from inputs
- Laravel's Blade escaping (not used in API-only mode, but available)
- JSON responses are automatically escaped by Laravel

**Frontend:**
- Vue's `{{ }}` interpolation escapes by default
- `v-html` is never used
- `sanitize.js` utility provides `escapeHtml()` for edge cases
- CSP header blocks inline scripts

### 4. Authentication & Authorization

**Sanctum Token-Based Auth:**
- Tokens expire after 30 days (`config/sanctum.php`)
- Tokens are revoked on logout
- All previous tokens revoked on login (prevents session fixation)
- Bearer tokens sent via `Authorization` header (not cookies)

**Ownership Checks:**
- Every resource controller has `checkOwnership()` method
- Returns 403 Forbidden if `user_id !== auth()->id()`
- Applied before any read/write operation

**Password Security:**
- Bcrypt hashing via Laravel's `Hash` facade
- Minimum 8 characters, mixed case, numbers required
- Constant-time comparison in login to prevent timing attacks
- Generic error messages (no user enumeration)

### 5. Rate Limiting

**Throttle Limiters:**
- `auth` limiter: 5 attempts/minute per IP (login, register)
- `api` limiter: 60 requests/minute per authenticated user
- Custom 429 JSON responses (no HTML leakage)

**Applied via routes:**
```php
Route::middleware('throttle:auth')->group(...) // public
Route::middleware('throttle:api')->group(...)  // protected
```

### 6. Security Headers

**`SecurityHeaders` middleware** sets:
- `Content-Security-Policy` — strict same-origin policy, no inline scripts
- `X-Frame-Options: DENY` — prevents clickjacking
- `X-XSS-Protection: 1; mode=block` — legacy XSS filter
- `X-Content-Type-Options: nosniff` — prevents MIME sniffing
- `Referrer-Policy: strict-origin-when-cross-origin` — limits referrer leakage
- `Permissions-Policy` — disables camera, microphone, geolocation, payment
- Removes `X-Powered-By` and `Server` headers

**HSTS (commented out):**
- `Strict-Transport-Security` ready to enable when TLS is deployed
- Enforces HTTPS for 1 year + subdomains

### 7. CORS Configuration

**Strict CORS (`config/cors.php`):**
- `allowed_origins`: only `FRONTEND_URL` env var (no wildcards)
- `allowed_methods`: explicit list (GET, POST, PUT, PATCH, DELETE, OPTIONS)
- `allowed_headers`: explicit whitelist (no `*`)
- `supports_credentials: false` — token-based auth doesn't need cookies
- `max_age: 86400` — 24h preflight cache

### 8. Error Handling

**Production Exception Handler:**
- Never exposes stack traces when `APP_ENV=production`
- Generic error messages for unknown exceptions
- Structured JSON for validation errors (422)
- HTTP exceptions return only status + safe message
- `ForceJsonResponse` middleware prevents HTML error pages

### 9. Environment Configuration

**`.env.example` production defaults:**
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

---

## Frontend Security (Vue 3)

### 1. Token Storage

**Secure Storage Strategy:**
- **Primary:** in-memory module variable (not accessible to XSS)
- **Fallback:** `sessionStorage` (cleared on tab close, not shared across tabs)
- **Never:** `localStorage` (persisted, accessible to any script)

**Trade-off:**
- Token lost on full page refresh → user must re-login
- This is the correct security posture for a diary app with sensitive content

**Implementation:** `utils/tokenStorage.js`

### 2. XSS Prevention

**Vue Template Safety:**
- All `{{ }}` interpolations are auto-escaped
- `v-html` is never used
- User content never inserted via `innerHTML`

**Sanitization Utilities (`utils/sanitize.js`):**
- `escapeHtml()` — escapes HTML entities for non-Vue contexts
- `sanitizeText()` — normalizes whitespace
- `stripDangerousChars()` — removes null bytes and control characters
- `isValidDate()` — validates date format before API calls

### 3. Axios Client Hardening

**`api/axios.js` security features:**
- 15-second timeout (prevents hanging requests)
- `X-Requested-With: XMLHttpRequest` header (helps Laravel return JSON)
- `withCredentials: false` (no cookies sent cross-origin)
- Error sanitization — strips internal Axios/network details
- 401 → auto-logout + redirect to login
- 429 → user-friendly rate limit message

### 4. Input Validation

**Client-side limits:**
- `maxlength` attributes on all inputs (matches backend limits)
- `autocomplete` hints for password managers
- `novalidate` on forms (custom validation, not browser defaults)
- Password strength indicator (visual feedback)
- Real-time password match check

### 5. Content Security Policy Compliance

**No inline scripts or styles:**
- All JS in `.js` files
- All CSS in `.css` files or `<style scoped>`
- No `eval()`, `Function()`, or `setTimeout(string)`
- External resources only from trusted CDNs (Google Fonts)

### 6. Secure Routing

**Router guards (`router/index.js`):**
- `meta.auth` — requires authentication
- `meta.guest` — redirects authenticated users away from login/register
- Prevents unauthorized access to protected routes

---

## OWASP Top 10 Coverage

| Risk | Mitigation |
|------|------------|
| **A01: Broken Access Control** | Ownership checks in every controller, route-level auth middleware |
| **A02: Cryptographic Failures** | Bcrypt password hashing, token expiration, secure session config |
| **A03: Injection** | 100% Eloquent ORM, strict validation, input sanitization |
| **A04: Insecure Design** | Principle of least privilege, fail-secure defaults |
| **A05: Security Misconfiguration** | Production `.env` defaults, security headers, CORS lockdown |
| **A06: Vulnerable Components** | Latest Laravel 11 + Vue 3, regular `composer update` / `npm update` |
| **A07: Authentication Failures** | Rate limiting, strong passwords, token expiration, no user enumeration |
| **A08: Software & Data Integrity** | Sanctum tokens, CSRF protection (stateful API), SRI for CDN assets |
| **A09: Logging & Monitoring** | Laravel logs (stack channel), error-level logging in production |
| **A10: SSRF** | No user-controlled URLs in backend requests, strict input validation |

---

## Deployment Checklist

Before deploying to production:

1. **Environment:**
   - [ ] Copy `.env.example` to `.env`
   - [ ] Set `APP_ENV=production`
   - [ ] Set `APP_DEBUG=false`
   - [ ] Generate `APP_KEY` via `php artisan key:generate`
   - [ ] Set strong `DB_PASSWORD`
   - [ ] Set `FRONTEND_URL` to actual domain
   - [ ] Set `SANCTUM_STATEFUL_DOMAINS` to actual domain

2. **TLS/HTTPS:**
   - [ ] Obtain SSL certificate (Let's Encrypt recommended)
   - [ ] Uncomment `Strict-Transport-Security` header in `SecurityHeaders.php`
   - [ ] Set `SESSION_SECURE_COOKIE=true` in `.env`

3. **Database:**
   - [ ] Run migrations: `php artisan migrate --force`
   - [ ] Restrict database user permissions (no DROP, CREATE)

4. **Server:**
   - [ ] Disable directory listing
   - [ ] Set proper file permissions (755 dirs, 644 files, 600 `.env`)
   - [ ] Hide `.env` from web access (should be outside webroot)
   - [ ] Configure firewall (allow only 80, 443)

5. **Monitoring:**
   - [ ] Set up log rotation
   - [ ] Configure error alerting
   - [ ] Monitor failed login attempts

6. **Frontend:**
   - [ ] Build for production: `npm run build`
   - [ ] Set `VITE_API_URL` to production API domain
   - [ ] Serve via CDN or static host with HTTPS

---

## Testing

**Run OWASP ZAP scan:**
```bash
# Automated scan
zap-cli quick-scan --self-contained --start-options '-config api.disablekey=true' \
  http://localhost:8000/api

# Full scan with authentication
zap-cli active-scan --recursive \
  -c "Authorization: Bearer YOUR_TOKEN_HERE" \
  http://localhost:8000/api
```

**Expected result:** 0 high/medium vulnerabilities

---

## Maintenance

**Regular updates:**
- `composer update` — monthly
- `npm update` — monthly
- Review Laravel security advisories
- Monitor CVE databases for dependencies

**Audit logs:**
- Review failed login attempts weekly
- Monitor rate limit hits
- Check for unusual API usage patterns

---

## Contact

For security issues, email: security@temporia.example (replace with actual contact)

**Do not** open public GitHub issues for security vulnerabilities.
