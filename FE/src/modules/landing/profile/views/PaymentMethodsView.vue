<script setup lang="ts">
/**
 * Payment Methods View
 * Currently a placeholder for future payment method management
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
    <div class="payment-methods">
        <h2 class="text-xl font-bold text-white mb-8 border-b border-white/5 pb-4">{{ t('common.paymentMethods') }}</h2>

        <div class="grid md:grid-cols-2 gap-4 mb-8 max-w-2xl mx-auto">
            <div v-for="method in paymentMethods" :key="method.id"
                class="flex items-center gap-4 p-5 rounded-2xl border-2 transition-all duration-300 relative group"
                :class="[
                    selectedMethod === method.id ? 'bg-primary/5 border-primary shadow-lg shadow-primary/10' : 'bg-dark-700/50 border-white/5 hover:border-white/10',
                    method.disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
                ]" @click="selectMethod(method.id)">

                <div class="text-3xl grayscale group-hover:grayscale-0 transition-all"
                    :class="{ 'grayscale-0': selectedMethod === method.id }">
                    {{ method.icon }}
                </div>

                <div class="flex-1">
                    <h4 class="font-bold text-white text-sm mb-1">{{ method.title }}</h4>
                    <p class="text-xs text-slate-400">{{ method.description }}</p>
                </div>

                <div v-if="selectedMethod === method.id"
                    class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-white text-xs">
                    ✓
                </div>

                <div v-if="method.disabled"
                    class="absolute top-2 right-2 px-2 py-0.5 bg-dark-600 rounded text-[8px] font-bold text-slate-400 uppercase">
                    Soon
                </div>
            </div>
        </div>

        <div v-if="message"
            class="max-w-2xl mx-auto mb-6 p-4 bg-success/10 border border-success/20 rounded-xl text-success text-center text-sm">
            {{ message }}
        </div>

        <div class="flex justify-center">
            <button @click="updatePaymentMethod" class="btn btn-primary px-12 py-3 font-bold" :disabled="isUpdating">
                <span v-if="isUpdating"
                    class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                {{ isUpdating ? t('common.saving') : t('common.saveSettings') }}
            </button>
        </div>
    </div>
</template>
