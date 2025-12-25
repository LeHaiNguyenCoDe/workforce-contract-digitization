/**
 * Landing Loyalty Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { LoyaltyPoints, LoyaltyTransaction, LoyaltyTier } from './types'

export const useLoyaltyStore = defineStore('landing-loyalty', () => {
  // State
  const points = ref<LoyaltyPoints>({ total: 0, available: 0, pending: 0, expired: 0 })
  const transactions = ref<LoyaltyTransaction[]>([])
  const tiers = ref<LoyaltyTier[]>([])
  const currentTier = ref<LoyaltyTier | null>(null)
  const isLoading = ref(false)

  // Getters
  const availablePoints = computed(() => points.value.available)
  const hasTransactions = computed(() => transactions.value.length > 0)

  // Actions
  async function fetchLoyaltyData() {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>('/frontend/loyalty')
      const data = response.data as any
      
      if (data?.data) {
        points.value = data.data.points || points.value
        transactions.value = data.data.transactions || []
        currentTier.value = data.data.current_tier || null
        tiers.value = data.data.tiers || []
      }
    } catch (error) {
      console.error('Failed to fetch loyalty data:', error)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchTransactions(page = 1) {
    try {
      const response = await httpClient.get<any>('/frontend/loyalty/transactions', {
        params: { page }
      })
      const data = response.data as any
      transactions.value = data?.data?.data || data?.data || []
    } catch (error) {
      console.error('Failed to fetch transactions:', error)
    }
  }

  function reset() {
    points.value = { total: 0, available: 0, pending: 0, expired: 0 }
    transactions.value = []
    currentTier.value = null
  }

  return {
    points,
    transactions,
    tiers,
    currentTier,
    isLoading,
    availablePoints,
    hasTransactions,
    fetchLoyaltyData,
    fetchTransactions,
    reset
  }
})
