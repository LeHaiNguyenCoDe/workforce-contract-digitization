/**
 * Orders Store
 * Manages state for order management
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminOrderService } from '@/plugins/api/services/OrderService'
import httpClient from '@/plugins/api/httpClient'

export interface OrderItem {
  id: number
  product_id: number
  product?: { id: number; name: string }
  qty: number
  price: number
}

export interface Order {
  id: number
  order_number: string
  full_name?: string
  phone?: string
  user?: { id: number; name: string; email: string }
  total: number
  total_amount?: number
  status: string
  payment_status?: string
  created_at: string
  items?: OrderItem[]
  stock_status?: 'available' | 'low' | 'out_of_stock'
  stock_check?: {
    is_available: boolean
    items: Array<{
      product_id: number
      name: string
      requested: number
      available: number
      is_sufficient: boolean
    }>
  }
}

export interface Shipper {
  id: number
  name: string
  phone: string
}

export const useAdminOrderStore = defineStore('admin-orders', () => {
  // State
  const orders = ref<Order[]>([])
  const isLoading = ref(false)
  const isUpdating = ref<number | null>(null)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const statusFilter = ref('')

  // Shipper management
  const shippers = ref<Shipper[]>([
    { id: 101, name: 'Nguyễn Văn Shipper', phone: '0901112223' },
    { id: 102, name: 'Trần Thị Giao Hàng', phone: '0904445556' },
    { id: 103, name: 'Lê Văn Tốc Hành', phone: '0907778889' }
  ])
  const showShipperModal = ref(false)
  const selectedOrderForShipper = ref<Order | null>(null)
  const selectedShipperId = ref<number | null>(null)

  // Getters
  const hasOrders = computed(() => orders.value.length > 0)

  // Actions
  async function fetchOrders(params?: { page?: number; per_page?: number; status?: string; search?: string }) {
    isLoading.value = true
    try {
      const queryParams: Record<string, unknown> = {
        page: params?.page || currentPage.value,
        per_page: params?.per_page || 10
      }

      if (params?.status) queryParams.status = params.status
      if (params?.search) queryParams.search = params.search

      const response = await httpClient.get('/admin/orders', { params: queryParams })
      const data = response.data as any

      if (data?.data?.data && Array.isArray(data.data.data)) {
        orders.value = data.data.data
        totalPages.value = data.data.last_page || 1
        currentPage.value = data.data.current_page || 1
      } else if (Array.isArray(data?.data)) {
        orders.value = data.data
        totalPages.value = 1
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

  async function updateOrderStatus(orderId: number, status: string): Promise<boolean> {
    isUpdating.value = orderId
    try {
      await adminOrderService.update(orderId, { status } as any)
      const order = orders.value.find((o: Order) => o.id === orderId)
      if (order) {
        order.status = status
      }
      return true
    } catch (error) {
      console.error('Failed to update order status:', error)
      throw error
    } finally {
      isUpdating.value = null
    }
  }

  async function assignShipper(orderId: number, shipperId: number): Promise<boolean> {
    try {
      await httpClient.post(`/admin/orders/${orderId}/assign-shipper`, { shipper_id: shipperId })
      const order = orders.value.find((o: Order) => o.id === orderId)
      if (order) {
        // Update order with shipper info if needed
      }
      return true
    } catch (error) {
      console.error('Failed to assign shipper:', error)
      throw error
    }
  }

  async function checkStock(orderId: number): Promise<any> {
    try {
      const response = await httpClient.get(`/admin/orders/${orderId}/check-stock`)
      return (response.data as any).data
    } catch (error) {
      console.error('Failed to check stock:', error)
      throw error
    }
  }

  async function fetchOrderDetail(orderId: number): Promise<Order | null> {
    try {
      const response = await httpClient.get(`/admin/orders/${orderId}`)
      const data = response.data as any
      return data?.data || data || null
    } catch (error) {
      console.error('Failed to fetch order detail:', error)
      return null
    }
  }

  // BR-SALES-02: Confirm order
  async function confirmOrder(orderId: number): Promise<boolean> {
    isUpdating.value = orderId
    try {
      await httpClient.post(`/admin/orders/${orderId}/confirm`)
      const order = orders.value.find((o: Order) => o.id === orderId)
      if (order) {
        order.status = 'confirmed'
      }
      return true
    } catch (error) {
      console.error('Failed to confirm order:', error)
      throw error
    } finally {
      isUpdating.value = null
    }
  }

  // BR-SALES-02: Mark as delivered
  async function markDelivered(orderId: number): Promise<boolean> {
    isUpdating.value = orderId
    try {
      await httpClient.post(`/admin/orders/${orderId}/deliver`)
      const order = orders.value.find((o: Order) => o.id === orderId)
      if (order) {
        order.status = 'delivered'
      }
      return true
    } catch (error) {
      console.error('Failed to mark as delivered:', error)
      throw error
    } finally {
      isUpdating.value = null
    }
  }

  // BR-SALES-03: Complete order
  async function completeOrder(orderId: number): Promise<boolean> {
    isUpdating.value = orderId
    try {
      await httpClient.post(`/admin/orders/${orderId}/complete`)
      const order = orders.value.find((o: Order) => o.id === orderId)
      if (order) {
        order.status = 'completed'
      }
      return true
    } catch (error) {
      console.error('Failed to complete order:', error)
      throw error
    } finally {
      isUpdating.value = null
    }
  }

  // BR-SALES-05: Cancel order
  async function cancelOrderAction(orderId: number, reason?: string): Promise<boolean> {
    isUpdating.value = orderId
    try {
      await httpClient.post(`/admin/orders/${orderId}/cancel`, { reason })
      const order = orders.value.find((o: Order) => o.id === orderId)
      if (order) {
        order.status = 'cancelled'
      }
      return true
    } catch (error) {
      console.error('Failed to cancel order:', error)
      throw error
    } finally {
      isUpdating.value = null
    }
  }

  function setPage(page: number) {
    currentPage.value = page
  }

  function setStatusFilter(status: string) {
    statusFilter.value = status
    if (currentPage.value !== 1) {
      currentPage.value = 1
    }
  }

  function reset() {
    orders.value = []
    currentPage.value = 1
    statusFilter.value = ''
  }

  return {
    // State
    orders,
    isLoading,
    isUpdating,
    currentPage,
    totalPages,
    statusFilter,
    shippers,
    showShipperModal,
    selectedOrderForShipper,
    selectedShipperId,
    // Getters
    hasOrders,
    // Actions
    fetchOrders,
    fetchOrderDetail,
    updateOrderStatus,
    assignShipper,
    checkStock,
    confirmOrder,
    markDelivered,
    completeOrder,
    cancelOrderAction,
    setPage,
    setStatusFilter,
    reset
  }
})

