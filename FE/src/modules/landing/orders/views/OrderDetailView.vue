<script setup lang="ts">
/**
 * Order Detail View
 * Uses useOrders composable for order detail logic
 */
import { onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useOrders } from '../composables/useOrders'

const route = useRoute()

// Use composable
const {
    currentOrder: order,
    isLoading,
    formatPrice,
    formatDate,
    getStatusLabel,
    getStatusColor,
    fetchOrderById
} = useOrders()

onMounted(() => {
    if (route.params.id) {
        fetchOrderById(route.params.id as string)
    }
})
</script>

<template>
    <div class="container py-8">
        <RouterLink to="/orders" class="inline-flex items-center gap-2 text-slate-400 hover:text-primary transition-colors mb-6">
            ← Quay lại đơn hàng
        </RouterLink>

        <!-- Loading -->
        <div v-if="isLoading" class="h-96 bg-dark-800 rounded-2xl animate-pulse"></div>

        <!-- Order Detail -->
        <div v-else-if="order" class="space-y-6">
            <!-- Header -->
            <div class="card flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold text-white">Đơn hàng #{{ order.code || order.order_number || order.id }}</h1>
                    <p class="text-slate-400 text-sm">Đặt lúc {{ formatDate(order.created_at) }}</p>
                </div>
                <div class="px-4 py-2 rounded-full text-sm font-bold" 
                    :class="['bg-' + getStatusColor(order.status) + '-500/10', 'text-' + getStatusColor(order.status) + '-400']">
                    {{ getStatusLabel(order.status) }}
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Items list -->
                <div class="lg:col-span-2 card space-y-4">
                    <h3 class="text-lg font-bold text-white border-b border-white/5 pb-4">Sản phẩm</h3>
                    <div class="divide-y divide-white/5">
                        <div v-for="item in order.items" :key="item.id" class="flex justify-between py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-white font-medium">{{ item.product.name }}</span>
                                <span class="text-slate-500 text-sm">x{{ item.qty }}</span>
                            </div>
                            <span class="text-white font-bold">{{ formatPrice(item.subtotal) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-white/5 font-bold text-lg">
                        <span class="text-slate-400">Tổng cộng</span>
                        <span class="gradient-text">{{ formatPrice(order.total_amount || order.total) }}</span>
                    </div>
                </div>

                <!-- Info -->
                <div class="lg:col-span-1 card space-y-6">
                    <h3 class="text-lg font-bold text-white border-b border-white/5 pb-4">Thông tin giao hàng</h3>
                    
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <span class="block text-xs text-slate-500 uppercase font-bold">Địa chỉ</span>
                            <span class="text-white text-sm leading-relaxed">{{ order.address_line || order.shipping_address }}</span>
                        </div>
                        <div class="space-y-1">
                            <span class="block text-xs text-slate-500 uppercase font-bold">Điện thoại</span>
                            <span class="text-white">{{ order.phone || order.shipping_phone }}</span>
                        </div>
                        <div class="space-y-1">
                            <span class="block text-xs text-slate-500 uppercase font-bold">Thanh toán</span>
                            <span class="text-white uppercase">{{ order.payment_method }}</span>
                        </div>
                        <div v-if="order.note || order.notes" class="space-y-1">
                            <span class="block text-xs text-slate-500 uppercase font-bold">Ghi chú</span>
                            <span class="text-white text-sm italic">{{ order.note || order.notes }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Not Found -->
        <div v-else class="text-center py-16 bg-dark-800 rounded-2xl">
            <h2 class="text-xl font-bold text-slate-400 mb-4">Không tìm thấy đơn hàng</h2>
            <RouterLink to="/orders" class="btn btn-primary">Quay lại danh sách</RouterLink>
        </div>
    </div>
</template>
