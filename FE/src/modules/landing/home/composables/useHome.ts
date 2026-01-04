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
      const categoriesResponse = await httpClient.get('/frontend/categories', { params: { per_page: 6 } })
      const categories = (categoriesResponse as any).data?.data || []
      
      const categoryProducts: Array<{ category: any; products: any[] }> = []
      for (const category of categories) {
        const response = await httpClient.get('/frontend/products', { 
          params: { 
            category_id: category.id,
            per_page: 4 
          } 
        })
        const data = response.data as any
        
        let products = []
        if (data?.data?.items && Array.isArray(data.data.items)) {
          products = data.data.items
        } else if (data?.data?.data && Array.isArray(data.data.data)) {
          products = data.data.data
        } else if (Array.isArray(data?.data)) {
          products = data.data
        }
        
        if (products.length > 0) {
          categoryProducts.push({
            category,
            products
          })
        }
      }
      
      productsByCategory.value = categoryProducts
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
    productsByCategory,
    featuredProducts,
    featuredCategories,
    banners,
    latestArticles,
    isLoading,
    loadHomeData
  }
}
