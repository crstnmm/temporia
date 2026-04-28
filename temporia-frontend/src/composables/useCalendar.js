import { ref, computed } from 'vue'

export function useCalendar() {
  const today   = new Date()
  const current = ref({ year: today.getFullYear(), month: today.getMonth() + 1 })

  // Cache of already-fetched "YYYY-M" keys — prevents duplicate API calls
  const fetchedMonths = new Set()

  const monthLabel = computed(() =>
    new Date(current.value.year, current.value.month - 1, 1)
      .toLocaleString('default', { month: 'long', year: 'numeric' })
  )

  const days = computed(() => {
    const { year, month } = current.value
    const firstDay    = new Date(year, month - 1, 1).getDay()
    const daysInMonth = new Date(year, month, 0).getDate()
    const grid        = []

    for (let i = 0; i < firstDay; i++) grid.push(null)
    for (let d = 1; d <= daysInMonth; d++) {
      const mm = String(month).padStart(2, '0')
      const dd = String(d).padStart(2, '0')
      grid.push({ day: d, date: `${year}-${mm}-${dd}` })
    }
    return grid
  })

  function prev() {
    if (current.value.month === 1) {
      current.value = { year: current.value.year - 1, month: 12 }
    } else {
      current.value = { ...current.value, month: current.value.month - 1 }
    }
  }

  function next() {
    if (current.value.month === 12) {
      current.value = { year: current.value.year + 1, month: 1 }
    } else {
      current.value = { ...current.value, month: current.value.month + 1 }
    }
  }

  function monthKey({ year, month } = current.value) {
    return `${year}-${month}`
  }

  function markFetched(year, month) {
    fetchedMonths.add(`${year}-${month}`)
  }

  function isFetched(year, month) {
    return fetchedMonths.has(`${year}-${month}`)
  }

  function invalidateMonth(year, month) {
    fetchedMonths.delete(`${year}-${month}`)
  }

  const todayStr = [
    today.getFullYear(),
    String(today.getMonth() + 1).padStart(2, '0'),
    String(today.getDate()).padStart(2, '0'),
  ].join('-')

  return {
    current, monthLabel, days, prev, next,
    todayStr, monthKey,
    markFetched, isFetched, invalidateMonth,
  }
}
