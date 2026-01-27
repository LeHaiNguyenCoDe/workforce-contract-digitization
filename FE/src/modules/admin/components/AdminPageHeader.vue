<script setup lang="ts">
interface Props {
  title: string
  description?: string
}

defineProps<Props>()
</script>

<template>
  <div class="flex items-center justify-between mb-6 flex-shrink-0">
    <div>
      <h1 class="text-2xl font-bold text-white">{{ title }}</h1>
      <p v-if="description" class="text-slate-400 mt-1">{{ description }}</p>
    </div>
    <div class="flex items-center gap-3">
      <slot name="actions"></slot>
    </div>
  </div>
</template>
