<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import BaseModal from '@/shared/components/BaseModal.vue'
import { useWarehouseStore } from '../store/store'

// Store
const store = useWarehouseStore()

// State
const searchQuery = ref('')
const showModal = ref(false)

// Form
const form = reactive({
    warehouse_id: null as number | null,
    product_id: null as number | null,
    stock_id: null as number | null,
    current_quantity: 0,
    new_quantity: 0,
    reason: '',
    notes: ''
})

// Computed
const isLoading = computed(() => store.isLoading)
const isSaving = computed(() => store.isSubmitting)
const adjustments = computed(() => store.stockAdjustments || [])
const warehouses = computed(() => store.warehouses || [])
const stocks = computed(() => store.stocks || [])

const filteredAdjustments = computed(() => {
    let result = adjustments.value

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter((a: any) =>
            a.product?.name?.toLowerCase().includes(query) ||
            a.reason?.toLowerCase().includes(query)
        )
    }

    return result
})

const stocksForWarehouse = computed(() => {
    if (!form.warehouse_id) return []
    return stocks.value.filter((s: any) => s.warehouse_id === form.warehouse_id)
})

const quantityDiff = computed(() => {
    return form.new_quantity - form.current_quantity
})

// Methods
const resetForm = () => {
    form.warehouse_id = null
    form.product_id = null
    form.stock_id = null
    form.current_quantity = 0
    form.new_quantity = 0
    form.reason = ''
    form.notes = ''
}

const openCreateModal = () => {
    resetForm()
    showModal.value = true
}

const onStockSelect = () => {
    const stock = stocks.value.find((s: any) => s.id === form.stock_id)
    if (stock) {
        form.product_id = stock.product_id
        form.current_quantity = stock.quantity
        form.new_quantity = stock.quantity
    }
}

const saveAdjustment = async () => {
    if (!form.reason) {
        alert('Vui lòng nhập lý do điều chỉnh!')
        return
    }

    try {
        await store.createStockAdjustment({
            stock_id: form.stock_id,
            warehouse_id: form.warehouse_id,
            product_id: form.product_id,
            previous_quantity: form.current_quantity,
            new_quantity: form.new_quantity,
            adjustment_quantity: quantityDiff.value,
            reason: form.reason,
            notes: form.notes
        })
        showModal.value = false
        await store.fetchStockAdjustments()
    } catch (error) {
        console.error('Error saving adjustment:', error)
    }
}

// Lifecycle
onMounted(async () => {
    await Promise.all([
        store.fetchStockAdjustments(),
        store.fetchWarehouses(),
        store.fetchAllStocks()
    ])
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Điều chỉnh tồn kho</h1>
                <p class="text-slate-400 mt-1">Điều chỉnh số lượng tồn kho (chỉ dành cho quản lý)</p>
            </div>
            <button @click="openCreateModal" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                </svg>
                Tạo điều chỉnh
            </button>
        </div>

        <!-- Warning Banner -->
        <div class="bg-error/10 border border-error/20 rounded-xl p-4 mb-6 flex-shrink-0">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" class="text-error flex-shrink-0 mt-0.5">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                </svg>
                <div class="text-sm text-error">
                    <p class="font-semibold mb-1">Cảnh báo nghiệp vụ:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Điều chỉnh tồn kho là nghiệp vụ đặc biệt, chỉ thực hiện khi cần thiết</li>
                        <li>Bắt buộc phải ghi lý do điều chỉnh</li>
                        <li>Mọi điều chỉnh sẽ được ghi log và không thể xóa</li>
                    </ul>
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
                    <input v-model="searchQuery" type="text" placeholder="Tìm kiếm..."
                        class="w-full pl-10 pr-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="sticky top-0 z-10 bg-dark-700">
                        <tr class="border-b border-white/10">
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Thời gian</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Kho</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Sản phẩm</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Trước</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Sau</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Chênh lệch</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Lý do</th>
                            <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">Người thực hiện</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="adj in filteredAdjustments" :key="adj.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-4 text-sm text-slate-400">{{ adj.created_at }}</td>
                            <td class="px-4 py-4 text-sm text-white">{{ adj.warehouse?.name || '-' }}</td>
                            <td class="px-4 py-4 text-sm text-white">{{ adj.product?.name || '-' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-300">{{ adj.previous_quantity }}</td>
                            <td class="px-4 py-4 text-sm text-white font-medium">{{ adj.new_quantity }}</td>
                            <td class="px-4 py-4">
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-medium',
                                    adj.adjustment_quantity > 0 ? 'bg-success/10 text-success' :
                                        adj.adjustment_quantity < 0 ? 'bg-error/10 text-error' : 'bg-slate-500/10 text-slate-400'
                                ]">
                                    {{ adj.adjustment_quantity > 0 ? '+' : '' }}{{ adj.adjustment_quantity }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-300 max-w-[200px] truncate" :title="adj.reason">
                                {{ adj.reason }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-400">{{ adj.user?.name || '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!filteredAdjustments.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có điều chỉnh nào</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <BaseModal v-model="showModal" title="Điều chỉnh tồn kho" size="md">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Kho *</label>
                    <select v-model="form.warehouse_id" class="form-input">
                        <option :value="null">Chọn kho</option>
                        <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                    </select>
                </div>

                <div v-if="form.warehouse_id">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Tồn kho cần điều chỉnh *</label>
                    <select v-model="form.stock_id" @change="onStockSelect" class="form-input">
                        <option :value="null">Chọn tồn kho</option>
                        <option v-for="s in stocksForWarehouse" :key="s.id" :value="s.id">
                            {{ s.product?.name }} - Lô: {{ s.inbound_batch?.batch_number || 'N/A' }} (Hiện có: {{
                            s.quantity }})
                        </option>
                    </select>
                </div>

                <div v-if="form.stock_id" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Số lượng hiện tại</label>
                        <div class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg">
                            <span class="text-lg font-semibold text-white">{{ form.current_quantity }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Số lượng mới *</label>
                        <input v-model.number="form.new_quantity" type="number" min="0" class="form-input" />
                    </div>
                </div>

                <div v-if="form.stock_id" class="bg-dark-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-400">Chênh lệch:</span>
                        <span :class="[
                            'text-xl font-bold',
                            quantityDiff > 0 ? 'text-success' : quantityDiff < 0 ? 'text-error' : 'text-slate-400'
                        ]">
                            {{ quantityDiff > 0 ? '+' : '' }}{{ quantityDiff }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Lý do điều chỉnh * <span class="text-error">(Bắt buộc)</span>
                    </label>
                    <textarea v-model="form.reason" rows="2" class="form-input"
                        placeholder="VD: Kiểm kê phát hiện thiếu, Hư hỏng do vận chuyển..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú thêm</label>
                    <textarea v-model="form.notes" rows="2" class="form-input"
                        placeholder="Ghi chú thêm (tùy chọn)"></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button @click="showModal = false" class="btn btn-secondary flex-1">Hủy</button>
                    <button @click="saveAdjustment" :disabled="isSaving || !form.stock_id || !form.reason"
                        class="btn btn-primary flex-1">
                        {{ isSaving ? 'Đang xử lý...' : 'Xác nhận điều chỉnh' }}
                    </button>
                </div>
            </div>
        </BaseModal>
    </div>
</template>
