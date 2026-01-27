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
    handleClose()
  }
}

const updateBodyOverflow = () => {
  document.body.style.overflow = props.modelValue ? 'hidden' : ''
}

onUpdated(updateBodyOverflow)
onMounted(() => {
  document.addEventListener('keydown', handleEscape)
  updateBodyOverflow()
})
onBeforeUnmount(() => document.removeEventListener('keydown', handleEscape))
onUnmounted(() => { document.body.style.overflow = '' })
</script>

<template>
  <Teleport to="body">
    <Transition enter-active-class="transition-opacity duration-150 ease-out" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition-opacity duration-100 ease-in"
      leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="handleClose">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <!-- Modal Container with CSS animation -->
        <div
          class="relative w-full flex flex-col bg-dark-800 rounded-2xl border border-white/10 shadow-2xl max-h-[90vh] animate-modal-enter"
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
                <path d="M18 6 6 18" /><path d="m6 6 12 12" />
              </svg>
            </button>
          </div>
          <!-- Body -->
          <div class="flex-1 overflow-y-auto px-6 py-5"><slot></slot></div>
          <!-- Footer -->
          <div v-if="$slots.footer" class="px-6 py-4 border-t border-white/10 flex-shrink-0">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
