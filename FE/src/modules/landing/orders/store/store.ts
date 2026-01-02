/**
 * Landing Orders Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { Order, OrderFilters, CreateOrderPayload } from '../types'

export const useLandingOrderStore = defineStore('landing-orders', () => {
  // State
  const orders = ref<Order[]>([])
  const currentOrder = ref<Order | null>(null)
  const isLoading = ref(false)
  const isSubmitting = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const filters = ref<OrderFilters>({})

  // Getters
  const hasOrders = computed(() => orders.value.length > 0)
  const pendingOrders = computed(() => orders.value.filter((o: Order) => o.status === 'pending'))
  const completedOrders = computed(() => orders.value.filter((o: Order) => o.status === 'delivered'))

  // Actions
  async function fetchOrders(params?: OrderFilters & { page?: number }) {
    isLoading.value = true
    try {
      const queryParams: Record<string, unknown> = {
        page: params?.page || currentPage.value
      }
      if (params?.status) queryParams.status = params.status

      const response = await httpClient.get<any>('/frontend/orders', { params: queryParams })
      const data = response.data as any

      // Support for wrapped data.items or direct data array
      const responseData = data?.data || data
      
      if (responseData?.items && Array.isArray(responseData.items)) {
        orders.value = responseData.items
        totalPages.value = responseData.last_page || responseData.meta?.last_page || 1
        currentPage.value = responseData.current_page || responseData.meta?.current_page || 1
      } else if (responseData?.data && Array.isArray(responseData.data)) {
        orders.value = responseData.data
        totalPages.value = responseData.last_page || 1
        currentPage.value = responseData.current_page || 1
      } else if (Array.isArray(responseData)) {
        orders.value = responseData
      }
    } catch (error) {
      console.error('Failed to fetch orders:', error)
      orders.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function fetchOrderById(id: number | string): Promise<Order | null> {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>(`/frontend/orders/${id}`)
      const data = response.data as any
      currentOrder.value = data?.data || data
      return currentOrder.value
    } catch (error) {
      console.error('Failed to fetch order:', error)
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function createOrder(payload: CreateOrderPayload): Promise<Order | null> {
    isSubmitting.value = true
    try {
      const response = await httpClient.post<any>('/frontend/orders', payload)
      const data = response.data as any
      const newOrder = data?.data || data
      orders.value.unshift(newOrder)
      return newOrder
    } catch (error) {
      console.error('Failed to create order:', error)
      throw error
    } finally {
      isSubmitting.value = false
    }
  }

  async function cancelOrder(id: number): Promise<boolean> {
    try {
      await httpClient.put(`/frontend/orders/${id}/cancel`)
      const order = orders.value.find((o: Order) => o.id === id)
      if (order) order.status = 'cancelled'
      return true
    } catch (error) {
      console.error('Failed to cancel order:', error)
      return false
    }
  }

  function setFilters(newFilters: OrderFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  function reset() {
    orders.value = []
    currentOrder.value = null
    currentPage.value = 1
    filters.value = {}
  }

  return {
    // State
    orders,
    currentOrder,
    isLoading,
    isSubmitting,
    currentPage,
    totalPages,
    filters,
    // Getters
    hasOrders,
    pendingOrders,
    completedOrders,
    // Actions
    fetchOrders,
    fetchOrderById,
    createOrder,
    cancelOrder,
    setFilters,
    reset
  }
})
