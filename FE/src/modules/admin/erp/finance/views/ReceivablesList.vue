<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { debtService, type AccountReceivable } from '../services/debtService'
import { financeService, type Fund } from '../services/financeService'
import { useSwal } from '@/utils'

const swal = useSwal()

// State
const isLoading = ref(false)
const receivables = ref<AccountReceivable[]>([])
const funds = ref<Fund[]>([])
const statusFilter = ref('')
const showPayModal = ref(false)
const showDetailModal = ref(false)
const selectedAR = ref<AccountReceivable | null>(null)
const payAmount = ref(0)
const selectedFundId = ref<number | null>(null)

// Columns
const columns = [
    { key: 'ar_code', label: 'Mã' },
    { key: 'order', label: 'Đơn hàng' },
    { key: 'customer', label: 'Khách hàng' },
    { key: 'total_amount', label: 'Tổng phải thu' },
    { key: 'paid_amount', label: 'Đã thu' },
    { key: 'remaining_amount', label: 'Còn lại' },
    { key: 'status', label: 'Trạng thái' },
    { key: 'due_date', label: 'Hạn thu' },
]

// Status labels
const statusLabels: Record<string, { text: string, color: string }> = {
    open: { text: 'Chưa thu', color: 'bg-warning/10 text-warning' },
    partial: { text: 'Thu một phần', color: 'bg-info/10 text-info' },
    paid: { text: 'Đã thu đủ', color: 'bg-success/10 text-success' },
    overdue: { text: 'Quá hạn', color: 'bg-error/10 text-error' },
    written_off: { text: 'Đã xóa nợ', color: 'bg-slate-500/10 text-slate-400' },
}

// Load data
async function loadData() {
    isLoading.value = true
    try {
        const params: Record<string, any> = {}
        if (statusFilter.value) params.status = statusFilter.value
        
        const [arResult, fundsData] = await Promise.all([
            debtService.getReceivables(params),
            financeService.getFunds()
        ])
        receivables.value = arResult.data
        funds.value = fundsData
        if (fundsData.length > 0) {
            selectedFundId.value = fundsData.find(f => f.is_default)?.id || fundsData[0].id
        }
    } catch (error) {
        console.error('Failed to load receivables:', error)
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
function openPayModal(ar: AccountReceivable) {
    selectedAR.value = ar
    payAmount.value = parseFloat(String(ar.remaining_amount || 0))
    showPayModal.value = true
}

// Open detail modal
function openDetailModal(ar: AccountReceivable) {
    selectedAR.value = ar
    showDetailModal.value = true
}

// Confirm payment
async function confirmPayment() {
    if (!selectedAR.value || payAmount.value <= 0) return
    
    try {
        await debtService.payReceivable(selectedAR.value.id, {
            amount: payAmount.value,
            fund_id: selectedFundId.value || undefined
        })
        await swal.success('Ghi nhận thu tiền thành công!')
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
        <AdminPageHeader title="Công nợ phải thu" description="Theo dõi và thu tiền từ khách hàng">
            <template #actions>
                <RouterLink to="/admin/finance/dashboard">
                    <DButton variant="secondary" size="sm">← Tổng quan</DButton>
                </RouterLink>
            </template>
        </AdminPageHeader>

        <AdminSearch placeholder="Tìm theo mã, khách hàng..." @search="loadData">
            <template #filters>
                <select v-model="statusFilter" @change="loadData" class="form-input w-40 bg-dark-700 border-white/10 text-white">
                    <option value="">Tất cả trạng thái</option>
                    <option value="open">Chưa thu</option>
                    <option value="partial">Thu một phần</option>
                    <option value="overdue">Quá hạn</option>
                    <option value="paid">Đã thu đủ</option>
                </select>
            </template>
        </AdminSearch>

        <AdminTable :columns="columns" :data="receivables" :loading="isLoading" empty-text="Không có công nợ phải thu">
            <template #cell-ar_code="{ value }">
                <code class="text-xs font-mono text-primary font-bold">{{ value }}</code>
            </template>

            <template #cell-order="{ item }">
                <span class="text-white font-medium">{{ item.order?.code || '-' }}</span>
            </template>

            <template #cell-customer="{ item }">
                <span class="text-slate-300">{{ item.customer?.name || 'Khách lẻ' }}</span>
            </template>

            <template #cell-total_amount="{ value }">
                <span class="text-white">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell-paid_amount="{ value }">
                <span class="text-success">{{ formatCurrency(value) }}</span>
            </template>

            <template #cell-remaining_amount="{ value }">
                <span class="text-warning font-semibold">{{ formatCurrency(value) }}</span>
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
                    <DAction v-if="item.status !== 'paid'" icon="complete" title="Thu tiền" variant="success" @click.stop="openPayModal(item)" />
                    <DAction icon="view" title="Chi tiết" variant="info" @click.stop="openDetailModal(item)" />
                </div>
            </template>
        </AdminTable>

        <!-- Pay Modal -->
        <DModal v-model="showPayModal" title="Ghi nhận thu tiền" size="md">
            <div v-if="selectedAR" class="space-y-4">
                <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                    <p class="text-xs text-slate-400 mb-1">Công nợ:</p>
                    <p class="text-white font-bold">{{ selectedAR.ar_code }} - {{ formatCurrency(selectedAR.remaining_amount) }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Số tiền thu</label>
                    <input type="number" v-model="payAmount" :max="selectedAR.remaining_amount" class="form-input" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Quỹ thu</label>
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
                    <DButton variant="primary" class="flex-1" @click="confirmPayment">Xác nhận thu</DButton>
                </div>
            </template>
        </DModal>

        <!-- Detail Modal -->
        <DModal v-model="showDetailModal" title="Chi tiết công nợ phải thu" size="lg">
            <div v-if="selectedAR" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Mã công nợ</p>
                        <p class="text-white font-bold font-mono">{{ selectedAR.ar_code }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Trạng thái</p>
                        <span :class="['px-2.5 py-1 rounded-full text-xs uppercase font-bold', statusLabels[selectedAR.status]?.color]">
                            {{ statusLabels[selectedAR.status]?.text }}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Đơn hàng</p>
                        <p class="text-white">{{ selectedAR.order?.code || '-' }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Khách hàng</p>
                        <p class="text-white">{{ selectedAR.customer?.name || 'Khách lẻ' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Tổng phải thu</p>
                        <p class="text-white font-bold text-lg">{{ formatCurrency(selectedAR.total_amount) }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Đã thu</p>
                        <p class="text-success font-bold text-lg">{{ formatCurrency(selectedAR.paid_amount) }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Còn lại</p>
                        <p class="text-warning font-bold text-lg">{{ formatCurrency(selectedAR.remaining_amount) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Hạn thu</p>
                        <p class="text-white">{{ formatDate(selectedAR.due_date) }}</p>
                    </div>
                    <div class="bg-dark-700 p-3 rounded-lg border border-white/5">
                        <p class="text-xs text-slate-400 mb-1">Ghi chú</p>
                        <p class="text-slate-300 text-sm">{{ selectedAR.notes || 'Không có ghi chú' }}</p>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-3">
                    <DButton variant="secondary" class="flex-1" @click="showDetailModal = false">Đóng</DButton>
                    <DButton v-if="selectedAR?.status !== 'paid'" variant="primary" class="flex-1" @click="showDetailModal = false; selectedAR && openPayModal(selectedAR)">Thu tiền</DButton>
                </div>
            </template>
        </DModal>
    </div>
</template>
