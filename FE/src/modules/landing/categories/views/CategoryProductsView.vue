<script setup lang="ts">
/**
 * Category Products View
 * Uses useCategories composable for category logic
 */
import { ref, watch, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import httpClient from '@/plugins/api/httpClient'
import type { Product } from '@/modules/landing/products/types'

const route = useRoute()

// Local state
const products = ref<Product[]>([])
const category = ref<any>(null)
const isLoading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)

// Format price helper
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

// Get product image
const getProductImage = (product: Product) => {
    if (product.thumbnail) return product.thumbnail
    if (product.images?.[0]?.url) return product.images[0].url
    return null
}

// Fetch category and products
const fetchCategoryAndProducts = async () => {
    isLoading.value = true
    const categoryId = route.params.id

    try {
        // Fetch category info
        const catResponse = await httpClient.get(`/categories/${categoryId}`)
        const catData = catResponse.data as any
        category.value = catData?.data || catData

        // Fetch products in category
        const productsResponse = await httpClient.get('/products', {
            params: { category_id: categoryId, page: currentPage.value, per_page: 12 }
        })
        const productsData = productsResponse.data as any

        if (productsData?.data?.data && Array.isArray(productsData.data.data)) {
            products.value = productsData.data.data
            totalPages.value = productsData.data.last_page || 1
        } else if (Array.isArray(productsData?.data)) {
            products.value = productsData.data
        } else {
            products.value = []
        }
    } catch (error) {
        console.error('Failed to fetch category products:', error)
        products.value = []
    } finally {
        isLoading.value = false
    }
}

// Pagination
const changePage = (page: number) => {
    currentPage.value = page
    fetchCategoryAndProducts()
}

// Watch route changes
watch(() => route.params.id, () => {
    currentPage.value = 1
    fetchCategoryAndProducts()
})

onMounted(fetchCategoryAndProducts)
</script>

<template>
    <div class="min-h-screen">
        <!-- Breadcrumb -->
        <div class="bg-dark-800/50 border-b border-white/10">
            <div class="container py-4">
                <nav class="flex items-center gap-2 text-sm">
                    <RouterLink to="/" class="text-slate-400 hover:text-white transition-colors">Trang chủ</RouterLink>
                    <span class="text-slate-600">/</span>
                    <RouterLink to="/products" class="text-slate-400 hover:text-white transition-colors">Sản phẩm
                    </RouterLink>
                    <span class="text-slate-600">/</span>
                    <span class="text-white">{{ category?.name || 'Danh mục' }}</span>
                </nav>
            </div>
        </div>

        <!-- Category Header -->
        <section class="py-12">
            <div class="container">
                <div class="text-center max-w-2xl mx-auto">
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        {{ category?.name || 'Danh mục' }}
                    </h1>
                    <p v-if="category?.description" class="text-slate-400">{{ category.description }}</p>
                </div>
            </div>
        </section>

        <!-- Products -->
        <section class="pb-16">
            <div class="container">
                <!-- Loading -->
                <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="i in 8" :key="i" class="bg-dark-800 rounded-2xl h-80 animate-pulse"></div>
                </div>

                <!-- Products Grid -->
                <div v-else-if="products.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <RouterLink v-for="product in products" :key="product.id" :to="`/products/${product.id}`"
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
                        <h3
                            class="font-semibold text-white mb-2 line-clamp-2 group-hover:text-primary-light transition-colors">
                            {{ product.name }}
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold gradient-text">{{ formatPrice(product.sale_price ||
                                product.price) }}</span>
                            <span v-if="product.sale_price && product.sale_price < product.price"
                                class="text-sm text-slate-500 line-through">
                                {{ formatPrice(product.price) }}
                            </span>
                        </div>
                    </RouterLink>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16 bg-dark-800/50 rounded-2xl border border-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 mx-auto text-slate-600 mb-6"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="m7.5 4.27 9 5.15" />
                        <path
                            d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">Không có sản phẩm</h3>
                    <p class="text-slate-400 mb-6">Danh mục này chưa có sản phẩm nào</p>
                    <RouterLink to="/products" class="btn btn-primary">
                        Xem tất cả sản phẩm
                    </RouterLink>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-center gap-4 mt-12">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1" class="btn btn-secondary"
                        :class="{ 'opacity-50 cursor-not-allowed': currentPage <= 1 }">
                        ← Trước
                    </button>
                    <span class="text-slate-400">{{ currentPage }} / {{ totalPages }}</span>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
                        class="btn btn-secondary"
                        :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
                        Sau →
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
