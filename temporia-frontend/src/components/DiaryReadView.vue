<template>
  <div class="space-y-4">

    <!-- Entry body -->
    <div
      class="relative rounded-2xl p-5 border transition-colors duration-200"
      :class="locked
        ? 'bg-slate-50 border-slate-200'
        : 'bg-gradient-to-br from-brand-50/60 to-white border-brand-100'"
    >
      <!-- Lock watermark -->
      <div
        v-if="locked"
        class="absolute top-3 right-3 flex items-center gap-1 text-[10px] font-semibold text-slate-400 bg-white border border-slate-200 rounded-full px-2 py-0.5"
      >
        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
            clip-rule="evenodd" />
        </svg>
        Locked
      </div>

      <!-- Mood badge -->
      <div v-if="entry.mood" class="flex items-center gap-2 mb-3">
        <span class="text-2xl">{{ moodEmoji }}</span>
        <span class="text-xs font-semibold text-slate-500 capitalize">{{ entry.mood }}</span>
      </div>

      <!-- Content -->
      <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ entry.content }}</p>

      <!-- Meta -->
      <div class="flex items-center gap-3 mt-4 pt-3 border-t border-slate-100">
        <span class="text-[11px] text-slate-400">
          {{ wordCount }} {{ wordCount === 1 ? 'word' : 'words' }}
        </span>
        <span class="text-slate-200">·</span>
        <span class="text-[11px] text-slate-400">
          Written {{ timeAgo }}
        </span>
      </div>
    </div>


  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const MOODS = {
  happy:   '😊',
  excited: '🤩',
  neutral: '😐',
  sad:     '😢',
  anxious: '😰',
}

const props = defineProps({
  entry:  { type: Object,  required: true },
  locked: { type: Boolean, default: false },
})


const moodEmoji = computed(() => MOODS[props.entry.mood] || '')

const wordCount = computed(() =>
  props.entry.content?.trim()
    ? props.entry.content.trim().split(/\s+/).length
    : 0
)

const timeAgo = computed(() => {
  if (!props.entry.created_at) return ''
  const diff = Date.now() - new Date(props.entry.created_at).getTime()
  const mins  = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days  = Math.floor(diff / 86400000)
  if (mins < 1)   return 'just now'
  if (mins < 60)  return `${mins}m ago`
  if (hours < 24) return `${hours}h ago`
  return `${days}d ago`
})

</script>
