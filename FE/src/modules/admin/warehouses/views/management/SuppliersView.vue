<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import BaseModal from '@/shared/components/BaseModal.vue'
import { useSwal } from '@/shared/utils'

interface Supplier {
    id: number
    name: string
    contact_person: string
    email: string
    phone: string
    address: string
    product_count: number
    status: 'active' | 'inactive'
    rating: number
}

const suppliers = ref<Supplier[]>([])
const isLoading = ref(true)
const showModal = ref(false)
const editingSupplier = ref<Supplier | null>(null)
const isSaving = ref(false)
const searchQuery = ref('')
const swal = useSwal()

const form = ref({
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    address: ''
})

const filteredSuppliers = computed(() => {
    if (!searchQuery.value) return suppliers.value
    const query = searchQuery.value.toLowerCase()
    return suppliers.value.filter(s =>
        s.name.toLowerCase().includes(query) ||
        s.contact_person?.toLowerCase().includes(query) ||
        s.email?.toLowerCase().includes(query)
    )
})

const fetchSuppliers = async () => {
    isLoading.value = true
    try {
        const response = await httpClient.get('/admin/suppliers')
        const data = response.data as any
        suppliers.value = data?.data?.data || data?.data || []
    } catch (error) {
        console.error('Failed to fetch suppliers:', error)
    } finally {
        isLoading.value = false
    }
}

const openAddModal = () => {
    editingSupplier.value = null
    form.value = { name: '', contact_person: '', email: '', phone: '', address: '' }
    showModal.value = true
}

const openEditModal = (supplier: Supplier) => {
    editingSupplier.value = supplier
    form.value = {
        name: supplier.name,
        contact_person: supplier.contact_person || '',
        email: supplier.email || '',
        phone: supplier.phone || '',
        address: supplier.address || ''
    }
    showModal.value = true
}

const saveSupplier = async () => {
    if (isSaving.value) return
    isSaving.value = true
    try {
        if (editingSupplier.value) {
            await httpClient.put(`/admin/suppliers/${editingSupplier.value.id}`, form.value)
            await swal.success('Cập nhật nhà cung cấp thành công!')
        } else {
            await httpClient.post('/admin/suppliers', form.value)
            await swal.success('Thêm nhà cung cấp thành công!')
        }
        showModal.value = false
        fetchSuppliers()
    } catch (error: any) {
        console.error('Failed to save supplier:', error)
        await swal.error(error.response?.data?.message || 'Lưu nhà cung cấp thất bại!')
    } finally {
        isSaving.value = false
    }
}

const deleteSupplier = async (id: number) => {
    const confirmed = await swal.confirmDelete('Bạn có chắc chắn muốn xóa nhà cung cấp này?')
    if (!confirmed) return
    
    try {
        await httpClient.delete(`/admin/suppliers/${id}`)
        suppliers.value = suppliers.value.filter(s => s.id !== id)
        await swal.success('Xóa nhà cung cấp thành công!')
    } catch (error: any) {
        console.error('Failed to delete supplier:', error)
        await swal.error(error.response?.data?.message || 'Xóa nhà cung cấp thất bại!')
    }
}

onMounted(fetchSuppliers)
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Nhà cung cấp</h1>
                <p class="text-slate-400 mt-1">Quản lý danh sách nhà cung cấp</p>
            </div>
            <button class="btn btn-primary" @click="openAddModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Thêm NCC
            </button>
        </div>

        <!-- Search -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex gap-4">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input v-model="searchQuery" type="text" class="form-input pl-10 w-full"
                        placeholder="Tìm nhà cung cấp..." />
                </div>
            </div>
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
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Nhà cung cấp</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Liên hệ</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Email / SĐT</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="supplier in filteredSuppliers" :key="supplier.id"
                            class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold">
                                        {{ supplier.name?.charAt(0) || 'N' }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ supplier.name }}</p>
                                        <p class="text-xs text-slate-500">{{ supplier.product_count || 0 }} sản phẩm</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-white">{{ supplier.contact_person || '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-white">{{ supplier.email || '-' }}</p>
                                <p class="text-xs text-slate-500">{{ supplier.phone || '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                                    supplier.status === 'active' ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-400'
                                ]">
                                    {{ supplier.status === 'active' ? 'Hoạt động' : 'Tạm ngưng' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(supplier)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteSupplier(supplier.id)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!filteredSuppliers.length" class="py-16 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <p class="text-slate-400">Chưa có nhà cung cấp nào</p>
                </div>
            </div>
        </div>

        <!-- Modal using BaseModal -->
        <BaseModal v-model="showModal" :title="editingSupplier ? 'Chỉnh sửa nhà cung cấp' : 'Thêm nhà cung cấp mới'"
            size="md">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tên công ty *</label>
                    <input v-model="form.name" type="text" class="form-input" placeholder="VD: Công ty ABC" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Người liên hệ *</label>
                    <input v-model="form.contact_person" type="text" class="form-input"
                        placeholder="VD: Nguyễn Văn A" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                        <input v-model="form.email" type="email" class="form-input" placeholder="email@company.com" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Điện thoại</label>
                        <input v-model="form.phone" type="tel" class="form-input" placeholder="0901234567" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Địa chỉ</label>
                    <textarea v-model="form.address" class="form-input" rows="2"
                        placeholder="Địa chỉ đầy đủ"></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary" :disabled="isSaving">Hủy</button>
                    <button @click="saveSupplier" class="btn btn-primary" :disabled="isSaving || !form.name">
                        <span v-if="isSaving"
                            class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
