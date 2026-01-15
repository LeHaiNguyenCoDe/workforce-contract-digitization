<script setup lang="ts">
import ProductCard from './ProductCard.vue'
import type { Product, SectionConfig } from '../types'

// Props
interface Props {
  products: Product[]
  config?: SectionConfig
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  config: () => ({
    title: 'Products',
    seeAllLink: '#',
    showArrows: true
  })
})

// Emit events
const emit = defineEmits<{
  (e: 'add-to-cart', product: Product): void
  (e: 'toggle-favorite', product: Product): void
  (e: 'see-all'): void
}>()

const handleAddToCart = (product: Product) => {
  emit('add-to-cart', product)
}

const handleToggleFavorite = (product: Product) => {
  emit('toggle-favorite', product)
}
</script>

<template>
  <section class="foodie-products-section">
    <div class="foodie-container">
      <!-- Section Header -->
      <div class="foodie-products-section__header">
        <h2 class="foodie-products-section__title">{{ config.title }}</h2>
        <div class="foodie-products-section__actions">
          <a 
            v-if="config.seeAllLink" 
            :href="config.seeAllLink" 
            class="foodie-products-section__see-all"
            @click.prevent="emit('see-all')"
          >
            See All
          </a>
          <div v-if="config.showArrows" class="foodie-products-section__arrows">
            <button class="foodie-products-section__arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
              </svg>
            </button>
            <button class="foodie-products-section__arrow foodie-products-section__arrow--active">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="foodie-products-section__grid">
        <!-- Loading State -->
        <template v-if="loading">
          <div v-for="n in 6" :key="n" class="foodie-product-card foodie-product-card--loading">
            <div class="foodie-product-card__image skeleton"></div>
            <div class="foodie-product-card__info">
              <div class="skeleton skeleton--text"></div>
              <div class="skeleton skeleton--text skeleton--short"></div>
            </div>
          </div>
        </template>

        <!-- Data State -->
        <template v-else>
          <ProductCard 
            v-for="product in products"
            :key="product.id"
            :product="product"
            @add-to-cart="handleAddToCart"
            @toggle-favorite="handleToggleFavorite"
          />
        </template>
      </div>
    </div>
  </section>
</template>
