import axios from 'axios'
import { tokenStorage } from '@/utils/tokenStorage'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: 15000, // 15s — prevent hanging requests
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    // Tell the server this is an XHR request — helps Laravel return JSON errors
    'X-Requested-With': 'XMLHttpRequest',
  },
  // Never send cookies cross-origin for token-based auth
  withCredentials: false,
})

// ── Request interceptor ────────────────────────────────
api.interceptors.request.use(
  (config) => {
    const token = tokenStorage.get()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(sanitizeError(error))
)

// ── Response interceptor ───────────────────────────────
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      tokenStorage.clear()
      // Use replace so the login page isn't in browser history
      window.location.replace('/login')
      return new Promise(() => {}) // prevent further error handling
    }

    if (status === 429) {
      return Promise.reject(new Error('Too many requests. Please slow down.'))
    }

    return Promise.reject(sanitizeError(error))
  }
)

/**
 * Strips internal Axios/network details from errors before they
 * reach UI components — prevents accidental stack trace exposure.
 */
function sanitizeError(error) {
  const status  = error.response?.status
  const data    = error.response?.data

  // Return a plain Error with only the safe server message
  const message = data?.message
    || data?.error
    || (status ? `Request failed with status ${status}` : 'Network error. Please check your connection.')

  const safe      = new Error(message)
  safe.status     = status
  safe.errors     = data?.errors || null  // validation field errors only
  safe.isApiError = true
  return safe
}

export default api
