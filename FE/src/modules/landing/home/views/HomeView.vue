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
const visibleCategories = computed(() => {
    const list = Array.isArray(featuredCategories) ? featuredCategories : featuredCategories.value
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

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + heroImages.length) % heroImages.length
    startSlider()
}

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % heroImages.length
    startSlider()
}

onMounted(startSlider)
onUnmounted(stopSlider)

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
        categories.forEach((item) => {
            if (item?.category?.id != null) {
                initial[item.category.id] = 0
            }
        })
        categorySlideIndex.value = initial
    },
    { immediate: true }
)

const prevProductSlide = (categoryId: number, totalProducts: number) => {
    if (totalProducts <= 3) return
    const current = categorySlideIndex.value[categoryId] || 0
    //const maxIndex = totalProducts - 3
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
</script>

<template>
    <div class="bg-white">
        <!-- Hero Section -->
        <div class="container hidden py-4 gap-2 text-sm md:flex">
            <RouterLink to="/" class="px-2 text-black font-poppins rounded-md hover:text-black transition-all">Phòng tắm</RouterLink>
            <RouterLink to="/" class="px-2 text-black font-poppins rounded-md hover:text-black transition-all">Phòng ngủ</RouterLink>
            <RouterLink to="/" class="px-2 text-black font-poppins rounded-md hover:text-black transition-all">Phòng bếp</RouterLink>
            <RouterLink to="/" class="px-2 text-black font-poppins rounded-md hover:text-black transition-all">Phòng làm việc</RouterLink>
            <RouterLink to="/" class="px-2 text-black font-poppins rounded-md hover:text-black transition-all">Phòng khách</RouterLink>
        </div>
        <section class="relative overflow-hidden min-h-[520px] md:min-h-[680px] flex items-center">
            <div class="absolute inset-0">
                <Transition enter-active-class="duration-700 ease-out" enter-from-class="opacity-0"
                    enter-to-class="opacity-100" leave-active-class="duration-700 ease-in"
                    leave-from-class="opacity-100" leave-to-class="opacity-0" mode="out-in">
                    <div :key="currentSlide" class="absolute inset-0">
                        <img :src="heroImages[currentSlide]" alt="" class="w-full h-full object-cover" />
                    </div>
                </Transition>
            </div>
            <div
                class="absolute top-1/2 left-1/2 h-[680px]">
            </div>

            <div class="container relative z-10 h-full flex flex-col">
                <div class="max-w-xl mx-auto text-center -mt-72">
                    <p class="text-4xl font-bold text-slate-200 mb-8 animate-slide-up">
                        {{ t('home.heroDescription') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up">
                        <RouterLink to="/products" class="button rounded-sm border border-white px-2 py-1">
                            {{ t('common.shopNow') }}
                        </RouterLink>
                    </div>
                </div>
            </div>

            <button @click="prevSlide"
                class="hidden md:inline-flex absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/80 hover:bg-white shadow-lg border border-white/60 transition-all">
                <img src="../../../../assets/landing/home/left.svg" alt="Previous" />
            </button>
            <button @click="nextSlide"
                class="hidden md:inline-flex absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/80 hover:bg-white shadow-lg border border-white/60 transition-all">
                <img src="../../../../assets/landing/home/right.svg" alt="Next" />
            </button>
        </section>

        <!-- Error Message -->
        <div v-if="error" class="container py-8">
            <div class="bg-error/10 border border-error text-error p-4 rounded-xl text-center">
                {{ error }} - <button @click="handleRetry" class="underline">{{ t('common.retry') }}</button>
            </div>
        </div>

        <!-- About Section -->
        <section class="py-8 bg-white">
            <div class="container flex flex-col justify-center items-center">
                <h2 class="text-3xl md:text-4xl font-bold text-[#9F7A5F] mb-6">{{ t('home.aboutTitle') || 'Giới thiệu' }}</h2>
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
        <section ref="categoriesSection" class="py-16 md:py-24 bg-white">
            <div class="container">
                <div class="flex items-center justify-between mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-[#9F7A5F]">{{ t('common.categories') }}</h2>
    
                </div>

                <div v-if="isLoading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="i in homeConfig.featuredCategoriesCount" :key="i"
                        class="h-32 bg-dark-700 rounded-2xl animate-pulse"></div>
                </div>

                <TransitionGroup
                    v-else-if="featuredCategories.length"
                    name="droplet"
                    tag="div"
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                >
                    <RouterLink
                        v-for="category in visibleCategories"
                        :key="category.id"
                        :to="`/categories/${category.id}`"
                        class="group"
                    >
                        <img
                            :src="getCategoryImage(category)"
                            :alt="category.name"
                            class="w-full h-52 object-cover mb-2 rounded-xl shadow-sm"
                        />
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
                    <button
                        v-if="featuredCategories.length > 1"
                        @click="toggleCategories"
                        class="relative flex items-center gap-2 text-[#9F7A5F] font-semibold hover:opacity-80 transition-opacity"
                        type="button"
                    >
                        <img
                            src="../../../../assets/landing/home/right.svg"
                            alt="Toggle categories"
                            class="w-10 h-10 transition-transform rotate-90"
                            :class="showAllCategories ? '-rotate-90' : ''"
                        />
                    </button>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="py-16 md:py-24">
            <div class="container">
                <div class="flex items-center justify-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-bold text-[#9F7A5F] mb-2">{{ t('common.featuredProducts') }}</h2>
                </div>

                <!-- Loop through each category with products -->
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
                        <!-- Category Title -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold text-black flex items-center gap-2">
                                {{ categoryItem.category.name }}
                            </h3>
                            <div class="flex gap-4">
                                <button
                                    class="w-8 h-8 rounded-full bg-white shadow border border-slate-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="categoryItem.products.length <= 3 || (categorySlideIndex[categoryItem.category.id] || 0) === 0"
                                    @click="prevProductSlide(categoryItem.category.id, categoryItem.products.length)">
                                    <img src="../../../../assets/landing/home/arrow.svg" alt="Prev products" class="rotate-180 w-5 h-5"/>
                                </button>
                                <button
                                    class="w-8 h-8 rounded-full bg-white shadow border border-slate-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="categoryItem.products.length <= 3 || (categorySlideIndex[categoryItem.category.id] || 0) >= categoryItem.products.length - 3"
                                    @click="nextProductSlide(categoryItem.category.id, categoryItem.products.length)">
                                    <img src="../../../../assets/landing/home/arrow.svg" alt="Next products" class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Products Slider -->
                        <div class="relative">
                            <div class="overflow-hidden">
                                <div
                                    class="flex gap-4 sm:gap-6 transition-transform duration-500"
                                    :style="{ transform: `translateX(calc(-${(categorySlideIndex[categoryItem.category.id] || 0)} * (100% / 2 + 1rem)))` }">
                                    <RouterLink v-for="product in categoryItem.products" :key="product.id"
                                        :to="`/products/${product.id}`"
                                        class="min-w-[calc(100%/2-0.5rem)] lg:min-w-[calc(100%/3-1rem)] hover:scale-[1.02] transition-all duration-300">
                                        <!-- Product Image -->
                                        <div class="relative aspect-square rounded-sm mb-4 overflow-hidden">
                                            <img v-if="getProductImage(product)" :src="getProductImage(product)!" :alt="product.name"
                                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                            <div v-else class="w-full h-full flex items-center justify-center text-slate-600"></div>
                                        </div>

                                        <div class="flex items-start gap-2 justify-between px-2">
                                            <h3 class="font-semibold text-xs md:text-base text-black group-hover:text-[#9F7A5F]">
                                                {{ product.name }}
                                            </h3>
                                            <span class="text-sm font-bold text-black">{{ formatPrice(product.sale_price ||
                                                product.price) }}</span>
                                            <span v-if="product.sale_price && product.sale_price < product.price"
                                                class="text-sm text-slate-500 line-through">
                                                {{ formatPrice(product.price) }}
                                            </span>
                                        </div>
                                    </RouterLink>
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
        <section class="bg-white">
            <div class="container">
                <div class="flex justify-between gap-12 py-8">
                    <div class="w-1/2">
                        <h2 class="text-2xl md:text-3xl font-bold text-black mb-4">{{ t('home.subscribeTitle') }}</h2>
                        <p class="text-black mb-6 text-md tracking-wide">{{ t('home.subscribeDesc') }}</p>
                    </div>
                    <div class="flex w-1/2 sm:flex-row gap-3 justify-center items-center">
                        <input type="email" :placeholder="t('home.yourEmail')" class="form-input flex-1 h-10 rounded-none border-2 border-[#9F7A5F] bg-white active:border-[#9F7A5F]" />
                        <button class="px-2 whitespace-nowrap h-10 rounded-sm bg-[#9F7A5F]">{{ t('common.subscribe') }}</button>
                    </div>
                </div>
                <div class="py-4">
                    
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
                            <h3 class="text-lg font-semibold text-slate-800">{{ t(feature.titleKey) || feature.title }}</h3>
                            <a href="#" class="text-sm text-primary hover:underline">{{ t('common.viewMore') || 'Xem thêm' }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.droplet-enter-active,
.droplet-leave-active {
    transition: all 320ms ease;
}

.droplet-enter-from,
.droplet-leave-to {
    opacity: 0;
    transform: translateY(-16px) scale(0.94);
    filter: drop-shadow(0 18px 24px rgba(159, 122, 95, 0.12));
    clip-path: ellipse(55% 65% at 50% 32%);
}

.droplet-indicator {
    width: 28px;
    height: 36px;
    border-radius: 55% 55% 65% 65%;
    background: linear-gradient(165deg, #f5ede7 0%, #e7d4c7 35%, #c7a78c 72%, #9f7a5f 100%);
    box-shadow: 0 10px 24px rgba(159, 122, 95, 0.35);
    position: relative;
    isolation: isolate;
    transition: transform 260ms ease, filter 260ms ease;
}

.droplet-indicator::after {
    content: '';
    position: absolute;
    inset: 4px 6px auto 6px;
    height: 14px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    filter: blur(1px);
}

.droplet-indicator--up {
    transform: rotate(180deg) translateY(-2px);
    filter: drop-shadow(0 -10px 20px rgba(159, 122, 95, 0.25));
}

button:active .droplet-indicator {
    animation: droplet-bounce 480ms ease;
}

@keyframes droplet-bounce {
    0% {
        transform: translateY(-2px) scale(0.96);
    }
    45% {
        transform: translateY(6px) scale(1.04);
    }
    100% {
        transform: translateY(0) scale(1);
    }
}
</style>
