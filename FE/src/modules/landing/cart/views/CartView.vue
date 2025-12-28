<script setup lang="ts">
/**
 * Cart View - Enhanced UI
 * Features: animations, promo codes, toast notifications, responsive design
 */
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useCart } from '../composables/useCart'

const { t } = useI18n()

// Use composable
const {
    cart,
    isLoading,
    total,
    subtotal,
    discount,
    isEmpty,
    itemCount,
    savings,
    promoCode,
    promoError,
    promoSuccess,
    isApplyingPromo,
    notification,
    updateQuantity,
    removeItem,
    clearCart,
    applyPromoCode,
    removePromoCode,
    isItemUpdating,
    formatPrice
} = useCart()

// Local state
const promoInput = ref('')
const showClearConfirm = ref(false)
const removingItemId = ref<number | null>(null)

// Handle promo code
async function handleApplyPromo() {
    await applyPromoCode(promoInput.value)
}

// Handle remove with animation
function handleRemoveItem(itemId: number) {
    removingItemId.value = itemId
    setTimeout(() => {
        removeItem(itemId)
        removingItemId.value = null
    }, 300)
}

// Handle clear cart
function handleClearCart() {
    clearCart()
    showClearConfirm.value = false
}

// Get item subtotal with fallback calculation
function getItemSubtotal(item: any): number {
    if (item.subtotal !== undefined && item.subtotal !== null && !isNaN(item.subtotal)) {
        return item.subtotal
    }
    // Fallback: calculate from price * qty
    const price = item.price || item.product?.price || item.product?.sale_price || 0
    const qty = item.qty || item.quantity || 1
    return price * qty
}
</script>

<template>
    <div class="container py-8">
        <!-- Toast Notification -->
        <Transition name="toast">
            <div v-if="notification" 
                class="fixed top-20 right-4 z-50 px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 animate-slide-up"
                :class="[notification.type === 'success' ? 'bg-success/90 text-white' : 'bg-error/90 text-white']">
                <span class="text-lg">{{ notification.type === 'success' ? '✓' : '✕' }}</span>
                <span class="font-medium">{{ notification.message }}</span>
            </div>
        </Transition>

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                    🛒 {{ t('cart.title') }}
                </h1>
                <p v-if="!isEmpty" class="text-slate-400">
                    {{ itemCount }} sản phẩm trong giỏ hàng
                </p>
            </div>
            <button v-if="!isEmpty && !isLoading" 
                @click="showClearConfirm = true"
                class="text-sm text-slate-400 hover:text-error transition-colors flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                </svg>
                Xóa tất cả
            </button>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="flex gap-4 p-4 bg-dark-800 rounded-2xl animate-pulse">
                <div class="w-24 h-24 bg-dark-700 rounded-xl"></div>
                <div class="flex-1 space-y-3">
                    <div class="h-5 bg-dark-700 rounded w-3/4"></div>
                    <div class="h-4 bg-dark-700 rounded w-1/4"></div>
                    <div class="h-8 bg-dark-700 rounded w-32"></div>
                </div>
            </div>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="isEmpty" class="text-center py-20 bg-dark-800 rounded-3xl border border-white/5">
            <div class="relative inline-block mb-6">
                <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full"></div>
                <svg xmlns="http://www.w3.org/2000/svg" class="relative w-24 h-24 text-slate-600" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="8" cy="21" r="1" />
                    <circle cx="19" cy="21" r="1" />
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-3">{{ t('cart.empty') }}</h2>
            <p class="text-slate-400 mb-8 max-w-md mx-auto">
                Giỏ hàng của bạn đang trống. Hãy khám phá các sản phẩm tuyệt vời của chúng tôi!
            </p>
            <RouterLink to="/products" class="btn btn-primary btn-lg inline-flex items-center gap-2">
                {{ t('common.shopNow') }}
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
            </RouterLink>
        </div>

        <!-- Cart Content -->
        <div v-else class="grid lg:grid-cols-3 gap-8">
            <!-- Items List -->
            <div class="lg:col-span-2 space-y-4">
                <TransitionGroup name="cart-item">
                    <div v-for="item in cart?.items" :key="item.id"
                        class="group relative flex gap-4 p-4 bg-dark-800 border border-white/5 rounded-2xl hover:border-primary/30 transition-all duration-300"
                        :class="{ 
                            'opacity-50 scale-95': removingItemId === item.id,
                            'animate-pulse': isItemUpdating(item.id)
                        }">
                        
                        <!-- Product Image -->
                        <RouterLink :to="`/products/${item.product.id}`" class="block w-28 h-28 bg-dark-700 rounded-xl overflow-hidden flex-shrink-0 group/img">
                            <img v-if="item.product.thumbnail" 
                                :src="item.product.thumbnail" 
                                :alt="item.product.name"
                                class="w-full h-full object-cover group-hover/img:scale-110 transition-transform duration-500" />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                                    <circle cx="9" cy="9" r="2"/>
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                </svg>
                            </div>
                        </RouterLink>

                        <!-- Product Info -->
                        <div class="flex-1 min-w-0 flex flex-col">
                            <RouterLink :to="`/products/${item.product.id}`"
                                class="font-semibold text-white hover:text-primary-light transition-colors line-clamp-2 mb-1">
                                {{ item.product.name }}
                            </RouterLink>
                            
                            <!-- Unit Price -->
                            <div class="flex items-center gap-2 text-sm mb-auto">
                                <span class="text-primary-light font-medium">{{ formatPrice(item.price) }}</span>
                                <span v-if="item.product.price > item.price" class="text-slate-500 line-through text-xs">
                                    {{ formatPrice(item.product.price) }}
                                </span>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center bg-dark-700 rounded-xl overflow-hidden border border-white/5">
                                    <button @click="updateQuantity(item.id, item.qty - 1)"
                                        :disabled="item.qty <= 1 || isItemUpdating(item.id)"
                                        class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-dark-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14"/>
                                        </svg>
                                    </button>
                                    <span class="w-12 text-center text-sm font-bold text-white">{{ item.qty }}</span>
                                    <button @click="updateQuantity(item.id, item.qty + 1)"
                                        :disabled="isItemUpdating(item.id)"
                                        class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-dark-600 transition-all disabled:opacity-30">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14"/><path d="M12 5v14"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Subtotal -->
                                <span class="text-lg font-bold gradient-text">{{ formatPrice(getItemSubtotal(item)) }}</span>
                            </div>
                        </div>

                        <!-- Remove Button -->
                        <button @click="handleRemoveItem(item.id)" 
                            class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center text-slate-500 hover:text-error hover:bg-error/10 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                            </svg>
                        </button>
                    </div>
                </TransitionGroup>

                <!-- Continue Shopping -->
                <div class="pt-4">
                    <RouterLink to="/products" class="text-sm text-primary-light hover:text-primary transition-colors inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                        </svg>
                        Tiếp tục mua sắm
                    </RouterLink>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="card sticky top-24 bg-dark-800 border-white/5 space-y-6">
                    <h3 class="text-xl font-bold text-white pb-4 border-b border-white/5">
                        {{ t('cart.summary') }}
                    </h3>

                    <!-- Promo Code Input -->
                    <div class="space-y-3">
                        <label class="text-sm font-medium text-slate-400">Mã khuyến mãi</label>
                        <div class="flex gap-2">
                            <input v-model="promoInput" 
                                type="text" 
                                placeholder="Nhập mã..."
                                class="form-input flex-1 py-2.5 text-sm"
                                :disabled="isApplyingPromo || !!promoCode"
                                @keyup.enter="handleApplyPromo" />
                            <button v-if="!promoCode"
                                @click="handleApplyPromo" 
                                :disabled="isApplyingPromo || !promoInput.trim()"
                                class="btn btn-secondary px-4 py-2.5 text-sm">
                                <span v-if="isApplyingPromo" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span v-else>Áp dụng</span>
                            </button>
                            <button v-else
                                @click="removePromoCode"
                                class="btn bg-error/20 text-error hover:bg-error/30 px-4 py-2.5 text-sm">
                                Xóa
                            </button>
                        </div>
                        <p v-if="promoError" class="text-xs text-error flex items-center gap-1">
                            <span>⚠️</span> {{ promoError }}
                        </p>
                        <p v-if="promoSuccess" class="text-xs text-success flex items-center gap-1">
                            <span>✓</span> {{ promoSuccess }}
                        </p>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="space-y-3 pt-2">
                        <div class="flex justify-between text-slate-400">
                            <span>{{ t('cart.subtotal') }} ({{ itemCount }} sản phẩm)</span>
                            <span class="text-white">{{ formatPrice(subtotal) }}</span>
                        </div>
                        
                        <div v-if="savings > 0" class="flex justify-between text-slate-400">
                            <span>Tiết kiệm</span>
                            <span class="text-success font-medium">-{{ formatPrice(savings) }}</span>
                        </div>
                        
                        <div v-if="discount > 0" class="flex justify-between text-slate-400">
                            <span>Giảm giá ({{ promoCode }})</span>
                            <span class="text-success font-medium">-{{ formatPrice(discount) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-slate-400">
                            <span>{{ t('cart.shipping') }}</span>
                            <span class="text-success font-medium">{{ t('cart.freeShipping') }}</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center text-lg font-bold text-white pt-4 border-t border-white/5">
                        <span>{{ t('cart.total') }}</span>
                        <span class="text-2xl gradient-text">{{ formatPrice(total) }}</span>
                    </div>

                    <!-- Checkout Button -->
                    <RouterLink 
                        to="/checkout" 
                        class="btn btn-primary w-full flex items-center justify-center gap-2 py-4 text-lg">
                        {{ t('cart.checkout') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14" /><path d="m12 5 7 7-7 7" />
                        </svg>
                    </RouterLink>

                    <!-- Trust Badges -->
                    <div class="grid grid-cols-3 gap-3 pt-4 border-t border-white/5">
                        <div class="text-center">
                            <div class="text-lg mb-1">🔒</div>
                            <p class="text-[10px] text-slate-500">Thanh toán an toàn</p>
                        </div>
                        <div class="text-center">
                            <div class="text-lg mb-1">🚚</div>
                            <p class="text-[10px] text-slate-500">Giao hàng nhanh</p>
                        </div>
                        <div class="text-center">
                            <div class="text-lg mb-1">↩️</div>
                            <p class="text-[10px] text-slate-500">Đổi trả 30 ngày</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clear Cart Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showClearConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showClearConfirm = false"></div>
                    <div class="relative bg-dark-800 rounded-2xl p-6 max-w-sm w-full border border-white/10 shadow-2xl animate-slide-up">
                        <h3 class="text-xl font-bold text-white mb-3">Xóa giỏ hàng?</h3>
                        <p class="text-slate-400 mb-6">Bạn có chắc muốn xóa tất cả sản phẩm trong giỏ hàng? Thao tác này không thể hoàn tác.</p>
                        <div class="flex gap-3">
                            <button @click="showClearConfirm = false" class="btn btn-secondary flex-1">Hủy</button>
                            <button @click="handleClearCart" class="btn bg-error hover:bg-error/80 text-white flex-1">Xóa tất cả</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
/* Cart item animations */
.cart-item-enter-active,
.cart-item-leave-active {
    transition: all 0.3s ease;
}

.cart-item-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.cart-item-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

/* Toast animation */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

/* Modal animation */
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.9);
}
</style>
