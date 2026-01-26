<template>
  <div class="flex items-center gap-4">
    <div v-for="item in timeParts" :key="item.label" class="flex flex-col items-center">
      <div class="w-12 h-12 md:w-16 md:h-16 bg-white shadow-sm rounded-md flex items-center justify-center text-xl md:text-2xl font-bold text-slate-800">
        {{ item.value.toString().padStart(2, '0') }}
      </div>
      <span class="text-[10px] md:text-xs font-bold uppercase text-slate-400 mt-2">{{ item.label }}</span>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  name: 'CountdownTimer'
}
</script>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps<{
  targetDate: string
}>()

const remaining = ref(0)
let timer: any = null

const calculateRemaining = () => {
  const diff = new Date(props.targetDate).getTime() - new Date().getTime()
  remaining.value = Math.max(0, diff)
}

const timeParts = computed(() => {
  const days = Math.floor(remaining.value / (1000 * 60 * 60 * 24))
  const hours = Math.floor((remaining.value % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  const mins = Math.floor((remaining.value % (1000 * 60 * 60)) / (1000 * 60))
  const secs = Math.floor((remaining.value % (1000 * 60)) / 1000)

  return [
    { label: 'Days', value: days },
    { label: 'Hr', value: hours },
    { label: 'Min', value: mins },
    { label: 'Sc', value: secs }
  ]
})

onMounted(() => {
  calculateRemaining()
  timer = setInterval(calculateRemaining, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
