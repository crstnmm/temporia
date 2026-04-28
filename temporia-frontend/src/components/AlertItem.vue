<template>
  <div
    class="rounded-2xl p-4 border transition-all duration-200"
    :class="priorityClass"
  >
    <div v-if="!editing">
      <div class="flex items-start justify-between gap-2">
        <div class="flex items-center gap-2 min-w-0">
          <span class="text-base shrink-0">{{ priorityIcon }}</span>
          <p class="text-sm font-semibold text-slate-800 truncate">{{ alert.title }}</p>
        </div>
        <div class="flex gap-1 shrink-0">
          <button @click="startEdit" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-white/60 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button @click="$emit('delete')" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-white/60 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>

      <div class="flex items-center gap-3 mt-2">
        <span v-if="alert.alert_time" class="flex items-center gap-1 text-xs text-slate-500">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ alert.alert_time }}
        </span>
        <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="badgeClass">
          {{ alert.priority }}
        </span>
      </div>

      <p v-if="alert.description" class="text-xs text-slate-500 mt-2 leading-relaxed">
        {{ alert.description }}
      </p>
    </div>

    <!-- Edit form -->
    <form v-else @submit.prevent="save" class="space-y-2">
      <input v-model="form.title" class="input text-sm" required />
      <input v-model="form.alert_time" type="time" class="input text-sm" />
      <select v-model="form.priority" class="input text-sm">
        <option value="low">🟢 Low</option>
        <option value="medium">🟡 Medium</option>
        <option value="high">🔴 High</option>
      </select>
      <textarea v-model="form.description" class="input text-sm resize-none" rows="2"></textarea>
      <div class="flex gap-2">
        <button type="submit" class="btn-primary text-xs px-3 py-1.5">Save</button>
        <button type="button" @click="editing = false" class="btn-secondary text-xs px-3 py-1.5">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

const props = defineProps({ alert: Object })
const emit  = defineEmits(['update', 'delete'])

const editing = ref(false)
const form    = reactive({ title: '', alert_time: '', priority: 'medium', description: '' })

const PRIORITY = {
  low:    { card: 'bg-emerald-50 border-emerald-200', badge: 'bg-emerald-100 text-emerald-700', icon: '🟢' },
  medium: { card: 'bg-amber-50 border-amber-200',    badge: 'bg-amber-100 text-amber-700',    icon: '🟡' },
  high:   { card: 'bg-rose-50 border-rose-200',      badge: 'bg-rose-100 text-rose-700',      icon: '🔴' },
}

const priorityClass = computed(() => PRIORITY[props.alert.priority]?.card || PRIORITY.medium.card)
const badgeClass    = computed(() => PRIORITY[props.alert.priority]?.badge || PRIORITY.medium.badge)
const priorityIcon  = computed(() => PRIORITY[props.alert.priority]?.icon || '🟡')

function startEdit() {
  form.title       = props.alert.title
  form.alert_time  = props.alert.alert_time || ''
  form.priority    = props.alert.priority
  form.description = props.alert.description || ''
  editing.value    = true
}

function save() {
  emit('update', { ...form })
  editing.value = false
}
</script>
