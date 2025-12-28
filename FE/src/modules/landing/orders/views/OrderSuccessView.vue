<script setup lang="ts">
/**
 * Order Success View - Tailwind CSS Redesign
 * Premium dark theme with confetti animation and order details
 */
import { ref, onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import httpClient from '@/plugins/api/httpClient'

const route = useRoute()

// State
const order = ref<any>(null)
const isLoading = ref(true)

// Payment method labels
const paymentLabels: Record<string, { icon: string; label: string }> = {
    cod: { icon: '💵', label: 'Thanh toán khi nhận hàng' },
    bank_transfer: { icon: '🏦', label: 'Chuyển khoản ngân hàng' },
    momo: { icon: '📱', label: 'Ví MoMo' },
    vnpay: { icon: '💳', label: 'VNPay' },
    zalopay: { icon: '🔵', label: 'ZaloPay' },
    credit_card: { icon: '💳', label: 'Thẻ tín dụng' },
    e_wallet: { icon: '📱', label: 'Ví điện tử' }
}

// Timeline steps
const timeline = [
    { step: 1, icon: '🛒', label: 'Đặt hàng', status: 'done' },
    { step: 2, icon: '✅', label: 'Xác nhận', status: 'current' },
    { step: 3, icon: '📦', label: 'Đóng gói', status: 'pending' },
    { step: 4, icon: '🚚', label: 'Giao hàng', status: 'pending' },
    { step: 5, icon: '🎉', label: 'Hoàn tất', status: 'pending' }
]

// Helpers
function formatPrice(price: number | undefined | null) {
    if (price === undefined || price === null || isNaN(price)) {
        return '0 ₫'
    }
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

function getTotal(o: any) {
    return o?.total ?? o?.total_amount ?? 0
}

function getPaymentInfo(method: string) {
    return paymentLabels[method] || { icon: '💳', label: method || 'N/A' }
}

// Computed
const orderNumber = computed(() => order.value?.order_number || order.value?.id || 'N/A')
const itemCount = computed(() => order.value?.items?.length || 0)

// Fetch order
async function fetchOrder() {
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
</script>

<template>
    <div class="min-h-screen bg-dark-900 relative overflow-hidden py-8 md:py-12">
        <!-- Confetti Animation -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div v-for="i in 40" :key="i" 
                class="absolute w-3 h-3 rounded-sm animate-confetti"
                :style="{
                    left: Math.random() * 100 + '%',
                    animationDelay: Math.random() * 3 + 's',
                    animationDuration: (3 + Math.random() * 2) + 's',
                    backgroundColor: ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'][Math.floor(Math.random() * 6)]
                }">
            </div>
        </div>

        <!-- Background Glow -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-radial from-primary/20 via-transparent to-transparent blur-3xl opacity-50"></div>

        <div class="container relative z-10">
            <!-- Loading -->
            <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 gap-4">
                <div class="w-16 h-16 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
                <p class="text-slate-400">Đang tải thông tin đơn hàng...</p>
            </div>

            <!-- Success Content -->
            <div v-else-if="order" class="max-w-3xl mx-auto space-y-8">
                <!-- Success Header -->
                <div class="text-center space-y-4">
                    <!-- Animated Check Icon -->
                    <div class="relative inline-block">
                        <div class="absolute inset-0 bg-success/30 rounded-full blur-xl animate-pulse"></div>
                        <div class="relative w-24 h-24 mx-auto bg-gradient-to-br from-success to-primary rounded-full flex items-center justify-center animate-pop shadow-2xl shadow-success/30">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold">
                        <span class="bg-gradient-to-r from-success via-primary to-secondary bg-clip-text text-transparent">
                            Đặt hàng thành công!
                        </span>
                    </h1>
                    <p class="text-slate-400 text-lg">
                        Cảm ơn <span class="text-white font-semibold">{{ order.full_name || 'Quý khách' }}</span> đã tin tưởng mua sắm tại shop
                    </p>
                </div>

                <!-- Timeline -->
                <div class="relative">
                    <!-- Progress Line -->
                    <div class="absolute top-6 left-8 right-8 h-1 bg-dark-700 rounded-full">
                        <div class="h-full bg-gradient-to-r from-success to-primary rounded-full transition-all duration-1000" style="width: 25%"></div>
                    </div>

                    <!-- Steps -->
                    <div class="flex justify-between relative">
                        <div v-for="item in timeline" :key="item.step" class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold transition-all duration-300 border-4 border-dark-900"
                                :class="[
                                    item.status === 'done' ? 'bg-gradient-to-br from-success to-primary text-white shadow-lg shadow-success/30' :
                                    item.status === 'current' ? 'bg-primary text-white shadow-lg shadow-primary/30 animate-pulse' :
                                    'bg-dark-700 text-slate-500'
                                ]">
                                <span v-if="item.status === 'done'">✓</span>
                                <span v-else>{{ item.icon }}</span>
                            </div>
                            <span class="mt-2 text-xs font-medium hidden md:block"
                                :class="[item.status !== 'pending' ? 'text-white' : 'text-slate-500']">
                                {{ item.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Card -->
                <div class="bg-dark-800 rounded-3xl border border-white/5 overflow-hidden shadow-2xl">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-success/10 via-primary/10 to-secondary/10 px-6 py-4 border-b border-white/5">
                        <div class="flex items-center justify-center gap-3">
                            <span class="text-2xl">🎉</span>
                            <span class="px-4 py-2 bg-gradient-to-r from-success to-primary text-white text-sm font-bold rounded-full shadow-lg">
                                Đơn hàng đã được ghi nhận
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 space-y-6">
                        <!-- Order Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Order Number -->
                            <div class="flex items-center gap-4 p-4 bg-dark-700/50 rounded-2xl border border-white/5">
                                <span class="text-2xl">📋</span>
                                <div class="flex-1">
                                    <p class="text-xs text-slate-400 mb-1">Mã đơn hàng</p>
                                    <p class="text-lg font-bold text-primary font-mono">#{{ orderNumber }}</p>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="flex items-center gap-4 p-4 bg-dark-700/50 rounded-2xl border border-white/5">
                                <span class="text-2xl">{{ getPaymentInfo(order.payment_method).icon }}</span>
                                <div class="flex-1">
                                    <p class="text-xs text-slate-400 mb-1">Phương thức thanh toán</p>
                                    <p class="text-sm font-semibold text-white">{{ getPaymentInfo(order.payment_method).label }}</p>
                                </div>
                            </div>

                            <!-- Recipient -->
                            <div class="flex items-center gap-4 p-4 bg-dark-700/50 rounded-2xl border border-white/5">
                                <span class="text-2xl">👤</span>
                                <div class="flex-1">
                                    <p class="text-xs text-slate-400 mb-1">Người nhận</p>
                                    <p class="text-sm font-semibold text-white">{{ order.full_name || 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex items-center gap-4 p-4 bg-dark-700/50 rounded-2xl border border-white/5">
                                <span class="text-2xl">📞</span>
                                <div class="flex-1">
                                    <p class="text-xs text-slate-400 mb-1">Số điện thoại</p>
                                    <p class="text-sm font-semibold text-white">{{ order.phone || 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Address (Full Width) -->
                            <div class="md:col-span-2 flex items-start gap-4 p-4 bg-dark-700/50 rounded-2xl border border-white/5">
                                <span class="text-2xl">📍</span>
                                <div class="flex-1">
                                    <p class="text-xs text-slate-400 mb-1">Địa chỉ giao hàng</p>
                                    <p class="text-sm font-semibold text-white">{{ order.address_line || 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Item Count -->
                            <div class="flex items-center gap-4 p-4 bg-dark-700/50 rounded-2xl border border-white/5">
                                <span class="text-2xl">📦</span>
                                <div class="flex-1">
                                    <p class="text-xs text-slate-400 mb-1">Số lượng sản phẩm</p>
                                    <p class="text-sm font-semibold text-white">{{ itemCount }} sản phẩm</p>
                                </div>
                            </div>

                            <!-- Estimated Delivery -->
                            <div class="flex items-center gap-4 p-4 bg-dark-700/50 rounded-2xl border border-white/5">
                                <span class="text-2xl">🚚</span>
                                <div class="flex-1">
                                    <p class="text-xs text-slate-400 mb-1">Thời gian dự kiến</p>
                                    <p class="text-sm font-semibold text-success">2-3 ngày làm việc</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items Preview (if available) -->
                        <div v-if="order.items?.length" class="space-y-3">
                            <p class="text-sm font-semibold text-slate-400">Sản phẩm đã đặt</p>
                            <div class="max-h-48 overflow-y-auto space-y-2 pr-2 scrollbar-thin">
                                <div v-for="item in order.items" :key="item.id" 
                                    class="flex items-center gap-3 p-3 bg-dark-600/50 rounded-xl">
                                    <div class="w-12 h-12 bg-dark-700 rounded-lg overflow-hidden flex-shrink-0">
                                        <img v-if="item.product?.thumbnail" :src="item.product.thumbnail" 
                                            :alt="item.product?.name" class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                                            📦
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white line-clamp-1">
                                            {{ item.product?.name || 'Sản phẩm' }}
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            SL: {{ item.qty || item.quantity || 1 }} × {{ formatPrice(item.price) }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-bold text-primary">
                                        {{ formatPrice(item.subtotal || (item.price * (item.qty || item.quantity || 1))) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer - Total -->
                    <div class="px-6 py-5 bg-gradient-to-r from-success/5 via-primary/5 to-secondary/5 border-t border-white/5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">💰</span>
                                <span class="text-slate-400 font-medium">Tổng thanh toán</span>
                            </div>
                            <span class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-success via-primary to-secondary bg-clip-text text-transparent">
                                {{ formatPrice(getTotal(order)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contact Support -->
                <div class="bg-dark-800 rounded-2xl border border-white/5 p-5 flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-2xl flex items-center justify-center text-2xl">
                        📞
                    </div>
                    <div class="flex-1">
                        <p class="text-slate-400 text-sm">Mọi thắc mắc về đơn hàng, vui lòng liên hệ</p>
                        <p class="text-white font-bold text-lg mt-1">Hotline: <span class="text-primary">0969.123.456</span></p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <RouterLink to="/orders" 
                        class="btn btn-secondary flex items-center justify-center gap-2 py-4 px-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Xem đơn hàng của tôi
                    </RouterLink>
                    <RouterLink to="/products" 
                        class="btn btn-primary flex items-center justify-center gap-2 py-4 px-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Tiếp tục mua sắm
                    </RouterLink>
                </div>

                <!-- Trust Badges -->
                <div class="flex justify-center gap-6 pt-4">
                    <div class="text-center">
                        <div class="text-2xl mb-1">🔒</div>
                        <p class="text-[10px] text-slate-500">Bảo mật SSL</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-1">🚚</div>
                        <p class="text-[10px] text-slate-500">Giao hàng nhanh</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-1">↩️</div>
                        <p class="text-[10px] text-slate-500">Đổi trả 30 ngày</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-1">💬</div>
                        <p class="text-[10px] text-slate-500">Hỗ trợ 24/7</p>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div v-else class="text-center py-20">
                <div class="text-6xl mb-6">😕</div>
                <h2 class="text-2xl font-bold text-white mb-3">Không tìm thấy đơn hàng</h2>
                <p class="text-slate-400 mb-8">Đơn hàng không tồn tại hoặc đã bị xóa</p>
                <RouterLink to="/" class="btn btn-primary inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Về trang chủ
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Confetti Animation */
@keyframes confetti-fall {
    0% {
        transform: translateY(-10px) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
    }
}

.animate-confetti {
    animation: confetti-fall linear forwards;
}

/* Pop Animation for Success Icon */
@keyframes pop {
    0% {
        transform: scale(0);
    }
    70% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.animate-pop {
    animation: pop 0.5s ease-out forwards;
}
</style>
