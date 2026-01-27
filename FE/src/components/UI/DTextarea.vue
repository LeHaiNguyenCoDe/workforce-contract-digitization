<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  modelValue?: string
  label?: string
  placeholder?: string
  rows?: number
  required?: boolean
  disabled?: boolean
  error?: string
  maxlength?: number
}

const props = withDefaults(defineProps<Props>(), {
  rows: 3,
  required: false,
  disabled: false
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const textareaValue = computed({
  get: () => props.modelValue ?? '',
  set: (val) => emit('update:modelValue', val)
})

const textareaClasses = computed(() => [
  'w-full px-4 py-2.5 rounded-lg bg-dark-700 border text-white placeholder-slate-500 resize-none',
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
    <textarea
      v-model="textareaValue"
      :rows="rows"
      :placeholder="placeholder"
      :disabled="disabled"
      :maxlength="maxlength"
      :class="textareaClasses"
    />
    <div v-if="error || maxlength" class="flex justify-between">
      <p v-if="error" class="text-xs text-error">{{ error }}</p>
      <span v-if="maxlength" class="text-xs text-slate-500 ml-auto">
        {{ (modelValue?.length || 0) }}/{{ maxlength }}
      </span>
    </div>
  </div>
</template>
