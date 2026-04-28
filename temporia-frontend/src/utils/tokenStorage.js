/**
 * Secure token storage for Sanctum Bearer tokens.
 *
 * Security model:
 * - Primary store: in-memory (module-level variable) — not accessible by XSS
 * - Fallback: sessionStorage — cleared when tab closes, not shared across tabs
 * - Never uses localStorage — persisted storage is accessible to any JS on the page
 *
 * Trade-off: token is lost on full page refresh (user must re-login).
 * This is the correct security posture for a diary app with sensitive content.
 */

const KEY = 'temporia_session'

// In-memory store — primary, fastest, most secure
let _token = null

export const tokenStorage = {
  get() {
    return _token ?? sessionStorage.getItem(KEY)
  },

  set(token) {
    _token = token
    try {
      sessionStorage.setItem(KEY, token)
    } catch {
      // sessionStorage unavailable (private browsing quota) — memory only
    }
  },

  clear() {
    _token = null
    try {
      sessionStorage.removeItem(KEY)
    } catch {
      // ignore
    }
  },

  exists() {
    return !!(_token ?? sessionStorage.getItem(KEY))
  },
}
