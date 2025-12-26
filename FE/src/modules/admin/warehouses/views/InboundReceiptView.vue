<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import BaseModal from '@/shared/components/BaseModal.vue'
import { useWarehouseStore } from '../store/store'

// Store
const store = useWarehouseStore()

// State
const searchQuery = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const editingReceipt = ref<any>(null)

// Form
const form = reactive({
    supplier_id: null as number | null,
    warehouse_id: null as number | null,
    expected_date: '',
    notes: '',
    items: [] as { product_id: number | null; expected_qty: number; unit_price: number }[]
})

// Computed
const isLoading = computed(() => store.isLoading)
const isSaving = computed(() => store.isSubmitting)
const receipts = computed(() => store.inboundReceipts || [])
const suppliers = computed(() => store.suppliers || [])
const warehouses = computed(() => store.warehouses || [])
const products = computed(() => store.products || [])

const filteredReceipts = computed(() => {
    let result = receipts.value

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter((r: any) =>
            r.receipt_number?.toLowerCase().includes(query) ||
            r.supplier?.name?.toLowerCase().includes(query)
        )
    }

    if (statusFilter.value) {
        result = result.filter((r: any) => r.status === statusFilter.value)
    }

    return result
})

// Methods
const getStatusClass = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-warning/10 text-warning'
        case 'received': return 'bg-info/10 text-info'
        case 'qc_in_progress': return 'bg-purple-500/10 text-purple-400'
        case 'qc_completed': return 'bg-primary/10 text-primary'
        case 'completed': return 'bg-success/10 text-success'
        case 'cancelled': return 'bg-error/10 text-error'
        default: return 'bg-slate-500/10 text-slate-400'
    }
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending': return 'Chờ xử lý'
        case 'received': return 'Đã nhận'
        case 'qc_in_progress': return 'Đang QC'
        case 'qc_completed': return 'QC xong'
        case 'completed': return 'Hoàn thành'
        case 'cancelled': return 'Đã hủy'
        default: return status
    }
}

const resetForm = () => {
    form.supplier_id = null
    form.warehouse_id = null
    form.expected_date = ''
    form.notes = ''
    form.items = [{ product_id: null, expected_qty: 1, unit_price: 0 }]
}

const openCreateModal = () => {
    editingReceipt.value = null
    resetForm()
    showModal.value = true
}

const openEditModal = (receipt: any) => {
    editingReceipt.value = receipt
    form.supplier_id = receipt.supplier_id
    form.warehouse_id = receipt.warehouse_id
    form.expected_date = receipt.expected_date || ''
    form.notes = receipt.notes || ''
    form.items = receipt.items?.map((i: any) => ({
        product_id: i.product_id,
        expected_qty: i.expected_qty,
        unit_price: i.unit_price || 0
    })) || [{ product_id: null, expected_qty: 1, unit_price: 0 }]
    showModal.value = true
}

const addItem = () => {
    form.items.push({ product_id: null, expected_qty: 1, unit_price: 0 })
}

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1)
    }
}

const calculateTotal = computed(() => {
    return form.items.reduce((sum, item) => {
        const qty = Number(item.expected_qty) || 0
        const price = Number(item.unit_price) || 0
        return sum + (qty * price)
    }, 0)
})

const saveReceipt = async () => {
    // Validate items - filter out empty items
    const validItems = form.items.filter(i => i.product_id !== null && i.expected_qty > 0)
    if (validItems.length === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm')
        return
    }

    try {
        const payload = {
            ...form,
            items: validItems
        }
        if (editingReceipt.value) {
            await store.updateInboundReceipt(editingReceipt.value.id, payload)
        } else {
            await store.createInboundReceipt(payload)
        }
        showModal.value = false
        await store.fetchInboundReceipts()
    } catch (error: any) {
        console.error('Error saving receipt:', error)
        alert(error.response?.data?.message || 'Có lỗi xảy ra khi lưu phiếu')
    }
}

const approveReceipt = async (id: number) => {
    console.log('approveReceipt called with id:', id)
    if (!window.confirm('Xác nhận duyệt phiếu nhập này?')) return
    try {
        await store.approveInboundReceipt(id)
        await store.fetchInboundReceipts()
    } catch (error: any) {
        console.error('Approve error:', error)
        alert(error.response?.data?.message || 'Có lỗi xảy ra')
    }
}

const cancelReceipt = async (id: number) => {
    console.log('cancelReceipt called with id:', id)
    if (!window.confirm('Xác nhận hủy phiếu nhập này?')) return
    try {
        await store.cancelInboundReceipt(id)
        await store.fetchInboundReceipts()
    } catch (error: any) {
        console.error('Cancel error:', error)
        alert(error.response?.data?.message || 'Có lỗi xảy ra')
    }
}

const deleteReceipt = async (id: number) => {
    console.log('deleteReceipt called with id:', id)
    if (!window.confirm('Xác nhận xóa phiếu nhập này? Thao tác này không thể hoàn tác.')) return
    try {
        await store.deleteInboundReceipt(id)
        await store.fetchInboundReceipts()
    } catch (error: any) {
        console.error('Delete error:', error)
        alert(error.response?.data?.message || 'Có lỗi xảy ra khi xóa')
    }
}

const createBatchFromReceipt = async (receipt: any) => {
    console.log('createBatchFromReceipt called with receipt:', receipt)
    // Navigate to batch creation with receipt info
    store.setSelectedReceipt(receipt)
    alert('Chức năng tạo lô nhập sẽ navigate đến trang Lô nhập & QC')
    // TODO: router.push to inbound-batches page
}

// Lifecycle
onMounted(async () => {
    await store.fetchWarehouses()
    await store.fetchSuppliers()
    await store.fetchProducts()
    await store.fetchInboundReceipts()
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Phiếu nhập kho</h1>
                <p class="text-slate-400 mt-1">Quản lý phiếu yêu cầu nhập kho từ nhà cung cấp</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                </svg>
                Tạo phiếu nhập
            </button>
        </div>

        <!-- Info Banner -->
        <div class="bg-info/10 border border-info/20 rounded-xl p-4 mb-6 flex-shrink-0">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" class="text-info flex-shrink-0 mt-0.5">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
                <div class="text-sm text-info">
                    <p class="font-semibold mb-1">Quy trình nhập kho:</p>
                    <p>1. Tạo phiếu nhập (Draft) → 2. Duyệt phiếu → 3. Nhận hàng & tạo Lô nhập → 4. QC → 5. Nhập kho</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex gap-4">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input v-model="searchQuery" type="text" placeholder="Tìm kiếm phiếu nhập..."
                        class="w-full pl-10 pr-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
                <select v-model="statusFilter"
                    class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <option value="">Tất cả trạng thái</option>
                    <option value="draft">Nháp</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="receiving">Đang nhận</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full min-w-[1000px]">
                    <thead class="sticky top-0 z-10 bg-dark-700">
                        <tr class="border-b border-white/10">
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Mã phiếu</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Nhà cung cấp</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Kho</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Ngày dự kiến</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Số SP</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-4 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="receipt in filteredReceipts" :key="receipt.id"
                            class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-4">
                                <code class="text-sm font-mono text-primary">{{ receipt.receipt_number }}</code>
                            </td>
                            <td class="px-4 py-4 text-sm text-white">{{ receipt.supplier?.name || '-' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-300">{{ receipt.warehouse?.name || '-' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-400">{{ receipt.expected_date || '-' }}</td>
                            <td class="px-4 py-4 text-sm text-white">{{ receipt.items?.length || 0 }}</td>
                            <td class="px-4 py-4">
                                <span
                                    :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusClass(receipt.status)]">
                                    {{ getStatusLabel(receipt.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit -->
                                    <button v-if="receipt.status === 'pending'" @click="openEditModal(receipt)"
                                        class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center"
                                        title="Sửa">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                    </button>
                                    <!-- Approve -->
                                    <button v-if="receipt.status === 'pending'" @click="approveReceipt(receipt.id)"
                                        class="w-8 h-8 rounded-lg bg-success/10 text-success hover:bg-success/20 flex items-center justify-center"
                                        title="Duyệt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </button>
                                    <!-- Create Batch -->
                                    <button v-if="receipt.status === 'received'"
                                        @click="createBatchFromReceipt(receipt)"
                                        class="w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 flex items-center justify-center"
                                        title="Tạo lô nhập">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path
                                                d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                        </svg>
                                    </button>
                                    <!-- Cancel -->
                                    <button v-if="['pending', 'received'].includes(receipt.status)"
                                        @click="cancelReceipt(receipt.id)"
                                        class="w-8 h-8 rounded-lg bg-warning/10 text-warning hover:bg-warning/20 flex items-center justify-center"
                                        title="Hủy">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 6 6 18" />
                                            <path d="m6 6 12 12" />
                                        </svg>
                                    </button>
                                    <!-- Delete -->
                                    <button v-if="receipt.status === 'pending'" @click="deleteReceipt(receipt.id)"
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

                <div v-if="!filteredReceipts.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có phiếu nhập nào</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal v-model="showModal" :title="editingReceipt ? 'Sửa phiếu nhập' : 'Tạo phiếu nhập'" size="lg">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nhà cung cấp *</label>
                        <select v-model="form.supplier_id" class="form-input">
                            <option :value="null">Chọn nhà cung cấp</option>
                            <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Kho nhập *</label>
                        <select v-model="form.warehouse_id" class="form-input">
                            <option :value="null">Chọn kho</option>
                            <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ngày dự kiến nhận</label>
                    <input v-model="form.expected_date" type="date" class="form-input" />
                </div>

                <!-- Items -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-slate-300">Sản phẩm</label>
                        <button @click="addItem" type="button" class="text-sm text-primary hover:text-primary-light">
                            + Thêm dòng
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div v-for="(item, index) in form.items" :key="index" class="flex gap-2 items-center">
                            <select v-model="item.product_id" class="form-input flex-1">
                                <option :value="null">Chọn sản phẩm</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <input v-model.number="item.expected_qty" type="number" min="1" placeholder="SL"
                                class="form-input w-24" />
                            <input v-model.number="item.unit_price" type="number" min="0" placeholder="Đơn giá"
                                class="form-input w-32" />
                            <button v-if="form.items.length > 1" @click="removeItem(index)" type="button"
                                class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 6 6 18" />
                                    <path d="m6 6 12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 mt-2">
                        Tổng giá trị: <span class="font-semibold text-primary">{{ calculateTotal.toLocaleString() }}
                            đ</span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
                    <textarea v-model="form.notes" rows="2" class="form-input" placeholder="Ghi chú thêm..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button @click="showModal = false" class="btn btn-secondary flex-1">Hủy</button>
                    <button @click="saveReceipt" :disabled="isSaving || !form.supplier_id || !form.warehouse_id"
                        class="btn btn-primary flex-1">
                        {{ isSaving ? 'Đang lưu...' : 'Lưu nháp' }}
                    </button>
                </div>
            </div>
        </BaseModal>
    </div>
</template>
