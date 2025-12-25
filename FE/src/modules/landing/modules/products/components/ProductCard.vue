<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import type { Product } from '@/plugins/api'

const { t } = useI18n()

const props = defineProps<{
    product: Product
}>()

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const displayPrice = computed(() => formatPrice(props.product.price))

const ratingStars = computed(() => {
    const rating = props.product.rating?.avg || 0
    return Array.from({ length: 5 }, (_, i) => i < Math.round(rating))
})
</script>

<template>
    <RouterLink :to="`/products/${product.id}`" class="product-card card">
        <div class="product-image">
            <img :src="product.thumbnail || 'https://placehold.co/300x300/1e293b/64748b?text=No+Image'"
                :alt="product.name" loading="lazy" />
            <div class="product-overlay">
                <button class="quick-view-btn" @click.prevent :title="t('product.detail')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
                <button class="wishlist-btn" @click.prevent :title="t('product.addToWishlist')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path
                            d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="product-info">
            <div class="product-category" v-if="product.category">
                {{ product.category.name }}
            </div>
            <h3 class="product-name">{{ product.name }}</h3>
            <div class="product-footer">
                <div class="product-rating" v-if="product.rating">
                    <div class="stars">
                        <svg v-for="(filled, index) in ratingStars" :key="index" :class="{ filled }"
                            xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                            fill="currentColor">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                    </div>
                    <span class="rating-count">({{ product.rating.count }})</span>
                </div>
                <div class="product-price">{{ displayPrice }}</div>
            </div>
        </div>
    </RouterLink>
</template>

<style scoped>
.product-card {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 0;
}

.product-image {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--color-bg-tertiary);
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-slow);
}

.product-card:hover .product-image img {
    transform: scale(1.08);
}

.product-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-3);
    background: rgba(0, 0, 0, 0.5);
    opacity: 0;
    transition: opacity var(--transition-normal);
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.quick-view-btn,
.wishlist-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: var(--color-bg-primary);
    border: none;
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: all var(--transition-normal);
}

.quick-view-btn:hover,
.wishlist-btn:hover {
    background: var(--color-primary);
    color: white;
}

.product-info {
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.product-category {
    font-size: var(--text-xs);
    color: var(--color-primary);
    text-transform: uppercase;
}

.product-name {
    font-size: var(--text-base);
    font-weight: 600;
    color: var(--color-text-primary);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.stars {
    display: flex;
    gap: 2px;
}

.stars svg {
    color: var(--color-text-muted);
}

.stars svg.filled {
    color: var(--color-warning);
}

.rating-count {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
}

.product-price {
    font-size: var(--text-lg);
    font-weight: 700;
    color: var(--color-secondary);
}
</style>
