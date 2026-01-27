<template>
  <div class="marketplace-view page-panel">
    <!-- Hero Banner (Slider + Promo) -->
    <HeroBanner />

    <!-- Popular Categories -->
    <PopularCategories />

    <!-- The Best Offers -->
    <ProductSection 
      title="The Best Offers" 
      :products="bestOffers" 
    />

    <!-- New Goods -->
    <ProductSection 
      title="New Goods" 
      :products="newGoods" 
    />

    <!-- Apple Shopping Event (Large Banner) -->
    <EventBanner />

    <!-- Accessories & Featured -->
    <AccessoriesBanner />

    <!-- Promo Cards (Tiny Banners) -->
    <PromoCards />

    <!-- Articles -->
    <ArticlesCarousel />

    <!-- Store Locations & SEO Info -->
    <StoreLocations />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import HeroBanner from './sections/HeroBanner.vue'
import PopularCategories from './sections/PopularCategories.vue'
import ProductSection from '../components/common/ProductSection.vue'
import EventBanner from './sections/EventBanner.vue'
import AccessoriesBanner from './sections/AccessoriesBanner.vue'
import PromoCards from './sections/PromoCards.vue'
import ArticlesCarousel from './sections/ArticlesCarousel.vue'
import StoreLocations from './sections/StoreLocations.vue'
import { useProductStore } from '../stores/products'

const productStore = useProductStore()
const { bestOffers, newGoods } = storeToRefs(productStore)

onMounted(() => {
  productStore.fetchHomePageData()
})
</script>

<style scoped>
.marketplace-view {
  background-color: #f6f6f6;
}
</style>