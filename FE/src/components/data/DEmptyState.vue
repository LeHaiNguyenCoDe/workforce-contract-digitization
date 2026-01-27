<script setup lang="ts">
interface Props {
  icon?: 'inbox' | 'search' | 'error' | 'folder'
  title?: string
  description?: string
}

withDefaults(defineProps<Props>(), {
  icon: 'inbox',
  title: 'Không có dữ liệu',
  description: ''
})

const icons: Record<string, string> = {
  inbox: 'M22 12h-6l-2 3h-4l-2-3H2 M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z',
  search: 'M11 17a6 6 0 100-12 6 6 0 000 12z M21 21l-4.35-4.35',
  error: 'M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z M12 9v4 M12 17h.01',
  folder: 'M3 6a2 2 0 012-2h5l2 2h7a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V6z'
}
</script>

<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
      stroke="currentColor" stroke-width="1" class="text-slate-600 mb-4">
      <path :d="icons[icon]" />
    </svg>
    <h3 class="text-lg font-semibold text-white mb-2">{{ title }}</h3>
    <p v-if="description" class="text-slate-400 max-w-md">{{ description }}</p>
    <div v-if="$slots.action" class="mt-4">
      <slot name="action" />
    </div>
  </div>
</template>
