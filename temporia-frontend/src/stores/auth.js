import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'
import { tokenStorage } from '@/utils/tokenStorage'
import { useNotification } from '@/composables/useNotification'

export const useAuthStore = defineStore('auth', () => {
  const notify = useNotification()
  const user   = ref(null)
  const token  = ref(tokenStorage.get())

  const isAuthenticated = computed(() => !!token.value)

  async function register(data) {
    const res = await authApi.register(data)
    setSession(res.data)
    notify.success(`Welcome to Temporia, ${res.data.user.name}! 🎉`)
  }

  async function login(data) {
    const res = await authApi.login(data)
    setSession(res.data)
    notify.success(`Welcome back, ${res.data.user.name}!`)
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      // Wipe calendar data on logout — strict user isolation
      try {
        const { useCalendarStore } = await import('@/stores/calendar')
        useCalendarStore().reset()
      } catch { /* store may not be initialized */ }
      clearSession()
    }
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const res  = await authApi.me()
      user.value = res.data
    } catch {
      clearSession()
    }
  }

  function setSession({ user: u, token: t }) {
    user.value  = u
    token.value = t
    tokenStorage.set(t)
  }

  function clearSession() {
    user.value  = null
    token.value = null
    tokenStorage.clear()
  }

  return { user, token, isAuthenticated, register, login, logout, fetchUser }
})
