<script lang="ts">
import { defineComponent, ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import type { CategoryItem, Product, Restaurant } from '../types'
import { useFoodieSearch } from '../composables/useFoodieSearch'

// Layout Components
import FoodieHeader from '../components/FoodieHeader.vue'
import FoodieFooter from '../components/FoodieFooter.vue'
import FoodieSearchOverlay from '../components/FoodieSearchOverlay.vue'

// Section Components
import HeroSection from '../components/HeroSection.vue'
import CategorySlider from '../components/CategorySlider.vue'
import ProductsSection from '../components/ProductsSection.vue'
import RestaurantsSection from '../components/RestaurantsSection.vue'
import CocktailBanner from '../components/CocktailBanner.vue'
import DraftBrewsBanner from '../components/DraftBrewsBanner.vue'
import SearchSection from '../components/SearchSection.vue'

import { useFoodieHome } from '../composables/useHome'

export default defineComponent({
  name: 'FoodieHomeView',
  components: {
    FoodieHeader,
    FoodieFooter,
    FoodieSearchOverlay,
    HeroSection,
    CategorySlider,
    ProductsSection,
    RestaurantsSection,
    CocktailBanner,
    DraftBrewsBanner,
    SearchSection
  },
  setup() {
    const { t } = useI18n()
    const { isSearchOpen, closeSearch } = useFoodieSearch()

    const {
      categories,
      popularDrinks,
      partyFavourites,
      restaurants,
      isLoading
    } = useFoodieHome()

    const handleCategorySelect = (category: CategoryItem) => {
      console.log('Category selected:', category)
    }

    const handleAddToCart = (product: Product) => {
      console.log('Add to cart:', product)
    }

    const handleToggleFavorite = (product: Product) => {
      console.log('Toggle favorite:', product)
    }

    const handleRestaurantSelect = (restaurant: Restaurant) => {
      console.log('Restaurant selected:', restaurant)
    }

    const handleSeeAllProducts = () => {
      console.log('See all products')
    }

    const handleSeeAllRestaurants = () => {
      console.log('See all restaurants')
    }

    return {
      t,
      isSearchOpen,
      closeSearch,
      categories,
      popularDrinks,
      partyFavourites,
      restaurants,
      isLoading,
      handleCategorySelect,
      handleAddToCart,
      handleToggleFavorite,
      handleRestaurantSelect,
      handleSeeAllProducts,
      handleSeeAllRestaurants
    }
  }
})
</script>

<template>
  <div class="foodie-page">
    <!-- Header -->
    <FoodieHeader />

    <!-- Hero Section -->
    <HeroSection />

    <!-- Category Slider -->
    <CategorySlider 
      :categories="categories"
      :loading="isLoading"
      @select="handleCategorySelect"
    />

    <!-- Popular Drinks -->
    <ProductsSection 
      :products="popularDrinks"
      :loading="isLoading"
      :config="{ title: t('foodie.popularDrinks'), seeAllLink: '#', showArrows: true }"
      @add-to-cart="handleAddToCart"
      @toggle-favorite="handleToggleFavorite"
      @see-all="handleSeeAllProducts"
    />

    <!-- Restaurants -->
    <RestaurantsSection 
      :restaurants="restaurants"
      :loading="isLoading"
      :config="{ title: t('foodie.restaurants'), seeAllLink: '#', showArrows: true }"
      @select="handleRestaurantSelect"
      @see-all="handleSeeAllRestaurants"
    />

    <!-- Party Favourites -->
    <ProductsSection 
      :products="partyFavourites"
      :loading="isLoading"
      :config="{ title: t('foodie.partyFavorites'), seeAllLink: '#', showArrows: true }"
      @add-to-cart="handleAddToCart"
      @toggle-favorite="handleToggleFavorite"
      @see-all="handleSeeAllProducts"
    />

    <!-- Cocktail Banner -->
    <CocktailBanner />

    <!-- Draft Brews Banner -->
    <DraftBrewsBanner />

    <!-- Search Section -->
    <SearchSection />

    <!-- Footer -->
    <div class="foodie-footer-wrapper">
      <FoodieFooter />
    </div>

    <!-- Search Overlay -->
    <FoodieSearchOverlay :is-open="isSearchOpen" @close="closeSearch" />
  </div>
</template>

<style scoped>
.foodie-footer-wrapper {
  position: relative;
  z-index: 9999;
  background-color: #1a1a1a;
  overflow: hidden;
}
</style>