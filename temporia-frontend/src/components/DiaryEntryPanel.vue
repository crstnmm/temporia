<template>
  <div class="space-y-4">

    <!-- ── LOCKED BANNER ─────────────────────────────── -->
    <Transition name="fade-in">
      <div
        v-if="isLocked"
        class="flex items-start gap-3 px-4 py-3.5 bg-amber-50 border border-amber-200 rounded-2xl"
        role="status"
      >
        <span class="text-lg shrink-0 mt-0.5">🔒</span>
        <div>
          <p class="text-sm font-semibold text-amber-800">
            This Temporia entry is locked after the day ends
          </p>
          <p class="text-xs text-amber-600 mt-0.5">
            Entries can only be edited on the day they were written.
          </p>
        </div>
      </div>
    </Transition>

    <!-- ── INLINE ERROR ───────────────────────────────── -->
    <Transition name="fade-in">
      <div
        v-if="inlineError"
        class="flex items-start gap-3 px-4 py-3 bg-rose-50 border border-rose-200 rounded-2xl"
        role="alert"
      >
        <span class="text-base shrink-0">❌</span>
        <p class="text-sm text-rose-700">{{ inlineError }}</p>
      </div>
    </Transition>

    <!-- ── NO ENTRY + PAST DATE ──────────────────────── -->
    <div v-if="!entry && isPastDate" class="text-center py-10 space-y-2">
      <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto">
        <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
      </div>
      <p class="text-sm font-medium text-slate-500">No Temporia entry was written for this day</p>
      <p class="text-xs text-slate-400">Past days cannot have new entries added</p>
    </div>

    <!-- ── FUTURE DATE ────────────────────────────────── -->
    <div v-else-if="!entry && isFutureDate" class="text-center py-10 space-y-2">
      <div class="w-14 h-14 bg-brand-50 rounded-full flex items-center justify-center mx-auto">
        <svg class="w-7 h-7 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <p class="text-sm font-medium text-slate-500">This day hasn't arrived yet</p>
      <p class="text-xs text-slate-400">Come back on {{ formattedDate }} to write your Temporia entry</p>
    </div>

    <!-- ── READ MODE (locked) ─────────────────────────── -->
    <DiaryReadView
      v-else-if="entry && isLocked"
      :entry="entry"
      :locked="true"
      :deleting="deleting"
      @delete="handleDelete"
    />

    <!-- ── READ MODE (today, not editing) ────────────── -->
    <DiaryReadView
      v-else-if="entry && !editing"
      :entry="entry"
      :locked="false"
      :deleting="deleting"
      @edit="startEdit"
      @delete="handleDelete"
    />

    <!-- ── WRITE MODE ─────────────────────────────────── -->
    <form v-else-if="isToday" @submit.prevent="submit" class="space-y-4">
      <div class="flex items-center justify-between">
        <span class="text-xs text-slate-400 font-medium">
          {{ wordCount }} {{ wordCount === 1 ? 'word' : 'words' }}
        </span>
        <span class="text-xs font-semibold text-brand-500 bg-brand-50 px-2.5 py-1 rounded-full">
          Today · {{ formattedDate }}
        </span>
      </div>

      <div class="relative">
        <textarea
          ref="textareaRef"
          v-model="form.content"
          class="input resize-none text-sm leading-relaxed min-h-[180px]"
          :class="{ 'min-h-[240px]': form.content.length > 200 }"
          placeholder="What happened today? How are you feeling? Write freely — this is your Temporia space…"
          maxlength="50000"
          required
          @input="autoGrow"
        ></textarea>
        <span
          v-if="form.content.length > 0"
          class="absolute bottom-3 right-3 text-[10px] text-slate-300 font-medium"
        >
          {{ form.content.length }}
        </span>
      </div>

      <!-- Mood picker -->
      <div class="space-y-2">
        <p class="text-xs font-medium text-slate-500">How are you feeling?</p>
        <div class="flex items-center gap-3 flex-wrap">
          <button
            v-for="m in MOODS" :key="m.value"
            type="button"
            @click="form.mood = form.mood === m.value ? '' : m.value"
            class="flex flex-col items-center gap-1 group transition-all duration-150"
            :title="m.label"
          >
            <span
              class="text-2xl transition-all duration-200"
              :class="form.mood === m.value
                ? 'scale-125 drop-shadow-md'
                : 'opacity-40 group-hover:opacity-80 group-hover:scale-110'"
            >{{ m.emoji }}</span>
            <span
              class="text-[10px] font-medium transition-colors duration-150"
              :class="form.mood === m.value ? 'text-brand-600' : 'text-slate-400'"
            >{{ m.label }}</span>
          </button>
        </div>
      </div>

      <div class="flex items-center gap-2 pt-1">
        <button type="submit" class="btn-primary" :disabled="saving || !form.content.trim()">
          <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
          {{ saving ? 'Saving…' : entry ? 'Update entry' : 'Save entry' }}
        </button>
        <button v-if="entry && editing" type="button" @click="cancelEdit" class="btn-secondary">
          Cancel
        </button>
      </div> 
    </form>

  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue'
import DiaryReadView from './DiaryReadView.vue'

const MOODS = [
  { value: 'happy',   emoji: '😊', label: 'Happy'   },
  { value: 'excited', emoji: '🤩', label: 'Excited' },
  { value: 'neutral', emoji: '😐', label: 'Neutral' },
  { value: 'sad',     emoji: '😢', label: 'Sad'     },
  { value: 'anxious', emoji: '😰', label: 'Anxious' },
]

const props = defineProps({
  date:   { type: String,  required: true },
  entry:  { type: Object,  default: null  },
  saving: { type: Boolean, default: false }, // driven by store busy map
})

const emit = defineEmits(['create-entry', 'update-entry', 'delete-entry'])

const editing     = ref(false)
const deleting    = ref(false)
const inlineError = ref('')
const textareaRef = ref(null)
const form        = reactive({ content: '', mood: '' })

const todayStr     = new Date().toISOString().slice(0, 10)
const isToday      = computed(() => props.date === todayStr)
const isPastDate   = computed(() => props.date < todayStr)
const isFutureDate = computed(() => props.date > todayStr)

const isLocked = computed(() => {
  if (!props.entry) return false
  return props.entry.is_locked ?? isPastDate.value
})

const formattedDate = computed(() =>
  new Date(props.date + 'T00:00:00').toLocaleDateString('default', {
    month: 'long', day: 'numeric', year: 'numeric',
  })
)

const wordCount = computed(() =>
  form.content.trim() ? form.content.trim().split(/\s+/).length : 0
)

watch(() => props.date, () => { editing.value = false; inlineError.value = ''; syncForm() }, { immediate: true })
watch(() => props.entry, (e) => { if (!editing.value) syncForm(); if (e && editing.value) editing.value = false })

function syncForm() {
  form.content = props.entry?.content || ''
  form.mood    = props.entry?.mood    || ''
}

function startEdit() {
  syncForm()
  editing.value    = true
  inlineError.value = ''
  nextTick(() => textareaRef.value?.focus())
}

function cancelEdit() { syncForm(); editing.value = false; inlineError.value = '' }

function autoGrow() {
  const el = textareaRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = el.scrollHeight + 'px'
}

async function submit() {
  if (!form.content.trim()) return
  inlineError.value = ''
  try {
    if (props.entry) {
      emit('update-entry', { id: props.entry.id, data: { content: form.content, mood: form.mood } })
    } else {
      emit('create-entry', { date: props.date, content: form.content, mood: form.mood })
    }
    editing.value = false
  } catch (err) {
    inlineError.value = err.message || 'Failed to save. Please try again.'
  }
}

async function handleDelete() {
  deleting.value = true
  try {
    emit('delete-entry', props.entry.id)
  } finally {
    deleting.value = false
  }
}
</script>

<style scoped>
.fade-in-enter-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-in-enter-from   { opacity: 0; transform: translateY(-6px); }
</style>
