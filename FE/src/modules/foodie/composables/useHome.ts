/**
 * Foodie Home Composable
 * Provides reusable logic for home page
 */

import { onMounted, onUnmounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useFoodieHomeStore } from '../store/store'

export function useFoodieHome() {
  const store = useFoodieHomeStore()
  const { 
    banners, 
    categories, 
    popularDrinks, 
    restaurants, 
    partyFavourites, 
    isLoading, 
    error,
    hasCategories,
    hasPopularDrinks,
    hasRestaurants,
    hasPartyFavourites
  } = storeToRefs(store)
  
  // Body style overrides for foodie pages
  let originalBg = ''
  let originalColor = ''

  function setupBodyStyles() {
    originalBg = document.body.style.backgroundColor
    originalColor = document.body.style.color
    document.body.style.backgroundColor = '#FFFFFF'
    document.body.style.color = '#2D2D2D'
  }

  function restoreBodyStyles() {
    document.body.style.backgroundColor = originalBg
    document.body.style.color = originalColor
  }

  onMounted(() => {
    setupBodyStyles()
    if (!hasCategories.value) {
      store.fetchHomeData()
    }
  })

  onUnmounted(() => {
    restoreBodyStyles()
  })

  return {
    // State
    banners,
    categories,
    popularDrinks,
    restaurants,
    partyFavourites,
    isLoading,
    error,
    // Getters
    hasCategories,
    hasPopularDrinks,
    hasRestaurants,
    hasPartyFavourites,
    // Methods
    fetchHomeData: store.fetchHomeData
  }
}
