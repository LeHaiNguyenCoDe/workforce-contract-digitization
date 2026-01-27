<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  modelValue?: string | number
  type?: 'text' | 'number' | 'email' | 'password' | 'date' | 'tel' | 'url'
  label?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  error?: string
  hint?: string
  min?: number
  max?: number
  step?: number
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  required: false,
  disabled: false
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
}>()

const inputValue = computed({
  get: () => props.modelValue ?? '',
  set: (val) => emit('update:modelValue', val)
})

const inputClasses = computed(() => [
  'w-full px-4 py-2.5 rounded-lg bg-dark-700 border text-white placeholder-slate-500',
  'focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all',
  props.error
    ? 'border-error focus:border-error'
    : 'border-white/10 focus:border-primary',
  props.disabled && 'opacity-50 cursor-not-allowed'
])
</script>

<template>
  <div class="space-y-1.5">
    <label v-if="label" class="block text-sm font-medium text-slate-300">
      {{ label }}
      <span v-if="required" class="text-error">*</span>
    </label>
    <input
      v-model="inputValue"
      :type="type"
      :placeholder="placeholder"
      :disabled="disabled"
      :min="min"
      :max="max"
      :step="step"
      :class="inputClasses"
    />
    <p v-if="error" class="text-xs text-error">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-slate-500">{{ hint }}</p>
  </div>
</template>
