<template>
  <Teleport to="body">
    <div
      aria-live="polite"
      aria-atomic="false"
      class="fixed bottom-5 right-4 z-[100] flex flex-col gap-2 w-full max-w-sm pointer-events-none"
    >
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-2xl shadow-lg border text-sm font-medium backdrop-blur-sm"
          :class="styleMap[toast.type]"
          role="alert"
        >
          <span class="text-base shrink-0 mt-0.5">{{ iconMap[toast.type] }}</span>
          <span class="flex-1 leading-snug">{{ toast.message }}</span>
          <button
            @click="dismiss(toast.id)"
            class="shrink-0 opacity-50 hover:opacity-100 transition-opacity mt-0.5"
            aria-label="Dismiss"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useNotification } from '@/composables/useNotification'

const { toasts, dismiss } = useNotification()

const iconMap = {
  success: '✅',
  error:   '❌',
  info:    'ℹ️',
  warning: '⚠️',
}

const styleMap = {
  success: 'bg-emerald-50/95 border-emerald-200 text-emerald-800',
  error:   'bg-rose-50/95 border-rose-200 text-rose-800',
  info:    'bg-brand-50/95 border-brand-200 text-brand-800',
  warning: 'bg-amber-50/95 border-amber-200 text-amber-800',
}
</script>

<style scoped>
.toast-enter-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.2s ease; }
.toast-enter-from   { opacity: 0; transform: translateX(40px) scale(0.95); }
.toast-leave-to     { opacity: 0; transform: translateX(40px) scale(0.95); }
.toast-move         { transition: transform 0.25s ease; }
</style>
