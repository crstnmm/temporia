import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import { diaryApi } from '@/api/diary'
import { notesApi } from '@/api/notes'
import { alertsApi } from '@/api/alerts'
import { useNotification } from '@/composables/useNotification'

export const useCalendarStore = defineStore('calendar', () => {
  const notify = useNotification()

  // ── State ────────────────────────────────────────────
  const entries      = ref([])
  const notes        = ref([])
  const alerts       = ref([])
  const selectedDate = ref(null)
  const loading      = ref(false)
  const modalOpen    = ref(false)

  // Per-action loading flags — keyed by action name or `${action}-${id}`
  const busy = reactive({})

  // ── Month fetch (with dedup guard) ───────────────────
  let _fetchController = null

  async function fetchMonth(year, month) {
    // Cancel any in-flight fetch for a previous month
    if (_fetchController) _fetchController.abort()
    _fetchController = new AbortController()

    loading.value = true
    try {
      const [e, n, a] = await Promise.all([
        diaryApi.list({ year, month }),
        notesApi.list({ year, month }),
        alertsApi.list({ year, month }),
      ])
      entries.value = e.data
      notes.value   = n.data
      alerts.value  = a.data
    } catch (err) {
      if (err.name !== 'AbortError' && err.name !== 'CanceledError') {
        notify.error('Failed to load calendar data. Please refresh.')
      }
    } finally {
      loading.value = false
    }
  }

  function openDate(date) {
    selectedDate.value = date
    modalOpen.value    = true
  }

  function closeModal() {
    modalOpen.value = false
  }

  /** Call on logout to wipe all data — enforces strict user isolation */
  function reset() {
    entries.value      = []
    notes.value        = []
    alerts.value       = []
    selectedDate.value = null
    modalOpen.value    = false
    Object.keys(busy).forEach((k) => delete busy[k])
  }

  // ── Diary ────────────────────────────────────────────
  async function createEntry(data) {
    busy['create-entry'] = true
    try {
      const res = await diaryApi.create(data)
      entries.value.push(res.data)
      notify.success('Diary entry saved ✨')
      return res.data
    } catch (err) {
      notify.error(err.message || 'Failed to save diary entry.')
      throw err
    } finally {
      busy['create-entry'] = false
    }
  }

  async function updateEntry(id, data) {
    busy[`update-entry-${id}`] = true
    try {
      const res = await diaryApi.update(id, data)
      const idx = entries.value.findIndex((e) => e.id === id)
      if (idx >= 0) entries.value[idx] = res.data
      notify.success('Entry updated.')
      return res.data
    } catch (err) {
      // Surface lock errors with a specific message
      const msg = err.status === 403
        ? 'This Temporia entry is locked after the day ends.'
        : (err.message || 'Failed to update entry.')
      notify.error(msg)
      throw err
    } finally {
      busy[`update-entry-${id}`] = false
    }
  }

  async function deleteEntry(id) {
    busy[`delete-entry-${id}`] = true
    try {
      await diaryApi.destroy(id)
      entries.value = entries.value.filter((e) => e.id !== id)
      notify.success('Entry deleted.')
    } catch (err) {
      notify.error(err.message || 'Failed to delete entry.')
      throw err
    } finally {
      busy[`delete-entry-${id}`] = false
    }
  }

  // ── Notes ────────────────────────────────────────────
  async function saveNote(data) {
    busy['create-note'] = true
    try {
      const res = await notesApi.create(data)
      notes.value.push(res.data)
      notify.success('Note added.')
      return res.data
    } catch (err) {
      notify.error(err.message || 'Failed to add note.')
      throw err
    } finally {
      busy['create-note'] = false
    }
  }

  async function updateNote(id, data) {
    busy[`update-note-${id}`] = true
    try {
      const res = await notesApi.update(id, data)
      const idx = notes.value.findIndex((n) => n.id === id)
      if (idx >= 0) notes.value[idx] = res.data
      notify.success('Note updated.')
      return res.data
    } catch (err) {
      notify.error(err.message || 'Failed to update note.')
      throw err
    } finally {
      busy[`update-note-${id}`] = false
    }
  }

  async function deleteNote(id) {
    busy[`delete-note-${id}`] = true
    try {
      await notesApi.destroy(id)
      notes.value = notes.value.filter((n) => n.id !== id)
      notify.success('Note deleted.')
    } catch (err) {
      notify.error(err.message || 'Failed to delete note.')
      throw err
    } finally {
      busy[`delete-note-${id}`] = false
    }
  }

  // ── Alerts ───────────────────────────────────────────
  async function saveAlert(data) {
    busy['create-alert'] = true
    try {
      const res = await alertsApi.create(data)
      alerts.value.push(res.data)
      notify.success('Alert set.')
      return res.data
    } catch (err) {
      notify.error(err.message || 'Failed to set alert.')
      throw err
    } finally {
      busy['create-alert'] = false
    }
  }

  async function updateAlert(id, data) {
    busy[`update-alert-${id}`] = true
    try {
      const res = await alertsApi.update(id, data)
      const idx = alerts.value.findIndex((a) => a.id === id)
      if (idx >= 0) alerts.value[idx] = res.data
      notify.success('Alert updated.')
      return res.data
    } catch (err) {
      notify.error(err.message || 'Failed to update alert.')
      throw err
    } finally {
      busy[`update-alert-${id}`] = false
    }
  }

  async function deleteAlert(id) {
    busy[`delete-alert-${id}`] = true
    try {
      await alertsApi.destroy(id)
      alerts.value = alerts.value.filter((a) => a.id !== id)
      notify.success('Alert removed.')
    } catch (err) {
      notify.error(err.message || 'Failed to remove alert.')
      throw err
    } finally {
      busy[`delete-alert-${id}`] = false
    }
  }

  // ── Selectors ────────────────────────────────────────
function entryForDate(date) {
  return entries.value.find((e) => {
    const entryDate = (e.date || e.created_at || '').split('T')[0]
    return entryDate === date
  }) || null
} function notesForDate(date) {
  return notes.value.filter((n) => {
    const noteDate = (n.note_date || '').split('T')[0]
    return noteDate === date
  })
}function alertsForDate(date) { return alerts.value.filter((a) => a.alert_date === date) }

  function dotFlags(date) {
    return {
      hasDiary:   entries.value.some((e) => e.date === date),
      noteCount: notes.value.filter((n) => {
  const noteDate = (n.note_date || '').split('T')[0]
  return noteDate === date
}).length,
    alertCount: alerts.value.filter((a) => a.alert_date === date).length,
    }
  }

  return {
    entries, notes, alerts, selectedDate, loading, modalOpen, busy,
    fetchMonth, openDate, closeModal, reset,
    createEntry, updateEntry, deleteEntry,
    saveNote, updateNote, deleteNote,
    saveAlert, updateAlert, deleteAlert,
    entryForDate, notesForDate, alertsForDate, dotFlags,
  }
})
