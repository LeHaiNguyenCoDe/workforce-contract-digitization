<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import httpClient from '@/plugins/api/httpClient'

const route = useRoute()

interface Order {
    id: number
    order_number: string
    full_name: string
    phone: string
    address_line: string
    payment_method: string
    status: string
    total: number
    total_amount?: number
    created_at: string
    items?: any[]
}

const order = ref<Order | null>(null)
const isLoading = ref(true)

const paymentLabels: Record<string, string> = {
    cod: '💵 Thanh toán khi nhận hàng',
    bank_transfer: '🏦 Chuyển khoản ngân hàng',
    credit_card: '💳 Thẻ tín dụng',
    e_wallet: '📱 Ví điện tử'
}

const formatPrice = (price: number | undefined | null) => {
    if (price === undefined || price === null || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const getTotal = (o: Order) => o.total ?? o.total_amount ?? 0

const fetchOrder = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get(`/frontend/orders/${route.params.id}`)
        const data = response.data as any
        order.value = data?.data || data
    } catch (error) {
        console.error('Failed to fetch order:', error)
    } finally {
        isLoading.value = false
    }
}

onMounted(fetchOrder)

const timeline = [
    { step: 1, icon: '🛒', label: 'Giỏ hàng', done: true },
    { step: 2, icon: '📝', label: 'Đặt hàng', done: true },
    { step: 3, icon: '🚚', label: 'Giao hàng', done: false },
    { step: 4, icon: '✅', label: 'Hoàn tất', done: false }
]
</script>

<template>
    <div class="success-page">
        <!-- Confetti Animation -->
        <div class="confetti">
            <div v-for="i in 50" :key="i" class="confetti-piece" :style="{
                left: Math.random() * 100 + '%',
                animationDelay: Math.random() * 3 + 's',
                backgroundColor: ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#3b82f6'][Math.floor(Math.random() * 5)]
            }"></div>
        </div>

        <div class="container">
            <!-- Loading -->
            <div v-if="isLoading" class="loading">
                <div class="spinner"></div>
            </div>

            <!-- Success Content -->
            <div v-else-if="order" class="success-content">
                <!-- Success Header -->
                <div class="success-header">
                    <div class="success-icon">
                        <span class="icon-circle">✓</span>
                    </div>
                    <h1>Đặt hàng thành công!</h1>
                    <p class="subtitle">
                        Cảm ơn <strong>{{ order.full_name || 'Quý khách' }}</strong> đã đặt hàng tại shop của chúng tôi
                    </p>
                </div>

                <!-- Timeline -->
                <div class="timeline-section">
                    <div class="timeline">
                        <div v-for="(item, index) in timeline" :key="item.step" class="timeline-item"
                            :class="{ done: item.done, current: index === 1 }">
                            <div class="timeline-dot">
                                <span>{{ item.icon }}</span>
                            </div>
                            <span class="timeline-label">{{ item.label }}</span>
                        </div>
                        <div class="timeline-line">
                            <div class="timeline-progress"></div>
                        </div>
                    </div>
                </div>

                <!-- Order Details Card -->
                <div class="order-card">
                    <div class="card-header">
                        <div class="order-badge">
                            🎉 Đơn hàng đã được ghi nhận
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-icon">📋</span>
                                <div class="info-content">
                                    <span class="info-label">Mã đơn hàng</span>
                                    <span class="info-value highlight">#{{ order.order_number || order.id }}</span>
                                </div>
                            </div>

                            <div class="info-item">
                                <span class="info-icon">💳</span>
                                <div class="info-content">
                                    <span class="info-label">Phương thức thanh toán</span>
                                    <span class="info-value">{{ paymentLabels[order.payment_method] ||
                                        order.payment_method }}</span>
                                </div>
                            </div>

                            <div class="info-item">
                                <span class="info-icon">👤</span>
                                <div class="info-content">
                                    <span class="info-label">Người nhận</span>
                                    <span class="info-value">{{ order.full_name || 'Quý khách' }}</span>
                                </div>
                            </div>

                            <div class="info-item">
                                <span class="info-icon">📞</span>
                                <div class="info-content">
                                    <span class="info-label">Số điện thoại</span>
                                    <span class="info-value">{{ order.phone || 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="info-item full-width">
                                <span class="info-icon">📍</span>
                                <div class="info-content">
                                    <span class="info-label">Địa chỉ giao hàng</span>
                                    <span class="info-value">{{ order.address_line || 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="info-item">
                                <span class="info-icon">📦</span>
                                <div class="info-content">
                                    <span class="info-label">Số lượng sản phẩm</span>
                                    <span class="info-value">{{ order.items?.length || 0 }} sản phẩm</span>
                                </div>
                            </div>

                            <div class="info-item">
                                <span class="info-icon">🚚</span>
                                <div class="info-content">
                                    <span class="info-label">Thời gian dự kiến</span>
                                    <span class="info-value">2-3 ngày làm việc</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="total-section">
                            <span class="total-label">💰 Tổng thanh toán</span>
                            <span class="total-value">{{ formatPrice(getTotal(order)) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="contact-section">
                    <div class="contact-card">
                        <span class="contact-icon">📞</span>
                        <div class="contact-info">
                            <p>Mọi thắc mắc về đơn hàng, vui lòng liên hệ</p>
                            <p class="hotline">Hotline: <strong>0969.123.456</strong></p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="actions">
                    <RouterLink to="/orders" class="btn btn-secondary">
                        📋 Xem đơn hàng của tôi
                    </RouterLink>
                    <RouterLink to="/products" class="btn btn-primary">
                        🛍️ Tiếp tục mua sắm
                    </RouterLink>
                </div>
            </div>

            <!-- Error -->
            <div v-else class="error-state">
                <div class="error-icon">😕</div>
                <h2>Không tìm thấy đơn hàng</h2>
                <p>Đơn hàng không tồn tại hoặc đã bị xóa</p>
                <RouterLink to="/" class="btn btn-primary">Về trang chủ</RouterLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
.success-page {
    min-height: 100vh;
    padding: var(--space-8) 0;
    background: linear-gradient(180deg, var(--color-bg-primary) 0%, var(--color-bg-secondary) 100%);
    position: relative;
    overflow: hidden;
}

/* Confetti Animation */
.confetti {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}

.confetti-piece {
    position: absolute;
    width: 10px;
    height: 10px;
    top: -10px;
    border-radius: 2px;
    animation: confetti-fall 4s ease-out forwards;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
    }

    100% {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
    }
}

.container {
    position: relative;
    z-index: 1;
}

.loading {
    display: flex;
    justify-content: center;
    padding: var(--space-16);
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid var(--color-bg-tertiary);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.success-content {
    max-width: 700px;
    margin: 0 auto;
}

.success-header {
    text-align: center;
    margin-bottom: var(--space-8);
}

.success-icon {
    margin-bottom: var(--space-4);
}

.icon-circle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #10b981, #3b82f6);
    border-radius: 50%;
    font-size: 2.5rem;
    color: white;
    animation: pop 0.5s ease-out;
}

@keyframes pop {
    0% {
        transform: scale(0);
    }

    80% {
        transform: scale(1.1);
    }

    100% {
        transform: scale(1);
    }
}

.success-header h1 {
    font-size: var(--text-3xl);
    font-weight: 700;
    margin-bottom: var(--space-2);
    background: linear-gradient(135deg, #10b981, #3b82f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
    color: var(--color-text-secondary);
    font-size: var(--text-base);
}

.timeline-section {
    margin-bottom: var(--space-8);
}

.timeline {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding: 0 var(--space-4);
}

.timeline-line {
    position: absolute;
    top: 20px;
    left: 40px;
    right: 40px;
    height: 4px;
    background: var(--color-bg-tertiary);
    border-radius: 2px;
    z-index: 0;
}

.timeline-progress {
    width: 35%;
    height: 100%;
    background: linear-gradient(90deg, #10b981, #3b82f6);
    border-radius: 2px;
}

.timeline-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 1;
}

.timeline-dot {
    width: 44px;
    height: 44px;
    background: var(--color-bg-tertiary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: var(--space-2);
    border: 3px solid var(--color-bg-primary);
    transition: all 0.3s;
}

.timeline-item.done .timeline-dot {
    background: linear-gradient(135deg, #10b981, #3b82f6);
}

.timeline-item.current .timeline-dot {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);
}

.timeline-label {
    font-size: var(--text-xs);
    color: var(--color-text-secondary);
    font-weight: 500;
}

.timeline-item.done .timeline-label {
    color: var(--color-primary);
}

.order-card {
    background: var(--color-bg-secondary);
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255, 255, 255, 0.05);
    overflow: hidden;
    margin-bottom: var(--space-6);
}

.card-header {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1));
    padding: var(--space-4);
    text-align: center;
}

.order-badge {
    display: inline-block;
    padding: var(--space-2) var(--space-4);
    background: linear-gradient(135deg, #10b981, #3b82f6);
    color: white;
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: 600;
}

.card-body {
    padding: var(--space-6);
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

.info-item {
    display: flex;
    gap: var(--space-3);
    padding: var(--space-3);
    background: var(--color-bg-tertiary);
    border-radius: var(--radius-lg);
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.info-label {
    font-size: var(--text-xs);
    color: var(--color-text-secondary);
}

.info-value {
    font-size: var(--text-sm);
    color: var(--color-text-primary);
    font-weight: 500;
}

.info-value.highlight {
    font-family: monospace;
    color: var(--color-primary);
    font-weight: 700;
}

.card-footer {
    padding: var(--space-6);
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(59, 130, 246, 0.05));
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.total-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-label {
    font-size: var(--text-base);
    color: var(--color-text-secondary);
}

.total-value {
    font-size: var(--text-2xl);
    font-weight: 700;
    background: linear-gradient(135deg, #10b981, #3b82f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.contact-section {
    margin-bottom: var(--space-6);
}

.contact-card {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--color-bg-secondary);
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.contact-icon {
    font-size: 2rem;
}

.contact-info p {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
}

.hotline {
    color: var(--color-text-primary);
}

.actions {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
}

.btn {
    padding: var(--space-3) var(--space-6);
    border-radius: var(--radius-lg);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-secondary {
    background: var(--color-bg-tertiary);
    color: var(--color-text-primary);
}

.btn-secondary:hover {
    background: var(--color-bg-secondary);
}

.btn-primary {
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(var(--color-primary-rgb), 0.4);
}

.error-state {
    text-align: center;
    padding: var(--space-16);
}

.error-icon {
    font-size: 4rem;
    margin-bottom: var(--space-4);
}

.error-state h2 {
    margin-bottom: var(--space-2);
}

.error-state p {
    color: var(--color-text-secondary);
    margin-bottom: var(--space-6);
}

@media (max-width: 640px) {
    .info-grid {
        grid-template-columns: 1fr;
    }

    .actions {
        flex-direction: column;
    }

    .timeline-label {
        display: none;
    }
}
</style>
