<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSwal } from '@/utils'
import BaseModal from '@/components/BaseModal.vue'
import httpClient from '@/plugins/api/httpClient'

interface Warehouse {
    id: number
    name: string
}

interface StocktakeItem {
    id: number
    product_id: number
    product?: { id: number; name: string; sku: string }
    system_quantity: number
    actual_quantity: number | null
    difference: number | null
    reason: string | null
}

interface Stocktake {
    id: number
    stocktake_code: string
    warehouse_id: number | null
    warehouse?: Warehouse
    status: 'draft' | 'in_progress' | 'pending_approval' | 'approved' | 'cancelled'
    is_locked: boolean
    started_at: string | null
    completed_at: string | null
    items_count: number
    items?: StocktakeItem[]
    created_at: string
}

const swal = useSwal()

// State
const stocktakes = ref<Stocktake[]>([])
const warehouses = ref<Warehouse[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const showCreateModal = ref(false)
const showDetailModal = ref(false)
const selectedStocktake = ref<Stocktake | null>(null)
const newStocktakeWarehouse = ref<number | null>(null)
const currentPage = ref(1)
const totalPages = ref(1)

// Methods
async function fetchStocktakes() {
    try {
        isLoading.value = true
        const response = await httpClient.get('/admin/stocktakes', { params: { page: currentPage.value } })
        stocktakes.value = response.data.data || []
        totalPages.value = response.data.meta?.last_page || 1
    } catch (error) {
        console.error('Failed to fetch stocktakes:', error)
    } finally {
        isLoading.value = false
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

function getStatusBadge(status: string) {
    switch (status) {
        case 'draft': return { class: 'bg-slate-500/10 text-slate-400', label: 'Nháp' }
        case 'in_progress': return { class: 'bg-info/10 text-info', label: 'Đang kiểm' }
        case 'pending_approval': return { class: 'bg-warning/10 text-warning', label: 'Chờ duyệt' }
        case 'approved': return { class: 'bg-success/10 text-success', label: 'Đã duyệt' }
        case 'cancelled': return { class: 'bg-error/10 text-error', label: 'Đã hủy' }
        default: return { class: 'bg-slate-500/10 text-slate-400', label: status }
    }
}

function formatDate(date: string | null): string {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

async function createStocktake() {
    try {
        isSaving.value = true
        const response = await httpClient.post('/admin/stocktakes', { warehouse_id: newStocktakeWarehouse.value })
        await swal.success('Tạo phiên kiểm kê thành công!')
        showCreateModal.value = false
        newStocktakeWarehouse.value = null
        await fetchStocktakes()
        // Open detail modal
        openDetail(response.data.data)
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Tạo thất bại!')
    } finally {
        isSaving.value = false
    }
}

async function openDetail(stocktake: Stocktake) {
    try {
        const response = await httpClient.get(`/admin/stocktakes/${stocktake.id}`)
        selectedStocktake.value = response.data.data
        showDetailModal.value = true
    } catch (error) {
        console.error('Failed to fetch stocktake detail:', error)
    }
}

async function startStocktake() {
    if (!selectedStocktake.value) return
    const confirm = await swal.confirm('Bắt đầu kiểm kê? Kho sẽ bị khóa trong quá trình kiểm kê.')
    if (!confirm.isConfirmed) return

    try {
        await httpClient.post(`/admin/stocktakes/${selectedStocktake.value.id}/start`)
        await swal.success('Bắt đầu kiểm kê, kho đã khóa!')
        await openDetail(selectedStocktake.value)
        await fetchStocktakes()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

async function saveItemCounts() {
    if (!selectedStocktake.value?.items) return

    try {
        isSaving.value = true
        const items = selectedStocktake.value.items.map(item => ({
            id: item.id,
            actual_quantity: item.actual_quantity || 0,
            reason: item.reason
        }))
        await httpClient.put(`/admin/stocktakes/${selectedStocktake.value.id}/items`, { items })
        await swal.success('Lưu số lượng thành công!')
        await openDetail(selectedStocktake.value)
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    } finally {
        isSaving.value = false
    }
}

async function completeStocktake() {
    if (!selectedStocktake.value) return
    const confirm = await swal.confirm('Hoàn thành kiểm kê và gửi duyệt?')
    if (!confirm.isConfirmed) return

    try {
        await httpClient.post(`/admin/stocktakes/${selectedStocktake.value.id}/complete`)
        await swal.success('Đã gửi duyệt!')
        await openDetail(selectedStocktake.value)
        await fetchStocktakes()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

async function approveStocktake() {
    if (!selectedStocktake.value) return
    const confirm = await swal.confirm('Duyệt và cân bằng kho? Tồn kho sẽ được điều chỉnh theo số đếm thực tế.')
    if (!confirm.isConfirmed) return

    try {
        await httpClient.post(`/admin/stocktakes/${selectedStocktake.value.id}/approve`)
        await swal.success('Đã duyệt và cân bằng kho!')
        showDetailModal.value = false
        await fetchStocktakes()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

async function cancelStocktake(stocktake: Stocktake) {
    const confirm = await swal.confirm(`Hủy phiên kiểm kê ${stocktake.stocktake_code}?`)
    if (!confirm.isConfirmed) return

    try {
        await httpClient.post(`/admin/stocktakes/${stocktake.id}/cancel`)
        await swal.success('Đã hủy!')
        await fetchStocktakes()
        if (showDetailModal.value) showDetailModal.value = false
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

async function deleteStocktake(stocktake: Stocktake) {
    const confirm = await swal.confirm(`Xóa phiên kiểm kê ${stocktake.stocktake_code}?`)
    if (!confirm.isConfirmed) return

    try {
        await httpClient.delete(`/admin/stocktakes/${stocktake.id}`)
        await swal.success('Đã xóa!')
        await fetchStocktakes()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

onMounted(async () => {
    await Promise.all([fetchStocktakes(), fetchWarehouses()])
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Kiểm kê định kỳ</h1>
                <p class="text-slate-400 mt-1">Tạo phiên kiểm kê, khóa kho và cân bằng tồn kho</p>
            </div>
            <button @click="showCreateModal = true" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Tạo phiên kiểm kê
            </button>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="stocktakes.length === 0" class="flex-1 flex items-center justify-center text-slate-400">
                Chưa có phiên kiểm kê nào
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="bg-dark-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mã</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Kho</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Số SP</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Trạng thái
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Khóa kho</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ngày tạo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="stk in stocktakes" :key="stk.id" class="hover:bg-white/5 cursor-pointer"
                            @click="openDetail(stk)">
                            <td class="px-4 py-3 font-mono text-primary">{{ stk.stocktake_code }}</td>
                            <td class="px-4 py-3 text-white">{{ stk.warehouse?.name || 'Tất cả' }}</td>
                            <td class="px-4 py-3 text-center text-white">{{ stk.items_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusBadge(stk.status).class]">
                                    {{ getStatusBadge(stk.status).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="stk.is_locked" class="text-warning">🔒</span>
                                <span v-else class="text-slate-500">-</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ formatDate(stk.created_at) }}</td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex items-center justify-end gap-2">
                                    <button v-if="stk.status === 'draft'" @click="deleteStocktake(stk)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center"
                                        title="Xóa">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        </svg>
                                    </button>
                                    <button v-if="stk.status !== 'approved' && stk.status !== 'cancelled'"
                                        @click="cancelStocktake(stk)"
                                        class="w-8 h-8 rounded-lg bg-slate-500/10 text-slate-400 hover:bg-slate-500/20 flex items-center justify-center"
                                        title="Hủy">
                                        ✕
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Modal -->
        <BaseModal v-model="showCreateModal" title="Tạo phiên kiểm kê" size="sm">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Chọn kho (để trống = tất cả)</label>
                    <select v-model="newStocktakeWarehouse" class="form-select w-full">
                        <option :value="null">-- Tất cả kho --</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showCreateModal = false" class="btn btn-secondary">Hủy</button>
                    <button @click="createStocktake" class="btn btn-primary" :disabled="isSaving">Tạo</button>
                </div>
            </template>
        </BaseModal>

        <!-- Detail Modal -->
        <BaseModal v-model="showDetailModal" :title="`Kiểm kê ${selectedStocktake?.stocktake_code || ''}`" size="xl">
            <div v-if="selectedStocktake" class="space-y-4">
                <!-- Status & Actions -->
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span
                            :class="['px-3 py-1.5 rounded-full text-sm font-medium', getStatusBadge(selectedStocktake.status).class]">
                            {{ getStatusBadge(selectedStocktake.status).label }}
                        </span>
                        <span v-if="selectedStocktake.is_locked" class="text-warning text-sm">🔒 Kho đang khóa</span>
                    </div>
                    <div class="flex gap-2">
                        <button v-if="selectedStocktake.status === 'draft'" @click="startStocktake"
                            class="btn btn-sm btn-info">
                            Bắt đầu kiểm kê
                        </button>
                        <button v-if="selectedStocktake.status === 'in_progress'" @click="saveItemCounts"
                            class="btn btn-sm btn-secondary" :disabled="isSaving">
                            Lưu
                        </button>
                        <button v-if="selectedStocktake.status === 'in_progress'" @click="completeStocktake"
                            class="btn btn-sm btn-warning">
                            Hoàn thành
                        </button>
                        <button v-if="selectedStocktake.status === 'pending_approval'" @click="approveStocktake"
                            class="btn btn-sm btn-success">
                            Duyệt & Cân bằng
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="max-h-96 overflow-auto border border-white/10 rounded-lg">
                    <table class="w-full">
                        <thead class="bg-dark-700 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs text-slate-400">Sản phẩm</th>
                                <th class="px-3 py-2 text-right text-xs text-slate-400">Tồn HT</th>
                                <th class="px-3 py-2 text-right text-xs text-slate-400">Thực tế</th>
                                <th class="px-3 py-2 text-right text-xs text-slate-400">Chênh lệch</th>
                                <th class="px-3 py-2 text-left text-xs text-slate-400">Lý do</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="item in selectedStocktake.items" :key="item.id">
                                <td class="px-3 py-2">
                                    <div class="text-white text-sm">{{ item.product?.name || '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ item.product?.sku }}</div>
                                </td>
                                <td class="px-3 py-2 text-right text-slate-400">{{ item.system_quantity }}</td>
                                <td class="px-3 py-2 text-right">
                                    <input v-if="selectedStocktake.status === 'in_progress'"
                                        v-model.number="item.actual_quantity" type="number" min="0"
                                        class="form-input w-20 text-right py-1 text-sm"
                                        @input="item.difference = (item.actual_quantity || 0) - item.system_quantity" />
                                    <span v-else class="text-white">{{ item.actual_quantity ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <span v-if="item.difference !== null"
                                        :class="item.difference > 0 ? 'text-success' : item.difference < 0 ? 'text-error' : 'text-slate-400'">
                                        {{ item.difference > 0 ? '+' : '' }}{{ item.difference }}
                                    </span>
                                    <span v-else class="text-slate-500">-</span>
                                </td>
                                <td class="px-3 py-2">
                                    <input v-if="selectedStocktake.status === 'in_progress' && item.difference !== 0"
                                        v-model="item.reason" type="text" placeholder="Lý do..."
                                        class="form-input w-full py-1 text-sm" />
                                    <span v-else class="text-slate-500 text-sm">{{ item.reason || '-' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <template #footer>
                <button @click="showDetailModal = false" class="btn btn-secondary">Đóng</button>
            </template>
        </BaseModal>
    </div>
</template>
