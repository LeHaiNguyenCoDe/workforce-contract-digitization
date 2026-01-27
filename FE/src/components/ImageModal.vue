<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation, Pagination, Zoom, Keyboard, Mousewheel } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import 'swiper/css/zoom'

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

const swiperModules = [Navigation, Pagination, Zoom, Keyboard, Mousewheel]
const swiperInstance = ref<any>(null)

const onSwiper = (swiper: any) => {
    swiperInstance.value = swiper
}

const close = () => {
    emit('update:modelValue', false)
}

const updateBodyScroll = (lock: boolean) => {
    document.body.style.overflow = lock ? 'hidden' : ''
}

watch(() => props.modelValue, (newVal) => {
    updateBodyScroll(newVal)
    if (newVal && swiperInstance.value) {
        // Use timeout to ensure swiper is ready after transition
        setTimeout(() => {
            swiperInstance.value.slideTo(props.currentIndex, 0)
            swiperInstance.value.update()
        }, 50)
    }
})

watch(() => props.currentIndex, (newVal) => {
    if (swiperInstance.value && props.modelValue) {
        swiperInstance.value.slideTo(newVal)
    }
})

// Handle escape key
const handleKeydown = (e: KeyboardEvent) => {
    if (props.modelValue && e.key === 'Escape') {
        close()
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
    updateBodyScroll(false)
})
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="modelValue" class="image-modal-root" @click.self="close">
                <!-- Close Button -->
                <button class="close-btn" @click="close" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>

                <!-- Main Slider -->
                <div class="image-slider-container">
                    <swiper
                        :modules="swiperModules"
                        :initial-slide="currentIndex"
                        :slides-per-view="1"
                        :space-between="0"
                        :navigation="{
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev'
                        }"
                        :pagination="{
                            type: 'fraction',
                            el: '.swiper-pagination'
                        }"
                        :zoom="true"
                        :keyboard="{ enabled: true }"
                        :mousewheel="true"
                        class="h-full w-full"
                        @swiper="onSwiper"
                    >
                        <swiper-slide v-for="(image, index) in images" :key="index" class="flex items-center justify-center swiper-zoom-container">
                            <img :src="image" class="preview-image" @click.stop />
                        </swiper-slide>

                        <!-- Navigation Buttons -->
                        <div v-if="images.length > 1" class="swiper-button-prev !text-white !w-12 !h-12 after:!text-2xl hover:bg-white/10 rounded-full transition-all"></div>
                        <div v-if="images.length > 1" class="swiper-button-next !text-white !w-12 !h-12 after:!text-2xl hover:bg-white/10 rounded-full transition-all"></div>
                        
                        <!-- Pagination -->
                        <div v-if="images.length > 1" class="swiper-pagination !text-white !bottom-8 !font-medium"></div>
                    </swiper>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

