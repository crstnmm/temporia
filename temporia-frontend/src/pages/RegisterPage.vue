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
        <h2 class="text-xl font-semibold text-slate-800 mb-6">Create your account</h2>

        <form @submit.prevent="submit" class="space-y-4" novalidate>
          <div>
            <label for="reg-name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input
              id="reg-name"
              v-model="form.name"
              type="text"
              class="input"
              placeholder="Your name"
              autocomplete="name"
              maxlength="100"
              required
            />
          </div>
          <div>
            <label for="reg-email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input
              id="reg-email"
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
            <label for="reg-password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input
              id="reg-password"
              v-model="form.password"
              type="password"
              class="input"
              placeholder="Min. 8 characters, mixed case + number"
              autocomplete="new-password"
              maxlength="200"
              required
            />
            <!-- Password strength indicator -->
            <div v-if="form.password" class="mt-1.5 flex gap-1">
              <div
                v-for="i in 4" :key="i"
                class="h-1 flex-1 rounded-full transition-colors duration-300"
                :class="passwordStrength >= i ? strengthColor : 'bg-slate-200'"
              ></div>
            </div>
            <p v-if="form.password && passwordStrength < 3" class="text-xs text-slate-400 mt-1">
              {{ strengthHint }}
            </p>
          </div>
          <div>
            <label for="reg-confirm" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
            <input
              id="reg-confirm"
              v-model="form.password_confirmation"
              type="password"
              class="input"
              placeholder="••••••••"
              autocomplete="new-password"
              maxlength="200"
              required
            />
            <p v-if="form.password_confirmation && form.password !== form.password_confirmation"
              class="text-xs text-rose-500 mt-1">
              Passwords do not match
            </p>
          </div>

          <p v-if="error" role="alert" class="text-rose-600 text-sm">{{ error }}</p>

          <button
            type="submit"
            class="btn-primary w-full"
            :disabled="loading || !canSubmit"
          >
            {{ loading ? 'Creating account…' : 'Create account' }}
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
          Already have an account?
          <RouterLink to="/login" class="text-brand-600 font-medium hover:underline">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const router  = useRouter()
const loading = ref(false)
const error   = ref('')
const form    = reactive({ name: '', email: '', password: '', password_confirmation: '' })

// ── Password strength ──────────────────────────────────
const passwordStrength = computed(() => {
  const p = form.password
  if (!p) return 0
  let score = 0
  if (p.length >= 8)              score++
  if (/[A-Z]/.test(p))            score++
  if (/[0-9]/.test(p))            score++
  if (/[^A-Za-z0-9]/.test(p))     score++
  return score
})

const strengthColor = computed(() => {
  if (passwordStrength.value <= 1) return 'bg-rose-400'
  if (passwordStrength.value === 2) return 'bg-amber-400'
  if (passwordStrength.value === 3) return 'bg-brand-400'
  return 'bg-emerald-500'
})

const strengthHint = computed(() => {
  if (!form.password) return ''
  if (form.password.length < 8)    return 'Use at least 8 characters'
  if (!/[A-Z]/.test(form.password)) return 'Add an uppercase letter'
  if (!/[0-9]/.test(form.password)) return 'Add a number'
  return ''
})

const canSubmit = computed(() =>
  form.name.trim() &&
  form.email.trim() &&
  form.password.length >= 8 &&
  form.password === form.password_confirmation
)

async function submit() {
  if (!canSubmit.value) return
  loading.value = true
  error.value   = ''
  try {
    await auth.register(form)
    router.push('/')
  } catch (e) {
    // e.errors contains field-level validation errors from the API
    error.value = e.errors
      ? Object.values(e.errors).flat()[0]
      : (e.message || 'Registration failed.')
  } finally {
    loading.value = false
  }
}
</script>
