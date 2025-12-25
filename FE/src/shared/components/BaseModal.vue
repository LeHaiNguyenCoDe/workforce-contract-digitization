<script setup lang="ts">
import { onMounted, onUnmounted, computed, onUpdated, onBeforeUnmount } from 'vue'

interface Props {
    modelValue: boolean
    title?: string
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
    closable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    title: '',
    size: 'md',
    closable: true
})

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
    close: []
}>()

// Computed để hỗ trợ cả v-model và show prop (backward compatibility)
const show = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const sizeClasses: Record<string, string> = {
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    full: 'max-w-[90vw]'
}

const handleClose = () => {
    if (props.closable) {
        show.value = false
        emit('close')
    }
}

const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.closable) {
        emit('close')
    }
}

// Quản lý body overflow khi modal mở/đóng
const updateBodyOverflow = () => {
    if (props.modelValue) {
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = ''
    }
}

// Update body overflow khi modelValue thay đổi
onUpdated(() => {
    updateBodyOverflow()
})

onMounted(() => {
    document.addEventListener('keydown', handleEscape)
    updateBodyOverflow()
})

// Update body overflow khi modelValue thay đổi (thay vì watch)
onUpdated(() => {
    updateBodyOverflow()
})

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleEscape)
})

onUnmounted(() => {
    document.body.style.overflow = ''
})
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="handleClose">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                <!-- Modal Container -->
                <Transition enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95">
                    <div v-if="show"
                        class="relative w-full flex flex-col bg-dark-800 rounded-2xl border border-white/10 shadow-2xl max-h-[90vh]"
                        :class="sizeClasses[size]">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 flex-shrink-0">
                            <h3 class="text-xl font-bold text-white">
                                <slot name="title">{{ title }}</slot>
                            </h3>
                            <button v-if="closable" @click="handleClose"
                                class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 6 6 18" />
                                    <path d="m6 6 12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body (scrollable) -->
                        <div class="flex-1 overflow-y-auto px-6 py-5">
                            <slot></slot>
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="px-6 py-4 border-t border-white/10 flex-shrink-0">
                            <slot name="footer"></slot>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
