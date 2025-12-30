<script setup lang="ts">
/**
 * Product List View
 * Uses useProducts composable for logic separation
 */
import { watch, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useSortOptions } from '../configs'
import type { Product } from '../types'

const { t } = useI18n()

// Use composable for all product logic
const {
    products,
    isLoading,
    currentPage,
    totalPages,
    searchQuery,
    selectedCategory,
    sortBy,
    formatPrice,
    setSearch,
    setCategory,
    setSortBy,
    changePage
} = useLandingProducts()

// Categories from store
const { categories } = useLandingCategoryStore()

// Translated sort options
const translatedSortOptions = computed(() => useSortOptions())

// Helpers
const getProductImage = (product: Product) => {
    if (product.thumbnail) return product.thumbnail
    if (product.images?.[0]?.url) return product.images[0].url
    return null
}

// Watch for filter changes
watch([searchQuery, selectedCategory], () => {
    changePage(1)
})
</script>

<template>
    <div class="container py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">{{ t('nav.products') }}</h1>
            <p class="text-slate-400">{{ t('product.exploreThousands') }}</p>
        </div>

        <!-- Filters -->
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div class="flex-1">
                <input v-model="searchQuery" type="text" :placeholder="t('common.search') + '...'" class="form-input"
                    @input="setSearch(($event.target as HTMLInputElement).value)" />
            </div>

            <select v-model="selectedCategory" class="form-input md:w-48"
                @change="setCategory(selectedCategory ? Number(selectedCategory) : null)">
                <option value="">{{ t('common.categories') }}</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>

            <select v-model="sortBy" class="form-input md:w-48" @change="setSortBy(sortBy)">
                <option v-for="opt in translatedSortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}
                </option>
            </select>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="i in 8" :key="i" class="h-80 bg-dark-700 rounded-2xl animate-pulse"></div>
        </div>

        <!-- Products Grid -->
        <div v-else-if="products.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <RouterLink v-for="product in products" :key="product.id" :to="`/products/${product.id}`"
                class="group card hover:scale-[1.02] transition-all duration-300">
                <div class="relative aspect-square bg-dark-700 rounded-xl mb-4 overflow-hidden">
                    <img v-if="getProductImage(product)" :src="getProductImage(product)!" :alt="product.name"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                        </svg>
                    </div>
                    <div v-if="product.discount_percentage"
                        class="absolute top-3 left-3 px-2 py-1 text-xs font-bold text-white bg-secondary rounded-full">
                        -{{ product.discount_percentage }}%
                    </div>
                </div>

                <h3 class="font-semibold text-white mb-2 line-clamp-2 group-hover:text-primary-light transition-colors">
                    {{ product.name }}
                </h3>

                <div class="flex items-center gap-2">
                    <span class="text-lg font-bold gradient-text">{{ formatPrice(product.sale_price || product.price)
                    }}</span>
                    <span v-if="product.sale_price && product.sale_price < product.price"
                        class="text-sm text-slate-500 line-through">
                        {{ formatPrice(product.price) }}
                    </span>
                </div>
            </RouterLink>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
            </svg>
            <h3 class="text-xl font-semibold text-slate-400 mb-2">{{ t('product.notFound') }}</h3>
            <p class="text-slate-500">{{ t('product.tryDifferentSearch') }}</p>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-center gap-4 mt-12">
            <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1" class="btn btn-secondary">
                ← {{ t('common.previous') }}
            </button>
            <span class="text-slate-400">{{ currentPage }} / {{ totalPages }}</span>
            <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
                class="btn btn-secondary">
                {{ t('common.next') }} →
            </button>
        </div>
    </div>
</template>
