<template>
  <div class="rounded-xl p-3 border" :class="colorClass">
    <div v-if="!editing">
      <div class="flex items-start justify-between gap-2">
        <p class="text-sm font-medium text-slate-800">{{ note.title }}</p>
        <div class="flex gap-1 shrink-0">
          <button @click="startEdit" class="text-slate-400 hover:text-slate-600">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button @click="$emit('delete')" class="text-slate-400 hover:text-rose-500">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
      <p class="text-xs text-slate-600 mt-1 whitespace-pre-wrap">{{ note.body }}</p>
    </div>

    <form v-else @submit.prevent="save" class="space-y-2">
      <input v-model="form.title" class="input text-xs py-1" required />
      <textarea v-model="form.body" class="input text-xs py-1 resize-none" rows="2" required></textarea>
      <div class="flex gap-2">
        <button type="submit" class="btn-primary text-xs px-2 py-1">Save</button>
        <button type="button" @click="editing = false" class="btn-secondary text-xs px-2 py-1">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

const props = defineProps({ note: Object })
const emit  = defineEmits(['update', 'delete'])

const editing = ref(false)
const form    = reactive({ title: '', body: '', color: '' })

const colorMap = {
  indigo:  'bg-indigo-50 border-indigo-200',
  rose:    'bg-rose-50 border-rose-200',
  amber:   'bg-amber-50 border-amber-200',
  emerald: 'bg-emerald-50 border-emerald-200',
}

const colorClass = computed(() => colorMap[props.note.color] || colorMap.indigo)

function startEdit() {
  form.title  = props.note.title
  form.body   = props.note.body
  form.color  = props.note.color
  editing.value = true
}

function save() {
  emit('update', { ...form })
  editing.value = false
}
</script>
