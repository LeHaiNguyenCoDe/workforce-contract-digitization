/**
 * Promotions Store
 * Manages state for promotion management
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminPromotionService } from '@/plugins/api/services/PromotionService'
import httpClient from '@/plugins/api/httpClient'
import type { Promotion } from '@/plugins/api/services/PromotionService'

export const usePromotionStore = defineStore('admin-promotions', () => {
  // State
  const promotions = ref<Promotion[]>([])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const selectedPromotion = ref<Promotion | null>(null)

  // Forms
  const promotionForm = ref({
    name: '',
    code: '',
    type: 'percent' as 'percent' | 'fixed_amount',
    value: 10,
    starts_at: '',
    ends_at: '',
    is_active: true
  })

  // Getters
  const hasPromotions = computed(() => promotions.value.length > 0)

  // Actions
  async function fetchPromotions() {
    isLoading.value = true
    try {
      // Try using httpClient directly to handle different response formats
      const response = await httpClient.get('/admin/promotions')
      const data = response.data as any
      
      // Handle different response formats
      if (data?.data?.data && Array.isArray(data.data.data)) {
        promotions.value = data.data.data
      } else if (data?.data && Array.isArray(data.data)) {
        promotions.value = data.data
      } else if (Array.isArray(data)) {
        promotions.value = data
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

  async function createPromotion(payload: Record<string, unknown>): Promise<boolean> {
    isSaving.value = true
    try {
      await adminPromotionService.create(payload)
      await fetchPromotions()
      return true
    } catch (error) {
      console.error('Failed to create promotion:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function updatePromotion(id: number, payload: Record<string, unknown>): Promise<boolean> {
    isSaving.value = true
    try {
      await adminPromotionService.update(id, payload)
      await fetchPromotions()
      return true
    } catch (error) {
      console.error('Failed to update promotion:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function deletePromotion(id: number): Promise<boolean> {
    try {
      await adminPromotionService.delete(id)
      promotions.value = promotions.value.filter(p => p.id !== id)
      return true
    } catch (error) {
      console.error('Failed to delete promotion:', error)
      return false
    }
  }

  function resetPromotionForm() {
    promotionForm.value = {
      name: '',
      code: '',
      type: 'percent',
      value: 10,
      starts_at: '',
      ends_at: '',
      is_active: true
    }
  }

  function reset() {
    promotions.value = []
    selectedPromotion.value = null
    resetPromotionForm()
  }

  return {
    // State
    promotions,
    isLoading,
    isSaving,
    selectedPromotion,
    promotionForm,
    // Getters
    hasPromotions,
    // Actions
    fetchPromotions,
    createPromotion,
    updatePromotion,
    deletePromotion,
    resetPromotionForm,
    reset
  }
})

