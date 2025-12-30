/**
 * Promotions Composable
 */

import { ref, computed, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

export function useLandingPromotions() {
  // State
  const promotions = ref<any[]>([])
  const isLoading = ref(true)

  // Methods
  function formatDiscount(promotion: any) {
    if (promotion.discount_type === 'percent') {
      return `${promotion.discount_value}%`
    }
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(promotion.discount_value)
  }

  async function fetchPromotions() {
    isLoading.value = true
    try {
      const response = await httpClient.get('/frontend/promotions', { params: { per_page: 12 } })
      const data = response.data as any

      if (data?.data?.data && Array.isArray(data.data.data)) {
        promotions.value = data.data.data
      } else if (Array.isArray(data?.data)) {
        promotions.value = data.data
      } else {
        promotions.value = []
      }
    } catch (error) {
      console.error('Failed to fetch promotions:', error)
      promotions.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function loadPromotions() {
    await fetchPromotions()
  }

  onMounted(loadPromotions)

  return {
    promotions,
    isLoading,
    formatDiscount,
    loadPromotions
  }
}
