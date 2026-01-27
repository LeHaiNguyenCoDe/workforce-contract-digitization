<script setup lang="ts">
import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface PnLReport {
    period: { from: string; to: string }
    revenue: { sales: number; other_income: number; total: number }
    costs: { cogs: number }
    gross_profit: number
    gross_margin: number
    expenses: { total: number; by_category: Array<{ category?: { name: string }; total: number }> }
    operating_income: number
    operating_margin: number
    summary: { order_count: number; avg_order_value: number }
}

const report = ref<PnLReport | null>(null)
const isLoading = ref(true)
const fromDate = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0])
const toDate = ref(new Date().toISOString().split('T')[0])

async function fetchReport() {
    try {
        isLoading.value = true
        const response = await httpClient.get('/admin/reports/pnl', {
            params: { from_date: fromDate.value, to_date: toDate.value }
        })
        report.value = (response.data as any).data
    } catch (error) {
        console.error('Failed to fetch report:', error)
    } finally {
        isLoading.value = false
    }
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

onMounted(() => fetchReport())
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Báo cáo Lợi nhuận (P&L)</h1>
                <p class="text-slate-400 mt-1">Doanh thu - Giá vốn - Chi phí = Lợi nhuận ròng</p>
            </div>
            <div class="flex items-center gap-4">
                <input v-model="fromDate" type="date" class="form-input" @change="fetchReport" />
                <span class="text-slate-400">đến</span>
                <input v-model="toDate" type="date" class="form-input" @change="fetchReport" />
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-auto">
            <div v-if="isLoading" class="flex items-center justify-center h-64">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="report" class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                        <p class="text-slate-400 text-sm">Đơn hàng</p>
                        <p class="text-2xl font-bold text-white">{{ report.summary.order_count }}</p>
                    </div>
                    <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                        <p class="text-slate-400 text-sm">Doanh thu</p>
                        <p class="text-2xl font-bold text-success">{{ formatCurrency(report.revenue.sales) }}</p>
                    </div>
                    <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                        <p class="text-slate-400 text-sm">Lợi nhuận gộp</p>
                        <p class="text-2xl font-bold text-info">{{ formatCurrency(report.gross_profit) }}</p>
                        <p class="text-xs text-slate-500">Margin: {{ report.gross_margin }}%</p>
                    </div>
                    <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                        <p class="text-slate-400 text-sm">Lợi nhuận ròng</p>
                        <p class="text-2xl font-bold"
                            :class="report.operating_income >= 0 ? 'text-success' : 'text-error'">
                            {{ formatCurrency(report.operating_income) }}
                        </p>
                        <p class="text-xs text-slate-500">Margin: {{ report.operating_margin }}%</p>
                    </div>
                </div>

                <!-- P&L Statement -->
                <div class="bg-dark-800 rounded-xl border border-white/10 overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/10">
                        <h3 class="text-lg font-semibold text-white">Báo cáo Lợi nhuận</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Revenue -->
                        <div class="flex justify-between items-center py-2 border-b border-white/5">
                            <span class="text-white font-medium">Doanh thu bán hàng</span>
                            <span class="text-success font-mono">{{ formatCurrency(report.revenue.sales) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-white/5 pl-4">
                            <span class="text-slate-400">Thu nhập khác</span>
                            <span class="text-slate-300 font-mono">{{ formatCurrency(report.revenue.other_income)
                                }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center py-2 border-b border-white/10 bg-dark-700 -mx-6 px-6">
                            <span class="text-white font-semibold">Tổng doanh thu</span>
                            <span class="text-success font-mono font-semibold">{{ formatCurrency(report.revenue.total)
                                }}</span>
                        </div>

                        <!-- COGS -->
                        <div class="flex justify-between items-center py-2 border-b border-white/5">
                            <span class="text-white font-medium">Giá vốn hàng bán (COGS)</span>
                            <span class="text-error font-mono">({{ formatCurrency(report.costs.cogs) }})</span>
                        </div>
                        <div
                            class="flex justify-between items-center py-2 border-b border-white/10 bg-dark-700 -mx-6 px-6">
                            <span class="text-white font-semibold">Lợi nhuận gộp</span>
                            <span class="text-info font-mono font-semibold">{{ formatCurrency(report.gross_profit)
                                }}</span>
                        </div>

                        <!-- Expenses -->
                        <div class="flex justify-between items-center py-2 border-b border-white/5">
                            <span class="text-white font-medium">Chi phí vận hành</span>
                            <span class="text-error font-mono">({{ formatCurrency(report.expenses.total) }})</span>
                        </div>
                        <div v-for="cat in report.expenses.by_category" :key="String(cat.category?.name)"
                            class="flex justify-between items-center py-1 pl-4">
                            <span class="text-slate-400 text-sm">{{ cat.category?.name || 'Khác' }}</span>
                            <span class="text-slate-400 font-mono text-sm">{{ formatCurrency(Number(cat.total))
                                }}</span>
                        </div>

                        <!-- Net Income -->
                        <div class="flex justify-between items-center py-3 bg-dark-700 -mx-6 px-6 mt-4 rounded-b-xl">
                            <span class="text-white text-lg font-bold">LỢI NHUẬN RÒNG</span>
                            <span class="text-xl font-mono font-bold"
                                :class="report.operating_income >= 0 ? 'text-success' : 'text-error'">
                                {{ formatCurrency(report.operating_income) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
