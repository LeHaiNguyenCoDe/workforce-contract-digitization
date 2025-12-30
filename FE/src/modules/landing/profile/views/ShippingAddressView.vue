<script setup lang="ts">
/**
 * Shipping Address View
 * Uses useProfile composable for address management
 */
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useProfile } from '../composables/useProfile'

const { t } = useI18n()

const {
    user,
    isSaving: isUpdating,
    message,
    updateAddress
} = useProfile()

const addressForm = ref({
    province: '',
    district: '',
    ward: '',
    address_detail: ''
})

const syncForm = () => {
    if (user.value) {
        addressForm.value.province = (user.value as any).province || ''
        addressForm.value.district = (user.value as any).district || ''
        addressForm.value.ward = (user.value as any).ward || ''
        addressForm.value.address_detail = (user.value as any).address_detail || ''
    }
}

onMounted(syncForm)
watch(user, syncForm)

const handleUpdateAddress = async () => {
    try {
        await updateAddress(addressForm.value)
    } catch (err) {
        // Error handled in composable/message
    }
}
</script>

<template>
    <div class="shipping-address">
        <h2 class="text-xl font-bold text-white mb-8 border-b border-white/5 pb-4">{{ t('common.shippingAddress') }}</h2>

        <form @submit.prevent="handleUpdateAddress" class="max-w-2xl mx-auto space-y-8">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="form-label">{{ t('common.province') }}</label>
                    <input v-model="addressForm.province" type="text" class="form-input" placeholder="Tỉnh / Thành phố" />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ t('common.district') }}</label>
                    <input v-model="addressForm.district" type="text" class="form-input" placeholder="Quận / Huyện" />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ t('common.ward') }}</label>
                    <input v-model="addressForm.ward" type="text" class="form-input" placeholder="Phường / Xã" />
                </div>
                <div class="form-group">
                    <label class="form-label">{{ t('common.addressDetail') }}</label>
                    <input v-model="addressForm.address_detail" type="text" class="form-input"
                        placeholder="Số nhà 21, Đường ABC..." />
                </div>
            </div>

            <div v-if="message" class="p-4 rounded-xl text-center text-sm" 
                :class="message.includes('thành công') ? 'bg-success/10 text-success' : 'bg-error/10 text-error'">
                {{ message }}
            </div>

            <div class="flex justify-center pt-4">
                <button type="submit" class="btn btn-primary px-12 py-3 font-bold" :disabled="isUpdating">
                    <span v-if="isUpdating" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                    {{ isUpdating ? t('common.updating') : t('common.saveAddress') }}
                </button>
            </div>
        </form>

        <!-- Address Card Visualization -->
        <div v-if="addressForm.province" class="mt-12 p-6 bg-dark-700/30 border border-white/5 rounded-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <span class="text-6xl">📮</span>
            </div>
            <div class="relative z-10">
                <span class="text-xs font-bold text-primary uppercase tracking-widest mb-2 block">{{ t('common.currentAddress') }}</span>
                <p class="text-white text-lg font-medium leading-relaxed">
                    {{ addressForm.address_detail }}{{ addressForm.address_detail ? ', ' : '' }}
                    {{ addressForm.ward }}{{ addressForm.ward ? ', ' : '' }}
                    {{ addressForm.district }}{{ addressForm.district ? ', ' : '' }}
                    {{ addressForm.province }}
                </p>
                <div class="flex items-center gap-2 mt-4 text-slate-400 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                    </svg>
                    <span>{{ t('common.defaultForOrders') }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
