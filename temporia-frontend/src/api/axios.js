import axios from 'axios'
import { tokenStorage } from '@/utils/tokenStorage'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'https://temporia-production-0576.up.railway.app/api',
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: false,
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const token = tokenStorage.get()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      tokenStorage.clear()
      window.location.replace('/login')
      return new Promise(() => {})
    }

    if (status === 429) {
      return Promise.reject(new Error('Too many requests. Please slow down.'))
    }

    return Promise.reject(sanitizeError(error))
  }
)

// Error sanitizer
function sanitizeError(error) {
  const status = error.response?.status
  const data = error.response?.data

  const message =
    data?.message ||
    data?.error ||
    (status
      ? `Request failed with status ${status}`
      : 'Network error. Please check your connection.')

  const safe = new Error(message)
  safe.status = status
  safe.errors = data?.errors || null
  safe.isApiError = true

  return safe
}

export default api