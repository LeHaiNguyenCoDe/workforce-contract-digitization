<script setup lang="ts">
/**
 * Checkout View - Redesigned to match minimalist two-column mockup
 */
import { useRouter, RouterLink } from 'vue-router'
import { useCart } from '@/modules/landing/cart/composables/useCart'
import { useCheckout } from '../composables/useCheckout'

// @ts-ignore
const router = useRouter()

// Use composables
const { cart, total, formatPrice, itemCount } = useCart()
const {
    form,
    errors,
    isSubmitting,
    submitError,
    createOrder
} = useCheckout()

// Handle form submission
async function handleSubmit() {
    if (isSubmitting.value) return
    try {
        await createOrder()
    } catch (err) {
        // Error handled in composable/submitError
    }
}
</script>

<template>
    <div class="checkout-container max-w-[1200px] mx-auto px-4">
        <!-- Title -->
        <h1 class="text-3xl font-medium text-[#9F7A5F] text-center mb-10">
            Đặt hàng
        </h1>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column: Order Form -->
            <div class="lg:col-span-2 space-y-8 border border-[#D9D9D9] p-8 rounded-sm bg-white">

                <!-- Recipient Info -->
                <div class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <h2 class="text-xl font-bold text-black">Thông tin người nhận</h2>
                            <input v-model="form.full_name" type="text"
                                class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                                :class="{ 'border-red-500': errors.full_name }" placeholder="Lê Văn Trung" />
                        </div>
                        <div class="space-y-4">
                            <h2 class="text-xl font-bold text-black">SĐT</h2>
                            <input v-model="form.phone" type="tel"
                                class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                                :class="{ 'border-red-500': errors.phone }" placeholder="0947225188" />
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-black">Địa chỉ nhận</h2>
                    <input v-model="form.address_line" type="text"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                        :class="{ 'border-red-500': errors.address_line }"
                        placeholder="Thanh Hóa, Hậu Lộc, Hưng Lộc, Số nhà 21" />
                </div>

                <!-- Payment Method -->
                <div class="space-y-6">
                    <h2 class="text-xl font-bold text-black">Phương thức thanh toán</h2>
                    <div class="flex flex-wrap gap-4">
                        <label v-for="method in [
                            { id: 'cod', label: 'Trực tiếp' },
                            { id: 'credit_card', label: 'Thẻ tín dụng' },
                            { id: 'momo', label: 'Ví MoMo' }
                        ]" :key="method.id"
                            class="flex-1 min-w-[120px] px-6 py-3 border rounded-lg cursor-pointer transition-all text-center font-medium"
                            :class="[
                                form.payment_method === method.id
                                    ? 'bg-white border-[#D9D9D9] text-black shadow-sm'
                                    : 'bg-white border-transparent text-gray-400 hover:border-[#D9D9D9]'
                            ]">
                            <input type="radio" v-model="form.payment_method" :value="method.id" class="hidden" />
                            {{ method.label }}
                        </label>
                    </div>
                </div>

                <!-- Error Display -->
                <div v-if="submitError" class="p-4 bg-red-50 text-red-600 rounded-lg text-sm">
                    {{ submitError }}
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="lg:col-span-1">
                <div class="border border-[#D9D9D9] rounded-sm bg-white overflow-hidden sticky top-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-black">Đơn hàng ({{ itemCount }}sp)</h2>
                            <RouterLink to="/profile/cart" class="text-gray-400 text-sm hover:text-[#9F7A5F]">Sửa >
                            </RouterLink>
                        </div>

                        <div class="space-y-4 mb-8">
                            <div v-for="item in cart?.items" :key="item.id" class="flex justify-between gap-4 text-lg">
                                <span class="text-black flex-1 line-clamp-1">{{ item.qty }} x {{ item.product.name
                                }}</span>
                                <span class="text-black font-medium">{{ formatPrice(item.subtotal || (item.price *
                                    item.qty)) }}</span>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-[#D9D9D9] space-y-8">
                            <div class="flex justify-between items-center text-xl">
                                <span class="text-black">Thành tiền</span>
                                <span class="text-[#E54335] font-bold">{{ formatPrice(total) }}</span>
                            </div>

                            <button @click="handleSubmit" :disabled="isSubmitting"
                                class="w-full py-4 bg-[#9F7A5F] text-white rounded-lg text-2xl font-medium hover:bg-[#8A6A52] transition-colors shadow-sm disabled:opacity-70 disabled:cursor-not-allowed">
                                <span v-if="isSubmitting"
                                    class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin inline-block mr-2"></span>
                                Thanh toán
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.checkout-container {
    padding-bottom: 4rem;
}
</style>
