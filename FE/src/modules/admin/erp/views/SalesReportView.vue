<script setup lang="ts">
import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface SalesReport {
    by_status: Array<{ status: string; count: number; total: number }>
    by_day: Array<{ date: string; count: number; total: number }>
    top_products: Array<{ id: number; name: string; total_qty: number; total_value: number }>
}

const report = ref<SalesReport | null>(null)
const isLoading = ref(true)
const fromDate = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0])
const toDate = ref(new Date().toISOString().split('T')[0])

async function fetchReport() {
    try {
        isLoading.value = true
        const response = await httpClient.get('/admin/reports/sales', {
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

function getStatusLabel(status: string): string {
    const labels: Record<string, string> = {
        'pending': 'Chờ xử lý', 'confirmed': 'Đã xác nhận', 'processing': 'Đang xử lý',
        'shipped': 'Đang giao', 'delivered': 'Đã giao', 'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy', 'returned': 'Đã trả'
    }
    return labels[status] || status
}

onMounted(() => fetchReport())
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Báo cáo Bán hàng</h1>
                <p class="text-slate-400 mt-1">Phân tích doanh số theo thời gian và sản phẩm</p>
            </div>
            <div class="flex items-center gap-4">
                <input v-model="fromDate" type="date" class="form-input" @change="fetchReport" />
                <span class="text-slate-400">đến</span>
                <input v-model="toDate" type="date" class="form-input" @change="fetchReport" />
            </div>
        </div>

        <div class="flex-1 overflow-auto">
            <div v-if="isLoading" class="flex items-center justify-center h-64">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="report" class="grid grid-cols-2 gap-6">
                <!-- By Status -->
                <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Theo trạng thái</h3>
                    <div class="space-y-2">
                        <div v-for="item in report.by_status" :key="item.status"
                            class="flex justify-between items-center py-2 border-b border-white/5">
                            <span class="text-slate-300">{{ getStatusLabel(item.status) }}</span>
                            <div class="text-right">
                                <span class="text-white">{{ item.count }} đơn</span>
                                <span class="text-slate-500 ml-2">{{ formatCurrency(Number(item.total)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-dark-800 rounded-xl border border-white/10 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Top sản phẩm bán chạy</h3>
                    <div class="space-y-2">
                        <div v-for="(item, idx) in report.top_products" :key="item.id"
                            class="flex justify-between items-center py-2 border-b border-white/5">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold">
                                    {{ idx + 1 }}
                                </span>
                                <span class="text-slate-300">{{ item.name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-white">{{ item.total_qty }} sp</span>
                                <span class="text-success ml-2">{{ formatCurrency(Number(item.total_value)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- By Day -->
                <div class="col-span-2 bg-dark-800 rounded-xl border border-white/10 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Doanh số theo ngày</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs text-slate-400">Ngày</th>
                                    <th class="px-3 py-2 text-right text-xs text-slate-400">Số đơn</th>
                                    <th class="px-3 py-2 text-right text-xs text-slate-400">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="item in report.by_day" :key="item.date">
                                    <td class="px-3 py-2 text-slate-300">{{ item.date }}</td>
                                    <td class="px-3 py-2 text-right text-white">{{ item.count }}</td>
                                    <td class="px-3 py-2 text-right text-success">{{ formatCurrency(Number(item.total))
                                        }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
