/**
 * Composable for Orders
 * Provides reusable logic for order management
 */

import { ref, computed } from 'vue'
import { useOrderStore } from '../store/store'
import { useSwal } from '@/shared/utils'
import { useAuthStore } from '@/stores'
import type { Order } from '../store/store'

export function useOrders() {
  const store = useOrderStore()
  const authStore = useAuthStore()
  const swal = useSwal()

  // Local state
  const searchQuery = ref('')

  // Computed
  const filteredOrders = computed(() => {
    let result = store.orders
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      result = result.filter(
        o =>
          o.order_number.toLowerCase().includes(query) ||
          o.full_name?.toLowerCase().includes(query) ||
          o.phone?.includes(query) ||
          o.user?.email?.toLowerCase().includes(query)
      )
    }
    return result
  })

  // Check if current user can approve orders
  const canApproveOrders = computed(() => {
    const email = authStore.user?.email
    return email === 'admin@example.com' || email === 'manager@example.com'
  })

  // Status labels
  const statusLabels: Record<string, { text: string; color: string }> = {
    pending: { text: 'Chờ xử lý', color: 'bg-warning/10 text-warning' },
    processing: { text: 'Đang xử lý', color: 'bg-info/10 text-info' },
    shipped: { text: 'Đang giao', color: 'bg-primary/10 text-primary-light' },
    delivered: { text: 'Đã giao', color: 'bg-success/10 text-success' },
    cancelled: { text: 'Đã hủy', color: 'bg-error/10 text-error' }
  }

  const stockStatusLabels: Record<string, { text: string; color: string }> = {
    available: { text: '✅ Còn hàng', color: 'text-success' },
    low: { text: '⚠️ Sắp hết', color: 'text-warning' },
    out_of_stock: { text: '❌ Hết hàng', color: 'text-error' }
  }

  // Methods
  function getStatusInfo(status: string) {
    return statusLabels[status] || { text: status, color: 'bg-slate-500/10 text-slate-400' }
  }

  function getStockStatus(order: Order) {
    if (order.stock_check) {
      if (!order.stock_check.is_available) return stockStatusLabels.out_of_stock
      return stockStatusLabels.available
    }
    return stockStatusLabels[order.stock_status || 'available'] || stockStatusLabels.available
  }

  function getCustomerName(order: Order) {
    return order.full_name || order.user?.name || 'Khách hàng'
  }

  function formatPrice(price: number | undefined | null) {
    if (price === undefined || price === null || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
  }

  function formatDate(date: string) {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('vi-VN')
  }

  function setSearchQuery(value: string) {
    searchQuery.value = value
    if (store.currentPage !== 1) {
      store.setPage(1)
    }
  }

  async function handleStatusUpdate(orderId: number, newStatus: string) {
    try {
      await store.updateOrderStatus(orderId, newStatus)
      await swal.success('Cập nhật trạng thái đơn hàng thành công!')
      await store.fetchOrders()
    } catch (error: any) {
      console.error('Status update error:', error)
      await swal.error(error.response?.data?.message || 'Cập nhật trạng thái thất bại!')
    }
  }

  async function handleAssignShipper(order: Order) {
    store.selectedOrderForShipper = order
    store.showShipperModal = true
  }

  async function confirmAssignShipper() {
    if (!store.selectedOrderForShipper || !store.selectedShipperId) return

    try {
      await store.assignShipper(store.selectedOrderForShipper.id, store.selectedShipperId)
      await swal.success('Phân công shipper thành công!')
      store.showShipperModal = false
      store.selectedOrderForShipper = null
      store.selectedShipperId = null
      await store.fetchOrders()
    } catch (error: any) {
      console.error('Assign shipper error:', error)
      await swal.error(error.response?.data?.message || 'Phân công shipper thất bại!')
    }
  }

  async function handleCheckStock(order: Order) {
    try {
      const stockCheck = await store.checkStock(order.id)
      // Update order with stock check result
      const orderIndex = store.orders.findIndex(o => o.id === order.id)
      if (orderIndex !== -1) {
        store.orders[orderIndex].stock_check = stockCheck
      }
      await swal.success('Kiểm tra tồn kho thành công!')
    } catch (error: any) {
      console.error('Check stock error:', error)
      await swal.error(error.response?.data?.message || 'Kiểm tra tồn kho thất bại!')
    }
  }

  async function cancelOrder(orderId: number) {
    const confirmed = await swal.confirmDelete('Bạn có chắc muốn hủy đơn hàng này?')
    if (!confirmed) return

    try {
      await store.updateOrderStatus(orderId, 'cancelled')
      await swal.success('Hủy đơn hàng thành công!')
      await store.fetchOrders()
    } catch (error: any) {
      console.error('Failed to cancel order:', error)
      await swal.error(error.response?.data?.message || 'Hủy đơn hàng thất bại!')
    }
  }

  function viewTracking(order: Order) {
    alert(`Đang theo dõi đơn hàng #${order.id}. Vị trí hiện tại: 10.76262, 106.66017`)
  }

  async function approveOrder(order: Order) {
    try {
      store.isUpdating = order.id
      const stockData = await store.checkStock(order.id)

      if (!stockData.is_available) {
        await swal.warning('Không đủ hàng trong kho! Vui lòng kiểm tra lại.')
        await store.fetchOrders()
        return
      }

      handleAssignShipper(order)
    } catch (error) {
      console.error('Stock check failed:', error)
      await swal.error('Không thể kiểm tra kho hàng!')
    } finally {
      store.isUpdating = null
    }
  }

  return {
    // State
    searchQuery,
    // Computed
    filteredOrders,
    canApproveOrders,
    // Methods
    getStatusInfo,
    getStockStatus,
    getCustomerName,
    formatPrice,
    formatDate,
    setSearchQuery,
    handleStatusUpdate,
    handleAssignShipper,
    confirmAssignShipper,
    handleCheckStock,
    cancelOrder,
    viewTracking,
    approveOrder
  }
}

