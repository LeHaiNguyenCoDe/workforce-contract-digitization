<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'

interface Props {
    modelValue: boolean
    images: string[]
    currentIndex?: number
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    images: () => [],
    currentIndex: 0
})

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
}>()

const activeIndex = ref(props.currentIndex)

watch(() => props.currentIndex, (newVal) => {
    activeIndex.value = newVal
})

const close = () => {
    emit('update:modelValue', false)
}

const prev = () => {
    if (activeIndex.value > 0) {
        activeIndex.value--
    } else {
        activeIndex.value = props.images.length - 1
    }
}

const next = () => {
    if (activeIndex.value < props.images.length - 1) {
        activeIndex.value++
    } else {
        activeIndex.value = 0
    }
}

const handleKeydown = (e: KeyboardEvent) => {
    if (!props.modelValue) return
    if (e.key === 'Escape') close()
    if (e.key === 'ArrowLeft') prev()
    if (e.key === 'ArrowRight') next()
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
})

const updateBodyScroll = (lock: boolean) => {
    document.body.style.overflow = lock ? 'hidden' : ''
}

watch(() => props.modelValue, (newVal) => {
    updateBodyScroll(newVal)
})
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="modelValue" class="image-modal-root" @click.self="close">
                <!-- Close Button -->
                <button class="close-btn" @click="close" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>

                <!-- Navigation Controls -->
                <div v-if="images.length > 1" class="nav-controls">
                    <button class="nav-btn prev" @click="prev" aria-label="Previous">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button class="nav-btn next" @click="next" aria-label="Next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- Image Container -->
                <div class="image-container" @click.self="close">
                    <Transition name="image-zoom" mode="out-in">
                        <img :key="activeIndex" :src="images[activeIndex]" class="preview-image" @click.stop />
                    </Transition>
                </div>

                <!-- Pagination Indicator -->
                <div v-if="images.length > 1" class="pagination">
                    {{ activeIndex + 1 }} / {{ images.length }}
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.image-modal-root {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(15, 15, 35, 0.9);
    backdrop-filter: blur(8px);
    overflow: hidden;
}

.image-container {
    padding: 40px;
    width: 90vw;
    max-width: 800px;
    height: 32rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.close-btn {
    position: absolute;
    top: 24px;
    right: 24px;
    z-index: 10001;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.nav-controls {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px;
    pointer-events: none;
    z-index: 10000;
}

.nav-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    cursor: pointer;
    pointer-events: auto;
    transition: all 0.2s ease;
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.pagination {
    position: absolute;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 1px;
}

/* Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.image-zoom-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.image-zoom-leave-active {
    transition: all 0.2s ease;
}

.image-zoom-enter-from {
    opacity: 0;
    transform: scale(0.9);
}

.image-zoom-leave-to {
    opacity: 0;
    transform: scale(1.05);
}
</style>
