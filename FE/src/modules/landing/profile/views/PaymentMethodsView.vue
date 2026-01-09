<script setup lang="ts">
/**
 * Payment Methods View - Redesigned to match minimalist mockup
 */
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const paymentMethods = ref([
    {
        id: 'cod',
        icon: '💵',
        title: t('common.codPayment'),
        description: t('common.codPaymentDesc'),
        active: true
    },
    {
        id: 'bank',
        icon: '🏦',
        title: t('common.bankTransfer'),
        description: t('common.bankTransferDesc'),
        active: false
    },
    {
        id: 'ewallet',
        icon: '📱',
        title: t('common.eWallet'),
        description: 'MoMo, ZaloPay, ShopeePay',
        active: false
    },
    {
        id: 'installment',
        icon: '📅',
        title: t('common.installment'),
        description: t('common.installmentDesc'),
        active: false,
        disabled: true
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
    // Placeholder for future logic
    setTimeout(() => {
        message.value = t('auth.paymentSaved')
        isUpdating.value = false
    }, 800)
}
</script>

<template>
    <div class="payment-methods-container max-w-[800px] mx-auto pb-10">
        <!-- Title -->
        <h1 class="text-3xl font-medium text-[#9F7A5F] text-center mb-10">
            Phương thức thanh toán
        </h1>

        <div class="grid md:grid-cols-2 gap-6 mb-12">
            <div v-for="method in paymentMethods" :key="method.id"
                class="flex items-center gap-5 p-6 rounded-lg border transition-all duration-300 relative group" :class="[
                    selectedMethod === method.id
                        ? 'bg-[#FEFBF2] border-[#9F7A5F] shadow-sm'
                        : 'bg-white border-[#D9D9D9] hover:border-[#9F7A5F]/50',
                    method.disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
                ]" @click="selectMethod(method.id)">

                <div class="text-4xl grayscale group-hover:grayscale-0 transition-all"
                    :class="{ 'grayscale-0': selectedMethod === method.id }">
                    {{ method.icon }}
                </div>

                <div class="flex-1">
                    <h4 class="font-bold text-black text-lg mb-1">{{ method.title }}</h4>
                    <p class="text-sm text-gray-500">{{ method.description }}</p>
                </div>

                <div v-if="selectedMethod === method.id"
                    class="w-7 h-7 bg-[#9F7A5F] rounded-full flex items-center justify-center text-white text-sm">
                    ✓
                </div>

                <div v-if="method.disabled"
                    class="absolute top-2 right-2 px-2 py-0.5 bg-gray-100 rounded text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                    Soon
                </div>
            </div>
        </div>

        <!-- Message -->
        <div v-if="message"
            class="mb-8 p-4 bg-green-50 border border-green-100 rounded-lg text-green-600 text-center text-sm">
            {{ message }}
        </div>

        <div class="flex justify-center">
            <button @click="updatePaymentMethod"
                class="px-16 py-3 bg-[#9F7A5F] text-white rounded-lg text-2xl font-medium hover:bg-[#8A6A52] transition-colors shadow-sm disabled:opacity-70"
                :disabled="isUpdating">
                <span v-if="isUpdating"
                    class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin inline-block mr-2"></span>
                {{ isUpdating ? 'Đang lưu...' : 'Lưu cài đặt' }}
            </button>
        </div>
    </div>
</template>
