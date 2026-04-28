<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-brand-50/30">

    <!-- ── Navbar ──────────────────────────────────────── -->
    <header class="bg-white/80 backdrop-blur-md border-b border-slate-100/80 sticky top-0 z-30">
      <div class="max-w-2xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-gradient-to-br from-brand-500 to-brand-700 rounded-xl flex items-center justify-center shadow-sm shadow-brand-200">
            <svg class="w-[18px] h-[18px] text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <span class="text-lg font-bold text-slate-900 tracking-tight">Temporia</span>
        </div>

        <div class="flex items-center gap-3">
          <div class="hidden sm:flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-brand-100 flex items-center justify-center text-xs font-bold text-brand-700">
              {{ userInitial }}
            </div>
            <span class="text-sm text-slate-600 font-medium">{{ auth.user?.name }}</span>
          </div>
          <button
            @click="handleLogout"
            :disabled="loggingOut"
            class="btn-secondary text-xs px-3 py-1.5 gap-1.5"
          >
            <svg v-if="loggingOut" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            {{ loggingOut ? 'Signing out…' : 'Logout' }}
          </button>
        </div>
      </div>
    </header>

    <!-- ── Main ────────────────────────────────────────── -->
    <main class="max-w-2xl mx-auto px-4 py-6 space-y-5">

      <!-- Month stats -->
      <div class="grid grid-cols-3 gap-3">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="bg-white rounded-2xl border border-slate-100 px-4 py-3 flex items-center gap-3 shadow-sm"
        >
          <span class="text-xl">{{ stat.icon }}</span>
          <div>
            <Transition name="count" mode="out-in">
              <p :key="stat.count" class="text-lg font-bold text-slate-800 leading-none">{{ stat.count }}</p>
            </Transition>
            <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ stat.label }}</p>
          </div>
        </div>
      </div>

      <!-- Calendar -->
      <CalendarGrid
        :days="days"
        :month-label="monthLabel"
        :today-str="todayStr"
        :selected-date="store.selectedDate"
        :loading="store.loading"
        :dot-flags="store.dotFlags"
        @prev="handlePrev"
        @next="handleNext"
        @select="handleSelectDate"
      />

      <!-- Today shortcut -->
      <div class="flex justify-center">
        <button
          @click="handleSelectDate(todayStr)"
          class="flex items-center gap-2 text-sm text-brand-600 font-medium hover:text-brand-800 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Open today
        </button>
      </div>
    </main>

    <!-- ── Date Modal ──────────────────────────────────── -->
    <DateModal
      :open="store.modalOpen"
      :date="store.selectedDate"
      :entry="selectedEntry"
      :notes="selectedNotes"
      :alerts="selectedAlerts"
      :busy="store.busy"
      @close="store.closeModal"
      @create-entry="(d)        => store.createEntry(d)"
      @update-entry="({ id, data }) => store.updateEntry(id, data)"
      @delete-entry="(id)       => store.deleteEntry(id)"
      @save-note="(d)           => store.saveNote(d)"
      @update-note="({ id, data }) => store.updateNote(id, data)"
      @delete-note="(id)        => store.deleteNote(id)"
      @save-alert="(d)          => store.saveAlert(d)"
      @update-alert="({ id, data }) => store.updateAlert(id, data)"
      @delete-alert="(id)       => store.deleteAlert(id)"
    />
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCalendarStore } from '@/stores/calendar'
import { useCalendar } from '@/composables/useCalendar'
import { useNotification } from '@/composables/useNotification'
import CalendarGrid from '@/components/CalendarGrid.vue'
import DateModal from '@/components/DateModal.vue'

const auth   = useAuthStore()
const store  = useCalendarStore()
const router = useRouter()
const notify = useNotification()
const { current, monthLabel, days, prev, next, todayStr, markFetched, isFetched } = useCalendar()

const loggingOut = ref(false)

// ── Computed ───────────────────────────────────────────
const userInitial    = computed(() => auth.user?.name?.[0]?.toUpperCase() || '?')
const selectedEntry  = computed(() => store.entryForDate(store.selectedDate))
const selectedNotes  = computed(() => store.notesForDate(store.selectedDate))
const selectedAlerts = computed(() => store.alertsForDate(store.selectedDate))

const stats = computed(() => [
  { icon: '📖', count: store.entries.length, label: 'Diary entries' },
  { icon: '📝', count: store.notes.length,   label: 'Notes'         },
  { icon: '🔔', count: store.alerts.length,  label: 'Alerts'        },
])

// ── Cache-aware month fetch — no redundant API calls ──
watch(current, ({ year, month }) => {
  if (!isFetched(year, month)) {
    store.fetchMonth(year, month)
    markFetched(year, month)
  }
}, { immediate: true })

// ── Handlers ───────────────────────────────────────────
function handlePrev() {
  prev()
  store.selectedDate = null
  store.modalOpen    = false
}

function handleNext() {
  next()
  store.selectedDate = null
  store.modalOpen    = false
}

function handleSelectDate(date) {
  store.openDate(date)
}

async function handleLogout() {
  loggingOut.value = true
  try {
    await auth.logout()
    router.push('/login')
  } catch {
    notify.error('Logout failed. Please try again.')
  } finally {
    loggingOut.value = false
  }
}
</script>

<style scoped>
.count-enter-active, .count-leave-active { transition: all 0.2s ease; }
.count-enter-from { opacity: 0; transform: translateY(-6px); }
.count-leave-to   { opacity: 0; transform: translateY(6px); }
</style>
