<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import BaseModal from '@/components/BaseModal.vue'

// Store
const store = useWarehouseStore()

// State
const searchQuery = ref('')
const statusFilter = ref('')
const purposeFilter = ref('')
const showModal = ref(false)
const editingReceipt = ref<any>(null)

// Form
const form = reactive({
    warehouse_id: null as number | null,
    purpose: 'sales' as 'sales' | 'transfer' | 'internal' | 'return',
    destination_warehouse_id: null as number | null,
    notes: '',
    items: [] as { stock_id: number | null; quantity: number }[]
})

// Computed
const isLoading = computed(() => store.isLoading)
const isSaving = computed(() => store.isSubmitting)
const receipts = computed(() => store.outboundReceipts || [])
const warehouses = computed(() => store.warehouses || [])
const availableStocks = computed(() => store.availableStocks || [])

const filteredReceipts = computed(() => {
    let result = receipts.value

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter((r: any) =>
            r.receipt_number?.toLowerCase().includes(query)
        )
    }

    if (statusFilter.value) {
        result = result.filter((r: any) => r.status === statusFilter.value)
    }

    if (purposeFilter.value) {
        result = result.filter((r: any) => r.purpose === purposeFilter.value)
    }

    return result
})

// Methods
const getStatusClass = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-warning/10 text-warning'
        case 'approved': return 'bg-info/10 text-info'
        case 'completed': return 'bg-success/10 text-success'
        case 'cancelled': return 'bg-error/10 text-error'
        default: return 'bg-slate-500/10 text-slate-400'
    }
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending': return 'Chờ duyệt'
        case 'approved': return 'Đã duyệt'
        case 'completed': return 'Hoàn thành'
        case 'cancelled': return 'Đã hủy'
        default: return status
    }
}

const getPurposeLabel = (purpose: string) => {
    switch (purpose) {
        case 'sales': return 'Bán hàng'
        case 'transfer': return 'Chuyển kho'
        case 'internal': return 'Nội bộ'
        case 'return': return 'Trả hàng'
        default: return purpose
    }
}

const resetForm = () => {
    form.warehouse_id = null
    form.purpose = 'sales'
    form.destination_warehouse_id = null
    form.notes = ''
    form.items = [{ stock_id: null, quantity: 1 }]
}

const openCreateModal = () => {
    editingReceipt.value = null
    resetForm()
    showModal.value = true
}

const addItem = () => {
    form.items.push({ stock_id: null, quantity: 1 })
}

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1)
    }
}

const getStockInfo = (stockId: number | null) => {
    if (!stockId) return null
    return availableStocks.value.find((s: any) => s.id === stockId)
}

const saveReceipt = async () => {
    // Validate items
    const validItems = form.items.filter(i => i.stock_id !== null && i.quantity > 0)
    if (validItems.length === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm để xuất kho')
        return
    }

    try {
        const payload = {
            ...form,
            items: validItems
        }
        if (editingReceipt.value) {
            await store.updateOutboundReceipt(editingReceipt.value.id, payload)
        } else {
            await store.createOutboundReceipt(payload)
        }
        showModal.value = false
        await store.fetchOutboundReceipts()
    } catch (error) {
        console.error('Error saving receipt:', error)
    }
}

const approveReceipt = async (id: number) => {
    if (confirm('Xác nhận duyệt phiếu xuất này? Hệ thống sẽ kiểm tra và giữ chỗ tồn kho.')) {
        await store.approveOutboundReceipt(id)
        await store.fetchOutboundReceipts()
    }
}

const completeReceipt = async (id: number) => {
    if (confirm('Xác nhận hoàn thành xuất kho? Tồn kho sẽ bị trừ.')) {
        await store.completeOutboundReceipt(id)
        await store.fetchOutboundReceipts()
    }
}

const cancelReceipt = async (id: number) => {
    if (confirm('Xác nhận hủy phiếu xuất này?')) {
        await store.cancelOutboundReceipt(id)
        await store.fetchOutboundReceipts()
    }
}

const viewReceipt = (receipt: any) => {
    const items = receipt.items || []
    const details = `
Mã phiếu: ${receipt.receipt_number}
Kho xuất: ${receipt.warehouse?.name || '-'}
Mục đích: ${getPurposeLabel(receipt.purpose)}
Trạng thái: ${getStatusLabel(receipt.status)}
Ngày tạo: ${receipt.created_at}

Chi tiết sản phẩm:
${items.map((i: any, idx: number) => `${idx + 1}. ${i.product?.name || 'Sản phẩm'} - SL: ${i.quantity}`).join('\n')}

Ghi chú: ${receipt.notes || 'Không có'}
    `.trim()

    alert(details)
}

// Lifecycle
onMounted(async () => {
    await store.fetchWarehouses()
    await store.fetchAllStocks()
    await store.fetchOutboundReceipts()
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Phiếu xuất kho</h1>
                <p class="text-slate-400 mt-1">Quản lý xuất kho cho bán hàng, chuyển kho, nội bộ</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                </svg>
                Tạo phiếu xuất
            </button>
        </div>

        <!-- Warning Banner -->
        <div class="bg-warning/10 border border-warning/20 rounded-xl p-4 mb-6 flex-shrink-0">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" class="text-warning flex-shrink-0 mt-0.5">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                </svg>
                <div class="text-sm text-warning">
                    <p class="font-semibold mb-1">Lưu ý quan trọng:</p>
                    <p>Chỉ xuất được từ tồn kho khả dụng (Available). Hệ thống sẽ kiểm tra và giữ chỗ khi duyệt phiếu.
                    </p>
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
                    <input v-model="searchQuery" type="text" placeholder="Tìm kiếm phiếu xuất..."
                        class="w-full pl-10 pr-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
                <select v-model="purposeFilter"
                    class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <option value="">Tất cả mục đích</option>
                    <option value="sales">Bán hàng</option>
                    <option value="transfer">Chuyển kho</option>
                    <option value="internal">Nội bộ</option>
                    <option value="return">Trả hàng</option>
                </select>
                <select v-model="statusFilter"
                    class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="approved">Đã duyệt</option>
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
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Kho xuất</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Mục đích</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Số SP</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Tổng SL</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Ngày tạo</th>
                            <th class="px-4 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="receipt in filteredReceipts" :key="receipt.id"
                            class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-4">
                                <code class="text-sm font-mono text-primary">{{ receipt.receipt_number }}</code>
                            </td>
                            <td class="px-4 py-4 text-sm text-white">{{ receipt.warehouse?.name || '-' }}</td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                    {{ getPurposeLabel(receipt.purpose) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-white">{{ receipt.items?.length || 0 }}</td>
                            <td class="px-4 py-4 text-sm font-medium text-white">
                                {{receipt.items?.reduce((sum: number, i: any) => sum + (i.quantity || 0), 0) || 0}}
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusClass(receipt.status)]">
                                    {{ getStatusLabel(receipt.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-400">{{ receipt.created_at }}</td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- View Detail (always show) -->
                                    <button @click="viewReceipt(receipt)"
                                        class="w-8 h-8 rounded-lg bg-slate-500/10 text-slate-400 hover:bg-slate-500/20 flex items-center justify-center"
                                        title="Xem chi tiết">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
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
                                    <!-- Complete -->
                                    <button v-if="receipt.status === 'approved'" @click="completeReceipt(receipt.id)"
                                        class="w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 flex items-center justify-center"
                                        title="Hoàn thành xuất kho">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                            <polyline points="22 4 12 14.01 9 11.01" />
                                        </svg>
                                    </button>
                                    <!-- Cancel -->
                                    <button v-if="['pending', 'approved'].includes(receipt.status)"
                                        @click="cancelReceipt(receipt.id)"
                                        class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center"
                                        title="Hủy">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 6 6 18" />
                                            <path d="m6 6 12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!filteredReceipts.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có phiếu xuất nào</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal v-model="showModal" title="Tạo phiếu xuất kho" size="lg">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Kho xuất *</label>
                        <select v-model="form.warehouse_id" class="form-input">
                            <option :value="null">Chọn kho</option>
                            <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Mục đích *</label>
                        <select v-model="form.purpose" class="form-input">
                            <option value="sales">Bán hàng</option>
                            <option value="transfer">Chuyển kho</option>
                            <option value="internal">Nội bộ</option>
                            <option value="return">Trả hàng</option>
                        </select>
                    </div>
                </div>

                <div v-if="form.purpose === 'transfer'">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Kho đích *</label>
                    <select v-model="form.destination_warehouse_id" class="form-input">
                        <option :value="null">Chọn kho đích</option>
                        <option v-for="wh in warehouses.filter((w: any) => w.id !== form.warehouse_id)" :key="wh.id"
                            :value="wh.id">
                            {{ wh.name }}
                        </option>
                    </select>
                </div>

                <!-- Items -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-slate-300">Sản phẩm xuất</label>
                        <button @click="addItem" type="button" class="text-sm text-primary hover:text-primary-light">
                            + Thêm dòng
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div v-for="(item, index) in form.items" :key="index" class="flex gap-2 items-center">
                            <select v-model="item.stock_id" class="form-input flex-1">
                                <option :value="null">Chọn tồn kho</option>
                                <option v-for="s in availableStocks" :key="s.id" :value="s.id">
                                    {{ s.product?.name }} - Lô: {{ s.inbound_batch?.batch_number || 'N/A' }} (Khả dụng:
                                    {{ s.available_quantity }})
                                </option>
                            </select>
                            <input v-model.number="item.quantity" type="number" min="1"
                                :max="getStockInfo(item.stock_id)?.available_quantity || 999" placeholder="SL"
                                class="form-input w-24" />
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
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
                    <textarea v-model="form.notes" rows="2" class="form-input" placeholder="Ghi chú thêm..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button @click="showModal = false" class="btn btn-secondary flex-1">Hủy</button>
                    <button @click="saveReceipt" :disabled="isSaving || !form.warehouse_id"
                        class="btn btn-primary flex-1">
                        {{ isSaving ? 'Đang lưu...' : 'Tạo phiếu' }}
                    </button>
                </div>
            </div>
        </BaseModal>
    </div>
</template>
