import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { HomeBanner } from './types'
import type { Product } from '../products/store/types'
import type { CategoryWithProducts } from '../categories/store/types'
import type { Promotion } from '../promotions/store/types'

export const useHomeStore = defineStore('landing-home', () => {
  // State
  const featuredProducts = ref<Product[]>([])
  const newProducts = ref<Product[]>([])
  const categories = ref<CategoryWithProducts[]>([])
  const promotions = ref<Promotion[]>([])
  const banners = ref<HomeBanner[]>([])
  const isLoading = ref(false)

  // Getters
  const hasFeaturedProducts = computed(() => featuredProducts.value.length > 0)
  const hasPromotions = computed(() => promotions.value.length > 0)

  // Actions
  async function fetchHomeData() {
    isLoading.value = true
    try {
      // Fetch all home data in parallel
      const [productsRes, categoriesRes, promotionsRes] = await Promise.all([
        httpClient.get<any>('/products', { params: { per_page: 8, featured: true } }),
        httpClient.get<any>('/categories'),
        httpClient.get<any>('/promotions', { params: { per_page: 4 } })
      ])

      const productsData = productsRes.data as any
      featuredProducts.value = productsData?.data?.data || productsData?.data || []

      const categoriesData = categoriesRes.data as any
      categories.value = Array.isArray(categoriesData?.data) ? categoriesData.data : []

      const promotionsData = promotionsRes.data as any
      promotions.value = promotionsData?.data?.data || promotionsData?.data || []
    } catch (error) {
      console.error('Failed to fetch home data:', error)
    } finally {
      isLoading.value = false
    }
  }

  function reset() {
    featuredProducts.value = []
    newProducts.value = []
    categories.value = []
    promotions.value = []
    banners.value = []
  }

  return {
    featuredProducts,
    newProducts,
    categories,
    promotions,
    banners,
    isLoading,
    hasFeaturedProducts,
    hasPromotions,
    fetchHomeData,
    reset
  }
})
