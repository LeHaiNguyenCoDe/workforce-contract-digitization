<script setup lang="ts">
import { ref } from 'vue'
import httpClient from '@/plugins/api/httpClient'

const addressForm = ref({
    province: '',
    district: '',
    ward: '',
    address_detail: ''
})

const isUpdating = ref(false)
const message = ref('')

const updateAddress = async () => {
    isUpdating.value = true
    message.value = ''
    try {
        await httpClient.put('/frontend/profile/address', addressForm.value)
        message.value = 'Cập nhật địa chỉ thành công!'
    } catch (error: any) {
        message.value = error.response?.data?.message || 'Cập nhật thất bại!'
    } finally {
        isUpdating.value = false
    }
}
</script>

<template>
    <div class="shipping-address">
        <h2 class="section-title">Địa chỉ nhận hàng</h2>

        <form @submit.prevent="updateAddress" class="address-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Tỉnh / Thành phố</label>
                    <input v-model="addressForm.province" type="text" class="form-input" placeholder="Thanh Hóa" />
                </div>
                <div class="form-group">
                    <label>Quận / Huyện</label>
                    <input v-model="addressForm.district" type="text" class="form-input" placeholder="Hậu Lộc" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phường / Xã</label>
                    <input v-model="addressForm.ward" type="text" class="form-input" placeholder="Hưng Lộc" />
                </div>
                <div class="form-group">
                    <label>Ghi chú (số nhà, địa chỉ chi tiết)</label>
                    <input v-model="addressForm.address_detail" type="text" class="form-input"
                        placeholder="Số nhà 21" />
                </div>
            </div>

            <div v-if="message" class="message" :class="{ success: message.includes('thành công') }">
                {{ message }}
            </div>

            <button type="submit" class="btn btn-primary" :disabled="isUpdating">
                {{ isUpdating ? 'Đang cập nhật...' : 'Cập nhật' }}
            </button>
        </form>
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

.address-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.form-group label {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--color-text-secondary);
}

.message {
    padding: var(--space-3);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-4);
    text-align: center;
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-error);
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
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
