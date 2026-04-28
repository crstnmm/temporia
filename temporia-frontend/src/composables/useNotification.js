import { ref } from 'vue'

const toasts = ref([])
let nextId = 0

const DURATION = { success: 3000, error: 5000, info: 3500, warning: 4000 }

export function useNotification() {
  function push(type, message, duration) {
    const id = ++nextId
    toasts.value.push({ id, type, message })
    setTimeout(() => dismiss(id), duration ?? DURATION[type] ?? 3000)
    return id
  }

  function dismiss(id) {
    const idx = toasts.value.findIndex((t) => t.id === id)
    if (idx >= 0) toasts.value.splice(idx, 1)
  }

  return {
    toasts,
    dismiss,
    success: (msg, ms)  => push('success', msg, ms),
    error:   (msg, ms)  => push('error',   msg, ms),
    info:    (msg, ms)  => push('info',    msg, ms),
    warning: (msg, ms)  => push('warning', msg, ms),
  }
}
