/**
 * Orders Composable
 */

import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'

export function useLandingOrders() {
  const { t } = useI18n()
  // State
  const orders = ref<any[]>([])
  const currentOrder = ref<any>(null)
  const isLoading = ref(true)

  // Methods
  function formatPrice(price: number | undefined | null) {
    if (price === undefined || price === null || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
  }

  function formatDate(date: string | undefined | null) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  function getStatusLabel(status: string) {
    const keys: Record<string, string> = {
      pending: 'common.pendingConfirmation',
      approved: 'common.confirmed',
      processing: 'common.processingOrder',
      shipped: 'common.shipped',
      delivered: 'common.delivered',
      cancelled: 'common.cancelled'
    }
    return keys[status] ? t(keys[status]) : status
  }

  function getStatusColor(status: string) {
    const colors: Record<string, string> = {
      pending: 'amber',
      approved: 'blue',
      processing: 'cyan',
      shipped: 'purple',
      delivered: 'emerald',
      cancelled: 'red'
    }
    return colors[status] || 'slate'
  }

  async function fetchOrders() {
    isLoading.value = true
    try {
      const response = await httpClient.get('/frontend/orders')
      const data = response.data as any
      if (data?.data?.data && Array.isArray(data.data.data)) {
        orders.value = data.data.data
      } else if (Array.isArray(data?.data)) {
        orders.value = data.data
      } else {
        orders.value = []
      }
    } catch (error) {
      console.error('Failed to fetch orders:', error)
      orders.value = []
    } finally {
      isLoading.value = false
    }
  }

  // Auto-fetch orders on mount
  onMounted(() => {
    fetchOrders()
  })

  async function fetchOrderById(id: string | number) {
    isLoading.value = true
    try {
      const response = await httpClient.get(`/frontend/orders/${id}`)
      const data = response.data as any
      currentOrder.value = data?.data || data
    } catch (error) {
      console.error('Failed to fetch order:', error)
      currentOrder.value = null
    } finally {
      isLoading.value = false
    }
  }

  return {
    orders,
    currentOrder,
    isLoading,
    formatPrice,
    formatDate,
    getStatusLabel,
    getStatusColor,
    fetchOrders,
    fetchOrderById
  }
}
