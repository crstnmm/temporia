import api from './axios'

/**
 * Validates that an ID is a safe positive integer before it is
 * interpolated into a URL path. This prevents CWE-918 (SSRF) via
 * path traversal (e.g. id = "../../admin") or prototype pollution.
 *
 * The backend also validates ownership, but defence-in-depth requires
 * the frontend to never construct malformed URLs.
 */
function safeId(id) {
  const n = parseInt(id, 10)
  if (!Number.isInteger(n) || n <= 0) {
    throw new Error(`Invalid resource ID: ${String(id).slice(0, 20)}`)
  }
  return n
}

export const diaryApi = {
  list:    (params)   => api.get('/diary-entries', { params }),
  create:  (data)     => api.post('/diary-entries', data),
  show:    (id)       => api.get(`/diary-entries/${safeId(id)}`),
  update:  (id, data) => api.put(`/diary-entries/${safeId(id)}`, data),
  destroy: (id)       => api.delete(`/diary-entries/${safeId(id)}`),
}
