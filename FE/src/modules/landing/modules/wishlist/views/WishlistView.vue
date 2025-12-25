<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import apiClient from '@/plugins/api/httpClient'
import type { WishlistItem, ApiResponse } from '@/api/types'

const wishlist = ref<WishlistItem[]>([])
const isLoading = ref(true)
const removingIds = ref<Set<number>>(new Set())

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const fetchWishlist = async () => {
    isLoading.value = true
    try {
        const response = await apiClient.get<ApiResponse<{ data: WishlistItem[] }>>('/frontend/wishlist')
        wishlist.value = response.data.data?.data || []
    } catch (error) {
        console.error('Failed to fetch wishlist:', error)
    } finally {
        isLoading.value = false
    }
}

const removeItem = async (id: number) => {
    if (removingIds.value.has(id)) return

    removingIds.value.add(id)
    try {
        await apiClient.delete(`/frontend/wishlist/${id}`)
        wishlist.value = wishlist.value.filter(item => item.id !== id)
    } catch (error) {
        console.error('Failed to remove from wishlist:', error)
    } finally {
        removingIds.value.delete(id)
    }
}

onMounted(fetchWishlist)
</script>

<template>
    <div class="wishlist-page">
        <div class="container">
            <h1>Danh sách yêu thích</h1>

            <div v-if="isLoading" class="loading-grid">
                <div v-for="i in 4" :key="i" class="skeleton-card"></div>
            </div>

            <div v-else-if="wishlist.length" class="wishlist-grid">
                <div v-for="item in wishlist" :key="item.id" class="wishlist-card card">
                    <RouterLink :to="`/products/${item.product.id}`" class="product-image">
                        <img :src="item.product.thumbnail || 'https://placehold.co/200x200/1e293b/64748b?text=No+Image'"
                            :alt="item.product.name" />
                    </RouterLink>
                    <div class="product-info">
                        <RouterLink :to="`/products/${item.product.id}`" class="product-name">
                            {{ item.product.name }}
                        </RouterLink>
                        <p class="product-price">{{ formatPrice(item.product.price) }}</p>
                    </div>
                    <button class="remove-btn" @click="removeItem(item.id)" :disabled="removingIds.has(item.id)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-else class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                </svg>
                <h3>Chưa có sản phẩm yêu thích</h3>
                <p>Thêm sản phẩm vào danh sách yêu thích để theo dõi</p>
                <RouterLink to="/products" class="btn btn-primary">Khám phá sản phẩm</RouterLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
.wishlist-page h1 {
    margin-bottom: var(--space-8);
}

.loading-grid,
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: var(--space-6);
}

.skeleton-card {
    height: 280px;
    background: var(--color-bg-tertiary);
    border-radius: var(--radius-lg);
    animation: pulse 2s infinite;
}

.wishlist-card {
    position: relative;
    padding: 0;
    overflow: hidden;
}

.product-image {
    aspect-ratio: 1;
    display: block;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    padding: var(--space-4);
}

.product-name {
    font-weight: 500;
    color: var(--color-text-primary);
    display: block;
    margin-bottom: var(--space-2);
}

.product-name:hover {
    color: var(--color-primary);
}

.product-price {
    font-weight: 700;
    color: var(--color-secondary);
}

.remove-btn {
    position: absolute;
    top: var(--space-3);
    right: var(--space-3);
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-bg-secondary);
    color: var(--color-text-muted);
    border: none;
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.remove-btn:hover {
    background: var(--color-error);
    color: white;
}

.empty-state {
    text-align: center;
    padding: var(--space-16);
}

.empty-state svg {
    color: var(--color-text-muted);
    margin-bottom: var(--space-6);
}

.empty-state h3 {
    margin-bottom: var(--space-2);
}

.empty-state p {
    margin-bottom: var(--space-6);
    color: var(--color-text-muted);
}
</style>
