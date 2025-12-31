<template>
    <div class="border-b border-gray-50 last:border-0">
        <button @click="isOpen = !isOpen"
            class="w-full flex items-center justify-between px-4 py-4 hover:bg-gray-50 transition-colors group">
            <div class="flex items-center gap-3">
                <span class="text-gray-400 group-hover:text-primary transition-colors">
                    <svg v-if="icon === 'info'" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="16" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                    </svg>
                    <svg v-else-if="icon === 'edit'" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                    </svg>
                    <svg v-else-if="icon === 'image'" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <path d="m21 15-5-5L5 21" />
                    </svg>
                    <svg v-else-if="icon === 'shield'" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                </span>
                <span class="text-sm font-semibold text-gray-800 tracking-tight">{{ title }}</span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"
                :class="['text-gray-400 transition-transform duration-200', isOpen ? 'rotate-180' : '']">
                <polyline points="6 9 12 15 18 9" />
            </svg>
        </button>
        <transition name="expand">
            <div v-show="isOpen" class="overflow-hidden bg-white">
                <slot />
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
    title: string
    icon: string
}>()

const isOpen = ref(false)
</script>

<style scoped>
.expand-enter-active,
.expand-leave-active {
    transition: all 0.3s ease-out;
    max-height: 500px;
}

.expand-enter-from,
.expand-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>
