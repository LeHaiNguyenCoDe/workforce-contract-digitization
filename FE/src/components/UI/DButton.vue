<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'primary' | 'secondary' | 'success' | 'error' | 'warning' | 'info' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
  loading?: boolean
  fullWidth?: boolean
  type?: 'button' | 'submit' | 'reset'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false,
  fullWidth: false,
  type: 'button'
})

const variantClasses: Record<string, string> = {
  primary: 'bg-gradient-to-r from-primary to-secondary text-white hover:opacity-90',
  secondary: 'bg-dark-700 border border-white/10 text-white hover:bg-dark-600',
  success: 'bg-success text-white hover:bg-success/90',
  error: 'bg-error text-white hover:bg-error/90',
  warning: 'bg-warning text-dark-900 hover:bg-warning/90',
  info: 'bg-info text-white hover:bg-info/90',
  ghost: 'bg-transparent text-slate-400 hover:bg-white/5 hover:text-white'
}

const sizeClasses: Record<string, string> = {
  sm: 'px-3 py-1.5 text-sm',
  md: 'px-4 py-2.5 text-sm',
  lg: 'px-6 py-3 text-base'
}

const buttonClasses = computed(() => [
  'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all',
  variantClasses[props.variant],
  sizeClasses[props.size],
  props.fullWidth && 'w-full',
  (props.disabled || props.loading) && 'opacity-50 cursor-not-allowed'
])
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
  >
    <svg v-if="loading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
    </svg>
    <slot />
  </button>
</template>
