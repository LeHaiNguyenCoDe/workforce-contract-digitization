<script setup lang="ts">
/**
 * Shipping Address View - Redesigned to match minimalist mockup
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
    <div class="shipping-address-container max-w-[800px] mx-auto pb-10">
        <!-- Title -->
        <h1 class="text-3xl font-medium text-[#9F7A5F] text-center mb-10">
            Địa chỉ nhận hàng
        </h1>

        <form @submit.prevent="handleUpdateAddress" class="space-y-8">
            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">{{ t('common.province') }}</label>
                    <input v-model="addressForm.province" type="text"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                        placeholder="Tỉnh / Thành phố" />
                </div>
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">{{ t('common.district') }}</label>
                    <input v-model="addressForm.district" type="text"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                        placeholder="Quận / Huyện" />
                </div>
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">{{ t('common.ward') }}</label>
                    <input v-model="addressForm.ward" type="text"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                        placeholder="Phường / Xã" />
                </div>
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">{{ t('common.addressDetail') }}</label>
                    <input v-model="addressForm.address_detail" type="text"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                        placeholder="Số nhà 21, Đường ABC..." />
                </div>
            </div>

            <!-- Message -->
            <div v-if="message" class="p-4 rounded-lg text-center"
                :class="message.includes('thành công') ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
                {{ message }}
            </div>

            <div class="flex justify-center pt-6">
                <button type="submit"
                    class="px-16 py-3 bg-[#9F7A5F] text-white rounded-lg text-2xl font-medium hover:bg-[#8A6A52] transition-colors shadow-sm disabled:opacity-70"
                    :disabled="isUpdating">
                    <span v-if="isUpdating"
                        class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin inline-block mr-2"></span>
                    {{ isUpdating ? 'Đang lưu...' : 'Lưu địa chỉ' }}
                </button>
            </div>
        </form>

        <!-- Current Address Card -->
        <div v-if="addressForm.province"
            class="mt-16 p-8 bg-[#FEFBF2] border border-[#D9D9D9] rounded-lg relative overflow-hidden group transition-all hover:shadow-md">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                <span class="text-7xl">📍</span>
            </div>
            <div class="relative z-10 space-y-4">
                <div class="flex items-center gap-2">
                    <span
                        class="px-3 py-1 bg-[#9F7A5F]/10 text-[#9F7A5F] text-xs font-bold rounded-full uppercase tracking-wider">
                        {{ t('common.currentAddress') }}
                    </span>
                    <span class="text-gray-400 text-sm">| Mặc định</span>
                </div>
                <p class="text-black text-2xl font-medium leading-relaxed max-w-[80%]">
                    {{ addressForm.address_detail }}{{ addressForm.address_detail ? ', ' : '' }}
                    {{ addressForm.ward }}{{ addressForm.ward ? ', ' : '' }}
                    {{ addressForm.district }}{{ addressForm.district ? ', ' : '' }}
                    {{ addressForm.province }}
                </p>
                <div class="flex items-center gap-2 text-gray-500 text-sm pt-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    <span>Liên hệ: {{ (user as any)?.phone || 'Chưa cập nhật' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional styling */
</style>
