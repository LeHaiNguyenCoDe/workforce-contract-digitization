<script setup lang="ts">
/**
 * Order List View
 * Uses useOrders composable for order management logic
 */
import { RouterLink } from 'vue-router'
import { useOrders } from '../composables/useOrders'


// Use composable
const {
    orders,
    isLoading,
    formatPrice,
    formatDate,
    getStatusLabel,
    getStatusColor
} = useOrders()

// Status config with icons
const statusConfig: Record<string, { icon: string; bg: string }> = {
    pending: { icon: '⏳', bg: 'bg-amber-500/10' },
    approved: { icon: '✓', bg: 'bg-blue-500/10' },
    processing: { icon: '⚙️', bg: 'bg-cyan-500/10' },
    shipped: { icon: '🚚', bg: 'bg-purple-500/10' },
    delivered: { icon: '✅', bg: 'bg-emerald-500/10' },
    cancelled: { icon: '✗', bg: 'bg-red-500/10' }
}

const getStatusIcon = (status: string) => statusConfig[status]?.icon || '•'
const getStatusBg = (status: string) => statusConfig[status]?.bg || 'bg-slate-500/10'
const getOrderTotal = (order: any) => order.total ?? order.total_amount ?? 0

// Timeline logic
const timelineSteps = [
    { key: 'pending', label: 'Đơn hàng được tạo', icon: '📋' }, // Matches user reference icon
    { key: 'approved', label: 'Đã xác nhận', icon: '✓' },    // Matches user reference icon
    { key: 'shipping', label: 'Chờ giao hàng', icon: '📦' },    // Matches user reference icon
    { key: 'delivered', label: 'Chờ nhận hàng', icon: '🏠' }    // Matches user reference icon
]

const getStepLabel = (order: any, step: any) => {
    if (step.key === 'delivered' && (order.status === 'delivered' || order.status === 'completed')) {
        return 'Đã nhận hàng'
    }
    return step.label
}

const getStepStatus = (orderStatus: string, stepKey: string) => {
    const statusMap: Record<string, number> = {
        'pending': 0,
        'approved': 1,
        'processing': 1,
        'shipped': 2,
        'delivered': 3,
        'completed': 3,
        'cancelled': -1
    }
    
    const currentLevel = statusMap[orderStatus] ?? 0
    const stepLevel = statusMap[stepKey] ?? 0
    
    if (currentLevel === -1) return 'cancelled'
    if (currentLevel >= stepLevel) return 'completed'
    if (currentLevel === stepLevel - 1) return 'active'
    return 'pending'
}
</script>

<template>
    <div class="container py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 p-6 bg-gradient-to-r from-primary/10 to-secondary/10 rounded-2xl border border-white/5">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold gradient-text mb-1">📦 Đơn hàng của tôi</h1>
                <p class="text-slate-400 text-sm">Theo dõi và quản lý đơn hàng của bạn</p>
            </div>
            <RouterLink to="/products" class="btn btn-primary">
                🛒 Tiếp tục mua sắm
            </RouterLink>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="i in 3" :key="i" class="h-48 bg-dark-700 rounded-2xl animate-pulse"></div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!orders.length" class="text-center py-16 bg-dark-800 rounded-2xl border border-white/5">
            <div class="text-6xl mb-4">📭</div>
            <h2 class="text-xl font-semibold text-white mb-2">Chưa có đơn hàng nào</h2>
            <p class="text-slate-400 mb-6 max-w-md mx-auto">Bạn chưa đặt đơn hàng nào. Hãy khám phá sản phẩm và đặt hàng ngay!</p>
            <RouterLink to="/products" class="btn btn-primary btn-lg">
                🛍️ Khám phá sản phẩm
            </RouterLink>
        </div>

        <!-- Orders Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <RouterLink v-for="order in orders" :key="order.id" :to="`/orders/${order.id}`" 
                class="group bg-dark-800 rounded-2xl border border-white/5 overflow-hidden hover:-translate-y-1 hover:shadow-2xl hover:border-primary/30 transition-all duration-300">
                <!-- Card Header -->
                <div class="flex justify-between items-center p-4 bg-dark-900/50 border-b border-white/5">
                    <div>
                        <span class="block text-xs text-slate-500">Mã đơn hàng</span>
                        <span class="font-mono font-bold text-white">#{{ order.code || order.order_number || order.id }}</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium" :class="getStatusBg(order.status)">
                        <span>{{ getStatusIcon(order.status) }}</span>
                        <span :class="'text-' + getStatusColor(order.status) + '-400'">{{ getStatusLabel(order.status) }}</span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-4 space-y-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">👤 Người nhận</span>
                        <span class="text-white font-medium">{{ order.full_name || 'Khách hàng' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">📅 Ngày đặt</span>
                        <span class="text-white">{{ formatDate(order.created_at) }}</span>
                    </div>

                    <!-- Visual Timeline -->
                    <div class="py-2">
                        <div class="flex items-center justify-between relative">
                            <!-- Progress Bar Background -->
                            <div class="absolute top-4 left-0 w-full h-0.5 bg-white/5 -z-0"></div>
                            
                            <!-- Steps -->
                            <div v-for="step in timelineSteps" :key="step.key" class="flex flex-col items-center gap-1.5 relative z-10">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs transition-colors duration-300"
                                    :class="[
                                        getStepStatus(order.status, step.key) === 'completed' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' :
                                        getStepStatus(order.status, step.key) === 'active' ? 'bg-primary text-white animate-pulse' :
                                        order.status === 'cancelled' ? 'bg-red-500/20 text-red-400' : 'bg-dark-900 border border-white/10 text-slate-500'
                                    ]">
                                    <template v-if="getStepStatus(order.status, step.key) === 'completed'">✓</template>
                                    <template v-else>{{ step.icon }}</template>
                                </div>
                                <span class="text-[10px] whitespace-nowrap" 
                                    :class="[
                                        getStepStatus(order.status, step.key) === 'completed' ? 'text-emerald-400' :
                                        getStepStatus(order.status, step.key) === 'active' ? 'text-primary font-bold' :
                                        'text-slate-500'
                                    ]">
                                    {{ getStepLabel(order, step) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm pt-2 border-t border-white/5">
                        <span class="text-slate-500">📦 Sản phẩm</span>
                        <span class="text-white font-medium">{{ order.items?.length || 0 }} sản phẩm</span>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-primary/5 to-secondary/5 border-t border-white/5">
                    <div>
                        <span class="block text-xs text-slate-500">Tổng tiền</span>
                        <span class="text-lg font-bold gradient-text">{{ formatPrice(getOrderTotal(order)) }}</span>
                    </div>
                    <span class="text-primary text-sm font-medium group-hover:translate-x-1 transition-transform">
                        Xem chi tiết →
                    </span>
                </div>
            </RouterLink>
        </div>
    </div>
</template>
