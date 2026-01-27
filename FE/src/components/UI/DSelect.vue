<script setup lang="ts">
import { computed } from 'vue'

interface Option {
  value: string | number
  label: string
  disabled?: boolean
}

interface Props {
  modelValue?: string | number | null
  options: Option[]
  label?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  placeholder: '-- Chọn --'
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
}>()

const selectValue = computed({
  get: () => props.modelValue ?? '',
  set: (val) => emit('update:modelValue', val === '' ? null : val)
})

const selectClasses = computed(() => [
  'w-full px-4 py-2.5 rounded-lg bg-dark-700 border text-white',
  'focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all cursor-pointer',
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
    <select v-model="selectValue" :disabled="disabled" :class="selectClasses">
      <option value="">{{ placeholder }}</option>
      <option
        v-for="opt in options"
        :key="opt.value"
        :value="opt.value"
        :disabled="opt.disabled"
      >
        {{ opt.label }}
      </option>
    </select>
    <p v-if="error" class="text-xs text-error">{{ error }}</p>
  </div>
</template>
