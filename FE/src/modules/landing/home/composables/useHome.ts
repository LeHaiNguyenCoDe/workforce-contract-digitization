/**
 * Home Composable
 * Provides reusable logic for home page data
 */

import { ref, computed, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

export function useHome() {
  // State
  const featuredProducts = ref<any[]>([])
  const featuredCategories = ref<any[]>([])
  const banners = ref<any[]>([])
  const latestArticles = ref<any[]>([])
  const isLoading = ref(true)

  // Methods
  async function fetchFeaturedProducts() {
    try {
      const response = await httpClient.get('/frontend/products', { params: { per_page: 8 } })
      const data = response.data as any
      
      if (data?.data?.data && Array.isArray(data.data.data)) {
        featuredProducts.value = data.data.data
      } else if (Array.isArray(data?.data)) {
        featuredProducts.value = data.data
      }
    } catch (error) {
      console.error('Failed to fetch featured products:', error)
    }
  }

  async function fetchFeaturedCategories() {
    try {
      const response = await httpClient.get('/frontend/categories', { params: { per_page: 6 } })
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
    featuredProducts,
    featuredCategories,
    banners,
    latestArticles,
    isLoading,
    loadHomeData
  }
}
