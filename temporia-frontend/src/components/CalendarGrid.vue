<template>
  <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- ── Header ─────────────────────────────────────── -->
    <div class="px-6 pt-6 pb-4">
      <div class="flex items-center justify-between">
        <button
          @click="$emit('prev')"
          class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all duration-200"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <div class="text-center">
          <Transition name="fade-slide" mode="out-in">
            <h2 :key="monthLabel" class="text-base font-semibold text-slate-800 tracking-tight">
              {{ monthLabel }}
            </h2>
          </Transition>
        </div>

        <button
          @click="$emit('next')"
          class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all duration-200"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>

    <!-- ── Day Labels ──────────────────────────────────── -->
    <div class="grid grid-cols-7 px-2 pb-1">
      <div
        v-for="d in DAYS"
        :key="d"
        class="py-1.5 text-center text-[10px] font-semibold text-slate-400 uppercase tracking-widest"
      >
        {{ d }}
      </div>
    </div>

    <!-- ── Loading ─────────────────────────────────────── -->
    <div v-if="loading" class="flex items-center justify-center h-56">
      <div class="flex gap-1.5">
        <span v-for="i in 3" :key="i"
          class="w-2 h-2 rounded-full bg-brand-400 animate-bounce"
          :style="{ animationDelay: `${(i - 1) * 0.15}s` }"
        ></span>
      </div>
    </div>

    <!-- ── Grid ────────────────────────────────────────── -->
    <Transition name="fade-slide" mode="out-in">
      <div v-if="!loading" :key="monthLabel" class="grid grid-cols-7 px-2 pb-4 gap-y-1">
        <div
          v-for="(cell, i) in days"
          :key="i"
          class="relative flex flex-col items-center pt-1.5 pb-2 rounded-2xl cursor-pointer select-none transition-all duration-200 group"
          :class="cellClass(cell)"
          @click="cell && $emit('select', cell.date)"
        >
          <template v-if="cell">
            <!-- Day number -->
            <span
              class="w-8 h-8 flex items-center justify-center text-sm font-medium rounded-full transition-all duration-200 z-10"
              :class="dayNumberClass(cell)"
            >
              {{ cell.day }}
            </span>

            <!-- Dot indicators -->
            <!-- Notes preview -->
<div class="text-[10px] mt-1 w-full px-1 space-y-0.5 pointer-events-none">
 <div
    v-for="note in getNotes(cell.date).slice(0, 2)"
    :key="note.id"
    class="text-amber-600 text-[10px] leading-tight text-center break-words"
  >
    {{ note.note || note.title || note.content || note.text }}
  </div>

  <div
    v-if="getNotes(cell.date).length > 2"
    class="text-[9px] text-slate-400 text-center"
  >
    +{{ getNotes(cell.date).length - 2 }} more
  </div>
</div>
            <div class="flex items-center gap-0.5 mt-1 h-2">
              <span
                v-if="dotFlags(cell.date).hasDiary"
                class="w-1.5 h-1.5 rounded-full bg-brand-500 transition-transform duration-200 group-hover:scale-125"
              ></span>
              <span
                v-if="dotFlags(cell.date).noteCount > 0"
                class="w-1.5 h-1.5 rounded-full bg-amber-400 transition-transform duration-200 group-hover:scale-125"
              ></span>
              <span
                v-if="dotFlags(cell.date).alertCount > 0"
                class="w-1.5 h-1.5 rounded-full bg-rose-400 transition-transform duration-200 group-hover:scale-125"
              ></span>
            </div>
          </template>
        </div>
      </div>
    </Transition>

    <!-- ── Legend ──────────────────────────────────────── -->
    <div class="flex items-center justify-center gap-5 px-6 py-3 border-t border-slate-50 bg-slate-50/60">
      <div v-for="l in LEGEND" :key="l.label" class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full" :class="l.color"></span>
        <span class="text-[10px] text-slate-400 font-medium">{{ l.label }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
const DAYS   = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const LEGEND = [
  { label: 'Diary',  color: 'bg-brand-500' },
  { label: 'Note',   color: 'bg-amber-400' },
  { label: 'Alert',  color: 'bg-rose-400'  },
]

const props = defineProps({
  days:         { type: Array,   required: true },
  monthLabel:   { type: String,  required: true },
  todayStr:     { type: String,  required: true },
  selectedDate: { type: String,  default: null  },
  loading:      { type: Boolean, default: false },
  dotFlags:     { type: Function, required: true },
  notes: { type: Array, default: () => [] },
})

defineEmits(['prev', 'next', 'select'])

function cellClass(cell) {
  if (!cell) return 'cursor-default'
  const isSelected = cell.date === props.selectedDate
  const isToday    = cell.date === props.todayStr
  if (isSelected) return 'bg-brand-600 shadow-md shadow-brand-200 scale-[1.04]'
  if (isToday)    return 'bg-brand-50 ring-1 ring-brand-200 hover:bg-brand-100'
  return 'hover:bg-slate-50'
}

function dayNumberClass(cell) {
  const isSelected = cell.date === props.selectedDate
  const isToday    = cell.date === props.todayStr
  if (isSelected) return 'text-white font-semibold'
  if (isToday)    return 'text-brand-700 font-semibold'
  const dow = new Date(cell.date + 'T00:00:00').getDay()
  return dow === 0 || dow === 6 ? 'text-slate-400' : 'text-slate-700'
}

import { useCalendarStore } from '@/stores/calendar'

const store = useCalendarStore()

function getNotes(date) {
  return props.notes.filter(n => {
    const noteDate = (n.note_date || '').split('T')[0]
    return noteDate === date
  })
}

</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(6px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
.truncate {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
</style>
