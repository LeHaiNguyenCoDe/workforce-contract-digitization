<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSwal } from '@/shared/utils'
import BaseModal from '@/shared/components/BaseModal.vue'
import httpClient from '@/plugins/api/httpClient'

interface Warehouse {
    id: number
    name: string
}

interface Product {
    id: number
    name: string
    sku: string
}

interface TransferItem {
    id: number
    product_id: number
    product?: Product
    quantity: number
    received_quantity: number | null
}

interface Transfer {
    id: number
    transfer_code: string
    from_warehouse_id: number
    to_warehouse_id: number
    from_warehouse?: Warehouse
    to_warehouse?: Warehouse
    status: 'draft' | 'pending' | 'in_transit' | 'received' | 'cancelled'
    shipped_at: string | null
    received_at: string | null
    items_count: number
    items?: TransferItem[]
    created_at: string
}

const swal = useSwal()

// State
const transfers = ref<Transfer[]>([])
const warehouses = ref<Warehouse[]>([])
const products = ref<Product[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const showCreateModal = ref(false)
const showDetailModal = ref(false)
const selectedTransfer = ref<Transfer | null>(null)

const newTransfer = ref({
    from_warehouse_id: null as number | null,
    to_warehouse_id: null as number | null,
    notes: '',
    items: [] as { product_id: number; quantity: number }[]
})

// Methods
async function fetchTransfers() {
    try {
        isLoading.value = true
        const response = await httpClient.get('/admin/transfers')
        transfers.value = (response.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch transfers:', error)
    } finally {
        isLoading.value = false
    }
}

async function fetchWarehouses() {
    try {
        const response = await httpClient.get('/admin/warehouses')
        warehouses.value = (response.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch warehouses:', error)
    }
}

async function fetchProducts() {
    try {
        const response = await httpClient.get('/admin/products', { params: { per_page: 100 } })
        products.value = (response.data as any).data || []
    } catch (error) {
        console.error('Failed to fetch products:', error)
    }
}

function getStatusBadge(status: string) {
    switch (status) {
        case 'draft': return { class: 'bg-slate-500/10 text-slate-400', label: 'Nháp' }
        case 'pending': return { class: 'bg-info/10 text-info', label: 'Chờ xuất' }
        case 'in_transit': return { class: 'bg-warning/10 text-warning', label: 'Đang vận chuyển' }
        case 'received': return { class: 'bg-success/10 text-success', label: 'Đã nhận' }
        case 'cancelled': return { class: 'bg-error/10 text-error', label: 'Đã hủy' }
        default: return { class: 'bg-slate-500/10 text-slate-400', label: status }
    }
}

function formatDate(date: string | null): string {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('vi-VN', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

function openCreateModal() {
    newTransfer.value = { from_warehouse_id: null, to_warehouse_id: null, notes: '', items: [] }
    showCreateModal.value = true
}

function addItem() {
    newTransfer.value.items.push({ product_id: 0, quantity: 1 })
}

function removeItem(index: number) {
    newTransfer.value.items.splice(index, 1)
}

async function createTransfer() {
    if (!newTransfer.value.from_warehouse_id || !newTransfer.value.to_warehouse_id) {
        await swal.warning('Vui lòng chọn kho nguồn và kho đích!')
        return
    }
    if (newTransfer.value.items.length === 0) {
        await swal.warning('Vui lòng thêm sản phẩm!')
        return
    }

    try {
        isSaving.value = true
        await httpClient.post('/admin/transfers', newTransfer.value)
        await swal.success('Tạo phiếu chuyển kho thành công!')
        showCreateModal.value = false
        await fetchTransfers()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    } finally {
        isSaving.value = false
    }
}

async function openDetail(transfer: Transfer) {
    try {
        const response = await httpClient.get(`/admin/transfers/${transfer.id}`)
        selectedTransfer.value = (response.data as any).data
        showDetailModal.value = true
    } catch (error) {
        console.error('Failed to fetch detail:', error)
    }
}

async function shipTransfer() {
    if (!selectedTransfer.value) return
    const confirmed = await swal.confirm('Xuất kho? Tồn kho nguồn sẽ bị trừ.')
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/transfers/${selectedTransfer.value.id}/ship`)
        await swal.success('Xuất kho thành công!')
        await openDetail(selectedTransfer.value)
        await fetchTransfers()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

async function receiveTransfer() {
    if (!selectedTransfer.value) return
    const confirmed = await swal.confirm('Nhận kho? Tồn kho đích sẽ được cộng.')
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/transfers/${selectedTransfer.value.id}/receive`)
        await swal.success('Nhận kho thành công!')
        showDetailModal.value = false
        await fetchTransfers()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

async function cancelTransfer(transfer: Transfer) {
    const confirmed = await swal.confirm(`Hủy phiếu ${transfer.transfer_code}?`)
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/transfers/${transfer.id}/cancel`)
        await swal.success('Đã hủy!')
        await fetchTransfers()
        if (showDetailModal.value) showDetailModal.value = false
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

async function deleteTransfer(transfer: Transfer) {
    const confirmed = await swal.confirm(`Xóa phiếu ${transfer.transfer_code}?`)
    if (!confirmed) return

    try {
        await httpClient.delete(`/admin/transfers/${transfer.id}`)
        await swal.success('Đã xóa!')
        await fetchTransfers()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi!')
    }
}

onMounted(async () => {
    await Promise.all([fetchTransfers(), fetchWarehouses(), fetchProducts()])
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Chuyển kho nội bộ</h1>
                <p class="text-slate-400 mt-1">Luân chuyển hàng giữa các kho/chi nhánh</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">+ Tạo phiếu chuyển</button>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="transfers.length === 0" class="flex-1 flex items-center justify-center text-slate-400">
                Chưa có phiếu chuyển kho nào
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="bg-dark-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mã</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Từ kho</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">→</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Đến kho</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Số SP</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Trạng thái
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ngày tạo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="trf in transfers" :key="trf.id" class="hover:bg-white/5 cursor-pointer"
                            @click="openDetail(trf)">
                            <td class="px-4 py-3 font-mono text-primary">{{ trf.transfer_code }}</td>
                            <td class="px-4 py-3 text-white">{{ trf.from_warehouse?.name || '-' }}</td>
                            <td class="px-4 py-3 text-center text-slate-500">→</td>
                            <td class="px-4 py-3 text-white">{{ trf.to_warehouse?.name || '-' }}</td>
                            <td class="px-4 py-3 text-center text-white">{{ trf.items_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusBadge(trf.status).class]">
                                    {{ getStatusBadge(trf.status).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ formatDate(trf.created_at) }}</td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex items-center justify-end gap-2">
                                    <button v-if="trf.status === 'draft'" @click="deleteTransfer(trf)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center">
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
        <BaseModal v-model="showCreateModal" title="Tạo phiếu chuyển kho" size="lg">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Kho nguồn *</label>
                        <select v-model="newTransfer.from_warehouse_id" class="form-select w-full">
                            <option :value="null">-- Chọn kho --</option>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Kho đích *</label>
                        <select v-model="newTransfer.to_warehouse_id" class="form-select w-full">
                            <option :value="null">-- Chọn kho --</option>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id"
                                :disabled="w.id === newTransfer.from_warehouse_id">{{ w.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-slate-300">Sản phẩm *</label>
                        <button @click="addItem" type="button" class="btn btn-sm btn-secondary">+ Thêm SP</button>
                    </div>
                    <div class="space-y-2 max-h-48 overflow-auto">
                        <div v-for="(item, idx) in newTransfer.items" :key="idx" class="flex items-center gap-2">
                            <select v-model="item.product_id" class="form-select flex-1">
                                <option :value="0">-- Chọn SP --</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <input v-model.number="item.quantity" type="number" min="1" class="form-input w-20"
                                placeholder="SL" />
                            <button @click="removeItem(idx)" class="text-error hover:text-error/80">✕</button>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showCreateModal = false" class="btn btn-secondary">Hủy</button>
                    <button @click="createTransfer" class="btn btn-primary" :disabled="isSaving">Tạo</button>
                </div>
            </template>
        </BaseModal>

        <!-- Detail Modal -->
        <BaseModal v-model="showDetailModal" :title="`Phiếu chuyển ${selectedTransfer?.transfer_code || ''}`" size="lg">
            <div v-if="selectedTransfer" class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div class="flex items-center gap-4">
                        <span
                            :class="['px-3 py-1.5 rounded-full text-sm font-medium', getStatusBadge(selectedTransfer.status).class]">
                            {{ getStatusBadge(selectedTransfer.status).label }}
                        </span>
                        <span class="text-slate-400 text-sm">
                            {{ selectedTransfer.from_warehouse?.name }} → {{ selectedTransfer.to_warehouse?.name }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button v-if="selectedTransfer.status === 'draft'" @click="shipTransfer"
                            class="btn btn-sm btn-warning">
                            Xuất kho
                        </button>
                        <button v-if="selectedTransfer.status === 'in_transit'" @click="receiveTransfer"
                            class="btn btn-sm btn-success">
                            Nhận kho
                        </button>
                        <button v-if="selectedTransfer.status !== 'received' && selectedTransfer.status !== 'cancelled'"
                            @click="cancelTransfer(selectedTransfer)" class="btn btn-sm btn-secondary">Hủy</button>
                    </div>
                </div>

                <div class="border border-white/10 rounded-lg">
                    <table class="w-full">
                        <thead class="bg-dark-700">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs text-slate-400">Sản phẩm</th>
                                <th class="px-3 py-2 text-right text-xs text-slate-400">SL chuyển</th>
                                <th class="px-3 py-2 text-right text-xs text-slate-400">SL nhận</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="item in selectedTransfer.items" :key="item.id">
                                <td class="px-3 py-2 text-white">{{ item.product?.name || '-' }}</td>
                                <td class="px-3 py-2 text-right text-slate-400">{{ item.quantity }}</td>
                                <td class="px-3 py-2 text-right text-white">{{ item.received_quantity ?? '-' }}</td>
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
