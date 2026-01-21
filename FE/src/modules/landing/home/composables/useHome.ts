/**
 * Home Composable
 * Provides reusable logic for home page data
 */

import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

export function useHome() {
  // State
  const productsByCategory = ref<Array<{ category: any; products: any[] }>>([])
  const featuredProducts = ref<any[]>([])
  const featuredCategories = ref<any[]>([])
  const banners = ref<any[]>([])
  const latestArticles = ref<any[]>([])
  const isLoading = ref(true)

  // Methods
  async function fetchFeaturedProducts() {
    try {
      // Single optimized API call - replaces N+1 queries
      const response = await httpClient.get('/frontend/home-data', {
        params: { categories_limit: 6, products_per_category: 4 }
      })
      const data = response.data as any
      
      if (data?.status === 'success' && Array.isArray(data?.data)) {
        productsByCategory.value = data.data
      } else {
        productsByCategory.value = []
      }
    } catch (error) {
      console.error('Failed to fetch featured products:', error)
      productsByCategory.value = []
    }
  }

  async function fetchFeaturedCategories() {
    try {
      const response = await httpClient.get('/frontend/categories', { 
        params: { per_page: 6, is_active: 1 } 
      })
      const data = response.data as any
      
      if (Array.isArray(data?.data)) {
        featuredCategories.value = data.data
      }
    } catch (error) {
      console.error('Failed to fetch categories:', error)
    }
  }

  async function fetchBanners() {
    // TODO: Implement banners API when available
    banners.value = []
  }

  async function fetchLatestArticles() {
    // TODO: Implement articles API when available
    latestArticles.value = []
  }

  async function loadHomeData() {
    isLoading.value = true
    try {
      await Promise.all([
        fetchFeaturedProducts(),
        fetchFeaturedCategories(),
        fetchBanners(),
        fetchLatestArticles()
      ])
    } finally {
      isLoading.value = false
    }
  }

  onMounted(loadHomeData)

  return {
    productsByCategory,
    featuredProducts,
    featuredCategories,
    banners,
    latestArticles,
    isLoading,
    loadHomeData
  }
}
