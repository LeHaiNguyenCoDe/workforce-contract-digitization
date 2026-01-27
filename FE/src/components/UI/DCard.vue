<script setup lang="ts">
interface Props {
  title?: string
  noPadding?: boolean
  hoverable?: boolean
}

withDefaults(defineProps<Props>(), {
  noPadding: false,
  hoverable: false
})
</script>

<template>
  <div
    class="bg-dark-800 rounded-xl border border-white/10"
    :class="[
      !noPadding && 'p-4',
      hoverable && 'hover:border-white/20 transition-colors cursor-pointer'
    ]"
  >
    <div v-if="title" class="mb-4 pb-4 border-b border-white/10">
      <h3 class="text-lg font-semibold text-white">{{ title }}</h3>
    </div>
    <slot />
    <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-white/10">
      <slot name="footer" />
    </div>
  </div>
</template>
