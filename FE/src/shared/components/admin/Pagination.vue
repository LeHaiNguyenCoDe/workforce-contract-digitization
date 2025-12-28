<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  currentPage: number
  totalPages: number
  totalItems?: number
  perPage?: number
  showInfo?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showInfo: true,
  perPage: 15
})

const emit = defineEmits<{
  'page-change': [page: number]
}>()

const canGoPrev = computed(() => props.currentPage > 1)
const canGoNext = computed(() => props.currentPage < props.totalPages)

const infoText = computed(() => {
  if (props.totalItems) {
    const start = (props.currentPage - 1) * props.perPage + 1
    const end = Math.min(props.currentPage * props.perPage, props.totalItems)
    return `${start}-${end} / ${props.totalItems}`
  }
  return `Trang ${props.currentPage} / ${props.totalPages}`
})

const goToPage = (page: number) => {
  if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
    emit('page-change', page)
  }
}

const prev = () => {
  if (canGoPrev.value) {
    goToPage(props.currentPage - 1)
  }
}

const next = () => {
  if (canGoNext.value) {
    goToPage(props.currentPage + 1)
  }
}
</script>

<template>
  <div v-if="totalPages > 1" class="flex items-center justify-between p-4">
    <span v-if="showInfo" class="text-sm text-slate-400">{{ infoText }}</span>
    <div v-else></div>
    
    <div class="flex items-center gap-2">
      <button
        @click="prev"
        :disabled="!canGoPrev"
        class="px-3 py-1.5 text-sm rounded-lg border border-white/10 transition-colors"
        :class="canGoPrev 
          ? 'bg-dark-700 text-white hover:bg-dark-600' 
          : 'bg-dark-800 text-slate-500 cursor-not-allowed'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" class="inline mr-1">
          <path d="m15 18-6-6 6-6"/>
        </svg>
        Trước
      </button>
      
      <div class="flex items-center gap-1">
        <button
          v-for="page in Math.min(5, totalPages)"
          :key="page"
          @click="goToPage(page)"
          class="w-8 h-8 text-sm rounded-lg transition-colors"
          :class="page === currentPage
            ? 'bg-primary text-white'
            : 'bg-dark-700 text-slate-400 hover:bg-dark-600 hover:text-white'"
        >
          {{ page }}
        </button>
        <span v-if="totalPages > 5" class="text-slate-500 px-2">...</span>
      </div>
      
      <button
        @click="next"
        :disabled="!canGoNext"
        class="px-3 py-1.5 text-sm rounded-lg border border-white/10 transition-colors"
        :class="canGoNext 
          ? 'bg-dark-700 text-white hover:bg-dark-600' 
          : 'bg-dark-800 text-slate-500 cursor-not-allowed'"
      >
        Sau
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" class="inline ml-1">
          <path d="m9 18 6-6-6-6"/>
        </svg>
      </button>
    </div>
  </div>
</template>
