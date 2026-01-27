<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  name: string
  size?: number | string
  color?: string
  strokeWidth?: number | string
}

const props = withDefaults(defineProps<Props>(), {
  size: 24,
  strokeWidth: 2
})

// Icon mapping - Add more icons here as needed
const icons: Record<string, any> = {
  orders: {
    viewBox: '0 0 24 24',
    content: '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" /><path d="M3 6h18" />'
  },
  products: {
    viewBox: '0 0 24 24',
    content: '<path d="m7.5 4.27 9 5.15" /><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />'
  },
  users: {
    viewBox: '0 0 24 24',
    content: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />'
  },
  revenue: {
    viewBox: '0 0 24 24',
    content: '<line x1="12" x2="12" y1="2" y2="22" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />'
  },
  'arrow-down': {
    viewBox: '0 0 24 24',
    content: '<path d="M12 5v14M5 12l7 7 7-7" />'
  },
  eye: {
    viewBox: '0 0 24 24',
    content: '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />'
  },
  chevron: {
    viewBox: '0 0 24 24',
    content: '<path d="m9 18 6-6-6-6" />'
  },
  'chevron-left': {
    viewBox: '0 0 24 24',
    content: '<path d="m15 18-6-6 6-6" />'
  },
  'chevron-right': {
    viewBox: '0 0 24 24',
    content: '<path d="m9 18 6-6 6-6" />'
  },
  inbox: {
    viewBox: '0 0 24 24',
    content: '<path d="M22 12h-6l-2 3h-4l-2-3H2" /><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />'
  },
  star: {
     viewBox: '0 0 24 24',
     content: '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />'
  }
}

const icon = computed(() => icons[props.name] || icons.products)
</script>

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    :width="size"
    :height="size"
    :viewBox="icon.viewBox"
    fill="none"
    :stroke="color || 'currentColor'"
    :stroke-width="strokeWidth"
    stroke-linecap="round"
    stroke-linejoin="round"
    v-html="icon.content"
  ></svg>
</template>
