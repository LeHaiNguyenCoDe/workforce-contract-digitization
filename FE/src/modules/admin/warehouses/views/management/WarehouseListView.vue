<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import BaseModal from '@/components/BaseModal.vue'
import { useSwal } from '@/utils'

const { t } = useI18n()

interface Warehouse {
    id: number
    name: string
    code: string
    address?: string
    is_active?: boolean
    stocks_count?: number
}

const warehouses = ref<Warehouse[]>([])
const isLoading = ref(true)
const showModal = ref(false)
const editingWarehouse = ref<Warehouse | null>(null)
const isSaving = ref(false)
const formData = ref({ name: '', code: '', address: '', is_active: true })
const swal = useSwal()

const generateCode = (name: string): string => 'WH-' + name.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6) + '-' + Math.random().toString(36).substring(2, 5).toUpperCase()

watch(() => formData.value.name, (newName) => { if (!editingWarehouse.value && !formData.value.code) formData.value.code = generateCode(newName) })

const fetchWarehouses = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/admin/warehouses')
        const data = response.data
        if (data?.data?.data && Array.isArray(data.data.data)) warehouses.value = data.data.data
        else if (Array.isArray(data?.data)) warehouses.value = data.data
        else warehouses.value = []
    } catch (error) {
        warehouses.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    editingWarehouse.value = null
    formData.value = { name: '', code: '', address: '', is_active: true }
    showModal.value = true
}

const openEditModal = (wh: Warehouse) => {
    editingWarehouse.value = wh
    formData.value = { name: wh.name, code: wh.code, address: wh.address || '', is_active: wh.is_active ?? true }
    showModal.value = true
}

const saveWarehouse = async () => {
    if (isSaving.value) return
    isSaving.value = true
    try {
        const payload: any = { name: formData.value.name, code: formData.value.code || generateCode(formData.value.name), address: formData.value.address, is_active: formData.value.is_active }
        if (editingWarehouse.value) {
            await httpClient.put(`/admin/warehouses/${editingWarehouse.value.id}`, payload)
            await swal.success('Cập nhật kho hàng thành công!')
        } else {
            await httpClient.post('/admin/warehouses', payload)
            await swal.success('Tạo kho hàng thành công!')
        }
        showModal.value = false
        fetchWarehouses()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lưu thất bại!')
    } finally {
        isSaving.value = false
    }
}

const deleteWarehouse = async (id: number) => {
    const confirmed = await swal.confirmDelete('Bạn có chắc muốn xóa kho hàng này?')
    if (!confirmed) return
    
    try {
        await httpClient.delete(`/admin/warehouses/${id}`)
        warehouses.value = warehouses.value.filter(w => w.id !== id)
        await swal.success('Xóa kho hàng thành công!')
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Xóa thất bại!')
    }
}

onMounted(fetchWarehouses)
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.warehouses') }}</h1>
                <p class="text-slate-400 mt-1">Quản lý kho hàng</p>
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

        <!-- Grid Cards -->
        <div class="flex-1 min-h-0 overflow-auto">
            <div v-if="isLoading" class="flex items-center justify-center py-20">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="warehouses.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 pb-6">
                <div v-for="wh in warehouses" :key="wh.id"
                    class="bg-dark-800 rounded-2xl border border-white/10 p-6 hover:border-primary/50 transition-colors">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                <polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                        </div>
                        <span
                            :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium', wh.is_active !== false ? 'bg-success/10 text-success' : 'bg-error/10 text-error']">{{
                                wh.is_active !== false ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <h3 class="font-semibold text-white text-lg mb-1">{{ wh.name }}</h3>
                    <p class="text-xs text-secondary font-mono mb-2">{{ wh.code }}</p>
                    <p v-if="wh.address" class="text-slate-400 text-sm mb-4">{{ wh.address }}</p>
                    <div class="flex items-center justify-between pt-4 border-t border-white/10">
                        <span class="text-sm text-slate-500">{{ wh.stocks_count ?? 0 }} sản phẩm</span>
                        <div class="flex items-center gap-2">
                            <button @click="openEditModal(wh)"
                                class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                </svg>
                            </button>
                            <button @click="deleteWarehouse(wh.id)"
                                class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18" />
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-16 bg-dark-800 rounded-xl border border-white/10">
                <p class="text-slate-400">Chưa có kho hàng nào</p>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal v-model="showModal" :title="editingWarehouse ? 'Chỉnh sửa kho' : 'Tạo kho mới'" size="md">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tên kho *</label>
                    <input v-model="formData.name" type="text" class="form-input" placeholder="VD: Kho Hà Nội" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Mã kho *</label>
                    <input v-model="formData.code" type="text" class="form-input font-mono uppercase"
                        placeholder="Tự động" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Địa chỉ</label>
                    <input v-model="formData.address" type="text" class="form-input" placeholder="Địa chỉ đầy đủ" />
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="formData.is_active" type="checkbox" id="wh_active" class="w-4 h-4 rounded" />
                    <label for="wh_active" class="text-sm text-slate-300">Kích hoạt</label>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">{{
                        t('common.cancel') }}</button>
                    <button @click="saveWarehouse" class="btn btn-primary" :disabled="isSaving || !formData.name">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : t('common.save') }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
