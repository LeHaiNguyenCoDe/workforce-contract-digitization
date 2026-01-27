<script setup lang="ts">
interface Props {
  size?: 'sm' | 'md' | 'lg'
  text?: string
  overlay?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  overlay: false
})

const sizeClasses: Record<string, string> = {
  sm: 'w-5 h-5 border-2',
  md: 'w-8 h-8 border-4',
  lg: 'w-12 h-12 border-4'
}
</script>

<template>
  <div
    :class="[
      'flex flex-col items-center justify-center gap-3',
      overlay && 'fixed inset-0 z-50 bg-dark-900/80 backdrop-blur-sm'
    ]"
  >
    <div
      :class="[
        'border-primary/20 border-t-primary rounded-full animate-spin',
        sizeClasses[size]
      ]"
    />
    <p v-if="text" class="text-sm text-slate-400">{{ text }}</p>
  </div>
</template>
