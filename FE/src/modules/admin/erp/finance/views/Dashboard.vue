<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { financeService, type Fund, type FinanceSummary } from '../services/financeService'
import { debtService } from '../services/debtService'
import { useSwal } from '@/shared/utils'

const swal = useSwal()

// State
const isLoading = ref(false)
const funds = ref<Fund[]>([])
const summary = ref<FinanceSummary | null>(null)
const arSummary = ref<any>(null)
const apSummary = ref<any>(null)
const dateRange = ref({
    from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    to: new Date().toISOString().split('T')[0]
})

// Load data
async function loadDashboard() {
    isLoading.value = true
    try {
        const [fundsData, summaryData, arData, apData] = await Promise.all([
            financeService.getFunds(),
            financeService.getSummary(dateRange.value.from, dateRange.value.to),
            debtService.getReceivableSummary(),
            debtService.getPayableSummary()
        ])
        funds.value = fundsData
        summary.value = summaryData
        arSummary.value = arData
        apSummary.value = apData
    } catch (error) {
        console.error('Failed to load dashboard:', error)
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

// Computed
const totalFundBalance = computed(() => {
    return funds.value.reduce((sum, f) => sum + parseFloat(String(f.balance || 0)), 0)
})

onMounted(() => {
    loadDashboard()
})
</script>

<template>
    <div class="h-full flex flex-col p-6 space-y-6 overflow-y-auto">
        <!-- Header -->
        <AdminPageHeader title="Tổng quan Tài chính" description="Theo dõi dòng tiền và công nợ">
            <template #actions>
                <div class="flex items-center gap-3">
                    <input type="date" v-model="dateRange.from" class="form-input bg-dark-700 border-white/10 text-white text-sm" />
                    <span class="text-slate-400">đến</span>
                    <input type="date" v-model="dateRange.to" class="form-input bg-dark-700 border-white/10 text-white text-sm" />
                    <DButton variant="primary" size="sm" @click="loadDashboard" :loading="isLoading">
                        Cập nhật
                    </DButton>
                </div>
            </template>
        </AdminPageHeader>

        <!-- Fund Balances -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <DCard v-for="fund in funds" :key="fund.id" class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">{{ fund.name }}</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ formatCurrency(fund.balance) }}</p>
                    </div>
                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center', 
                        fund.type === 'cash' ? 'bg-success/20' : 'bg-primary/20']">
                        <span class="text-2xl">{{ fund.type === 'cash' ? '💵' : '🏦' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <span v-if="fund.is_default" class="text-xs px-2 py-0.5 bg-primary/20 text-primary rounded-full">Mặc định</span>
                    <span class="text-xs text-slate-500">{{ fund.code }}</span>
                </div>
            </DCard>

            <!-- Total Balance -->
            <DCard class="p-4 bg-gradient-to-r from-primary/20 to-purple-500/20 border-primary/30">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-300">Tổng số dư</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ formatCurrency(totalFundBalance) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                        <span class="text-2xl">💰</span>
                    </div>
                </div>
            </DCard>
        </div>

        <!-- Cash Flow Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <DCard class="p-4">
                <p class="text-sm text-slate-400">Tổng thu</p>
                <p class="text-2xl font-bold text-success mt-1">{{ formatCurrency(summary?.total_receipt) }}</p>
                <p class="text-xs text-slate-500 mt-2">Trong kỳ chọn</p>
            </DCard>
            <DCard class="p-4">
                <p class="text-sm text-slate-400">Tổng chi</p>
                <p class="text-2xl font-bold text-error mt-1">{{ formatCurrency(summary?.total_payment) }}</p>
                <p class="text-xs text-slate-500 mt-2">Trong kỳ chọn</p>
            </DCard>
            <DCard class="p-4">
                <p class="text-sm text-slate-400">Chênh lệch</p>
                <p :class="['text-2xl font-bold mt-1', (summary?.net ?? 0) >= 0 ? 'text-success' : 'text-error']">
                    {{ formatCurrency(summary?.net) }}
                </p>
                <p class="text-xs text-slate-500 mt-2">Thu - Chi</p>
            </DCard>
        </div>

        <!-- AR/AP Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Account Receivable -->
            <DCard class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Công nợ phải thu</h3>
                    <RouterLink to="/admin/finance/receivables" class="text-sm text-primary hover:underline">
                        Xem tất cả →
                    </RouterLink>
                </div>
                <div class="text-3xl font-bold text-warning">{{ formatCurrency(arSummary?.total_receivable) }}</div>
                <p class="text-sm text-slate-400 mt-1">Đang chờ thu từ khách hàng</p>
                
                <div v-if="arSummary?.by_status?.length" class="mt-4 space-y-2">
                    <div v-for="item in arSummary.by_status" :key="item.status" class="flex items-center justify-between text-sm">
                        <span :class="[
                            item.status === 'open' ? 'text-warning' : 
                            item.status === 'partial' ? 'text-info' : 
                            item.status === 'overdue' ? 'text-error' : 'text-slate-400'
                        ]">
                            {{ item.status === 'open' ? 'Chưa thu' : 
                               item.status === 'partial' ? 'Thu một phần' : 
                               item.status === 'overdue' ? 'Quá hạn' : item.status }}
                            ({{ item.count }})
                        </span>
                        <span class="text-white">{{ formatCurrency(item.total) }}</span>
                    </div>
                </div>
            </DCard>

            <!-- Account Payable -->
            <DCard class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Công nợ phải trả</h3>
                    <RouterLink to="/admin/finance/payables" class="text-sm text-primary hover:underline">
                        Xem tất cả →
                    </RouterLink>
                </div>
                <div class="text-3xl font-bold text-error">{{ formatCurrency(apSummary?.total_payable) }}</div>
                <p class="text-sm text-slate-400 mt-1">Đang chờ trả NCC</p>
                
                <div v-if="apSummary?.by_status?.length" class="mt-4 space-y-2">
                    <div v-for="item in apSummary.by_status" :key="item.status" class="flex items-center justify-between text-sm">
                        <span :class="[
                            item.status === 'open' ? 'text-warning' : 
                            item.status === 'partial' ? 'text-info' : 
                            item.status === 'overdue' ? 'text-error' : 'text-slate-400'
                        ]">
                            {{ item.status === 'open' ? 'Chưa trả' : 
                               item.status === 'partial' ? 'Trả một phần' : 
                               item.status === 'overdue' ? 'Quá hạn' : item.status }}
                            ({{ item.count }})
                        </span>
                        <span class="text-white">{{ formatCurrency(item.total) }}</span>
                    </div>
                </div>
            </DCard>
        </div>

        <!-- Quick Actions -->
        <DCard class="p-5">
            <h3 class="text-lg font-semibold text-white mb-4">Thao tác nhanh</h3>
            <div class="flex flex-wrap gap-3">
                <RouterLink to="/admin/finance/expenses">
                    <DButton variant="secondary">📊 Thu chi</DButton>
                </RouterLink>
                <RouterLink to="/admin/finance/expense-categories">
                    <DButton variant="secondary">📁 Danh mục</DButton>
                </RouterLink>
                <RouterLink to="/admin/finance/receivables">
                    <DButton variant="secondary">📥 Công nợ phải thu</DButton>
                </RouterLink>
                <RouterLink to="/admin/finance/payables">
                    <DButton variant="secondary">📤 Công nợ phải trả</DButton>
                </RouterLink>
            </div>
        </DCard>
    </div>
</template>
