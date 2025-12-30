/**
 * Composable for Orders
 * Provides reusable logic for order management
 */

import { ref, computed } from 'vue'
import { useSwal } from '@/shared/utils'
import { useAuthStore } from '@/stores'
import type { Order } from '../store/store'

export function useAdminOrders() {
  const store = useAdminOrderStore()
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
        (o: Order) =>
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

  // Status labels - BR-SALES-02
  const statusLabels: Record<string, { text: string; color: string }> = {
    draft: { text: 'Nháp', color: 'bg-slate-500/10 text-slate-400' },
    pending: { text: 'Chờ xử lý', color: 'bg-warning/10 text-warning' },
    confirmed: { text: 'Đã xác nhận', color: 'bg-info/10 text-info' },
    processing: { text: 'Đang xử lý', color: 'bg-info/10 text-info' },
    shipped: { text: 'Đang giao', color: 'bg-primary/10 text-primary-light' },
    delivered: { text: 'Đã giao', color: 'bg-success/10 text-success' },
    completed: { text: 'Hoàn thành', color: 'bg-emerald-500/10 text-emerald-400' },
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

  // State for order detail modal
  const selectedOrder = ref<Order | null>(null)
  const showOrderDetail = ref(false)
  const isLoadingDetail = ref(false)

  async function viewOrderDetail(order: Order) {
    // Open modal immediately with basic info
    selectedOrder.value = order
    showOrderDetail.value = true
    
    // Fetch full order details with items
    isLoadingDetail.value = true
    try {
      const detailedOrder = await store.fetchOrderDetail(order.id)
      if (detailedOrder) {
        selectedOrder.value = detailedOrder
      }
    } catch (error) {
      console.error('Failed to fetch order details:', error)
    } finally {
      isLoadingDetail.value = false
    }
  }

  function closeOrderDetail() {
    showOrderDetail.value = false
    selectedOrder.value = null
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

  // BR-SALES-02: Confirm order
  async function handleConfirmOrder(order: Order) {
    try {
      await store.confirmOrder(order.id)
      await swal.success('Xác nhận đơn hàng thành công! Đã trừ kho.')
      await store.fetchOrders()
    } catch (error: any) {
      await swal.error(error.response?.data?.message || 'Xác nhận đơn hàng thất bại!')
    }
  }

  // BR-SALES-02: Mark delivered
  async function handleMarkDelivered(order: Order) {
    try {
      await store.markDelivered(order.id)
      await swal.success('Đã giao hàng thành công!')
      await store.fetchOrders()
    } catch (error: any) {
      await swal.error(error.response?.data?.message || 'Cập nhật thất bại!')
    }
  }

  // BR-SALES-03: Complete order
  async function handleCompleteOrder(order: Order) {
    try {
      await store.completeOrder(order.id)
      await swal.success('Hoàn thành đơn hàng! Doanh thu đã được ghi nhận.')
      await store.fetchOrders()
    } catch (error: any) {
      await swal.error(error.response?.data?.message || 'Hoàn thành đơn hàng thất bại!')
    }
  }

  return {
    // State
    searchQuery,
    selectedOrder,
    showOrderDetail,
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
    viewOrderDetail,
    closeOrderDetail,
    cancelOrder,
    viewTracking,
    approveOrder,
    handleConfirmOrder,
    handleMarkDelivered,
    handleCompleteOrder
  }
}

