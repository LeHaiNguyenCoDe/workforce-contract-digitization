<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSwal } from '@/utils'
import BaseModal from '@/components/BaseModal.vue'
import httpClient from '@/plugins/api/httpClient'

interface Product {
    id: number
    name: string
    sku: string
}

interface Warehouse {
    id: number
    name: string
}

interface Supplier {
    id: number
    name: string
}

interface Batch {
    id: number
    batch_code: string
    product_id: number
    warehouse_id: number | null
    supplier_id: number | null
    quantity: number
    remaining_quantity: number
    unit_cost: number
    manufacturing_date: string | null
    expiry_date: string | null
    status: 'available' | 'reserved' | 'expired' | 'depleted'
    created_at: string
    product?: Product
    warehouse?: Warehouse
    supplier?: Supplier
}

interface BatchForm {
    product_id: number | null
    warehouse_id: number | null
    supplier_id: number | null
    quantity: number
    unit_cost: number
    manufacturing_date: string
    expiry_date: string
    notes: string
}

const swal = useSwal()

// State
const batches = ref<Batch[]>([])
const products = ref<Product[]>([])
const warehouses = ref<Warehouse[]>([])
const suppliers = ref<Supplier[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const showModal = ref(false)
const editingBatch = ref<Batch | null>(null)
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const batchForm = ref<BatchForm>({
    product_id: null,
    warehouse_id: null,
    supplier_id: null,
    quantity: 1,
    unit_cost: 0,
    manufacturing_date: '',
    expiry_date: '',
    notes: ''
})

// Computed
const filteredBatches = computed(() => batches.value)

// Methods
async function fetchBatches() {
    try {
        isLoading.value = true
        const params: Record<string, string | number> = {
            page: currentPage.value,
            per_page: 15
        }
        if (searchQuery.value) params.search = searchQuery.value
        if (statusFilter.value) params.status = statusFilter.value

        const response = await httpClient.get('/admin/batches', { params })
        batches.value = response.data.data || []
        totalPages.value = response.data.meta?.last_page || 1
    } catch (error) {
        console.error('Failed to fetch batches:', error)
    } finally {
        isLoading.value = false
    }
}

async function fetchProducts() {
    try {
        const response = await httpClient.get('/admin/products', { params: { per_page: 100 } })
        products.value = response.data.data || []
    } catch (error) {
        console.error('Failed to fetch products:', error)
    }
}

async function fetchWarehouses() {
    try {
        const response = await httpClient.get('/admin/warehouses')
        warehouses.value = response.data.data || []
    } catch (error) {
        console.error('Failed to fetch warehouses:', error)
    }
}

async function fetchSuppliers() {
    try {
        const response = await httpClient.get('/admin/suppliers')
        suppliers.value = response.data.data || []
    } catch (error) {
        console.error('Failed to fetch suppliers:', error)
    }
}

function getStatusBadge(status: string) {
    switch (status) {
        case 'available': return { class: 'bg-success/10 text-success', label: 'Còn hàng' }
        case 'reserved': return { class: 'bg-warning/10 text-warning', label: 'Đã đặt' }
        case 'expired': return { class: 'bg-error/10 text-error', label: 'Hết hạn' }
        case 'depleted': return { class: 'bg-slate-500/10 text-slate-400', label: 'Đã hết' }
        default: return { class: 'bg-slate-500/10 text-slate-400', label: status }
    }
}

function getDaysUntilExpiry(expiryDate: string | null): number | null {
    if (!expiryDate) return null
    const expiry = new Date(expiryDate)
    const now = new Date()
    const diff = Math.ceil((expiry.getTime() - now.getTime()) / (1000 * 60 * 60 * 24))
    return diff
}

function getExpiryClass(expiryDate: string | null): string {
    const days = getDaysUntilExpiry(expiryDate)
    if (days === null) return ''
    if (days < 0) return 'text-error'
    if (days <= 30) return 'text-warning'
    return 'text-success'
}

function formatDate(date: string | null): string {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('vi-VN')
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

function openCreateModal() {
    editingBatch.value = null
    batchForm.value = {
        product_id: null,
        warehouse_id: null,
        supplier_id: null,
        quantity: 1,
        unit_cost: 0,
        manufacturing_date: '',
        expiry_date: '',
        notes: ''
    }
    showModal.value = true
}

function openEditModal(batch: Batch) {
    editingBatch.value = batch
    batchForm.value = {
        product_id: batch.product_id,
        warehouse_id: batch.warehouse_id,
        supplier_id: batch.supplier_id,
        quantity: batch.quantity,
        unit_cost: batch.unit_cost,
        manufacturing_date: batch.manufacturing_date || '',
        expiry_date: batch.expiry_date || '',
        notes: ''
    }
    showModal.value = true
}

async function saveBatch() {
    if (isSaving.value) return

    if (!batchForm.value.product_id) {
        await swal.warning('Vui lòng chọn sản phẩm!')
        return
    }

    if (batchForm.value.quantity < 1) {
        await swal.warning('Số lượng phải lớn hơn 0!')
        return
    }

    try {
        isSaving.value = true
        const payload = { ...batchForm.value }

        if (editingBatch.value) {
            await httpClient.put(`/admin/batches/${editingBatch.value.id}`, payload)
            await swal.success('Cập nhật lô hàng thành công!')
        } else {
            await httpClient.post('/admin/batches', payload)
            await swal.success('Tạo lô hàng thành công!')
        }

        showModal.value = false
        await fetchBatches()
    } catch (error: any) {
        console.error('Failed to save batch:', error)
        await swal.error(error.response?.data?.message || 'Lưu thất bại!')
    } finally {
        isSaving.value = false
    }
}

async function deleteBatch(batch: Batch) {
    const confirm = await swal.confirm(`Xóa lô hàng ${batch.batch_code}?`)
    if (!confirm.isConfirmed) return

    try {
        await httpClient.delete(`/admin/batches/${batch.id}`)
        await swal.success('Xóa lô hàng thành công!')
        await fetchBatches()
    } catch (error: any) {
        console.error('Failed to delete batch:', error)
        await swal.error(error.response?.data?.message || 'Xóa thất bại!')
    }
}

function changePage(page: number) {
    currentPage.value = page
    fetchBatches()
}

// Lifecycle
onMounted(async () => {
    await Promise.all([fetchBatches(), fetchProducts(), fetchWarehouses(), fetchSuppliers()])
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Lô hàng & Hạn sử dụng</h1>
                <p class="text-slate-400 mt-1">Quản lý lô hàng theo FEFO (Hết hạn trước xuất trước)</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Tạo lô hàng
            </button>
        </div>

        <!-- Filters -->
        <div class="flex gap-4 mb-4 flex-shrink-0">
            <input v-model="searchQuery" @input="fetchBatches" type="text" class="form-input w-64"
                placeholder="Tìm mã lô, tên SP..." />
            <select v-model="statusFilter" @change="fetchBatches" class="form-select w-40">
                <option value="">Tất cả trạng thái</option>
                <option value="available">Còn hàng</option>
                <option value="reserved">Đã đặt</option>
                <option value="expired">Hết hạn</option>
                <option value="depleted">Đã hết</option>
            </select>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="batches.length === 0" class="flex-1 flex items-center justify-center">
                <div class="text-center text-slate-400">
                    <p>Chưa có lô hàng nào</p>
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="bg-dark-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mã lô</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Sản phẩm</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Kho</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">SL ban đầu
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">SL còn</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Giá vốn</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">HSD</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Trạng thái
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="batch in filteredBatches" :key="batch.id" class="hover:bg-white/5">
                            <td class="px-4 py-3">
                                <span class="font-mono text-primary">{{ batch.batch_code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-white">{{ batch.product?.name || '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ batch.product?.sku || '' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ batch.warehouse?.name || '-' }}</td>
                            <td class="px-4 py-3 text-right text-white">{{ batch.quantity }}</td>
                            <td class="px-4 py-3 text-right">
                                <span :class="batch.remaining_quantity === 0 ? 'text-slate-500' : 'text-success'">
                                    {{ batch.remaining_quantity }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-white">{{ formatCurrency(batch.unit_cost) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div v-if="batch.expiry_date" :class="getExpiryClass(batch.expiry_date)">
                                    {{ formatDate(batch.expiry_date) }}
                                    <span class="text-xs block">
                                        ({{ getDaysUntilExpiry(batch.expiry_date) }} ngày)
                                    </span>
                                </div>
                                <span v-else class="text-slate-500">-</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusBadge(batch.status).class]">
                                    {{ getStatusBadge(batch.status).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(batch)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center"
                                        title="Sửa">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteBatch(batch)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center"
                                        title="Xóa">
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
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/10">
                <span class="text-sm text-slate-400">Trang {{ currentPage }} / {{ totalPages }}</span>
                <div class="flex gap-2">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                        class="btn btn-sm btn-secondary" :class="{ 'opacity-50': currentPage === 1 }">Trước</button>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                        class="btn btn-sm btn-secondary"
                        :class="{ 'opacity-50': currentPage === totalPages }">Sau</button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal v-model="showModal" :title="editingBatch ? 'Sửa lô hàng' : 'Tạo lô hàng mới'" size="lg">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Sản phẩm *</label>
                        <select v-model="batchForm.product_id" class="form-select w-full" :disabled="!!editingBatch">
                            <option :value="null">-- Chọn sản phẩm --</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Kho</label>
                        <select v-model="batchForm.warehouse_id" class="form-select w-full">
                            <option :value="null">-- Chọn kho --</option>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nhà cung cấp</label>
                        <select v-model="batchForm.supplier_id" class="form-select w-full">
                            <option :value="null">-- Chọn NCC --</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Số lượng *</label>
                        <input v-model.number="batchForm.quantity" type="number" min="1" class="form-input w-full" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Giá vốn (VNĐ) *</label>
                        <input v-model.number="batchForm.unit_cost" type="number" min="0" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Ngày sản xuất</label>
                        <input v-model="batchForm.manufacturing_date" type="date" class="form-input w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Hạn sử dụng</label>
                        <input v-model="batchForm.expiry_date" type="date" class="form-input w-full" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
                    <textarea v-model="batchForm.notes" rows="2" class="form-input w-full"
                        placeholder="Ghi chú..."></textarea>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false" class="btn btn-secondary">Hủy</button>
                    <button @click="saveBatch" class="btn btn-primary" :disabled="isSaving">
                        <span v-if="isSaving">Đang lưu...</span>
                        <span v-else>{{ editingBatch ? 'Cập nhật' : 'Tạo lô hàng' }}</span>
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
