<template>
  <Teleport to="body">
    <Transition name="backdrop">
      <div
        v-if="open"
        class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm"
        @click.self="$emit('close')"
      ></div>
    </Transition>

    <Transition name="slide-up">
      <div
        v-if="open"
        class="fixed inset-x-0 bottom-0 z-50 sm:inset-0 sm:flex sm:items-center sm:justify-center sm:p-4"
      >
        <div class="bg-white w-full sm:max-w-lg sm:rounded-3xl rounded-t-3xl shadow-2xl flex flex-col max-h-[90vh] sm:max-h-[85vh]">

          <!-- ── Modal Header ──────────────────────────── -->
          <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-slate-100 shrink-0">
            <div>
              <p class="text-xs font-semibold text-brand-500 uppercase tracking-widest mb-0.5">
                {{ weekday }}
              </p>
              <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ formattedDate }}</h3>
            </div>
            <button
              @click="$emit('close')"
              class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all mt-0.5"
              aria-label="Close Temporia day panel"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- ── Tabs ─────────────────────────────────── -->
          <div class="flex gap-1 px-6 pt-3 pb-0 shrink-0">
            <button
              v-for="tab in TABS"
              :key="tab.key"
              @click="activeTab = tab.key"
              class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-medium transition-all duration-200"
              :class="activeTab === tab.key
                ? 'bg-brand-600 text-white shadow-sm shadow-brand-200'
                : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'"
            >
              <span>{{ tab.icon }}</span>
              <span>{{ tab.label }}</span>
              <span
                v-if="badgeCount(tab.key) > 0"
                class="text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none"
                :class="activeTab === tab.key ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-600'"
              >
                {{ badgeCount(tab.key) }}
              </span>
            </button>
          </div>

          <!-- ── Tab Content ───────────────────────────── -->
          <div class="flex-1 overflow-y-auto px-6 py-4">
            <Transition name="tab-fade" mode="out-in">

              <!-- DIARY TAB -->
              <div v-if="activeTab === 'diary'" key="diary">
                <DiaryEntryPanel
                  :date="date"
                  :entry="entry"
                  :saving="!!busy['create-entry'] || !!(entry && busy[`update-entry-${entry.id}`])"
                  @create-entry="(data)    => $emit('create-entry', data)"
                  @update-entry="(payload) => $emit('update-entry', payload)"
                  @delete-entry="(id)      => $emit('delete-entry', id)"
                />
              </div>

              <!-- NOTES TAB -->
              <div v-else-if="activeTab === 'notes'" key="notes" class="space-y-3">
                <button
                  @click="showNoteForm = !showNoteForm"
                  class="w-full flex items-center justify-center gap-2 py-2.5 rounded-2xl border-2 border-dashed border-slate-200 text-sm text-slate-400 hover:border-brand-300 hover:text-brand-500 transition-all duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add a Temporia note
                </button>

                <Transition name="expand">
                  <form v-if="showNoteForm" @submit.prevent="submitNote" class="bg-slate-50 rounded-2xl p-4 space-y-3">
                    <input v-model="noteForm.title" class="input text-sm" placeholder="Note title" maxlength="255" required />
                    <textarea v-model="noteForm.body" class="input text-sm resize-none" rows="3" placeholder="Write your note…" maxlength="10000" required></textarea>
                    <div class="flex items-center gap-3">
                      <span class="text-xs text-slate-500">Color:</span>
                      <div class="flex gap-2">
                        <button
                          v-for="c in NOTE_COLORS" :key="c.value"
                          type="button"
                          @click="noteForm.color = c.value"
                          class="w-6 h-6 rounded-full border-2 transition-all duration-150 hover:scale-110"
                          :class="[c.bg, noteForm.color === c.value ? 'border-slate-700 scale-110' : 'border-transparent']"
                        ></button>
                      </div>
                    </div>
                    <div class="flex gap-2">
                      <button type="submit" class="btn-primary text-sm" :disabled="busy['create-note']">
                        <svg v-if="busy['create-note']" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        {{ busy['create-note'] ? 'Adding…' : 'Add note' }}
                      </button>
                      <button type="button" @click="showNoteForm = false" class="btn-secondary text-sm">Cancel</button>
                    </div>
                  </form>
                </Transition>

                <NoteItem
                  v-for="note in notes" :key="note.id" :note="note"
                  :updating="!!busy[`update-note-${note.id}`]"
                  :deleting="!!busy[`delete-note-${note.id}`]"
                  @update="(data) => $emit('update-note', { id: note.id, data })"
                  @delete="$emit('delete-note', note.id)"
                />

                <div v-if="!notes.length && !showNoteForm" class="text-center py-8">
                  <p class="text-3xl mb-2">📝</p>
                  <p class="text-sm text-slate-400">No Temporia notes for this day yet</p>
                </div>
              </div>

              <!-- ALERTS TAB -->
              <div v-else-if="activeTab === 'alerts'" key="alerts" class="space-y-3">
                <button
                  @click="showAlertForm = !showAlertForm"
                  class="w-full flex items-center justify-center gap-2 py-2.5 rounded-2xl border-2 border-dashed border-slate-200 text-sm text-slate-400 hover:border-rose-300 hover:text-rose-500 transition-all duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Set a Temporia alert
                </button>

                <Transition name="expand">
                  <form v-if="showAlertForm" @submit.prevent="submitAlert" class="bg-slate-50 rounded-2xl p-4 space-y-3">
                    <input v-model="alertForm.title" class="input text-sm" placeholder="Alert title" maxlength="255" required />
                    <input v-model="alertForm.alert_time" type="time" class="input text-sm" />
                    <select v-model="alertForm.priority" class="input text-sm">
                      <option value="low">🟢 Low priority</option>
                      <option value="medium">🟡 Medium priority</option>
                      <option value="high">🔴 High priority</option>
                    </select>
                    <textarea v-model="alertForm.description" class="input text-sm resize-none" rows="2" placeholder="Optional description…" maxlength="2000"></textarea>
                    <div class="flex gap-2">
                      <button type="submit" class="btn-primary text-sm" :disabled="busy['create-alert']">
                        <svg v-if="busy['create-alert']" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        {{ busy['create-alert'] ? 'Saving…' : 'Save alert' }}
                      </button>
                      <button type="button" @click="showAlertForm = false" class="btn-secondary text-sm">Cancel</button>
                    </div>
                  </form>
                </Transition>

                <AlertItem
                  v-for="alert in alerts" :key="alert.id" :alert="alert"
                  :updating="!!busy[`update-alert-${alert.id}`]"
                  :deleting="!!busy[`delete-alert-${alert.id}`]"
                  @update="(data) => $emit('update-alert', { id: alert.id, data })"
                  @delete="$emit('delete-alert', alert.id)"
                />

                <div v-if="!alerts.length && !showAlertForm" class="text-center py-8">
                  <p class="text-3xl mb-2">🔔</p>
                  <p class="text-sm text-slate-400">No Temporia alerts set for this day</p>
                </div>
              </div>

            </Transition>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import NoteItem from './NoteItem.vue'
import AlertItem from './AlertItem.vue'
import DiaryEntryPanel from './DiaryEntryPanel.vue'

const TABS = [
  { key: 'diary',  label: 'Diary',  icon: '📖' },
  { key: 'notes',  label: 'Notes',  icon: '📝' },
  { key: 'alerts', label: 'Alerts', icon: '🔔' },
]

const NOTE_COLORS = [
  { value: 'indigo',  bg: 'bg-indigo-400'  },
  { value: 'rose',    bg: 'bg-rose-400'    },
  { value: 'amber',   bg: 'bg-amber-400'   },
  { value: 'emerald', bg: 'bg-emerald-400' },
]

const props = defineProps({
  open:   { type: Boolean,  required: true },
  date:   { type: String,   default: null  },
  entry:  { type: Object,   default: null  },
  notes:  { type: Array,    default: () => [] },
  alerts: { type: Array,    default: () => [] },
  busy:   { type: Object,   default: () => ({}) },
})

const emit = defineEmits([
  'close',
  'create-entry', 'update-entry', 'delete-entry',
  'save-note', 'update-note', 'delete-note',
  'save-alert', 'update-alert', 'delete-alert',
])

const activeTab     = ref('diary')
const showNoteForm  = ref(false)
const showAlertForm = ref(false)

const noteForm  = reactive({ title: '', body: '', color: 'indigo' })
const alertForm = reactive({ title: '', alert_time: '', priority: 'medium', description: '' })

const formattedDate = computed(() => {
  if (!props.date) return ''
  return new Date(props.date + 'T00:00:00').toLocaleDateString('default', {
    month: 'long', day: 'numeric', year: 'numeric',
  })
})

const weekday = computed(() => {
  if (!props.date) return ''
  return new Date(props.date + 'T00:00:00').toLocaleDateString('default', { weekday: 'long' })
})

function badgeCount(tab) {
  if (tab === 'diary')  return props.entry ? 1 : 0
  if (tab === 'notes')  return props.notes.length
  if (tab === 'alerts') return props.alerts.length
  return 0
}

watch(() => props.open, (val) => {
  if (val) {
    activeTab.value     = 'diary'
    showNoteForm.value  = false
    showAlertForm.value = false
  }
})

async function submitNote() {
  await emit('save-note', { ...noteForm, note_date: props.date })
  noteForm.title = ''
  noteForm.body  = ''
  noteForm.color = 'indigo'
  showNoteForm.value = false
}

async function submitAlert() {
  await emit('save-alert', { ...alertForm, alert_date: props.date })
  alertForm.title       = ''
  alertForm.alert_time  = ''
  alertForm.priority    = 'medium'
  alertForm.description = ''
  showAlertForm.value = false
}
</script>

<style scoped>
.backdrop-enter-active, .backdrop-leave-active { transition: opacity 0.25s ease; }
.backdrop-enter-from, .backdrop-leave-to       { opacity: 0; }

.slide-up-enter-active { transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.slide-up-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.slide-up-enter-from   { opacity: 0; transform: translateY(40px); }
.slide-up-leave-to     { opacity: 0; transform: translateY(20px); }

.tab-fade-enter-active, .tab-fade-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.tab-fade-enter-from   { opacity: 0; transform: translateX(8px); }
.tab-fade-leave-to     { opacity: 0; transform: translateX(-8px); }

.expand-enter-active { transition: all 0.25s ease; max-height: 400px; }
.expand-leave-active { transition: all 0.2s ease; }
.expand-enter-from   { opacity: 0; max-height: 0; transform: translateY(-8px); }
.expand-leave-to     { opacity: 0; max-height: 0; }
</style>
