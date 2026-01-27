<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import httpClient from '@/plugins/api/httpClient'


interface WarehouseStats {
    totalProducts: number
    newProducts: number
    stockProducts: number
    expiringSoon: number
    outOfStock: number
    totalSuppliers: number
    pendingQualityCheck: number
}

interface Order {
    id: number
    order_number: string
    full_name?: string
    phone?: string
    user?: { name: string }
    total: number
    total_amount?: number
    status: string
    created_at: string
}

const stats = ref<WarehouseStats>({
    totalProducts: 0,
    newProducts: 0,
    stockProducts: 0,
    expiringSoon: 0,
    outOfStock: 0,
    totalSuppliers: 0,
    pendingQualityCheck: 0
})
const isLoading = ref(true)
const recentActivities = ref<any[]>([])
const pendingOrders = ref<Order[]>([])
const isUpdatingOrder = ref<number | null>(null)

const fetchStats = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/admin/warehouses/dashboard-stats')
        const data = (response.data as any).data

        stats.value = {
            totalProducts: data.totalProducts,
            newProducts: data.newProducts,
            stockProducts: data.stockProducts,
            expiringSoon: data.expiringSoon,
            outOfStock: data.outOfStock,
            totalSuppliers: data.totalSuppliers,
            pendingQualityCheck: data.pendingQualityCheck
        }

        // Fetch recent activities (mocked for now, will connect to stock movements later)
        recentActivities.value = [
            { id: 1, type: 'import', message: 'Hệ thống đã kết nối dữ liệu thực', time: 'Vừa xong', icon: '⚡' },
            { id: 2, type: 'export', message: 'Xuất kho 5 SP cho đơn #ORD-001', time: '15 phút trước', icon: '🚚' },
            { id: 3, type: 'alert', message: 'SP "Samsung Galaxy S24" sắp hết hàng', time: '1 giờ trước', icon: '⚠️' },
            { id: 4, type: 'quality', message: 'Kiểm tra chất lượng lô hàng #LOT-123', time: '3 giờ trước', icon: '✅' },
        ]
    } catch (error) {
        console.error('Failed to fetch stats:', error)
    } finally {
        isLoading.value = false
    }
}

const fetchPendingOrders = async () => {
    try {
        const response = await httpClient.get('/admin/orders', { params: { status: 'pending', per_page: 5 } })
        const data = response.data as any
        pendingOrders.value = data?.data?.data || data?.data || []
    } catch (error) {
        console.error('Failed to fetch pending orders:', error)
    }
}

const updateOrderStatus = async (orderId: number, newStatus: string) => {
    isUpdatingOrder.value = orderId
    try {
        await httpClient.put(`/admin/orders/${orderId}/status`, { status: newStatus })
        await fetchPendingOrders()
    } catch (error: any) {
        console.error('Failed to update order status:', error)
        alert(error.response?.data?.message || 'Cập nhật trạng thái thất bại!')
    } finally {
        isUpdatingOrder.value = null
    }
}

const cancelOrder = async (orderId: number) => {
    if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) return
    isUpdatingOrder.value = orderId
    try {
        await httpClient.put(`/admin/orders/${orderId}/cancel`)
        await fetchPendingOrders()
    } catch (error: any) {
        console.error('Failed to cancel order:', error)
        alert(error.response?.data?.message || 'Hủy đơn hàng thất bại!')
    } finally {
        isUpdatingOrder.value = null
    }
}

const formatPrice = (price: number | undefined) => {
    if (!price) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const formatDate = (date: string) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('vi-VN')
}

const getCustomerName = (order: Order) => order.full_name || order.user?.name || 'Khách vãng lai'

onMounted(() => {
    fetchStats()
    fetchPendingOrders()
})

const statCards = computed(() => [
    { label: 'Tổng sản phẩm', value: stats.value.totalProducts, icon: '📦', color: 'from-blue-500 to-blue-600', link: '/admin/warehouse/products' },
    { label: 'SP mới nhập', value: stats.value.newProducts, icon: '🆕', color: 'from-green-500 to-green-600', link: '/admin/warehouse/products?filter=new' },
    { label: 'SP tồn kho', value: stats.value.stockProducts, icon: '📊', color: 'from-purple-500 to-purple-600', link: '/admin/warehouse/products?filter=stock' },
    { label: 'Sắp hết hạn', value: stats.value.expiringSoon, icon: '⏰', color: 'from-amber-500 to-amber-600', link: '/admin/warehouse/products?filter=expiring' },
    { label: 'Hết hàng', value: stats.value.outOfStock, icon: '❌', color: 'from-red-500 to-red-600', link: '/admin/warehouse/products?filter=outofstock' },
    { label: 'Nhà cung cấp', value: stats.value.totalSuppliers, icon: '🏭', color: 'from-cyan-500 to-cyan-600', link: '/admin/warehouse/suppliers' },
])
</script>

<template>
    <div class="warehouse-dashboard p-6">
        <!-- Header -->
        <div class="header mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">🏬 Quản lý Kho hàng</h1>
            <p class="text-slate-400">Tổng quan tình trạng kho và hoạt động</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid mb-8">
            <RouterLink v-for="card in statCards" :key="card.label" :to="card.link" class="stat-card">
                <div class="stat-icon" :class="`bg-gradient-to-br ${card.color}`">
                    {{ card.icon }}
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ card.value }}</span>
                    <span class="stat-label">{{ card.label }}</span>
                </div>
            </RouterLink>
        </div>

        <!-- Pending Orders Section -->
        <div class="orders-section mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">📦 Đơn hàng chờ xử lý</h2>
                <RouterLink to="/admin/orders" class="text-primary text-sm hover:underline">Xem tất cả →</RouterLink>
            </div>
            <div v-if="pendingOrders.length" class="orders-list">
                <div v-for="order in pendingOrders" :key="order.id" class="order-item">
                    <div class="order-info">
                        <span class="order-number">#{{ order.order_number || order.id }}</span>
                        <span class="order-customer">{{ getCustomerName(order) }}</span>
                        <span class="order-date">{{ formatDate(order.created_at) }}</span>
                    </div>
                    <div class="order-total">{{ formatPrice(order.total || order.total_amount) }}</div>
                    <div class="order-actions">
                        <button class="btn btn-sm bg-success/20 text-success hover:bg-success/30"
                            :disabled="isUpdatingOrder === order.id" @click="updateOrderStatus(order.id, 'processing')">
                            {{ isUpdatingOrder === order.id ? '...' : '✓ Duyệt' }}
                        </button>
                        <button class="btn btn-sm bg-error/20 text-error hover:bg-error/30"
                            :disabled="isUpdatingOrder === order.id" @click="cancelOrder(order.id)">
                            ✕ Hủy
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="empty-orders">
                <p class="text-slate-500">Không có đơn hàng chờ xử lý</p>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="activities-section">
            <h2 class="section-title">📋 Hoạt động gần đây</h2>
            <div class="activities-list">
                <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
                    <span class="activity-icon">{{ activity.icon }}</span>
                    <div class="activity-content">
                        <p class="activity-message">{{ activity.message }}</p>
                        <span class="activity-time">{{ activity.time }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
