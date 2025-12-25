<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'
import httpClient from '@/plugins/api/httpClient'

const { t } = useI18n()
const authStore = useAuthStore()

interface OrderItem {
    id: number
    product_id: number
    product?: { id: number; name: string }
    qty: number
    price: number
}

interface Order {
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

const orders = ref<Order[]>([])
const isLoading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const statusFilter = ref('')
const isUpdating = ref<number | null>(null)

// Check if current user can approve orders
const canApproveOrders = () => {
    const email = authStore.user?.email
    return email === 'admin@example.com' || email === 'manager@example.com'
}

const shippers = ref([
    { id: 101, name: 'Nguyễn Văn Shipper', phone: '0901112223' },
    { id: 102, name: 'Trần Thị Giao Hàng', phone: '0904445556' },
    { id: 103, name: 'Lê Văn Tốc Hành', phone: '0907778889' }
])

const showShipperModal = ref(false)
const selectedOrderForShipper = ref<Order | null>(null)
const selectedShipperId = ref<number | null>(null)

const formatPrice = (price: number | undefined | null) => {
    if (price === undefined || price === null || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const formatDate = (date: string) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('vi-VN')
}

const statusLabels: Record<string, { text: string; color: string }> = {
    pending: { text: 'Chờ xử lý', color: 'bg-warning/10 text-warning' },
    processing: { text: 'Đang xử lý', color: 'bg-info/10 text-info' },
    shipped: { text: 'Đang giao', color: 'bg-primary/10 text-primary-light' },
    delivered: { text: 'Đã giao', color: 'bg-success/10 text-success' },
    cancelled: { text: 'Đã hủy', color: 'bg-error/10 text-error' }
}

const getStatusInfo = (status: string) => statusLabels[status] || { text: status, color: 'bg-slate-500/10 text-slate-400' }

const stockStatusLabels: Record<string, { text: string; color: string }> = {
    available: { text: '✅ Còn hàng', color: 'text-success' },
    low: { text: '⚠️ Sắp hết', color: 'text-warning' },
    out_of_stock: { text: '❌ Hết hàng', color: 'text-error' }
}

const getStockStatus = (order: Order) => {
    if (order.stock_check) {
        if (!order.stock_check.is_available) return stockStatusLabels.out_of_stock
        return stockStatusLabels.available
    }
    return stockStatusLabels[order.stock_status || 'available'] || stockStatusLabels.available
}

const getCustomerName = (order: Order) => {
    return order.full_name || order.user?.name || 'Khách vãng lai'
}

const getCustomerContact = (order: Order) => {
    return order.phone || order.user?.email || ''
}

const getOrderTotal = (order: Order) => {
    return order.total ?? order.total_amount ?? 0
}

const fetchOrders = async () => {
    isLoading.value = true
    try {
        const params: any = { page: currentPage.value, per_page: 15 }
        if (statusFilter.value) params.status = statusFilter.value
        const response = await httpClient.get('/admin/orders', { params })
        const data = response.data as any

        if (data?.data?.data && Array.isArray(data.data.data)) {
            orders.value = data.data.data
            totalPages.value = data.data.last_page || 1
        } else if (Array.isArray(data?.data)) {
            orders.value = data.data
        } else {
            orders.value = []
        }
        console.log('Admin orders loaded:', orders.value)
    } catch (error) {
        console.error('Failed to fetch orders:', error)
        orders.value = []
    } finally {
        isLoading.value = false
    }
}

const viewTracking = (order: Order) => {
    // Navigate to tracking view or open modal
    alert(`Đang theo dõi đơn hàng #${order.id}. Vị trí hiện tại: 10.76262, 106.66017`)
}

const approveOrder = async (order: Order) => {
    // 1. Check stock first via API
    try {
        isUpdating.value = order.id
        const response = await httpClient.get(`/admin/orders/${order.id}/check-stock`)
        const stockData = (response.data as any).data

        if (!stockData.is_available) {
            alert('Không đủ hàng trong kho! Vui lòng kiểm tra lại.')
            fetchOrders()
            return
        }

        // 2. Open shipper modal if stock is OK
        selectedOrderForShipper.value = order
        showShipperModal.value = true
    } catch (error) {
        console.error('Stock check failed:', error)
        alert('Không thể kiểm tra kho hàng!')
    } finally {
        isUpdating.value = null
    }
}

const confirmAssignShipper = async () => {
    if (!selectedOrderForShipper.value || !selectedShipperId.value) return

    isUpdating.value = selectedOrderForShipper.value.id
    try {
        await httpClient.post(`/admin/orders/${selectedOrderForShipper.value.id}/assign-shipper`, {
            shipper_id: selectedShipperId.value
        })
        showShipperModal.value = false
        selectedShipperId.value = null
        selectedOrderForShipper.value = null
        await fetchOrders()
    } catch (error) {
        console.error('Failed to assign shipper:', error)
        alert('Giao shipper thất bại!')
    } finally {
        isUpdating.value = null
    }
}

const cancelOrder = async (orderId: number) => {
    if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
        isUpdating.value = orderId
        try {
            await httpClient.put(`/admin/orders/${orderId}/cancel`)
            await fetchOrders()
        } catch (error: any) {
            console.error('Failed to cancel order:', error)
            alert(error.response?.data?.message || 'Hủy đơn hàng thất bại!')
        } finally {
            isUpdating.value = null
        }
    }
}

onMounted(fetchOrders)
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.orders') }}</h1>
                <p class="text-slate-400 mt-1">Quản lý đơn hàng</p>
            </div>
            <div v-if="canApproveOrders()" class="text-sm text-success">
                ✓ Có quyền duyệt đơn hàng
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex gap-4">
                <select v-model="statusFilter" @change="fetchOrders" class="form-input w-48">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="processing">Đang xử lý</option>
                    <option value="shipped">Đang giao</option>
                    <option value="delivered">Đã giao</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
        </div>

        <!-- Table Container -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 bg-dark-700">
                        <tr class="border-b border-white/10">
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Mã đơn</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Khách hàng</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Tổng tiền</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Kho hàng</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Ngày đặt</th>
                            <th v-if="canApproveOrders()"
                                class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="order in orders" :key="order.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-4">
                                <p class="font-mono font-medium text-white">#{{ order.order_number || order.id }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-medium text-white">{{ getCustomerName(order) }}</p>
                                <p class="text-xs text-slate-500">{{ getCustomerContact(order) }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-primary-light">{{ formatPrice(getOrderTotal(order)) }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span :class="getStockStatus(order).color">{{ getStockStatus(order).text }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-medium', getStatusInfo(order.status).color]">
                                    {{ getStatusInfo(order.status).text }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-400">{{ formatDate(order.created_at) }}</td>
                            <td v-if="canApproveOrders()" class="px-4 py-4">
                                <div class="flex gap-2">
                                    <button v-if="order.status === 'pending'" @click="approveOrder(order)"
                                        :disabled="isUpdating === order.id"
                                        class="btn btn-sm bg-success/20 text-success hover:bg-success/30">
                                        {{ isUpdating === order.id ? '...' : 'Duyệt' }}
                                    </button>
                                    <button v-if="order.status === 'processing' || order.status === 'shipped'"
                                        @click="viewTracking(order)"
                                        class="btn btn-sm bg-info/20 text-info hover:bg-info/30">
                                        🚚 Theo dõi
                                    </button>
                                    <button v-if="order.status === 'pending' || order.status === 'processing'"
                                        @click="cancelOrder(order.id)" :disabled="isUpdating === order.id"
                                        class="btn btn-sm bg-error/20 text-error hover:bg-error/30">
                                        {{ isUpdating === order.id ? '...' : 'Hủy' }}
                                    </button>
                                    <span v-if="order.status !== 'pending' && order.status !== 'processing'"
                                        class="text-slate-500 text-sm">
                                        —
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!orders.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có đơn hàng nào</p>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1"
                    class="sticky bottom-0 flex items-center justify-center gap-2 p-4 border-t border-white/10 bg-dark-800">
                    <button @click="currentPage--; fetchOrders()" :disabled="currentPage <= 1"
                        class="btn btn-secondary btn-sm">{{ t('common.previous') }}</button>
                    <span class="text-slate-400 text-sm">{{ currentPage }} / {{ totalPages }}</span>
                    <button @click="currentPage++; fetchOrders()" :disabled="currentPage >= totalPages"
                        class="btn btn-secondary btn-sm">{{ t('common.next') }}</button>
                </div>
            </div>
        </div>

        <!-- Shipper Assignment Modal -->
        <div v-if="showShipperModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-dark-800 border border-white/10 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-white/10">
                    <h3 class="text-xl font-bold text-white">Giao cho Shipper</h3>
                    <p class="text-slate-400 text-sm mt-1">Chọn nhân viên giao hàng cho đơn #{{
                        selectedOrderForShipper?.order_number || selectedOrderForShipper?.id }}</p>
                </div>

                <div class="p-6 space-y-4">
                    <div v-for="shipper in shippers" :key="shipper.id"
                        class="flex items-center justify-between p-4 rounded-xl border transition-all cursor-pointer"
                        :class="selectedShipperId === shipper.id ? 'bg-primary/10 border-primary text-white' : 'bg-white/5 border-white/5 text-slate-300 hover:border-white/20'"
                        @click="selectedShipperId = shipper.id">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-lg">👤
                            </div>
                            <div>
                                <p class="font-medium">{{ shipper.name }}</p>
                                <p class="text-xs text-slate-500">{{ shipper.phone }}</p>
                            </div>
                        </div>
                        <div v-if="selectedShipperId === shipper.id" class="text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-dark-700/50 flex gap-3">
                    <button @click="showShipperModal = false"
                        class="flex-1 px-4 py-2 rounded-xl border border-white/10 text-white hover:bg-white/5 transition-colors">
                        Hủy
                    </button>
                    <button @click="confirmAssignShipper" :disabled="!selectedShipperId || isUpdating !== null"
                        class="flex-1 px-4 py-2 rounded-xl bg-primary text-white font-bold hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ isUpdating !== null ? 'Đang xử lý...' : 'Xác nhận' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
