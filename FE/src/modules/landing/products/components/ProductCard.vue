<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import type { Product } from '@/plugins/api'
import ImageModal from '@/components/ImageModal.vue'
import { ref } from 'vue'

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

const showImageModal = ref(false)
const openPreview = () => {
    showImageModal.value = true
}
</script>

<template>
    <div class="product-card-container">
        <RouterLink :to="`/products/${product.id}`" class="product-card card">
            <div class="product-image">
                <img :src="product.thumbnail || 'https://placehold.co/300x300/1e293b/64748b?text=No+Image'"
                    :alt="product.name" loading="lazy" />
                <div class="product-overlay">
                    <button class="quick-view-btn" @click.stop.prevent="openPreview" :title="t('product.detail')">
                        <BaseIcon name="eye" :size="20" />
                    </button>
                    <button class="wishlist-btn" @click.stop.prevent :title="t('product.addToWishlist')">
                        <BaseIcon name="heart" :size="20" />
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
                            <BaseIcon 
                                v-for="(filled, index) in ratingStars" 
                                :key="index" 
                                name="star" 
                                :size="14"
                                :fill="filled ? 'currentColor' : 'none'"
                                :class="{ filled }" 
                            />
                        </div>
                        <span class="rating-count">({{ product.rating.count }})</span>
                    </div>
                    <div class="product-price">{{ displayPrice }}</div>
                </div>
            </div>
        </RouterLink>

        <!-- Image Modal -->
        <ImageModal v-model="showImageModal"
            :images="[product.thumbnail || 'https://placehold.co/300x300/1e293b/64748b?text=No+Image']" />
    </div>
</template>
