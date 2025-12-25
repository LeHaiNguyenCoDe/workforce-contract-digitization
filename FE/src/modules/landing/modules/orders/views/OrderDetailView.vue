<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '@/plugins/api/httpClient'
import type { Order, ApiResponse } from '@/api/types'

const route = useRoute()
const order = ref<Order | null>(null)
const isLoading = ref(true)

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('vi-VN')
}

const statusLabels: Record<string, { text: string; class: string }> = {
    pending: { text: 'Chờ xử lý', class: 'status-pending' },
    processing: { text: 'Đang xử lý', class: 'status-processing' },
    shipped: { text: 'Đang giao', class: 'status-shipped' },
    delivered: { text: 'Đã giao', class: 'status-delivered' },
    cancelled: { text: 'Đã hủy', class: 'status-cancelled' }
}

const fetchOrder = async () => {
    isLoading.value = true
    try {
        const response = await apiClient.get<ApiResponse<Order>>(`/frontend/orders/${route.params.id}`)
        order.value = response.data.data || null
    } catch (error) {
        console.error('Failed to fetch order:', error)
    } finally {
        isLoading.value = false
    }
}

onMounted(fetchOrder)
</script>

<template>
    <div class="order-detail-page">
        <div class="container">
            <RouterLink to="/orders" class="back-link">← Quay lại đơn hàng</RouterLink>

            <div v-if="isLoading" class="loading-state">
                <div class="skeleton-block"></div>
            </div>

            <div v-else-if="order" class="order-detail">
                <div class="order-header card">
                    <div class="header-info">
                        <h1>Đơn hàng #{{ order.order_number }}</h1>
                        <p>Đặt lúc {{ formatDate(order.created_at) }}</p>
                    </div>
                    <span class="order-status" :class="statusLabels[order.status].class">
                        {{ statusLabels[order.status].text }}
                    </span>
                </div>

                <div class="order-grid">
                    <div class="order-items card">
                        <h3>Sản phẩm</h3>
                        <div v-for="item in order.items" :key="item.id" class="order-item">
                            <div class="item-info">
                                <span class="item-name">{{ item.product.name }}</span>
                                <span class="item-qty">x{{ item.qty }}</span>
                            </div>
                            <span class="item-price">{{ formatPrice(item.subtotal) }}</span>
                        </div>
                        <div class="order-total">
                            <span>Tổng cộng</span>
                            <span>{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>

                    <div class="order-info card">
                        <h3>Thông tin giao hàng</h3>
                        <div class="info-row">
                            <span class="label">Địa chỉ</span>
                            <span>{{ order.shipping_address }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Điện thoại</span>
                            <span>{{ order.shipping_phone }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Thanh toán</span>
                            <span>{{ order.payment_method }}</span>
                        </div>
                        <div class="info-row" v-if="order.notes">
                            <span class="label">Ghi chú</span>
                            <span>{{ order.notes }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.back-link {
    display: inline-block;
    margin-bottom: var(--space-6);
    color: var(--color-text-muted);
}

.back-link:hover {
    color: var(--color-primary);
}

.loading-state .skeleton-block {
    height: 400px;
    background: var(--color-bg-tertiary);
    border-radius: var(--radius-lg);
    animation: pulse 2s infinite;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-6);
}

.order-header h1 {
    font-size: var(--text-2xl);
    margin-bottom: var(--space-1);
}

.order-header p {
    color: var(--color-text-muted);
    font-size: var(--text-sm);
}

.order-status {
    padding: var(--space-2) var(--space-4);
    font-size: var(--text-sm);
    font-weight: 600;
    border-radius: var(--radius-full);
}

.status-pending {
    background: rgba(245, 158, 11, 0.2);
    color: var(--color-warning);
}

.status-processing {
    background: rgba(14, 165, 233, 0.2);
    color: var(--color-info);
}

.status-shipped {
    background: rgba(99, 102, 241, 0.2);
    color: var(--color-primary);
}

.status-delivered {
    background: rgba(16, 185, 129, 0.2);
    color: var(--color-success);
}

.status-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: var(--color-error);
}

.order-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: var(--space-6);
}

.order-items h3,
.order-info h3 {
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-4);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: var(--space-3) 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.item-info {
    display: flex;
    gap: var(--space-3);
}

.item-name {
    color: var(--color-text-primary);
}

.item-qty {
    color: var(--color-text-muted);
}

.item-price {
    font-weight: 500;
}

.order-total {
    display: flex;
    justify-content: space-between;
    padding-top: var(--space-4);
    font-size: var(--text-lg);
    font-weight: 700;
}

.info-row {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    margin-bottom: var(--space-4);
}

.info-row .label {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

@media (max-width: 768px) {
    .order-grid {
        grid-template-columns: 1fr;
    }
}
</style>
