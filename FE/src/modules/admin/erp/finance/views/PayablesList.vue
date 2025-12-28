<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { debtService, type AccountPayable } from '../services/debtService'
import { financeService, type Fund } from '../services/financeService'
import { useSwal } from '@/shared/utils'

const swal = useSwal()

// State
const isLoading = ref(false)
const payables = ref<AccountPayable[]>([])
const funds = ref<Fund[]>([])
const statusFilter = ref('')
const showPayModal = ref(false)
const showDetailModal = ref(false)
const selectedAP = ref<AccountPayable | null>(null)
const payAmount = ref(0)
const selectedFundId = ref<number | null>(null)

// Columns
const columns = [
    { key: 'ap_code', label: 'Mã' },
    { key: 'supplier', label: 'Nhà cung cấp' },
    { key: 'reference', label: 'Tham chiếu' },
    { key: 'total_amount', label: 'Tổng phải trả' },
    { key: 'paid_amount', label: 'Đã trả' },
    { key: 'remaining_amount', label: 'Còn lại' },
    { key: 'status', label: 'Trạng thái' },
    { key: 'due_date', label: 'Hạn trả' },
]

// Status labels
const statusLabels: Record<string, { text: string, color: string }> = {
    open: { text: 'Chưa trả', color: 'bg-warning/10 text-warning' },
    partial: { text: 'Trả một phần', color: 'bg-info/10 text-info' },
    paid: { text: 'Đã trả đủ', color: 'bg-success/10 text-success' },
    overdue: { text: 'Quá hạn', color: 'bg-error/10 text-error' },
}

// Load data
async function loadData() {
    isLoading.value = true
    try {
        const params: Record<string, any> = {}
        if (statusFilter.value) params.status = statusFilter.value
        
        const [apResult, fundsData] = await Promise.all([
            debtService.getPayables(params),
            financeService.getFunds()
        ])
        payables.value = apResult.data
        funds.value = fundsData
        if (fundsData.length > 0) {
            selectedFundId.value = fundsData.find(f => f.is_default)?.id || fundsData[0].id
        }
    } catch (error) {
        console.error('Failed to load payables:', error)
    } finally {
        isLoading.value = false
    }
}

// Format
function formatCurrency(amount: number | string | undefined | null) {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount
    if (num === undefined || num === null || isNaN(num)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num)
}

function formatDate(date: string | undefined) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('vi-VN')
}

// Open pay modal
function openPayModal(ap: AccountPayable) {
    selectedAP.value = ap
    payAmount.value = parseFloat(String(ap.remaining_amount || 0))
    showPayModal.value = true
}

// Open detail modal  
function openDetailModal(ap: AccountPayable) {
    selectedAP.value = ap
    showDetailModal.value = true
}

// Confirm payment
async function confirmPayment() {
    if (!selectedAP.value || payAmount.value <= 0) return
    
    try {
        await debtService.payPayable(selectedAP.value.id, {
            amount: payAmount.value,
            fund_id: selectedFundId.value || undefined
        })
        await swal.success('Ghi nhận thanh toán thành công!')
        showPayModal.value = false
        await loadData()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Ghi nhận thất bại!')
    }
}

onMounted(() => {
    loadData()
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <AdminPageHeader title="Công nợ phải trả" description="Theo dõi và thanh toán cho nhà cung cấp">
            <template #actions>
                <RouterLink to="/admin/finance/dashboard">
                    <DButton variant="secondary" size="sm">← Tổng quan</DButton>
                </RouterLink>
            </template>
        </AdminPageHeader>

        <AdminSearch placeholder="Tìm theo mã, NCC..." @search="loadData">
            <template #filters>
                <select v-model="statusFilter" @change="loadData" class="form-input w-40 bg-dark-700 border-white/10 text-white">
                    <option value="">Tất cả trạng thái</option>
                    <option value="open">Chưa trả</option>
                    <option value="partial">Trả một phần</option>
                    <option value="overdue">Quá hạn</option>
                    <option value="paid">Đã trả đủ</option>
                </select>
            </template>
        </AdminSearch>

        <AdminTable :columns="columns" :data="payables" :loading="isLoading" empty-text="Không có công nợ phải trả">
            <template #cell-ap_code="{ value }">
                <code class="text-xs font-mono text-primary font-bold">{{ value }}</code>
            </template>

            <template #cell-supplier="{ item }">
                <span class="text-white">{{ item.supplier?.name || 'Chưa xác định' }}</span>
            </template>

            <template #cell-reference="{ item }">
                <span class="text-slate-400 text-sm">{{ item.reference_type || '-' }}</span>
            </template>

            <template #cell-total_amount="{ value }">
                <span class="text-white">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell-paid_amount="{ value }">
                <span class="text-success">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell-remaining_amount="{ value }">
                <span class="text-error font-semibold">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell-status="{ value }">
                <span :class="['px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold', statusLabels[value]?.color || 'bg-slate-500/10 text-slate-400']">
                    {{ statusLabels[value]?.text || value }}
                </span>
            </template>

            <template #cell-due_date="{ value }">
                <span class="text-slate-400 text-xs">{{ formatDate(value) }}</span>
            </template>

            <template #actions="{ item }">
                <div class="flex items-center justify-end gap-1">
                    <DAction v-if="item.status !== 'paid'" icon="complete" title="Thanh toán" variant="primary" @click.stop="openPayModal(item)" />
                    <DAction icon="view" title="Chi tiết" variant="info" @click.stop="openDetailModal(item)" />
                </div>
            </template>
        </AdminTable>

        <!-- Pay Modal -->
        <DModal v-model="showPayModal" title="Ghi nhận thanh toán NCC" size="md">
            <div v-if="selectedAP" class="space-y-4">
                <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                    <p class="text-xs text-slate-400 mb-1">Công nợ:</p>
                    <p class="text-white font-bold">{{ selectedAP.ap_code }} - {{ formatCurrency(selectedAP.remaining_amount) }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Số tiền thanh toán</label>
                    <input type="number" v-model="payAmount" :max="selectedAP.remaining_amount" class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Quỹ chi</label>
                    <select v-model="selectedFundId" class="form-input">
                        <option v-for="fund in funds" :key="fund.id" :value="fund.id">
                            {{ fund.name }} ({{ formatCurrency(fund.balance) }})
                        </option>
                    </select>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3">
                    <DButton variant="secondary" class="flex-1" @click="showPayModal = false">Hủy</DButton>
                    <DButton variant="primary" class="flex-1" @click="confirmPayment">Xác nhận thanh toán</DButton>
                </div>
            </template>
        </DModal>

        <!-- Detail Modal -->
        <DModal v-model="showDetailModal" title="Chi tiết công nợ phải trả" size="lg">
            <div v-if="selectedAP" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Mã công nợ</p>
                        <p class="text-white font-bold font-mono">{{ selectedAP.ap_code }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Trạng thái</p>
                        <span :class="['px-2.5 py-1 rounded-full text-xs uppercase font-bold', statusLabels[selectedAP.status]?.color]">
                            {{ statusLabels[selectedAP.status]?.text }}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Nhà cung cấp</p>
                        <p class="text-white">{{ selectedAP.supplier?.name || 'Chưa xác định' }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Tham chiếu</p>
                        <p class="text-white">{{ selectedAP.reference_type || '-' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Tổng phải trả</p>
                        <p class="text-white font-bold text-lg">{{ formatCurrency(selectedAP.total_amount) }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Đã trả</p>
                        <p class="text-success font-bold text-lg">{{ formatCurrency(selectedAP.paid_amount) }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Còn lại</p>
                        <p class="text-error font-bold text-lg">{{ formatCurrency(selectedAP.remaining_amount) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Hạn trả</p>
                        <p class="text-white">{{ formatDate(selectedAP.due_date) }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Ghi chú</p>
                        <p class="text-slate-300 text-sm">{{ selectedAP.notes || 'Không có ghi chú' }}</p>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3">
                    <DButton variant="secondary" class="flex-1" @click="showDetailModal = false">Đóng</DButton>
                    <DButton v-if="selectedAP?.status !== 'paid'" variant="primary" class="flex-1" @click="showDetailModal = false; selectedAP && openPayModal(selectedAP)">Thanh toán</DButton>
                </div>
            </template>
        </DModal>
    </div>
</template>
