<script setup lang="ts">
/**
 * Product List View - Redesigned
 */
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useLandingProducts } from '../composables/useLandingProducts'
import { useSortOptions } from '../configs'
import type { Product } from '../types'
import ProductCard from '../components/ProductCard.vue'

const { t } = useI18n()

// Use composable for all product logic
const {
    products,
    isLoading,
    currentPage,
    totalPages,
    categories,
    selectedCategories,
    selectedBrands,
    selectedDimensions,
    selectedColor,
    sortBy,
    formatPrice,
    toggleCategory,
    toggleBrand,
    toggleDimension,
    setColor,
    setSortBy,
    changePage
} = useLandingProducts()

// Demo data for filters (mockup values)
const brands = ref(['Gốm Sứ Lạc Hồng', 'Khác'])
const dimensions = ref(['10cm', '20cm', '30cm', '40cm'])
const colorSwatches = [
    { name: 'Hồng', color: '#E8A3B7' },
    { name: 'Đen', color: '#000000' },
    { name: 'Trắng', color: '#FFFFFF' },
    { name: 'Đỏ', color: '#F25C5C' },
    { name: 'Tím', color: '#A061D1' },
    { name: 'Xanh lá', color: '#7CC674' },
    { name: 'Xanh', color: '#2B4B8B' },
    { name: 'Cam', color: '#F28A4B' },
    { name: 'Nâu', color: '#634A35' }
]

const translatedSortOptions = computed(() => useSortOptions())

// Filter expansion state
const showAllCategories = ref(false)
const showAllDimensions = ref(false)
const showAllColors = ref(false)

const getProductImage = (product: Product) => {
    if (product.thumbnail) return product.thumbnail
    if (product.images?.[0]?.image_url) return product.images[0].image_url
    return null
}
</script>

<template>
    <div class="product-list-view bg-white min-h-screen">
        <div class="container mx-auto px-4 py-6">
            <!-- Breadcrumbs -->
            <nav class="flex text-sm text-gray-500 mb-6">
                <RouterLink to="/" class="hover:text-gray-800">{{ t('common.home') }}</RouterLink>
                <span class="mx-2">/</span>
                <span class="text-gray-800 font-medium">{{ t('product.allProducts') }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Filters -->
                <aside class="w-full lg:w-64 flex-shrink-0">
                    <h1 class="text-2xl font-semibold mb-8 text-gray-800">Tất cả sản phẩm</h1>

                    <!-- Category Filter -->
                    <div class="mb-10">
                        <h3 class="text-xs font-bold uppercase tracking-wider mb-4 text-gray-800">LOẠI SẢN PHẨM</h3>
                        <div class="space-y-3">
                            <label v-for="cat in (showAllCategories ? categories : categories.slice(0, 5))"
                                :key="cat.id" class="flex items-center group cursor-pointer">
                                <input type="checkbox" :checked="selectedCategories.includes(cat.id)"
                                    class="w-4 h-4 border-gray-300 rounded text-[#9F7A5F] focus:ring-[#9F7A5F]"
                                    @change="toggleCategory(cat.id)" />
                                <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-900">{{ cat.name }}</span>
                            </label>
                            <button v-if="categories.length > 5" @click="showAllCategories = !showAllCategories"
                                class="text-xs text-gray-400 hover:text-gray-600 underline mt-2 block mx-auto py-1">
                                {{ showAllCategories ? 'Ẩn bớt' : 'Xem thêm' }}
                            </button>
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="mb-10 border-t border-gray-100 pt-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider mb-4 text-gray-800">THƯƠNG HIỆU</h3>
                        <div class="space-y-3">
                            <label v-for="brand in brands" :key="brand" class="flex items-center group cursor-pointer">
                                <input type="checkbox" :checked="selectedBrands.includes(brand)"
                                    class="w-4 h-4 border-gray-300 rounded text-[#9F7A5F] focus:ring-[#9F7A5F]"
                                    @change="toggleBrand(brand)" />
                                <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-900">{{ brand }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Dimension Filter -->
                    <div class="mb-10 border-t border-gray-100 pt-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider mb-4 text-gray-800 leading-tight">ĐƯỜNG
                            KÍNH / CHIỀU CAO TỐI ĐA</h3>
                        <div class="space-y-3">
                            <label v-for="dim in (showAllDimensions ? dimensions : dimensions.slice(0, 5))" :key="dim"
                                class="flex items-center group cursor-pointer">
                                <input type="checkbox" :checked="selectedDimensions.includes(dim)"
                                    class="w-4 h-4 border-gray-300 rounded text-[#9F7A5F] focus:ring-[#9F7A5F]"
                                    @change="toggleDimension(dim)" />
                                <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-900">{{ dim }}</span>
                            </label>
                            <button v-if="dimensions.length > 5" @click="showAllDimensions = !showAllDimensions"
                                class="text-xs text-gray-400 hover:text-gray-600 underline mt-2 block mx-auto py-1">
                                {{ showAllDimensions ? 'Ẩn bớt' : 'Xem thêm' }}
                            </button>
                        </div>
                    </div>

                    <!-- Color Filter -->
                    <div class="mb-10 border-t border-gray-100 pt-6">
                        <div class="grid grid-cols-3 gap-y-6 gap-x-2">
                            <button v-for="swatch in (showAllColors ? colorSwatches : colorSwatches.slice(0, 6))"
                                :key="swatch.name" @click="setColor(selectedColor === swatch.name ? null : swatch.name)"
                                class="flex flex-col items-center gap-1.5 group">
                                <div :style="{ backgroundColor: swatch.color }"
                                    class="w-5 h-5 rounded-full border border-gray-200 group-hover:scale-110 transition-transform"
                                    :class="{ 'ring-2 ring-offset-2 ring-[#9F7A5F]': selectedColor === swatch.name }">
                                </div>
                                <span class="text-[10px] text-gray-500 text-center">{{ swatch.name }}</span>
                            </button>
                        </div>
                        <button v-if="colorSwatches.length > 6" @click="showAllColors = !showAllColors"
                            class="text-xs text-gray-400 hover:text-gray-600 underline mt-6 block mx-auto py-1">
                            {{ showAllColors ? 'Ẩn bớt' : 'Xem thêm' }}
                        </button>
                    </div>

                    <!-- Price Filter Mockup -->
                    <div class="mb-10 border-t border-gray-100 pt-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider mb-4 text-gray-800 leading-tight">GIÁ SẢN
                            PHẨM</h3>
                        <!-- Placeholder for price range input if needed -->
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-1">
                    <!-- Top Bar -->
                    <div class="flex justify-end items-center mb-8">
                        <div class="flex items-center gap-4 text-sm">
                            <span class="text-gray-600">Sắp xếp theo:</span>
                            <div class="relative min-w-[140px]">
                                <select v-model="sortBy" @change="setSortBy(sortBy)"
                                    class="appearance-none w-full bg-white border border-gray-200 text-gray-700 py-1.5 px-4 pr-8 rounded focus:outline-none focus:border-[#9F7A5F] text-xs">
                                    <option v-for="opt in translatedSortOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div v-if="isLoading" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-12">
                        <div v-for="i in 8" :key="i" class="animate-pulse">
                            <div class="aspect-square bg-gray-100 rounded mb-4"></div>
                            <div class="h-4 bg-gray-100 rounded w-3/4 mb-2"></div>
                            <div class="h-4 bg-gray-100 rounded w-1/2"></div>
                        </div>
                    </div>

                    <div v-else-if="products.length"
                        class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-12">
                        <div v-for="product in products" :key="product.id">
                            <ProductCard :product="product as any" />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-20 bg-gray-50 rounded-xl">
                        <h3 class="text-lg font-medium text-gray-800">{{ t('product.notFound') }}</h3>
                        <p class="text-gray-500 text-sm mt-2">{{ t('product.tryDifferentSearch') }}</p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="totalPages > 1" class="mt-16 flex items-center justify-center gap-2">
                        <button v-for="page in totalPages" :key="page" @click="changePage(page)"
                            class="w-7 h-7 flex items-center justify-center text-xs rounded-full transition-colors"
                            :class="page === currentPage ? 'bg-[#9D0A0E] text-white' : 'text-gray-400 hover:bg-gray-100'">
                            {{ page }}
                        </button>
                        <button v-if="currentPage < totalPages" @click="changePage(currentPage + 1)"
                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </button>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
