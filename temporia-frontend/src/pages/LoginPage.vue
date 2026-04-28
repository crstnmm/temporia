<template>
  <div class="min-h-screen bg-gradient-to-br from-brand-50 to-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-brand-600 rounded-2xl mb-4 shadow-lg">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Temporia</h1>
        <p class="text-slate-500 text-sm mt-1">Your smart diary calendar</p>
      </div>

      <div class="card">
        <h2 class="text-xl font-semibold text-slate-800 mb-6">Welcome back</h2>

        <form @submit.prevent="submit" class="space-y-4" novalidate>
          <div>
            <label for="login-email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input
              id="login-email"
              v-model="form.email"
              type="email"
              class="input"
              placeholder="you@example.com"
              autocomplete="email"
              maxlength="254"
              required
            />
          </div>
          <div>
            <label for="login-password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input
              id="login-password"
              v-model="form.password"
              type="password"
              class="input"
              placeholder="••••••••"
              autocomplete="current-password"
              maxlength="200"
              required
            />
          </div>

          <!-- Error uses {{ }} — Vue escapes by default, no XSS risk -->
          <p v-if="error" role="alert" class="text-rose-600 text-sm">{{ error }}</p>

          <button type="submit" class="btn-primary w-full" :disabled="loading">
            {{ loading ? 'Signing in…' : 'Sign in' }}
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
          No account?
          <RouterLink to="/register" class="text-brand-600 font-medium hover:underline">Create one</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const router  = useRouter()
const loading = ref(false)
const error   = ref('')
const form    = reactive({ email: '', password: '' })

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    await auth.login(form)
    router.push('/')
  } catch (e) {
    // e.message is already sanitized by the axios interceptor
    error.value = e.message || 'Login failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
