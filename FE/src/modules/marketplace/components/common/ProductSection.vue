<template>
  <section class="mkp-section">
    <div class="container-mkp">
      <div class="section-header">
        <h2>{{ title }}</h2>
        <router-link to="/marketplace/shop" class="view-more">
          More Products <i class="fas fa-chevron-right text-[10px]"></i>
        </router-link>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
        <ProductCard 
          v-for="product in products" 
          :key="product.id" 
          :product="product" 
        />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { Product } from '../../types'
import ProductCard from './ProductCard.vue'

defineProps<{
  title: string
  products: Product[]
}>()
</script>
