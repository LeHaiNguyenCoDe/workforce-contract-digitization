<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { cartService, orderService } from '@/plugins/api'
import { useAuthStore } from '@/stores'
import type { Cart } from '@/plugins/api'

const router = useRouter()
const authStore = useAuthStore()

const cart = ref<Cart | null>(null)
const isLoading = ref(true)
const isSubmitting = ref(false)

const form = ref({
    full_name: '',
    phone: '',
    email: '',
    address_line: '',
    province: '',
    district: '',
    ward: '',
    payment_method: 'cod',
    note: ''
})

const errors = ref<Record<string, string>>({})

const paymentMethods = [
    { id: 'cod', icon: '💵', name: 'Thanh toán khi nhận hàng', desc: 'Trả tiền mặt khi nhận hàng' },
    { id: 'bank_transfer', icon: '🏦', name: 'Chuyển khoản ngân hàng', desc: 'Chuyển khoản qua tài khoản ngân hàng' },
    { id: 'e_wallet', icon: '📱', name: 'Ví điện tử', desc: 'MoMo, ZaloPay, VNPay...' }
]

const formatPrice = (price: number | undefined | null) => {
    if (price === undefined || price === null || isNaN(price)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const fetchCart = async () => {
    isLoading.value = true
    try {
        cart.value = await cartService.getCart()
        if (!cart.value?.items?.length) {
            router.push('/cart')
        }
        if (authStore.user) {
            form.value.full_name = authStore.user.name || ''
            form.value.email = authStore.user.email || ''
        }
    } catch (error) {
        console.error('Failed to fetch cart:', error)
        router.push('/cart')
    } finally {
        isLoading.value = false
    }
}

const validateForm = () => {
    errors.value = {}
    if (!form.value.full_name.trim()) errors.value.full_name = 'Vui lòng nhập họ tên'
    if (!form.value.phone.trim()) {
        errors.value.phone = 'Vui lòng nhập số điện thoại'
    } else if (!/^[0-9]{10,11}$/.test(form.value.phone)) {
        errors.value.phone = 'Số điện thoại không hợp lệ'
    }
    if (!form.value.address_line.trim()) errors.value.address_line = 'Vui lòng nhập địa chỉ'
    return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
    if (!validateForm() || isSubmitting.value) return
    isSubmitting.value = true
    try {
        const order = await orderService.create(form.value as any)
        authStore.setCartCount(0)
        router.push(`/orders/${order.id}/success`)
    } catch (error: any) {
        console.error('Failed to create order:', error)
        errors.value.form = error.response?.data?.message || 'Không thể tạo đơn hàng. Vui lòng thử lại.'
    } finally {
        isSubmitting.value = false
    }
}

onMounted(fetchCart)
</script>

<template>
    <div class="checkout-page">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <RouterLink to="/cart" class="back-link">← Quay lại giỏ hàng</RouterLink>
                <h1>🛍️ Thanh toán đơn hàng</h1>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="loading-state">
                <div class="spinner"></div>
                <p>Đang tải thông tin...</p>
            </div>

            <!-- Checkout Form -->
            <form v-else @submit.prevent="handleSubmit" class="checkout-grid">
                <!-- Left Column: Form -->
                <div class="checkout-form">
                    <!-- Error Banner -->
                    <div v-if="errors.form" class="error-banner">
                        <span>⚠️</span>
                        {{ errors.form }}
                    </div>

                    <!-- Customer Info -->
                    <section class="form-section">
                        <h2>👤 Thông tin người nhận</h2>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Họ và tên *</label>
                                <input v-model="form.full_name" type="text" :class="{ error: errors.full_name }"
                                    placeholder="Nguyễn Văn A" />
                                <span v-if="errors.full_name" class="error-text">{{ errors.full_name }}</span>
                            </div>

                            <div class="form-group">
                                <label>Số điện thoại *</label>
                                <input v-model="form.phone" type="tel" :class="{ error: errors.phone }"
                                    placeholder="0901234567" />
                                <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
                            </div>

                            <div class="form-group full-width">
                                <label>Email</label>
                                <input v-model="form.email" type="email" placeholder="email@example.com" />
                            </div>
                        </div>
                    </section>

                    <!-- Shipping Address -->
                    <section class="form-section">
                        <h2>📍 Địa chỉ giao hàng</h2>

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>Địa chỉ chi tiết *</label>
                                <textarea v-model="form.address_line" rows="2" :class="{ error: errors.address_line }"
                                    placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"></textarea>
                                <span v-if="errors.address_line" class="error-text">{{ errors.address_line }}</span>
                            </div>
                        </div>
                    </section>

                    <!-- Payment Method -->
                    <section class="form-section">
                        <h2>💳 Phương thức thanh toán</h2>

                        <div class="payment-grid">
                            <label v-for="method in paymentMethods" :key="method.id" class="payment-option"
                                :class="{ active: form.payment_method === method.id }">
                                <input type="radio" v-model="form.payment_method" :value="method.id" />
                                <div class="payment-icon">{{ method.icon }}</div>
                                <div class="payment-info">
                                    <span class="payment-name">{{ method.name }}</span>
                                    <span class="payment-desc">{{ method.desc }}</span>
                                </div>
                                <div v-if="form.payment_method === method.id" class="payment-check">✓</div>
                            </label>
                        </div>
                    </section>

                    <!-- Notes -->
                    <section class="form-section">
                        <h2>📝 Ghi chú</h2>
                        <textarea v-model="form.note" rows="2"
                            placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao..."></textarea>
                    </section>
                </div>

                <!-- Right Column: Summary -->
                <div class="checkout-summary">
                    <div class="summary-card">
                        <h3>🧾 Đơn hàng của bạn</h3>

                        <div class="summary-items">
                            <div v-for="item in cart!.items" :key="item.id" class="summary-item">
                                <div class="item-info">
                                    <span class="item-name">{{ item.product.name }}</span>
                                    <span class="item-qty">x{{ item.qty }}</span>
                                </div>
                                <span class="item-price">{{ formatPrice(item.subtotal) }}</span>
                            </div>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-row">
                            <span>Tạm tính</span>
                            <span>{{ formatPrice(cart!.total) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển</span>
                            <span class="free-ship">🎉 Miễn phí</span>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-total">
                            <span>Tổng cộng</span>
                            <span class="total-price">{{ formatPrice(cart!.total) }}</span>
                        </div>

                        <button type="submit" class="submit-btn" :disabled="isSubmitting">
                            <span v-if="isSubmitting" class="spinner-sm"></span>
                            {{ isSubmitting ? 'Đang xử lý...' : '🎯 Đặt hàng ngay' }}
                        </button>

                        <p class="policy-note">
                            Bằng việc đặt hàng, bạn đồng ý với
                            <a href="#">Điều khoản dịch vụ</a> và
                            <a href="#">Chính sách bảo mật</a> của chúng tôi.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.checkout-page {
    min-height: 100vh;
    padding: var(--space-6) 0 var(--space-12);
    background: linear-gradient(180deg, var(--color-bg-primary) 0%, var(--color-bg-secondary) 100%);
}

.page-header {
    margin-bottom: var(--space-8);
}

.back-link {
    display: inline-block;
    color: var(--color-text-secondary);
    font-size: var(--text-sm);
    margin-bottom: var(--space-2);
    transition: color 0.2s;
}

.back-link:hover {
    color: var(--color-primary);
}

.page-header h1 {
    font-size: var(--text-2xl);
    font-weight: 700;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-16);
    color: var(--color-text-secondary);
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--color-bg-tertiary);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: var(--space-8);
    align-items: start;
}

.checkout-form {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.error-banner {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-4);
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: var(--radius-lg);
    color: var(--color-error);
}

.form-section {
    background: var(--color-bg-secondary);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.form-section h2 {
    font-size: var(--text-lg);
    font-weight: 600;
    margin-bottom: var(--space-5);
    color: var(--color-text-primary);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--color-text-secondary);
}

.form-group input,
.form-group textarea {
    padding: var(--space-3) var(--space-4);
    background: var(--color-bg-tertiary);
    border: 2px solid transparent;
    border-radius: var(--radius-lg);
    color: var(--color-text-primary);
    font-size: var(--text-sm);
    transition: all 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    background: var(--color-bg-primary);
}

.form-group input.error,
.form-group textarea.error {
    border-color: var(--color-error);
}

.error-text {
    font-size: var(--text-xs);
    color: var(--color-error);
}

.payment-grid {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.payment-option {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--color-bg-tertiary);
    border: 2px solid transparent;
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all 0.2s;
}

.payment-option:hover {
    border-color: rgba(var(--color-primary-rgb), 0.3);
}

.payment-option.active {
    border-color: var(--color-primary);
    background: rgba(var(--color-primary-rgb), 0.1);
}

.payment-option input {
    display: none;
}

.payment-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.payment-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.payment-name {
    font-weight: 600;
    color: var(--color-text-primary);
    font-size: var(--text-sm);
}

.payment-desc {
    font-size: var(--text-xs);
    color: var(--color-text-secondary);
}

.payment-check {
    width: 24px;
    height: 24px;
    background: var(--color-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.summary-card {
    background: var(--color-bg-secondary);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    border: 1px solid rgba(255, 255, 255, 0.05);
    position: sticky;
    top: var(--space-6);
}

.summary-card h3 {
    font-size: var(--text-lg);
    font-weight: 600;
    margin-bottom: var(--space-5);
    padding-bottom: var(--space-4);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.summary-items {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    max-height: 200px;
    overflow-y: auto;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--space-4);
}

.item-info {
    flex: 1;
    display: flex;
    gap: var(--space-2);
}

.item-name {
    font-size: var(--text-sm);
    color: var(--color-text-primary);
    line-height: 1.4;
}

.item-qty {
    font-size: var(--text-xs);
    color: var(--color-text-secondary);
    flex-shrink: 0;
}

.item-price {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--color-text-primary);
    flex-shrink: 0;
}

.summary-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: var(--space-4) 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-2);
}

.free-ship {
    color: var(--color-success);
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: var(--text-base);
    color: var(--color-text-primary);
}

.total-price {
    font-size: var(--text-xl);
    font-weight: 700;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.submit-btn {
    width: 100%;
    padding: var(--space-4);
    margin-top: var(--space-6);
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    border: none;
    border-radius: var(--radius-lg);
    color: white;
    font-size: var(--text-base);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(var(--color-primary-rgb), 0.4);
}

.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner-sm {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.policy-note {
    margin-top: var(--space-4);
    font-size: var(--text-xs);
    color: var(--color-text-secondary);
    text-align: center;
    line-height: 1.5;
}

.policy-note a {
    color: var(--color-primary);
}

@media (max-width: 900px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }

    .summary-card {
        position: static;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
