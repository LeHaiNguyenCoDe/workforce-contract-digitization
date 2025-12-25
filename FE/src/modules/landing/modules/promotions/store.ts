/**
 * Landing Promotions Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { Promotion } from './types'
import { isPromotionActive } from './helpers'

export const usePromotionStore = defineStore('landing-promotions', () => {
  // State
  const promotions = ref<Promotion[]>([])
  const currentPromotion = ref<Promotion | null>(null)
  const isLoading = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)

  // Getters
  const activePromotions = computed(() => promotions.value.filter(isPromotionActive))
  const hasPromotions = computed(() => promotions.value.length > 0)

  // Actions
  async function fetchPromotions(params?: { page?: number }) {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>('/frontend/promotions', {
        params: { page: params?.page || currentPage.value }
      })
      const data = response.data as any

      if (data?.data?.data && Array.isArray(data.data.data)) {
        promotions.value = data.data.data
        totalPages.value = data.data.last_page || 1
        currentPage.value = data.data.current_page || 1
      } else if (Array.isArray(data?.data)) {
        promotions.value = data.data
      }
    } catch (error) {
      console.error('Failed to fetch promotions:', error)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchPromotionById(id: number | string): Promise<Promotion | null> {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>(`/frontend/promotions/${id}`)
      const data = response.data as any
      currentPromotion.value = data?.data || data
      return currentPromotion.value
    } catch (error) {
      console.error('Failed to fetch promotion:', error)
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function applyPromoCode(code: string): Promise<Promotion | null> {
    try {
      const response = await httpClient.post<any>('/frontend/promotions/apply', { code })
      const data = response.data as any
      return data?.data || null
    } catch (error) {
      console.error('Failed to apply promo code:', error)
      return null
    }
  }

  function reset() {
    promotions.value = []
    currentPromotion.value = null
    currentPage.value = 1
  }

  return {
    promotions,
    currentPromotion,
    isLoading,
    currentPage,
    totalPages,
    activePromotions,
    hasPromotions,
    fetchPromotions,
    fetchPromotionById,
    applyPromoCode,
    reset
  }
})
