<script setup lang="ts">
/**
 * Checkout View - Multi-step Wizard
 * Features: 3-step checkout, progress indicator, animations, form persistence
 */
import { computed, watch } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useCart } from '@/modules/landing/cart/composables/useCart'
import { useCheckout } from '../composables/useCheckout'

useI18n()
const router = useRouter()

// Use composables
const { cart, isLoading: isCartLoading, total, isEmpty, formatPrice, itemCount } = useCart()
const { 
    currentStep,
    isFirstStep,
    isLastStep,
    stepTitles,
    form,
    errors,
    savedAddresses,
    selectedAddressId,
    paymentMethods,
    isSubmitting,
    submitError,
    nextStep,
    prevStep,
    goToStep,
    selectAddress,
    createOrder
} = useCheckout()

// Computed
const selectedPaymentMethod = computed(() => 
    paymentMethods.find(m => m.id === form.value.payment_method)
)

// Handle form submission
async function handleSubmit() {
    if (isSubmitting.value) return
    try {
        await createOrder()
    } catch (err) {
        // Error handled in composable
    }
}

// Redirect if cart empty
watch([isCartLoading, isEmpty], ([loading, empty]) => {
    if (!loading && empty) {
        router.push('/cart')
    }
}, { immediate: true })
</script>

<template>
    <div class="min-h-screen bg-dark-900">
        <div class="container py-8">
            <!-- Header -->
            <div class="mb-8">
                <RouterLink to="/cart" class="text-sm text-slate-400 hover:text-primary-light transition-colors mb-4 inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                    </svg>
                    Quay lại giỏ hàng
                </RouterLink>
                <h1 class="text-2xl md:text-3xl font-bold text-white">🛒 Thanh toán</h1>
            </div>

            <!-- Loading -->
            <div v-if="isCartLoading" class="flex flex-col items-center justify-center py-20 gap-4">
                <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
                <p class="text-slate-400">Đang tải thông tin...</p>
            </div>

            <template v-else>
                <!-- Progress Steps -->
                <div class="mb-10">
                    <div class="flex items-center justify-between max-w-2xl mx-auto">
                        <div v-for="(s, idx) in stepTitles" :key="s.step" class="contents">
                            <button 
                                @click="goToStep(s.step)"
                                class="flex flex-col items-center gap-2 group"
                                :class="{ 'cursor-pointer': s.step <= currentStep, 'cursor-not-allowed': s.step > currentStep }">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold transition-all duration-300"
                                    :class="[
                                        currentStep === s.step ? 'bg-gradient-primary text-white shadow-lg shadow-primary/30 scale-110' :
                                        currentStep > s.step ? 'bg-success text-white' : 'bg-dark-700 text-slate-500'
                                    ]">
                                    <span v-if="currentStep > s.step">✓</span>
                                    <span v-else>{{ s.icon }}</span>
                                </div>
                                <span class="text-xs font-medium hidden md:block transition-colors"
                                    :class="[currentStep >= s.step ? 'text-white' : 'text-slate-500']">
                                    {{ s.title }}
                                </span>
                            </button>
                            
                            <!-- Connector Line -->
                            <div v-if="idx < stepTitles.length - 1" 
                                class="flex-1 h-1 mx-2 rounded-full bg-dark-700 overflow-hidden">
                                <div class="h-full bg-gradient-primary transition-all duration-500"
                                    :style="{ width: currentStep > s.step ? '100%' : '0%' }">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Left Column: Step Content -->
                    <div class="lg:col-span-2">
                        <!-- Error Banner -->
                        <div v-if="submitError" class="mb-6 p-4 bg-error/10 border border-error/20 rounded-xl text-error flex items-start gap-3">
                            <span class="text-xl">⚠️</span>
                            <div>
                                <p class="font-medium">Đã xảy ra lỗi</p>
                                <p class="text-sm opacity-80">{{ submitError }}</p>
                            </div>
                        </div>

                        <!-- Step 1: Shipping Address -->
                        <Transition name="step" mode="out-in">
                            <div v-if="currentStep === 1" key="step1" class="space-y-6">
                                <!-- Saved Addresses -->
                                <section v-if="savedAddresses.length > 0" class="card">
                                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                        📍 Địa chỉ đã lưu
                                    </h2>
                                    <div class="grid gap-3">
                                        <label v-for="addr in savedAddresses" :key="addr.id"
                                            class="flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all"
                                            :class="[selectedAddressId === addr.id ? 'bg-primary/5 border-primary' : 'bg-dark-700 border-white/5 hover:border-primary/30']">
                                            <input type="radio" :value="addr.id" v-model="selectedAddressId" 
                                                @change="selectAddress(addr)" class="hidden" />
                                            <div class="w-5 h-5 rounded-full border-2 flex-shrink-0 flex items-center justify-center mt-0.5"
                                                :class="[selectedAddressId === addr.id ? 'border-primary bg-primary' : 'border-slate-500']">
                                                <span v-if="selectedAddressId === addr.id" class="text-white text-xs">✓</span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-semibold text-white">{{ addr.full_name }}</span>
                                                    <span v-if="addr.is_default" class="text-[10px] px-2 py-0.5 bg-primary/20 text-primary rounded-full">Mặc định</span>
                                                </div>
                                                <p class="text-sm text-slate-400">{{ addr.phone }}</p>
                                                <p class="text-sm text-slate-400 line-clamp-2">{{ addr.address_line }}</p>
                                            </div>
                                        </label>
                                    </div>
                                </section>

                                <!-- Manual Address Form -->
                                <section class="card">
                                    <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                        <span class="text-xl">👤</span> Thông tin người nhận
                                    </h2>
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div class="form-group">
                                            <label class="form-label">Họ và tên <span class="text-error">*</span></label>
                                            <input v-model="form.full_name" type="text" class="form-input" 
                                                :class="{ 'border-error': errors.full_name }"
                                                placeholder="Nguyễn Văn A" />
                                            <span v-if="errors.full_name" class="error-text">{{ errors.full_name }}</span>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Số điện thoại <span class="text-error">*</span></label>
                                            <input v-model="form.phone" type="tel" class="form-input"
                                                :class="{ 'border-error': errors.phone }"
                                                placeholder="0901234567" />
                                            <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
                                        </div>
                                        <div class="form-group md:col-span-2">
                                            <label class="form-label">Email</label>
                                            <input v-model="form.email" type="email" class="form-input"
                                                :class="{ 'border-error': errors.email }"
                                                placeholder="email@example.com" />
                                            <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                                        </div>
                                    </div>
                                </section>

                                <section class="card">
                                    <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                        <span class="text-xl">📍</span> Địa chỉ giao hàng
                                    </h2>
                                    <div class="form-group">
                                        <label class="form-label">Địa chỉ chi tiết <span class="text-error">*</span></label>
                                        <textarea v-model="form.address_line" rows="3" class="form-input"
                                            :class="{ 'border-error': errors.address_line }"
                                            placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"></textarea>
                                        <span v-if="errors.address_line" class="error-text">{{ errors.address_line }}</span>
                                    </div>
                                </section>
                            </div>
                        </Transition>

                        <!-- Step 2: Payment Method -->
                        <Transition name="step" mode="out-in">
                            <div v-if="currentStep === 2" key="step2" class="space-y-6">
                                <section class="card">
                                    <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                        <span class="text-xl">💳</span> Phương thức thanh toán
                                    </h2>
                                    <div class="space-y-3">
                                        <label v-for="method in paymentMethods" :key="method.id"
                                            class="flex items-center gap-4 p-5 rounded-xl border-2 cursor-pointer transition-all duration-300"
                                            :class="[
                                                form.payment_method === method.id 
                                                    ? 'bg-primary/5 border-primary shadow-lg shadow-primary/10' 
                                                    : 'bg-dark-700 border-white/5 hover:border-primary/30',
                                                method.disabled ? 'opacity-50 cursor-not-allowed' : ''
                                            ]">
                                            <input type="radio" v-model="form.payment_method" :value="method.id" 
                                                :disabled="method.disabled" class="hidden" />
                                            <span class="text-3xl">{{ method.icon }}</span>
                                            <div class="flex-1">
                                                <span class="block font-bold text-white">{{ method.name }}</span>
                                                <span class="block text-sm text-slate-400">{{ method.desc }}</span>
                                            </div>
                                            <div v-if="form.payment_method === method.id" 
                                                class="w-7 h-7 bg-primary rounded-full flex items-center justify-center text-white">
                                                ✓
                                            </div>
                                        </label>
                                    </div>
                                </section>

                                <section class="card">
                                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                        <span class="text-xl">📝</span> Ghi chú đơn hàng
                                    </h2>
                                    <textarea v-model="form.note" rows="3" class="form-input"
                                        placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao..."></textarea>
                                </section>
                            </div>
                        </Transition>

                        <!-- Step 3: Review & Confirm -->
                        <Transition name="step" mode="out-in">
                            <div v-if="currentStep === 3" key="step3" class="space-y-6">
                                <!-- Shipping Info Review -->
                                <section class="card">
                                    <div class="flex items-center justify-between mb-4">
                                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                            <span class="text-xl">📍</span> Thông tin giao hàng
                                        </h2>
                                        <button @click="goToStep(1)" class="text-sm text-primary-light hover:text-primary">
                                            Chỉnh sửa
                                        </button>
                                    </div>
                                    <div class="bg-dark-700 rounded-xl p-4 space-y-2">
                                        <p class="font-semibold text-white">{{ form.full_name }}</p>
                                        <p class="text-slate-400">{{ form.phone }}</p>
                                        <p v-if="form.email" class="text-slate-400">{{ form.email }}</p>
                                        <p class="text-slate-400">{{ form.address_line }}</p>
                                    </div>
                                </section>

                                <!-- Payment Review -->
                                <section class="card">
                                    <div class="flex items-center justify-between mb-4">
                                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                            <span class="text-xl">💳</span> Phương thức thanh toán
                                        </h2>
                                        <button @click="goToStep(2)" class="text-sm text-primary-light hover:text-primary">
                                            Chỉnh sửa
                                        </button>
                                    </div>
                                    <div v-if="selectedPaymentMethod" class="bg-dark-700 rounded-xl p-4 flex items-center gap-4">
                                        <span class="text-3xl">{{ selectedPaymentMethod.icon }}</span>
                                        <div>
                                            <p class="font-semibold text-white">{{ selectedPaymentMethod.name }}</p>
                                            <p class="text-sm text-slate-400">{{ selectedPaymentMethod.desc }}</p>
                                        </div>
                                    </div>
                                </section>

                                <!-- Order Items Review -->
                                <section class="card">
                                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                        <span class="text-xl">📦</span> Sản phẩm ({{ itemCount }})
                                    </h2>
                                    <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                                        <div v-for="item in cart?.items" :key="item.id" 
                                            class="flex gap-4 p-3 bg-dark-700 rounded-xl">
                                            <div class="w-16 h-16 bg-dark-600 rounded-lg overflow-hidden flex-shrink-0">
                                                <img v-if="item.product.thumbnail" :src="item.product.thumbnail" 
                                                    :alt="item.product.name" class="w-full h-full object-cover" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-white line-clamp-1">{{ item.product.name }}</p>
                                                <p class="text-sm text-slate-400">SL: {{ item.qty }} × {{ formatPrice(item.price) }}</p>
                                            </div>
                                            <p class="font-bold text-white">{{ formatPrice(item.subtotal) }}</p>
                                        </div>
                                    </div>
                                </section>

                                <!-- Note Review -->
                                <section v-if="form.note" class="card">
                                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                                        <span class="text-xl">📝</span> Ghi chú
                                    </h2>
                                    <p class="text-slate-400 bg-dark-700 rounded-xl p-4">{{ form.note }}</p>
                                </section>
                            </div>
                        </Transition>

                        <!-- Navigation Buttons -->
                        <div class="flex items-center justify-between mt-8 pt-6 border-t border-white/5">
                            <button v-if="!isFirstStep" @click="prevStep" 
                                class="btn btn-secondary flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                                </svg>
                                Quay lại
                            </button>
                            <div v-else></div>
                            
                            <button v-if="!isLastStep" @click="nextStep" 
                                class="btn btn-primary flex items-center gap-2">
                                Tiếp tục
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                </svg>
                            </button>
                            <button v-else @click="handleSubmit" :disabled="isSubmitting"
                                class="btn btn-primary btn-lg flex items-center gap-2">
                                <span v-if="isSubmitting" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span>{{ isSubmitting ? 'Đang xử lý...' : '🎯 Đặt hàng ngay' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="card sticky top-8 bg-dark-800 border-white/5 space-y-6">
                            <h3 class="text-xl font-bold text-white flex items-center gap-2 border-b border-white/5 pb-4">
                                🧾 Đơn hàng của bạn
                            </h3>

                            <!-- Order Items Preview -->
                            <div class="max-h-48 overflow-y-auto pr-2 space-y-3 scrollbar-thin">
                                <div v-for="item in cart?.items" :key="item.id" class="flex justify-between gap-3 text-sm">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-slate-300 line-clamp-1">{{ item.product.name }}</span>
                                        <span class="text-slate-500 text-xs">x{{ item.qty }}</span>
                                    </div>
                                    <span class="text-white font-medium">{{ formatPrice(item.subtotal) }}</span>
                                </div>
                            </div>

                            <!-- Price Breakdown -->
                            <div class="space-y-3 pt-4 border-t border-white/5">
                                <div class="flex justify-between text-sm text-slate-400">
                                    <span>Tạm tính ({{ itemCount }} sản phẩm)</span>
                                    <span class="text-white">{{ formatPrice(total) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-slate-400">
                                    <span>Phí vận chuyển</span>
                                    <span class="text-success font-medium">🎉 Miễn phí</span>
                                </div>
                                <div class="flex justify-between items-center pt-4 border-t border-white/5">
                                    <span class="font-bold text-lg text-white">Tổng cộng</span>
                                    <span class="text-2xl font-bold gradient-text">{{ formatPrice(total) }}</span>
                                </div>
                            </div>

                            <!-- Trust Badges -->
                            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-white/5">
                                <div class="flex items-center gap-2 text-slate-400">
                                    <span class="text-lg">🔒</span>
                                    <span class="text-xs">Bảo mật SSL</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-400">
                                    <span class="text-lg">🚚</span>
                                    <span class="text-xs">Giao hàng nhanh</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-400">
                                    <span class="text-lg">↩️</span>
                                    <span class="text-xs">Đổi trả 30 ngày</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-400">
                                    <span class="text-lg">💬</span>
                                    <span class="text-xs">Hỗ trợ 24/7</span>
                                </div>
                            </div>

                            <p class="text-[10px] text-slate-500 text-center leading-relaxed">
                                Bằng việc đặt hàng, bạn đồng ý với Điều khoản dịch vụ và Chính sách bảo mật của chúng tôi.
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<style scoped>
/* Step transition animations */
.step-enter-active,
.step-leave-active {
    transition: all 0.3s ease;
}

.step-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.step-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>
