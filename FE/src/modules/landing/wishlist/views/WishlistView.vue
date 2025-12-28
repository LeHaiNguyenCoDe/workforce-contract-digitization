<script setup lang="ts">
/**
 * Wishlist View
 * Uses useWishlist composable for logic separation
 */
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useWishlist } from '../composables/useWishlist'
import httpClient from '@/plugins/api/httpClient'

// Use composable
const {
    wishlistItems,
    isEmpty,
    removeFromWishlist
} = useWishlist()

// Local state for UI
const isLoading = ref(true)
const removingIds = ref<Set<number>>(new Set())

// Format price helper
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

// Fetch wishlist on mount
const fetchWishlist = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/frontend/wishlist')
        const data = response.data as any
        // Update store with fetched data
        if (data?.data?.data) {
            wishlistItems.value = data.data.data
        } else if (Array.isArray(data?.data)) {
            wishlistItems.value = data.data
        }
    } catch (error) {
        console.error('Failed to fetch wishlist:', error)
    } finally {
        isLoading.value = false
    }
}

// Remove item with loading state
const removeItem = async (id: number) => {
    if (removingIds.value.has(id)) return
    
    removingIds.value.add(id)
    try {
        await httpClient.delete(`/frontend/wishlist/${id}`)
        removeFromWishlist(id)
    } catch (error) {
        console.error('Failed to remove from wishlist:', error)
    } finally {
        removingIds.value.delete(id)
    }
}

onMounted(fetchWishlist)
</script>

<template>
    <div class="container py-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-8">Danh sách yêu thích</h1>

        <!-- Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="i in 4" :key="i" class="h-72 bg-dark-700 rounded-2xl animate-pulse"></div>
        </div>

        <!-- Wishlist Grid -->
        <div v-else-if="wishlistItems.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="item in wishlistItems" :key="item.id" class="card relative overflow-hidden">
                <!-- Remove Button -->
                <button 
                    class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-dark-700 text-slate-400 rounded-full hover:bg-error hover:text-white transition-all z-10" 
                    @click="removeItem(item.id)" 
                    :disabled="removingIds.has(item.id)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>

                <!-- Product Image -->
                <RouterLink :to="`/products/${item.product.id}`" class="block aspect-square bg-dark-700 rounded-xl overflow-hidden mb-4">
                    <img 
                        :src="item.product.thumbnail || 'https://placehold.co/200x200/1e293b/64748b?text=No+Image'"
                        :alt="item.product.name" 
                        class="w-full h-full object-cover hover:scale-110 transition-transform duration-500" />
                </RouterLink>

                <!-- Product Info -->
                <RouterLink :to="`/products/${item.product.id}`" class="block font-semibold text-white hover:text-primary-light transition-colors line-clamp-2 mb-2">
                    {{ item.product.name }}
                </RouterLink>
                <p class="text-lg font-bold gradient-text">{{ formatPrice(item.product.price) }}</p>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
            </svg>
            <h3 class="text-xl font-semibold text-slate-400 mb-2">Chưa có sản phẩm yêu thích</h3>
            <p class="text-slate-500 mb-6">Thêm sản phẩm vào danh sách yêu thích để theo dõi</p>
            <RouterLink to="/products" class="btn btn-primary">Khám phá sản phẩm</RouterLink>
        </div>
    </div>
</template>
