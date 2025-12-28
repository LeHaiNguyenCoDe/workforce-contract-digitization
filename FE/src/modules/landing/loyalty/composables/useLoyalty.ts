/**
 * Loyalty Composable
 */

import { ref, computed, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

export function useLoyalty() {
  // State
  const points = ref<number | null>(null)
  const tier = ref<any>(null)
  const history = ref<any[]>([])
  const isLoading = ref(true)

  // Methods
  function formatPoints(pts: number) {
    return new Intl.NumberFormat('vi-VN').format(pts)
  }

  function getTierLabel(tierName: string) {
    const labels: Record<string, string> = {
      bronze: 'Đồng',
      silver: 'Bạc',
      gold: 'Vàng',
      platinum: 'Bạch kim',
      diamond: 'Kim cương'
    }
    return labels[tierName] || tierName
  }

  async function loadLoyaltyInfo() {
    isLoading.value = true
    try {
      const response = await httpClient.get('/frontend/loyalty')
      const data = response.data as any
      
      if (data?.data) {
        points.value = data.data.points || 0
        tier.value = data.data.tier || { id: 'bronze', name: 'Đồng' }
      }
    } catch (error) {
      console.error('Failed to fetch loyalty info:', error)
      points.value = null
    } finally {
      isLoading.value = false
    }
  }

  async function loadHistory() {
    try {
      const response = await httpClient.get('/frontend/loyalty/history')
      const data = response.data as any
      
      if (data?.data?.data) {
        history.value = data.data.data
      } else if (Array.isArray(data?.data)) {
        history.value = data.data
      }
    } catch (error) {
      console.error('Failed to fetch loyalty history:', error)
      history.value = []
    }
  }

  return {
    points,
    tier,
    history,
    isLoading,
    formatPoints,
    getTierLabel,
    loadLoyaltyInfo,
    loadHistory
  }
}
