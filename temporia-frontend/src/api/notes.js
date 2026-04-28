import api from './axios'

/**
 * Validates that an ID is a safe positive integer before it is
 * interpolated into a URL path — prevents CWE-918 SSRF via path traversal.
 */
function safeId(id) {
  const n = parseInt(id, 10)
  if (!Number.isInteger(n) || n <= 0) {
    throw new Error(`Invalid resource ID: ${String(id).slice(0, 20)}`)
  }
  return n
}

export const notesApi = {
  list:    (params)   => api.get('/notes', { params }),
  create:  (data)     => api.post('/notes', data),
  show:    (id)       => api.get(`/notes/${safeId(id)}`),
  update:  (id, data) => api.put(`/notes/${safeId(id)}`, data),
  destroy: (id)       => api.delete(`/notes/${safeId(id)}`),
}
