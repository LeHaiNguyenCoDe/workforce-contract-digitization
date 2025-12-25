<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import BaseModal from '@/shared/components/BaseModal.vue'

const { t } = useI18n()

interface Promotion {
    id: number
    name: string
    code?: string
    type: 'percent' | 'fixed_amount'
    value: number
    starts_at?: string
    ends_at?: string
    is_active?: boolean
}

const promotions = ref<Promotion[]>([])
const isLoading = ref(true)
const showModal = ref(false)
const editingPromotion = ref<Promotion | null>(null)
const isSaving = ref(false)
const formData = ref({ name: '', code: '', type: 'percent' as 'percent' | 'fixed_amount', value: 10, starts_at: '', ends_at: '', is_active: true })

const formatDate = (date?: string) => date ? new Date(date).toLocaleDateString('vi-VN') : '-'
const formatDiscount = (promo: Promotion) => promo.type === 'percent' ? `${promo.value}%` : new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(promo.value)
const isExpired = (promo: Promotion) => promo.ends_at ? new Date(promo.ends_at) < new Date() : false
const generateCode = (name: string): string => name.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10) + Math.random().toString(36).substring(2, 5).toUpperCase()

const fetchPromotions = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/admin/promotions')
        const data = response.data
        if (data?.data?.data && Array.isArray(data.data.data)) promotions.value = data.data.data
        else if (Array.isArray(data?.data)) promotions.value = data.data
        else promotions.value = []
    } catch (error) {
        promotions.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    editingPromotion.value = null
    formData.value = { name: '', code: '', type: 'percent', value: 10, starts_at: '', ends_at: '', is_active: true }
    showModal.value = true
}

const openEditModal = (promo: Promotion) => {
    editingPromotion.value = promo
    formData.value = { name: promo.name, code: promo.code || '', type: promo.type, value: promo.value, starts_at: promo.starts_at?.split('T')[0] || '', ends_at: promo.ends_at?.split('T')[0] || '', is_active: promo.is_active ?? true }
    showModal.value = true
}

const savePromotion = async () => {
    if (isSaving.value) return
    isSaving.value = true
    try {
        const payload: any = { name: formData.value.name, type: formData.value.type, value: formData.value.value, is_active: formData.value.is_active }
        if (formData.value.code) payload.code = formData.value.code
        if (formData.value.starts_at) payload.starts_at = formData.value.starts_at
        if (formData.value.ends_at) payload.ends_at = formData.value.ends_at
        if (editingPromotion.value) await httpClient.put(`/admin/promotions/${editingPromotion.value.id}`, payload)
        else { if (!payload.code) payload.code = generateCode(formData.value.name); await httpClient.post('/admin/promotions', payload) }
        showModal.value = false
        fetchPromotions()
    } catch (error: any) {
        alert(error.response?.data?.message || 'Lưu thất bại!')
    } finally {
        isSaving.value = false
    }
}

const deletePromotion = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa khuyến mãi này?')) return
    try {
        await httpClient.delete(`/admin/promotions/${id}`)
        promotions.value = promotions.value.filter(p => p.id !== id)
    } catch (error: any) {
        alert(error.response?.data?.message || 'Xóa thất bại!')
    }
}

onMounted(fetchPromotions)
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.promotions') }}</h1>
                <p class="text-slate-400 mt-1">Quản lý các chương trình khuyến mãi</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                {{ t('common.create') }}
            </button>
        </div>

        <!-- Table Container -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 bg-dark-700">
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tên / Mã</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Giảm giá</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Thời gian</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">{{ t('common.actions')
                                }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="promo in promotions" :key="promo.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-400">#{{ promo.id }}</td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-white">{{ promo.name }}</p>
                                <p class="text-xs font-mono text-secondary">{{ promo.code }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-primary text-white">{{
                                    formatDiscount(promo) }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <p class="text-slate-400">{{ formatDate(promo.starts_at) }}</p>
                                <p class="text-slate-500">→ {{ formatDate(promo.ends_at) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', isExpired(promo) ? 'bg-error/10 text-error' : 'bg-success/10 text-success']">{{
                                        isExpired(promo) ? 'Hết hạn' : 'Đang chạy' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(promo)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button @click="deletePromotion(promo.id)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!promotions.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có khuyến mãi nào</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal :show="showModal" :title="editingPromotion ? 'Chỉnh sửa khuyến mãi' : 'Tạo khuyến mãi mới'" size="md"
            @close="showModal = false">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Tên *</label>
                        <input v-model="formData.name" type="text" class="form-input" placeholder="Black Friday" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Mã</label>
                        <input v-model="formData.code" type="text" class="form-input font-mono uppercase"
                            placeholder="Tự động" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Loại *</label>
                        <select v-model="formData.type" class="form-input">
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed_amount">Số tiền</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Giá trị *</label>
                        <input v-model.number="formData.value" type="number" class="form-input" min="1" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Bắt đầu</label>
                        <input v-model="formData.starts_at" type="date" class="form-input" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Kết thúc</label>
                        <input v-model="formData.ends_at" type="date" class="form-input" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="formData.is_active" type="checkbox" id="is_active" class="w-4 h-4 rounded" />
                    <label for="is_active" class="text-sm text-slate-300">Kích hoạt</label>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">{{
                        t('common.cancel') }}</button>
                    <button @click="savePromotion" class="btn btn-primary"
                        :disabled="isSaving || !formData.name || !formData.value">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : t('common.save') }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
