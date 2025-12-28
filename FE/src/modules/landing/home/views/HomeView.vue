<script setup lang="ts">
/**
 * Home View
 * Uses useHome composable for logic separation
 */
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useHome } from '../composables/useHome'
import { homeConfig } from '../configs'
import type { Product } from '@/modules/landing/products/types'

const { t } = useI18n()

// Use composable for home data
const {
    featuredProducts,
    featuredCategories,
    isLoading,
    loadHomeData
} = useHome()

const error = ref<string | null>(null)

// Format price helper
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

// Get product image helper
const getProductImage = (product: Product) => {
    if (product.thumbnail) return product.thumbnail
    if (product.images?.[0]?.image_url) return product.images[0].image_url
    return null
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
    <div>
        <!-- Hero Section -->
        <section class="relative py-20 md:py-32 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-glow pointer-events-none"></div>
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary/20 rounded-full blur-3xl opacity-30">
            </div>

            <div class="container relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
                        <span class="text-white">Khám phá </span>
                        <span class="gradient-text">Sản phẩm tuyệt vời</span>
                    </h1>
                    <p class="text-lg md:text-xl text-slate-400 mb-8 animate-slide-up">
                        Mua sắm thông minh với hàng ngàn sản phẩm chất lượng cao và giá cả hợp lý
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up">
                        <RouterLink to="/products" class="btn btn-primary btn-lg">
                            {{ t('common.shopNow') }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14" />
                                <path d="m12 5 7 7-7 7" />
                            </svg>
                        </RouterLink>
                        <RouterLink to="/promotions" class="btn btn-outline btn-lg">
                            {{ t('nav.promotions') }}
                        </RouterLink>
                    </div>
                </div>
            </div>
        </section>

        <!-- Error Message -->
        <div v-if="error" class="container py-8">
            <div class="bg-error/10 border border-error text-error p-4 rounded-xl text-center">
                {{ error }} - <button @click="handleRetry" class="underline">Thử lại</button>
            </div>
        </div>

        <!-- Categories Section -->
        <section class="py-16 md:py-24">
            <div class="container">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ t('common.categories') }}</h2>
                    <p class="text-slate-400">Khám phá các danh mục sản phẩm đa dạng</p>
                </div>

                <div v-if="isLoading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div v-for="i in homeConfig.featuredCategoriesCount" :key="i" class="h-32 bg-dark-700 rounded-2xl animate-pulse"></div>
                </div>

                <div v-else-if="featuredCategories.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <RouterLink v-for="category in featuredCategories" :key="category.id" :to="`/categories/${category.id}`"
                        class="group relative h-32 bg-dark-800 rounded-2xl border border-white/10 hover:border-primary/50 transition-all duration-300 overflow-hidden flex items-center justify-center">
                        <div class="absolute inset-0 bg-gradient-primary opacity-0 group-hover:opacity-10 transition-opacity"></div>
                        <span class="font-semibold text-slate-200 group-hover:text-white transition-colors">{{ category.name }}</span>
                    </RouterLink>
                </div>

                <div v-else class="text-center text-slate-500">
                    Chưa có danh mục nào
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="py-16 md:py-24 bg-dark-800/50">
            <div class="container">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-2">{{ t('common.featuredProducts') }}</h2>
                        <p class="text-slate-400">Sản phẩm được yêu thích nhất</p>
                    </div>
                    <RouterLink to="/products" class="btn btn-outline">
                        {{ t('common.viewAll') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg>
                    </RouterLink>
                </div>

                <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="i in 4" :key="i" class="h-80 bg-dark-700 rounded-2xl animate-pulse"></div>
                </div>

                <div v-else-if="featuredProducts.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <RouterLink v-for="product in featuredProducts" :key="product.id" :to="`/products/${product.id}`"
                        class="group card hover:scale-[1.02] transition-all duration-300">
                        <!-- Product Image -->
                        <div class="relative aspect-square bg-dark-700 rounded-xl mb-4 overflow-hidden">
                            <img v-if="getProductImage(product)" :src="getProductImage(product)!" :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1">
                                    <rect width="18" height="18" x="3" y="3" rx="2" />
                                    <circle cx="9" cy="9" r="2" />
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                </svg>
                            </div>
                            <div v-if="product.discount_percentage"
                                class="absolute top-3 left-3 px-2 py-1 text-xs font-bold text-white bg-secondary rounded-full">
                                -{{ product.discount_percentage }}%
                            </div>
                        </div>

                        <!-- Product Info -->
                        <h3 class="font-semibold text-white mb-2 line-clamp-2 group-hover:text-primary-light transition-colors">
                            {{ product.name }}
                        </h3>

                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold gradient-text">{{ formatPrice(product.sale_price || product.price) }}</span>
                            <span v-if="product.sale_price && product.sale_price < product.price"
                                class="text-sm text-slate-500 line-through">
                                {{ formatPrice(product.price) }}
                            </span>
                        </div>
                    </RouterLink>
                </div>

                <div v-else class="text-center text-slate-500 py-8">
                    Chưa có sản phẩm nào
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20">
            <div class="container">
                <div class="relative bg-dark-800 rounded-3xl p-8 md:p-12 overflow-hidden border border-white/10">
                    <div class="absolute inset-0 bg-gradient-primary opacity-10"></div>
                    <div class="absolute top-0 right-0 w-96 h-96 bg-primary/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>

                    <div class="relative z-10 max-w-2xl">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Đăng ký nhận thông báo</h2>
                        <p class="text-slate-400 mb-6">Nhận thông tin khuyến mãi và sản phẩm mới nhất</p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="email" placeholder="Email của bạn" class="form-input flex-1" />
                            <button class="btn btn-primary whitespace-nowrap">Đăng ký</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
