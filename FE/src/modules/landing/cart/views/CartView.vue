<script setup lang="ts">
/**
 * Cart View - Redesigned to match minimalist mockup
 */
import { ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useCart } from '../composables/useCart'

const { t } = useI18n()
const route = useRoute()

// Use composable
const {
    cart,
    isLoading,
    total,
    isEmpty,
    itemCount,
    updateQuantity,
    removeItem,
    isItemUpdating,
    formatPrice
} = useCart()

// Local state
const removingItemId = ref<number | null>(null)

// Handle remove with animation
function handleRemoveItem(itemId: number) {
    removingItemId.value = itemId
    setTimeout(() => {
        removeItem(itemId)
        removingItemId.value = null
    }, 300)
}
</script>

<template>
    <div class="cart-container">
        <!-- Title - Hidden when in profile layout -->
        <h1 v-if="!route.path.startsWith('/profile')" class="text-3xl font-medium text-[#9F7A5F] text-center mb-10">
            Giỏ hàng ({{ itemCount || 0 }})
        </h1>

        <!-- Loading -->
        <div v-if="isLoading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#9F7A5F]"></div>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="isEmpty" class="text-center py-20">
            <div class="text-6xl mb-6 text-[#D9D9D9]">🛒</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-8">{{ t('cart.empty') }}</h2>
            <RouterLink to="/products"
                class="inline-block px-12 py-3 bg-[#9F7A5F] text-white rounded-lg hover:bg-[#8A6A52] transition-colors">
                {{ t('common.shopNow') }}
            </RouterLink>
        </div>

        <!-- Cart Items -->
        <div v-else class="max-w-[1000px] mx-auto pb-10" :class="{ 'px-0': route.path.startsWith('/profile') }">
            <div class="border border-[#D9D9D9] rounded-sm overflow-hidden mb-10 bg-white"
                :class="{ 'border-none shadow-none': route.path.startsWith('/profile') }">
                <div v-for="(item, index) in cart?.items" :key="item.id"
                    class="flex flex-col md:flex-row items-stretch gap-6 p-6 transition-all duration-300" :class="[
                        index !== (cart?.items.length - 1) ? 'border-b border-[#D9D9D9]' : '',
                        removingItemId === item.id ? 'opacity-0 translate-x-10 scale-95' : ''
                    ]">

                    <!-- Product Image -->
                    <div
                        class="w-full md:w-[220px] aspect-[4/3] bg-[#FEFBF2] rounded-sm flex items-center justify-center p-4">
                        <img :src="item.product.thumbnail || ''" :alt="item.product.name"
                            class="max-w-full max-h-full object-contain" />
                    </div>

                    <!-- Product Info -->
                    <div class="flex-1 flex flex-col md:flex-row justify-between w-full gap-6">
                        <div class="flex flex-col justify-between">
                            <div class="space-y-4">
                                <h3 class="text-2xl font-medium text-[#9F7A5F]">{{ item.product.name }}</h3>

                                <!-- Quantity -->
                                <div class="flex items-center gap-4">
                                    <span class="text-black font-medium">Số lượng</span>
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 border border-[#D9D9D9] flex items-center justify-center bg-white text-lg font-bold">
                                            {{ item.qty }}
                                        </div>
                                        <button @click="updateQuantity(item.id, item.qty + 1)"
                                            class="w-10 h-10 border-y border-r border-[#D9D9D9] flex items-center justify-center hover:bg-gray-50 transition-colors"
                                            :disabled="isItemUpdating(item.id)">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M12 5v14M5 12h14" />
                                            </svg>
                                        </button>
                                        <button @click="updateQuantity(item.id, item.qty - 1)"
                                            class="w-10 h-10 border-y border border-[#D9D9D9] flex items-center justify-center hover:bg-gray-50 transition-colors ml-2"
                                            :disabled="item.qty <= 1 || isItemUpdating(item.id)">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M5 12h14" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Color -->
                                <div class="flex items-center gap-2">
                                    <span class="text-black font-medium">Màu sắc :</span>
                                    <span class="text-black">{{ (item as any).variant?.color || 'Mặc định' }}</span>
                                </div>
                            </div>

                            <!-- Delete link -->
                            <button @click="handleRemoveItem(item.id)"
                                class="text-black font-medium hover:text-red-600 transition-colors inline-block w-fit mt-4">
                                Xóa
                            </button>
                        </div>

                        <!-- Price Section -->
                        <div class="md:text-right flex flex-col justify-between items-end">
                            <div class="space-y-1">
                                <div class="text-3xl font-bold text-[#E54335]">
                                    {{ formatPrice(item.price) }}
                                </div>
                                <div v-if="item.product.price > item.price" class="text-xl text-[#D9D9D9] line-through">
                                    {{ formatPrice(item.product.price) }}
                                </div>
                            </div>

                            <RouterLink :to="`/products/${item.product.id}`"
                                class="text-[#D9D9D9] hover:text-[#9F7A5F] transition-colors font-medium text-lg mt-4 block">
                                Xem chi tiết
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Box -->
            <div class="flex flex-col md:flex-row items-center justify-end gap-10">
                <div class="text-2xl font-bold text-black">
                    Tổng tiền : <span class="text-[#E54335] ml-2 font-bold">{{ formatPrice(total) }}</span>
                </div>
                <RouterLink to="/checkout"
                    class="px-16 py-3 bg-[#9F7A5F] text-white rounded-lg text-2xl font-medium hover:bg-[#8A6A52] transition-colors shadow-sm">
                    Đặt hàng
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cart-container {
    padding-top: 1rem;
}

@media (max-width: 768px) {
    .cart-container {
        padding-top: 0;
    }
}
</style>
