<script setup lang="ts">
import { ref } from 'vue'

const paymentMethods = ref([
    {
        id: 'cod',
        icon: '💵',
        title: 'Thanh toán khi nhận hàng',
        description: 'Thanh toán bằng tiền mặt khi nhận hàng',
        active: true
    },
    {
        id: 'bank',
        icon: '🏦',
        title: 'Thanh toán qua thẻ tín dụng',
        description: 'Ngân hàng MBBank',
        active: false
    },
    {
        id: 'installment',
        icon: '📅',
        title: 'Trả góp',
        description: 'Tính năng sắp bổ sung',
        active: false,
        disabled: true
    },
    {
        id: 'ewallet',
        icon: '📱',
        title: 'Ví điện tử',
        description: 'MoMo',
        active: false
    }
])

const selectedMethod = ref('cod')
const message = ref('')
const isUpdating = ref(false)

const selectMethod = (id: string) => {
    const method = paymentMethods.value.find(m => m.id === id)
    if (method && !method.disabled) {
        selectedMethod.value = id
    }
}

const updatePaymentMethod = async () => {
    isUpdating.value = true
    message.value = ''
    // Simulated API call
    setTimeout(() => {
        message.value = 'Cập nhật phương thức thanh toán thành công!'
        isUpdating.value = false
    }, 500)
}
</script>

<template>
    <div class="payment-methods">
        <h2 class="section-title">Phương thức thanh toán</h2>

        <div class="methods-grid">
            <div v-for="method in paymentMethods" :key="method.id" class="method-card"
                :class="{ active: selectedMethod === method.id, disabled: method.disabled }"
                @click="selectMethod(method.id)">
                <div class="method-icon">{{ method.icon }}</div>
                <div class="method-info">
                    <h4>{{ method.title }}</h4>
                    <p>{{ method.description }}</p>
                </div>
                <div class="method-check" v-if="selectedMethod === method.id">✓</div>
            </div>
        </div>

        <div v-if="message" class="message success">
            {{ message }}
        </div>

        <button @click="updatePaymentMethod" class="btn btn-primary" :disabled="isUpdating">
            {{ isUpdating ? 'Đang cập nhật...' : 'Cập nhật' }}
        </button>
    </div>
</template>

<style scoped>
.section-title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--color-primary);
    margin-bottom: var(--space-6);
    text-align: center;
}

.methods-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
    margin-bottom: var(--space-6);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.method-card {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    background: var(--color-bg-tertiary);
    border: 2px solid transparent;
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}

.method-card:hover:not(.disabled) {
    border-color: var(--color-primary);
}

.method-card.active {
    border-color: var(--color-primary);
    background: rgba(var(--color-primary-rgb), 0.1);
}

.method-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.method-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.method-info h4 {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: var(--space-1);
}

.method-info p {
    font-size: var(--text-xs);
    color: var(--color-text-secondary);
}

.method-check {
    position: absolute;
    top: var(--space-2);
    right: var(--space-2);
    width: 20px;
    height: 20px;
    background: var(--color-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.message {
    padding: var(--space-3);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-4);
    text-align: center;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.message.success {
    background: rgba(34, 197, 94, 0.1);
    color: var(--color-success);
}

.btn {
    display: block;
    margin: 0 auto;
    min-width: 150px;
}

@media (max-width: 640px) {
    .methods-grid {
        grid-template-columns: 1fr;
    }
}
</style>
