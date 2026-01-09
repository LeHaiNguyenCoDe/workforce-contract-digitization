<script setup lang="ts">
/**
 * Home View
 * Uses useHome composable for logic separation
 */
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import BlogView from './BlogView.vue'
import ReviewView from './ReviewView.vue'
import ImageModal from '@/components/ImageModal.vue'
import ProductCard from '@/modules/landing/products/components/ProductCard.vue'
import { useHome } from '../composables/useHome'
import { homeConfig } from '../configs'
import type { Product } from '@/modules/landing/products/types'
import slide1 from '@/assets/landing/home/slide1.png'
import slide2 from '@/assets/landing/home/slide2.png'
import slide3 from '@/assets/landing/home/slide3.png'
import iconCall from '@/assets/landing/home/call.svg'
import iconShop from '@/assets/landing/home/shop.svg'
import iconPayment from '@/assets/landing/home/payment.svg'
import iconShipping from '@/assets/landing/home/shipping.svg'

const { t } = useI18n()

// Use composable for home data
const {
    productsByCategory,
    featuredCategories,
    isLoading,
    loadHomeData
} = useHome()

const error = ref<string | null>(null)
const heroImages = [slide1, slide2, slide3]
const currentSlide = ref(0)
let heroTimer: ReturnType<typeof setInterval> | null = null
const categorySlideIndex = ref<Record<number, number>>({})
const showAllCategories = ref(false)
const categoriesSection = ref<HTMLElement | null>(null)

// Scroll state for showing/hiding scrollbar
const isPastHero = ref(false)
const contentSection = ref<HTMLElement | null>(null)

const visibleCategories = computed(() => {
    const list = Array.isArray(featuredCategories) ? featuredCategories : (featuredCategories.value || [])
    return showAllCategories.value ? list : list.slice(0, 4)
})

const toggleCategories = async () => {
    showAllCategories.value = !showAllCategories.value
    if (!showAllCategories.value) {
        await nextTick()
        categoriesSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
}

const startSlider = () => {
    if (heroTimer) clearInterval(heroTimer)
    heroTimer = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % heroImages.length
    }, 15000)
}

const stopSlider = () => {
    if (heroTimer) {
        clearInterval(heroTimer)
        heroTimer = null
    }
}

// Handle scroll to track position for button visibility and scrollbar
const handleScroll = () => {
    const heroHeight = window.innerHeight - 80
    isPastHero.value = window.scrollY > heroHeight * 0.5

    // Toggle scrollbar visibility (using transparent colors, not hiding)
    if (isPastHero.value) {
        document.documentElement.classList.remove('hide-scrollbar')
    } else {
        document.documentElement.classList.add('hide-scrollbar')
    }
}

// Custom smooth scroll function with easing
const smoothScrollTo = (targetY: number, duration: number = 800) => {
    const startY = window.pageYOffset
    const difference = targetY - startY
    const startTime = performance.now()

    const easeInOutCubic = (t: number): number => {
        return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2
    }

    const animateScroll = (currentTime: number) => {
        const elapsed = currentTime - startTime
        const progress = Math.min(elapsed / duration, 1)
        const easeProgress = easeInOutCubic(progress)

        window.scrollTo(0, startY + difference * easeProgress)

        if (progress < 1) {
            requestAnimationFrame(animateScroll)
        }
    }

    requestAnimationFrame(animateScroll)
}

// Scroll down to content
const scrollToContent = () => {
    const target = document.getElementById('content-section')
    const headerHeight = 80 // Height of sticky header
    if (target) {
        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight
        smoothScrollTo(targetPosition, 1000) // 1 second smooth scroll
    }
}

onMounted(() => {
    startSlider()
    window.addEventListener('scroll', handleScroll, { passive: true })
    handleScroll() // Check initial state
})
onUnmounted(() => {
    stopSlider()
    window.removeEventListener('scroll', handleScroll)
})


// Format price helper
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

// Get product image helper
const getProductImage = (product: Product) => {
    if (product.thumbnail) return product.thumbnail
    if (product.images?.[0]?.url) return product.images[0].url
    return null
}

const features = [
    { id: 1, titleKey: 'home.feature1Title', title: 'Phục vụ 24/24', icon: iconCall },
    { id: 2, titleKey: 'home.feature2Title', title: 'Mua sắm online', icon: iconShop },
    { id: 3, titleKey: 'home.feature3Title', title: 'Thanh toán online', icon: iconPayment },
    { id: 4, titleKey: 'home.feature4Title', title: 'Vận chuyển free', icon: iconShipping }
]

const categoriesSymbolicNames: Record<number, string> = {
    1: 'https://vuongomviet.com/public/uploads/Tintuc/tuong-gom-su-1.jpg',
    2: 'https://naty.vn/wp-content/uploads/2023/06/qua-tang-tan-gia-4.jpg',
    3: 'https://bizweb.dktcdn.net/100/400/560/products/z4544943015781-2f0075e3e216d9a6399fc773d1533632.jpg?v=1690254500157',
    4: 'https://phapduyen.com/wp-content/uploads/2024/10/00-1a-1.jpg',
    5: 'https://ecocare.vn/wp-content/uploads/2019/03/do-gia-dung-thong-minh-4-800x800.jpg',
    6: 'https://naty.vn/wp-content/uploads/2023/06/qua-tang-tan-gia-4.jpg',
    7: 'https://battrangceramics.com/User_folder_upload/admin/images/2021/gom-tam-linh-aug/loc-linh-tam-hop-ty-dau-suu-mau-xanh-ngoc-cao-31cm3.jpg',
    8: 'https://decopro.vn/wp-content/uploads/2018/12/Binh-gom-trang-tri-bg025-2.jpg',
    9: 'https://gomsubaokhanh.vn/media/product/1_hu_sanh_dung_gao_20_kg_tai_loc_dang_tru__cg_004_20_2.jpg',
    10: 'https://quatangcaominh.com/wp-content/uploads/2024/12/hop-qua-tam-linh-1024x576.jpg',
    11: 'https://gomtruongan.vn/uploads/products/02062020012026/tranh-gom-su-op-tuong-canh-dong-que_02062020012026.jpg',
    12: 'https://vuongomviet.com/public/uploads/Tintuc/tuong-gom-su-1.jpg'
}

const getCategoryImage = (category: any) => {
    return categoriesSymbolicNames[category.id] || category.image || ''
}
watch(
    productsByCategory,
    (categories) => {
        const initial: Record<number, number> = {}
        if (Array.isArray(categories)) {
            categories.forEach((item) => {
                if (item?.category?.id != null) {
                    initial[item.category.id] = 0
                }
            })
        }
        categorySlideIndex.value = initial
    },
    { immediate: true }
)

const prevProductSlide = (categoryId: number, totalProducts: number) => {
    if (totalProducts <= 3) return
    const current = categorySlideIndex.value[categoryId] || 0
    if (current > 0) {
        categorySlideIndex.value = { ...categorySlideIndex.value, [categoryId]: current - 1 }
    }
}

const nextProductSlide = (categoryId: number, totalProducts: number) => {
    if (totalProducts <= 3) return
    const current = categorySlideIndex.value[categoryId] || 0
    const maxIndex = totalProducts - 3
    if (current < maxIndex) {
        categorySlideIndex.value = { ...categorySlideIndex.value, [categoryId]: current + 1 }
    }
}

// Retry on error
const handleRetry = async () => {
    error.value = null
    try {
        await loadHomeData()
    } catch (err: any) {
        error.value = err.message || 'Failed to fetch data'
    }
}

// Image Modal State
const showImageModal = ref(false)
const modalImages = ref<string[]>([])
const modalInitialIndex = ref(0)

const openPreview = (imageUrl: string | null) => {
    if (!imageUrl) return
    modalImages.value = [imageUrl]
    modalInitialIndex.value = 0
    showImageModal.value = true
}
</script>

<template>
    <div class="bg-white">
        <section class="relative overflow-hidden flex items-center" style="height: calc(100vh - 80px);">
            <div class="absolute inset-0">
                <Transition enter-active-class="duration-700 ease-out" enter-from-class="opacity-0"
                    enter-to-class="opacity-100" leave-active-class="duration-700 ease-in"
                    leave-from-class="opacity-100" leave-to-class="opacity-0" mode="out-in">
                    <div :key="currentSlide" class="absolute inset-0">
                        <img :src="heroImages[currentSlide]" alt="" class="w-full h-full object-cover" />
                    </div>
                </Transition>
            </div>

            <div class="container relative z-10 h-full flex flex-col items-center justify-start pt-24">
                <div class="max-w-2xl mx-auto text-center">
                    <h1 class="text-3xl md:text-5xl font-bold text-white mb-8 animate-slide-up leading-tight">
                        Giảm giá 20% toàn bộ<br>
                        Sofa trong ngày hôm nay
                    </h1>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up">
                        <RouterLink to="/products"
                            class="px-8 py-3 border-2 border-white text-white rounded-sm hover:bg-white hover:text-[#9F7A5F] transition-all font-medium">
                            Mua ngay
                        </RouterLink>
                    </div>
                </div>
            </div>

            <!-- Slide Indicators (Dashes) -->
            <div class="absolute bottom-20 left-1/2 -translate-x-1/2 flex gap-3">
                <button v-for="(_, index) in heroImages" :key="index" @click="currentSlide = index; startSlider()"
                    class="w-8 h-1 rounded-full transition-all duration-300"
                    :class="currentSlide === index ? 'bg-white' : 'bg-white/40 hover:bg-white/60'"></button>
            </div>

            <!-- Scroll Down Arrow -->
            <button v-show="!isPastHero" @click="scrollToContent"
                class="absolute bottom-6 left-1/2 -translate-x-1/2 w-10 h-10 z-20 rounded-full bg-white/80 hover:bg-white shadow-lg border border-white/60 transition-all animate-bounce flex items-center justify-center cursor-pointer">
                <BaseIcon name="arrow-down" color="#9F7A5F" :size="20" />
            </button>
        </section>


        <!-- Error Message -->
        <div v-if="error" class="container py-8">
            <div class="bg-error/10 border border-error text-error p-4 rounded-xl text-center">
                {{ error }} - <button @click="handleRetry" class="underline">{{ t('common.retry') }}</button>
            </div>
        </div>

        <!-- About Section -->
        <section id="content-section" class="py-8 bg-white">
            <div class="container flex flex-col justify-center items-center">
                <h2 class="text-3xl md:text-4xl font-bold text-[#9F7A5F] mb-6">{{ t('home.aboutTitle') || 'Giới thiệu'
                    }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center py-2">
                    <p class="text-slate-700 text-md leading-relaxed md:text-lg h-full">
                        {{ t('home.aboutDescription') }}
                    </p>
                    <div class="relative">
                        <img src="../../../../assets/landing/home/about.png" alt="Giới thiệu"
                            class="w-full h-auto rounded-sm shadow-lg object-cover" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section ref="categoriesSection" class="py-16 md:py-218 bg-white">
            <div class="container">
                <div class="flex items-center justify-between mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-[#9F7A5F]">{{ t('common.categories') }}</h2>
                </div>

                <div v-if="isLoading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="i in homeConfig.featuredCategoriesCount" :key="i"
                        class="h-32 bg-dark-700 rounded-2xl animate-pulse"></div>
                </div>

                <TransitionGroup v-else-if="featuredCategories.length" name="droplet" tag="div"
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <RouterLink v-for="category in visibleCategories" :key="category.id"
                        :to="`/categories/${category.id}`" class="group">
                        <div class="relative group/img overflow-hidden rounded-xl shadow-sm mb-2">
                            <img :src="getCategoryImage(category)" :alt="category.name"
                                class="w-full h-52 object-cover transition-transform duration-500 group-hover:scale-110" />
                            <div
                                class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center">
                                <button
                                    class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center hover:bg-white/40 transition-all transform translate-y-4 group-hover/img:translate-y-0"
                                    @click.prevent="openPreview(getCategoryImage(category))">
                                    <BaseIcon name="eye" :size="24" />
                                </button>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-black group-hover:text-[#9F7A5F] transition-colors">
                                {{ category.name }}
                            </span>
                        </div>
                    </RouterLink>
                </TransitionGroup>

                <div v-else class="text-center text-slate-500">
                    {{ t('common.noCategories') }}
                </div>
                <div class="flex justify-center mt-4">
                    <button v-if="featuredCategories.length > 1" @click="toggleCategories"
                        class="relative flex items-center gap-2 text-[#9F7A5F] font-semibold hover:opacity-80 transition-opacity"
                        type="button">
                        <img src="../../../../assets/landing/home/right.svg" alt="Toggle categories"
                            class="w-10 h-10 transition-transform rotate-90"
                            :class="showAllCategories ? '-rotate-90' : ''" />
                    </button>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section>
            <div class="container">
                <div class="flex items-center justify-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-[#9F7A5F] mb-2">{{ t('common.featuredProducts') }}
                    </h2>
                </div>

                <div v-if="isLoading" class="space-y-16">
                    <div v-for="i in 3" :key="i">
                        <div class="h-8 bg-dark-700 rounded mb-6 w-32 animate-pulse"></div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div v-for="j in 4" :key="j" class="h-80 bg-dark-700 rounded-2xl animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <div v-else-if="productsByCategory.length" class="space-y-16">
                    <div v-for="categoryItem in productsByCategory" :key="categoryItem.category.id">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold text-black flex items-center gap-2">
                                {{ categoryItem.category.name }}
                            </h3>
                            <div class="flex gap-4">
                                <button
                                    class="w-8 h-8 rounded-full bg-white shadow border border-slate-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="categoryItem.products.length <= 3 || (categorySlideIndex[categoryItem.category.id] || 0) === 0"
                                    @click="prevProductSlide(categoryItem.category.id, categoryItem.products.length)">
                                    <img src="../../../../assets/landing/home/arrow.svg" alt="Prev products"
                                        class="rotate-180 w-5 h-5" />
                                </button>
                                <button
                                    class="w-8 h-8 rounded-full bg-white shadow border border-slate-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="categoryItem.products.length <= 3 || (categorySlideIndex[categoryItem.category.id] || 0) >= categoryItem.products.length - 3"
                                    @click="nextProductSlide(categoryItem.category.id, categoryItem.products.length)">
                                    <img src="../../../../assets/landing/home/arrow.svg" alt="Next products"
                                        class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="overflow-hidden">
                                <div class="flex gap-4 sm:gap-6 transition-transform duration-500"
                                    :style="{ transform: `translateX(calc(-${(categorySlideIndex[categoryItem.category.id] || 0)} * (100% / 2 + 1rem)))` }">
                                    <div v-for="product in categoryItem.products" :key="product.id"
                                        class="min-w-[calc(100%/2-0.5rem)] lg:min-w-[calc(100%/3-1rem)]">
                                        <ProductCard :product="product as any" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center text-slate-500 py-8">
                    {{ t('common.noProducts') }}
                </div>
            </div>
        </section>

        <BlogView />
        <ReviewView />

        <!-- CTA Section -->
        <section class="py-8 bg-white">
            <div class="container">
                <div class="flex justify-between gap-12 py-8">
                    <div class="w-1/2">
                        <h2 class="text-2xl md:text-3xl font-bold text-black mb-4">{{ t('home.subscribeTitle') }}</h2>
                        <p class="text-black mb-6 text-md tracking-wide">{{ t('home.subscribeDesc') }}</p>
                    </div>
                    <div class="flex w-1/2 sm:flex-row gap-3 justify-center items-center">
                        <input type="email" :placeholder="t('home.yourEmail')"
                            class="form-input flex-1 h-10 rounded-none border-2 border-[#9F7A5F] bg-white active:border-[#9F7A5F]" />
                        <button class="px-2 whitespace-nowrap h-10 rounded-sm bg-[#9F7A5F]">{{ t('common.subscribe')
                            }}</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-8 md:py-10 bg-white">
            <div class="container">
                <div class="flex justify-between">
                    <div v-for="feature in features" :key="feature.id" class="text-center flex gap-4">
                        <div class="mb-4 flex justify-center">
                            <img :src="feature.icon" :alt="feature.title" class="w-16 h-16" />
                        </div>
                        <div class="flex flex-col items-start justify-center">
                            <h3 class="text-lg font-semibold text-slate-800">{{ t(feature.titleKey) || feature.title }}
                            </h3>
                            <a href="#" class="text-sm text-primary hover:underline">
                                {{ t('common.viewMore') || 'Xem thêm' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Image Modal -->
        <ImageModal v-model="showImageModal" :images="modalImages" :currentIndex="modalInitialIndex" />
    </div>
</template>
