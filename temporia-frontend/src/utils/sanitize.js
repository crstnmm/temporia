/**
 * Escapes HTML special characters to prevent XSS when content
 * is used outside of Vue's default text interpolation.
 * Vue {{ }} binding already escapes — use this only for non-Vue contexts.
 */
export function escapeHtml(str) {
  if (typeof str !== 'string') return ''
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
}

/**
 * Strips leading/trailing whitespace and collapses internal whitespace runs.
 */
export function sanitizeText(str) {
  if (typeof str !== 'string') return ''
  return str.trim().replace(/\s+/g, ' ')
}

/**
 * Validates a YYYY-MM-DD date string before sending to the API.
 */
export function isValidDate(str) {
  return /^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/.test(str)
}

/**
 * Strips null bytes and control characters from a string.
 */
export function stripDangerousChars(str) {
  if (typeof str !== 'string') return ''
  // eslint-disable-next-line no-control-regex
  return str.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '')
}
