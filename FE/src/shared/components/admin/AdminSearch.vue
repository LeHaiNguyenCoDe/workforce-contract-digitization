<script setup lang="ts">
import { ref, watch } from 'vue'

interface Props {
  modelValue?: string
  placeholder?: string
  debounce?: number
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Tìm kiếm...',
  debounce: 300
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'search': [value: string]
}>()

const localValue = ref(props.modelValue)
let debounceTimer: ReturnType<typeof setTimeout> | null = null

watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal
})

const handleInput = (e: Event) => {
  const value = (e.target as HTMLInputElement).value
  localValue.value = value
  emit('update:modelValue', value)
  
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('search', value)
  }, props.debounce)
}

const handleSearch = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  emit('search', localValue.value)
}

const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Enter') {
    handleSearch()
  }
}
</script>

<template>
  <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
    <div class="flex gap-4">
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg"
          width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.3-4.3" />
        </svg>
        <input
          :value="localValue"
          @input="handleInput"
          @keydown="handleKeydown"
          type="text"
          class="form-input pl-10"
          :placeholder="placeholder"
        />
      </div>
      
      <!-- Filter Slots -->
      <slot name="filters"></slot>
      
      <button @click="handleSearch" class="btn btn-secondary">
        Tìm kiếm
      </button>
    </div>
  </div>
</template>
