<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import httpClient from '@/plugins/api/httpClient'

interface OrderItem {
    id: number
    product: { id: number; name: string }
    qty: number
    price: number
    subtotal: number
}

interface Order {
    id: number
    order_number: string
    full_name?: string
    status: string
    total: number
    total_amount?: number
    created_at: string
    items?: OrderItem[]
}

const orders = ref<Order[]>([])
const isLoading = ref(true)

const formatPrice = (price: number | undefined | null) => {
    if (price === undefined || price === null || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const formatDate = (date: string | undefined | null) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const statusConfig: Record<string, { text: string; color: string; icon: string; bg: string }> = {
    pending: { text: 'Chờ xác nhận', color: 'text-amber-400', icon: '⏳', bg: 'bg-amber-500/10' },
    approved: { text: 'Đã xác nhận', color: 'text-blue-400', icon: '✓', bg: 'bg-blue-500/10' },
    processing: { text: 'Đang xử lý', color: 'text-cyan-400', icon: '⚙️', bg: 'bg-cyan-500/10' },
    shipped: { text: 'Đang giao', color: 'text-purple-400', icon: '🚚', bg: 'bg-purple-500/10' },
    delivered: { text: 'Đã giao', color: 'text-emerald-400', icon: '✅', bg: 'bg-emerald-500/10' },
    cancelled: { text: 'Đã hủy', color: 'text-red-400', icon: '✗', bg: 'bg-red-500/10' }
}

const getStatus = (status: string) => statusConfig[status] || { text: status, color: 'text-slate-400', icon: '•', bg: 'bg-slate-500/10' }
const getOrderTotal = (order: Order) => order.total ?? order.total_amount ?? 0

const fetchOrders = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/frontend/orders')
        const data = response.data as any
        if (Array.isArray(data)) {
            orders.value = data
        } else if (data?.data) {
            orders.value = Array.isArray(data.data) ? data.data : data.data?.data || []
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

onMounted(fetchOrders)
</script>

<template>
    <div class="orders-page">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1>📦 Đơn hàng của tôi</h1>
                    <p>Theo dõi và quản lý đơn hàng của bạn</p>
                </div>
                <RouterLink to="/products" class="btn btn-primary">
                    🛒 Tiếp tục mua sắm
                </RouterLink>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="loading-grid">
                <div v-for="i in 3" :key="i" class="skeleton-card"></div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!orders.length" class="empty-state">
                <div class="empty-icon">📭</div>
                <h2>Chưa có đơn hàng nào</h2>
                <p>Bạn chưa đặt đơn hàng nào. Hãy khám phá sản phẩm và đặt hàng ngay!</p>
                <RouterLink to="/products" class="btn btn-primary btn-lg">
                    🛍️ Khám phá sản phẩm
                </RouterLink>
            </div>

            <!-- Orders List -->
            <div v-else class="orders-grid">
                <RouterLink v-for="order in orders" :key="order.id" :to="`/orders/${order.id}`" class="order-card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="order-id">
                            <span class="label">Mã đơn hàng</span>
                            <span class="value">#{{ order.order_number || order.id }}</span>
                        </div>
                        <div class="order-status" :class="[getStatus(order.status).bg]">
                            <span class="status-icon">{{ getStatus(order.status).icon }}</span>
                            <span :class="getStatus(order.status).color">{{ getStatus(order.status).text }}</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="order-info">
                            <div class="info-item">
                                <span class="info-label">👤 Người nhận</span>
                                <span class="info-value">{{ order.full_name || 'Khách hàng' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">📅 Ngày đặt</span>
                                <span class="info-value">{{ formatDate(order.created_at) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">📦 Sản phẩm</span>
                                <span class="info-value">{{ order.items?.length || 0 }} sản phẩm</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer">
                        <div class="order-total">
                            <span class="label">Tổng tiền</span>
                            <span class="value">{{ formatPrice(getOrderTotal(order)) }}</span>
                        </div>
                        <div class="view-btn">
                            Xem chi tiết →
                        </div>
                    </div>
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
.orders-page {
    min-height: 100vh;
    padding: var(--space-8) 0;
    background: linear-gradient(180deg, var(--color-bg-primary) 0%, var(--color-bg-secondary) 100%);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-8);
    padding: var(--space-6);
    background: linear-gradient(135deg, rgba(var(--color-primary-rgb), 0.1), rgba(var(--color-secondary-rgb), 0.1));
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.header-content h1 {
    font-size: var(--text-2xl);
    font-weight: 700;
    margin-bottom: var(--space-1);
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.header-content p {
    color: var(--color-text-secondary);
    font-size: var(--text-sm);
}

.loading-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--space-6);
}

.skeleton-card {
    height: 200px;
    background: linear-gradient(90deg, var(--color-bg-tertiary) 25%, var(--color-bg-secondary) 50%, var(--color-bg-tertiary) 75%);
    background-size: 200% 100%;
    border-radius: var(--radius-xl);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

.empty-state {
    text-align: center;
    padding: var(--space-16);
    background: var(--color-bg-secondary);
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: var(--space-4);
}

.empty-state h2 {
    font-size: var(--text-xl);
    margin-bottom: var(--space-2);
}

.empty-state p {
    color: var(--color-text-secondary);
    margin-bottom: var(--space-6);
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn-lg {
    padding: var(--space-4) var(--space-8);
    font-size: var(--text-base);
}

.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--space-6);
}

.order-card {
    background: var(--color-bg-secondary);
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255, 255, 255, 0.05);
    overflow: hidden;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    flex-direction: column;
}

.order-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    border-color: rgba(var(--color-primary-rgb), 0.3);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4) var(--space-5);
    background: var(--color-bg-tertiary);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.order-id .label {
    display: block;
    font-size: var(--text-xs);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-1);
}

.order-id .value {
    font-family: monospace;
    font-weight: 700;
    font-size: var(--text-base);
    color: var(--color-text-primary);
}

.order-status {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: 500;
}

.status-icon {
    font-size: 1rem;
}

.card-body {
    padding: var(--space-5);
    flex: 1;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
}

.info-value {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--color-text-primary);
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4) var(--space-5);
    background: linear-gradient(135deg, rgba(var(--color-primary-rgb), 0.05), rgba(var(--color-secondary-rgb), 0.05));
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.order-total .label {
    display: block;
    font-size: var(--text-xs);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-1);
}

.order-total .value {
    font-size: var(--text-lg);
    font-weight: 700;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.view-btn {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--color-primary);
    transition: transform 0.2s;
}

.order-card:hover .view-btn {
    transform: translateX(4px);
}

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        text-align: center;
        gap: var(--space-4);
    }

    .orders-grid {
        grid-template-columns: 1fr;
    }
}
</style>
