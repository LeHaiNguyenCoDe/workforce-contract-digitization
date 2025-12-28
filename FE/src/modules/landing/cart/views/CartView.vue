<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { cartService } from '@/plugins/api'
import { useAuthStore } from '@/stores'
import type { Cart } from '@/plugins/api'

const { t } = useI18n()
const authStore = useAuthStore()

const cart = ref<Cart | null>(null)
const isLoading = ref(true)

const fetchCart = async () => {
    isLoading.value = true
    try {
        cart.value = await cartService.getCart()
    } catch (error) {
        console.error('Failed to fetch cart:', error)
    } finally {
        isLoading.value = false
    }
}

const updateQuantity = async (itemId: number, qty: number) => {
    if (qty < 1) return
    try {
        await cartService.updateItem(itemId, qty)
        fetchCart()
    } catch (error) {
        console.error('Failed to update cart:', error)
    }
}

const removeItem = async (itemId: number) => {
    try {
        await cartService.removeItem(itemId)
        authStore.decrementCartCount()
        fetchCart()
    } catch (error) {
        console.error('Failed to remove item:', error)
    }
}

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const total = computed(() => cart.value?.total || 0)

onMounted(fetchCart)
</script>

<template>
    <div class="container py-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-8">{{ t('cart.title') }}</h1>

        <!-- Loading -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="h-24 bg-dark-700 rounded-xl animate-pulse"></div>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="!cart?.items?.length" class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 mx-auto text-slate-600 mb-4" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1">
                <circle cx="8" cy="21" r="1" />
                <circle cx="19" cy="21" r="1" />
                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
            </svg>
            <h2 class="text-xl font-semibold text-slate-400 mb-2">{{ t('cart.empty') }}</h2>
            <p class="text-slate-500 mb-6">Hãy thêm sản phẩm vào giỏ hàng của bạn</p>
            <RouterLink to="/products" class="btn btn-primary">{{ t('common.shopNow') }}</RouterLink>
        </div>

        <!-- Cart Items -->
        <div v-else class="grid lg:grid-cols-3 gap-8">
            <!-- Items List -->
            <div class="lg:col-span-2 space-y-4">
                <div v-for="item in cart.items" :key="item.id"
                    class="flex gap-4 p-4 bg-dark-800 border border-white/10 rounded-xl">
                    <!-- Image -->
                    <div class="w-24 h-24 bg-dark-700 rounded-lg overflow-hidden flex-shrink-0">
                        <img v-if="item.product.images?.[0]" :src="item.product.images[0].url" :alt="item.product.name"
                            class="w-full h-full object-cover" />
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <RouterLink :to="`/products/${item.product.id}`"
                            class="font-semibold text-white hover:text-primary-light line-clamp-1">
                            {{ item.product.name }}
                        </RouterLink>
                        <p class="text-sm text-slate-400 mt-1">{{ formatPrice(item.price) }}</p>

                        <!-- Quantity -->
                        <div class="flex items-center gap-2 mt-3">
                            <button @click="updateQuantity(item.id, item.qty - 1)"
                                class="w-8 h-8 flex items-center justify-center text-slate-400 bg-dark-700 rounded-lg hover:text-white">-</button>
                            <span class="w-10 text-center text-white">{{ item.qty }}</span>
                            <button @click="updateQuantity(item.id, item.qty + 1)"
                                class="w-8 h-8 flex items-center justify-center text-slate-400 bg-dark-700 rounded-lg hover:text-white">+</button>
                        </div>
                    </div>

                    <!-- Price & Remove -->
                    <div class="flex flex-col items-end justify-between">
                        <button @click="removeItem(item.id)" class="text-slate-500 hover:text-error transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18" />
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                            </svg>
                        </button>
                        <span class="font-semibold text-white">{{ formatPrice(item.subtotal) }}</span>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div class="card sticky top-24">
                    <h3 class="text-xl font-bold text-white mb-6 pb-4 border-b border-white/10">{{ t('cart.summary') }}
                    </h3>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-slate-400">
                            <span>{{ t('cart.subtotal') }}</span>
                            <span>{{ formatPrice(total) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>{{ t('cart.shipping') }}</span>
                            <span class="text-success">{{ t('cart.freeShipping') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between text-lg font-bold text-white pt-4 border-t border-white/10 mb-6">
                        <span>{{ t('cart.total') }}</span>
                        <span class="gradient-text">{{ formatPrice(total) }}</span>
                    </div>

                    <RouterLink to="/checkout" class="btn btn-primary w-full">
                        {{ t('cart.checkout') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg>
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>
