<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSwal } from '@/utils'
import BaseModal from '@/components/BaseModal.vue'
import httpClient from '@/plugins/api/httpClient'

interface Product {
    id: number
    name: string
    sku: string
    thumbnail?: string
}

interface Warehouse {
    id: number
    name: string
}

interface Supplier {
    id: number
    name: string
}

interface User {
    id: number
    name: string
}

interface PurchaseRequest {
    id: number
    request_code: string
    product_id: number
    warehouse_id: number | null
    supplier_id: number | null
    requested_quantity: number
    current_stock: number
    min_stock: number
    status: 'pending' | 'approved' | 'rejected' | 'ordered' | 'completed'
    source: 'auto' | 'manual'
    notes: string | null
    created_at: string
    approved_at: string | null
    product?: Product
    warehouse?: Warehouse
    supplier?: Supplier
    requester?: User
    approver?: User
}

const swal = useSwal()

// State
const requests = ref<PurchaseRequest[]>([])
const products = ref<Product[]>([])
const warehouses = ref<Warehouse[]>([])
const suppliers = ref<Supplier[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const showModal = ref(false)
const showDetailModal = ref(false)
const selectedRequest = ref<PurchaseRequest | null>(null)
const statusFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const form = ref({
    product_id: null as number | null,
    warehouse_id: null as number | null,
    supplier_id: null as number | null,
    requested_quantity: 1,
    current_stock: 0,
    min_stock: 0,
    notes: ''
})

// Computed
const statusCounts = computed(() => {
    const counts: Record<string, number> = { pending: 0, approved: 0, rejected: 0, ordered: 0, completed: 0 }
    requests.value.forEach(r => {
        if (counts[r.status] !== undefined) counts[r.status]++
    })
    return counts
})

// Methods
async function fetchRequests() {
    isLoading.value = true
    try {
        const params: Record<string, any> = { per_page: 20, page: currentPage.value }
        if (statusFilter.value) params.status = statusFilter.value

        const response = await httpClient.get('/admin/purchase-requests', { params })
        const data = response.data as any
        if (data.status === 'success') {
            requests.value = data.data
            totalPages.value = data.meta?.last_page || 1
        }
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi tải dữ liệu')
    } finally {
        isLoading.value = false
    }
}

async function fetchProducts() {
    try {
        const response = await httpClient.get('/admin/products?per_page=100')
        products.value = (response.data as any).data || []
    } catch (error) {
        console.error('Error fetching products', error)
    }
}

async function fetchWarehouses() {
    try {
        const response = await httpClient.get('/admin/warehouses')
        warehouses.value = (response.data as any).data || []
    } catch (error) {
        console.error('Error fetching warehouses', error)
    }
}

async function fetchSuppliers() {
    try {
        const response = await httpClient.get('/admin/suppliers')
        suppliers.value = (response.data as any).data || []
    } catch (error) {
        console.error('Error fetching suppliers', error)
    }
}

function getStatusBadge(status: string) {
    const badges: Record<string, { bg: string; text: string; label: string }> = {
        pending: { bg: 'bg-yellow-500/20', text: 'text-yellow-400', label: 'Chờ duyệt' },
        approved: { bg: 'bg-blue-500/20', text: 'text-blue-400', label: 'Đã duyệt' },
        rejected: { bg: 'bg-red-500/20', text: 'text-red-400', label: 'Từ chối' },
        ordered: { bg: 'bg-purple-500/20', text: 'text-purple-400', label: 'Đã đặt hàng' },
        completed: { bg: 'bg-green-500/20', text: 'text-green-400', label: 'Hoàn thành' },
    }
    return badges[status] || { bg: 'bg-slate-700', text: 'text-slate-400', label: status }
}

function getSourceBadge(source: string) {
    return source === 'auto'
        ? { bg: 'bg-cyan-500/20', text: 'text-cyan-400', label: 'Tự động' }
        : { bg: 'bg-slate-700', text: 'text-slate-400', label: 'Thủ công' }
}

function formatDate(date: string | null): string {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('vi-VN')
}

function openCreateModal() {
    form.value = {
        product_id: null,
        warehouse_id: null,
        supplier_id: null,
        requested_quantity: 1,
        current_stock: 0,
        min_stock: 0,
        notes: ''
    }
    showModal.value = true
}

function openDetail(request: PurchaseRequest) {
    selectedRequest.value = request
    showDetailModal.value = true
}

async function createRequest() {
    if (!form.value.product_id) {
        await swal.warning('Vui lòng chọn sản phẩm')
        return
    }
    if (form.value.requested_quantity < 1) {
        await swal.warning('Số lượng phải lớn hơn 0')
        return
    }

    isSaving.value = true
    try {
        await httpClient.post('/admin/purchase-requests', form.value)
        await swal.success('Tạo phiếu đề nghị thành công')
        showModal.value = false
        await fetchRequests()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi tạo phiếu')
    } finally {
        isSaving.value = false
    }
}

async function approveRequest(request: PurchaseRequest) {
    const confirmed = await swal.confirm(`Duyệt phiếu ${request.request_code}?`)
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/purchase-requests/${request.id}/approve`)
        await swal.success('Đã duyệt phiếu')
        await fetchRequests()
        if (selectedRequest.value?.id === request.id) {
            showDetailModal.value = false
        }
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi')
    }
}

async function rejectRequest(request: PurchaseRequest) {
    const confirmed = await swal.confirm(`Từ chối phiếu ${request.request_code}?`)
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/purchase-requests/${request.id}/reject`)
        await swal.success('Đã từ chối phiếu')
        await fetchRequests()
        if (selectedRequest.value?.id === request.id) {
            showDetailModal.value = false
        }
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi')
    }
}

async function markOrdered(request: PurchaseRequest) {
    const confirmed = await swal.confirm(`Đánh dấu đã đặt hàng phiếu ${request.request_code}?`)
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/purchase-requests/${request.id}/mark-ordered`)
        await swal.success('Đã đánh dấu đặt hàng')
        await fetchRequests()
        showDetailModal.value = false
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi')
    }
}

async function completeRequest(request: PurchaseRequest) {
    const confirmed = await swal.confirm(`Hoàn thành phiếu ${request.request_code}?`)
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/purchase-requests/${request.id}/complete`)
        await swal.success('Đã hoàn thành phiếu')
        await fetchRequests()
        showDetailModal.value = false
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi')
    }
}

async function cancelRequest(request: PurchaseRequest) {
    const confirmed = await swal.confirmDelete(`Hủy phiếu ${request.request_code}?`)
    if (!confirmed) return

    try {
        await httpClient.post(`/admin/purchase-requests/${request.id}/cancel`)
        await swal.success('Đã hủy phiếu')
        await fetchRequests()
        showDetailModal.value = false
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi')
    }
}

async function deleteRequest(request: PurchaseRequest) {
    const confirmed = await swal.confirmDelete('Thao tác này không thể hoàn tác')
    if (!confirmed) return

    try {
        await httpClient.delete(`/admin/purchase-requests/${request.id}`)
        await swal.success('Đã xóa phiếu')
        await fetchRequests()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi')
    }
}

function changePage(page: number) {
    currentPage.value = page
    fetchRequests()
}

// Lifecycle
onMounted(async () => {
    await Promise.all([fetchRequests(), fetchProducts(), fetchWarehouses(), fetchSuppliers()])
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Phiếu đề nghị nhập hàng</h1>
                <p class="text-slate-400 mt-1">Quản lý yêu cầu nhập hàng từ hệ thống hoặc nhân viên</p>
            </div>
            <button @click="openCreateModal"
                class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Tạo phiếu đề nghị
            </button>
        </div>

        <!-- Status Summary -->
        <div class="grid grid-cols-5 gap-3 mb-6 flex-shrink-0">
            <button @click="statusFilter = ''; fetchRequests()"
                :class="['rounded-lg p-3 text-left transition-colors border', !statusFilter ? 'bg-slate-700 border-primary' : 'bg-dark-800 border-white/10 hover:border-white/20']">
                <div class="text-xl font-bold text-white">{{ requests.length }}</div>
                <div class="text-xs text-slate-400">Tất cả</div>
            </button>
            <button @click="statusFilter = 'pending'; fetchRequests()"
                :class="['rounded-lg p-3 text-left transition-colors border', statusFilter === 'pending' ? 'bg-yellow-500/20 border-yellow-500' : 'bg-dark-800 border-white/10 hover:border-white/20']">
                <div class="text-xl font-bold text-yellow-400">{{ statusCounts.pending }}</div>
                <div class="text-xs text-slate-400">Chờ duyệt</div>
            </button>
            <button @click="statusFilter = 'approved'; fetchRequests()"
                :class="['rounded-lg p-3 text-left transition-colors border', statusFilter === 'approved' ? 'bg-blue-500/20 border-blue-500' : 'bg-dark-800 border-white/10 hover:border-white/20']">
                <div class="text-xl font-bold text-blue-400">{{ statusCounts.approved }}</div>
                <div class="text-xs text-slate-400">Đã duyệt</div>
            </button>
            <button @click="statusFilter = 'ordered'; fetchRequests()"
                :class="['rounded-lg p-3 text-left transition-colors border', statusFilter === 'ordered' ? 'bg-purple-500/20 border-purple-500' : 'bg-dark-800 border-white/10 hover:border-white/20']">
                <div class="text-xl font-bold text-purple-400">{{ statusCounts.ordered }}</div>
                <div class="text-xs text-slate-400">Đã đặt hàng</div>
            </button>
            <button @click="statusFilter = 'completed'; fetchRequests()"
                :class="['rounded-lg p-3 text-left transition-colors border', statusFilter === 'completed' ? 'bg-green-500/20 border-green-500' : 'bg-dark-800 border-white/10 hover:border-white/20']">
                <div class="text-xl font-bold text-green-400">{{ statusCounts.completed }}</div>
                <div class="text-xs text-slate-400">Hoàn thành</div>
            </button>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
            </div>

            <div v-else-if="requests.length === 0" class="flex-1 flex items-center justify-center text-slate-400">
                Chưa có phiếu đề nghị nào
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="bg-dark-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mã phiếu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Sản phẩm</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">SL yêu cầu
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Nguồn</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Trạng thái
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ngày tạo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="req in requests" :key="req.id" class="hover:bg-white/5 cursor-pointer"
                            @click="openDetail(req)">
                            <td class="px-4 py-3">
                                <span class="font-mono text-primary">{{ req.request_code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-white">{{ req.product?.name }}</div>
                                <div class="text-xs text-slate-500">{{ req.product?.sku }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-white">{{ req.requested_quantity }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="['px-2 py-0.5 rounded text-xs', getSourceBadge(req.source).bg, getSourceBadge(req.source).text]">
                                    {{ getSourceBadge(req.source).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    :class="['px-2 py-0.5 rounded text-xs', getStatusBadge(req.status).bg, getStatusBadge(req.status).text]">
                                    {{ getStatusBadge(req.status).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ formatDate(req.created_at) }}</td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex items-center justify-end gap-1">
                                    <button v-if="req.status === 'pending'" @click="approveRequest(req)"
                                        class="p-1.5 hover:bg-green-500/20 rounded text-green-400" title="Duyệt">
                                        ✓
                                    </button>
                                    <button v-if="req.status === 'pending'" @click="rejectRequest(req)"
                                        class="p-1.5 hover:bg-red-500/20 rounded text-red-400" title="Từ chối">
                                        ✕
                                    </button>
                                    <button v-if="req.status === 'pending'" @click="deleteRequest(req)"
                                        class="p-1.5 hover:bg-red-500/20 rounded text-slate-400 hover:text-red-400"
                                        title="Xóa">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="p-4 border-t border-white/10 flex justify-center gap-2">
                <button v-for="page in totalPages" :key="page" @click="changePage(page)"
                    :class="['w-8 h-8 rounded', page === currentPage ? 'bg-primary text-white' : 'bg-dark-700 text-slate-400 hover:bg-dark-600']">
                    {{ page }}
                </button>
            </div>
        </div>

        <!-- Create Modal -->
        <BaseModal v-model="showModal" title="Tạo phiếu đề nghị nhập hàng">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Sản phẩm *</label>
                    <select v-model="form.product_id"
                        class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                        <option :value="null">Chọn sản phẩm</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Kho</label>
                        <select v-model="form.warehouse_id"
                            class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                            <option :value="null">Không chỉ định</option>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nhà cung cấp</label>
                        <select v-model="form.supplier_id"
                            class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                            <option :value="null">Chọn NCC</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Số lượng yêu cầu *</label>
                    <input type="number" v-model.number="form.requested_quantity" min="1"
                        class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Ghi chú</label>
                    <textarea v-model="form.notes" rows="2"
                        class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary resize-none"></textarea>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false"
                        class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition-colors">
                        Hủy
                    </button>
                    <button @click="createRequest" :disabled="isSaving"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors disabled:opacity-50">
                        {{ isSaving ? 'Đang tạo...' : 'Tạo phiếu' }}
                    </button>
                </div>
            </template>
        </BaseModal>

        <!-- Detail Modal -->
        <BaseModal v-model="showDetailModal" title="Chi tiết phiếu đề nghị" size="lg">
            <div v-if="selectedRequest" class="space-y-6">
                <!-- Header Info -->
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-2xl font-bold font-mono text-primary">{{ selectedRequest.request_code }}</div>
                        <div class="text-slate-400 mt-1">Ngày tạo: {{ formatDate(selectedRequest.created_at) }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            :class="['px-3 py-1 rounded text-sm', getSourceBadge(selectedRequest.source).bg, getSourceBadge(selectedRequest.source).text]">
                            {{ getSourceBadge(selectedRequest.source).label }}
                        </span>
                        <span
                            :class="['px-3 py-1 rounded text-sm', getStatusBadge(selectedRequest.status).bg, getStatusBadge(selectedRequest.status).text]">
                            {{ getStatusBadge(selectedRequest.status).label }}
                        </span>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="bg-dark-700 rounded-lg p-4">
                    <div class="text-sm text-slate-400 mb-2">Sản phẩm</div>
                    <div class="text-lg font-semibold text-white">{{ selectedRequest.product?.name }}</div>
                    <div class="text-slate-400">SKU: {{ selectedRequest.product?.sku }}</div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <div class="text-3xl font-bold text-primary">{{ selectedRequest.requested_quantity }}</div>
                        <div class="text-sm text-slate-400">Số lượng yêu cầu</div>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <div class="text-3xl font-bold text-white">{{ selectedRequest.current_stock }}</div>
                        <div class="text-sm text-slate-400">Tồn kho hiện tại</div>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <div class="text-3xl font-bold text-red-400">{{ selectedRequest.min_stock }}</div>
                        <div class="text-sm text-slate-400">Ngưỡng tối thiểu</div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-slate-400">Kho:</span>
                        <span class="text-white ml-2">{{ selectedRequest.warehouse?.name || 'Không chỉ định' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Nhà cung cấp:</span>
                        <span class="text-white ml-2">{{ selectedRequest.supplier?.name || 'Chưa chọn' }}</span>
                    </div>
                    <div v-if="selectedRequest.requester">
                        <span class="text-slate-400">Người tạo:</span>
                        <span class="text-white ml-2">{{ selectedRequest.requester.name }}</span>
                    </div>
                    <div v-if="selectedRequest.approver">
                        <span class="text-slate-400">Người duyệt:</span>
                        <span class="text-white ml-2">{{ selectedRequest.approver.name }}</span>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="selectedRequest.notes" class="bg-dark-700 rounded-lg p-4">
                    <div class="text-sm text-slate-400 mb-1">Ghi chú</div>
                    <div class="text-white whitespace-pre-wrap">{{ selectedRequest.notes }}</div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-between">
                    <div>
                        <button v-if="selectedRequest?.status === 'pending' || selectedRequest?.status === 'approved'"
                            @click="cancelRequest(selectedRequest!)"
                            class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors">
                            Hủy phiếu
                        </button>
                    </div>
                    <div class="flex gap-3">
                        <button v-if="selectedRequest?.status === 'pending'" @click="rejectRequest(selectedRequest!)"
                            class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors">
                            Từ chối
                        </button>
                        <button v-if="selectedRequest?.status === 'pending'" @click="approveRequest(selectedRequest!)"
                            class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition-colors">
                            Duyệt
                        </button>
                        <button v-if="selectedRequest?.status === 'approved'" @click="markOrdered(selectedRequest!)"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition-colors">
                            Đánh dấu đã đặt hàng
                        </button>
                        <button v-if="selectedRequest?.status === 'ordered'" @click="completeRequest(selectedRequest!)"
                            class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition-colors">
                            Hoàn thành
                        </button>
                        <button @click="showDetailModal = false"
                            class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition-colors">
                            Đóng
                        </button>
                    </div>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
