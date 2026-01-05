<script setup lang="ts">
/**
 * Order Success View - Redesigned to match minimalist mockup
 */
import { ref, onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import httpClient from '@/plugins/api/httpClient'

const route = useRoute()

// State
const order = ref<any>(null)
const isLoading = ref(true)

// Tab icons and labels (matching ProfileLayout)
const tabs = [
    {
        path: '/profile/cart',
        label: 'Giỏ hàng của tôi',
        svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>`
    },
    {
        path: '/profile/info',
        label: 'Thông tin của tôi',
        svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>`
    },
    {
        path: '/profile/address',
        label: 'Địa chỉ nhận hàng',
        svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`
    },
    {
        path: '/profile/payment',
        label: 'Phương thức thanh toán',
        svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10"/><line x1="7" x2="7" y1="15"/></svg>`
    }
]

// Progress tracking icons
const progressSteps = [
    { id: 1, label: '1. Giỏ hàng', icon: '🛒', completed: true },
    { id: 2, label: '2. Đặt hàng', icon: '✅', completed: true },
    { id: 3, label: '3. Vận chuyển', icon: '🚚', completed: false },
    { id: 4, label: '4. Thanh toán', icon: '💰', completed: false }
]

// Helpers
function formatPrice(price: number | undefined | null) {
    if (price === undefined || price === null || isNaN(price)) {
        return '0 ₫'
    }
    return new Intl.NumberFormat('vi-VN').format(price)
}

function getPaymentMethodLabel(method: string) {
    const labels: Record<string, string> = {
        cod: 'Thanh toán khi nhận hàng',
        credit_card: 'Thẻ tín dụng',
        momo: 'Ví MoMo',
        bank_transfer: 'Chuyển khoản ngân hàng'
    }
    return labels[method] || method || 'Trực tiếp'
}

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
    <div class="order-success-container max-w-[1200px] mx-auto px-4 py-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-gray-400 mb-8 text-lg">
            <RouterLink to="/" class="hover:text-[#9F7A5F] transition-colors">Home</RouterLink>
            <span>/</span>
            <RouterLink to="/profile/info" class="hover:text-[#9F7A5F] transition-colors">Tài khoản</RouterLink>
            <span>/</span>
            <RouterLink to="/profile/cart" class="hover:text-[#9F7A5F] transition-colors">Giỏ hàng</RouterLink>
            <span>/</span>
            <span class="text-gray-600">Đặt hàng thành công</span>
        </nav>

        <!-- Navigation Tabs -->
        <div class="flex border border-[#D9D9D9] mb-12 bg-white rounded-lg overflow-hidden h-24">
            <template v-for="(tab, index) in tabs" :key="tab.path">
                <RouterLink :to="tab.path"
                    class="flex-1 flex items-center justify-center gap-4 transition-all duration-300 group" :class="[
                        tab.path === '/profile/cart' ? 'bg-[#F2F2F2]' : 'hover:bg-gray-50',
                        index < tabs.length - 1 ? 'border-r border-[#D9D9D9]' : ''
                    ]">
                    <div class="text-[#9F7A5F]" v-html="tab.svg"></div>
                    <span class="font-medium text-lg"
                        :class="tab.path === '/profile/cart' ? 'text-black' : 'text-gray-600 group-hover:text-[#9F7A5F]'">
                        {{ tab.label }}
                    </span>
                </RouterLink>
            </template>
        </div>

        <!-- Success Message -->
        <div class="flex flex-col items-center mb-10">
            <div class="w-16 h-16 bg-[#52C41A] rounded-full flex items-center justify-center mb-4 shadow-sm">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="4">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h1 class="text-4xl font-medium text-[#9F7A5F]">Đặt hàng thành công</h1>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#9F7A5F]"></div>
        </div>

        <!-- Order Box -->
        <div v-else-if="order"
            class="max-w-[1000px] mx-auto border border-[#D9D9D9] rounded-sm bg-white overflow-hidden shadow-sm mb-12">
            <!-- Thank you section -->
            <div class="p-10 border-b border-[#D9D9D9]">
                <h2 class="text-3xl font-bold text-black mb-4">Cảm ơn {{ order.full_name?.split(' ').pop() || 'bạn' }}
                </h2>
                <p class="text-2xl text-black mb-2">Chúc mừng bạn đã đặt hàng thành công {{ order.items?.length || 0 }}
                    sản phẩm
                    của chúng tôi</p>
                <p class="text-2xl text-black">House sẽ sớm liên hệ với bạn để bàn giao sản phẩm nhanh nhất</p>
            </div>

            <!-- Detail Grid -->
            <div class="p-10 space-y-6 text-2xl">
                <div class="grid grid-cols-2">
                    <span class="text-black">Mã đơn hàng :</span>
                    <span class="text-black">{{ order.order_number || order.id }}</span>
                </div>
                <div class="grid grid-cols-2">
                    <span class="text-black">Phương thức thanh toán :</span>
                    <span class="text-black">{{ getPaymentMethodLabel(order.payment_method) }}</span>
                </div>
                <div class="grid grid-cols-2">
                    <span class="text-black">Thời gian nhận hàng dự kiến :</span>
                    <span class="text-black">2-3 ngày</span>
                </div>
                <div class="grid grid-cols-2">
                    <span class="text-black">Họ tên người nhận :</span>
                    <span class="text-black">{{ order.full_name }}</span>
                </div>
                <div class="grid grid-cols-2">
                    <span class="text-black">Địa chỉ nhận :</span>
                    <span class="text-black">{{ order.address_line }}</span>
                </div>
                <div class="grid grid-cols-2">
                    <span class="text-black">Tổng tiền :</span>
                    <span class="text-[#E54335] font-bold">{{ formatPrice(order.total || order.total_amount) }}</span>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="max-w-[800px] mx-auto mb-16 relative">
            <!-- Gray track -->
            <div class="absolute top-8 left-10 right-10 h-1 bg-[#D9D9D9]"></div>
            <!-- Green track -->
            <div class="absolute top-8 left-10 right-[66%] h-1 bg-[#52C41A]"></div>

            <div class="flex justify-between relative z-10">
                <div v-for="step in progressSteps" :key="step.id" class="flex flex-col items-center gap-4">
                    <div class="w-16 h-16 rounded-full border-2 flex items-center justify-center text-3xl transition-all"
                        :class="[
                            step.completed
                                ? 'bg-white border-[#52C41A] text-[#52C41A]'
                                : 'bg-white border-[#D9D9D9] text-[#D9D9D9]'
                        ]">
                        <span v-if="step.id === 1" class="p-2 rounded-full"
                            :class="step.completed ? 'bg-[#52C41A]/10' : ''">🛒</span>
                        <span v-else-if="step.id === 2" class="p-2 rounded-full"
                            :class="step.completed ? 'bg-[#52C41A]/10' : ''">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span v-else-if="step.id === 3" class="p-2">🚚</span>
                        <span v-else-if="step.id === 4" class="p-2">💰</span>
                    </div>
                    <span class="text-xl font-medium" :class="step.completed ? 'text-black' : 'text-gray-400'">
                        {{ step.label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Helpline Footer -->
        <div class="text-center space-y-2 mb-10">
            <p class="text-2xl text-black">Mọi thông tin, thắc mắc, sự cố về đơn hàng vui lòng gọi ngay</p>
            <p class="text-2xl text-black">cho chúng tôi tại Hotline: <span class="font-bold">0969.123.456</span></p>
        </div>
    </div>
</template>

<style scoped>
.order-success-container {
    background-color: transparent;
}
</style>
