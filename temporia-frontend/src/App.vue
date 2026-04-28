<template>
  <div>
    <!-- Global loading gate — prevents flash of login page on refresh -->
    <div
      v-if="initializing"
      class="min-h-screen bg-gradient-to-br from-brand-50 to-slate-100 flex items-center justify-center"
    >
      <div class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl flex items-center justify-center shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <div class="flex gap-1.5">
          <span v-for="i in 3" :key="i"
            class="w-2 h-2 rounded-full bg-brand-400 animate-bounce"
            :style="{ animationDelay: `${(i - 1) * 0.15}s` }"
          ></span>
        </div>
        <p class="text-sm font-medium text-slate-500 tracking-wide">Temporia</p>
      </div>
    </div>

    <RouterView v-else />

    <!-- Global toast notifications -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import ToastContainer from '@/components/ToastContainer.vue'


const activeView = ref('all')

function setView(view) {
  activeView.value = view
}
const auth        = useAuthStore()
const initializing = ref(true)

onMounted(async () => {
  // Restore user from token before rendering any route
  await auth.fetchUser()
  initializing.value = false
})
</script>
