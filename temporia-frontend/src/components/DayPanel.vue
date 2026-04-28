<template>
  <div class="space-y-4">
    <!-- Date heading -->
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-semibold text-slate-700">
        {{ formattedDate }}
      </h3>
    </div>

    <!-- Diary Entry Card -->
    <div class="card p-4">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-brand-600 uppercase tracking-wide">Diary</span>
        <button v-if="entry && !editingEntry" @click="editingEntry = true" class="text-xs text-slate-400 hover:text-brand-600">Edit</button>
      </div>

      <!-- View mode -->
      <div v-if="entry && !editingEntry">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-sm font-medium text-slate-800">{{ entry.title }}</span>
          <span v-if="entry.mood" class="text-base">{{ moodEmoji(entry.mood) }}</span>
        </div>
        <p class="text-sm text-slate-600 whitespace-pre-wrap leading-relaxed">{{ entry.content }}</p>
        <button @click="$emit('delete-entry', entry.id)" class="mt-3 text-xs text-rose-400 hover:text-rose-600">Delete entry</button>
      </div>

      <!-- Edit / Create mode -->
      <form v-else @submit.prevent="submitEntry" class="space-y-3">
        <input v-model="entryForm.title" class="input text-sm" placeholder="Entry title" required />
        <textarea v-model="entryForm.content" class="input text-sm resize-none" rows="4" placeholder="Write your thoughts…" required></textarea>
        <div class="flex items-center gap-2">
          <label class="text-xs text-slate-500">Mood:</label>
          <select v-model="entryForm.mood" class="input text-xs py-1 w-auto">
            <option value="">—</option>
            <option v-for="m in moods" :key="m.value" :value="m.value">{{ m.emoji }} {{ m.label }}</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="btn-primary text-xs px-3 py-1.5" :disabled="saving">
            {{ saving ? 'Saving…' : 'Save' }}
          </button>
          <button v-if="entry" type="button" @click="cancelEdit" class="btn-secondary text-xs px-3 py-1.5">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Notes Card -->
    <div class="card p-4">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Notes</span>
        <button @click="showNoteForm = !showNoteForm" class="text-xs text-slate-400 hover:text-amber-600">
          {{ showNoteForm ? 'Cancel' : '+ Add' }}
        </button>
      </div>

      <!-- New note form -->
      <form v-if="showNoteForm" @submit.prevent="submitNote" class="space-y-2 mb-4 pb-4 border-b border-slate-100">
        <input v-model="noteForm.title" class="input text-sm" placeholder="Note title" required />
        <textarea v-model="noteForm.body" class="input text-sm resize-none" rows="3" placeholder="Note content…" required></textarea>
        <div class="flex items-center gap-2">
          <label class="text-xs text-slate-500">Color:</label>
          <div class="flex gap-1.5">
            <button
              v-for="c in colors" :key="c.value" type="button"
              @click="noteForm.color = c.value"
              class="w-5 h-5 rounded-full border-2 transition-all"
              :class="[c.bg, noteForm.color === c.value ? 'border-slate-600 scale-110' : 'border-transparent']"
            ></button>
          </div>
        </div>
        <button type="submit" class="btn-primary text-xs px-3 py-1.5" :disabled="savingNote">
          {{ savingNote ? 'Saving…' : 'Add note' }}
        </button>
      </form>

      <!-- Notes list -->
      <div v-if="notes.length" class="space-y-2">
        <NoteItem
          v-for="note in notes"
          :key="note.id"
          :note="note"
          @update="(data) => $emit('update-note', { id: note.id, data })"
          @delete="$emit('delete-note', note.id)"
        />
      </div>
      <p v-else-if="!showNoteForm" class="text-xs text-slate-400 text-center py-2">No notes for this day</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue'
import NoteItem from './NoteItem.vue'

const props = defineProps({
  date:  String,
  entry: Object,
  notes: Array,
})

const emit = defineEmits(['save-entry', 'delete-entry', 'save-note', 'update-note', 'delete-note'])

const moods = [
  { value: 'happy',   emoji: '😊', label: 'Happy' },
  { value: 'sad',     emoji: '😢', label: 'Sad' },
  { value: 'neutral', emoji: '😐', label: 'Neutral' },
  { value: 'excited', emoji: '🤩', label: 'Excited' },
  { value: 'anxious', emoji: '😰', label: 'Anxious' },
]

const colors = [
  { value: 'indigo',  bg: 'bg-indigo-400' },
  { value: 'rose',    bg: 'bg-rose-400' },
  { value: 'amber',   bg: 'bg-amber-400' },
  { value: 'emerald', bg: 'bg-emerald-400' },
]

const moodEmoji = (val) => moods.find((m) => m.value === val)?.emoji || ''

const editingEntry = ref(false)
const saving       = ref(false)
const showNoteForm = ref(false)
const savingNote   = ref(false)

const entryForm = reactive({ title: '', content: '', mood: '' })
const noteForm  = reactive({ title: '', body: '', color: 'indigo' })

const formattedDate = computed(() => {
  if (!props.date) return ''
  return new Date(props.date + 'T00:00:00').toLocaleDateString('default', {
    weekday: 'long', month: 'long', day: 'numeric',
  })
})

watch(() => props.date, () => {
  editingEntry.value = false
  showNoteForm.value = false
  resetEntryForm()
}, { immediate: true })

watch(() => props.entry, (e) => {
  if (e) {
    entryForm.title   = e.title
    entryForm.content = e.content
    entryForm.mood    = e.mood || ''
  } else {
    resetEntryForm()
    editingEntry.value = false
  }
}, { immediate: true })

function resetEntryForm() {
  entryForm.title   = ''
  entryForm.content = ''
  entryForm.mood    = ''
}

function cancelEdit() {
  editingEntry.value = false
  if (props.entry) {
    entryForm.title   = props.entry.title
    entryForm.content = props.entry.content
    entryForm.mood    = props.entry.mood || ''
  }
}

async function submitEntry() {
  saving.value = true
  await emit('save-entry', { ...entryForm, entry_date: props.date })
  editingEntry.value = false
  saving.value = false
}

async function submitNote() {
  savingNote.value = true
  await emit('save-note', { ...noteForm, note_date: props.date })
  noteForm.title = ''
  noteForm.body  = ''
  noteForm.color = 'indigo'
  showNoteForm.value = false
  savingNote.value   = false
}
</script>
